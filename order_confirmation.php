<?php
session_start();
include('connection.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: signin.php');
    exit;
}

// Get the order ID from the URL
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

$order = null;
$orderItems = [];

// Fetch order details
if ($order_id > 0) {
    $orderSql = "SELECT o.order_id, o.name, o.email, o.address, o.order_date, o.total_amount, o.payment_method 
                 FROM orders o 
                 WHERE o.order_id = ?";
    $orderStmt = $conn->prepare($orderSql);
    $orderStmt->bind_param("i", $order_id);
    $orderStmt->execute();
    $orderResult = $orderStmt->get_result();

    if ($orderResult->num_rows > 0) {
        $order = $orderResult->fetch_assoc();

        // Fetch order items
        $itemsSql = "SELECT oi.product_id, oi.quantity, oi.price, p.product_name 
                     FROM order_items oi 
                     JOIN products p ON oi.product_id = p.id 
                     WHERE oi.order_id = ?";
        $itemsStmt = $conn->prepare($itemsSql);
        $itemsStmt->bind_param("i", $order_id);
        $itemsStmt->execute();
        $itemsResult = $itemsStmt->get_result();

        while ($item = $itemsResult->fetch_assoc()) {
            $orderItems[] = $item;
        }

        // Close statements
        $itemsStmt->close();
    } else {
        $error_message = "Order not found.";
    }
    
    // Close order statement
    $orderStmt->close();
} else {
    $error_message = "Invalid order ID.";
}

// After purchase is successful
$deleteWishlist = "DELETE FROM wishlist WHERE user_id = ? AND product_id = ?";
$stmt = $conn->prepare($deleteWishlist);
$stmt->bind_param("ii", $userId, $productId);
$stmt->execute();


// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 20px;
        }
        .receipt {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        p {
            color: #555;
        }
        .order-details, .order-items {
            margin: 20px 0;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .order-items table {
            width: 100%;
            border-collapse: collapse;
        }
        .order-items th, .order-items td {
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
        }
        .order-items th {
            background-color: #f2f2f2;
        }
        .total {
            font-weight: bold;
            font-size: 1.2em;
        }
        .continue-shopping {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="receipt">
    <?php if (isset($error_message)): ?>
        <p><?php echo $error_message; ?></p>
    <?php elseif ($order): ?>
        <h1>Order Confirmation</h1>
        <p>Thank you for your order, <strong><?php echo htmlspecialchars($order['name']); ?></strong>!</p>
        <div class="order-details">
            <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($order['address']); ?></p>
            <p><strong>Order Date:</strong> <?php echo date("F j, Y, g:i a", strtotime($order['order_date'])); ?></p>
            <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
            <p class="total"><strong>Total Amount:</strong> ₱<?php echo number_format($order['total_amount'], 2); ?></p>
            <h3>The Electronic Receipt will sent to your email shortly.</h3>
        </div> <!-- close order-details -->

        <h2>Items Ordered:</h2>
        <div class="order-items">
            <table>
                <tr><th>Product Name</th><th>Quantity</th><th>Price</th><th>Total Price</th></tr>
                <?php foreach ($orderItems as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                        <td>₱<?php echo number_format($item['price'], 2); ?></td>
                        <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>

                    </tr>
                <?php endforeach; ?>
            </table>
        </div> <!-- close order-items -->
    <?php else: ?>
        <h1>Error</h1>
        <p>Invalid order details.</p>
    <?php endif; ?>
</div> <!-- close receipt -->

<!-- Optional: Link back to shop -->
<div class="continue-shopping">
    <p><a href="userDashboard.php">Continue Shopping</a></p>
</div>

</body>
</html>
