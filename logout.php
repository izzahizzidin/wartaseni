<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>WartaSeni - Logout</title>
</head>

<body>
  <?php
  // Start session if not already started
  session_start();

  // Check if user is logged in (session exists)
  if (isset($_SESSION["user_id"])) {
    // Unset all session variables
    session_unset();

    // Destroy the session
    session_destroy();

    echo "You have been logged out successfully!";
  } else {
    echo "You are not currently logged in.";
  }

  // Redirect to index.php regardless of login status
  header("Location: index.php");
  ?>
</body>

</html>