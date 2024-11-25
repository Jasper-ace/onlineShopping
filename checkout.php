<?php
session_start();
include('connection.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: signin.php');
    exit;
}

// Fetch user's cart items from the database
$userId = $_SESSION['user_id'];
$sql = "SELECT products.id AS product_id, products.product_name, products.price, products.picture, cart.quantity 
        FROM cart 
        JOIN products ON cart.product_id = products.id 
        WHERE cart.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$cartItems = [];
while ($row = $result->fetch_assoc()) {
    $cartItems[] = $row;
}

// Fetch user's details from the database
$userSql = "SELECT fname, lname, email_or_phone, address FROM users WHERE user_id = ?";
$userStmt = $conn->prepare($userSql);
$userStmt->bind_param("i", $userId);
$userStmt->execute();
$userResult = $userStmt->get_result();
$userDetails = $userResult->fetch_assoc();

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

        <?php if (!empty($cartItems)) : ?>
            <?php $totalAmount = 0; ?>
            <?php foreach ($cartItems as $item) : ?>
                <div class="cart-item">
                    <img src="<?= htmlspecialchars($item['picture']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>">
                    <h3><?= htmlspecialchars($item['product_name']) ?></h3>
                    <div class="details">
                        <span class="price">$<?= number_format($item['price'], 2) ?></span>
                        <span class="quantity">Quantity: <?= htmlspecialchars($item['quantity']) ?></span>
                    </div>
                </div>
                <?php
                $totalAmount += $item['price'] * $item['quantity'];
                ?>
            <?php endforeach; ?>

            <div class="cart-total">
                Total: $<?= number_format($totalAmount, 2) ?>
            </div>

            <h2>Customer Details</h2>
            <form method="post" action="process_checkout.php">
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

                <input type="hidden" name="name" value="<?= htmlspecialchars($userDetails['fname'] . ' ' . $userDetails['lname']) ?>">
                <input type="hidden" name="email" value="<?= htmlspecialchars($userDetails['email_or_phone']) ?>">
                <input type="hidden" name="address" value="<?= htmlspecialchars($userDetails['address']) ?>">
                <input type="hidden" name="total" value="<?= $totalAmount ?>">

                <button type="submit" class="checkout-btn">Confirm Order</button>
            </form>

        <?php else : ?>
            <p>Your cart is empty.</p>
        <?php endif; ?>
    </div>
</body>

</html>
