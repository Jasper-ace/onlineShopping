<?php
session_start();
include('connection.php');

$query = isset($_GET['query']) ? trim($_GET['query']) : '';

if ($query !== '') {
    $sql = "SELECT product_name FROM products WHERE LOWER(product_name) LIKE LOWER(?) LIMIT 5";
    $stmt = $conn->prepare($sql);
    $searchTerm = $query . '%'; 
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    $suggestions = [];
    while ($row = $result->fetch_assoc()) {
        $suggestions[] = $row['product_name'];
    }

    $stmt->close();
    $conn->close();

    header('Content-Type: application/json');
    echo json_encode($suggestions);
} else {
    echo json_encode([]);
}
?>
