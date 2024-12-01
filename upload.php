<?php
// upload.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require 'includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION["user_id"];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $tags = $_POST['tags'];
    $media_type = $_POST['media_type'];
    $category = $_POST['category'];
    $upload_dir = 'uploads/';

    // Check if file was uploaded without errors
    if (isset($_FILES["artwork"]) && $_FILES["artwork"]["error"] == 0) {
        $filename = basename($_FILES["artwork"]["name"]);
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        $filesize = $_FILES["artwork"]["size"];
        $new_filename = uniqid() . '.' . $filetype;
        $file_path = $upload_dir . $new_filename;

        // Allow certain file formats
        $allowed = array("jpg", "jpeg", "png", "gif", "webp", "mp4", "avi", "mov", "webm", "ogg");
        if (!in_array($filetype, $allowed)) {
            echo "<p class='upload-error-message'><i class='fas fa-times-circle'></i> Error: Only JPG, JPEG, PNG, GIF, WEBP, MP4, AVI, MOV, WEBM and OGG files are allowed.</p>";
            exit;
        }

        // Verify file size - 1GB maximum
        if ($filesize > 1 * 1024 * 1024 * 1024) {
            echo "<p class='upload-error-message'><i class='fas fa-times-circle'></i> Error: File size is larger than the allowed limit.</p>";
            exit;
        }

        // Check if file already exists
        if (file_exists($file_path)) {
            echo "<p class='upload-error-message'><i class='fas fa-times-circle'></i> Error: File already exists.</p>";
            exit;
        }

        // Move the uploaded file to the server
        if (move_uploaded_file($_FILES["artwork"]["tmp_name"], $file_path)) {
            // Insert artwork information into database
            $stmt = $conn->prepare("INSERT INTO artworks (user_id, title, description, file_path, media_type, category) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssss", $user_id, $title, $description, $file_path, $media_type, $category);

            if ($stmt->execute()) {
                $artwork_id = $stmt->insert_id;
                $stmt->close();

                // Process tags
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
                echo "<p class='upload-success-message'><i class='fas fa-check-circle'></i> Your artwork has been uploaded successfully.</p>";
            } else {
                echo "<p class='upload-error-message'><i class='fas fa-times-circle'></i> Error: Could not save artwork details to database.</p>";
            }
        } else {
            echo "<p class='upload-error-message'><i class='fas fa-times-circle'></i> Error: There was a problem uploading your file.</p>";
        }
    } else {
        echo "<p class='upload-error-message'><i class='fas fa-times-circle'></i> Error: " . $_FILES["artwork"]["error"] . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WartaSeni - Upload Artwork</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
</head>

<body>
    <div class="upload-page">
        <div class="upload-container">
            <h1 class="upload-title"><i class="fas fa-upload"></i> Upload Artwork</h1>
            <form method="post" enctype="multipart/form-data" class="upload-form">
                <input type="hidden" name="user_id" value="<?php echo $_SESSION["user_id"]; ?>">

                <label for="artwork"><i class="fas fa-image"></i> Artwork:</label>
                <input type="file" name="artwork" id="artwork" required>

                <label for="title"><i class="fas fa-heading"></i> Title:</label>
                <input type="text" name="title" id="title" required>

                <label for="description"><i class="fas fa-align-left"></i> Description:</label>
                <textarea name="description" id="description" rows="5" required></textarea>

                <label for="tags"><i class="fas fa-tags"></i> Tags (separated by commas, maximum 10 tags):</label>
                <input type="text" name="tags" id="tags" placeholder="e.g., landscape, painting" required>

                <label for="media_type"><i class="fas fa-filter"></i> Media Type:</label>
                <select name="media_type" id="media_type" required>
                    <option value="image">Image</option>
                    <option value="video">Video</option>
                </select>

                <label for="category"><i class="fas fa-list"></i> Category:</label>
                <select name="category" id="category" required>
                    <optgroup label="Illustrations">
                        <option value="Vector Art">Vector Art</option>
                        <option value="Pixel Art">Pixel Art</option>
                        <option value="Digital Painting">Digital Painting</option>
                        <option value="Photo Painting">Photo Painting</option>
                        <option value="Speedpaint">Speedpaint</option>
                        <option value="Sketches">Sketches</option>
                        <option value="Drawings">Drawings</option>
                    </optgroup>
                    <optgroup label="Animation">
                        <option value="2D Animation">2D Animation</option>
                        <option value="3D Modeling">3D Modeling</option>
                        <option value="CGI Art">CGI Art</option>
                    </optgroup>
                    <optgroup label="Graphic Design">
                        <option value="Logos">Logos</option>
                        <option value="Typeface">Typeface</option>
                        <option value="Icons">Icons</option>
                        <option value="Graphics">Graphics</option>
                    </optgroup>
                    <optgroup label="Concept Art">
                        <option value="Storyboard">Storyboard</option>
                        <option value="Character Sheets">Character Sheets</option>
                        <option value="Portraits">Portraits</option>
                        <option value="Backgrounds">Backgrounds</option>
                    </optgroup>
                    <optgroup label="Mixed Media">
                        <option value="Digital Collage">Digital Collage</option>
                        <option value="Integrated Art">Integrated Art</option>
                    </optgroup>
                    <optgroup label="Comics & Graphic Novels">
                        <option value="Graphic Novels">Graphic Novels</option>
                        <option value="Comics">Comics</option>
                    </optgroup>
                    <optgroup label="Commissioned Artworks">
                        <option value="Commissioned Artworks">Commissioned Artworks</option>
                    </optgroup>
                    <optgroup label="Work in Progress (WIPs)">
                        <option value="WIPs">Work in Progress (WIPs)</option>
                    </optgroup>
                </select>

                <div class="upload-buttons">
                    <button type="submit" class="btn"><i class="fas fa-upload"></i> Upload Artwork</button>
                    <button type="reset" class="btn"><i class="fas fa-redo"></i> Reset</button>
                    <a href="index.php" class="btn"><i class="fas fa-arrow-left"></i> Back to Homepage</a>
                </div>
            </form>
        </div>
    </div>
</body>
<footer>
    <p>WartaSeni by Nur Izzah Maimunah binti Mohammad Izzidin</p>
</footer>

</html>