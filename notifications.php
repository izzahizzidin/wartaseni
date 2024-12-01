<?php
// notifications.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once("includes/db_connect.php");

// Get the currently logged-in user ID
$user_id = $_SESSION['user_id'];

// Fetch user_notifications for the logged-in user
$stmt = $conn->prepare("
    SELECT user_notifications.*, 
           users.username AS actor_username, 
           users.profile_picture AS actor_profile_picture,
           artworks.title AS artwork_title, 
           artworks.file_path AS artwork_file_path
    FROM user_notifications
    LEFT JOIN users ON user_notifications.actor_id = users.id
    LEFT JOIN artworks ON user_notifications.artwork_id = artworks.id
    WHERE user_notifications.user_id = ?
    ORDER BY user_notifications.created_at DESC
");
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
    <title>Notifications</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
</head>

<body class="notifications-body">
    <div class="notifications-container">
        <h1>Notifications</h1>
        <ul class="notifications-list">
            <?php foreach ($notifications as $notification): ?>
            <li class="notification-item">
                <?php
                    $notification_text = '';
                    $redirect_url = '';
                    $thumbnail = '';

                    switch ($notification['notification_type']) {
                        case 'like':
                            $notification_text = "{$notification['actor_username']} liked your artwork \"{$notification['artwork_title']}\"";
                            $redirect_url = "artwork.php?id={$notification['artwork_id']}";
                            $thumbnail = $notification['artwork_file_path'];
                            break;
                        case 'comment':
                            $notification_text = "{$notification['actor_username']} commented on your artwork \"{$notification['artwork_title']}\"";
                            $redirect_url = "artwork.php?id={$notification['artwork_id']}";
                            $thumbnail = $notification['artwork_file_path'];
                            break;
                        case 'follow':
                            $notification_text = "{$notification['actor_username']} started following you";
                            $redirect_url = "profile.php?user_id={$notification['actor_id']}";
                            $thumbnail = $notification['actor_profile_picture'];
                            break;
                    }
                ?>
                <a href="<?php echo $redirect_url; ?>" class="notification-link">
                    <?php if ($thumbnail): ?>
                    <img src="<?php echo htmlspecialchars($thumbnail); ?>" alt="Thumbnail"
                        class="notification-thumbnail">
                    <?php endif; ?>
                    <?php echo htmlspecialchars($notification_text); ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
        <a href="index.php" class="back-to-home"><i class="fas fa-home"></i> Back to Homepage</a>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <a href="javascript:history.back()" class="back-button">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</body>

</html>