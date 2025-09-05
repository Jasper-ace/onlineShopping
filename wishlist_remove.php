<?php
session_start();
include("connection.php");

header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Login required"]);
    exit;
}

$userId = $_SESSION['user_id'];
$wishlistId = isset($_POST['wishlist_id']) ? intval($_POST['wishlist_id']) : 0;

if ($wishlistId <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
    exit;
}

$sql = "DELETE FROM wishlist WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $wishlistId, $userId);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Database error"]);
}
