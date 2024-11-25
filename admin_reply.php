<?php
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: adminLogin.php');
    exit;
}

include('connection.php');

// Get the product_id and other parameters from the URL
if (isset($_GET['product_id'])) {
    $productId = $_GET['product_id'];
    $messageId = isset($_GET['message_id']) ? $_GET['message_id'] : null;
    $senderId = isset($_GET['sender_id']) ? $_GET['sender_id'] : null;

    // Fetch the messages and admin replies for the specific product and sender
   // Fetch the messages and admin replies for the specific product and sender (user_id)
$stmt = $conn->prepare("
SELECT u.fname, u.lname, m.message, m.timestamp, 'user' AS role, m.sender_id
FROM messages m
JOIN users u ON m.sender_id = u.user_id
WHERE m.product_id = ? AND m.sender_id = ?  

UNION ALL

SELECT a.fname, a.lname, ar.reply AS message, ar.timestamp, 'admin' AS role, ar.admin_id AS sender_id
FROM admin_reply ar
JOIN admin a ON ar.admin_id = a.id
WHERE ar.product_id = ? AND ar.user_id = ?  

ORDER BY timestamp ASC
");
$stmt->bind_param("iiii", $productId, $senderId, $productId, $senderId);  // Filter for both product_id and sender_id
$stmt->execute();
$chatMessages = $stmt->get_result();


    // Fetch the product name for the header
    $stmt = $conn->prepare("SELECT product_name FROM products WHERE id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $productResult = $stmt->get_result();
    $product = $productResult->fetch_assoc();
} else {
    echo "No product selected.";
    exit;
}// Handle admin's reply
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_message'])) {
    $replyMessage = $_POST['reply_message'];
    $adminId = $_SESSION['admin_id'];

    // Ensure senderId is set correctly
    $userId = isset($_GET['sender_id']) ? $_GET['sender_id'] : null;

    // Insert the admin's reply into the admin_reply table
    $stmt = $conn->prepare("INSERT INTO admin_reply (admin_id, user_id, product_id, reply) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $adminId, $userId, $productId, $replyMessage);

    // Execute the query and check for errors
    if ($stmt->execute()) {
        // Redirect to the same page to reload the chat with the message ID and sender ID
        header("Location: admin_reply.php?product_id=$productId&message_id=$messageId&sender_id=$senderId");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Chat - Product: <?php echo htmlspecialchars($product['product_name']); ?></title>
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

        .chat-container {
            margin-top: 20px;
        }

        .message {
            padding: 10px;
            margin-bottom: 10px;
            background-color: #f1f1f1;
            border-radius: 5px;
        }

        .message.admin {
            background-color: #d4edda;
            text-align: right;
        }

        .message.user {
            background-color: #cce5ff;
            text-align: left;
        }

        .reply-form {
            margin-top: 20px;
        }

        .reply-form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            resize: vertical;
        }

        .reply-form button {
            margin-top: 10px;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .reply-form button:hover {
            background-color: #45a049;
        }
    </style>
</head>

<body>

    <h1>Chat with User: <?php echo htmlspecialchars($product['product_name']); ?></h1>

    <div class="chat-container">
        <!-- Display all messages (user and admin) for the selected product and specific sender -->
        <?php while ($chatMessage = $chatMessages->fetch_assoc()): ?>
            <div class="message <?php echo htmlspecialchars($chatMessage['role']); ?>">
                <strong><?php echo htmlspecialchars($chatMessage['fname'] . ' ' . $chatMessage['lname']); ?>:</strong><br>
                <?php echo nl2br(htmlspecialchars($chatMessage['message'])); ?><br>
                <small>Sent on: <?php echo htmlspecialchars($chatMessage['timestamp']); ?></small>
            </div>
        <?php endwhile; ?>
    </div>

    <div class="reply-form">
        <form action="admin_reply.php?product_id=<?php echo $productId; ?>&message_id=<?php echo $messageId; ?>&sender_id=<?php echo $senderId; ?>" method="POST">
            <textarea name="reply_message" rows="4" placeholder="Type your reply here..." required></textarea><br>
            <button type="submit">Reply</button>
        </form>
    </div>

</body>
</html>
