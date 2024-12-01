<?php
// admin_login.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include connection script
require_once("includes/db_connect.php");

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["username"]) && isset($_POST["password"])) {
    $username = mysqli_real_escape_string($conn, $_POST["username"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION["admin_info"] = [
            "id" => $row["id"],
            "username" => $row["username"],
        ];

        // Redirect to admin dashboard
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $error_message = "Invalid credentials or not an admin user";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>WartaSeni - Admin Login</title>
  <link rel="stylesheet" href="style.css">
</head>

<body class="admin-login-body">
  <div class="admin-login-container">
    <h1>Admin Login</h1>
    <?php if (isset($error_message)): ?>
    <div class="admin-error-message"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" class="admin-login-form">
      <div class="form-group">
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" required>
      </div>
      <div class="form-group">
        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required>
      </div>
      <div class="form-actions">
        <input type="submit" value="Login" class="admin-login-button">
        <input type="reset" value="Reset" class="admin-reset-button">
      </div>
    </form>
  </div>
</body>

</html>