<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: adminLogin.php'); 
    exit; 
}

include('connection.php'); 

$userId = $_SESSION['admin_id'];

// Fetch admin data
$stmt = $conn->prepare("SELECT * FROM admin WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$userResult = $stmt->get_result();
$userData = $userResult->fetch_assoc();

// Fetch products for display
$stmt = $conn->prepare("SELECT * FROM products WHERE admin_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$productsResult = $stmt->get_result();
$products = [];
while ($product = $productsResult->fetch_assoc()) {
    $products[] = $product;
}

// Calculate total earnings from sold products
$earningsStmt = $conn->prepare("
    SELECT SUM(oi.price * oi.quantity) AS totalEarnings
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE p.admin_id = ?
");
$earningsStmt->bind_param("i", $userId);
$earningsStmt->execute();
$earningsResult = $earningsStmt->get_result();
$earningsData = $earningsResult->fetch_assoc();
$totalEarnings = $earningsData['totalEarnings'] ?? 0;
$earningsStmt->close();

$message = "";

// Update profile
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

    // Refresh admin data
    $stmt = $conn->prepare("SELECT * FROM admin WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $userResult = $stmt->get_result();
    $userData = $userResult->fetch_assoc();
}

// Update password
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .password-requirements li.valid { color: green; }
        .password-requirements li.invalid { color: red; }
    </style>
    <script>
        function validatePassword() {
            const passwordInput = document.getElementById('new_password');
            const requirementsList = document.getElementById('password-requirements');
            const requirements = [
                { regex: /[A-Z]/, message: 'At least one uppercase letter' },
                { regex: /[a-z]/, message: 'At least one lowercase letter' },
                { regex: /[0-9]/, message: 'At least one numeric digit' },
                { regex: /[!@#$%^&*]/, message: 'At least one special symbol' },
                { regex: /.{8,}/, message: 'At least 8 characters long' }
            ];
            requirementsList.innerHTML = '';
            requirements.forEach(req => {
                const li = document.createElement('li');
                li.textContent = req.message;
                li.className = req.regex.test(passwordInput.value) ? 'valid' : 'invalid';
                requirementsList.appendChild(li);
            });
        }
    </script>
</head>
<body class="bg-light">
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="text-success">User Profile</h1>
        <a href="logout.php" class="btn btn-danger"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-danger text-center"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Profile Form -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">Profile Info</div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="fname" class="form-label">First Name:</label>
                            <input type="text" id="fname" name="fname" class="form-control" value="<?php echo htmlspecialchars($userData['fname']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="mname" class="form-label">Middle Name:</label>
                            <input type="text" id="mname" name="mname" class="form-control" value="<?php echo htmlspecialchars($userData['mname']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="lname" class="form-label">Last Name:</label>
                            <input type="text" id="lname" name="lname" class="form-control" value="<?php echo htmlspecialchars($userData['lname']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username:</label>
                            <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($userData['username']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="birthdate" class="form-label">Birthdate:</label>
                            <input type="date" id="birthdate" name="birthdate" class="form-control" value="<?php echo htmlspecialchars($userData['birthdate']); ?>" required>
                        </div>
                        <button type="submit" name="update_user" class="btn btn-success w-100">Update Profile</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Password Form -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">Change Password</div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="old_password" class="form-label">Old Password:</label>
                            <input type="password" id="old_password" name="old_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password:</label>
                            <input type="password" id="new_password" name="new_password" class="form-control" onkeyup="validatePassword()" required>
                            <ul id="password-requirements" class="password-requirements mt-2"></ul>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password:</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" name="update_password" class="btn btn-primary w-100">Change Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card shadow-sm mt-5">
        <div class="card-header bg-info text-white">Your Products</div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Picture</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Stocks</th>
                        <th>Total Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($products) > 0): ?>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><img src="<?php echo htmlspecialchars($product['picture']); ?>" class="img-thumbnail" style="width: 100px;"></td>
                                <td><?php echo htmlspecialchars($product['description']); ?></td>
                                <td>₱<?php echo htmlspecialchars($product['price']); ?></td>
                                <td><?php echo htmlspecialchars($product['stocks']); ?></td>
                                <td>₱<?php echo htmlspecialchars($product['price'] * $product['stocks']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No products found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Earnings -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body text-center bg-light">
            <h5 class="card-title">Total Earnings</h5>
            <p class="card-text fs-4 fw-bold text-success">₱<?php echo number_format($totalEarnings, 2); ?></p>
        </div>
    </div>

    <!-- Back Button -->
    <div class="text-center mt-4">
        <a href="dashboard.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back Home</a>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
