<?php
require_once __DIR__ . '/includes/auth.php';
requireAdminLogin();

$page_title = 'Manage Bookings';
$active_page = 'bookings';

$search = '';
$sql = 'SELECT * FROM bookings ORDER BY id DESC';

if (isset($_GET['search']) && $_GET['search'] !== '') {
    $search = mysqli_real_escape_string($conn, trim($_GET['search']));
    $sql = "SELECT * FROM bookings
        WHERE first_name LIKE '%$search%'
        OR last_name LIKE '%$search%'
        OR mobile LIKE '%$search%'
        OR email LIKE '%$search%'
        OR service_type LIKE '%$search%'
        OR company_type LIKE '%$search%'
        ORDER BY id DESC";
}

$result = mysqli_query($conn, $sql);

require_once __DIR__ . '/includes/header.php';
?>

<style>
.compact-bookings-table {
    width: 100% !important;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 13px;
}

.compact-bookings-table thead th {
    background: #0f172a !important;
    color: #ffffff !important;
    font-weight: 700;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 12px 10px;
    border: none;
    vertical-align: middle;
}

.compact-bookings-table tbody td {
    padding: 10px 8px;
    vertical-align: middle;
    border-bottom: 1px solid #e2e8f0;
}

.compact-bookings-table tbody tr:hover td {
    background-color: #f0f9ff;
}

.btn-action-icon {
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    font-size: 13px;
    margin: 0 1px;
}
</style>

