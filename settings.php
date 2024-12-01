<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
$user = $user_result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_settings'])) {
        $notifications = isset($_POST['notifications']) ? 1 : 0;

        $update_query = $conn->prepare("UPDATE users SET notifications = ? WHERE id = ?");
        $update_query->bind_param("ii", $notifications, $user_id);

        if ($update_query->execute()) {
            $message = "Settings updated successfully!";
        } else {
            $error = "Failed to update settings. Please try again.";
        }

        $update_query->close();
    } elseif (isset($_POST['delete_account'])) {
        $delete_query = $conn->prepare("DELETE FROM users WHERE id = ?");
        $delete_query->bind_param("i", $user_id);

        if ($delete_query->execute()) {
            session_destroy();
            header('Location: goodbye.php'); // Redirect to a goodbye page after account deletion
            exit;
        } else {
            $error = "Failed to delete account. Please try again.";
        }

        $delete_query->close();
    }
}

$user_query->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
</head>

<body>
    <div class="settings-container">
        <h1>Account Settings</h1>
        <?php if (isset($message)): ?>
        <div class="settings-success"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
        <div class="settings-error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form action="settings.php" method="post">
            <div class="settings-form-group">
                <input type="checkbox" id="notifications" name="notifications"
                    <?php echo $user['notifications'] ? 'checked' : ''; ?>>
                <label for="notifications"><i class="fas fa-bell"></i> Enable Notifications</label>
            </div>
            <button type="submit" name="update_settings" class="settings-btn"><i class="fas fa-save"></i> Save
                Changes</button>
        </form>
        <form action="settings.php" method="post"
            onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">
            <button type="submit" name="delete_account" class="btn settings-delete-btn"><i class="fas fa-trash-alt"></i>
                Delete
                Account</button>
        </form>
        <a href="index.php" class="settings-btn"><i class="fas fa-arrow-left"></i> Back to Home</a>
    </div>
</body>

</html>