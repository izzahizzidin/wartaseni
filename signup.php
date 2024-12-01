<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// signup.php
require_once("includes/db_connect.php");

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $display_name = $_POST['display_name'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $bio = $_POST['bio'];
    $profile_picture = null;
    $profile_banner = null;
    $notifications = 1;

    // Check if passwords match
    if ($password !== $confirm_password) {
        $message = "<p class='error'>Passwords do not match.</p>";
    } else {
        // Handle profile picture upload
        if (isset($_FILES["profile_picture"]) && $_FILES["profile_picture"]["error"] == 0) {
            $profile_picture_dir = 'uploads/profile_pictures/';
            $profile_picture_filename = basename($_FILES["profile_picture"]["name"]);
            $profile_picture_extension = pathinfo($profile_picture_filename, PATHINFO_EXTENSION);
            $profile_picture_new_filename = uniqid() . '.' . $profile_picture_extension;
            $profile_picture_path = $profile_picture_dir . $profile_picture_new_filename;

            if (!move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $profile_picture_path)) {
                $message = "<p class='error'>Error uploading profile picture.</p>";
            } else {
                $profile_picture = $profile_picture_path;
            }
        }

        // Handle profile banner upload
        if (isset($_FILES["profile_banner"]) && $_FILES["profile_banner"]["error"] == 0) {
            $profile_banner_dir = 'uploads/profile_banners/';
            $profile_banner_filename = basename($_FILES["profile_banner"]["name"]);
            $profile_banner_extension = pathinfo($profile_banner_filename, PATHINFO_EXTENSION);
            $profile_banner_new_filename = uniqid() . '.' . $profile_banner_extension;
            $profile_banner_path = $profile_banner_dir . $profile_banner_new_filename;

            if (!move_uploaded_file($_FILES["profile_banner"]["tmp_name"], $profile_banner_path)) {
                $message = "<p class='error'>Error uploading profile banner.</p>";
            } else {
                $profile_banner = $profile_banner_path;
            }
        }

        if (empty($message)) {
            // Insert user into database
            $stmt = $conn->prepare("INSERT INTO users (email, display_name, username, password, profile_picture, profile_banner, bio, notifications) VALUES (?, ?, ?, ?, ?, ?, ?,?)");
            $stmt->bind_param("ssssssss", $email, $display_name, $username, $password, $profile_picture, $profile_banner, $bio, $notifications);

            if ($stmt->execute()) {
                $message = "<p class='success'>Signup successful. <a href='login.php'>Login here</a>.</p>";
            } else {
                $message = "<p class='error'>Error: Could not sign up. " . $stmt->error . "</p>";
            }

            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WartaSeni - Signup</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
</head>

<body class="signup-page">

    <?php if (!empty($message)): ?>
    <div class="message-container">
        <?php echo $message; ?>
    </div>
    <?php endif; ?>

    <div class="signup-container">
        <h1 class="signup-title">Signup</h1>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="signup-form"
            enctype="multipart/form-data">
            <label for="email" class="signup-label">Email:</label>
            <input type="email" name="email" id="email" class="signup-input" required>
            <br>

            <label for="display_name" class="signup-label">Display Name:</label>
            <input type="text" name="display_name" id="display_name" class="signup-input" required>
            <br>

            <label for="username" class="signup-label">Username:</label>
            <input type="text" name="username" id="username" class="signup-input" required>
            <br>

            <label for="password" class="signup-label">Password:</label>
            <input type="password" name="password" id="password" class="signup-input" required><br>
            <input type="checkbox" onclick="togglePasswordVisibility()"> Show Password
            <br>

            <label for="confirm_password" class="signup-label">Confirm Password:</label>
            <input type="password" name="confirm_password" id="confirm_password" class="signup-input" required>
            <br>

            <label for="profile_picture" class="signup-label">Profile Picture (optional):</label>
            <input type="file" name="profile_picture" id="profile_picture" class="signup-input">
            <br>

            <label for="profile_banner" class="signup-label">Profile Banner (optional):</label>
            <input type="file" name="profile_banner" id="profile_banner" class="signup-input">
            <br>

            <label for="bio" class="signup-label">Bio:</label>
            <textarea name="bio" id="bio" class="signup-textarea"></textarea>
            <br>

            <input type="checkbox" name="terms" id="terms" required>
            <label for="terms_and_conditions" class="signup-label">I agree to the <a href="terms_and_conditions.html"
                    target=”_blank”>
                    Terms & Conditions</a>.</label>
            <br>

            <input type="submit" value="Signup" class="signup-button">&nbsp;&nbsp;&nbsp;
            <input type="reset" value="Reset" class="signup-button">
        </form>
        <br>
        <a href="login.php" class="signup-login-link">Already have an account? Login here.</a>
        <a href="index.php" class="home-button"><i class="fas fa-home"></i> Home</a>
    </div>
    <script>
        /* signup.php script*/
        function togglePasswordVisibility() {
            var passwordField = document.getElementById("password");
            var confirmPasswordField = document.getElementById("confirm_password");
            if (passwordField.type === "password") {
                passwordField.type = "text";
                confirmPasswordField.type = "text";
            } else {
                passwordField.type = "password";
                confirmPasswordField.type = "password";
            }
        }
    </script>
    <script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
</body>

</html>