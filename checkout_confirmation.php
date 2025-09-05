<?php
session_start();
include('connection.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: signin.php');
    exit;
}

// Retrieve the order details from the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
    $total = isset($_POST['total']) ? htmlspecialchars($_POST['total']) : 0;
    $paymentMethod = isset($_POST['payment_method']) ? htmlspecialchars($_POST['payment_method']) : '';

    // Fetch product details from the database
    $sql = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    // Fetch user's details from the database
    $userId = $_SESSION['user_id'];
    $userSql = "SELECT fname, lname, email_or_phone, address FROM users WHERE user_id = ?";
    $userStmt = $conn->prepare($userSql);
    $userStmt->bind_param("i", $userId);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    $userDetails = $userResult->fetch_assoc();

    // Close the database connection
    $stmt->close();
    $userStmt->close();
    $conn->close();

    // Check if product and user details were found
    if (!$product || !$userDetails) {
        echo "Order details not found.";
        exit;
    }
} else {
    echo "Invalid request.";
    exit;
}
// After purchase is successful
$deleteWishlist = "DELETE FROM wishlist WHERE user_id = ? AND product_id = ?";
$stmt = $conn->prepare($deleteWishlist);
$stmt->bind_param("ii", $userId, $productId);
$stmt->execute();

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
        .order-details {
            margin: 20px 0;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
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
    <h1>Thank You for Your Order!</h1>
    <p>Your order has been successfully placed. Here are the details:</p>

    <div class="order-details">
        <h2>Order Summary</h2>
        <p><strong>Product:</strong> <?= htmlspecialchars($product['product_name']) ?></p>
        <p><strong>Quantity:</strong> <?= htmlspecialchars($quantity) ?></p>
        <p><strong>Price:</strong> $<?= number_format($product['price'], 2) ?></p>
        <p class="total"><strong>Total:</strong> $<?= number_format($total, 2) ?></p>
    </div>

    <h2>Customer Information</h2>
    <div class="order-details">
        <p><strong>Name:</strong> <?= htmlspecialchars($userDetails['fname'] . ' ' . $userDetails['lname']) ?></p>
        <p><strong>Email or Phone:</strong> <?= htmlspecialchars($userDetails['email_or_phone']) ?></p>
        <p><strong>Shipping Address:</strong> <?= htmlspecialchars($userDetails['address']) ?></p>
    </div>

    <h2>Payment Method</h2>
    <div class="order-details">
        <p><strong>Selected Method:</strong> <?= htmlspecialchars(ucwords(str_replace('_', ' ', $paymentMethod))) ?></p>
    </div>

    <p>If you have any questions, feel free to contact our support team.</p>
</div> <!-- close receipt -->

<!-- Optional: Link back to shop -->
<div class="continue-shopping">
    <p><a href="userDashboard.php">Return to Home</a></p>
</div>

</body>
</html>
