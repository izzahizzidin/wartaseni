<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["user_id"])) {
  // User not logged in, redirect to login page
  header("Location: login.php");
  exit(); // Exit script after redirect
}