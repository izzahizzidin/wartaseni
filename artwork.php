<?php
// artwork.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once("includes/db_connect.php");

// Get the user ID from the URL
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null);

if ($user_id === null) {
    die('User ID is required');
}

$current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Function to fetch artwork details
function getArtwork($artwork_id, $conn) {
    $stmt = $conn->prepare("SELECT artworks.*, users.display_name, users.profile_picture, users.username, users.bio FROM artworks JOIN users ON artworks.user_id = users.id WHERE artworks.id = ?");
    $stmt->bind_param("i", $artwork_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Function to fetch tags for an artwork
function getTags($artwork_id, $conn) {
    $stmt = $conn->prepare("SELECT tags.tag_name FROM artwork_tags JOIN tags ON artwork_tags.tag_id = tags.id WHERE artwork_tags.artwork_id = ?");
    $stmt->bind_param("i", $artwork_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $tags = [];
    while ($row = $result->fetch_assoc()) {
        $tags[] = $row['tag_name'];
    }
    return $tags;
}

// Function to fetch like count for an artwork
function getLikeCount($artwork_id, $conn) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS like_count FROM likes WHERE artwork_id = ?");
    $stmt->bind_param("i", $artwork_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['like_count'];
}

// Function to check if the user liked the artwork
function userLiked($user_id, $artwork_id, $conn) {
    $stmt = $conn->prepare("SELECT * FROM likes WHERE user_id = ? AND artwork_id = ?");
    $stmt->bind_param("ii", $user_id, $artwork_id);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

// Function to fetch comments for an artwork
function getComments($artwork_id, $conn) {
    $stmt = $conn->prepare("SELECT comments.*, users.username FROM comments JOIN users ON comments.user_id = users.id WHERE comments.artwork_id = ?");
    $stmt->bind_param("i", $artwork_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $comments = [];
    while ($row = $result->fetch_assoc()) {
        $comments[] = $row;
    }
    return $comments;
}

// Function to add a notification
function addNotification($user_id, $actor_id, $artwork_id, $comment_id, $type, $conn) {
    $stmt = $conn->prepare("INSERT INTO user_notifications (user_id, actor_id, artwork_id, comment_id, notification_type) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiss", $user_id, $actor_id, $artwork_id, $comment_id, $type);
    $stmt->execute();
    $stmt->close();
}

// Fetch artwork id from URL
$artwork_id = $_GET['id'] ?? null;
if (!$artwork_id) {
    die("Artwork ID is required.");
}

// Fetch artwork details
$artwork = getArtwork($artwork_id, $conn);
if (!$artwork) {
    die("Artwork not found.");
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment']) && isset($_POST['comment_text']) && isset($_SESSION['user_id'])) {
    $comment_text = trim($_POST['comment_text']);
    if (!empty($comment_text)) {
        $stmt = $conn->prepare("INSERT INTO comments (user_id, artwork_id, comment) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $_SESSION['user_id'], $artwork_id, $comment_text);
        $stmt->execute();
        $comment_id = $stmt->insert_id;
        $stmt->close();
        
        // Add a notification for the comment
        addNotification($artwork['user_id'], $_SESSION['user_id'], $artwork_id, $comment_id, 'comment', $conn);
    }
}

// Handle like submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['like']) && isset($_SESSION['user_id'])) {
    if (userLiked($_SESSION['user_id'], $artwork_id, $conn)) {
        $stmt = $conn->prepare("DELETE FROM likes WHERE user_id = ? AND artwork_id = ?");
        $stmt->bind_param("ii", $_SESSION['user_id'], $artwork_id);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt = $conn->prepare("INSERT INTO likes (user_id, artwork_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $_SESSION['user_id'], $artwork_id);
        $stmt->execute();
        $stmt->close();

        // Add a notification for the like
        addNotification($artwork['user_id'], $_SESSION['user_id'], $artwork_id, null, 'like', $conn);
    }
}

// Fetch tags, like count, and comments
$tags = getTags($artwork_id, $conn);
$like_count = getLikeCount($artwork_id, $conn);
$comments = getComments($artwork_id, $conn);

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$user_liked = $is_logged_in ? userLiked($_SESSION['user_id'], $artwork_id, $conn) : false;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($artwork['title']); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
</head>

<body class="artwork-body">
    <header>
        <nav class="navbar">
            <a href="index.php" class="logo">WartaSeni</a>
            <div class="menu" id="menu">
                <?php if ($is_logged_in): ?>
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
                <?php else: ?>
                <a href="about.html"><i class="fas fa-info-circle"></i> About</a>
                <a href="help_faq.html"><i class="fas fa-question-circle"></i> Help/FAQ</a>
                <a href="signup.php"><i class="fas fa-user-plus"></i> Signup</a>
                <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
                <?php endif; ?>
            </div>
            <div class="hamburger" id="hamburger">
                <i class="fas fa-bars"></i>
            </div>
        </nav>
    </header>

    <div class="artwork-container">
        <h1><?php echo htmlspecialchars($artwork['title']); ?></h1>
        <div class="artwork-media">
            <?php
        $file_extension = pathinfo($artwork['file_path'], PATHINFO_EXTENSION);
        if (in_array(strtolower($file_extension), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            echo '<img src="' . htmlspecialchars($artwork['file_path']) . '" alt="' . htmlspecialchars($artwork['title']) . '">';
        } elseif (in_array(strtolower($file_extension), ['mp4', 'avi', 'mov', 'webm', 'ogg'])) {
            echo '<video controls>
                    <source src="' . htmlspecialchars($artwork['file_path']) . '" type="video/' . $file_extension . '">
                    Your browser does not support the video tag.
                  </video>';
        }
        ?>
        </div>
        <p><?php echo htmlspecialchars($artwork['description']); ?></p>
        <p>Tags/Keywords:
            <?php 
            if (!empty($tags)) {
            $tags_html = [];
            foreach ($tags as $tag) {
                $tags_html[] = '<a href="search.php?search=' . urlencode($tag) . '">' . htmlspecialchars($tag) . '</a>';
            }
            echo implode(", ", $tags_html);
            } else {
            echo 'No tags';
            }
        ?>
        </p>
        <p>Uploaded on: <?php echo htmlspecialchars($artwork['created_at']); ?></p>
        <p>Likes: <?php echo $like_count; ?></p>

        <?php if ($is_logged_in): ?>
        <form method="post" class="like-form">
            <div class="button-container">
                <button type="submit" name="like" class="like-button">
                    <i class="fas <?php echo $user_liked ? 'fa-thumbs-down' : 'fa-thumbs-up'; ?>"></i>
                    <?php echo $user_liked ? 'Unlike' : 'Like'; ?>
                </button>
            </div>
        </form>
        <?php endif; ?>

        <h2>Comments</h2>
        <?php if ($is_logged_in): ?>
        <form method="post" class="comment-form">
            <textarea name="comment_text" required></textarea><br>
            <div class="button-container"><button type="submit" name="comment"><i class="fas fa-comment"></i>
                    Comment</button></div>

        </form>
        <?php endif; ?>
        <ul class="comments-list">
            <?php foreach ($comments as $comment): ?>
            <li>
                <strong><?php echo htmlspecialchars($comment['username']); ?>:</strong>
                <?php echo htmlspecialchars($comment['comment']); ?>
            </li>
            <?php endforeach; ?>
        </ul>

        <h2>Artwork by <a
                href="profile.php?user_id=<?php echo $artwork['user_id']; ?>"><?php echo htmlspecialchars($artwork['display_name']); ?></a>
        </h2>
        <div class="artwork-user-info">
            <div class="artwork-user-info-img">
                <img src="<?php echo htmlspecialchars($artwork['profile_picture']); ?>"
                    alt="<?php echo htmlspecialchars($artwork['display_name']); ?>'s Profile Picture">
            </div>
            <div class="user-details">
                <p>Username: <a
                        href="profile.php?user_id=<?php echo $artwork['user_id']; ?>"><?php echo htmlspecialchars($artwork['username']); ?></a>
                </p>
                <p>Bio: <?php echo htmlspecialchars($artwork['bio']); ?></p>
                <?php if ($user_id): ?>
                <div class="button-container">
                    <a href="message.php?artist_id=<?php echo $artwork['user_id']; ?>" class="message-button"><i
                            class="fas fa-envelope"></i> Message</a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="social-share">
            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('http://wartaseni.com/artwork.php?id=' . $artwork_id); ?>&text=<?php echo urlencode($artwork['title']); ?>"
                target="_blank">
                <i class="fab fa-twitter"></i> Share on Twitter
            </a>
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('http://wartaseni.com/artwork.php?id=' . $artwork_id); ?>"
                target="_blank">
                <i class="fab fa-facebook"></i> Share on Facebook
            </a>
        </div>

        <?php if ($is_logged_in && $_SESSION['user_id'] == $artwork['user_id']): ?>
        <div class="button-container">
            <a href="update_artwork.php?id=<?php echo $artwork_id; ?>" class="edit-button"><i class="fas fa-edit"></i>
                Edit Artwork</a>
        </div>
        <?php endif; ?>

        <br><br>
        <nav class="artwork-nav">
            <?php if ($is_logged_in): ?>
            <a href="javascript:history.back()"><i class="fas fa-arrow-left"></i>
                Back</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            <?php endif; ?>
        </nav>
    </div>

    <script src="js.js"></script>
    <script type="text/javascript">
        document.addEventListener('keydown', function (e) {
            if (e.key === 'PrintScreen') {
                e.preventDefault();
                alert('Screenshots are disabled on this platform.');
            }
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                alert('Screenshots are disabled on this platform.');
            }
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                alert('Screenshots are disabled on this platform.');
            }
            if (e.metaKey && e.shiftKey && e.key === '4') {
                e.preventDefault();
                alert('Screenshots are disabled on this platform.');
            }
        });
    </script>

</body>
<footer>
    <p>WartaSeni by Nur Izzah Maimunah binti Mohammad Izzidin</p>
</footer>

</html>