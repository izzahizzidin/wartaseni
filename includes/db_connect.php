<?php
// db_connect.php
$conn = mysqli_connect("localhost", "root", "", "wartaseni");

if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}

?>
