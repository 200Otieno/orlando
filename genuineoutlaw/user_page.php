<?php
require_once __DIR__ . '/config2.php';
require_once __DIR__ . '/auth_functions.php';

if (!isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$current_page = 'user_page';
$recipient_page = 'admin_page';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_notification'])) {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $message = sanitizeInput($_POST['message']);
    
    $stmt = $conn->prepare("INSERT INTO notifications (sender_page, recipient_page, name, email, message) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $current_page, $recipient_page, $name, $email, $message);
    $stmt->execute();
    $stmt->close();
    
    header("Location: user_page.php?success=1");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_reply'])) {
    $notification_id = sanitizeInput($_POST['notification_id']);
    $reply = sanitizeInput($_POST['reply']);
    
    $stmt = $conn->prepare("UPDATE notifications SET reply = ?, replied_at = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt->bind_param("si", $reply, $notification_id);
    $stmt->execute();
    $stmt->close();
    
    header("Location: user_page.php?reply_success=1");
    exit();
}

$conn->query("UPDATE notifications SET is_read = TRUE WHERE recipient_page = '$current_page'");
$notifications = $conn->query("SELECT * FROM notifications WHERE recipient_page = '$current_page' ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Page - OUTLAWZ PHARMACY</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <a href="home.php" class="logo">OUTLAWZ PHARMACY</a>
        <nav>
            <a href="home.php">Dashboard</a>
            <a href="user_page.php">Notifications</a>
            <a href="home.php?logout=1">Logout</a>
        </nav>
    </header>
    
    <div class="container">
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">Notification sent successfully!</div>
        <?php elseif (isset($_GET['reply_success'])): ?>
            <div class="alert alert-success">Reply sent successfully!</div>
        <?php endif; ?>
        
        <div class="card">
            <h2>Send Notification to Admin</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="name">Your Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="email">Your Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" required></textarea>
                </div>
                <button type="submit" name="send_notification">Send Notification</button>
            </form>
        </div>
        
        <div class="card">
            <h2>Notifications from Admin</h2>
            <?php if ($notifications->num_rows > 0): ?>
                <?php while($notification = $notifications->fetch_assoc()): ?>
                    <div class="notification <?= $notification['is_read'] ? '' : 'unread' ?>">
                        <h3>From: <?= $notification['name'] ?></h3>
                        <div class="meta">
                            <span>Email: <?= $notification['email'] ?></span> | 
                            <span>Sent: <?= date('M j, Y g:i a', strtotime($notification['created_at'])) ?></span>
                        </div>
                        <p><?= nl2br($notification['message']) ?></p>
                        
                        <?php if (!empty($notification['reply'])): ?>
                            <div class="reply">
                                <strong>Admin reply:</strong>
                                <p><?= nl2br($notification['reply']) ?></p>
                                <small>Replied on: <?= date('M j, Y g:i a', strtotime($notification['replied_at'])) ?></small>
                            </div>
                        <?php else: ?>
                            <div class="reply-form">
                                <form method="POST">
                                    <input type="hidden" name="notification_id" value="<?= $notification['id'] ?>">
                                    <textarea name="reply" placeholder="Type your reply here..." required></textarea>
                                    <button type="submit" name="send_reply">Send Reply</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No notifications yet.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>