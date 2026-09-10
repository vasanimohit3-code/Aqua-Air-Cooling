<?php
// Database Configuration

// Set Asia/Kolkata Default Timezone for exact Indian Standard Time
date_default_timezone_set('Asia/Kolkata');

$http_host = $_SERVER['HTTP_HOST'] ?? '';
$is_live = (strpos($http_host, 'infinityfreeapp.com') !== false || strpos($http_host, 'free.nf') !== false || strpos($http_host, 'epizy.com') !== false);

if ($is_live) {
    // Live Server (InfinityFree) Credentials
    $host = "sql303.infinityfree.com";
    $username = "if0_42880850";
    $password = "sEcbMMCRuC";
    $database = "if0_42880850_aqua_air_coolling";

    @mysqli_report(MYSQLI_REPORT_OFF);
    $conn = mysqli_connect($host, $username, $password, $database);
    if (!$conn) {
        die("Database Connection Failed: " . mysqli_connect_error());
    }
} else {
    // Localhost XAMPP Configuration
    $host = "localhost";
    $username = "root";
    $password = "";
    $database = "aqua_air_cooling";
    $conn = mysqli_connect($host, $username, $password, $database);
    
    if (!$conn) {
        die("Database Connection Failed: " . mysqli_connect_error());
    }
}

// Set UTF-8 Character Set
mysqli_set_charset($conn, "utf8");
?>