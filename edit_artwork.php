<?php
// edit_artwork.php
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

// Get artwork ID from URL
$artwork_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$artwork_id) {
    header("Location: admin_dashboard.php");
    exit;
}

// Fetch artwork details
$stmt = $conn->prepare("SELECT * FROM artworks WHERE id = ?");
$stmt->bind_param("i", $artwork_id);
$stmt->execute();
$result = $stmt->get_result();
$artwork = $result->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $tags = $_POST['tags'];
    $media_type = $_POST['media_type'];
    $category = $_POST['category'];

    $stmt = $conn->prepare("UPDATE artworks SET title = ?, description = ?, media_type = ?, category = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $title, $description, $media_type, $category, $artwork_id);

    if ($stmt->execute()) {
        $stmt->close();

        // Update tags
        $stmt = $conn->prepare("DELETE FROM artwork_tags WHERE artwork_id = ?");
        $stmt->bind_param("i", $artwork_id);
        $stmt->execute();
        $stmt->close();

        $tags_array = array_map('trim', explode(',', $tags));
        $tags_array = array_slice($tags_array, 0, 10); // Limit to 10 tags

        foreach ($tags_array as $tag_name) {
            if (!empty($tag_name)) {
                // Check if tag already exists
                $stmt = $conn->prepare("SELECT id FROM tags WHERE tag_name = ?");
                $stmt->bind_param("s", $tag_name);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $tag = $result->fetch_assoc();
                    $tag_id = $tag['id'];
                } else {
                    // Insert new tag
                    $stmt->close(); // Close the previous statement
                    $stmt = $conn->prepare("INSERT INTO tags (tag_name) VALUES (?)");
                    $stmt->bind_param("s", $tag_name);
                    $stmt->execute();
                    $tag_id = $stmt->insert_id;
                }
                $stmt->close(); // Close after each tag handling

                // Associate tag with artwork
                $stmt = $conn->prepare("INSERT INTO artwork_tags (artwork_id, tag_id) VALUES (?, ?)");
                $stmt->bind_param("ii", $artwork_id, $tag_id);
                $stmt->execute();
                $stmt->close(); // Close the statement after executing
            }
        }
        set_message('success', 'Artwork details updated successfully.');
    } else {
        set_message('error', 'Error: Could not save artwork details to database.');
    }

    header("Location: edit_artwork.php?id=$artwork_id");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Artwork</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
</head>

