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
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">

  <div class="card shadow-lg p-4" style="width: 26rem;">
    <h2 class="text-center mb-4">Admin Login</h2>

    <?php if (!empty($error_message)): ?>
      <div class="alert alert-danger text-center"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input type="text" class="form-control" id="username" name="username" required>
      </div>

      <div class="mb-3 position-relative">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" required>
        <i class="fa-solid fa-eye position-absolute top-50 end-0 translate-middle-y me-3 text-secondary"
           style="cursor:pointer;" onclick="togglePasswordVisibility('password')"></i>
      </div>

      <button type="submit" class="btn btn-success w-100">Sign In</button>
    </form>

    <p class="text-center mt-3">
      <a href="adminSignup.php" class="text-decoration-none">Don't have an account? Sign Up</a>
    </p>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    function togglePasswordVisibility(inputId) {
      const input = document.getElementById(inputId);
      input.type = input.type === "password" ? "text" : "password";
    }
  </script>
</body>
</html>