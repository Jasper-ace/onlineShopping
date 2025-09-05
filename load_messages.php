<?php
// load_messages.php

include("connection.php");

// Get the seller_id, user_id, and product_id from the request
$sellerId  = isset($_GET['seller_id'])  ? intval($_GET['seller_id'])  : 0;
$userId    = isset($_GET['user_id'])    ? intval($_GET['user_id'])    : 0;
$productId = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

/*
   Combine user/seller messages and admin replies
   into one result set, ordered by timestamp.
*/
$query = "
    SELECT 'msg' AS type, m.sender_id, m.message AS body, m.timestamp
    FROM messages m
    WHERE m.product_id = ?
      AND ((m.sender_id = ? AND m.recipient_id = ?) OR (m.sender_id = ? AND m.recipient_id = ?))

    UNION ALL

    SELECT 'admin' AS type, ar.admin_id AS sender_id, ar.reply AS body, ar.timestamp
    FROM admin_reply ar
    WHERE ar.product_id = ?
      AND ar.user_id = ?
      AND ar.admin_id = ?

    ORDER BY 
      STR_TO_DATE(timestamp, '%Y-%m-%d %H:%i:%s') ASC,
      STR_TO_DATE(timestamp, '%m/%d/%Y %h:%i:%s %p') ASC,
      STR_TO_DATE(timestamp, '%h:%i %p') ASC,
      timestamp ASC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("iiiiiiii", 
    $productId, $userId, $sellerId, $sellerId, $userId,  // for messages
    $productId, $userId, $sellerId                       // for admin_reply
);
$stmt->execute();
$result = $stmt->get_result();

// Display all messages in correct order
while ($row = $result->fetch_assoc()) {
    $time = date("h:i A", strtotime($row['timestamp']));

    if ($row['type'] === 'msg') {
        $isUserMessage = ($row['sender_id'] == $userId);
        $messageClass  = $isUserMessage ? 'message-you' : 'message-seller';

        echo "<div class='message $messageClass'>
                " . htmlspecialchars($row['body']) . "
                <div class='timestamp'>$time</div>
              </div>";
    } else { // admin reply
        echo "<div class='message admin-reply'>
                " . htmlspecialchars($row['body']) . "
                <div class='timestamp'>$time</div>
              </div>";
    }
}
?>
