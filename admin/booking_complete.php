<?php
require_once __DIR__ . '/includes/auth.php';
requireAdminLogin();

if (!isset($_GET['id'])) {
    header('Location: bookings.php');
    exit();
}

$id = (int)$_GET['id'];
$result = mysqli_query($conn, "SELECT * FROM bookings WHERE id='$id'");

if (!$result || mysqli_num_rows($result) === 0) {
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script><style>body{font-family:sans-serif;background:#f5f7fb;}</style></head><body>';
    echo "<script>
    Swal.fire({
        icon: 'error',
        title: 'Booking Not Found',
        text: 'The requested booking could not be found.',
        confirmButtonColor: '#0d6efd'
    }).then(() => { window.location = 'bookings.php'; });
    </script></body></html>";
    exit();
}

$booking = mysqli_fetch_assoc($result);

$cust_name = htmlspecialchars(trim($booking['first_name'] . ' ' . $booking['last_name']));
$final_price = isset($booking['final_price']) && $booking['final_price'] > 0 ? (float)$booking['final_price'] : (float)$booking['price'];

// Handle Completion Form Submission
if (isset($_POST['mark_complete'])) {
    $payment_mode = mysqli_real_escape_string($conn, trim($_POST['payment_mode'] ?? 'Cash'));
    $payment_status = 'Paid';
    $tech_notes = mysqli_real_escape_string($conn, trim($_POST['technician_notes'] ?? 'Service completed at customer location'));
    
    $update_sql = "
        UPDATE bookings 
        SET status = 'Completed',
            payment_status = '$payment_status',
            payment_mode = '$payment_mode',
            technician_notes = '$tech_notes',
            completed_at = NOW()
        WHERE id = '$id'
    ";

    if (mysqli_query($conn, $update_sql)) {
        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Service Completed</title><script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script><script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script><style>body{font-family:sans-serif;background:#f5f7fb;}</style></head><body>';
        echo "<script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof confetti === 'function') {
                confetti({ particleCount: 100, spread: 70, origin: { y: 0.6 } });
            }
            Swal.fire({
                icon: 'success',
                title: '🎉 Service Completed Successfully!',
                html: '<div style=\"text-align:center;\">' +
                      '<p style=\"font-size:15px; color:#475569;\">Booking <strong>#$id</strong> for <strong>$cust_name</strong> has been marked as <strong>COMPLETED</strong>.</p>' +
                      '<div style=\"background:#ecfdf5; border:1px solid #a7f3d0; border-radius:10px; padding:12px; margin-top:10px;\">' +
                      '<span style=\"color:#065f46; font-weight:700;\">💰 Payment Received: ₹ " . number_format($final_price, 2) . " ($payment_mode)</span>' +
                      '</div>' +
                      '</div>',
                confirmButtonColor: '#10b981',
                confirmButtonText: '<i class=\"fas fa-receipt me-1\"></i> View in Bookings'
            }).then(() => {
                window.location = 'bookings.php';
            });
        });
        </script></body></html>";
        exit();
    } else {
        $error_msg = mysqli_error($conn);
    }
}

$page_title = 'Complete Service #' . $booking['id'];
$active_page = 'bookings';

