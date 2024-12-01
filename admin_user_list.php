<?php
// admin_user_list.php

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

// Fetch and filter users
$users = [];
$users_query = "SELECT * FROM users ORDER BY id ASC";
$result = $conn->query($users_query);
if ($result) {
    $users = $result->fetch_all(MYSQLI_ASSOC);
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
            <h2 class="admin-h2">Users</h2>
            <div class="filter-sort">
                <input type="text" id="users-filter" placeholder="Filter by username...">
                <button onclick="sortTable('users-table', 0, 'asc')">Sort by ID Asc</button>
                <button onclick="sortTable('users-table', 0, 'desc')">Sort by ID Desc</button>
            </div>
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
        </div>
    </div>

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