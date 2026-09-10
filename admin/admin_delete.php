<?php
require_once __DIR__ . '/includes/auth.php';
requireAdminLogin();

$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
    || isset($_GET['ajax']) 
    || isset($_POST['ajax']);

$id = isset($_POST['id']) ? (int)$_POST['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

if ($id <= 0) {
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Invalid administrator ID provided.']);
        exit();
    }
    header('Location: manage_admins.php');
    exit();
}

// Prevent self-deletion
if ($id == $_SESSION['admin_id']) {
    $msg = 'You cannot delete your own administrator account while you are logged in.';
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => $msg]);
        exit();
    }
    $_SESSION['flash_alert'] = [
        'title' => 'Action Not Allowed',
        'text'  => $msg,
        'icon'  => 'warning'
    ];
    header('Location: manage_admins.php');
    exit();
}

// Fetch admin details before deletion
$fetch = mysqli_query($conn, "SELECT name, username FROM admin WHERE id='$id' LIMIT 1");
if (!$fetch || mysqli_num_rows($fetch) === 0) {
    $msg = 'Administrator account not found or already deleted.';
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => $msg]);
        exit();
    }
    $_SESSION['flash_alert'] = [
        'title' => 'Not Found',
        'text'  => $msg,
        'icon'  => 'error'
    ];
    header('Location: manage_admins.php');
    exit();
}

$admin_info = mysqli_fetch_assoc($fetch);
$admin_name = $admin_info['name'];
$admin_user = $admin_info['username'];

$sql = "DELETE FROM admin WHERE id='$id'";

if (mysqli_query($conn, $sql)) {
    $success_msg = "Administrator '{$admin_name}' (@{$admin_user}) has been deleted successfully.";
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode([
            'status'     => 'success',
            'message'    => $success_msg,
            'admin_name' => $admin_name,
            'username'   => $admin_user,
            'id'         => $id
        ]);
        exit();
    }
    $_SESSION['flash_alert'] = [
        'title' => 'Administrator Deleted!',
        'text'  => $success_msg,
        'icon'  => 'success'
    ];
    header('Location: manage_admins.php');
    exit();
} else {
    $error_msg = 'Database error: Failed to delete administrator account.';
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => $error_msg]);
        exit();
    }
    $_SESSION['flash_alert'] = [
        'title' => 'Delete Failed',
        'text'  => $error_msg,
        'icon'  => 'error'
    ];
    header('Location: manage_admins.php');
    exit();
}