require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-8">
    
    <div class="card shadow border-0" style="border-radius: 20px; overflow: hidden; background: rgba(255,255,255,0.95); backdrop-filter: blur(16px);">
      
      <!-- Card Header -->
      <div class="card-header py-3 px-4" style="background: linear-gradient(135deg, #059669, #10b981); color: #ffffff;">
        <div class="d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-white text-success d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; font-size: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
              <i class="fas fa-check-double"></i>
            </div>
            <div>
              <h4 class="mb-0 fw-bold">Complete Service & Payment</h4>
              <small class="text-white-50">Technician Visit Done & Payment Received Verification</small>
            </div>
          </div>
          <span class="badge bg-white text-success rounded-pill px-3 py-2 fw-bold fs-6">
            #<?php echo $booking['id']; ?>
          </span>
        </div>
      </div>

      <form method="POST">
        <div class="card-body p-4">
          
          <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger rounded-3 mb-4">
              <i class="fas fa-exclamation-circle me-1"></i> <?php echo $error_msg; ?>
            </div>
          <?php endif; ?>

          <!-- Booking Summary Box -->
          <div class="p-3 rounded-4 mb-4" style="background: #f8fafc; border: 1.5px solid #e2e8f0;">
            <div class="row g-3">
              <div class="col-sm-6">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 11px;">Customer</small>
                <div class="fw-bold text-dark fs-6"><?php echo $cust_name; ?></div>
                <small class="text-muted"><i class="fas fa-phone-alt me-1 text-primary"></i><?php echo htmlspecialchars($booking['mobile']); ?></small>
              </div>
              <div class="col-sm-6">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 11px;">Service & AC Brand</small>
                <div class="fw-bold text-dark"><?php echo htmlspecialchars($booking['service_type']); ?></div>
                <small class="text-muted d-block">Brand: <strong class="text-primary"><?php echo htmlspecialchars($booking['company_type']); ?></strong></small>
                <?php if (!empty($booking['original_part']) && $booking['original_part'] !== 'None (Service Only)' && $booking['original_part'] !== 'None / General Service Only' && $booking['original_part'] !== 'None'): ?>
                  <small class="text-danger fw-bold d-block mt-1"><i class="fas fa-cogs me-1"></i><?php echo htmlspecialchars($booking['original_part']); ?></small>
                <?php endif; ?>
              </div>
              <div class="col-sm-6">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 11px;">Scheduled Visit</small>
                <div class="text-dark">
                  <?php echo !empty($booking['visit_date']) ? date('d-M-Y', strtotime($booking['visit_date'])) : 'Today'; ?> 
                  (<?php echo !empty($booking['visit_time']) ? date('h:i A', strtotime($booking['visit_time'])) : 'Completed'; ?>)
                </div>
              </div>
              <div class="col-sm-6">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 11px;">Payable Amount</small>
                <div class="text-success fw-bold fs-4">₹ <?php echo number_format($final_price, 2); ?></div>
              </div>
            </div>
          </div>

          <!-- Step 1: Payment Method Selection -->
          <h5 class="fw-bold text-dark mb-3">
            <i class="fas fa-money-bill-wave text-success me-2"></i> 1. Select Payment Mode (રૂપિયા કઈ રીતે મળ્યા?)
          </h5>

          <div class="row g-3 mb-4">
            
            <!-- Cash -->
            <div class="col-md-6">
              <label class="payment-card-label w-100">
                <input type="radio" name="payment_mode" value="Cash" checked class="d-none payment-radio">
                <div class="payment-card p-3 rounded-4 border d-flex align-items-center gap-3">
                  <div class="payment-icon bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; font-size: 18px;">
                    💵
                  </div>
                  <div>
                    <strong class="d-block text-dark">Cash Payment</strong>
                    <small class="text-muted">રૂપિયા રોકડા આપ્યા (Cash in hand)</small>
                  </div>
                </div>
              </label>
            </div>

            <!-- Online / UPI -->
            <div class="col-md-6">
              <label class="payment-card-label w-100">
                <input type="radio" name="payment_mode" value="Online (UPI / QR)" class="d-none payment-radio">
                <div class="payment-card p-3 rounded-4 border d-flex align-items-center gap-3">
                  <div class="payment-icon bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; font-size: 18px;">
                    📱
                  </div>
                  <div>
                    <strong class="d-block text-dark">Online (UPI / QR Code)</strong>
                    <small class="text-muted">GPay, PhonePe, Paytm, QR સ્કેન</small>
                  </div>
                </div>
              </label>
            </div>

            <!-- Card / NetBanking -->
            <div class="col-md-6">
              <label class="payment-card-label w-100">
                <input type="radio" name="payment_mode" value="Debit / Credit Card" class="d-none payment-radio">
                <div class="payment-card p-3 rounded-4 border d-flex align-items-center gap-3">
                  <div class="payment-icon bg-info-subtle text-info rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; font-size: 18px;">
                    💳
                  </div>
                  <div>
                    <strong class="d-block text-dark">Debit / Credit Card</strong>
                    <small class="text-muted">Swipe Machine / Card POS</small>
                  </div>
                </div>
              </label>
            </div>

            <!-- Bank Transfer -->
            <div class="col-md-6">
              <label class="payment-card-label w-100">
                <input type="radio" name="payment_mode" value="Net Banking / NEFT" class="d-none payment-radio">
                <div class="payment-card p-3 rounded-4 border d-flex align-items-center gap-3">
                  <div class="payment-icon bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; font-size: 18px;">
                    🏦
                  </div>
                  <div>
                    <strong class="d-block text-dark">Net Banking / IMPS</strong>
                    <small class="text-muted">Direct Bank Account Transfer</small>
                  </div>
                </div>
              </label>
            </div>

          </div>

          <!-- Step 2: Work Notes -->
          <h5 class="fw-bold text-dark mb-2">
            <i class="fas fa-clipboard-check text-primary me-2"></i> 2. Technician Service Completion Note
          </h5>
          <div class="form-group mb-0">
            <textarea name="technician_notes" class="form-control rounded-3" rows="3" placeholder="उदा. AC Service & Gas checking completed properly. Customer verified cooling."></textarea>
            <small class="text-muted">Optional: Mention parts replaced or any service remarks.</small>
          </div>

        </div>

        <!-- Card Footer -->
        <div class="card-footer bg-light px-4 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
          <a href="bookings.php" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold">
            <i class="fas fa-times me-1"></i> Cancel
          </a>

          <button type="submit" name="mark_complete" class="btn btn-success btn-lg rounded-pill px-5 fw-bold shadow">
            <i class="fas fa-check-circle me-2"></i> Confirm Service Completed & Paid
          </button>
        </div>
      </form>

    </div>

  </div>
</div>

<style>
.payment-card-label {
    cursor: pointer;
}
.payment-card {
    transition: all 0.2s ease;
    background: #ffffff;
}
.payment-radio:checked + .payment-card {
    border-color: #10b981 !important;
    background: #ecfdf5 !important;
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.25);
    transform: translateY(-2px);
}
.payment-card:hover {
    border-color: #cbd5e1;
    background: #f8fafc;
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
