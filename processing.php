<?php
session_start();
include('connection.php');
require 'vendor/autoload.php'; // Ensure PHPMailer is loaded

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: signin.php');
    exit;
}

// Retrieve order details from the form
$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'];
$quantity = intval($_POST['quantity']);
$total = floatval($_POST['total']);
$name = $_POST['name'];
$email = $_POST['email'];
$address = $_POST['address'];
$payment_method = $_POST['payment_method'];

// Fetch product details to get the price and product name
$sql = "SELECT product_name, price, stocks FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$product_name = $product['product_name'];
$product_price = $product['price'];
$current_stock = $product['stocks'];

// Check if there is enough stock
if ($quantity > $current_stock) {
    echo "Insufficient stock available.";
    exit;
}

// Calculate the total amount for the order
$total_amount = $product_price * $quantity;

// Insert order into `orders` table
$order_sql = "INSERT INTO orders (user_id, name, email, address, total_amount, payment_method) VALUES (?, ?, ?, ?, ?, ?)";
$order_stmt = $conn->prepare($order_sql);
$order_stmt->bind_param("isssds", $user_id, $name, $email, $address, $total_amount, $payment_method);
$order_stmt->execute();
$order_id = $order_stmt->insert_id; // Get the new order ID

// Insert the product into `order_items` table
$order_item_sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
$order_item_stmt = $conn->prepare($order_item_sql);
$order_item_stmt->bind_param("iiid", $order_id, $product_id, $quantity, $product_price);
$order_item_stmt->execute();

// Update the stock of the product
$new_stock = $current_stock - $quantity;
$update_stock_sql = "UPDATE products SET stocks = ? WHERE id = ?";
$update_stock_stmt = $conn->prepare($update_stock_sql);
$update_stock_stmt->bind_param("ii", $new_stock, $product_id);
$update_stock_stmt->execute();

// Prepare and send the email receipt
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
        <ul>
            <li><strong>Product:</strong> {$product_name}</li>
            <li><strong>Quantity:</strong> {$quantity}</li>
            <li><strong>Total Amount:</strong> ₱" . number_format($total_amount, 2) . "</li>
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
$order_item_stmt->close();
$order_stmt->close();
$update_stock_stmt->close();
$conn->close();

// Redirect to order confirmation page
header("Location: order_confirmation.php?order_id=$order_id");
exit;
?>
