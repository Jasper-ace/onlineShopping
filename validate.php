<?php
session_start();
include('connection.php');

$message = "";

// Ensure user came from forgot-password.php
if (!isset($_SESSION['reset_email']) || !isset($_SESSION['reset_code'])) {
    header("Location: forgot-password.php");
    exit;
}

$code = $_SESSION['reset_code'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_code = implode('', $_POST['code']); // Combine all digits

    if ($entered_code == $code) {
        // Code valid, allow to reset password
        $_SESSION['code_validated'] = true;
        header("Location: reset-password.php");
        exit;
    } else {
        $message = "Invalid code. Try again!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Validate Code</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    .otp-inputs {
        display: flex;
        justify-content: space-between;
    }
    .otp-inputs input {
        width: 3rem;
        height: 3rem;
        font-size: 1.5rem;
        text-align: center;
    }
</style>
</head>
<body class="bg-light">
<div class="container mt-5" style="max-width: 400px;">
    <div class="card shadow-sm">
        <div class="card-body text-center">
            <h4 class="card-title mb-3">Enter 6-digit Code</h4>

            <?php if (!empty($message)): ?>
                <div class="alert alert-danger"><?php echo $message; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="otp-inputs mb-3">
                    <?php for ($i=0; $i<6; $i++): ?>
                        <input type="text" name="code[]" maxlength="1" required pattern="[0-9]" oninput="moveNext(this, <?php echo $i; ?>)" autofocus>
                    <?php endfor; ?>
                </div>
                <button type="submit" class="btn btn-primary w-100">Validate Code</button>
            </form>
        </div>
    </div>
</div>

<script>
function moveNext(element, index) {
    const inputs = document.querySelectorAll('.otp-inputs input');
    if (element.value.length === 1 && index < inputs.length - 1) {
        inputs[index + 1].focus();
    }
    if (element.value.length === 0 && index > 0) {
        inputs[index - 1].focus();
    }
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
