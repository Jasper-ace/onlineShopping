<?php
session_start();
include('connection.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: signin.php');
    exit;
}

// Get the product ID and quantity from the URL
$productId = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
$quantity = isset($_GET['quantity']) ? intval($_GET['quantity']) : 0;

// Fetch product details from the database
$sql = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

// Check if the product was found
if (!$product) {
    echo "Product not found.";
    exit;
}

// Fetch user's details from the database
$userId = $_SESSION['user_id']; // Ensure user_id is set in the session
$userSql = "SELECT fname, lname, email_or_phone, address FROM users WHERE user_id = ?";
$userStmt = $conn->prepare($userSql);
$userStmt->bind_param("i", $userId);
$userStmt->execute();
$userResult = $userStmt->get_result();
$userDetails = $userResult->fetch_assoc();

// After purchase is successful
$deleteWishlist = "DELETE FROM wishlist WHERE user_id = ? AND product_id = ?";
$stmt = $conn->prepare($deleteWishlist);
$stmt->bind_param("ii", $userId, $productId);
$stmt->execute();


// Check if user details were found
if (!$userDetails) {
    echo "User details not found.";
    exit;
}

// Close the database connection
$stmt->close();
$userStmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f9fc;
            color: #333;
            margin: 0;
        }

        .container {
            max-width: 800px;
            margin: auto;
            padding: 20px;
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        .cart-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
        }

        .cart-item h3 {
            margin: 0;
        }

        .cart-item .details {
            display: flex;
            align-items: center;
        }

        .cart-item .price {
            font-weight: bold;
            color: #009688;
            margin-right: 20px;
        }

        .cart-total {
            text-align: right;
            font-size: 1.2rem;
            font-weight: bold;
            margin-top: 20px;
        }

        .checkout-btn {
            background-color: #009688;
            color: white;
            padding: 8px 16px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        .checkout-btn:hover {
            background-color: #007d6a;
        }

        .payment-method {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Checkout</h1>

        <div class="cart-item">
            <img src="<?= htmlspecialchars($product['picture']) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>">
            <h3><?= htmlspecialchars($product['product_name']) ?></h3>
            <div class="details">
                <span class="price">₱<?= number_format($product['price'], 2) ?></span>
                <span class="quantity">Quantity: <?= htmlspecialchars($quantity) ?></span>
            </div>
        </div>

        <div class="cart-total">
            Total: ₱<?= number_format($product['price'] * $quantity, 2) ?>
        </div>

        <h2>Customer Details</h2>
<form method="post" action="processing.php">
    <div>
        <label for="name">Full Name:</label>
        <span id="name"><?= htmlspecialchars($userDetails['fname'] . ' ' . $userDetails['lname']) ?></span>
    </div>
    <div>
        <label for="email">Email or Phone:</label>
        <span id="email"><?= htmlspecialchars($userDetails['email_or_phone']) ?></span>
    </div>
    <div>
        <label for="address">Shipping Address:</label>
        <span id="address"><?= htmlspecialchars($userDetails['address']) ?></span>
    </div>

    <h2>Payment Method</h2>
    <div class="payment-method">
        <label>
            <input type="radio" name="payment_method" value="cash_on_delivery" required>
            Cash on Delivery
        </label>
        <br>
        <label>
            <input type="radio" name="payment_method" value="credit_card" required>
            Pay via Card
        </label>
    </div>

    <input type="hidden" name="product_id" value="<?= $productId ?>">
    <input type="hidden" name="quantity" value="<?= $quantity ?>">
    <input type="hidden" name="total" value="<?= number_format($product['price'] * $quantity, 2) ?>">
    
    <!-- Hidden fields for user details -->
    <input type="hidden" name="name" value="<?= htmlspecialchars($userDetails['fname'] . ' ' . $userDetails['lname']) ?>">
    <input type="hidden" name="email" value="<?= htmlspecialchars($userDetails['email_or_phone']) ?>">
    <input type="hidden" name="address" value="<?= htmlspecialchars($userDetails['address']) ?>">

    <button type="submit" class="checkout-btn">Confirm Order</button>
</form>

    </div>
</body>
</html>
