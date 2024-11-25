<?php
session_start();
include('connection.php');

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['message' => 'User not logged in']);
    exit;
}

// Retrieve POST data
if (isset($_POST['product_id'], $_POST['quantity'])) {
    $user_id = $_SESSION['user_id'];
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    // Debugging - check if values are received
    error_log("Product ID: $product_id, Quantity: $quantity");

    // Validate product and check availability
    $query = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        $total_price = number_format((float)$product['price'] * $quantity, 2, '.', '');

        // Check if product is already in the cart
        $check_query = "SELECT * FROM cart WHERE user_id = ? AND product_id = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("ii", $user_id, $product_id);
        $check_stmt->execute();
        $cart_result = $check_stmt->get_result();

        if ($cart_result->num_rows > 0) {
            // Update quantity in cart
            $cart = $cart_result->fetch_assoc();
            $new_quantity = $cart['quantity'] + $quantity;
            $new_total_price = number_format((float)$product['price'] * $new_quantity, 2, '.', '');

            $update_query = "UPDATE cart SET quantity = ?, total_price = ? WHERE user_id = ? AND product_id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("idii", $new_quantity, $new_total_price, $user_id, $product_id);

            if ($update_stmt->execute()) {
                echo json_encode(['message' => 'Product quantity updated in cart']);
            } else {
                echo json_encode(['message' => 'Error updating cart: ' . $conn->error]);
            }
        } else {
            // Insert new product into cart
            $insert_query = "INSERT INTO cart (user_id, product_id, quantity, total_price) VALUES (?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("iiid", $user_id, $product_id, $quantity, $total_price);

            if ($insert_stmt->execute()) {
                // Echo success message if product is added successfully
                echo json_encode(['message' => 'Product added to cart successfully']);
            } else {
                echo json_encode(['message' => 'Error adding to cart: ' . $conn->error]);
            }
        }
    } else {
        echo json_encode(['message' => 'Product not found']);
    }
} else {
    echo json_encode(['message' => 'Invalid request']);
}
?>
