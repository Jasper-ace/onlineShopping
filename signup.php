<?php
include('connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $birthdate = $_POST['birthdate'];
    $email_or_phone = $_POST['email_or_phone'];
    $address = $_POST['address'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);  

    $stmt = $conn->prepare("INSERT INTO users (fname, lname, birthdate, email_or_phone, address, password) VALUES (?, ?, ?, ?, ?, ?)");
    
    if ($stmt) {
        $stmt->bind_param("ssssss", $fname, $lname, $birthdate, $email_or_phone, $address, $password);

        if ($stmt->execute()) {
            echo "Record inserted successfully!";
            header("Location: signin.php");
            exit();
        } else {
            echo "Error inserting record: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
    
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
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
        .signup-container {
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
        input[type="text"], input[type="date"], input[type="email"], input[type="tel"], input[type="password"] {
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
    </style>
</head>
<body>

<div class="signup-container">
    <h2>Create your account</h2>
    <form action="signup.php" method="post">

        <label for="fname">First Name:</label>
        <input type="text" id="fname" name="fname" required>

        <label for="lname">Last Name:</label>
        <input type="text" id="lname" name="lname" required>

        <label for="birthdate">Birthdate:</label>
        <input type="date" id="birthdate" name="birthdate" required>

        <label for="email_or_phone">Email or Phone Number:</label>
        <input type="text" id="email_or_phone" name="email_or_phone" required>

        <label for="address">Address:</label>
        <input type="text" id="address" name="address" required> 

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>

        <input type="submit" value="Create Account">
    </form>
    <div class="account-link">
        <p>Have an account?<a href="signin.php"> Sign in</a></p>
    </div>
</div>

</body>
</html>
