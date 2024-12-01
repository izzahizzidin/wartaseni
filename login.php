<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// login.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include connection script
include "includes/db_connect.php";

// Debugging output
function debug_to_console($data) {
    echo "<script>console.log('Debug: " . addslashes($data) . "');</script>";
}

// Handle login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["username"]) && isset($_POST["password"])) {
  // Retrieve the POST values
  $username = mysqli_real_escape_string($conn, $_POST["username"]);
  $password = $_POST["password"];

  // Use prepared statement to prevent SQL injection
  $sql = "SELECT * FROM users WHERE username=?";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, 's', $username);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);

  if (mysqli_num_rows($result) > 0) {
      $row = mysqli_fetch_assoc($result);
      if ($password === $row['password']) { // Directly compare plaintext password
          // Login successful - Start user session
          $_SESSION["user_id"] = $row["id"];
          $_SESSION["username"] = $row["username"];
          // Redirect to homepage after successful login
          debug_to_console("Login successful, redirecting to index.php");
          header("Location: index.php");
          exit(); // Exit script after redirect
      } else {
          $error_message = "Invalid username or password";
          debug_to_console("Password verification failed");
      }
  } else {
      $error_message = "Invalid username or password";
      debug_to_console("Username not found");
  }
} else {
  debug_to_console("Form not submitted correctly");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>WartaSeni - Login</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
</head>

<body class="login-page">
  <div class="login-container">
    <h1 class="login-title">Login</h1>
    <?php if (isset($error_message)): ?>
    <div class="message-container">
      <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
    </div>
    <?php endif; ?>
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" class="login-form">
      <label for="username" class="login-label">Username:</label>
      <input type="text" name="username" id="username" required class="login-input">
      <br>
      <label for="password" class="login-label">Password:</label>
      <input type="password" name="password" id="password" required class="login-input">
      <br>
      <input type="submit" value="Login" class="login-button">
      <input type="reset" value="Reset" class="login-button">
    </form>
    <br>
    <a href="signup.php" class="login-signup-link">Don't have an account? Sign up here.</a>
    <a href="index.php" class="home-button"><i class="fas fa-home"></i> Home</a>
  </div>
</body>

</html>