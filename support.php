<?php
// support.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once("includes/db_connect.php");

$user_id = $_SESSION['user_id'];

// Fetch all contact forms and replies for the authenticated user
$contact_forms = $conn->prepare("
    SELECT cf.*, ar.reply, ar.created_at AS reply_created_at, a.username AS admin_username
    FROM contact_forms cf
    LEFT JOIN admin_replies ar ON cf.id = ar.form_id
    LEFT JOIN admin a ON ar.admin_id = a.id
    WHERE cf.user_id = ?
    ORDER BY cf.created_at DESC
");
$contact_forms->bind_param("i", $user_id);
$contact_forms->execute();
$contact_forms_result = $contact_forms->get_result()->fetch_all(MYSQLI_ASSOC);
$contact_forms->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WartaSeni - Support</title>
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
                <a href="contact_us.php"><i class="fas fa-envelope"></i> Contact Us</a>
                <a href="support.php"><i class="fas fa-life-ring"></i> Support</a>
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
    <div class="container support-container">
        <h1>Support</h1>
        <?php if (count($contact_forms_result) > 0): ?>
        <ul class="support-list">
            <?php foreach ($contact_forms_result as $form): ?>
            <li class="support-item">
                <h2>Your Message</h2>
                <p><?php echo nl2br(htmlspecialchars($form['message'])); ?></p>
                <p><strong>Status:</strong> <?php echo htmlspecialchars($form['status']); ?></p>
                <?php if ($form['reply']): ?>
                <h3>Admin Reply from <?php echo htmlspecialchars($form['admin_username']); ?></h3>
                <p><?php echo nl2br(htmlspecialchars($form['reply'])); ?></p>
                <p><em>Replied on: <?php echo htmlspecialchars($form['reply_created_at']); ?></em></p>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php else: ?>
        <p>No support requests found.</p>
        <?php endif; ?>
    </div>
    <script src="js.js"></script>
</body>

</html>