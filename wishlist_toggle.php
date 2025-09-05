<?php
session_start();
include("connection.php");

header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Login required"]);
    exit;
}

$userId = $_SESSION['user_id'];
$productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$adminId = isset($_POST['admin_id']) ? intval($_POST['admin_id']) : 0;

if ($productId <= 0 || $adminId <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid input"]);
    exit;
}

// Check if already in wishlist
$sql = "SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $userId, $productId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Remove from wishlist
    $sql = "DELETE FROM wishlist WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $userId, $productId);
    $stmt->execute();
    echo json_encode(["success" => true, "action" => "removed"]);
} else {
    // Add to wishlist
    $sql = "INSERT INTO wishlist (user_id, product_id, admin_id) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $userId, $productId, $adminId);
    $stmt->execute();
    echo json_encode(["success" => true, "action" => "added"]);
}
