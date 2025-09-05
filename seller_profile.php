<?php
session_start();
include('connection.php');

$sellerId = isset($_GET['id']) ? intval($_GET['id']) : 0;

$sql = "SELECT * FROM admin WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $sellerId);
$stmt->execute();
$result = $stmt->get_result();
$seller = $result->fetch_assoc();

if ($seller) {
    $sql = "SELECT * FROM products WHERE admin_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $sellerId);
    $stmt->execute();
    $products = $stmt->get_result();
} else {
    echo "Seller not found.";
    exit;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($seller['shopname']); ?>'s Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 20px;
        }

        .profile-header {
            text-align: center;
            padding: 20px;
            background-color: #1f3c88;
            color: white;
            border-radius: 8px;
            margin-bottom: 40px;
        }

        .profile-header h1 {
            margin: 0;
            font-size: 32px;
        }

        .profile-header p {
            margin: 10px 0 0;
            font-size: 18px;
        }

        .product-list {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .product-item {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease;
            width: 200px;
            max-width: 300px;
            text-align: center;
            color: #1f3c88;
            text-decoration: none;
            height: calc(100% + 10px);

        }

        .product-item:hover {
            transform: translateY(-5px);
        }

        .product-item img {
            width: 100%;
            height: 210px;
            object-fit: cover;
        }

        .product-item h3 {
            margin: 15px 0;
            font-size: 20px;
            font-weight: 600;
        }

        .product-item .price {
            font-size: 1.4rem;
            color: #009688;
            margin: 10px 0;
        }

        .product-item p {
            font-size: 14px;
            color: #666;
            margin: 5px 0;
        }

        .product-item .stocks {
            color: #28a745;
            font-weight: 600;
            font-size: 16px;
        }

        .product-item .description {
            padding: 15px;
            font-size: 14px;
            color: #444;
            text-align: left;
        }

        a {
            text-decoration: none;
            color: inherit;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="profile-header">
            <h1><?php echo htmlspecialchars($seller['shopname']); ?></h1>
            <p>Owner: <?php echo htmlspecialchars($seller['fname']) . ' ' . htmlspecialchars($seller['lname']); ?></p>
        </div>

        <h2>Products by <?php echo htmlspecialchars($seller['shopname']); ?>:</h2>

        <div class="product-list">
    <?php if ($products->num_rows > 0): ?>
        <?php while ($product = $products->fetch_assoc()): ?>
            <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="product-item">
                <img src="<?php echo htmlspecialchars($product['picture']); ?>" alt="Product Image">
                <h3>
                    <?php
                    $words = explode(' ', $product['product_name']);
                    $shortName = implode(' ', array_slice($words, 0, 8));
                    echo htmlspecialchars($shortName);
                    ?>
                </h3>
                <p class="price">₱<?php echo number_format($product['price'], 2); ?></p>
                <p>In Stock: <?php echo htmlspecialchars($product['stocks']); ?></p>
            </a>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No products found.</p>
    <?php endif; ?>
</div>

    </div>

</body>

</html>