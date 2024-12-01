<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// index.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once("includes/db_connect.php");

// Fetch trending tags
$trending_tags_query = "SELECT tags.id, tags.tag_name, COUNT(artwork_tags.tag_id) as tag_count 
                        FROM tags 
                        JOIN artwork_tags ON tags.id = artwork_tags.tag_id 
                        GROUP BY tags.id 
                        ORDER BY tag_count DESC 
                        LIMIT 15";
$trending_tags_result = $conn->query($trending_tags_query);
$trending_tags = $trending_tags_result->fetch_all(MYSQLI_ASSOC);

// Handle filters and sorting
$category = isset($_GET['category']) ? $_GET['category'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'created_at_desc';
$items_per_page = isset($_GET['items_per_page']) ? (int)$_GET['items_per_page'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Prepare SQL query for recent artworks with filters and sorting
$filter_query = "";
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

$total_artworks_query = "SELECT COUNT(*) FROM artworks WHERE 1=1 $filter_query";
$total_artworks_result = $conn->query($total_artworks_query);
$total_artworks = $total_artworks_result->fetch_row()[0];

$recent_artworks_query = "SELECT artworks.*, users.username,
                         (SELECT COUNT(*) FROM likes WHERE likes.artwork_id = artworks.id) AS like_count 
                          FROM artworks 
                          JOIN users ON artworks.user_id = users.id 
                          WHERE 1=1 $filter_query
                          ORDER BY $sort_order 
                          LIMIT $items_per_page OFFSET $offset";
$recent_artworks_result = $conn->query($recent_artworks_query);
$recent_artworks = $recent_artworks_result->fetch_all(MYSQLI_ASSOC);

$is_logged_in = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WartaSeni</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
</head>

<body>
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

    <main>
        <section class="trending-tags">
            <h2>Trending Tags/Keywords</h2>
            <div class="tags-list">
                <?php foreach ($trending_tags as $tag): ?>
                <a href="search.php?search=<?php echo urlencode($tag['tag_name']); ?>">
                    <?php echo htmlspecialchars($tag['tag_name']); ?>
                </a>
                <?php endforeach; ?>
            </div>
            <br>
        </section>

        <section class="filter-sort-artworks">
            <h2>Filter and Sort Artworks</h2>
            <form method="GET" class="filter-sort-form">
                <div class="filter-group">
                    <label for="category"><i class="fas fa-filter"></i> Category</label>
                    <select name="category" id="category">
                        <option value="">All Categories</option>
                        <optgroup label="Illustrations">
                            <option value="Vector Art" <?php if ($category === 'Vector Art') echo 'selected'; ?>>Vector
                                Art</option>
                            <option value="Pixel Art" <?php if ($category === 'Pixel Art') echo 'selected'; ?>>Pixel Art
                            </option>
                            <option value="Digital Painting"
                                <?php if ($category === 'Digital Painting') echo 'selected'; ?>>Digital Painting
                            </option>
                            <option value="Photo Painting"
                                <?php if ($category === 'Photo Painting') echo 'selected'; ?>>Photo Painting</option>
                            <option value="Speedpaint" <?php if ($category === 'Speedpaint') echo 'selected'; ?>>
                                Speedpaint</option>
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
                            <option value="CGI Art" <?php if ($category === 'CGI Art') echo 'selected'; ?>>CGI Art
                            </option>
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
                            <option value="Storyboard" <?php if ($category === 'Storyboard') echo 'selected'; ?>>
                                Storyboard</option>
                            <option value="Character Sheets"
                                <?php if ($category === 'Character Sheets') echo 'selected'; ?>>Character Sheets
                            </option>
                            <option value="Portraits" <?php if ($category === 'Portraits') echo 'selected'; ?>>Portraits
                            </option>
                            <option value="Backgrounds" <?php if ($category === 'Backgrounds') echo 'selected'; ?>>
                                Backgrounds</option>
                        </optgroup>
                        <optgroup label="Mixed Media">
                            <option value="Digital Collage"
                                <?php if ($category === 'Digital Collage') echo 'selected'; ?>>Digital Collage</option>
                            <option value="Integrated Art"
                                <?php if ($category === 'Integrated Art') echo 'selected'; ?>>Integrated Art</option>
                        </optgroup>
                        <optgroup label="Comics & Graphic Novels">
                            <option value="Graphic Novels"
                                <?php if ($category === 'Graphic Novels') echo 'selected'; ?>>Graphic Novels</option>
                            <option value="Comics" <?php if ($category === 'Comics') echo 'selected'; ?>>Comics</option>
                        </optgroup>
                        <optgroup label="Commissioned Artworks">
                            <option value="Commissioned Artworks"
                                <?php if ($category === 'Commissioned Artworks') echo 'selected'; ?>>Commissioned
                                Artworks</option>
                        </optgroup>
                        <optgroup label="Work in Progress (WIPs)">
                            <option value="WIPs" <?php if ($category === 'WIPs') echo 'selected'; ?>>Work in Progress
                                (WIPs)</option>
                        </optgroup>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="date_from"><i class="fas fa-calendar-alt"></i> Date From</label>
                    <input type="date" name="date_from" id="date_from"
                        value="<?php echo htmlspecialchars($date_from); ?>">
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
                        <option value="created_at_desc" <?php if ($sort === 'created_at_desc') echo 'selected'; ?>>
                            Latest Uploads</option>
                        <option value="created_at_asc" <?php if ($sort === 'created_at_asc') echo 'selected'; ?>>
                            Earliest Uploads</option>
                        <option value="title_asc" <?php if ($sort === 'title_asc') echo 'selected'; ?>>Title (A-Z)
                        </option>
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

        <section class="recent-artworks">
            <h2>Recent Artworks</h2>
            <div class="artworks-grid">
                <?php foreach ($recent_artworks as $artwork): ?>
                <div class="artwork-card">
                    <a href="artwork.php?id=<?php echo $artwork['id']; ?>">
                        <?php
                        $file_extension = strtolower(pathinfo($artwork['file_path'], PATHINFO_EXTENSION));
                        if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                            echo '<img src="' . htmlspecialchars($artwork['file_path']) . '" alt="' . htmlspecialchars($artwork['title']) . '" class="artwork-preview">';
                        } elseif (in_array($file_extension, ['mp4', 'avi', 'mov', 'webm', 'ogg'])) {
                            echo '<video controls class="artwork-preview">
                                    <source src="' . htmlspecialchars($artwork['file_path']) . '" type="video/' . $file_extension . '">
                                    Your browser does not support the video tag.
                                  </video>';
                        }
                        ?>
                        <div class="artwork-info">
                            <h3><?php echo htmlspecialchars($artwork['title']); ?></h3>
                            <p>by <?php echo htmlspecialchars($artwork['username']); ?></p>
                            <?php
                            $artwork_id = $artwork['id'];
                            $tags_query = "SELECT tags.tag_name FROM tags
                                           JOIN artwork_tags ON tags.id = artwork_tags.tag_id
                                           WHERE artwork_tags.artwork_id = $artwork_id";
                            $tags_result = $conn->query($tags_query);
                            while ($tag = $tags_result->fetch_assoc()) {
                                echo '<span class="artwork-tag">#' . htmlspecialchars($tag['tag_name']) . '</span> ';
                            }
                            ?>
                            <p><i class="fas fa-heart"></i> <?php echo $artwork['like_count']; ?></p>
                        </div>
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
        </section>
    </main>

    <script src="js.js"></script>
</body>
<footer>
    <p>WartaSeni by Nur Izzah Maimunah binti Mohammad Izzidin</p>
</footer>

</html>