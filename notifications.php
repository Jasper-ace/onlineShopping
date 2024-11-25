<?php
session_start(); // Start the session
include("connection.php"); // Include your database connection file

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: adminLogin.php'); // Redirect to login if not logged in
    exit();
}

$adminId = $_SESSION['admin_id']; // Get the logged-in admin's ID

// Fetch comments associated with this admin
$queryComments = "
    SELECT c.id, c.comment, c.comment_date, p.product_name, p.picture, 
           CONCAT(u.fname, ' ', u.lname) AS username  
    FROM comments c 
    JOIN products p ON c.product_id = p.id 
    JOIN users u ON c.user_id = u.user_id 
    WHERE p.admin_id = ? 
    ORDER BY c.comment_date DESC
";

// Prepare and execute the comments query
$stmtComments = $conn->prepare($queryComments);
if ($stmtComments) {
    $stmtComments->bind_param("i", $adminId);
    $stmtComments->execute();
    $resultComments = $stmtComments->get_result();
} else {
    die('Comments query preparation failed: ' . $conn->error);
}

// Fetch checkout notifications based on admin_id
$queryCheckout = "
    SELECT o.order_id, p.product_name, p.picture, o.name AS buyer_name, o.address, 
           o.order_date, ci.quantity
    FROM orders o 
    JOIN cart ci ON o.user_id = ci.user_id 
    JOIN products p ON ci.product_id = p.id 
    WHERE p.admin_id = ?  
    ORDER BY o.order_date DESC
";

// Prepare and execute the checkout query
$stmtCheckout = $conn->prepare($queryCheckout);
if ($stmtCheckout) {
    $stmtCheckout->bind_param("i", $adminId); // Bind the admin ID
    $stmtCheckout->execute();
    $resultCheckout = $stmtCheckout->get_result();
} else {
    die('Checkout query preparation failed: ' . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="stylesheet" href="styles.css"> <!-- Include your styles here -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
         body {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            font-family: 'Roboto', sans-serif;
            background-color: #f5f5f5; /* Subtle background color */
            margin: 0; /* Remove default margin */
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #2c3e50; /* Darker color for the header */
            font-size: 2.5em; /* Increase font size for prominence */
            font-weight: 700; /* Bold text for the title */
        }

        .notification-section {
            background-color: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            margin: 10px 0; /* Space between sections */
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1); /* Shadow for depth */
            width: 100%; /* Full width for sections */
            max-width: 800px; /* Maximum width for readability */
        }

        .notification-item {
            display: flex;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #e0e0e0;
        }

        .notification-item:last-child {
            border-bottom: none; /* Remove border from last item */
        }

        .notification-item img {
            width: 80px; /* Adjust width for product images */
            height: auto; /* Maintain aspect ratio */
            margin-right: 15px; /* Space between image and text */
            border-radius: 4px; /* Rounded corners for images */
        }

        .notification-content {
            flex: 1; /* Allow content to grow */
        }

        .bold {
            font-weight: bold; /* Bold text */
        }

        .comment-item {
            text-decoration: none; /* Remove underline from comments */
            color: inherit; /* Inherit color from parent */
        }

        .comment-item:hover {
            color: #2980b9; /* Darker shade on hover */
        }
    </style>
</head>
<body>
    <a href="dashboard.php" class="back-button">Back to Dashboard</a>
    <h1>Your Notifications</h1>

    <!-- Checkout Section -->
    <div class="notification-section">
        <h2 class="bold">Checkout</h2>
        <?php if ($resultCheckout->num_rows > 0): ?>
            <?php while ($rowCheckout = $resultCheckout->fetch_assoc()): ?>
                <div class="notification-item">
                    <img src="<?php echo htmlspecialchars($rowCheckout['picture']); ?>" alt="Product Image">
                    <div class="notification-content">
                        <span class="bold"><?php echo htmlspecialchars($rowCheckout['product_name']); ?></span><br>
                        Quantity: <?php echo htmlspecialchars($rowCheckout['quantity']); ?><br>
                        Name: <?php echo htmlspecialchars($rowCheckout['buyer_name']); ?><br>
                        Address: <?php echo htmlspecialchars($rowCheckout['address']); ?><br>
                        Time: <?php echo date('Y-m-d H:i:s', strtotime($rowCheckout['order_date'])); ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No checkout notifications available.</p>
        <?php endif; ?>
    </div>

    <!-- Comments Section -->
    <div class="notification-section">
        <h2 class="bold">Comments</h2>
        <?php if ($resultComments->num_rows > 0): ?>
            <?php while ($rowComment = $resultComments->fetch_assoc()): ?>
                <a href="view_comment.php?id=<?php echo $rowComment['id']; ?>" class="comment-item">
                    <div class="notification-item">
                        <img src="<?php echo htmlspecialchars($rowComment['picture']); ?>" alt="Product Image">
                        <div class="notification-content">
                            <span class="bold"><?php echo htmlspecialchars($rowComment['username']); ?></span><br>
                            <?php echo htmlspecialchars($rowComment['comment']); ?><br>
                            Product: <?php echo htmlspecialchars($rowComment['product_name']); ?><br>
                            Time: <?php echo date('Y-m-d H:i:s', strtotime($rowComment['comment_date'])); ?>
                        </div>
                    </div>
                </a>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No comments available.</p>
        <?php endif; ?>
    </div>

    <?php
    $stmtComments->close(); // Close the comments statement
    $stmtCheckout->close(); // Close the checkout statement
    $conn->close(); // Close the connection
    ?>
</body>
</html>
