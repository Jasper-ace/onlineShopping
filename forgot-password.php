<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // PHPMailer

include('connection.php');

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_or_phone = trim($_POST['email_or_phone']);

    // Check if the user exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email_or_phone = ?");
    $stmt->bind_param("s", $email_or_phone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Generate 6-digit code
        $code = rand(100000, 999999);

        // Store code in session
        $_SESSION['reset_email'] = $email_or_phone;
        $_SESSION['reset_code'] = $code;
        $_SESSION['code_generated_time'] = time();

        // Send email using PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'lazadaconfirmation@gmail.com';
            $mail->Password = 'rbyo zvfg bedp birq'; // use app password for Gmail
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('no-reply@yourdomain.com', 'SwiftShop');
            $mail->addAddress($email_or_phone);

            $mail->isHTML(true);  // Set email format to HTML
$mail->Subject = 'Your Password Reset Code';
$mail->Body = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Password Reset Code</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 30px auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
        }
        .header {
            font-size: 24px;
            color: #333333;
            margin-bottom: 20px;
        }
        .code {
            display: inline-block;
            font-size: 32px;
            font-weight: bold;
            color: #ffffff;
            background-color: #007bff;
            padding: 15px 25px;
            border-radius: 8px;
            letter-spacing: 5px;
            margin: 20px 0;
        }
        .info {
            font-size: 16px;
            color: #555555;
            line-height: 1.5;
        }
        .footer {
            margin-top: 30px;
            font-size: 14px;
            color: #888888;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">Password Reset Request</div>
        <div class="info">
            You requested to reset your password.<br>
            Use the following code to proceed. It is valid for 10 minutes.
        </div>
        <div class="code">' . htmlspecialchars($code) . '</div>
        <div class="info">
            If you did not request this, please ignore this email.
        </div>
        <div class="footer">
            &copy; ' . date("Y") . ' ShopWise. All rights reserved.
        </div>
    </div>
</body>
</html>
';


            $mail->send();
            header("Location: validate.php");
            exit;
        } catch (Exception $e) {
            $message = "Failed to send email. Mailer Error: {$mail->ErrorInfo}";
        }

    } else {
        $message = "No account found with this email or phone.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Forgot Password</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5" style="max-width: 400px;">
    <div class="card shadow-sm">
        <div class="card-body">
            <h4 class="card-title text-center mb-3">Forgot Password</h4>
            <?php if(!empty($message)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label for="email_or_phone" class="form-label">Email</label>
                    <input type="text" id="email_or_phone" name="email_or_phone" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Send Reset Code</button>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
