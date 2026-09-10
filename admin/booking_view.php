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
    die('Booking Not Found');
}

$booking = mysqli_fetch_assoc($result);

$page_title = 'Booking Details #' . $booking['id'];
$active_page = 'bookings';

require_once __DIR__ . '/includes/header.php';

$cust_name = htmlspecialchars(trim($booking['first_name'] . ' ' . $booking['last_name']));
$status = $booking['status'];
$status_badge = 'bg-warning text-dark';
if ($status === 'Approved') $status_badge = 'bg-success';
elseif ($status === 'Rejected') $status_badge = 'bg-danger';

$final_price = isset($booking['final_price']) && $booking['final_price'] > 0 ? (float)$booking['final_price'] : (float)$booking['price'];
?>

<div class="row justify-content-center">
  <div class="col-lg-10">
    
    <div class="card shadow border-0" style="border-radius: 20px; overflow: hidden; background: rgba(255,255,255,0.92); backdrop-filter: blur(14px);">
      
      <!-- Card Header -->
      <div class="card-header d-flex justify-content-between align-items-center py-3 px-4" style="background: linear-gradient(135deg, #0284c7, #2563eb); color: #ffffff;">
        <div class="d-flex align-items-center gap-3">
          <div class="rounded-circle bg-white text-primary d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; font-size: 18px; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
            <i class="fas fa-calendar-check"></i>
          </div>
          <div>
            <h4 class="mb-0 fw-bold">Booking Request #<?php echo $booking['id']; ?></h4>
            <small class="text-white-50">Placed on <?php echo date('d M Y, h:i A', strtotime($booking['created_at'])); ?></small>
          </div>
        </div>
        <span class="badge <?php echo $status_badge; ?> rounded-pill px-3 py-2 fs-6 shadow-sm">
          ● <?php echo $status; ?>
        </span>
      </div>

      <!-- Card Body -->
      <div class="card-body p-4">
        
        <div class="row g-4">

          <!-- Left Column: Customer & Service Info -->
          <div class="col-md-6">
            <h6 class="text-uppercase fw-bold text-primary mb-3 pb-2 border-bottom">
              <i class="fas fa-user me-2"></i> Customer Information
            </h6>
            
            <table class="table table-sm table-borderless align-middle mb-4">
              <tr>
                <td class="text-muted fw-semibold" style="width: 35%;">Full Name:</td>
                <td><strong class="text-dark"><?php echo $cust_name; ?></strong></td>
              </tr>
              <tr>
                <td class="text-muted fw-semibold">Mobile:</td>
                <td><a href="tel:<?php echo htmlspecialchars($booking['mobile']); ?>" class="text-primary fw-bold text-decoration-none"><i class="fas fa-phone-alt me-1"></i><?php echo htmlspecialchars($booking['mobile']); ?></a></td>
              </tr>
              <tr>
                <td class="text-muted fw-semibold">Email:</td>
                <td><a href="mailto:<?php echo htmlspecialchars($booking['email']); ?>" class="text-muted text-decoration-none"><?php echo htmlspecialchars($booking['email']); ?></a></td>
              </tr>
              <tr>
                <td class="text-muted fw-semibold">Address:</td>
                <td class="text-dark"><i class="fas fa-map-marker-alt text-danger me-1"></i><?php echo nl2br(htmlspecialchars($booking['address'])); ?></td>
              </tr>
            </table>

            <h6 class="text-uppercase fw-bold text-primary mb-3 pb-2 border-bottom">
              <i class="fas fa-tools me-2"></i> Service Specifications
            </h6>
            <table class="table table-sm table-borderless align-middle mb-0">
              <tr>
                <td class="text-muted fw-semibold" style="width: 35%;">Service Type:</td>
                <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fw-bold"><?php echo htmlspecialchars($booking['service_type']); ?></span></td>
              </tr>
              <tr>
                <td class="text-muted fw-semibold">AC Brand / Company:</td>
                <td><strong class="text-dark"><i class="fas fa-snowflake text-primary me-1"></i><?php echo htmlspecialchars($booking['company_type']); ?></strong></td>
              </tr>
              <tr>
                <td class="text-muted fw-semibold">AC Original Spare Part:</td>
                <td>
                  <?php if (!empty($booking['original_part']) && $booking['original_part'] !== 'None (Service Only)' && $booking['original_part'] !== 'None / General Service Only' && $booking['original_part'] !== 'None'): ?>
                    <strong class="text-danger"><i class="fas fa-cogs me-1"></i><?php echo htmlspecialchars($booking['original_part']); ?></strong>
                  <?php else: ?>
                    <span class="text-muted">None (Service Only)</span>
                  <?php endif; ?>
                </td>
              </tr>
            </table>
          </div>

          <!-- Right Column: Pricing & Scheduling Info -->
          <div class="col-md-6">
            <h6 class="text-uppercase fw-bold text-success mb-3 pb-2 border-bottom">
              <i class="fas fa-wallet me-2"></i> Payment & Pricing
            </h6>

            <div class="p-3 rounded-4 mb-4" style="background: #f8fafc; border: 1.5px solid #e2e8f0;">
              <?php if (!empty($booking['coupon_code'])): ?>
                <div class="d-flex justify-content-between mb-2">
                  <span class="text-muted">Original Charge:</span>
                  <span class="text-muted text-decoration-line-through">₹ <?php echo number_format($booking['price'], 2); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                  <span class="text-success"><i class="fas fa-tag me-1"></i> Promo Coupon (<strong><?php echo htmlspecialchars($booking['coupon_code']); ?></strong>):</span>
                  <span class="badge bg-success-subtle text-success border border-success-subtle"><?php echo htmlspecialchars($booking['discount_percent']); ?>% OFF</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                  <span class="text-danger">Discount Applied:</span>
                  <span class="text-danger fw-bold">- ₹ <?php echo number_format($booking['discount_amount'], 2); ?></span>
                </div>
                <hr class="my-2">
                <div class="d-flex justify-content-between align-items-center">
                  <span class="fw-bold text-dark fs-6">Final Payable Amount:</span>
                  <strong class="text-success fs-4">₹ <?php echo number_format($final_price, 2); ?></strong>
                </div>
              <?php else: ?>
                <div class="d-flex justify-content-between align-items-center">
                  <span class="fw-bold text-dark fs-6">Service Charge:</span>
                  <strong class="text-success fs-4">₹ <?php echo number_format($booking['price'], 2); ?></strong>
                </div>
              <?php endif; ?>

              <?php if ($booking['status'] === 'Completed'): ?>
                <div class="mt-3 pt-3 border-top">
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-muted">Payment Status:</span>
                    <span class="badge bg-success rounded-pill px-3 py-1">🟢 PAID</span>
                  </div>
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-muted">Payment Mode:</span>
                    <strong class="text-dark">💰 <?php echo htmlspecialchars($booking['payment_mode'] ?? 'Cash'); ?></strong>
                  </div>
                  <?php if (!empty($booking['completed_at'])): ?>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                      <span class="text-muted">Completed On:</span>
                      <small class="text-dark fw-semibold"><?php echo date('d-M-Y h:i A', strtotime($booking['completed_at'])); ?></small>
                    </div>
                  <?php endif; ?>
                  <?php if (!empty($booking['technician_notes'])): ?>
                    <div class="mt-2 p-2 rounded bg-light border text-muted small">
                      <i class="fas fa-info-circle me-1 text-primary"></i> <strong>Note:</strong> <?php echo htmlspecialchars($booking['technician_notes']); ?>
                    </div>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            </div>

            <h6 class="text-uppercase fw-bold text-info mb-3 pb-2 border-bottom">
              <i class="fas fa-clock me-2"></i> Schedule & Arrival
            </h6>
            <table class="table table-sm table-borderless align-middle mb-0">
              <tr>
                <td class="text-muted fw-semibold" style="width: 40%;">Scheduled Visit Date:</td>
                <td><strong class="text-dark"><?php echo !empty($booking['visit_date']) ? date('d-M-Y (D)', strtotime($booking['visit_date'])) : '<span class="text-warning">Pending Assignment</span>'; ?></strong></td>
              </tr>
              <tr>
                <td class="text-muted fw-semibold">Scheduled Visit Time:</td>
                <td><strong class="text-dark"><?php echo !empty($booking['visit_time']) ? date('h:i A', strtotime($booking['visit_time'])) : '<span class="text-warning">Pending Assignment</span>'; ?></strong></td>
              </tr>
            </table>

          </div>

        </div>

      </div>

      <!-- Card Footer Actions -->
      <div class="card-footer bg-light px-4 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <a href="bookings.php" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold">
          <i class="fas fa-arrow-left me-1"></i> Back to Bookings
        </a>

        <div class="d-flex gap-2">
          <?php if ($booking['status'] === 'Pending'): ?>
            <a href="booking_approve.php?id=<?php echo $booking['id']; ?>" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm">
              <i class="fas fa-check-circle me-1"></i> Approve Booking
            </a>
            <a href="booking_reject.php?id=<?php echo $booking['id']; ?>" class="btn btn-outline-danger rounded-pill px-3 fw-semibold btn-confirm-action" data-title="Reject Booking?" data-text="Are you sure you want to reject this booking?">
              <i class="fas fa-times-circle me-1"></i> Reject
            </a>
          <?php elseif ($booking['status'] === 'Approved'): ?>
            <a href="booking_complete.php?id=<?php echo $booking['id']; ?>" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm" style="background:#059669; border-color:#059669;">
              <i class="fas fa-check-double me-1"></i> Complete Service & Payment
            </a>
            <a href="receipt.php?id=<?php echo $booking['id']; ?>" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
              <i class="fas fa-file-invoice me-1"></i> View / Print Receipt
            </a>
          <?php elseif ($booking['status'] === 'Completed'): ?>
            <a href="receipt.php?id=<?php echo $booking['id']; ?>" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm">
              <i class="fas fa-print me-1"></i> Print Paid Receipt
            </a>
          <?php endif; ?>
        </div>
      </div>

    </div>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
