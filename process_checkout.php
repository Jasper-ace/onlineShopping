<?php
session_start();
include('connection.php');

// Include PHPMailer autoload file
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';  // If using Composer, otherwise adjust the path accordingly

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: signin.php');
    exit;
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $userId = $_SESSION['user_id'];
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $address = htmlspecialchars(trim($_POST['address']));
    $totalAmount = htmlspecialchars(trim($_POST['total']));
    $paymentMethod = htmlspecialchars(trim($_POST['payment_method']));

    // Insert order into the orders table
    $orderSql = "INSERT INTO orders (user_id, name, email, address, total_amount, payment_method, order_date) VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $orderStmt = $conn->prepare($orderSql);
    $orderStmt->bind_param("isssis", $userId, $name, $email, $address, $totalAmount, $paymentMethod);

    if ($orderStmt->execute()) {
        $orderId = $orderStmt->insert_id;

        // Fetch user's cart items
        $cartSql = "SELECT product_id, quantity FROM cart WHERE user_id = ?";
        $cartStmt = $conn->prepare($cartSql);
        $cartStmt->bind_param("i", $userId);
        $cartStmt->execute();
        $cartResult = $cartStmt->get_result();

        // Insert each cart item into the order_items table
        $orderItems = [];
        while ($cartItem = $cartResult->fetch_assoc()) {
            $productId = $cartItem['product_id'];
            $quantity = $cartItem['quantity'];

            // Fetch product details
            $productSql = "SELECT product_name, price, stocks FROM products WHERE id = ?";
            $productStmt = $conn->prepare($productSql);
            $productStmt->bind_param("i", $productId);
            $productStmt->execute();
            $productResult = $productStmt->get_result();
            $product = $productResult->fetch_assoc();

            // Check for sufficient stock
            if ($quantity > $product['stocks']) {
                echo "Insufficient stock for {$product['product_name']}.";
                exit;
            }

            // Insert into order_items
            $orderItemSql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
            $orderItemStmt = $conn->prepare($orderItemSql);
            $orderItemStmt->bind_param("iiid", $orderId, $productId, $quantity, $product['price']);
            $orderItemStmt->execute();

            // Update stock in the products table
            $newStock = $product['stocks'] - $quantity;
            $updateStockSql = "UPDATE products SET stocks = ? WHERE id = ?";
            $updateStockStmt = $conn->prepare($updateStockSql);
            $updateStockStmt->bind_param("ii", $newStock, $productId);
            $updateStockStmt->execute();

            // Add to orderItems for the receipt
            $orderItems[] = [
                'name' => $product['product_name'],
                'price' => $product['price'],
                'quantity' => $quantity,
                'total' => $product['price'] * $quantity
            ];
        }

        // Clear the user's cart after successful order
        $clearCartSql = "DELETE FROM cart WHERE user_id = ?";
        $clearCartStmt = $conn->prepare($clearCartSql);
        $clearCartStmt->bind_param("i", $userId);
        $clearCartStmt->execute();

        // Generate the e-receipt
        $receipt = "Order ID: $orderId\n";
        $receipt .= "Name: $name\n";
        $receipt .= "Email: $email\n";
        $receipt .= "Address: $address\n\n";
        $receipt .= "Items Ordered:\n";

        foreach ($orderItems as $item) {
            $receipt .= "{$item['name']} (Qty: {$item['quantity']}) - ₱" . number_format($item['total'], 2) . "\n";
        }
        $receipt .= "Total Amount: ₱" . number_format($totalAmount, 2) . "\n";
        $receipt .= "Payment Method: $paymentMethod\n";
        $receipt .= "Thank you for your order!\n";

        // Send confirmation email using PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'lazadaconfirmation@gmail.com'; 
            $mail->Password   = 'rbyo zvfg bedp birq'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('no-reply@yourdomain.com', 'SwiftShop');
            $mail->addAddress($email, $name);

            $mail->isHTML(true);
            $mail->Subject = 'Order Confirmation and Receipt';
            $mail->Body    = "
                <h1>Thank you for your order!</h1>
                <p>Dear {$name},</p>
                <p>We have received your order with the following details:</p>
                <ul>";
                
            foreach ($orderItems as $item) {
                $mail->Body .= "<li><strong>Product:</strong> {$item['name']}</li>
                                <li><strong>Quantity:</strong> {$item['quantity']}</li>
                                <li><strong>Total Amount:</strong> ₱" . number_format($item['total'], 2) . "</li>";
            }

            $mail->Body .= "
                </ul>
                <p>Your order will be delivered to the following address:</p>
                <p>{$address}</p>
                <p>Thank you for shopping with us!</p>
                <p><strong>SwiftShop</strong></p>
                <p><strong>Please contact our customer support at support@SwiftShop.com if you have any questions or concerns.</strong></p>
            ";

            $mail->send();
            echo "Receipt sent to {$email}.";
        } catch (Exception $e) {
            echo "Receipt could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

        // Close database connections
        $orderItemStmt->close();
        $orderStmt->close();
        $updateStockStmt->close();
        $conn->close();

        // Redirect to order confirmation page
        header("Location: order_confirmation.php?order_id=$orderId");
        exit;
    } else {
        echo "Error placing the order.";
    }
}
?>
