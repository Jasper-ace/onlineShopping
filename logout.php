<?php
// adminLogout.php
session_start();
unset($_SESSION['admin_id']); // Only remove the admin session
session_destroy();
header('Location: adminLogin.php');
exit;
?>
