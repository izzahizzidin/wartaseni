<?php
// delete_user.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once("includes/db_connect.php");

$user_id = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$user_id) {
    $_SESSION['error_message'] = "<p class='error-message'><i class='fas fa-times-circle'></i> Invalid user ID.</p>";
    header("Location: admin_dashboard.php");
    exit;
}

$conn->begin_transaction();

try {
    // Fetch user profile picture and banner before deleting the user
    $stmt = $conn->prepare("SELECT profile_picture, profile_banner FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_files = $result->fetch_assoc();
    $stmt->close();

    // Fetch all artwork ids and file paths of the user
    $stmt = $conn->prepare("SELECT id, file_path FROM artworks WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $artwork_ids = [];
    $artwork_files = [];
    while ($row = $result->fetch_assoc()) {
        $artwork_ids[] = $row['id'];
        $artwork_files[] = $row['file_path'];
    }
    $stmt->close();

    // Delete related data
    if (!empty($artwork_ids)) {
        $in_clause = implode(',', array_fill(0, count($artwork_ids), '?'));
        $types = str_repeat('i', count($artwork_ids));

        $stmt = $conn->prepare("DELETE FROM user_notifications WHERE artwork_id IN ($in_clause)");
        $stmt->bind_param($types, ...$artwork_ids);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM artwork_tags WHERE artwork_id IN ($in_clause)");
        $stmt->bind_param($types, ...$artwork_ids);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM comments WHERE artwork_id IN ($in_clause)");
        $stmt->bind_param($types, ...$artwork_ids);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM likes WHERE artwork_id IN ($in_clause)");
        $stmt->bind_param($types, ...$artwork_ids);
        $stmt->execute();
        $stmt->close();
    }

    // Delete user's artworks
    $stmt = $conn->prepare("DELETE FROM artworks WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    // Delete entries in admin_replies related to contact_forms of the user
    $stmt = $conn->prepare("DELETE ar FROM admin_replies ar JOIN contact_forms cf ON ar.form_id = cf.id WHERE cf.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    // Delete entries in contact_forms
    $stmt = $conn->prepare("DELETE FROM contact_forms WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    // Delete other user-related data
    $stmt = $conn->prepare("DELETE FROM notifications WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM follows WHERE follower_id = ? OR following_id = ?");
    $stmt->bind_param("ii", $user_id, $user_id);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM likes WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM comments WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM messages WHERE sender_id = ? OR recipient_id = ?");
    $stmt->bind_param("ii", $user_id, $user_id);
    $stmt->execute();
    $stmt->close();

    // Delete the user
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    $conn->commit();

    // Delete user files from the filesystem
    $files_to_delete = array_merge($artwork_files, array_filter([$user_files['profile_picture'], $user_files['profile_banner']]));
    foreach ($files_to_delete as $file_path) {
        $absolute_path = realpath(__DIR__ . '/../' . $file_path);
        if ($absolute_path && file_exists($absolute_path)) {
            if (!unlink($absolute_path)) {
                error_log("Error deleting file: $absolute_path");
            }
        } else {
            error_log("File not found: $absolute_path");
        }
    }

    $_SESSION['success_message'] = "<p class='success-message'><i class='fas fa-check-circle'></i> User and all related data have been deleted successfully.</p>";
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error_message'] = "<p class='error-message'><i class='fas fa-times-circle'></i> Error: Could not delete user and related data. Please try again. Details: " . $e->getMessage() . "</p>";
    error_log("Error deleting user: " . $e->getMessage());
}

header("Location: admin_dashboard.php");
exit;
?>