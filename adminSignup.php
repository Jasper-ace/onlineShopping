<?php
include('connection.php');

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fname = $_POST['fname'];
    $mname = $_POST['mname'];
    $lname = $_POST['lname'];
    $username = $_POST['username'];
    $birthdate = $_POST['birthdate'];
    $password = $_POST['password'];
    $repeat_password = $_POST['repeat_password'];
    $shopname = $_POST['shopname']; // Add this line to capture the shop name

    $password_regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';

    if (!empty($fname) && !empty($lname) && !empty($username) && !empty($birthdate) && !empty($password) && !empty($repeat_password) && !empty($shopname)) {
        if ($password === $repeat_password) {
            if (preg_match($password_regex, $password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $checkStmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
                $checkStmt->bind_param("s", $username);
                $checkStmt->execute();
                $result = $checkStmt->get_result();

                if ($result->num_rows > 0) {
                    $message = "<p class='error'>Username already exists. Please choose a different username.</p>";
                } else {
                    // Updated query to include shop name
                    $stmt = $conn->prepare("INSERT INTO admin (fname, mname, lname, username, birthdate, password, shopname) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssssss", $fname, $mname, $lname, $username, $birthdate, $hashed_password, $shopname);

                    if ($stmt->execute()) {
                        $message = "<p class='success'>Account created successfully!</p>";
                    } else {
                        $message = "<p class='error'>Error: " . $stmt->error . "</p>";
                    }
                    $stmt->close();
                }
                $checkStmt->close();
            } else {
                $message = "<p class='error'>Password must be at least 8 characters long and include uppercase, lowercase, numbers, and special characters.</p>";
            }
        } else {
            $message = "<p class='error'>Passwords do not match.</p>";
        }
    } else {
        $message = "<p class='error'>Please fill in all fields.</p>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Sign-Up</title>
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
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 400px;
            padding: 50px 50px 50px 35px; 
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
        input[type="date"],
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

        .success {
            color: green;
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
        <h2>Admin Sign-Up Page</h2>
        <?php if ($message): ?>
            <div><?php echo $message; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <label for="shopname">Shop Name:</label>
            <input type="text" id="shopname" name="shopname" required>

            <label for="fname">First Name:</label>
            <input type="text" id="fname" name="fname" required>

            <label for="mname">Middle Name:</label>
            <input type="text" id="mname" name="mname">

            <label for="lname">Last Name:</label>
            <input type="text" id="lname" name="lname" required>

            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="birthdate">Birthdate:</label>
            <input type="date" id="birthdate" name="birthdate" required>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                <i class="fa-solid fa-eye eye-icon" onclick="togglePasswordVisibility('password')"></i>
            </div>

            <div class="form-group">
                <label for="repeat_password">Repeat Password:</label>
                <input type="password" id="repeat_password" name="repeat_password" required>
                <i class="fa-solid fa-eye eye-icon" onclick="togglePasswordVisibility('repeat_password')"></i>
            </div>

            <input type="submit" value="Sign Up">
            <div class="link"><a href="adminLogin.php">Have an account? Sign In</a></div> 
        </form>
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
