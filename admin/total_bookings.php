<?php
require_once __DIR__ . '/includes/auth.php';
requireAdminLogin();

$page_title = 'Total Bookings Analytics';
$active_page = 'total_bookings';

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
@media print {
  .no-print { display: none !important; }
  body { background: white; }
  .card { border: none; box-shadow: none; }
}

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
</style>

<div class="card shadow-sm border-0" style="border-radius: 16px; overflow: hidden;">
  
  <!-- Header & Toolbar -->
  <div class="card-header bg-white border-bottom py-3">
    <div class="row align-items-center g-2">
      <div class="col-md-5">
        <h4 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
          <i class="fas fa-list-alt text-success"></i> Total Bookings Records
        </h4>
      </div>
      <div class="col-md-7 text-md-right d-flex align-items-center justify-content-md-end gap-2 no-print">
        <form method="GET" class="d-flex align-items-center gap-2">
          <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" class="form-control form-control-sm" placeholder="🔍 Search booking records..." style="width: 240px; border-radius: 20px;">
          <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold">Search</button>
          <?php if (!empty($search)): ?>
            <a href="total_bookings.php" class="btn btn-outline-secondary btn-sm rounded-pill px-2" title="Reset Search"><i class="fas fa-times"></i></a>
          <?php endif; ?>
        </form>
        <button onclick="window.print()" class="btn btn-outline-dark btn-sm rounded-pill px-3 fw-bold">
          <i class="fas fa-print me-1"></i> Print
        </button>
      </div>
    </div>
  </div>

  <!-- Table Body (Single Page Fit) -->
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table compact-bookings-table text-center align-middle mb-0">
        <thead>
          <tr>
            <th style="width: 55px;">#ID</th>
            <th style="width: 25%; text-align: left;">Customer Information</th>
            <th style="width: 22%; text-align: left;">Service & Original Part</th>
            <th style="width: 15%;">Amount</th>
            <th style="width: 12%;">Status</th>
            <th style="width: 13%;">Booking Date</th>
            <th style="width: 13%;" class="no-print">Receipt</th>
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
              <!-- ID -->
              <td>
                <strong class="text-primary">#<?php echo $row['id']; ?></strong>
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

              <!-- Price -->
              <td>
                <strong class="text-success fs-6">₹ <?php echo number_format($finalPrice, 2); ?></strong>
                <?php if (!empty($row['coupon_code'])): ?>
                  <br><small class="badge bg-light text-success border border-success-subtle py-0" style="font-size: 10px;">
                    🎟️ <?php echo htmlspecialchars($row['coupon_code']); ?>
                  </small>
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

              <!-- Date -->
              <td>
                <small class="text-dark fw-semibold d-block"><?php echo date('d-m-Y', strtotime($row['created_at'])); ?></small>
                <small class="text-muted" style="font-size: 10px;"><?php echo date('h:i A', strtotime($row['created_at'])); ?></small>
              </td>

              <!-- Receipt Action -->
              <td class="no-print">
                <?php if ($status === 'Approved' || $status === 'Completed') { ?>
                  <a href="receipt.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm rounded-pill px-3" target="_blank">
                    <i class="fas fa-file-invoice me-1"></i> Receipt
                  </a>
                <?php } else { ?>
                  <span class="text-muted small">--</span>
                <?php } ?>
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

  <!-- Footer -->
  <div class="card-footer bg-light px-4 py-3 no-print">
    <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill px-4 btn-sm fw-bold">
      <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
    </a>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
