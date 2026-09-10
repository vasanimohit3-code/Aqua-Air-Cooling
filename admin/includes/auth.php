<?php
require_once __DIR__ . '/../../includes/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireAdminLogin()
{
    if (!isset($_SESSION['admin_id'])) {
        header('Location: admin_login.php');
        exit();
    }
}

function redirectIfLoggedIn()
{
    if (isset($_SESSION['admin_id'])) {
        header('Location: dashboard.php');
        exit();
    }
}

function getAdminName()
{
    return isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin';
}
