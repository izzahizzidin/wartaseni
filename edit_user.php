<?php
// edit_user.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once("includes/db_connect.php");

// Function to display messages
function display_message() {
    if (isset($_SESSION['message'])) {
        echo $_SESSION['message'];
        unset($_SESSION['message']);
    }
}

// Function to set messages
function set_message($type, $message) {
    $_SESSION['message'] = "<p class='$type-message'><i class='fas fa-".($type === 'success' ? 'check-circle' : 'times-circle')."'></i> $message</p>";
}

// Get user ID from URL
$user_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$user_id) {
    header("Location: admin_dashboard.php");
    exit;
}

// Fetch user details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $display_name = $_POST['display_name'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $bio = $_POST['bio'];

    $stmt = $conn->prepare("UPDATE users SET display_name = ?, username = ?, email = ?, bio = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $display_name, $username, $email, $bio, $user_id);

    if ($stmt->execute()) {
        set_message('success', 'User details updated successfully.');
    } else {
        set_message('error', 'Error: Could not save user details to database.');
    }
    $stmt->close();

    header("Location: edit_user.php?id=$user_id");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
</head>

<body>
    <div class="edit-container">
        <h1 class="edit-title">Edit User</h1>
        <?php display_message(); ?>
        <form method="post" class="edit-form">
            <label for="display_name" class="edit-label">Display Name:</label>
            <input type="text" id="display_name" name="display_name" class="edit-input"
                value="<?php echo htmlspecialchars($user['display_name']); ?>" required>

            <label for="username" class="edit-label">Username:</label>
            <input type="text" id="username" name="username" class="edit-input"
                value="<?php echo htmlspecialchars($user['username']); ?>" required>

            <label for="email" class="edit-label">Email:</label>
            <input type="email" id="email" name="email" class="edit-input"
                value="<?php echo htmlspecialchars($user['email']); ?>" required>

            <label for="bio" class="edit-label">Bio:</label>
            <textarea id="bio" name="bio" class="edit-textarea"><?php echo htmlspecialchars($user['bio']); ?></textarea>

            <button type="submit" class="edit-button"><i class="fas fa-save"></i> Update User</button>
        </form>
        <a href="admin_dashboard.php" class="edit-back-link"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>
</body>

</html>