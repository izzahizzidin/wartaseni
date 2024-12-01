<?php
// message_notifications.php

// Initialize the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once("includes/db_connect.php");

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch message notifications
$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$notifications = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message Notifications</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
</head>

<body>
    <header>
        <nav class="navbar">
            <a href="index.php" class="logo">WartaSeni</a>
            <div class="menu" id="menu">
                <a href="upload.php"><i class="fas fa-upload"></i> Upload</a>
                <a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a>
                <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
                <a href="message_history.php"><i class="fas fa-envelope"></i> Messages</a>
                <a href="contact_us.php"><i class="fas fa-life-ring"></i> Contact Us</a>
                <form action="search.php" method="GET" class="search-form">
                    <input type="text" name="search" placeholder="Search">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </nav>
    </header>
    <br>
    <div class="container contact-container">
        <h2 class="h2-messages">Message Notifications</h2>
        <?php if (count($notifications) > 0): ?>
        <ul class="notifications-list">
            <?php foreach ($notifications as $notification): ?>
            <li class="notification-item">
                <a href="message_history.php?conversation_id=<?= $notification['id'] ?>">
                    <?= htmlspecialchars($notification['message']) ?>
                </a>
                <span><?= htmlspecialchars($notification['created_at']) ?></span>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php else: ?>
        <p>No message notifications found.</p>
        <?php endif; ?>
    </div>
</body>

</html>