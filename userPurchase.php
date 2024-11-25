<?php
session_start();
include('connection.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: signin.php');
    exit;
}

$userId = $_SESSION['user_id'];
$sql = "SELECT o.order_id, o.total_amount, o.order_date, oi.product_id, p.product_name, oi.quantity, oi.price, p.picture
        FROM orders o
        JOIN order_items oi ON o.order_id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        WHERE o.user_id = ?
        ORDER BY o.order_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$purchases = [];

while ($row = $result->fetch_assoc()) {
    $purchases[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase History</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .container {
            width: 100%;
            max-width: 800px;
            padding: 20px;
            box-sizing: border-box;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }

        .purchase-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            padding: 20px;
            overflow: hidden;
        }

        .purchase-card h2 {
            font-size: 1.2em;
            margin: 0;
            color: #009688;
        }

        .purchase-details {
            font-size: 0.9em;
            color: #777;
            margin-bottom: 15px;
        }

        .item {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .item:last-child {
            border-bottom: none;
        }

        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            margin-right: 15px;
            border-radius: 5px;
        }

        .item-details {
            flex: 1;
        }

        .item-details h3 {
            font-size: 1em;
            margin: 0;
            color: #333;
        }

        .item-details span {
            font-size: 0.85em;
            color: #555;
            display: block;
            margin-top: 3px;
        }

        .buttons {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .buttons a {
            text-decoration: none;
            font-size: 0.85em;
            padding: 5px 10px;
            color: #fff;
            background-color: #009688;
            border-radius: 5px;
            text-align: center;
        }

        .buttons a:hover {
            background-color: #00796b;
        }

        .total {
            font-weight: bold;
            font-size: 1em;
            color: #009688;
            margin-top: 10px;
            text-align: right;
        }

        @media (max-width: 600px) {
            .item {
                flex-direction: column;
                align-items: flex-start;
            }

            .product-image {
                margin-bottom: 10px;
            }

            .total {
                text-align: left;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Purchase History</h1>

        <?php if (empty($purchases)): ?>
            <p style="text-align: center; color: #777;">No purchases found.</p>
        <?php else: ?>
            <?php
            $currentOrderId = null;
            $currentTotal = 0;

            foreach ($purchases as $purchase): 
                if ($purchase['order_id'] !== $currentOrderId) {
                    if ($currentOrderId !== null) {
            ?>             
                        <div class="total">Total: $<?= number_format($currentTotal, 2) ?></div>
                    </div>
                </div>
            <?php
                    }
                    $currentOrderId = $purchase['order_id'];
                    $currentTotal = 0;
            ?>
                <div class="purchase-card">
                    <div class="purchase-details">
                        <span>Date: <?= htmlspecialchars($purchase['order_date']) ?></span>
                    </div>
                    <div class="items">
            <?php
                } 
                $currentTotal += $purchase['price'] * $purchase['quantity'];
            ?>
                    <div class="item">
                        <img src="<?= htmlspecialchars($purchase['picture']) ?>" alt="<?= htmlspecialchars($purchase['product_name']) ?>" class="product-image">
                        <div class="item-details">
                            <h3><?= htmlspecialchars($purchase['product_name']) ?></h3>
                            <span>Quantity: <?= htmlspecialchars($purchase['quantity']) ?></span>
                            <span>Price: $<?= number_format($purchase['price'], 2) ?></span>
                        </div>
                        <div class="buttons">
                            <a href="product_detail.php?id=<?= htmlspecialchars($purchase['product_id']) ?>">View Product</a>
                            <a href="order_confirmation.php?order_id=<?= htmlspecialchars($purchase['order_id']) ?>">View Receipt</a>
                        </div>
                    </div>
            <?php 
            endforeach; 
            ?>
                    </div> 
                    <div class="total">Total: $<?= number_format($currentTotal, 2) ?></div>
                </div> 
            <?php 
            endif; 
            ?>
    </div>
</body>
</html>
