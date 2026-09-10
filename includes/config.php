<?php
// Database Configuration

$host = "localhost";
$username = "root";
$password = "";
$database = "aqua_air_cooling";

// Create Connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check Connection
if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}

// Set UTF-8 Character Set
mysqli_set_charset($conn, "utf8");

// Set Asia/Kolkata Default Timezone for exact Indian Standard Time
date_default_timezone_set('Asia/Kolkata');
?>