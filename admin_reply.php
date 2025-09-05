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
    <title>Admin Chat - <?php echo htmlspecialchars($product['product_name']); ?></title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .chat-container {
            margin-top: 20px;
            max-height: 70vh;
            overflow-y: auto;
            padding: 15px;
            background: #fff;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        .message {
            padding: 10px 15px;
            margin-bottom: 12px;
            border-radius: 10px;
            max-width: 70%;
            word-wrap: break-word;
        }
        .message.user {
            background-color: #cce5ff;
            align-self: flex-start;
        }
        .message.admin {
            background-color: #d4edda;
            align-self: flex-end;
            text-align: right;
        }
        .chat-wrapper {
            display: flex;
            flex-direction: column;
        }
    </style>
</head>

<body class="bg-light">

<div class="container mt-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4">
            <i class="fa-solid fa-comments"></i> Chat - <?php echo htmlspecialchars($product['product_name']); ?>
        </h1>
        <a href="chat_list.php" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left"></i> Back to Chat List
        </a>
    </div>

    <!-- Chat Messages -->
    <div class="chat-container d-flex flex-column">
        <?php while ($chatMessage = $chatMessages->fetch_assoc()): ?>
            <div class="message <?php echo htmlspecialchars($chatMessage['role']); ?>">
                <div class="fw-bold mb-1">
                    <?php echo htmlspecialchars($chatMessage['fname'] . ' ' . $chatMessage['lname']); ?>
                </div>
                <div><?php echo nl2br(htmlspecialchars($chatMessage['message'])); ?></div>
                <small class="text-muted d-block mt-1">
                    <i class="fa-regular fa-clock"></i>
                    <?php echo htmlspecialchars($chatMessage['timestamp']); ?>
                </small>
            </div>
        <?php endwhile; ?>
    </div>

    <!-- Reply Form -->
    <div class="card mt-4 shadow-sm">
        <div class="card-body">
            <form action="admin_reply.php?product_id=<?php echo $productId; ?>&message_id=<?php echo $messageId; ?>&sender_id=<?php echo $senderId; ?>" method="POST">
                <div class="mb-3">
                    <textarea name="reply_message" rows="3" class="form-control" placeholder="Type your reply here..." required></textarea>
                </div>
                <button type="submit" class="btn btn-success">
                    <i class="fa-solid fa-paper-plane"></i> Send Reply
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
    window.onload = loadMessages;
    setInterval(loadMessages, 3000); // auto refresh every 3s
</script>
</body>
</html>
