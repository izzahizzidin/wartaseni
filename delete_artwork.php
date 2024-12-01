<?php
// delete_artwork.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once("includes/db_connect.php");

// Get artwork ID from URL
$artwork_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$artwork_id) {
    $_SESSION['error_message'] = "<p class='error-message'><i class='fas fa-times-circle'></i> Invalid artwork ID.</p>";
    header("Location: admin_dashboard.php");
    exit;
}

// Start a transaction to ensure all deletions occur together
$conn->begin_transaction();

try {
    // Get the file path of the artwork
    $stmt = $conn->prepare("SELECT file_path FROM artworks WHERE id = ?");
    $stmt->bind_param("i", $artwork_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $artwork = $result->fetch_assoc();
    $stmt->close();

    if (!$artwork) {
        throw new Exception("Artwork not found.");
    }

    $file_path = $artwork['file_path'];

    // Convert to absolute path
    $absolute_path = realpath(__DIR__ . '/../' . $file_path);

    // Log paths for debugging
    error_log("Relative path: " . __DIR__ . '/../' . $file_path);
    error_log("Absolute path: " . $absolute_path);

    // Fetch comment IDs related to the artwork
    $stmt = $conn->prepare("SELECT id FROM comments WHERE artwork_id = ?");
    $stmt->bind_param("i", $artwork_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $comment_ids = [];
    while ($row = $result->fetch_assoc()) {
        $comment_ids[] = $row['id'];
    }
    $stmt->close();

    // Delete notifications related to the artwork's comments
    if (!empty($comment_ids)) {
        $in_clause = implode(',', array_fill(0, count($comment_ids), '?'));
        $types = str_repeat('i', count($comment_ids));

        $stmt = $conn->prepare("DELETE FROM user_notifications WHERE comment_id IN ($in_clause)");
        $stmt->bind_param($types, ...$comment_ids);
        $stmt->execute();
        $stmt->close();
    }

    // Delete notifications related to the artwork itself
    $stmt = $conn->prepare("DELETE FROM user_notifications WHERE artwork_id = ?");
    $stmt->bind_param("i", $artwork_id);
    $stmt->execute();
    $stmt->close();

    // Delete artwork's likes
    $stmt = $conn->prepare("DELETE FROM likes WHERE artwork_id = ?");
    $stmt->bind_param("i", $artwork_id);
    $stmt->execute();
    $stmt->close();

    // Delete artwork's comments
    $stmt = $conn->prepare("DELETE FROM comments WHERE artwork_id = ?");
    $stmt->bind_param("i", $artwork_id);
    $stmt->execute();
    $stmt->close();

    // Delete artwork's tags
    $stmt = $conn->prepare("DELETE FROM artwork_tags WHERE artwork_id = ?");
    $stmt->bind_param("i", $artwork_id);
    $stmt->execute();
    $stmt->close();

    // Delete the artwork record
    $stmt = $conn->prepare("DELETE FROM artworks WHERE id = ?");
    $stmt->bind_param("i", $artwork_id);
    $stmt->execute();
    $stmt->close();

    // Commit the transaction
    $conn->commit();

    $_SESSION['success_message'] = "<p class='success-message'><i class='fas fa-check-circle'></i> Artwork has been deleted successfully.</p>";
    
    // Check if the file exists
    if ($absolute_path && file_exists($absolute_path)) {
        // Attempt to delete the file
        if (unlink($absolute_path)) {
            $_SESSION['success_message'] = "<p class='success-message'><i class='fas fa-check-circle'></i> Artwork has been deleted successfully.</p>";
        } else {
            error_log("Error deleting file: $absolute_path");
            $_SESSION['error_message'] = "<p class='error-message'><i class='fas fa-times-circle'></i> Error: Could not delete the file from the filesystem.</p>";
        }
    } else {
        error_log("File not found or invalid path: $absolute_path");
       // $_SESSION['error_message'] = "<p class='error-message'><i class='fas fa-times-circle'></i> Error: File not found or invalid path.</p>";
    }

} catch (Exception $e) {
    // Rollback the transaction if any error occurs
    $conn->rollback();
    $_SESSION['error_message'] = "<p class='error-message'><i class='fas fa-times-circle'></i> Error: Could not delete artwork. " . $e->getMessage() . "</p>";
}

header("Location: admin_dashboard.php");
exit;
?>
