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
    <link rel="stylesheet" href="styles.css"> 
    <style>
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            font-family: Arial, sans-serif;
            background-color: #f5f5f5; 
            margin: 0; 
        }
        
        .container {
            max-width: 800px;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
            width: 100%; 
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #2c3e50;
        }

        .comment-item {
            display: flex;
            padding: 10px;
            border-bottom: 1px solid #e0e0e0;
        }

        .comment-item img {
            width: 80px;
            height: auto; 
            margin-right: 15px;
            border-radius: 4px; 
        }

        .comment-content {
            flex: 1; 
        }

        .bold {
            font-weight: bold; 
        }

        .back-button {
            margin-top: 20px;
            text-decoration: none; 
            color: #2980b9;
            font-weight: bold; 
        }

        .back-button:hover {
            text-decoration: underline; 
        }

        .reply-form {
            margin-top: 20px; 
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 8px; 
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); 
        }

        .reply-form textarea {
            width: 97%; 
            height: 100px; 
            padding: 10px; 
            border: 1px solid #ccc; 
            border-radius: 4px; 
            resize: none; 
            font-family: 'Roboto', sans-serif; 
        }

        .reply-button {
            margin-top: 10px; 
            padding: 10px 15px; 
            background-color: #2980b9; 
            color: #fff; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
        }

        .reply-button:hover {
            background-color: #1f6691; 
        }

        .reply-item {
            border-top: 1px solid #e0e0e0; 
            padding: 10px; 
            margin-top: 10px; 
        }

        .reply-item .bold {
            color: #2980b9; 
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Comment Details</h1>
        <div class="comment-item">
            <img src="<?php echo htmlspecialchars($commentData['picture']); ?>" alt="Product Image">
            <div class="comment-content">
                <span class="bold">Product: <?php echo htmlspecialchars($commentData['product_name']); ?></span><br>
                <span class="bold">Comment By: <?php echo htmlspecialchars($commentData['username']); ?></span><br>
                <p><?php echo nl2br(htmlspecialchars($commentData['comment'])); ?></p>
                <span>Date: <?php echo date('Y-m-d H:i:s', strtotime($commentData['comment_date'])); ?></span>
            </div>
        </div>

        <h3>Replies:</h3>
        <?php if ($repliesResult->num_rows > 0): ?>
            <?php while ($replyData = $repliesResult->fetch_assoc()): ?>
                <div class="reply-item">
                    <span class="bold"><?php echo htmlspecialchars($replyData['admin_name']); ?>:</span><br>
                    <p><?php echo nl2br(htmlspecialchars($replyData['reply'])); ?></p>
                    <span>Date: <?php echo date('Y-m-d H:i:s', strtotime($replyData['reply_date'])); ?></span>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No replies yet.</p>
        <?php endif; ?>

        <!-- Move reply form here -->
        <div class="reply-form">
            <h3>Reply to Comment</h3>
            <form method="POST" action="">
                <textarea name="reply" placeholder="Type your reply here..." required></textarea>
                <button type="submit" class="reply-button">Submit Reply</button>
            </form>
        </div>
        <br>
        <a href="notifications.php" class="back-button">Back to Notifications</a>
    </div>
</body>
</html>

