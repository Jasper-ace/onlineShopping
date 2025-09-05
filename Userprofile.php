<?php
session_start();
include('connection.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: signin.php');
    exit;
}

$userId = $_SESSION['user_id'];

$userSql = "SELECT user_id, fname, lname, birthdate, email_or_phone, profile_picture, Address FROM users WHERE user_id = ?";
$userStmt = $conn->prepare($userSql);
$userStmt->bind_param("i", $userId);
$userStmt->execute();
$userResult = $userStmt->get_result();
$user = $userResult->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fname = htmlspecialchars(trim($_POST['fname']));
    $lname = htmlspecialchars(trim($_POST['lname']));
    $email = htmlspecialchars(trim($_POST['email_or_phone']));
    $address = htmlspecialchars(trim($_POST['Address'])); 

    $uploadFilePath = $user['profile_picture']; 
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        $fileName = basename($_FILES['profile_picture']['name']);
        $newFilePath = $uploadDir . uniqid() . '_' . $fileName;

        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $newFilePath)) {
            $uploadFilePath = $newFilePath; 
        } else {
            $errorMessage = "Error uploading profile picture.";
        }
    }

    $updateSql = "UPDATE users SET fname = ?, lname = ?, email_or_phone = ?, profile_picture = ?, Address = ? WHERE user_id = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("sssssi", $fname, $lname, $email, $uploadFilePath, $address, $userId);

    if ($updateStmt->execute()) {
        $successMessage = "Profile updated successfully.";
        $updateStmt->close();

        header('Location: UserProfile.php');
        exit();
    } else {
        $errorMessage = "Error updating profile: " . $updateStmt->error;
    }
}

$userStmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 800px;
            margin: auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            text-align: center;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input[type="text"],
        .form-group input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            background-color: #28a745;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
        .profile-picture {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background-color: #e9ecef;
            margin: 0 auto 20px; 
            cursor: pointer; 
            position: relative;
        }
        .profile-picture img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }
        .profile-picture .add-icon {
            position: absolute;
            font-size: 30px;
            color: #555;
        }
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background-color: #1f3b73;
            color: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .navbar h1 {
            margin: 0;
            font-size: 1.8rem;
            color: white; /* Change the color of the title to white */
        }

        .navbar a {
            color: white;
            text-decoration: none;
            font-size: 1rem;
            padding: 10px;
            transition: background-color 0.3s ease;
        }

        .navbar a:hover {
            background-color: #15315b;
            border-radius: 5px;
        }

        .navbar form {
            display: flex;
            position: relative;
        }

        .navbar input[type="text"] {
            padding: 8px;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            width: 250px;
            margin-right: 10px;
        }

        .navbar button {
            padding: 8px 15px;
            border: none;
            background-color: #009688;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s ease;
        }

        .navbar button:hover {
            background-color: #007d6a;
        }
        .burger-menu {
            font-size: 1.8rem;
            cursor: pointer;
            position: relative;
        }

        .dropdown-menu {
            display: none; 
            position: absolute;
            top: 100%; 
            right: 0; 
            background-color: white;
            border: 1px solid #ccc;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 150px;
            z-index: 1000;
        }

        .dropdown-menu a {
            display: block;
            padding: 10px;
            color: #333;
            text-decoration: none;
        }

        .dropdown-menu a:hover {
            background-color: #f0f0f0;
        }

        /* Show burger icon on smaller screens */
        @media (max-width: 768px) {
            .navbar h1,
            .navbar form,
            .navbar a {
                display: none;
            }

            .burger-menu {
                display: block;
            }
        }
</style>
    <script>
        function openFileDialog() {
            document.getElementById('profile_picture').click();
        }
        function toggleDropdown() {
            const dropdownMenu = document.querySelector(".dropdown-menu");
            dropdownMenu.style.display = dropdownMenu.style.display === "block" ? "none" : "block";
        }

        // Optional: Close the dropdown if clicked outside
        document.addEventListener("click", function(event) {
            const isClickInside = document.querySelector(".burger-menu").contains(event.target);
            const dropdownMenu = document.querySelector(".dropdown-menu");

            if (!isClickInside && dropdownMenu.style.display === "block") {
                dropdownMenu.style.display = "none";
            }
        });

    </script>
</head>
<body>
    <div class="navbar">
        <a href="userDashboard.php">
            <h1>SwiftShop</h1>
        </a>
        <div class="burger-menu" onclick="toggleDropdown()">
            <i class="fas fa-bars"></i>
            <div class="dropdown-menu">
                <a href="Userprofile.php">Profile</a>
                <a href="cart.php">My Cart</a>
                <a href="wishlist.php">Wishlist</a>
                <a href="userPurchase.php">My Purchase</a>
                <a href="userLogout.php">Logout</a>
            </div>
        </div>
    </div>
    </div>
    <div class="container">
        <h1><?php echo htmlspecialchars($user['fname']); ?>'s Profile</h1>


        <?php if (isset($successMessage)): ?>
            <div class="alert alert-success"><?php echo $successMessage; ?></div>
        <?php endif; ?>

        <?php if (isset($errorMessage)): ?>
            <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <form action="UserProfile.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <div class="profile-picture" onclick="openFileDialog()">
                    <?php if ($user['profile_picture']): ?>
                        <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture">
                    <?php else: ?>
                        <span class="add-icon">+</span>
                    <?php endif; ?>
                </div>
                <input type="file" name="profile_picture" id="profile_picture" accept="image/*" style="display: none;" onchange="this.form.submit();">
            </div>

            <div class="form-group">
                <label for="fname">First Name:</label>
                <input type="text" name="fname" id="fname" value="<?php echo htmlspecialchars($user['fname']); ?>" required>
            </div>

            <div class="form-group">
                <label for="lname">Last Name:</label>
                <input type="text" name="lname" id="lname" value="<?php echo htmlspecialchars($user['lname']); ?>" required>
            </div>

            <div class="form-group">
                <label for="email_or_phone">Email or Phone:</label>
                <input type="text" name="email_or_phone" id="email_or_phone" value="<?php echo htmlspecialchars($user['email_or_phone']); ?>" required>
            </div>

            <div class="form-group">
                <label for="Address">Address:</label>
                <input type="text" name="Address" id="Address" value="<?php echo htmlspecialchars($user['Address']); ?>" required>
            </div>

            <button type="submit">Update Profile</button>
        </form>
    </div>
</body>
</html>
