<?php
// admin_dashboard.php

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

// Initialize stats array
$stats = [
    'total_users' => 0,
    'total_artworks' => 0,
    'total_likes' => 0,
    'total_comments' => 0,
];

// Fetch overall statistics
$query = "
    SELECT 
        (SELECT COUNT(*) FROM users) AS total_users,
        (SELECT COUNT(*) FROM artworks) AS total_artworks,
        (SELECT COUNT(*) FROM likes) AS total_likes,
        (SELECT COUNT(*) FROM comments) AS total_comments
";
$result = $conn->query($query);
if ($result) {
    $stats = $result->fetch_assoc();
}

// Fetch recent 10 users
$users = [];
$users_query = "SELECT * FROM users ORDER BY id DESC LIMIT 10";
$result = $conn->query($users_query);
if ($result) {
    $users = $result->fetch_all(MYSQLI_ASSOC);
}

// Fetch recent 10 artworks
$artworks = [];
$category = isset($_GET['category']) ? $_GET['category'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'created_at_desc';
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

$artworks_query = "
    SELECT artworks.*, users.username 
    FROM artworks 
    JOIN users ON artworks.user_id = users.id 
    WHERE 1=1 $filter_query
    ORDER BY $sort_order
    LIMIT 10
";
$result = $conn->query($artworks_query);
if ($result) {
    $artworks = $result->fetch_all(MYSQLI_ASSOC);
}

// Process contact form replies
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_id'], $_POST['reply'])) {
    $form_id = intval($_POST['form_id']);
    $reply = trim($_POST['reply']);
    $admin_id = $_SESSION['admin_info']['id'];

    // Check if admin ID exists in the database
    $stmt = $conn->prepare("SELECT COUNT(*) FROM admin WHERE id = ?");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $stmt->bind_result($admin_exists);
    $stmt->fetch();
    $stmt->close();

    if ($admin_exists) {
        $stmt = $conn->prepare("INSERT INTO admin_replies (form_id, admin_id, reply) VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("iis", $form_id, $admin_id, $reply);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("UPDATE contact_forms SET status = 'closed' WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $form_id);
                $stmt->execute();
                $stmt->close();
            }
        }

        $_SESSION['success_message'] = "<p class='success-message'><i class='fas fa-check-circle'></i> Reply sent successfully.</p>";
    } else {
        $_SESSION['error_message'] = "<p class='error-message'><i class='fas fa-times-circle'></i> Error: Invalid admin ID.</p>";
    }

    header("Location: admin_dashboard.php");
    exit;
}

// Fetch all open contact forms
$contact_forms_open = [];
$result_open = $conn->query("SELECT * FROM contact_forms WHERE status = 'open'");
if ($result_open) {
    $contact_forms_open = $result_open->fetch_all(MYSQLI_ASSOC);
}

// Fetch all closed contact forms with admin replies
$contact_forms_closed = [];
$sql_closed = "
    SELECT cf.*, ar.reply, ar.created_at as reply_created_at, a.username as admin_username
    FROM contact_forms cf
    LEFT JOIN admin_replies ar ON cf.id = ar.form_id
    LEFT JOIN admin a ON ar.admin_id = a.id
    WHERE cf.status = 'closed'";
