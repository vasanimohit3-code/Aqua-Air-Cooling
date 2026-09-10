<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../includes/mail_functions.php';
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
$result = mysqli_query($conn, "SELECT * FROM bookings WHERE id='$id'");

if (!$result || mysqli_num_rows($result) === 0) {
    sweetAlertAndRedirect('Booking Not Found', 'The requested booking could not be found.', 'error', 'bookings.php');
}

$row = mysqli_fetch_assoc($result);

if ($row['status'] === 'Rejected') {
    sweetAlertAndRedirect('Already Rejected', 'This booking has already been rejected.', 'info', 'bookings.php');
}

$update = mysqli_query($conn, "UPDATE bookings SET status='Rejected' WHERE id='$id'");

if ($update) {
    sendRejectMail(
        $row['email'],
        $row['first_name'] . ' ' . $row['last_name'],
        $row['id'],
        $row['service_type'],
        $row['company_type'],
        $row['original_part'] ?? 'None (Service Only)'
    );

    sweetAlertAndRedirect('Booking Rejected', 'Booking #' . $id . ' has been rejected successfully.', 'success', 'bookings.php');
}

sweetAlertAndRedirect('Action Failed', 'Something went wrong while rejecting the booking. Please try again.', 'error', 'bookings.php');
