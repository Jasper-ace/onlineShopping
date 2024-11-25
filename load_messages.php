<?php
// load_messages.php

include("connection.php");

// Get the seller_id, user_id, and product_id from the request
$sellerId = isset($_GET['seller_id']) ? intval($_GET['seller_id']) : 0;
$userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$productId = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

// Fetch user-seller messages
$query = "SELECT sender_id, recipient_id, message, timestamp FROM messages WHERE product_id = ? AND (sender_id = ? OR recipient_id = ?) ORDER BY timestamp ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("iii", $productId, $userId, $userId);
$stmt->execute();
$result = $stmt->get_result();

// Display messages
while ($row = $result->fetch_assoc()) {
    $isUserMessage = $row['sender_id'] == $userId;
    $messageClass = $isUserMessage ? 'message-you' : 'message-seller';
    echo "<div class='message $messageClass'>" . htmlspecialchars($row['message']) . "</div>";
}

// Fetch admin replies associated with the product
$query = "SELECT reply, timestamp FROM admin_reply WHERE product_id = ? ORDER BY timestamp ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $productId);
$stmt->execute();
$adminReplies = $stmt->get_result();

// Display admin replies
while ($adminReply = $adminReplies->fetch_assoc()) {
    echo "<div class='message admin-reply'>" . htmlspecialchars($adminReply['reply']) . "</div>";
}
?>
