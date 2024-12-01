<?php
// search.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once("includes/db_connect.php");

// Ensure user is authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$search_term = '';
$artworks = [];
$users = [];
$tags = [];
$results_per_page = 10;

if (isset($_GET['search'])) {
    $search_term = trim($_GET['search']);
    $like_term = '%' . $search_term . '%';

    // Determine the current page
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $results_per_page;

    // Fetch artworks
    $stmt = $conn->prepare("
        SELECT * FROM artworks
        WHERE title LIKE ? OR description LIKE ?
        ORDER BY created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->bind_param("ssii", $like_term, $like_term, $results_per_page, $offset);
    $stmt->execute();
    $artworks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Fetch total number of artworks
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total FROM artworks
        WHERE title LIKE ? OR description LIKE ?
    ");
    $stmt->bind_param("ss", $like_term, $like_term);
    $stmt->execute();
    $total_artworks = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();
    $total_artwork_pages = ceil($total_artworks / $results_per_page);

    // Fetch users
    $stmt = $conn->prepare("
        SELECT * FROM users
        WHERE display_name LIKE ? OR username LIKE ? OR bio LIKE ?
        ORDER BY created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->bind_param("sssii", $like_term, $like_term, $like_term, $results_per_page, $offset);
    $stmt->execute();
    $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Fetch total number of users
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total FROM users
        WHERE display_name LIKE ? OR username LIKE ? OR bio LIKE ?
    ");
    $stmt->bind_param("sss", $like_term, $like_term, $like_term);
    $stmt->execute();
    $total_users = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();
    $total_user_pages = ceil($total_users / $results_per_page);

    // Fetch tags
    $stmt = $conn->prepare("
        SELECT tags.tag_name, artworks.id, artworks.title, artworks.file_path
        FROM tags
        JOIN artwork_tags ON tags.id = artwork_tags.tag_id
        JOIN artworks ON artwork_tags.artwork_id = artworks.id
        WHERE tags.tag_name LIKE ?
        ORDER BY artworks.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->bind_param("sii", $like_term, $results_per_page, $offset);
    $stmt->execute();
    $tags = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Fetch total number of tags
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total FROM tags
        JOIN artwork_tags ON tags.id = artwork_tags.tag_id
        JOIN artworks ON artwork_tags.artwork_id = artworks.id
        WHERE tags.tag_name LIKE ?
    ");
    $stmt->bind_param("s", $like_term);
    $stmt->execute();
    $total_tags = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();
    $total_tag_pages = ceil($total_tags / $results_per_page);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WartaSeni - Search Results</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
</head>

<body class="search-body">
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
    <main>
        <br>
        <div class="search-container">
            <h1><i class="fas fa-search"></i> Search</h1>
            <form method="get" class="search-form">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search_term); ?>"
                    placeholder="Search for artworks, users, or tags/keywords">
                <button type="submit"><i class="fas fa-search"></i> Search</button>
            </form>

            <?php if ($search_term): ?>
            <h2>Search Results for "<?php echo htmlspecialchars($search_term); ?>"</h2>

            <h3><i class="fas fa-palette"></i> Artworks</h3>
            <?php if (count($artworks) > 0): ?>
            <ul class="search-results artworks-results">
                <?php foreach ($artworks as $artwork): ?>
                <li>
                    <a href="artwork.php?id=<?php echo $artwork['id']; ?>">
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
                        <span><?php echo htmlspecialchars($artwork['title']); ?></span>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_artwork_pages; $i++): ?>
                <a href="?search=<?php echo urlencode($search_term); ?>&page=<?php echo $i; ?>"
                    class="<?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
            </div>
            <?php else: ?>
            <p>No artworks found.</p>
            <?php endif; ?>
            <br>
            <h3><i class="fas fa-user"></i> Users</h3>
            <?php if (count($users) > 0): ?>
            <ul class="search-results users-results">
                <?php foreach ($users as $user): ?>
                <li>
                    <a href="profile.php?user_id=<?php echo $user['id']; ?>">
                        <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>"
                            alt="<?php echo htmlspecialchars($user['display_name']); ?>'s Profile Picture">
                        <span><?php echo htmlspecialchars($user['display_name']); ?>
                            (@<?php echo htmlspecialchars($user['username']); ?>)</span>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_user_pages; $i++): ?>
                <a href="?search=<?php echo urlencode($search_term); ?>&page=<?php echo $i; ?>"
                    class="<?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
            </div>
            <?php else: ?>
            <p>No users found.</p>
            <?php endif; ?>
            <br>
            <h3><i class="fas fa-tags"></i> Tags/Keywords</h3>
            <?php if (count($tags) > 0): ?>
            <ul class="search-results tags-results">
                <?php foreach ($tags as $tag): ?>
                <li>
                    <a href="artwork.php?id=<?php echo $tag['id']; ?>">
                        <?php
                        $file_extension = pathinfo($tag['file_path'], PATHINFO_EXTENSION);
                        if (in_array(strtolower($file_extension), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                            echo '<img src="' . htmlspecialchars($tag['file_path']) . '" alt="' . htmlspecialchars($tag['title']) . '">';
                        } elseif (in_array(strtolower($file_extension), ['mp4', 'avi', 'mov', 'webm', 'ogg'])) {
                            echo '<video controls>
                                    <source src="' . htmlspecialchars($tag['file_path']) . '" type="video/' . $file_extension . '">
                                    Your browser does not support the video tag.
                                  </video>';
                        }
                        ?>
                        <span>#<?php echo htmlspecialchars($tag['tag_name']); ?> -
                            <?php echo htmlspecialchars($tag['title']); ?></span>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_tag_pages; $i++): ?>
                <a href="?search=<?php echo urlencode($search_term); ?>&page=<?php echo $i; ?>"
                    class="<?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
            </div>
            <?php else: ?>
            <p>No tags found.</p>
            <?php endif; ?>

            <?php endif; ?>
        </div>
        <br>
    </main>
    <script src="js.js"></script>
</body>
<footer>
    <p>WartaSeni by Nur Izzah Maimunah binti Mohammad Izzidin</p>
</footer>

</html>