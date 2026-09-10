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
    
    @mysqli_report(MYSQLI_REPORT_OFF);

    // Auto-detect candidate database name
    $possible_dbs = [
        "if0_42880850_aquaaircoolling",
        "if0_42880850_aquaaircooling",
        "if0_42880850_aqua_air_cooling"
    ];
    
    $conn = false;
    foreach ($possible_dbs as $candidate) {
        try {
            $test_conn = @mysqli_connect($host, $username, $password, $candidate);
            if ($test_conn) {
                $conn = $test_conn;
                $database = $candidate;
                break;
            }
        } catch (Throwable $e) {
            // continue trying next candidate
        }
    }
    
    if (!$conn) {
        try {
            $database = "if0_42880850_aquaaircoolling";
            $conn = @mysqli_connect($host, $username, $password, $database);
        } catch (Throwable $e) {
            $conn = false;
        }
    }
} else {
    // Localhost XAMPP Configuration
    $host = "localhost";
    $username = "root";
    $password = "";
    $database = "aqua_air_cooling";
    $conn = mysqli_connect($host, $username, $password, $database);
}

// Check Connection
if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}

// Set UTF-8 Character Set
mysqli_set_charset($conn, "utf8");
?>