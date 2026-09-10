<?php
require_once __DIR__ . '/includes/auth.php';
requireAdminLogin();

function sweetAlertAndRedirect($title, $text, $icon, $redirectUrl = 'users.php') {
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script><style>body{font-family:sans-serif;background:#eef4fb;}</style></head><body>';
    echo "<script>
    Swal.fire({
        title: " . json_encode($title) . ",
        text: " . json_encode($text) . ",
        icon: " . json_encode($icon) . ",
        confirmButtonColor: '#0d6efd',
        confirmButtonText: 'OK'
    }).then(() => {
        window.location = " . json_encode($redirectUrl) . ";
    });
    </script></body></html>";
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: users.php');
    exit();
}

$id = (int)$_GET['id'];
$sql = "DELETE FROM users WHERE id='$id'";

if (mysqli_query($conn, $sql)) {
    sweetAlertAndRedirect('User Deleted', 'The user account has been deleted successfully.', 'success', 'users.php');
} else {
    sweetAlertAndRedirect('Delete Failed', 'Failed to delete user account. Please try again.', 'error', 'users.php');
}
