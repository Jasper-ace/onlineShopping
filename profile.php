<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: adminLogin.php'); 
    exit; 
}

include('connection.php'); 

$userId = $_SESSION['admin_id'];
$stmt = $conn->prepare("SELECT * FROM admin WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$userResult = $stmt->get_result();
$userData = $userResult->fetch_assoc();

$stmt = $conn->prepare("SELECT * FROM products WHERE admin_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$productsResult = $stmt->get_result();

$totalEarnings = 0;
$products = [];
while ($product = $productsResult->fetch_assoc()) {
    $totalEarnings += $product['price'] * $product['stocks']; 
    $products[] = $product; 
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $fname = $_POST['fname'];
    $mname = $_POST['mname'];
    $lname = $_POST['lname'];
    $username = $_POST['username'];
    $birthdate = $_POST['birthdate'];

    $stmt = $conn->prepare("UPDATE admin SET fname = ?, mname = ?, lname = ?, username = ?, birthdate = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $fname, $mname, $lname, $username, $birthdate, $userId);
    if ($stmt->execute()) {
        $message = "User information updated successfully!";
    } else {
        $message = "Failed to update user information!";
    }
    $stmt->close();

    $stmt = $conn->prepare("SELECT * FROM admin WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $userResult = $stmt->get_result();
    $userData = $userResult->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $oldPassword = $_POST['old_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if (password_verify($oldPassword, $userData['password'])) {
        if ($newPassword === $confirmPassword) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("UPDATE admin SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashedPassword, $userId);
            if ($stmt->execute()) {
                $message = "Password updated successfully!";
            } else {
                $message = "Failed to update password!";
            }
            $stmt->close();
        } else {
            $message = "New passwords do not match!";
        }
    } else {
        $message = "Old password is incorrect!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #e9ecef;
            color: #343a40;
        }

        .container {
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1,
        h2 {
            color: #4CAF50;
            text-align: center;
            margin-bottom: 20px;
        }

        .message {
            padding: 10px;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            color: red;
            margin-bottom: 20px;
            text-align: center;
        }

        .password-requirements {
            margin-top: 10px;
            font-size: 0.9em;
            color: #6c757d;
            list-style-type: none;
            padding: 0;
        }

        .valid {
            color: green;
        }

        .invalid {
            color: red;
        }

        a {
            display: inline-block;
            text-align: center;
            margin: 10px 0;
            color: #4CAF50;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }

        a:hover {
            color: #388e3c;
        }

        form {
            margin-bottom: 20px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
        }

        label {
            margin-top: 10px;
            display: block;
            font-weight: bold;
        }

        input[type="text"],
        input[type="date"],
        input[type="password"],
        input[type="submit"] {
            width: calc(100% - 22px);
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }

        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            margin-top: 20px;
            transition: background-color 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #4CAF50;
            color: white;
        }

        img {
            width: 100px;
            height: auto;
            border-radius: 5px;
        }

        .product-table {
            margin-top: 20px;
        }

        .earnings {
            font-size: 1.2em;
            margin-top: 20px;
            text-align: center;
            padding: 10px;
            background-color: #e7f3fe;
            border: 1px solid #b8daff;
            border-radius: 5px;
            color: #31708f;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .action-buttons a {
            margin: 0 10px;
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .action-buttons a:hover {
            background-color: #388e3c;
        }
    </style>
    <script>
        function validatePassword() {
            const passwordInput = document.getElementById('new_password');
            const requirementsList = document.getElementById('password-requirements');
            const requirements = [
                { regex: /[A-Z]/, message: 'At least one uppercase letter', valid: false },
                { regex: /[a-z]/, message: 'At least one lowercase letter', valid: false },
                { regex: /[0-9]/, message: 'At least one numeric digit', valid: false },
                { regex: /[!@#$%^&*]/, message: 'At least one special symbol', valid: false },
                { regex: /.{8,}/, message: 'At least 8 characters long', valid: false }
            ];

            // Clear previous messages
            requirementsList.innerHTML = '';

            // Check each requirement
            requirements.forEach(req => {
                req.valid = req.regex.test(passwordInput.value);
                const li = document.createElement('li');
                li.textContent = req.message;
                li.className = req.valid ? 'valid' : 'invalid';
                requirementsList.appendChild(li);
            });
        }
    </script>
</head>

<body>

    <div class="container">
        <h1>User Profile</h1>

        <div class="action-buttons">
            <a href="logout.php">Logout</a>
        </div>

        <?php if (!empty($message)): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <h2>Profile and Change Password</h2>
        <div>
            <form method="POST" action="">
                <label for="fname">First Name:</label>
                <input type="text" id="fname" name="fname" value="<?php echo htmlspecialchars($userData['fname']); ?>" required>

                <label for="mname">Middle Name:</label>
                <input type="text" id="mname" name="mname" value="<?php echo htmlspecialchars($userData['mname']); ?>" required>

                <label for="lname">Last Name:</label>
                <input type="text" id="lname" name="lname" value="<?php echo htmlspecialchars($userData['lname']); ?>" required>

                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($userData['username']); ?>" required>

                <label for="birthdate">Birthdate:</label>
                <input type="date" id="birthdate" name="birthdate" value="<?php echo htmlspecialchars($userData['birthdate']); ?>" required>

                <input type="submit" name="update_user" value="Update Profile">
            </form>

            <form method="POST" action="">
                <h3>Change Password</h3>
                <label for="old_password">Old Password:</label>
                <input type="password" id="old_password" name="old_password" required>

                <label for="new_password">New Password:</label>
                <input type="password" id="new_password" name="new_password" required onkeyup="validatePassword()">

                <ul id="password-requirements" class="password-requirements"></ul>

                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>

                <input type="submit" name="update_password" value="Change Password">
            </form>
        </div>

        <h2>Your Products</h2>
        <div class="product-table">
            <table>
                <thead>
                    <tr>
                        <th>Product Picture</th>
                        <th>Product Name</th>
                        <th>Price</th>
                        <th>Stocks</th>
                        <th>Total Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($products) > 0): ?>
                        <?php foreach ($products as $product): ?>
                            <tr>
                            <td><img src="<?php echo htmlspecialchars($product['picture']); ?>" alt="Product Image" style="width: 100px; height: auto;">
                            </td>
                                <td><?php echo htmlspecialchars($product['description']); ?></td>
                                <td><?php echo htmlspecialchars($product['price']); ?></td>
                                <td><?php echo htmlspecialchars($product['stocks']); ?></td>
                                <td><?php echo htmlspecialchars($product['price'] * $product['stocks']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align:center;">No products found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="earnings">
            <strong>Expected Earnings: $<?php echo htmlspecialchars($totalEarnings); ?></strong>
        </div>
        <div class="action-buttons">
            <a href="dashboard.php">Back Home</a>
        </div>
    </div>

</body>

</html>
