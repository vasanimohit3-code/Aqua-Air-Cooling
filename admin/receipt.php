<?php
require_once __DIR__ . '/includes/auth.php';
requireAdminLogin();

if (!isset($_GET['id'])) {
    die('Invalid Receipt');
}

$booking_id = (int)$_GET['id'];
$query = "SELECT * FROM bookings WHERE id='$booking_id'";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) === 0) {
    die('Receipt Not Found');
}

$row = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Booking Receipt</title>
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <style>
    body { background: #f5f5f5; }
    .receipt-box {
      width: 750px;
      max-width: 100%;
      margin: 40px auto;
      background: #fff;
      padding: 35px;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(0,0,0,.15);
    }
    .logo { text-align: center; color: #007bff; font-size: 30px; font-weight: bold; }
    .table td, .table th { padding: 12px; }
    @media print {
      body { background: white; }
      .receipt-box { width: 100%; margin: 0; box-shadow: none; }
      .btn-print { display: none; }
    }
  </style>
</head>
<body>
<div class="receipt-box">
  <div class="logo">❄ Aqua Air Cooling</div>
  <h3 class="text-center mb-4">Booking Receipt (Admin)</h3>
  <table class="table table-bordered">
    <tr><th width="35%">Booking ID</th><td><?php echo $row['id']; ?></td></tr>
    <tr><th>Customer Name</th><td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td></tr>
    <tr><th>Mobile</th><td><?php echo htmlspecialchars($row['mobile']); ?></td></tr>
    <tr><th>Email</th><td><?php echo htmlspecialchars($row['email']); ?></td></tr>
    <tr><th>Address</th><td><?php echo htmlspecialchars($row['address']); ?></td></tr>
    <tr><th>Service</th><td><?php echo htmlspecialchars($row['service_type']); ?></td></tr>
    <tr><th>AC Brand</th><td><?php echo htmlspecialchars($row['company_type']); ?></td></tr>
    <tr><th>Original Spare Part</th><td><?php echo (!empty($row['original_part']) && $row['original_part'] !== 'None (Service Only)' && $row['original_part'] !== 'None' ? htmlspecialchars($row['original_part']) : 'None (Service Only)'); ?></td></tr>
    <?php if (!empty($row['coupon_code'])) { ?>
    <tr><th>Original Service Charge</th><td>₹ <?php echo number_format($row['price'], 2); ?></td></tr>
    <tr><th>Coupon Code</th><td><span class="badge badge-success"><i class="fas fa-tag"></i> <?php echo htmlspecialchars($row['coupon_code']); ?></span></td></tr>
    <tr><th>Discount (<?php echo htmlspecialchars($row['discount_percent']); ?>%)</th><td><span class="text-danger">- ₹ <?php echo number_format($row['discount_amount'], 2); ?></span></td></tr>
    <tr><th>Final Service Charge</th><td><strong class="text-success">₹ <?php echo number_format($row['final_price'], 2); ?></strong></td></tr>
    <?php } else { ?>
    <tr><th>Service Charge</th><td>₹ <?php echo number_format($row['price'], 2); ?></td></tr>
    <?php } ?>
    <tr><th>Visit Date</th><td><?php echo htmlspecialchars($row['visit_date']); ?></td></tr>
    <tr><th>Visit Time</th><td><?php echo htmlspecialchars($row['visit_time']); ?></td></tr>
    <tr><th>Booking Date</th><td><?php echo date('d-m-Y h:i A', strtotime($row['created_at'])); ?></td></tr>
    <tr>
      <th>Status & Payment</th>
      <td>
        <?php if ($row['status'] === 'Completed') { ?>
          <span class="badge badge-success"><i class="fas fa-check-circle me-1"></i> Completed</span>
          <span class="badge badge-primary"><i class="fas fa-coins me-1"></i> Paid (<?php echo htmlspecialchars($row['payment_mode'] ?? 'Cash'); ?>)</span>
        <?php } elseif ($row['status'] === 'Approved') { ?>
          <span class="badge badge-info text-white">Approved (Visit Scheduled)</span>
        <?php } elseif ($row['status'] === 'Pending') { ?>
          <span class="badge badge-warning">Pending</span>
        <?php } else { ?>
          <span class="badge badge-danger">Rejected</span>
        <?php } ?>
      </td>
    </tr>
  </table>
  <div class="text-center mt-4">
    <button onclick="window.print()" class="btn btn-success btn-print"><i class="fas fa-print"></i> Print</button>
    <a href="receipt_pdf.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-print"><i class="fas fa-file-pdf"></i> Download PDF</a>
    <a href="javascript:history.back()" class="btn btn-secondary btn-print"><i class="fas fa-arrow-left"></i> Back</a>
  </div>
</div>
</body>
</html>