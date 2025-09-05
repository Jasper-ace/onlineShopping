<?php
session_start(); 
include("connection.php"); 

if (!isset($_SESSION['admin_id'])) {
    header('Location: adminLogin.php'); 
    exit();
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $commentId = $_GET['id'];

    $query = "
        SELECT c.comment, c.comment_date, p.product_name, p.picture,
               CONCAT(u.fname, ' ', u.lname) AS username
        FROM comments c
        JOIN products p ON c.product_id = p.id
        JOIN users u ON c.user_id = u.user_id
        WHERE c.id = ?
    ";

    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("i", $commentId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $commentData = $result->fetch_assoc();
        } else {
            echo "<script>alert('Comment not found.'); window.location.href='notifications.php';</script>";
            exit();
        }
    } else {
        die('Query preparation failed: ' . $conn->error);
    }

    $repliesQuery = "
        SELECT r.reply, r.reply_date, CONCAT(a.fname, ' ', a.lname) AS admin_name
        FROM replies r
        JOIN admin a ON r.admin_id = a.id
        WHERE r.comment_id = ?
        ORDER BY r.reply_date DESC
    ";
    
    $repliesStmt = $conn->prepare($repliesQuery);
    if ($repliesStmt) {
        $repliesStmt->bind_param("i", $commentId);
        $repliesStmt->execute();
        $repliesResult = $repliesStmt->get_result();
    } else {
        die('Query preparation failed: ' . $conn->error);
    }
} else {
    echo "<script>alert('Invalid comment ID.'); window.location.href='notifications.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply'])) {
    $reply = trim($_POST['reply']);
    $adminId = $_SESSION['admin_id']; 

    if (!empty($reply)) {
        $replyQuery = "INSERT INTO replies (comment_id, admin_id, reply, reply_date) VALUES (?, ?, ?, NOW())";
        $replyStmt = $conn->prepare($replyQuery);
        if ($replyStmt) {
            $replyStmt->bind_param("iis", $commentId, $adminId, $reply);
            if ($replyStmt->execute()) {
                header("Location: view_comment.php?id=" . $commentId . "&success=1");
                exit();
            } else {
                echo "<script>alert('Error submitting reply.');</script>";
            }
            $replyStmt->close();
        } else {
            die('Query preparation failed: ' . $conn->error);
        }
    } else {
        echo "<script>alert('Reply cannot be empty.');</script>";
    }
}




?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Comment</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Optional: Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="bg-light">

    <div class="container my-5">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white text-center">
                <h2 class="mb-0">Comment Details</h2>
            </div>
            <div class="card-body">
                <!-- Comment Item -->
                <div class="d-flex mb-4">
                    <img src="<?php echo htmlspecialchars($commentData['picture']); ?>" alt="Product Image" class="img-fluid rounded me-3" style="width: 120px; height: auto;">
                    <div>
                        <h5 class="fw-bold">Product: <?php echo htmlspecialchars($commentData['product_name']); ?></h5>
                        <h6 class="fw-bold">Comment By: <?php echo htmlspecialchars($commentData['username']); ?></h6>
                        <p><?php echo nl2br(htmlspecialchars($commentData['comment'])); ?></p>
                        <small class="text-muted">Date: <?php echo date('Y-m-d H:i:s', strtotime($commentData['comment_date'])); ?></small>
                    </div>
                </div>

                <!-- Replies -->
                <h4 class="mb-3">Replies:</h4>
                <?php if ($repliesResult->num_rows > 0): ?>
                    <?php while ($replyData = $repliesResult->fetch_assoc()): ?>
                        <div class="card mb-3 shadow-sm">
                            <div class="card-body">
                                <h6 class="fw-bold text-primary"><?php echo htmlspecialchars($replyData['admin_name']); ?>:</h6>
                                <p><?php echo nl2br(htmlspecialchars($replyData['reply'])); ?></p>
                                <small class="text-muted">Date: <?php echo date('Y-m-d H:i:s', strtotime($replyData['reply_date'])); ?></small>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No replies yet.</p>
                <?php endif; ?>

                <!-- Reply Form -->
                <div class="card mt-4 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Reply to Comment</h5>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <textarea class="form-control" name="reply" rows="4" placeholder="Type your reply here..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-reply"></i> Submit Reply</button>
                        </form>
                    </div>
                </div>

                <!-- Back Button -->
                <div class="mt-4 text-center">
                    <a href="notifications.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left"></i> Back to Notifications</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
