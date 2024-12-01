<?php
// contact_us.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once("includes/db_connect.php");

// Ensure user is authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user information
$stmt = $conn->prepare("SELECT display_name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $email);
$stmt->fetch();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message']);

    $stmt = $conn->prepare("INSERT INTO contact_forms (user_id, name, email, message) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $name, $email, $message);
    $stmt->execute();
    $stmt->close();

    header("Location: support.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WartaSeni - Contact Us</title>
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
            <div class="hamburger" id="hamburger">
                <i class="fas fa-bars"></i>
            </div>
        </nav>
    </header>
    <br>
    <div class="container contact-container">
        <h1>Contact Us</h1>
        <form action="contact_us.php" method="post" class="contact-form">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="message">Message:</label>
                <textarea id="message" name="message" required></textarea>
            </div>
            <button type="submit" class="submit-button"><i class="fas fa-paper-plane"></i> Send</button>
        </form>
        <br>
        <div class="container contact-container">
            <h1>Support Page</h1>
            <a href="support.php" class="message-button"><i class="fas fa-life-ring"></i> Click Here</a>
        </div>
    </div>
    <script src="js.js"></script>
</body>

</html>