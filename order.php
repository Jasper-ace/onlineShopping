<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: signin.php');
    exit;
}

// Optional: Fetch order details from the session or database if needed
// For example, you could pass order ID through session variables during the checkout process.

// Confirmation message
$orderMessage = "Thank you! Your order has been placed successfully.";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <style>
        /* Styling for confirmation page */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .confirmation-container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            text-align: center;
        }
        .confirmation-container h1 {
            color: #0066cc;
        }
        .confirmation-container p {
            font-size: 18px;
            color: #333;
        }
        .confirmation-container .button {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #0066cc;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .confirmation-container .button:hover {
            background-color: #004a99;
        }
    </style>
</head>
<body>

<div class="confirmation-container">
    <h1>Order Confirmation</h1>
    <p><?php echo $orderMessage; ?></p>
    <p>Your order ID is: <strong>#<?php echo uniqid(); ?></strong></p> <!-- Optional order ID -->
    <a href="userDashboard.php" class="button">Back to Home</a>
</div>

</body>
</html>