$result_closed = $conn->query($sql_closed);
if ($result_closed) {
    $contact_forms_closed = $result_closed->fetch_all(MYSQLI_ASSOC);
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
        <div class="stats">
            <?php foreach ($stats as $key => $value): ?>
                <div class="stat">
                    <h2><?= htmlspecialchars($value) ?></h2>
                    <p><?= ucwords(str_replace('_', ' ', $key)) ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="charts">
            <canvas id="statsChart"></canvas>
        </div>

        <div class="list">
            <h2 class="admin-h2">Recent Users</h2>
            <table id="users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Email</th>
                        <th>Display Name</th>
                        <th>Username</th>
                        <th>Profile Picture</th>
                        <th>Profile Banner</th>
                        <th>Bio</th>
                        <th>Created At</th>
                        <th>Updated At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['id']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['display_name']) ?></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><img src="<?= htmlspecialchars($user['profile_picture']) ?>" alt="Profile Picture" width="50" height="auto"></td>
                            <td><img src="<?= htmlspecialchars($user['profile_banner']) ?>" alt="Profile Banner" width="100" height="auto"></td>
                            <td><?= htmlspecialchars($user['bio']) ?></td>
                            <td><?= htmlspecialchars($user['created_at']) ?></td>
                            <td><?= htmlspecialchars($user['updated_at']) ?></td>
                            <td class="actions">
                                <a href="edit_user.php?id=<?= $user['id'] ?>"><i class="fas fa-edit"></i></a>
                                <a href="delete_user.php?id=<?= $user['id'] ?>" onclick="return confirm('Are you sure you want to delete this user?')"><i class="fas fa-trash-alt"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button class="apply-filters-button" onclick="window.location.href='admin_user_list.php'">Show All Users</button>
        </div>

        <div class="list">
            <h2 class="admin-h2">Recent Artworks</h2>
            <table id="artworks-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Thumbnail of Artwork</th>
                        <th>Artist</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Created At</th>
                        <th>Updated At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($artworks as $artwork): ?>
                        <tr>
                            <td><?= htmlspecialchars($artwork['id']) ?></td>
                            <td><?= htmlspecialchars($artwork['title']) ?></td>
                            <td><?php if (!empty($artwork['file_path'])): ?>
                                    <img src="<?= htmlspecialchars($artwork['file_path']) ?>" alt="Thumbnail" width="50" height="auto">
                                <?php else: ?>
                                    <span>No thumbnail</span>
                                <?php endif; ?></td>
                            <td><?= htmlspecialchars($artwork['username']) ?></td>
                            <td><?= htmlspecialchars($artwork['description']) ?></td>
                            <td><?= htmlspecialchars($artwork['category']) ?></td>            
                            <td><?= htmlspecialchars($artwork['created_at']) ?></td>
                            <td><?= htmlspecialchars($artwork['updated_at']) ?></td>
                            <td class="actions">
                                <a href="edit_artwork.php?id=<?= $artwork['id'] ?>"><i class="fas fa-edit"></i></a>
                                <a href="delete_artwork.php?id=<?= $artwork['id'] ?>" onclick="return confirm('Are you sure you want to delete this artwork?')"><i class="fas fa-trash-alt"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button class="apply-filters-button" onclick="window.location.href='admin_artwork_list.php'">Show All Artworks</button>
        </div>

        <div class="contact-forms-section">
            <h2 class="admin-h2">Open Contact Forms</h2>
            <?php if (count($contact_forms_open) > 0): ?>
                <ul class="contact-forms-list">
                    <?php foreach ($contact_forms_open as $form): ?>
                        <li class="contact-form-item">
                            <h3>Message from <?= htmlspecialchars($form['name']) ?></h3>
                            <p><?= nl2br(htmlspecialchars($form['message'])) ?></p>
                            <p><em>Created on: <?= htmlspecialchars($form['created_at']); ?></em></p>
                            <form action="admin_dashboard.php" method="post" class="contact-form-reply">
                                <input type="hidden" name="form_id" value="<?= $form['id'] ?>">
                                <textarea name="reply" required></textarea>
                                <button type="submit" class="reply-button"><i class="fas fa-paper-plane"></i> Send Reply</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No open support requests.</p>
            <?php endif; ?>

            <h2 class="admin-h2">Closed Contact Forms</h2>
            <?php if (count($contact_forms_closed) > 0): ?>
                <ul class="contact-forms-list">
                    <?php foreach ($contact_forms_closed as $form): ?>
                        <li class="contact-form-item">
                            <h3>Message from <?= htmlspecialchars($form['name']) ?></h3>
                            <p><?= nl2br(htmlspecialchars($form['message'])) ?></p>
                            <p><strong>Status:</strong> Closed</p>
                            <?php if (!empty($form['reply'])): ?>
                                <h3>Admin Reply from <?= htmlspecialchars($form['admin_username']); ?></h3>
                                <p><?= nl2br(htmlspecialchars($form['reply'])); ?></p>
                                <p><em>Replied on: <?= htmlspecialchars($form['reply_created_at']); ?></em></p>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No closed support requests.</p>
            <?php endif; ?>
        </div>

    </div>
    <script>
    var ctx = document.getElementById('statsChart').getContext('2d');
    var statsChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Users', 'Artworks', 'Likes', 'Comments'],
            datasets: [{
                label: 'Statistics',
                data: [
                    <?= $stats['total_users'] ?>,
                    <?= $stats['total_artworks'] ?>,
                    <?= $stats['total_likes'] ?>,
                    <?= $stats['total_comments'] ?>
                ],
                backgroundColor: 'rgba(153, 102, 255, 0.6)',
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    </script>
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