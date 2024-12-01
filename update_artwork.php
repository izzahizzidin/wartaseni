<?php
// update_artwork.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require 'includes/db_connect.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("You need to be logged in to update artwork.");
}

// Fetch artwork id from URL
$artwork_id = $_GET['id'] ?? null;
if (!$artwork_id) {
    die("Artwork ID is required.");
}

// Fetch artwork details
$stmt = $conn->prepare("SELECT * FROM artworks WHERE id = ?");
$stmt->bind_param("i", $artwork_id);
$stmt->execute();
$artwork = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$artwork) {
    die("Artwork not found.");
}

// Ensure the logged-in user is the owner of the artwork
if ($artwork['user_id'] != $_SESSION['user_id']) {
    die("You do not have permission to update this artwork.");
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update'])) {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $tags = $_POST['tags'];
        $media_type = $_POST['media_type'];
        $category = $_POST['category'];

        // Update artwork details
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
            echo "<p class='upload-success-message'><i class='fas fa-check-circle'></i> Your artwork has been updated successfully.</p>";
        } else {
            echo "<p class='upload-error-message'><i class='fas fa-times-circle'></i> Error: Could not update artwork details in database.</p>";
        }
    } elseif (isset($_POST['delete'])) {
        // Start a transaction to ensure all deletions occur together
        $conn->begin_transaction();
    
        try {
            // Delete associated tags
            $stmt = $conn->prepare("DELETE FROM artwork_tags WHERE artwork_id = ?");
            $stmt->bind_param("i", $artwork_id);
            $stmt->execute();
            $stmt->close();
    
            // Delete associated comments
            $stmt = $conn->prepare("DELETE FROM comments WHERE artwork_id = ?");
            $stmt->bind_param("i", $artwork_id);
            $stmt->execute();
            $stmt->close();
    
            // Delete associated likes
            $stmt = $conn->prepare("DELETE FROM likes WHERE artwork_id = ?");
            $stmt->bind_param("i", $artwork_id);
            $stmt->execute();
            $stmt->close();
    
            // Delete the artwork itself
            $stmt = $conn->prepare("DELETE FROM artworks WHERE id = ?");
            $stmt->bind_param("i", $artwork_id);
            $stmt->execute();
            $stmt->close();
    
            // Commit the transaction
            $conn->commit();
    
            echo "<p class='upload-success-message'><i class='fas fa-check-circle'></i> Your artwork has been deleted successfully.</p>";
        } catch (Exception $e) {
            // Rollback the transaction if any error occurs
            $conn->rollback();
            echo "<p class='upload-error-message'><i class='fas fa-times-circle'></i> Error: Could not delete artwork. " . $e->getMessage() . "</p>";
        }
    }
    
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Artwork</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
</head>

<body>
    <div class="update-page">
        <div class="update-container">
            <h1 class="update-title"><i class="fas fa-edit"></i> Update Artwork</h1>
            <form method="post" class="update-form">
                <label for="title"><i class="fas fa-heading"></i> Title:</label>
                <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($artwork['title']); ?>"
                    required>

                <label for="description"><i class="fas fa-align-left"></i> Description:</label>
                <textarea name="description" id="description" rows="5"
                    required><?php echo htmlspecialchars($artwork['description']); ?></textarea>

                <label for="tags"><i class="fas fa-tags"></i> Tags/Keywords (separated by commas, maximum 10 tags):</label>
                <input type="text" name="tags" id="tags" placeholder="e.g., landscape, painting"
                    value="<?php echo htmlspecialchars(implode(', ', getTags($artwork_id, $conn))); ?>">

                <label for="media_type"><i class="fas fa-filter"></i> Media Type:</label>
                <select name="media_type" id="media_type" required>
                    <option value="image" <?php echo $artwork['media_type'] == 'image' ? 'selected' : ''; ?>>Image
                    </option>
                    <option value="video" <?php echo $artwork['media_type'] == 'video' ? 'selected' : ''; ?>>Video
                    </option>
                </select>

                <label for="category"><i class="fas fa-list"></i> Category:</label>
                <select name="category" id="category" required>
                    <optgroup label="Illustrations">
                        <option value="Vector Art"
                            <?php echo $artwork['category'] == 'Vector Art' ? 'selected' : ''; ?>>Vector Art</option>
                        <option value="Pixel Art" <?php echo $artwork['category'] == 'Pixel Art' ? 'selected' : ''; ?>>
                            Pixel Art</option>
                        <option value="Digital Painting"
                            <?php echo $artwork['category'] == 'Digital Painting' ? 'selected' : ''; ?>>Digital Painting
                        </option>
                        <option value="Photo Painting"
                            <?php echo $artwork['category'] == 'Photo Painting' ? 'selected' : ''; ?>>Photo Painting
                        </option>
                        <option value="Speedpaint"
                            <?php echo $artwork['category'] == 'Speedpaint' ? 'selected' : ''; ?>>Speedpaint</option>
                        <option value="Sketches" <?php echo $artwork['category'] == 'Sketches' ? 'selected' : ''; ?>>
                            Sketches</option>
                        <option value="Drawings" <?php echo $artwork['category'] == 'Drawings' ? 'selected' : ''; ?>>
                            Drawings</option>
                    </optgroup>
                    <optgroup label="Animation">
                        <option value="2D Animation"
                            <?php echo $artwork['category'] == '2D Animation' ? 'selected' : ''; ?>>2D Animation
                        </option>
                        <option value="3D Modeling"
                            <?php echo $artwork['category'] == '3D Modeling' ? 'selected' : ''; ?>>3D Modeling</option>
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
                        <option value="Storyboard"
                            <?php echo $artwork['category'] == 'Storyboard' ? 'selected' : ''; ?>>Storyboard</option>
                        <option value="Character Sheets"
                            <?php echo $artwork['category'] == 'Character Sheets' ? 'selected' : ''; ?>>Character Sheets
                        </option>
                        <option value="Portraits" <?php echo $artwork['category'] == 'Portraits' ? 'selected' : ''; ?>>
                            Portraits</option>
                        <option value="Backgrounds"
                            <?php echo $artwork['category'] == 'Backgrounds' ? 'selected' : ''; ?>>Backgrounds</option>
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

                <div class="update-buttons">
                    <button type="submit" name="update" class="btn"><i class="fas fa-save"></i> Update
                        Artwork</button>
                    <button type="submit" name="delete" class="btn"
                        onclick="return confirm('Are you sure you want to delete this artwork?');"><i
                            class="fas fa-trash-alt"></i> Delete Artwork</button>
                    <a href="profile.php" class="btn"><i class="fas fa-arrow-left"></i> Back to Profile</a>
                </div>
            </form>
        </div>
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