<?php
// Rename this file to db.php and update with your local MySQL settings
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "gpa_tracker_demo";
$port = 3307; // Change to 3307 if your MySQL runs on port 3307

$conn = new mysqli($host, $user, $pass, $dbname, $port);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
