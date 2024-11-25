<?php
session_start(); 
include('connection.php');

$message = ""; 
if (isset($_SESSION['user_id'])) {
    header('Location: userDashboard.php'); 
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_or_phone = $_POST['email_or_phone'];
    $password = $_POST['password'];

    $sql = "SELECT user_id, password FROM users WHERE email_or_phone = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        error_log("Prepare failed: " . htmlspecialchars($conn->error));
        exit; 
    }

    $stmt->bind_param("s", $email_or_phone);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($userId, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $userId; 
            header("Location: userDashboard.php");
            exit(); 
        } else {
            $message = "Invalid password. Please try again.";
        }
    } else {
        $message = "No account found with that email or phone number.";
    }

    $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
    <style>
        body {
            display: flex; 
            height: 100vh; 
            margin: 0; 
            background-image: url('https://cdn.shopify.com/s/files/1/0070/7032/articles/ecommerce_20shopping_20cart_4c343e41-1041-49d3-bca8-3d7d1aa06d90.png?v=1729263457&originalWidth=1848&originalHeight=782&width=1600');
            background-size: cover; 
            background-position: left; 
            background-repeat: no-repeat; 
        }
        .signin-container {
            width: 400px; 
            background-color: rgba(22, 45, 80, 0.5); 
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px); 
            margin-left: 1px; 
            display: flex;
            flex-direction: column; 
            justify-content: center; 
            box-sizing: border-box; 
            font-family: Arial, sans-serif;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        input[type="text"], input[type="password"] {
            width: calc(100% - 0px); 
            padding: 10px; 
            margin: 10px 0; 
            border: none;
            border-radius: 4px;
            box-sizing: border-box; 
        }
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: transparent; 
            border: 2px solid #fff; 
            border-radius: 4px;
            color: white;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: rgba(255, 255, 255, 0.2); 
        }
        .account-link {
            text-align: center;
            margin-top: 15px;
        }
        .account-link a {
            color: #fff;
            text-decoration: none;
        }
        .account-link a:hover {
            text-decoration: underline;
        }
        .error-message {
            color: red;
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<div class="signin-container">
    <h2>Sign In</h2>
    <form action="signin.php" method="post">
        <label for="email_or_phone">Email or Phone Number:</label>
        <input type="text" id="email_or_phone" name="email_or_phone" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>

        <input type="submit" value="Sign In">
    </form>
    <div class="account-link">
        <p>Don't have an account? <a href="signup.php">Create one</a></p>
    </div>
    <?php if (!empty($message)): ?>
        <div class="error-message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
</div>

</body>
</html>