<div class="card shadow-sm border-0" style="border-radius: 16px; overflow: hidden;">
  
  <!-- Header & Search Toolbar -->
  <div class="card-header bg-white border-bottom py-3">
    <div class="row align-items-center g-2">
      <div class="col-md-6">
        <h4 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
          <i class="fas fa-calendar-check text-primary"></i> All Service Bookings
        </h4>
      </div>
      <div class="col-md-6 text-md-right">
        <form method="GET" class="d-flex align-items-center justify-content-md-end gap-2">
          <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" class="form-control form-control-sm" placeholder="🔍 Search customer, phone, brand..." style="max-width: 280px; border-radius: 20px;">
          <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold">Search</button>
          <?php if (!empty($search)): ?>
            <a href="bookings.php" class="btn btn-outline-secondary btn-sm rounded-pill px-2" title="Reset Search"><i class="fas fa-times"></i></a>
          <?php endif; ?>
        </form>
      </div>
    </div>
  </div>

  <!-- Table Body (Fits 100% on screen) -->
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table compact-bookings-table text-center align-middle mb-0">
        <thead>
          <tr>
            <th style="width: 55px;">#ID</th>
            <th style="width: 22%; text-align: left;">Customer Details</th>
            <th style="width: 22%; text-align: left;">Service & Original Part</th>
            <th style="width: 14%;">Amount</th>
            <th style="width: 16%;">Visit Schedule</th>
            <th style="width: 10%;">Status</th>
            <th style="width: 13%; min-width: 120px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result && mysqli_num_rows($result) > 0) { ?>
            <?php while ($row = mysqli_fetch_assoc($result)) { 
              $cName = htmlspecialchars(trim($row['first_name'] . ' ' . $row['last_name']));
              $status = $row['status'];
              $statusBadge = 'bg-warning text-dark';
              if ($status === 'Approved') $statusBadge = 'bg-success';
              elseif ($status === 'Rejected') $statusBadge = 'bg-danger';

              $finalPrice = isset($row['final_price']) && $row['final_price'] > 0 ? (float)$row['final_price'] : (float)$row['price'];
            ?>
            <tr>
              <!-- ID & Date -->
              <td>
                <strong class="text-primary">#<?php echo $row['id']; ?></strong><br>
                <small class="text-muted" style="font-size: 10.5px;"><?php echo date('d-m-Y', strtotime($row['created_at'])); ?></small>
              </td>

              <!-- Customer Info -->
              <td style="text-align: left;">
                <strong class="text-dark d-block mb-1" style="font-size: 13.5px;"><?php echo $cName; ?></strong>
                <div class="d-flex align-items-center gap-2 mb-1">
                  <i class="fas fa-phone-alt text-primary" style="font-size: 11px; width: 14px; text-align: center;"></i>
                  <span class="text-dark fw-semibold" style="font-size: 12px;"><?php echo htmlspecialchars($row['mobile']); ?></span>
                </div>
                <div class="d-flex align-items-center gap-2 text-truncate" style="max-width: 240px;" title="<?php echo htmlspecialchars($row['email']); ?>">
                  <i class="fas fa-envelope text-secondary" style="font-size: 11px; width: 14px; text-align: center;"></i>
                  <span class="text-muted" style="font-size: 12px;"><?php echo htmlspecialchars($row['email']); ?></span>
                </div>
              </td>

              <!-- Service, AC Brand & Original Part -->
              <td style="text-align: left;">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-1 mb-1 d-inline-block fw-bold" style="font-size: 11px;">
                  <?php echo htmlspecialchars($row['service_type']); ?>
                </span>
                <small class="text-muted d-block">Brand: <strong class="text-dark"><?php echo htmlspecialchars($row['company_type']); ?></strong></small>
                <?php if (!empty($row['original_part']) && $row['original_part'] !== 'None (Service Only)' && $row['original_part'] !== 'None'): ?>
                  <small class="text-danger d-block fw-bold mt-1"><i class="fas fa-cogs me-1"></i><?php echo htmlspecialchars($row['original_part']); ?></small>
                <?php endif; ?>
              </td>

              <!-- Amount / Coupon -->
              <td>
                <strong class="text-success fs-6">₹ <?php echo number_format($finalPrice, 2); ?></strong>
                <?php if (!empty($row['coupon_code'])): ?>
                  <br><small class="badge bg-light text-success border border-success-subtle py-0" style="font-size: 10px;" title="Coupon Discount: -₹<?php echo number_format($row['discount_amount'] ?? 0, 2); ?>">
                    🎟️ <?php echo htmlspecialchars($row['coupon_code']); ?> (<?php echo htmlspecialchars($row['discount_percent']); ?>%)
                  </small>
                <?php endif; ?>
              </td>

              <!-- Visit Schedule -->
              <td>
                <?php if (!empty($row['visit_date'])): ?>
                  <strong class="text-dark d-block"><?php echo date('d-m-Y', strtotime($row['visit_date'])); ?></strong>
                  <small class="text-primary fw-bold"><i class="far fa-clock me-1"></i><?php echo !empty($row['visit_time']) ? date('h:i A', strtotime($row['visit_time'])) : '--'; ?></small>
                <?php else: ?>
                  <span class="badge bg-light text-muted border">Not Scheduled</span>
                <?php endif; ?>
              </td>

              <!-- Status & Payment -->
              <td>
                <?php if ($status === 'Completed'): ?>
                  <span class="badge bg-success rounded-pill px-2 py-1" style="font-size: 11px;">
                    <i class="fas fa-check-circle me-1"></i> Completed
                  </span>
                  <small class="d-block text-success fw-bold mt-1" style="font-size: 10.5px;">
                    💰 Paid (<?php echo htmlspecialchars($row['payment_mode'] ?? 'Cash'); ?>)
                  </small>
                <?php elseif ($status === 'Approved'): ?>
                  <span class="badge bg-info text-white rounded-pill px-2 py-1" style="font-size: 11px;">
                    <i class="fas fa-tools me-1"></i> Approved
                  </span>
                  <small class="d-block text-muted mt-1" style="font-size: 10px;">Pending Visit</small>
                <?php elseif ($status === 'Rejected'): ?>
                  <span class="badge bg-danger rounded-pill px-2 py-1" style="font-size: 11px;">
                    Rejected
                  </span>
                <?php else: ?>
                  <span class="badge bg-warning text-dark rounded-pill px-2 py-1" style="font-size: 11px;">
                    Pending
                  </span>
                <?php endif; ?>
              </td>

              <!-- Actions -->
              <td>
                <div class="d-inline-flex align-items-center justify-content-center">
                  <!-- View Details -->
                  <a href="booking_view.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-action-icon" title="View Details">
                    <i class="fas fa-eye"></i>
                  </a>

                  <?php if ($status === 'Pending') { ?>
                    <!-- Approve -->
                    <a href="booking_approve.php?id=<?php echo $row['id']; ?>" class="btn btn-success btn-action-icon" title="Approve Booking">
                      <i class="fas fa-check"></i>
                    </a>
                    <!-- Reject -->
                    <a href="booking_reject.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-action-icon btn-confirm-action" data-title="Reject Booking?" data-text="Are you sure you want to reject booking #<?php echo $row['id']; ?>?" data-icon="warning" data-confirm-btn="Yes, Reject" data-confirm-color="#e0a800" title="Reject">
                      <i class="fas fa-times"></i>
                    </a>
                  <?php } elseif ($status === 'Approved') { ?>
                    <!-- Complete Service Button (Doorstep visit done & cash/online received) -->
                    <a href="booking_complete.php?id=<?php echo $row['id']; ?>" class="btn btn-success btn-action-icon" title="Mark Service Completed & Paid" style="background:#059669; border-color:#059669;">
                      <i class="fas fa-check-double text-white"></i>
                    </a>
                    <!-- Receipt -->
                    <a href="receipt.php?id=<?php echo $row['id']; ?>" class="btn btn-outline-primary btn-action-icon" title="Print Receipt">
                      <i class="fas fa-print"></i>
                    </a>
                  <?php } elseif ($status === 'Completed') { ?>
                    <!-- Receipt -->
                    <a href="receipt.php?id=<?php echo $row['id']; ?>" class="btn btn-outline-success btn-action-icon" title="Print Paid Receipt">
                      <i class="fas fa-print"></i>
                    </a>
                  <?php } ?>

                  <!-- Delete -->
                  <a href="booking_delete.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-action-icon btn-confirm-action" data-title="Delete Booking?" data-text="Are you sure you want to delete booking #<?php echo $row['id']; ?>?" data-icon="error" data-confirm-btn="Yes, Delete" data-confirm-color="#dc3545" title="Delete">
                    <i class="fas fa-trash"></i>
                  </a>
                </div>
              </td>
            </tr>
            <?php } ?>
          <?php } else { ?>
            <tr>
              <td colspan="7" class="text-center py-4 text-muted">
                <i class="fas fa-calendar-times fs-3 mb-2 d-block text-muted"></i>
                No bookings found.
              </td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Footer Navigation -->
  <div class="card-footer bg-light px-4 py-3">
    <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill px-4 btn-sm fw-bold">
      <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
    </a>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
