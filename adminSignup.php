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
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">

  <div class="card shadow-lg p-4" style="width: 28rem;">
    <h2 class="text-center mb-4">Admin Sign-Up</h2>

    <?php if ($message): ?>
      <div class="alert alert-info text-center"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="mb-3">
        <label for="shopname" class="form-label">Shop Name</label>
        <input type="text" class="form-control" id="shopname" name="shopname" required>
      </div>

      <div class="mb-3">
        <label for="fname" class="form-label">First Name</label>
        <input type="text" class="form-control" id="fname" name="fname" required>
      </div>

      <div class="mb-3">
        <label for="mname" class="form-label">Middle Name</label>
        <input type="text" class="form-control" id="mname" name="mname">
      </div>

      <div class="mb-3">
        <label for="lname" class="form-label">Last Name</label>
        <input type="text" class="form-control" id="lname" name="lname" required>
      </div>

      <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input type="text" class="form-control" id="username" name="username" required>
      </div>

      <div class="mb-3">
        <label for="birthdate" class="form-label">Birthdate</label>
        <input type="date" class="form-control" id="birthdate" name="birthdate" required>
      </div>

      <div class="mb-3 position-relative">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" required>
        <i class="fa-solid fa-eye position-absolute top-50 end-0 translate-middle-y me-3 text-secondary"
           style="cursor:pointer;" onclick="togglePasswordVisibility('password')"></i>
      </div>

      <div class="mb-3 position-relative">
        <label for="repeat_password" class="form-label">Repeat Password</label>
        <input type="password" class="form-control" id="repeat_password" name="repeat_password" required>
        <i class="fa-solid fa-eye position-absolute top-50 end-0 translate-middle-y me-3 text-secondary"
           style="cursor:pointer;" onclick="togglePasswordVisibility('repeat_password')"></i>
      </div>

      <button type="submit" class="btn btn-success w-100">Sign Up</button>
      <p class="text-center mt-3">
        <a href="adminLogin.php" class="text-decoration-none">Have an account? Sign In</a>
      </p>
    </form>
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