<?php
session_start();
include('connection.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: signin.php');
    exit;
}

$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;

$sql = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if ($product) {
    $adminId = $product['admin_id'];
    $sql = "SELECT shopname FROM admin WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $adminId);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    $shopname = $admin ? $admin['shopname'] : "Unknown Shop";
    $sellerId = $adminId;
} else {
    $shopname = "Product not found";
    $product = null; 
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $comment = trim($_POST['comment']);
    if (!empty($comment)) {
        $userId = $_SESSION['user_id'];

        $sql = "INSERT INTO comments (product_id, user_id, comment, admin_id) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisi", $productId, $userId, $comment, $adminId); // Include admin_id
        $stmt->execute();
        $stmt->close();

        header("Location: product_detail.php?id=" . $productId);
        exit;
    }
}

$sql = "SELECT c.comment, c.comment_date, 
        CONCAT(u.fname, ' ', u.lname) AS username, c.id AS comment_id 
        FROM comments c 
        JOIN users u ON c.user_id = u.user_id 
        WHERE c.product_id = ? 
        ORDER BY c.comment_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $productId);
$stmt->execute();
$commentsResult = $stmt->get_result();
$comments = $commentsResult->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product ? $product['product_name'] : "Product Not Found"); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script>
        function changeQuantity(amount) {
            const quantityInput = document.getElementById('quantity');
            let currentQuantity = parseInt(quantityInput.value);

            if (currentQuantity + amount > 0 && currentQuantity + amount <= <?php echo htmlspecialchars($product['stocks']); ?>) {
                quantityInput.value = currentQuantity + amount;
            }
        }


        function changeQuantity(amount) {
            const quantityInput = document.getElementById('quantity');
            let currentQuantity = parseInt(quantityInput.value);

            if (currentQuantity + amount > 0 && currentQuantity + amount <= <?php echo htmlspecialchars($product['stocks']); ?>) {
                quantityInput.value = currentQuantity + amount;
            }
        }

        function addToCart(productId) {
            const quantity = document.getElementById('quantity').value; 

            fetch('add_to_cart.php', {
                    method: 'POST', // Changed to POST
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        'product_id': productId,
                        'quantity': quantity
                    })
                })
                .then(response => response.json()) // Parse the JSON response
                .then(data => {
                    alert(data.message); // Show the message returned from the PHP script
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while adding to cart.');
                });
        }

        function buyNow(productId) {
            const quantity = document.getElementById('quantity').value; // Get the quantity from the input field

            // Redirect to checkout page with product ID and quantity as URL parameters
            window.location.href = `buynow.php?product_id=${productId}&quantity=${quantity}`;
        }

        function toggleDropdown() {
            const dropdownMenu = document.querySelector(".dropdown-menu");
            dropdownMenu.style.display = dropdownMenu.style.display === "block" ? "none" : "block";
        }

        // Optional: Close the dropdown if clicked outside
        document.addEventListener("click", function(event) {
            const isClickInside = document.querySelector(".burger-menu").contains(event.target);
            const dropdownMenu = document.querySelector(".dropdown-menu");

            if (!isClickInside && dropdownMenu.style.display === "block") {
                dropdownMenu.style.display = "none";
            }
        });
    </script>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }

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
            color: white;
            /* Change the color of the title to white */
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
            /* Hidden by default */
            position: absolute;
            top: 100%;
            /* Place below the burger icon */
            right: 0;
            /* Align to the right */
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

        /* Show burger icon on smaller screens */
        @media (max-width: 768px) {

            .navbar h1,
            .navbar form,
            .navbar a {
                display: none;
            }

            .burger-menu {
                display: block;
            }
        }

        .product-detail {
            padding: 20px;
            background-color: white;
            margin: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .product-detail img {
            width: 100%;
            height: auto;
            object-fit: contain;
        }

        .product-detail h2 {
            margin: 10px 0;
        }

        .product-detail .price {
            font-size: 24px;
            color: #0066cc;
        }

        .product-detail p {
            margin: 10px 0;
        }

        .product-info {
            display: flex;
            flex-direction: column;
            /* Stack elements vertically */
            align-items: flex-start;
            /* Align items to the left */
            margin-bottom: 20px;
            /* Space between product info and buttons */
        }

        .button-group {
            display: flex;
            justify-content: center;
            /* Center the buttons */
            gap: 10px;
            /* Space between buttons */
            margin-top: 20px;
            /* Adds space above the button group */
        }

        .button-group button {
            padding: 10px;
            border: none;
            background-color: #0066cc;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            flex: 1;
            /* Make buttons take equal space */
        }

        .button-group button:hover {
            background-color: #004a99;
        }

        /* New styles for seller link */
        .seller-link {
            text-decoration: none;
            /* Remove underline */
            font-size: 20px;
            /* Smaller font size */
            color: #0066cc;
            /* Color to match the price */

        }

        .seller-link:hover {
            text-decoration: underline;
            /* Optional: underline on hover */
        }

        .quantity-selector {
            display: flex;
            align-items: center;
            margin-top: 10px;

        }

        .quantity-selector button {
            padding: 10px;
            border: none;
            background-color: #0066cc;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }

        .quantity-selector input {
            text-align: center;
            /* Center the text */
            margin: 0 10px;
            /* Margin around the input */
            border: 1px solid #ccc;
            /* Border style */
            border-radius: 5px;
            /* Rounded corners */
            width: 30px;
            /* Set a fixed width */
            height: 30px;
            /* Set a fixed height */
        }


        .button-group {
            display: flex;
            justify-content: center;
            /* Center the buttons */
            gap: 10px;
            /* Space between buttons */
            margin-top: 20px;
            /* Adds space above the button group */
        }

        .comments-section {
            padding: 20px;
            background-color: white;
            margin: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .comments-section h3,
        .comments-section h4 {
            margin-bottom: 10px;
        }

        .comment {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }

        .comment strong {
            font-weight: bold;
            color: #333;
        }

        .comment span {
            color: #777;
            font-size: 0.9em;
        }

        .comments-section textarea {
            width: 96%;
            padding: 10px;
            margin-top: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
            resize: none;
        }

        .comments-section button {
            margin-top: 10px;
            padding: 10px 15px;
            background-color: #0066cc;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .comments-section button:hover {
            background-color: #004a99;
        }

        .replies {
            font-weight: bolder;
        }
        .chat-link {
    display: flex;
    align-items: center;
    color: #2196f3;
    text-decoration: none;
    margin-top: 10px;
    font-size: 1.2em;
}

.chat-link i {
    margin-right: 5px;
}

    </style>
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
                <a href="wishlist.php">Wishlist</a>
                <a href="userPurchase.php">My Purchase</a>
                <a href="userLogout.php">Logout</a>
            </div>
        </div>
    </div>

    <div class="product-detail">
    <?php if ($product): ?>
        <img src="<?php echo htmlspecialchars($product['picture']); ?>" alt="Product Image">
        <h2><?php echo htmlspecialchars($product['product_name']); ?></h2>
        <p class="price">₱<?php echo htmlspecialchars($product['price']); ?></p>
        <p><?php echo htmlspecialchars($product['description']); ?></p>
        <p><strong>Available Stock:</strong> <?php echo htmlspecialchars($product['stocks']); ?></p>
        <div class="quantity-selector">
            <button onclick="changeQuantity(-1)">-</button>
            <input type="number" id="quantity" value="1" min="1" max="<?php echo htmlspecialchars($product['stocks']); ?>" readonly>
            <button onclick="changeQuantity(1)">+</button>
        </div>
        <div class="button-group">
    <?php if ($product['stocks'] > 0): ?>
        <button onclick="addToCart(<?php echo $productId; ?>)">Add to Cart</button>
        <button onclick="buyNow(<?php echo $productId; ?>)">Buy Now</button>
    <?php else: ?>
        <?php
        // Check if already in wishlist
        $userId = $_SESSION['user_id'];
        $wishlistQuery = "SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?";
        $wishlistStmt = $conn->prepare($wishlistQuery);
        $wishlistStmt->bind_param("ii", $userId, $productId);
        $wishlistStmt->execute();
        $wishlistResult = $wishlistStmt->get_result();
        $inWishlist = $wishlistResult->num_rows > 0;
        ?>
        <button id="wishlistBtn" style="background:blue; color:white; padding:10px; border:none; border-radius:5px; cursor:pointer;"
                onclick="toggleWishlist(<?php echo $productId; ?>, <?php echo $adminId; ?>)">
            <i id="wishlistIcon" class="<?php echo $inWishlist ? 'fas' : 'far'; ?> fa-heart"
               style="color: <?php echo $inWishlist ? 'red' : 'white'; ?>; margin-right:5px;"></i>
            Wishlist
        </button>
    <?php endif; ?>
</div>

        <p><a href="seller_profile.php?id=<?php echo $sellerId; ?>" class="seller-link">Shop: <?php echo htmlspecialchars($shopname); ?></a></p>

        <!-- Chat Icon (Font Awesome) -->
        <a href="chat_with_seller.php?id=<?php echo $sellerId; ?>&product_id=<?php echo $productId; ?>" class="chat-link" title="Chat with seller">
    <i class="fas fa-comments"></i> Chat
</a>

        
    <?php else: ?>
        <p><?php echo htmlspecialchars($shopname); ?></p>
    <?php endif; ?>
</div>


    <div class="comments-section">
        <h3>Comments</h3>
        <?php foreach ($comments as $comment): ?>
            <div class="comment">
                <strong><?php echo htmlspecialchars($comment['username']); ?></strong>
                <span><?php echo htmlspecialchars($comment['comment_date']); ?></span>
                <p><?php echo htmlspecialchars($comment['comment']); ?></p>

                <!-- Fetch replies for this comment -->
                <?php
                $commentId = $comment['comment_id'];
                $sql = "SELECT r.reply, r.reply_date, 
               CONCAT(a.fname, ' ', a.lname) AS admin_name,
               a.shopname  -- Fetch shopname from the admin table
        FROM replies r 
        JOIN admin a ON r.admin_id = a.id 
        WHERE r.comment_id = ? 
        ORDER BY r.reply_date ASC";

                $replyStmt = $conn->prepare($sql);
                $replyStmt->bind_param("i", $commentId);
                $replyStmt->execute();
                $repliesResult = $replyStmt->get_result();
                $replies = $repliesResult->fetch_all(MYSQLI_ASSOC);
                $replyStmt->close();
                ?>



                <!-- Display replies -->
                <div class="replies">
                    <?php foreach ($replies as $reply): ?>
                        <div class="reply">
                            <strong style="color:#004a99"><?php echo htmlspecialchars($reply['shopname']); ?></strong>
                            <span><?php echo htmlspecialchars($reply['reply_date']); ?></span>
                            <p><?php echo htmlspecialchars($reply['reply']); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <form method="POST">
            <textarea name="comment" rows="4" placeholder="Write your comment here..." required></textarea>
            <button type="submit">Submit Comment</button>
        </form>
    </div>
    <script>
        function toggleWishlist(productId, adminId) {
    fetch("wishlist_toggle.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({
            product_id: productId,
            admin_id: adminId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const icon = document.getElementById("wishlistIcon");
            if (data.action === "added") {
                icon.classList.remove("far");
                icon.classList.add("fas");
                icon.style.color = "red";
            } else {
                icon.classList.remove("fas");
                icon.classList.add("far");
                icon.style.color = "white";
            }
        } else {
            alert(data.message || "Something went wrong.");
        }
    })
    .catch(error => console.error("Error:", error));
}
    </script>