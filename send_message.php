<?php
session_start();
include("connection.php");

if (isset($_POST['message'], $_POST['seller_id'], $_POST['user_id'], $_POST['product_id'])) {
    $message = trim($_POST['message']);
    $sellerId = $_POST['seller_id'];
    $userId = $_POST['user_id'];
    $productId = $_POST['product_id'];

    error_log("Received message: " . $message);

    $query = "INSERT INTO messages (sender_id, recipient_id, admin_id, product_id, message, timestamp) 
              VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);

    if ($stmt === false) {
        echo "Error preparing query: " . $conn->error;
    } else {
        $stmt->bind_param("iiiis", $userId, $sellerId, $sellerId, $productId, $message);

        if ($stmt->execute()) {
            echo "Message sent";
        } else {
            echo "Error sending message: " . $stmt->error;
        }
    }
} else {
    echo "Missing parameters";
}
?>
