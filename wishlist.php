<?php
session_start();
include("connection.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Fetch wishlist products with shop info
$query = "
    SELECT w.id AS wishlist_id, 
           p.id AS product_id, 
           p.product_name, 
           p.price, 
           p.picture, 
           a.shopname
    FROM wishlist w
    JOIN products p ON w.product_id = p.id
    JOIN admin a ON w.admin_id = a.id
    WHERE w.user_id = ?
    ORDER BY w.created_at DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Wishlist</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 0;
        }
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background-color: #1f3b73;
            color: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .navbar h1 { margin: 0; font-size: 1.8rem; color: white; }
        .navbar a { color: white; text-decoration: none; font-size: 1rem; padding: 10px; }
        .navbar a:hover { background-color: #15315b; border-radius: 5px; }
        .burger-menu { font-size: 1.8rem; cursor: pointer; position: relative; }
        .dropdown-menu {
            display: none; position: absolute; top: 100%; right: 0;
            background-color: white; border: 1px solid #ccc; box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            width: 150px; z-index: 1000;
        }
        .dropdown-menu a { display: block; padding: 10px; color: #333; text-decoration: none; }
        .dropdown-menu a:hover { background-color: #f0f0f0; }
        @media (max-width: 768px) {
            .navbar h1, .navbar a { display: none; }
            .burger-menu { display: block; }
        }

        h1.page-title { text-align: center; margin: 20px 0; color: #333; }
        .wishlist-container { max-width: 900px; margin: 0 auto; padding: 20px; }
        .wishlist-item {
            display: flex; align-items: center; background: #fff; border-radius: 10px;
            padding: 15px; margin-bottom: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .wishlist-item img {
            width: 100px; height: 100px; object-fit: cover;
            border-radius: 8px; margin-right: 20px; cursor: pointer;
        }
        .wishlist-info { flex: 1; }
        .wishlist-info h3 { margin: 0 0 5px; font-size: 18px; color: #222; }
        .wishlist-info h3 a { text-decoration: none; color: #1f3b73; }
        .wishlist-info h3 a:hover { text-decoration: underline; }
        .wishlist-info p { margin: 2px 0; font-size: 14px; color: #666; }
        .remove-btn {
            background: none; border: none; color: red;
            font-size: 20px; cursor: pointer;
        }
        .remove-btn:hover { color: darkred; }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="userDashboard.php"><h1>SwiftShop</h1></a>
        <div class="burger-menu" onclick="toggleDropdown()">
            <i class="fas fa-bars"></i>
            <div class="dropdown-menu">
                <a href="Userprofile.php">Profile</a>
                <a href="cart.php">My Cart</a>
                <a href="wishlist.php">Wishlist</a>
                <a href="userPurchase.php">My Purchase</a>
                <a href="userLogout.php">Logout</a>
            </div>
        </div>
    </div>

    <h1 class="page-title">Your Wishlist</h1>

    <div class="wishlist-container">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="wishlist-item" id="wishlist-<?php echo $row['wishlist_id']; ?>">
                    <!-- Make product clickable -->
                    <a href="product_detail.php?id=<?php echo $row['product_id']; ?>">
                        <img src="<?php echo htmlspecialchars($row['picture']); ?>" alt="Product">
                    </a>
                    <div class="wishlist-info">
                        <h3>
                            <a href="product_detail.php?id=<?php echo $row['product_id']; ?>">
                                <?php echo htmlspecialchars($row['product_name']); ?>
                            </a>
                        </h3>
                        <p><strong>Price:</strong> ₱<?php echo number_format($row['price'], 2); ?></p>
                        <p><strong>Shop:</strong> <?php echo htmlspecialchars($row['shopname']); ?></p>
                    </div>
                    <button class="remove-btn" onclick="removeWishlist(<?php echo $row['wishlist_id']; ?>)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align:center; color:#777;">Your wishlist is empty.</p>
        <?php endif; ?>
    </div>

    <script>
        function removeWishlist(wishlistId) {
            if (!confirm("Remove this item from wishlist?")) return;

            fetch("wishlist_remove.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "wishlist_id=" + wishlistId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById("wishlist-" + wishlistId).remove();
                } else {
                    alert(data.message || "Failed to remove item.");
                }
            })
            .catch(err => console.error(err));
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
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
