<?php
// message.php

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

// Fetch user information
$stmt = $conn->prepare("SELECT display_name, email, username FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $email, $username);
$stmt->fetch();
$stmt->close();

// Fetch artist details based on ID passed in URL
$artist_id = isset($_GET['artist_id']) ? intval($_GET['artist_id']) : 0;
$artist = null;

if ($artist_id > 0) {
    $stmt = $conn->prepare("SELECT id, email, display_name FROM users WHERE id = ?");
    $stmt->bind_param("i", $artist_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $artist = $result->fetch_assoc();
    } else {
        echo "Artist not found in database. Please check the URL parameter.";
        exit;
    }
    $stmt->close();
} else {
    echo "Invalid artist ID.";
    exit;
}

$categories = [
    'General Inquiries',
    'Business Purposes',
    'Hiring',
    'Personal Art Commission',
    'Commercial Art Commission',
    'Others'
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_email = $email; // The logged-in user's email
    $artist_id = intval($_POST['artist_id']);
    $category = $_POST['category'];
    $message = $_POST['message'];

    // Insert message into the database
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, recipient_id, category, message) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $user_id, $artist_id, $category, $message);
    if ($stmt->execute()) {
        // Insert notification for the recipient
        $notification_message = "You have a new message from $username.";
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $artist_id, $notification_message);
        $stmt->execute();

        $success_message = "Message has been sent successfully!";
    } else {
        $error_message = "Message could not be sent. Please try again.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message Artist</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
</head>

<body class="message-artist-body">
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
            <h2>Message Artist: <?= htmlspecialchars($artist['display_name']) ?></h2>

            <?php if (isset($success_message)): ?>
            <div class="alert-success"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
            <div class="alert error"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>

            <form action="message.php?artist_id=<?= $artist['id'] ?>" method="post">
                <input type="hidden" name="artist_id" value="<?= htmlspecialchars($artist['id']) ?>">
                <input type="hidden" name="client_email" value="<?= htmlspecialchars($email) ?>">

                <div class="form-group">
                    <label for="category">Category:</label>
                    <select name="category" id="category" class="edit-artwork-select" required>
                        <?php foreach ($categories as $category): ?>
                        <option value="<?= htmlspecialchars($category) ?>"><?= htmlspecialchars($category) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="message">Message:</label>
                    <textarea name="message" id="message" rows="5" required></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="submit-button"><i class="fas fa-paper-plane"></i> Send</button>
                    <button type="reset" class="submit-button"><i class="fas fa-redo"></i> Reset</button>
                    <a href="profile.php?id=<?= $artist['id'] ?>" class="edit-button"><i class="fas fa-arrow-left"></i>
                        Return to Profile</a>
                </div>
            </form>
    </div>
</body>

</html>