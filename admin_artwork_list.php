<?php
// admin_artwork_list.php

// Initialize the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once("includes/db_connect.php");

// Ensure admin is logged in
if (!isset($_SESSION['admin_info'])) {
    header("Location: admin_login.php");
    exit;
}

// Fetch and filter artworks
$artworks = [];
$category = isset($_GET['category']) ? $_GET['category'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'created_at_desc';
$items_per_page = isset($_GET['items_per_page']) ? (int)$_GET['items_per_page'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;
$filter_query = "";

// Filter by category
if ($category) {
    $filter_query .= " AND artworks.category = '" . $conn->real_escape_string($category) . "'";
}

// Filter by date range
if ($date_from) {
    $filter_query .= " AND artworks.created_at >= '" . $conn->real_escape_string($date_from) . "'";
}
if ($date_to) {
    $filter_query .= " AND artworks.created_at <= '" . $conn->real_escape_string($date_to) . "'";
}

// Sorting logic
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
$total_stmt = $conn->prepare($total_artworks_query);

// Check if preparation succeeded
if ($total_stmt === false) {
    die('Prepare error: ' . htmlspecialchars($conn->error));
}

$total_stmt->execute();
$total_stmt->bind_result($total_artworks);
$total_stmt->fetch();
$total_stmt->close();

$artworks_query = "
    SELECT artworks.*, users.username 
    FROM artworks 
    JOIN users ON artworks.user_id = users.id 
    WHERE 1=1 $filter_query
    ORDER BY $sort_order
    LIMIT ?, ?
";
$stmt = $conn->prepare($artworks_query);

// Calculate limit values for pagination
$stmt->bind_param("ii", $offset, $items_per_page);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    $artworks = $result->fetch_all(MYSQLI_ASSOC);
}

// Handle session messages
$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="admin-dashboard-body">
    <nav class="admin-navbar">
        <h1 class="admin-h1">WartaSeni: Admin Dashboard</h1>
        <div class="menu" id="menu">
            <ul>
                <li><a href="admin_user_list.php"><i class="fas fa-users"></i> User List</a></li>
                <li><a href="admin_artwork_list.php"><i class="fas fa-paint-brush"></i> Artwork List</a></li>
                <li><a href="admin_logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Display success or error messages -->
    <?php if ($success_message): ?>
    <div class="alert success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>
    <?php if ($error_message): ?>
    <div class="alert error"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <div class="admin-container">
        <div class="list">
            <h2 class="admin-h2">Artworks</h2>
            <div class="filter-sort-artworks">
                <div class="filter-sort">
                <form method="GET" class="filter-sort-form">
                    <div class="filter-group">
                        <label for="category"><i class="fas fa-filter"></i> Category</label>
                        <select name="category" id="category">
                            <option value="">All Categories</option>
                            <optgroup label="Illustrations">
                                <option value="Vector Art" <?php if ($category === 'Vector Art') echo 'selected'; ?>>Vector Art</option>
                                <option value="Pixel Art" <?php if ($category === 'Pixel Art') echo 'selected'; ?>>Pixel Art</option>
                                <option value="Digital Painting" <?php if ($category === 'Digital Painting') echo 'selected'; ?>>Digital Painting</option>
                                <option value="Photo Painting" <?php if ($category === 'Photo Painting') echo 'selected'; ?>>Photo Painting</option>
                                <option value="Speedpaint" <?php if ($category === 'Speedpaint') echo 'selected'; ?>>Speedpaint</option>
                                <option value="Sketches" <?php if ($category === 'Sketches') echo 'selected'; ?>>Sketches</option>
                                <option value="Drawings" <?php if ($category === 'Drawings') echo 'selected'; ?>>Drawings</option>
                            </optgroup>
                            <optgroup label="Animation">
                                <option value="2D Animation" <?php if ($category === '2D Animation') echo 'selected'; ?>>2D Animation</option>
                                <option value="3D Modeling" <?php if ($category === '3D Modeling') echo 'selected'; ?>>3D Modeling</option>
                                <option value="CGI Art" <?php if ($category === 'CGI Art') echo 'selected'; ?>>CGI Art</option>
                            </optgroup>
                            <optgroup label="Graphic Design">
                                <option value="Logos" <?php if ($category === 'Logos') echo 'selected'; ?>>Logos</option>
                                <option value="Typeface" <?php if ($category === 'Typeface') echo 'selected'; ?>>Typeface</option>
                                <option value="Icons" <?php if ($category === 'Icons') echo 'selected'; ?>>Icons</option>
                                <option value="Graphics" <?php if ($category === 'Graphics') echo 'selected'; ?>>Graphics</option>
                            </optgroup>
                            <optgroup label="Concept Art">
                                <option value="Storyboard" <?php if ($category === 'Storyboard') echo 'selected'; ?>>Storyboard</option>
                                <option value="Character Sheets" <?php if ($category === 'Character Sheets') echo 'selected'; ?>>Character Sheets</option>
                                <option value="Portraits" <?php if ($category === 'Portraits') echo 'selected'; ?>>Portraits</option>
                                <option value="Backgrounds" <?php if ($category === 'Backgrounds') echo 'selected'; ?>>Backgrounds</option>
                            </optgroup>
                            <optgroup label="Mixed Media">
                                <option value="Digital Collage" <?php if ($category === 'Digital Collage') echo 'selected'; ?>>Digital Collage</option>
                                <option value="Integrated Art" <?php if ($category === 'Integrated Art') echo 'selected'; ?>>Integrated Art</option>
                            </optgroup>
                            <optgroup label="Comics & Graphic Novels">
                                <option value="Graphic Novels" <?php if ($category === 'Graphic Novels') echo 'selected'; ?>>Graphic Novels</option>
                                <option value="Comics" <?php if ($category === 'Comics') echo 'selected'; ?>>Comics</option>
                            </optgroup>
                            <optgroup label="Commissioned Artworks">
                                <option value="Commissioned Artworks" <?php if ($category === 'Commissioned Artworks') echo 'selected'; ?>>Commissioned Artworks</option>
                            </optgroup>
                            <optgroup label="Work in Progress (WIPs)">
                                <option value="WIPs" <?php if ($category === 'WIPs') echo 'selected'; ?>>Work in Progress (WIPs)</option>
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
                    <div class="sort-group">
                        <label for="sort"><i class="fas fa-sort"></i> Sort By</label>
                        <select name="sort" id="sort">
                            <option value="created_at_desc" <?php if ($sort === 'created_at_desc') echo 'selected'; ?>>Latest Uploads</option>
                            <option value="created_at_asc" <?php if ($sort === 'created_at_asc') echo 'selected'; ?>>Earliest Uploads</option>
                            <option value="title_asc" <?php if ($sort === 'title_asc') echo 'selected'; ?>>Title (A-Z)</option>
                            <option value="title_desc" <?php if ($sort === 'title_desc') echo 'selected'; ?>>Title (Z-A)</option>
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
                </div>
            </div>

            <table id="artworks-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Thumbnail</th>
                        <th>Artist</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($artworks as $artwork): ?>
                        <tr>
                            <td><?= htmlspecialchars($artwork['id']) ?></td>
                            <td><?= htmlspecialchars($artwork['title']) ?></td>
                            <td>
                                <?php if (!empty($artwork['file_path'])): ?>
                                    <img src="<?= htmlspecialchars($artwork['file_path']) ?>" alt="Thumbnail" width="50" height="auto">
                                <?php else: ?>
                                    <span>No thumbnail</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($artwork['username']) ?></td>
                            <td><?= htmlspecialchars($artwork['category']) ?></td>
                            <td><?= htmlspecialchars($artwork['description']) ?></td>
                            <td><?= htmlspecialchars($artwork['created_at']) ?></td>
                            <td class="actions">
                                <a href="edit_artwork.php?id=<?= $artwork['id'] ?>"><i class="fas fa-edit"></i></a>
                                <a href="delete_artwork.php?id=<?= $artwork['id'] ?>" onclick="return confirm('Are you sure you want to delete this artwork?')"><i class="fas fa-trash-alt"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
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

    <script>
        // Filtering and sorting functions
        document.getElementById('users-filter').addEventListener('input', function() {
            filterTable('users-table', this.value);
        });

        document.getElementById('artworks-filter').addEventListener('input', function() {
            filterTable('artworks-table', this.value);
        });

        function filterTable(tableId, filter) {
            var table, tr, td, i, j, txtValue;
            filter = filter.toUpperCase();
            table = document.getElementById(tableId);
            tr = table.getElementsByTagName("tr");

            for (i = 1; i < tr.length; i++) {
                tr[i].style.display = "none";
                td = tr[i].getElementsByTagName("td");
                for (j = 0; j < td.length; j++) {
                    if (td[j]) {
                        txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            tr[i].style.display = "";
                            break;
                        }
                    }
                }
            }
        }

        function sortTable(tableId, column, order) {
            var table, rows, switching, i, x, y, shouldSwitch, switchCount = 0;
            table = document.getElementById(tableId);
            switching = true;

            while (switching) {
                switching = false;
                rows = table.rows;

                for (i = 1; i < (rows.length - 1); i++) {
                    shouldSwitch = false;
                    x = rows[i].getElementsByTagName("TD")[column];
                    y = rows[i + 1].getElementsByTagName("TD")[column];

                    if ((order == "asc" && x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) ||
                        (order == "desc" && x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase())) {
                        shouldSwitch = true;
                        break;
                    }
                }
                if (shouldSwitch) {
                    rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                    switching = true;
                    switchCount++;
                }
            }
        }
    </script>
</body>
<footer>
    <p>WartaSeni by Nur Izzah Maimunah binti Mohammad Izzidin</p>
</footer>

</html>