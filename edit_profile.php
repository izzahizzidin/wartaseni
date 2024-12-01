<?php
// edit_profile.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once("includes/db_connect.php");

// Fetch user data from the database
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $display_name = $_POST['display_name'];
    $username = $_POST['username'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $bio = $_POST['bio'];
    $profile_picture = $user['profile_picture'];
    $profile_banner = $user['profile_banner'];

    // Handle password update
    if (!empty($current_password) && !empty($new_password) && !empty($confirm_password)) {
        if (password_verify($current_password, $user['password'])) {
            if ($new_password === $confirm_password) {
                $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $new_password_hash, $user_id);
                $stmt->execute();
                $stmt->close();
            } else {
                echo "New passwords do not match.";
                exit;
            }
        } else {
            echo "Current password is incorrect.";
            exit;
        }
    }

    // Handle profile picture upload
    if (isset($_FILES["profile_picture"]) && $_FILES["profile_picture"]["error"] == 0) {
        $profile_picture_dir = 'uploads/profile_pictures/';
        $profile_picture_filename = basename($_FILES["profile_picture"]["name"]);
        $profile_picture_extension = pathinfo($profile_picture_filename, PATHINFO_EXTENSION);
        $profile_picture_new_filename = uniqid() . '.' . $profile_picture_extension;
        $profile_picture_path = $profile_picture_dir . $profile_picture_new_filename;

        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $profile_picture_path)) {
            $profile_picture = $profile_picture_path;
        } else {
            echo "Error uploading profile picture.";
            exit;
        }
    }

    // Handle profile banner upload
    if (isset($_FILES["profile_banner"]) && $_FILES["profile_banner"]["error"] == 0) {
        $profile_banner_dir = 'uploads/profile_banners/';
        $profile_banner_filename = basename($_FILES["profile_banner"]["name"]);
        $profile_banner_extension = pathinfo($profile_banner_filename, PATHINFO_EXTENSION);
        $profile_banner_new_filename = uniqid() . '.' . $profile_banner_extension;
        $profile_banner_path = $profile_banner_dir . $profile_banner_new_filename;

        if (move_uploaded_file($_FILES["profile_banner"]["tmp_name"], $profile_banner_path)) {
            $profile_banner = $profile_banner_path;
        } else {
            echo "Error uploading profile banner.";
            exit;
        }
    }

    // Update user information in the database
    $stmt = $conn->prepare("UPDATE users SET display_name = ?, username = ?, profile_picture = ?, profile_banner = ?, bio = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $display_name, $username, $profile_picture, $profile_banner, $bio, $user_id);

    if ($stmt->execute()) {
        echo "Profile updated successfully.";
        header("Location: profile.php?user_id=" . $user_id);
        exit;
    } else {
        echo "Error updating profile.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WartaSeni - Edit Profile</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
</head>

<body>
    <header>
        <nav class="navbar">
            <a href="index.php" class="logo">WartaSeni</a>
            <div class="menu" id="menu">
                <a href="upload.php"><i class="fas fa-upload"></i> Upload</a>
                <a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a>
                <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
                <a href="message.php"><i class="fas fa-envelope"></i> Messages</a>
                <form action="search.php" method="GET" class="search-form">
                    <input type="text" name="search" placeholder="Search">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
            <div class="hamburger" id="hamburger">
                <i class="fas fa-bars"></i>
            </div>
        </nav>
    </header>
    <div class="edit-profile-container">
        <h1>Edit Profile</h1>
        <form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
            <div class="form-group">
                <label for="display_name">Display Name:</label>
                <input type="text" name="display_name" id="display_name" value="<?php echo $user['display_name']; ?>">
            </div>

            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" value="<?php echo $user['username']; ?>">
            </div>

            <div class="form-group">
                <label for="current_password">Current Password (to change password):</label>
                <input type="password" name="current_password" id="current_password">
                <span class="password-toggle" onclick="togglePasswordVisibility('current_password')">
                    <i class="fas fa-eye-slash"></i> </span>
            </div>

            <div class="form-group">
                <label for="new_password">New Password (optional):</label>
                <input type="password" name="new_password" id="new_password">
                <span class="password-toggle" onclick="togglePasswordVisibility('new_password')">
                    <i class="fas fa-eye-slash"></i> </span>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" name="confirm_password" id="confirm_password">
                <span class="password-toggle" onclick="togglePasswordVisibility('confirm_password')">
                    <i class="fas fa-eye-slash"></i> </span>
            </div>


            <div class="form-group">
                <label for="profile_picture">Profile Picture (optional):</label>
                <input type="file" name="profile_picture" id="profile_picture">
            </div>

            <div class="form-group">
                <label for="profile_banner">Profile Banner (optional):</label>
                <input type="file" name="profile_banner" id="profile_banner">
            </div>

            <div class="form-group">
                <label for="bio">Bio:</label>
                <textarea name="bio" id="bio"><?php echo $user['bio']; ?></textarea>
            </div>

            <div class="form-buttons">
                <button type="submit" name="submit"><i class="fas fa-save"></i> Save Changes</button>
                <a href="profile.php?user_id=<?php echo $user['id']; ?>" class="cancel-button"><i
                        class="fas fa-times"></i> Cancel</a>
            </div>
        </form>
    </div>
    <script src="js.js"></script>
    <script>
        /* edit_profile.php script */
        function togglePasswordVisibility(inputId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = document.querySelector(`[onclick="togglePasswordVisibility('${inputId}')"] i`);
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                toggleIcon.classList.remove("fa-eye-slash");
                toggleIcon.classList.add("fa-eye");
            } else {
                passwordInput.type = "password";
                toggleIcon.classList.remove("fa-eye");
                toggleIcon.classList.add("fa-eye-slash");
            }
        }
    </script>
</body>
<footer>
    <p>WartaSeni by Nur Izzah Maimunah binti Mohammad Izzidin</p>
</footer>

</html>