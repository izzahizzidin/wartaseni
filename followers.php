<?php
// followers.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once("includes/db_connect.php");

// Get the user ID from the URL or session
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null);
$current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if ($user_id) {
    // Fetch the username of the user whose followers list is being viewed
    $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $viewed_user = $result->fetch_assoc();
    $viewed_user_username = $viewed_user['username'];
    $stmt->close();

    // Fetch followers
    $stmt = $conn->prepare("
        SELECT users.*, 
               EXISTS(SELECT 1 FROM follows WHERE follower_id = ? AND following_id = users.id) AS is_following_back,
               EXISTS(SELECT 1 FROM follows WHERE follower_id = ? AND following_id = users.id) AS current_user_follows
        FROM follows
        JOIN users ON follows.follower_id = users.id
        WHERE follows.following_id = ?
    ");
    $stmt->bind_param("iii", $current_user_id, $current_user_id, $user_id);
    $stmt->execute();
    $followers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $followers = [];
    $viewed_user_username = "Unknown User";
}

// Handle follow/unfollow action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['follow_id']) && $current_user_id) {
    $follow_id = $_POST['follow_id'];

    // Check if already following
    $stmt = $conn->prepare("SELECT COUNT(*) AS is_following FROM follows WHERE follower_id = ? AND following_id = ?");
    $stmt->bind_param("ii", $current_user_id, $follow_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $is_following = $result->fetch_assoc()['is_following'] > 0;
    $stmt->close();

    if ($is_following) {
        $stmt = $conn->prepare("DELETE FROM follows WHERE follower_id = ? AND following_id = ?");
        $stmt->bind_param("ii", $current_user_id, $follow_id);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt = $conn->prepare("INSERT INTO follows (follower_id, following_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $current_user_id, $follow_id);
        $stmt->execute();
        $stmt->close();

        // Notify the followed user
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, actor_id, notification_type) VALUES (?, ?, 'follow')");
        $stmt->bind_param("ii", $follow_id, $current_user_id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: followers.php?user_id=$user_id");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Followers</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
</head>

<body class="followers-body">
    <div class="followers-container">
        <h1>Followers</h1>
        <?php if (empty($followers)): ?>
        <p><?php echo htmlspecialchars($viewed_user_username); ?> has no followers yet.</p>
        <?php else: ?>
        <ul class="followers-list">
            <?php foreach ($followers as $follower): ?>
            <li class="follower-item">
                <a href="profile.php?user_id=<?php echo htmlspecialchars($follower['id']); ?>" class="follower-link">
                    <img src="<?php echo htmlspecialchars($follower['profile_picture']); ?>" alt="Profile Picture"
                        class="profile-picture-small">
                    <div class="follower-info">
                        <span class="follower-name"><?php echo htmlspecialchars($follower['display_name']); ?></span>
                        <span class="follower-username">(@<?php echo htmlspecialchars($follower['username']); ?>)</span>
                    </div>
                </a>
                <?php if ($follower['id'] != $current_user_id && $current_user_id): ?>
                <form method="post" class="follower-form">
                    <input type="hidden" name="follow_id" value="<?php echo $follower['id']; ?>">
                    <button type="submit" class="follow-button">
                        <?php echo $follower['current_user_follows'] ? 'Unfollow' : 'Follow'; ?>
                    </button>
                </form>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
        <nav class="followers-nav">
            <a href="index.php" class="nav-link"><i class="fas fa-home"></i> Home</a>
            <a href="javascript:history.back()" class="nav-link"><i class="fas fa-arrow-left"></i> Back</a>
        </nav>
    </div>
</body>

</html>