<body>
    <div class="edit-artwork-container">
        <h1 class="edit-artwork-title">Edit Artwork</h1>
        <?php display_message(); ?>
        <form method="post" class="edit-artwork-form">
            <label for="title" class="edit-artwork-label"><i class="fas fa-heading"></i> Title:</label>
            <input type="text" id="title" name="title" class="edit-artwork-input"
                value="<?php echo htmlspecialchars($artwork['title']); ?>" required>

            <label for="description" class="edit-artwork-label"><i class="fas fa-align-left"></i> Description:</label>
            <textarea id="description" name="description" class="edit-artwork-textarea"
                required><?php echo htmlspecialchars($artwork['description']); ?></textarea>

            <label for="tags" class="edit-artwork-label"><i class="fas fa-tags"></i> Tags (separated by commas, maximum 10 tags):</label>
            <input type="text" name="tags" id="tags" class="edit-artwork-input" placeholder="e.g., landscape, painting"
                value="<?php echo htmlspecialchars(implode(', ', getTags($artwork_id, $conn))); ?>">

            <label for="media_type" class="edit-artwork-label"><i class="fas fa-filter"></i> Media Type:</label>
            <select name="media_type" id="media_type" class="edit-artwork-select" required>
                <option value="image" <?php echo $artwork['media_type'] == 'image' ? 'selected' : ''; ?>>Image
                </option>
                <option value="video" <?php echo $artwork['media_type'] == 'video' ? 'selected' : ''; ?>>Video
                </option>
            </select>

            <label for="category" class="edit-artwork-label"><i class="fas fa-list"></i> Category:</label>
            <select name="category" id="category" class="edit-artwork-select" required>
                <optgroup label="Illustrations">
                    <option value="Vector Art" <?php echo $artwork['category'] == 'Vector Art' ? 'selected' : ''; ?>>
                        Vector Art</option>
                    <option value="Pixel Art" <?php echo $artwork['category'] == 'Pixel Art' ? 'selected' : ''; ?>>
                        Pixel Art</option>
                    <option value="Digital Painting"
                        <?php echo $artwork['category'] == 'Digital Painting' ? 'selected' : ''; ?>>Digital Painting
                    </option>
                    <option value="Photo Painting"
                        <?php echo $artwork['category'] == 'Photo Painting' ? 'selected' : ''; ?>>Photo Painting
                    </option>
                    <option value="Speedpaint" <?php echo $artwork['category'] == 'Speedpaint' ? 'selected' : ''; ?>>
                        Speedpaint</option>
                    <option value="Sketches" <?php echo $artwork['category'] == 'Sketches' ? 'selected' : ''; ?>>
                        Sketches</option>
                    <option value="Drawings" <?php echo $artwork['category'] == 'Drawings' ? 'selected' : ''; ?>>
                        Drawings</option>
                </optgroup>
                <optgroup label="Animation">
                    <option value="2D Animation"
                        <?php echo $artwork['category'] == '2D Animation' ? 'selected' : ''; ?>>2D Animation
                    </option>
                    <option value="3D Modeling" <?php echo $artwork['category'] == '3D Modeling' ? 'selected' : ''; ?>>
                        3D Modeling</option>
                    <option value="CGI Art" <?php echo $artwork['category'] == 'CGI Art' ? 'selected' : ''; ?>>CGI
                        Art</option>
                </optgroup>
                <optgroup label="Graphic Design">
                    <option value="Logos" <?php echo $artwork['category'] == 'Logos' ? 'selected' : ''; ?>>Logos
                    </option>
                    <option value="Typeface" <?php echo $artwork['category'] == 'Typeface' ? 'selected' : ''; ?>>
                        Typeface</option>
                    <option value="Icons" <?php echo $artwork['category'] == 'Icons' ? 'selected' : ''; ?>>Icons
                    </option>
                    <option value="Graphics" <?php echo $artwork['category'] == 'Graphics' ? 'selected' : ''; ?>>
                        Graphics</option>
                </optgroup>
                <optgroup label="Concept Art">
                    <option value="Storyboard" <?php echo $artwork['category'] == 'Storyboard' ? 'selected' : ''; ?>>
                        Storyboard</option>
                    <option value="Character Sheets"
                        <?php echo $artwork['category'] == 'Character Sheets' ? 'selected' : ''; ?>>Character Sheets
                    </option>
                    <option value="Portraits" <?php echo $artwork['category'] == 'Portraits' ? 'selected' : ''; ?>>
                        Portraits</option>
                    <option value="Backgrounds" <?php echo $artwork['category'] == 'Backgrounds' ? 'selected' : ''; ?>>
                        Backgrounds</option>
                </optgroup>
                <optgroup label="Mixed Media">
                    <option value="Digital Collage"
                        <?php echo $artwork['category'] == 'Digital Collage' ? 'selected' : ''; ?>>Digital Collage
                    </option>
                    <option value="Integrated Art"
                        <?php echo $artwork['category'] == 'Integrated Art' ? 'selected' : ''; ?>>Integrated Art
                    </option>
                </optgroup>
                <optgroup label="Comics & Graphic Novels">
                    <option value="Graphic Novels"
                        <?php echo $artwork['category'] == 'Graphic Novels' ? 'selected' : ''; ?>>Graphic Novels
                    </option>
                    <option value="Comics" <?php echo $artwork['category'] == 'Comics' ? 'selected' : ''; ?>>Comics
                    </option>
                </optgroup>
                <optgroup label="Commissioned Artworks">
                    <option value="Commissioned Artworks"
                        <?php echo $artwork['category'] == 'Commissioned Artworks' ? 'selected' : ''; ?>>
                        Commissioned Artworks</option>
                </optgroup>
                <optgroup label="Work in Progress (WIPs)">
                    <option value="WIPs" <?php echo $artwork['category'] == 'WIPs' ? 'selected' : ''; ?>>Work in
                        Progress (WIPs)</option>
                </optgroup>
            </select>
            <br>
            <button type="submit" class="edit-artwork-button"><i class="fas fa-save"></i> Update Artwork</button>
        </form>
        <a href="admin_dashboard.php" class="edit-artwork-back-link"><i class="fas fa-arrow-left"></i> Back to
            Dashboard</a>
    </div>
</body>

</html>

<?php
function getTags($artwork_id, $conn) {
    $tags = [];
    $stmt = $conn->prepare("SELECT tags.tag_name FROM tags INNER JOIN artwork_tags ON tags.id = artwork_tags.tag_id WHERE artwork_tags.artwork_id = ?");
    $stmt->bind_param("i", $artwork_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $tags[] = $row['tag_name'];
    }
    $stmt->close();
    return $tags;
}
?>