<?php
session_start();
include('connection.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: signin.php');
    exit;
}

// Handle remove from cart action
if (isset($_POST['remove'])) {
    $productId = $_POST['product_id'];
    $sql = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $_SESSION['user_id'], $productId);
    $stmt->execute();
    $stmt->close();
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

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f7f9fc; color: #333; margin: 0; }
        .container { max-width: 800px; margin: auto; padding: 20px; }
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background-color: #1f3b73;
            color: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .navbar h1 {
            margin: 0;
            font-size: 1.8rem;
        }
        .navbar a {
            color: white;
            text-decoration: none;
            font-size: 1rem;
            padding: 10px;
            transition: background-color 0.3s ease;
        }
        .navbar a:hover {
            background-color: #15315b;
            border-radius: 5px;
        }
        .navbar form {
            display: flex;
            position: relative;
        }
        .navbar input[type="text"] {
            padding: 8px;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            width: 250px;
            margin-right: 10px;
        }
        .navbar button {
            padding: 8px 15px;
            border: none;
            background-color: #009688;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s ease;
        }
        .navbar button:hover {
            background-color: #007d6a;
        }
        .burger-menu {
            font-size: 1.8rem;
            cursor: pointer;
            position: relative;
        }
        .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background-color: white;
            border: 1px solid #ccc;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 150px;
            z-index: 1000;
        }
        .dropdown-menu a {
            display: block;
            padding: 10px;
            color: #333;
            text-decoration: none;
        }
        .dropdown-menu a:hover {
            background-color: #f0f0f0;
        }
        @media (max-width: 768px) {
            .navbar h1, .navbar form, .navbar a {
                display: none;
            }
            .burger-menu {
                display: block;
            }
        }
        .cart-item { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; padding: 10px; border-bottom: 1px solid #ddd; }
        .cart-item img { width: 80px; height: 80px; object-fit: cover; }
        .cart-item h3 { margin: 0; }
        .cart-item .details { display: flex; align-items: center; }
        .cart-item .price { font-weight: bold; color: #009688; margin-right: 20px; }
        .cart-item .quantity { margin-right: 20px; }
        .cart-total { text-align: right; font-size: 1.2rem; font-weight: bold; margin-top: 20px; }
        .checkout-btn { background-color: #009688; color: white; padding: 8px 16px; border: none; cursor: pointer; border-radius: 5px; }
        .checkout-btn:hover { background-color: #007d6a; }
        .select-product { margin-right: 10px; }
    </style>
    <script>
        function updateTotal() {
            let checkboxes = document.querySelectorAll('.select-product');
            let total = 0;

            checkboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    let price = parseFloat(checkbox.getAttribute('data-price'));
                    let quantity = parseInt(checkbox.getAttribute('data-quantity'));
                    total += price * quantity;
                }
            });

            document.getElementById('totalAmount').textContent = "Total: ₱" + total.toFixed(2);
        }
        
        function toggleDropdown() {
            const dropdownMenu = document.querySelector(".dropdown-menu");
            dropdownMenu.style.display = dropdownMenu.style.display === "block" ? "none" : "block";
        }

        document.addEventListener("click", function(event) {
            const isClickInside = document.querySelector(".burger-menu").contains(event.target);
            const dropdownMenu = document.querySelector(".dropdown-menu");

            if (!isClickInside && dropdownMenu.style.display === "block") {
                dropdownMenu.style.display = "none";
            }
        });
    </script>
</head>
<body>
<div class="navbar">
    <a href="userDashboard.php">
        <h1>SwiftShop</h1>
    </a>
    <div class="burger-menu" onclick="toggleDropdown()">
        <i class="fas fa-bars"></i>
        <div class="dropdown-menu">
            <a href="Userprofile.php">Profile</a>
            <a href="cart.php">My Cart</a>
            <a href="userLogout.php">Logout</a>
        </div>
    </div>
</div>

<div class="container">
    <h1>My Cart</h1>

    <?php if (!empty($cartItems)) : ?>
        <?php foreach ($cartItems as $item) : ?>
            <div class="cart-item">
                <input type="checkbox" name="selected_products[]" value="<?= $item['product_id'] ?>" class="select-product" data-price="<?= $item['price'] ?>" data-quantity="<?= $item['quantity'] ?>" onchange="updateTotal()">
                <img src="<?= $item['picture'] ?>" alt="<?= $item['product_name'] ?>">
                <h3><?= $item['product_name'] ?></h3>
                <div class="details">
                    <span class="price">₱<?= number_format($item['price'], 2) ?></span>
                    <span class="quantity">Quantity: <?= $item['quantity'] ?></span>
                </div>

                <form method="post" action="cart.php" style="display: inline;">
                    <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                    <button type="submit" name="remove" class="remove-btn">Remove</button>
                </form>
            </div>
        <?php endforeach; ?>

        <div id="totalAmount" class="cart-total">
            Total: ₱0.00
        </div>

        <form method="post" action="checkout.php">
            <button type="submit" class="checkout-btn">Proceed to Checkout</button>
        </form>

    <?php else : ?>
        <p>Your cart is empty.</p>
    <?php endif; ?>
</div>

</body>
</html>
