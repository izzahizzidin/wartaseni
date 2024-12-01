<?php
// profile.php
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

// Fetch user details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Handle filters and sorting
$category = isset($_GET['category']) ? $_GET['category'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'created_at_desc';
$items_per_page = isset($_GET['items_per_page']) ? (int)$_GET['items_per_page'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Prepare SQL query for user's artworks with filters and sorting
$filter_query = "WHERE artworks.user_id = ?";
if ($category) {
    $filter_query .= " AND artworks.category = '" . $conn->real_escape_string($category) . "'";
}
if ($date_from) {
    $filter_query .= " AND artworks.created_at >= '" . $conn->real_escape_string($date_from) . "'";
}
if ($date_to) {
    $filter_query .= " AND artworks.created_at <= '" . $conn->real_escape_string($date_to) . "'";
}
if ($type) {
    if ($type === 'image') {
        $filter_query .= " AND LOWER(artworks.file_path) REGEXP '\\.(jpg|jpeg|png|gif|webp)$'";
    } elseif ($type === 'video') {
        $filter_query .= " AND LOWER(artworks.file_path) REGEXP '\\.(mp4|avi|mov|webm|ogg)$'";
    }
}

switch ($sort) {
    case 'title_asc':
        $sort_order = 'artworks.title ASC';
        break;
    case 'title_desc':
        $sort_order = 'artworks.title DESC';
        break;
    case 'created_at_asc':
        $sort_order = 'artworks.created_at ASC';
        break;
    case 'created_at_desc':
    default:
        $sort_order = 'artworks.created_at DESC';
        break;
}

$total_artworks_query = "SELECT COUNT(*) FROM artworks $filter_query";
$stmt = $conn->prepare($total_artworks_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$total_artworks = $result->fetch_row()[0];
$stmt->close();

$artworks_query = "SELECT artworks.*, 
    (SELECT COUNT(*) FROM likes WHERE likes.artwork_id = artworks.id) AS like_count,
    (SELECT COUNT(*) FROM comments WHERE comments.artwork_id = artworks.id) AS comment_count
    FROM artworks 
    $filter_query
    ORDER BY $sort_order 
    LIMIT $items_per_page OFFSET $offset";
$stmt = $conn->prepare($artworks_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$artworks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$is_logged_in = isset($_SESSION['user_id']);
$is_following = false;

// Check if the current user follows the profile user
if ($is_logged_in && $current_user_id !== $user_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS is_following FROM follows WHERE follower_id = ? AND following_id = ?");
    $stmt->bind_param("ii", $current_user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $is_following = $result->fetch_assoc()['is_following'] > 0;
    $stmt->close();
}

// Handle follow/unfollow action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['follow']) && $is_logged_in) {
    if ($is_following) {
        $stmt = $conn->prepare("DELETE FROM follows WHERE follower_id = ? AND following_id = ?");
        $stmt->bind_param("ii", $current_user_id, $user_id);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt = $conn->prepare("INSERT INTO follows (follower_id, following_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $current_user_id, $user_id);
        $stmt->execute();
        $stmt->close();
        
        // Notify the followed user
        $stmt = $conn->prepare("INSERT INTO user_notifications (user_id, actor_id, notification_type) VALUES (?, ?, 'follow')");
        $stmt->bind_param("ii", $user_id, $current_user_id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: profile.php?user_id=$user_id");
    exit;
}

// Function to generate menu items
function generate_menu_items($is_logged_in) {
    if ($is_logged_in) {
        return [
            ['href' => 'upload.php', 'icon' => 'fa-upload', 'text' => 'Upload'],
            ['href' => 'notifications.php', 'icon' => 'fa-bell', 'text' => 'Notifications'],
            ['href' => 'profile.php', 'icon' => 'fa-user', 'text' => 'Profile'],
            ['href' => 'message_history.php', 'icon' => 'fa-envelope', 'text' => 'Message'],
            ['href' => 'contact_us.php', 'icon' => 'fa-life-ring', 'text' => 'Contact Us'],           
            ['href' => 'logout.php', 'icon' => 'fa-sign-out-alt', 'text' => 'Logout']
        ];
    } else {
        return [
            ['href' => 'index.php', 'icon' => 'fa-home', 'text' => 'Home'],
            ['href' => 'about.html', 'icon' => 'fa-info-circle', 'text' => 'About'],
            ['href' => 'help_faq.html', 'icon' => 'fa-question-circle', 'text' => 'Help/FAQ'],
            ['href' => 'signup.php', 'icon' => 'fa-user-plus', 'text' => 'Signup'],
            ['href' => 'login.php', 'icon' => 'fa-sign-in-alt', 'text' => 'Login']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($user['display_name']); ?>'s Profile</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
</head>

<body class="profile-page">
    <header>
        <nav class="navbar">
            <a href="index.php" class="logo">WartaSeni</a>
            <div class="menu" id="menu">
                <?php
                $menu_items = generate_menu_items($is_logged_in);
                foreach ($menu_items as $item) {
                    echo '<a href="' . $item['href'] . '"><i class="fas ' . $item['icon'] . '"></i> ' . $item['text'] . '</a>';
                }
                ?>
                <?php if ($is_logged_in): ?>
                <form action="search.php" method="GET" class="search-form">
                    <input type="text" name="query" placeholder="Search">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
                <?php endif; ?>
            </div>
            <div class="hamburger" id="hamburger">
                <i class="fas fa-bars"></i>
            </div>
        </nav>
    </header>
    <div class="profile-banner"
        style="background-image: url('<?php echo htmlspecialchars($user['profile_banner']); ?>');"></div>
    <div class="profile-header">
        <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture"
            class="profile-picture">
        <h1><?php echo htmlspecialchars($user['display_name']); ?></h1>
        <p>@<?php echo htmlspecialchars($user['username']); ?></p>
        <p class="bio"><?php echo nl2br(htmlspecialchars($user['bio'])); ?></p>
        <br>
        <div class="profile-buttons">
            <a href="following.php?user_id=<?php echo $user_id; ?>" class="message-link"><i
                    class="fas fa-user-friends"></i> Following</a>
            <a href="followers.php?user_id=<?php echo $user_id; ?>" class="message-link"><i class="fas fa-users"></i>
                Followers</a>
            <?php if ($is_logged_in && $current_user_id == $user_id): ?>
            <a href="edit_profile.php" class="edit-profile-button"><i class="fas fa-edit"></i> Edit Profile</a>
            <?php elseif ($is_logged_in && $current_user_id != $user_id): ?>
            <form method="post" class="follow-form">
                <button type="submit" name="follow" class="follow-link">
                    <?php echo $is_following ? '<i class="fas fa-user-minus"></i> Unfollow' : '<i class="fas fa-user-plus"></i> Follow'; ?>
                </button>
            </form>

            <a href="message.php?artist_id=<?php echo $user_id; ?>" class="message-link"><i class="fas fa-envelope"></i>
                Message</a>
            <?php endif; ?>
        </div>
    </div>
    <br>
    <section class="artworks-filters-profile">
        <h2 class="artworks-title">Artworks</h2>
        <form method="GET" action="profile.php">
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
            <div class="filter-group">
                <label for="category"><i class="fas fa-tags"></i> Category</label>
                <select name="category" id="category">
                    <option value="">All Categories</option>
                    <optgroup label="Illustrations">
                        <option value="Vector Art" <?php if ($category === 'Vector Art') echo 'selected'; ?>>Vector Art
                        </option>
                        <option value="Pixel Art" <?php if ($category === 'Pixel Art') echo 'selected'; ?>>Pixel Art
                        </option>
                        <option value="Digital Painting"
                            <?php if ($category === 'Digital Painting') echo 'selected'; ?>>Digital Painting</option>
                        <option value="Photo Painting" <?php if ($category === 'Photo Painting') echo 'selected'; ?>>
                            Photo Painting</option>
                        <option value="Speedpaint" <?php if ($category === 'Speedpaint') echo 'selected'; ?>>Speedpaint
                        </option>
                        <option value="Sketches" <?php if ($category === 'Sketches') echo 'selected'; ?>>Sketches
                        </option>
                        <option value="Drawings" <?php if ($category === 'Drawings') echo 'selected'; ?>>Drawings
                        </option>
                    </optgroup>
                    <optgroup label="Animation">
                        <option value="2D Animation" <?php if ($category === '2D Animation') echo 'selected'; ?>>2D
                            Animation</option>
                        <option value="3D Modeling" <?php if ($category === '3D Modeling') echo 'selected'; ?>>3D
                            Modeling</option>
                        <option value="CGI Art" <?php if ($category === 'CGI Art') echo 'selected'; ?>>CGI Art</option>
                    </optgroup>
                    <optgroup label="Graphic Design">
                        <option value="Logos" <?php if ($category === 'Logos') echo 'selected'; ?>>Logos</option>
                        <option value="Typeface" <?php if ($category === 'Typeface') echo 'selected'; ?>>Typeface
                        </option>
                        <option value="Icons" <?php if ($category === 'Icons') echo 'selected'; ?>>Icons</option>
                        <option value="Graphics" <?php if ($category === 'Graphics') echo 'selected'; ?>>Graphics
                        </option>
                    </optgroup>
                    <optgroup label="Concept Art">
                        <option value="Storyboard" <?php if ($category === 'Storyboard') echo 'selected'; ?>>Storyboard
                        </option>
                        <option value="Character Sheets"
                            <?php if ($category === 'Character Sheets') echo 'selected'; ?>>Character Sheets</option>
                        <option value="Portraits" <?php if ($category === 'Portraits') echo 'selected'; ?>>Portraits
                        </option>
                        <option value="Backgrounds" <?php if ($category === 'Backgrounds') echo 'selected'; ?>>
                            Backgrounds</option>
                    </optgroup>
                    <optgroup label="Mixed Media">
                        <option value="Digital Collage" <?php if ($category === 'Digital Collage') echo 'selected'; ?>>
                            Digital Collage</option>
                        <option value="Integrated Art" <?php if ($category === 'Integrated Art') echo 'selected'; ?>>
                            Integrated Art</option>
                    </optgroup>
                    <optgroup label="Comics & Graphic Novels">
                        <option value="Graphic Novels" <?php if ($category === 'Graphic Novels') echo 'selected'; ?>>
                            Graphic Novels</option>
                        <option value="Comics" <?php if ($category === 'Comics') echo 'selected'; ?>>Comics</option>
                    </optgroup>
                    <optgroup label="Commissioned Artworks">
                        <option value="Commissioned Artworks"
                            <?php if ($category === 'Commissioned Artworks') echo 'selected'; ?>>Commissioned Artworks
                        </option>
                    </optgroup>
                    <optgroup label="Work in Progress (WIPs)">
                        <option value="WIPs" <?php if ($category === 'WIPs') echo 'selected'; ?>>Work in Progress (WIPs)
                        </option>
                    </optgroup>
                </select>
            </div>
            <div class="filter-group">
                <label for="date_from"><i class="fas fa-calendar-alt"></i> Date From</label>
                <input type="date" name="date_from" id="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
            </div>
            <div class="filter-group">
                <label for="date_to"><i class="fas fa-calendar-alt"></i> Date To</label>
                <input type="date" name="date_to" id="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
            </div>
            <div class="filter-group">
                <label for="type"><i class="fas fa-file-image"></i> Type</label>
                <select name="type" id="type">
                    <option value="">All Types</option>
                    <option value="image" <?php if ($type === 'image') echo 'selected'; ?>>Image</option>
                    <option value="video" <?php if ($type === 'video') echo 'selected'; ?>>Video</option>
                </select>
            </div>
            <div class="sort-group">
                <label for="sort"><i class="fas fa-sort"></i> Sort By</label>
                <select name="sort" id="sort">
                    <option value="created_at_desc" <?php if ($sort === 'created_at_desc') echo 'selected'; ?>>Latest
                        Uploads</option>
                    <option value="created_at_asc" <?php if ($sort === 'created_at_asc') echo 'selected'; ?>>Earliest
                        Uploads</option>
                    <option value="title_asc" <?php if ($sort === 'title_asc') echo 'selected'; ?>>Title (A-Z)</option>
                    <option value="title_desc" <?php if ($sort === 'title_desc') echo 'selected'; ?>>Title (Z-A)
                    </option>
                </select>
            </div>
            <div class="items-per-page-group">
                <label for="items_per_page"><i class="fas fa-list"></i> Items Per Page</label>
                <select name="items_per_page" id="items_per_page">
                    <option value="5" <?php if ($items_per_page === 5) echo 'selected'; ?>>5</option>
                    <option value="10" <?php if ($items_per_page === 10) echo 'selected'; ?>>10</option>
                    <option value="25" <?php if ($items_per_page === 25) echo 'selected'; ?>>25</option>
                    <option value="50" <?php if ($items_per_page === 50) echo 'selected'; ?>>50</option>
                </select>
            </div>
            <button type="submit" class="apply-filters-button"><i class="fas fa-check"></i> Apply Filters</button>
        </form>
        <br>
    </section>

    <div class="artworks">
        <?php foreach ($artworks as $artwork): ?>
        <div class="artwork">
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

                <h3>
                    <?php echo htmlspecialchars($artwork['title']); ?>
                </h3>
                <p><?php echo htmlspecialchars($artwork['description']); ?></p>
                <p><i class="fas fa-heart"></i> <?php echo $artwork['like_count']; ?> <i class="fas fa-comment"></i>
                    <?php echo $artwork['comment_count']; ?></p>
            </a>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="pagination">
        <?php
        $total_pages = ceil($total_artworks / $items_per_page);
        for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"
            class="<?php if ($i == $page) echo 'active'; ?>">
            <?php echo $i; ?>
        </a>
        <?php endfor; ?>
    </div>

    <br><br><br>
    <script src="js.js"></script>
</body>
<footer>
    <p>WartaSeni by Nur Izzah Maimunah binti Mohammad Izzidin</p>
</footer>

</html>