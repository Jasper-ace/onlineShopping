<?php
include('connection.php');

session_start();

// Check if admin is already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php'); 
    exit;
}

// Check if user is logged in (you can adjust this based on your logic)
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php'); 
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query for admin login
    $stmt = $conn->prepare("SELECT id, password FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($adminId, $hashed_password);
        $stmt->fetch();

        // Verify password for admin
        if (password_verify($password, $hashed_password)) {
            $_SESSION['admin_id'] = $adminId; // Use a separate session variable for admin
            header('Location: dashboard.php'); // Redirect to admin dashboard
            exit;
        } else {
            $error_message = "Invalid password. Please try again.";
        }
    } else {
        $error_message = "Username not found. Please sign up.";
    }

    $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-m5FqR6VMuIZu8hxvPZZRhtsDZHZBWvWn9bHs+7AuI7Nm5z2kbyGauAfLKhcoO9W6EKxGdMDPhqkmxupflEb1zQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            background: white;
            padding: 20px 30px 20px 20px; 
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 400px;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        label {
            font-weight: bold;
            margin-top: 10px;
            display: block;
            color: #555;
        }

        input[type="text"],
        input[type="password"],
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }

        input[type="submit"] {
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #218838;
        }

        .eye-icon {
            cursor: pointer;
            position: absolute;
            margin-left: -30px;
            margin-top: 10px;
            font-size: 18px;
            color: #555;
        }

        .error {
            color: red;
            font-weight: bold;
            text-align: center;
        }

        .form-group {
            position: relative;
        }

        .link {
            text-align: center;
            margin-top: 20px;
            color: #007bff;
            cursor: pointer;
            text-decoration: none; 
        }

        .link a {
            text-decoration: none; 
            color: #007bff; 
        }

        .link:hover a {
            text-decoration: underline; 
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Admin Login</h2>
        
        <?php if (isset($error_message)): ?>
            <p class="error"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                <i class="fa-solid fa-eye eye-icon" onclick="togglePasswordVisibility('password')"></i>
            </div>

            <input type="submit" value="Sign in">
        </form>
        <div class="link"><a href="adminSignup.php">Don't have an account? Sign Up</a></div> 
    </div>
    <script>
        function togglePasswordVisibility(inputId) {
            var input = document.getElementById(inputId);
            if (input.type === "password") {
                input.type = "text";
            } else {
                input.type = "password";
            }
        }
    </script>
</body>
</html>
