<?php
require_once __DIR__ . '/includes/auth.php';

if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit();
}

header('Location: admin_login.php');
exit();
