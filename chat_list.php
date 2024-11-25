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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat List</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f9f9f9;
        }

        h1 {
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

        .chat-icon-container {
            position: fixed;
            top: 25px;
            right: 10px;
            font-size: 48px;
            color: black;
            text-decoration: none;
        }

        .chat-icon-container:hover {
            color: blue;
        }

        .message-list {
            margin-top: 10px;
        }

        .message {
            background-color: #e9e9e9;
            padding: 5px;
            margin: 5px 0;
            border-radius: 4px;
        }

        .message .user {
            font-weight: bold;
        }

        .message .timestamp {
            font-size: small;
            color: gray;
        }

        .reply-btn {
            background-color: #4CAF50;
            color: white;
            padding: 6px 12px;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            text-decoration: none;
        }

        .reply-btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h1>Chat List</h1>
    <p>Select a product to see the chats:</p>

    <!-- Display products and users with their messages -->
    <table>
        <tr>
            <th>Product Name</th>
            <th>User</th>
            <th>Message</th>
            <th>Last Message Timestamp</th>
            <th>Action</th>
        </tr>
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

            // Display the messages for each user for this product
            foreach ($userMessages as $userMessage):
        ?>
            <tr>
                <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                <td><strong><?php echo htmlspecialchars($userMessage['fname'] . ' ' . $userMessage['lname']); ?>:</strong></td>
                <td><?php echo htmlspecialchars($userMessage['message']); ?></td>
                <td><?php echo timeAgo($userMessage['timestamp']); ?></td>
                <td>
                    <!-- Reply button that redirects to admin_reply.php with product_id, message_id, and sender_id as query parameters -->
                    <a href="admin_reply.php?product_id=<?php echo $productId; ?>&message_id=<?php echo $userMessage['message_id']; ?>&sender_id=<?php echo $userMessage['user_id']; ?>" class="reply-btn">Reply</a>
                </td>
            </tr>
        <?php endforeach; endwhile; ?>
    </table>
</body>
</html>
