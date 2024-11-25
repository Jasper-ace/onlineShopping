<?php
session_start();
include('connection.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: signin.php');
    exit;
}

$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

if (is_numeric($searchTerm)) {
    $sql = "SELECT * FROM products WHERE id = ?";
} else {
    $sql = "SELECT * FROM products WHERE product_name LIKE ?";
}

$stmt = $conn->prepare($sql);
if (is_numeric($searchTerm)) {
    $stmt->bind_param("i", $searchTerm); 
} else {
    $searchTermLike = "%" . $searchTerm . "%"; 
    $stmt->bind_param("s", $searchTermLike); 
}
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($products);
