<?php
session_start();
include('connection.php');

$message = "";

// Check if user came from validate.php
if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot-password.php");
    exit;
}

$email_or_phone = $_SESSION['reset_email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Basic password validation
    if ($new_password !== $confirm_password) {
        $message = "Passwords do not match!";
    } elseif (strlen($new_password) < 8) {
        $message = "Password must be at least 8 characters long!";
    } else {
        $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);

        // Update the password in the database
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email_or_phone = ?");
        $stmt->bind_param("ss", $hashedPassword, $email_or_phone);
        if ($stmt->execute()) {
            // Clear reset session
            unset($_SESSION['reset_email']);
            unset($_SESSION['reset_code']);
            unset($_SESSION['code_generated_time']);

            $message = "Password reset successfully! You can now <a href='signin.php'>Sign In</a>.";
        } else {
            $message = "Failed to reset password. Try again!";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Password</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5" style="max-width: 400px;">
    <div class="card shadow-sm">
        <div class="card-body">
            <h4 class="card-title text-center mb-3">Reset Password</h4>

            <?php if (!empty($message)): ?>
                <div class="alert alert-info"><?php echo $message; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label for="new_password" class="form-label">New Password</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success w-100">Reset Password</button>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
