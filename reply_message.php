<?php
// reply_message.php

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

// Initialize variables
$display_name = $_SESSION['display_name'] ?? null;
$username = $_SESSION['username'] ?? null;

// Fetch user details if not already in session
if (!$display_name || !$username) {
    $stmt = $conn->prepare("SELECT display_name, username FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($fetched_display_name, $fetched_username);
    
    if ($stmt->fetch()) {
        if (!$display_name) {
            $_SESSION['display_name'] = $fetched_display_name;
            $display_name = $fetched_display_name;
        }
        if (!$username) {
            $_SESSION['username'] = $fetched_username;
            $username = $fetched_username;
        }
    }
    
    $stmt->close();
}

$conversation_id = isset($_GET['conversation_id']) ? intval($_GET['conversation_id']) : 0;

if ($conversation_id <= 0) {
    echo "Invalid conversation ID.";
    exit;
}

// Fetch conversation messages
$stmt = $conn->prepare("
    SELECT m.*, u.display_name
    FROM messages m
    JOIN users u ON m.sender_id = u.id
    WHERE (m.sender_id = ? OR m.recipient_id = ?) AND (m.sender_id = ? OR m.recipient_id = ?)
    ORDER BY m.created_at ASC
");
$stmt->bind_param("iiii", $user_id, $user_id, $conversation_id, $conversation_id);
$stmt->execute();
$result = $stmt->get_result();
$messages = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reply_message = $_POST['message'];
    $recipient_id = $messages[0]['sender_id'] == $user_id ? $messages[0]['recipient_id'] : $messages[0]['sender_id'];

    // Insert reply message into the database
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, recipient_id, category, message) VALUES (?, ?, ?, ?)");
    $category = 'Reply'; // Assuming all replies fall under 'Reply' category
    $stmt->bind_param("iiss", $user_id, $recipient_id, $category, $reply_message);
    if ($stmt->execute()) {
        // Insert notification for the recipient
        $notification_message = "You have a new reply from $username.";
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $recipient_id, $notification_message);
        $stmt->execute();

        header("Location: reply_message.php?conversation_id=$conversation_id");
        exit;
    } else {
        $error_message = "Reply could not be sent. Please try again.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reply to Message</title>
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
        <h2 class="h2-messages">Conversation with <?= htmlspecialchars($messages[0]['display_name']) ?></h2>

        <?php if (isset($error_message)): ?>
        <div class="alert error"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <div class="messages-list">
            <?php foreach ($messages as $message): ?>
            <div class="message">
                <strong><?= htmlspecialchars($message['display_name']) ?>:</strong>
                <p><?= nl2br(htmlspecialchars($message['message'])) ?></p>
                <span><?= htmlspecialchars($message['created_at']) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <br>
        <form action="reply_message.php?conversation_id=<?= $conversation_id ?>" method="post">
            <div class="form-group">
                <label for="message">Reply:</label>
                <textarea name="message" id="message" rows="5" required></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="submit-button"><i class="fas fa-paper-plane"></i> Send</button>
                <button type="reset" class="submit-button"><i class="fas fa-redo"></i> Reset</button>
                <a href="message_history.php" class="edit-button"><i class="fas fa-arrow-left"></i> Return to
                    History</a>
            </div>
        </form>
    </div>
</body>

</html>