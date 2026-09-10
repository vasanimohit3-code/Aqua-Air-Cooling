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

    // Connect to MySQL server first
    $conn = @mysqli_connect($host, $username, $password);
    if (!$conn) {
        die("Database Server Connection Failed: " . mysqli_connect_error());
    }

    // Auto-discover the exact database created on this account
    $database = "";
    $db_res = @mysqli_query($conn, "SHOW DATABASES");
    if ($db_res) {
        while ($row = mysqli_fetch_row($db_res)) {
            if ($row[0] !== 'information_schema' && strpos($row[0], 'if0_42880850') !== false) {
                $database = $row[0];
                break;
            }
        }
    }

    if (empty($database)) {
        $database = "if0_42880850_aquaaircoolling";
    }

    if (!@mysqli_select_db($conn, $database)) {
        die("Database Selection Failed for '$database': " . mysqli_error($conn));
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