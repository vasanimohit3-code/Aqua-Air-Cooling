<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../includes/mail_functions.php';
requireAdminLogin();

if (!isset($_GET['id'])) {
    header('Location: bookings.php');
    exit();
}

$id = (int)$_GET['id'];
$result = mysqli_query($conn, "SELECT * FROM bookings WHERE id='$id'");

function sweetAlertAndRedirect($title, $text, $icon, $redirectUrl = 'bookings.php') {
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script><style>body{font-family:sans-serif;background:#eef4fb;}</style></head><body>';
    echo "<script>
    Swal.fire({
        title: " . json_encode($title) . ",
        text: " . json_encode($text) . ",
        icon: " . json_encode($icon) . ",
        confirmButtonColor: '#28a745',
        confirmButtonText: 'OK'
    }).then(() => {
        window.location = " . json_encode($redirectUrl) . ";
    });
    </script></body></html>";
    exit();
}

if (!$result || mysqli_num_rows($result) === 0) {
    sweetAlertAndRedirect('Booking Not Found', 'The requested booking could not be found.', 'error', 'bookings.php');
}

$row = mysqli_fetch_assoc($result);

if ($row['status'] === 'Approved') {
    sweetAlertAndRedirect('Already Approved', 'This booking has already been approved.', 'info', 'bookings.php');
}

if (isset($_POST['approve'])) {
    $price = (float)mysqli_real_escape_string($conn, $_POST['price']);
    $visit_date = mysqli_real_escape_string($conn, $_POST['visit_date']);
    $visit_time = mysqli_real_escape_string($conn, $_POST['visit_time']);

    $coupon_code = $row['coupon_code'] ?? '';
    $discount_percent = floatval($row['discount_percent'] ?? 0);
    $discount_amount = floatval($row['discount_amount'] ?? 0);
    $final_price = floatval($row['final_price'] ?? $price);

    if (!empty($coupon_code) && $discount_percent > 0) {
        $discount_amount = round($price * ($discount_percent / 100), 2);
        $final_price = max(0, $price - $discount_amount);
    } else {
        $discount_amount = 0;
        $final_price = $price;
    }

    $update = mysqli_query($conn, "
        UPDATE bookings
        SET status='Approved', price='$price', discount_amount='$discount_amount', final_price='$final_price', visit_date='$visit_date', visit_time='$visit_time'
        WHERE id='$id'
    ");

    sendApproveMail(
        $row['email'],
        $row['first_name'] . ' ' . $row['last_name'],
        $row['id'],
        $row['service_type'],
        $row['company_type'],
        $price,
        date('d-m-Y', strtotime($visit_date)),
        date('h:i A', strtotime($visit_time)),
        $coupon_code,
        $discount_percent,
        $discount_amount,
        $final_price,
        $row['original_part'] ?? 'None (Service Only)'
    );

    if ($update) {
        sweetAlertAndRedirect('Booking Approved!', 'Booking #' . $id . ' has been approved successfully and confirmation email with pricing details has been sent to customer.', 'success', 'bookings.php');
    }

    sweetAlertAndRedirect('Update Failed', 'Something went wrong while approving the booking. Please try again.', 'error', 'booking_approve.php?id=' . $id);
}

$page_title = 'Approve Booking';
$active_page = 'bookings';

require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card card-success">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-check-circle"></i> Approve Booking</h3>
      </div>
      <form method="POST">
        <div class="card-body">
          <div class="row">
            <div class="col-md-6 form-group">
              <label>Customer Name</label>
              <input type="text" class="form-control" value="<?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?>" readonly>
            </div>
            <div class="col-md-6 form-group">
              <label>Mobile</label>
              <input type="text" class="form-control" value="<?php echo htmlspecialchars($row['mobile']); ?>" readonly>
            </div>
            <div class="col-md-6 form-group">
              <label>Service</label>
              <input type="text" class="form-control" value="<?php echo htmlspecialchars($row['service_type']); ?>" readonly>
            </div>
            <div class="col-md-6 form-group">
              <label>Original Spare Part</label>
              <input type="text" class="form-control" value="<?php echo htmlspecialchars($row['company_type']); ?>" readonly>
            </div>
            <div class="col-md-6 form-group">
              <label>Service Charge (₹)</label>
              <input type="number" name="price" class="form-control" min="0" step="1" value="<?php echo htmlspecialchars($row['price']); ?>" required>
            </div>
            <?php if (!empty($row['coupon_code'])) { ?>
            <div class="col-md-6 form-group">
              <label>Applied Coupon</label>
              <div class="input-group">
                <input type="text" class="form-control font-weight-bold text-success" value="<?php echo htmlspecialchars($row['coupon_code'] . ' (' . (float)$row['discount_percent'] . '% OFF)'); ?>" readonly>
              </div>
            </div>
            <div class="col-md-6 form-group">
              <label>Final Estimated Price (₹)</label>
              <input type="text" class="form-control font-weight-bold text-primary" value="₹ <?php echo number_format((float)($row['final_price'] > 0 ? $row['final_price'] : $row['price']), 2); ?>" readonly>
            </div>
            <?php } ?>
            <div class="col-md-6 form-group">
              <label>Visit Date</label>
              <input type="date" name="visit_date" class="form-control" value="<?php echo htmlspecialchars($row['visit_date']); ?>" required>
            </div>
            <div class="col-md-6 form-group">
              <label>Visit Time</label>
              <input type="time" name="visit_time" class="form-control" value="<?php echo htmlspecialchars($row['visit_time']); ?>" required>
            </div>
          </div>
        </div>
        <div class="card-footer">
          <button type="submit" name="approve" class="btn btn-success"><i class="fas fa-check"></i> Approve Booking</button>
          <a href="bookings.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
