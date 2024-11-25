<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: adminLogin.php'); 
    exit; 
}

include('connection.php'); 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $picture = $_POST['product_picture'];
    $productName = $_POST['product_name']; // Get product name
    $price = $_POST['product_price'];
    $stocks = $_POST['product_stocks'];
    $description = $_POST['product_description'];
    $adminId = $_SESSION['admin_id']; 

    $stmt = $conn->prepare("INSERT INTO products (picture, product_name, price, stocks, description, admin_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdssi", $picture, $productName, $price, $stocks, $description, $adminId); 
    if ($stmt->execute()) {
        // Product added successfully
    } else {
        echo "Error: " . $stmt->error; 
    }
    $stmt->close();
}

if (isset($_GET['delete_id'])) {
    $productId = $_GET['delete_id'];

    $stmt = $conn->prepare("DELETE FROM products WHERE id = ? AND admin_id = ?"); 
    $stmt->bind_param("ii", $productId, $_SESSION['admin_id']);
    $stmt->execute();
    $stmt->close();

    header('Location: dashboard.php');
    exit; 
}

$productToEdit = null; 
if (isset($_GET['id'])) {
    $productId = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND admin_id = ?");
    $stmt->bind_param("ii", $productId, $_SESSION['admin_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $productToEdit = $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $productId = $_POST['product_id'];
    $picture = $_POST['product_picture'];
    $productName = $_POST['product_name']; // Get product name
    $price = $_POST['product_price'];
    $stocks = $_POST['product_stocks'];
    $description = $_POST['product_description'];

    $stmt = $conn->prepare("UPDATE products SET picture = ?, product_name = ?, price = ?, stocks = ?, description = ? WHERE id = ? AND admin_id = ?"); 
    $stmt->bind_param("ssdssii", $picture, $productName, $price, $stocks, $description, $productId, $_SESSION['admin_id']);
    if ($stmt->execute()) {
        // Product updated successfully
    } else {
        echo "Error: " . $stmt->error; 
    }
    $stmt->close();

    header('Location: dashboard.php');
    exit; 
}

$stmt = $conn->prepare("SELECT * FROM products WHERE admin_id = ?"); 
$stmt->bind_param("i", $_SESSION['admin_id']);
$stmt->execute();
$result = $stmt->get_result();

// Fetch products for the admin
$stmt = $conn->prepare("SELECT * FROM products WHERE admin_id = ?"); 
$stmt->bind_param("i", $_SESSION['admin_id']);
$stmt->execute();
$result = $stmt->get_result();

// Fetch comments for the products owned by the admin
$commentsStmt = $conn->prepare("
    SELECT comments.*, products.product_name 
    FROM comments 
    JOIN products ON comments.product_id = products.id 
    WHERE products.admin_id = ? 
    ORDER BY comments.comment_date DESC
    LIMIT 3");
$commentsStmt->bind_param("i", $_SESSION['admin_id']);
$commentsStmt->execute();
$commentsResult = $commentsStmt->get_result();
$comments = $commentsResult->fetch_all(MYSQLI_ASSOC);


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
   <script>
   
   </script>
   <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f9f9f9;
        }

        h1, h2 {
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #4CAF50;
            color: white;
        }

        img {
            width: 100px;
            height: auto;
        }

        .action-buttons a {
            margin-right: 10px;
            text-decoration: none;
            color: blue;
        }

        .action-buttons a:hover {
            text-decoration: underline;
        }

        form {
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #fff;
            padding: 20px 30px 20px 20px;
        }

        label {
            display: block;
            margin: 5px 0;
        }

        input[type="text"],
        input[type="number"],
        textarea {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        .icon-container {
            position: absolute;
            top: 25px;
            right: 70px; /* Adjusted to make room for notification bell */
            font-size: 48px;
            color: black;
            text-decoration: none;
        }

        .icon-container:hover {
            color: red;
        }

        .notification-bell {
            position: absolute;
            top: 25px;
            right: 10px; /* Positioned to the right */
            font-size: 48px;
            color: black;
            text-decoration: none;
        }

        .notification-bell:hover {
            color: red;
        }

        .notification-count {
            position: absolute;
            top: 15px;
            right: 25px; /* Adjust position relative to the bell icon */
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px;
        }
        /* Chat Icon Container */
.chat-icon-container {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background-color: #4CAF50; /* Green background */
    color: white;
    border-radius: 50%;
    padding: 20px;
    font-size: 36px;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1); /* Optional shadow */
    z-index: 1000;
    text-decoration: none; /* Remove underline */
}

.chat-icon-container:hover {
    background-color: #45a049; /* Darker green on hover */
}

/* Chat Icon inside container */
.chat-icon-container i {
    margin: 0;
}

        
    </style>
</head>

<body>

    <h1>Welcome to the Dashboard</h1>
    <p>Your User ID is: <?php echo htmlspecialchars($_SESSION['admin_id']); ?></p>
    
    <!-- Chat Icon -->
    <a href="chat_list.php" class="chat-icon-container">
        <i class="fa-solid fa-comment"></i>
    </a>


    <!-- Notification Bell -->
    <a href="notifications.php" class="notification-bell">
        <i class="fa-solid fa-bell"></i>
        <span class="notification-count"><?php echo count($comments); ?></span> <!-- Change this number based on actual notifications -->
    </a>
    
    <a href="logout.php" style="background-color: red; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;">Log out</a>

    <a href="profile.php" class="icon-container">
        <i class="fa-solid fa-user"></i>
    </a>

    <h2><?php echo $productToEdit ? 'Update Product' : 'Add New Product'; ?></h2>
    <form method="POST" action="">
        <?php if ($productToEdit): ?>
            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($productToEdit['id']); ?>">
        <?php endif; ?>
        
        <label for="product_name">Product Name:</label>
        <input type="text" id="product_name" name="product_name" value="<?php echo $productToEdit ? htmlspecialchars($productToEdit['product_name']) : ''; ?>" required>

        <label for="product_picture">Product Picture URL:</label>
        <input type="text" id="product_picture" name="product_picture" value="<?php echo $productToEdit ? htmlspecialchars($productToEdit['picture']) : ''; ?>" required>

        <label for="product_price">Price:</label>
        <input type="number" step="0.01" id="product_price" name="product_price" value="<?php echo $productToEdit ? htmlspecialchars($productToEdit['price']) : ''; ?>" required>

        <label for="product_stocks">Stocks:</label>
        <input type="number" id="product_stocks" name="product_stocks" value="<?php echo $productToEdit ? htmlspecialchars($productToEdit['stocks']) : ''; ?>" required>

        <label for="product_description">Description:</label>
        <textarea id="product_description" name="product_description" required><?php echo $productToEdit ? htmlspecialchars($productToEdit['description']) : ''; ?></textarea>

        <input type="submit" name="<?php echo $productToEdit ? 'update_product' : 'add_product'; ?>" value="<?php echo $productToEdit ? 'Update Product' : 'Add Product'; ?>">
    </form>

    <h2>Product List</h2>
    <table>
        <tr>
            <th>Product Name</th>
            <th>Product Picture</th>
            <th>Price</th>
            <th>Stocks</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>
        <?php while ($product = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                <td><img src="<?php echo htmlspecialchars($product['picture']); ?>" alt="Product Image"></td>
                <td><?php echo htmlspecialchars($product['price']); ?></td>
                <td><?php echo htmlspecialchars($product['stocks']); ?></td>
                <td><?php echo htmlspecialchars($product['description']); ?></td>
                <td class="action-buttons">
                    <a href="?id=<?php echo $product['id']; ?>" style="background-color: green; color: white; padding: 10px 15px; border-radius: 5px; text-decoration: none;">
                    <i class="fa-sharp fa-solid fa-pen-to-square"></i> Update
                    </a>
                    <a href="?delete_id=<?php echo $product['id']; ?>" onclick="return confirm('Are you sure you want to delete this product?');" style="background-color: red; color: white; padding: 10px 15px; border-radius: 5px; text-decoration: none;">
                    <i class="fa-solid fa-trash"></i> Delete
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

</body>

</html>
