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

    // 1. Delete comments
    $deleteComments = $conn->prepare("DELETE FROM comments WHERE product_id = ?");
    $deleteComments->bind_param("i", $productId);
    $deleteComments->execute();
    $deleteComments->close();

    // 2. Delete admin replies
    $deleteReplies = $conn->prepare("DELETE FROM admin_reply WHERE product_id = ?");
    $deleteReplies->bind_param("i", $productId);
    $deleteReplies->execute();
    $deleteReplies->close();

    // 3. Delete messages
    $deleteMessages = $conn->prepare("DELETE FROM messages WHERE product_id = ?");
    $deleteMessages->bind_param("i", $productId);
    $deleteMessages->execute();
    $deleteMessages->close();

    // 4. Delete the product itself
    $deleteProduct = $conn->prepare("DELETE FROM products WHERE id = ? AND admin_id = ?");
    $deleteProduct->bind_param("ii", $productId, $_SESSION['admin_id']);
    $deleteProduct->execute();
    $deleteProduct->close();

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

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success px-3">
        <a class="navbar-brand fw-bold" href="#">Admin Dashboard</a>

        <div class="ms-auto d-flex align-items-center">
            <!-- Notification Bell -->
            <a href="notifications.php" class="nav-link position-relative me-3 text-white">
                <i class="fa-solid fa-bell fa-lg"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    <?php echo count($comments); ?>
                </span>
            </a>

            <!-- Chat Icon -->
            <a href="chat_list.php" class="nav-link me-3 text-white">
                <i class="fa-solid fa-comment fa-lg"></i>
            </a>

            <!-- Profile -->
            <a href="profile.php" class="nav-link me-3 text-white">
                <i class="fa-solid fa-user fa-lg"></i>
            </a>

            <!-- Logout -->
            <a href="logout.php" class="btn btn-danger btn-sm">Log out</a>
        </div>
    </nav>

    <div class="container mt-4">
        <h1 class="mb-3">Welcome to the Dashboard</h1>
        <p>Your User ID is: <strong><?php echo htmlspecialchars($_SESSION['admin_id']); ?></strong></p>

        <!-- Add / Update Product Form -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <?php echo $productToEdit ? 'Update Product' : 'Add New Product'; ?>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <?php if ($productToEdit): ?>
                        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($productToEdit['id']); ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label">Product Name</label>
                        <input type="text" class="form-control" name="product_name" value="<?php echo $productToEdit ? htmlspecialchars($productToEdit['product_name']) : ''; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Product Picture URL</label>
                        <input type="text" class="form-control" name="product_picture" value="<?php echo $productToEdit ? htmlspecialchars($productToEdit['picture']) : ''; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Price</label>
                        <input type="number" step="0.01" class="form-control" name="product_price" value="<?php echo $productToEdit ? htmlspecialchars($productToEdit['price']) : ''; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Stocks</label>
                        <input type="number" class="form-control" name="product_stocks" value="<?php echo $productToEdit ? htmlspecialchars($productToEdit['stocks']) : ''; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="product_description" required><?php echo $productToEdit ? htmlspecialchars($productToEdit['description']) : ''; ?></textarea>
                    </div>

                    <button type="submit" name="<?php echo $productToEdit ? 'update_product' : 'add_product'; ?>" class="btn btn-success">
                        <?php echo $productToEdit ? 'Update Product' : 'Add Product'; ?>
                    </button>
                </form>
            </div>
        </div>

        <!-- Product List -->
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">Product List</div>
            <div class="card-body">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-success">
                        <tr>
                            <th>Product Name</th>
                            <th>Product Picture</th>
                            <th>Price</th>
                            <th>Stocks</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($product = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                <td><img src="<?php echo htmlspecialchars($product['picture']); ?>" class="img-fluid rounded" style="max-width: 100px;" alt="Product"></td>
                                <td>₱<?php echo htmlspecialchars($product['price']); ?></td>
                                <td><?php echo htmlspecialchars($product['stocks']); ?></td>
                                <td><?php echo htmlspecialchars($product['description']); ?></td>
                                <td>
                                    <a href="?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary me-1">
                                        <i class="fa-solid fa-pen-to-square"></i> Update
                                    </a>
                                    <a href="?delete_id=<?php echo $product['id']; ?>" onclick="return confirm('Are you sure you want to delete this product?');" class="btn btn-sm btn-danger">
                                        <i class="fa-solid fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
