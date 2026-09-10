<?php
require_once __DIR__ . '/includes/auth.php';
requireAdminLogin();

function sweetAlertAndRedirect($title, $text, $icon, $redirectUrl = 'bookings.php') {
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
    header('Location: bookings.php');
    exit();
}

$id = (int)$_GET['id'];
$sql = "DELETE FROM bookings WHERE id='$id'";

if (mysqli_query($conn, $sql)) {
    sweetAlertAndRedirect('Booking Deleted', 'Booking #' . $id . ' has been deleted successfully.', 'success', 'bookings.php');
} else {
    sweetAlertAndRedirect('Delete Failed', 'Failed to delete booking. Please try again.', 'error', 'bookings.php');
}
