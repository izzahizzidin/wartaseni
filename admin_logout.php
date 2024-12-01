<?php
session_start();

// Check if a session is already started
if (session_status() === PHP_SESSION_ACTIVE) {
  // Unset all session variables
  session_unset();

  // Destroy the session
  session_destroy();

  // Redirect to admin login page
  header("Location: admin_login.php");
  exit();
} else {
  // Display message if no session is active (optional)
  echo "You are not currently logged in.";
}
?>
