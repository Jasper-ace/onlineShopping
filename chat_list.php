<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: adminLogin.php');
    exit;
}

include('connection.php');

// Fetch products owned by the admin that have associated messages
$stmt = $conn->prepare("SELECT p.id, p.product_name
                        FROM products p
                        JOIN messages m ON p.id = m.product_id
                        WHERE p.admin_id = ? 
                        GROUP BY p.id");
$stmt->bind_param("i", $_SESSION['admin_id']);
$stmt->execute();
$productsResult = $stmt->get_result();

// Function to convert timestamp to human-readable format
function timeAgo($timestamp) {
    $time_ago = strtotime($timestamp);
    $current_time = time();
    $time_difference = $current_time - $time_ago;
    $seconds = $time_difference;

    $minutes      = round($seconds / 60);           // value 60 is seconds
    $hours        = round($seconds / 3600);         // value 3600 is 60 minutes * 60 sec
    $days         = round($seconds / 86400);        // value 86400 is 24 hours * 60 minutes * 60 sec
    $weeks        = round($seconds / 604800);       // value 604800 is 7 days * 24 hours * 60 minutes * 60 sec
    $months       = round($seconds / 2629440);      // value 2629440 is ((365+365+365+365+365)/5/12) days * 24 hours * 60 minutes * 60 sec
    $years        = round($seconds / 31553280);     // value 31553280 is ((365+365+365+365+365)/5/12) days * 24 hours * 60 minutes * 60 sec

    if ($seconds <= 60) {
        return "Just Now";
    } else if ($minutes <= 60) {
        if ($minutes == 1) {
            return "one minute ago";
        } else {
            return "$minutes minutes ago";
        }
    } else if ($hours <= 24) {
        if ($hours == 1) {
            return "an hour ago";
        } else {
            return "$hours hours ago";
        }
    } else if ($days <= 7) {
        if ($days == 1) {
            return "yesterday";
        } else {
            return "$days days ago";
        }
    } else if ($weeks <= 4.3) { // 4.3 == 30/7
        if ($weeks == 1) {
            return "a week ago";
        } else {
            return "$weeks weeks ago";
        }
    } else if ($months <= 12) {
        if ($months == 1) {
            return "a month ago";
        } else {
            return "$months months ago";
        }
    } else {
        if ($years == 1) {
            return "one year ago";
        } else {
            return "$years years ago";
        }
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat List</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-light">

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3">Chat List</h1>
        <a href="dashboard.php" class="btn btn-outline-primary">
            <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
    <p class="text-muted">Select a product to see the chats:</p>

    <?php
    // Iterate through each product
    while ($product = $productsResult->fetch_assoc()):
        $productId = $product['id'];

        // Fetch messages for this product, grouped by user (last message for each user)
        $stmt = $conn->prepare("SELECT u.user_id, u.fname, u.lname, m.message, m.timestamp, m.id AS message_id
                                FROM messages m
                                JOIN users u ON m.sender_id = u.user_id
                                WHERE m.product_id = ? 
                                ORDER BY u.user_id, m.timestamp DESC");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $usersResult = $stmt->get_result();

        // Group messages by user
        $userMessages = [];
        while ($user = $usersResult->fetch_assoc()) {
            $userId = $user['user_id'];
            if (!isset($userMessages[$userId])) {
                $userMessages[$userId] = $user;
            }
        }
    ?>

    <!-- Product Card -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-success text-white">
            <i class="fa-solid fa-box"></i> 
            <?php echo htmlspecialchars($product['product_name']); ?>
        </div>
        <ul class="list-group list-group-flush">
            <?php foreach ($userMessages as $userMessage): ?>
                <li class="list-group-item d-flex justify-content-between align-items-start">
                    <div class="ms-2 me-auto">
                        <div class="fw-bold">
                            <i class="fa-solid fa-user"></i> 
                            <?php echo htmlspecialchars($userMessage['fname'] . ' ' . $userMessage['lname']); ?>
                        </div>
                        <small class="text-muted">
                            <?php echo htmlspecialchars($userMessage['message']); ?>
                        </small><br>
                        <span class="badge bg-secondary mt-1">
                            <?php echo timeAgo($userMessage['timestamp']); ?>
                        </span>
                    </div>
                    <a href="admin_reply.php?product_id=<?php echo $productId; ?>&message_id=<?php echo $userMessage['message_id']; ?>&sender_id=<?php echo $userMessage['user_id']; ?>" 
                       class="btn btn-sm btn-success">
                        <i class="fa-solid fa-reply"></i> Reply
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <?php endwhile; ?>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
