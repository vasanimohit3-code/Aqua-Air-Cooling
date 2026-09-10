<?php
require_once __DIR__ . '/includes/auth.php';
requireAdminLogin();

if (!isset($_GET['id'])) {
    header('Location: total_users.php');
    exit();
}

$user_id = (int)$_GET['id'];

$userResult = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");

if (!$userResult || mysqli_num_rows($userResult) === 0) {
    die('User Not Found');
}

$user = mysqli_fetch_assoc($userResult);

$bookings = mysqli_query($conn, "
SELECT * FROM bookings
WHERE user_id='$user_id'
ORDER BY id DESC
");

$total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) total FROM bookings WHERE user_id='$user_id'"))['total'];
$approved = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) total FROM bookings WHERE user_id='$user_id' AND status IN ('Approved', 'Completed')"))['total'];
$pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) total FROM bookings WHERE user_id='$user_id' AND status='Pending'"))['total'];
$rejected = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) total FROM bookings WHERE user_id='$user_id' AND status='Rejected'"))['total'];
$total_amount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(CASE WHEN final_price IS NOT NULL AND final_price > 0 THEN final_price ELSE price END) amount FROM bookings WHERE user_id='$user_id' AND status IN ('Approved', 'Completed')"))['amount'];

if ($total_amount === null || $total_amount === '') {
    $total_amount = 0;
}

$page_title = 'User Details';
$active_page = 'total_users';

require_once __DIR__ . '/includes/header.php';
?>

<div class="card">
  <div class="card-header bg-primary">
    <h3 class="card-title"><i class="fas fa-user"></i> User Complete Details</h3>
  </div>
  <div class="card-body">
    <div class="row">
      <div class="col-md-6">
        <h5><b>Name:</b> <?php echo htmlspecialchars($user['name']); ?></h5>
        <h5><b>Email:</b> <?php echo htmlspecialchars($user['email']); ?></h5>
      </div>
      <div class="col-md-6 text-md-right">
        <h5><b>Total Bookings:</b> <?php echo $total; ?></h5>
        <h5><b>Total Amount:</b> ₹ <?php echo number_format((float)$total_amount, 2); ?></h5>
      </div>
    </div>
    <hr>
    <div class="row text-center">
      <div class="col-md-4"><div class="alert alert-success"><h2><?php echo $approved; ?></h2>Approved</div></div>
      <div class="col-md-4"><div class="alert alert-warning"><h2><?php echo $pending; ?></h2>Pending</div></div>
      <div class="col-md-4"><div class="alert alert-danger"><h2><?php echo $rejected; ?></h2>Rejected</div></div>
    </div>
    <hr>
    <h4>Booking History</h4>
    <div class="table-responsive">
      <table class="table table-bordered table-hover text-center">
        <thead class="thead-dark">
          <tr>
            <th>ID</th>
            <th>Customer</th>
            <th>Mobile</th>
            <th>Service</th>
            <th>Company</th>
            <th>Price</th>
            <th>Status</th>
            <th>Date</th>
            <th>Receipt</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($bookings && mysqli_num_rows($bookings) > 0) { ?>
            <?php while ($row = mysqli_fetch_assoc($bookings)) { ?>
            <tr>
              <td><?php echo $row['id']; ?></td>
              <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
              <td><?php echo htmlspecialchars($row['mobile']); ?></td>
              <td><?php echo htmlspecialchars($row['service_type']); ?></td>
              <td><?php echo htmlspecialchars($row['company_type']); ?></td>
              <td>
                <?php
                if ($row['price'] > 0) {
                    if (!empty($row['coupon_code'])) {
                        $final = isset($row['final_price']) && $row['final_price'] > 0 ? $row['final_price'] : ($row['price'] - ($row['discount_amount'] ?? 0));
                        echo '<b class="text-success">₹ ' . number_format($final, 2) . '</b><br>';
                        echo '<small class="text-muted"><del>₹ ' . number_format($row['price'], 2) . '</del></small><br>';
                        echo '<span class="badge badge-success" title="Coupon: ' . htmlspecialchars($row['coupon_code']) . '"><i class="fas fa-tag"></i> ' . htmlspecialchars($row['coupon_code']) . ' (' . htmlspecialchars($row['discount_percent']) . '%)</span>';
                    } else {
                        echo '₹ ' . number_format($row['price'], 2);
                    }
                } else {
                    echo '-';
                }
                ?>
              </td>
              <td>
                <?php if ($row['status'] === 'Pending') { ?>
                  <span class="badge badge-warning">Pending</span>
                <?php } elseif ($row['status'] === 'Approved') { ?>
                  <span class="badge badge-success">Approved</span>
                <?php } else { ?>
                  <span class="badge badge-danger">Rejected</span>
                <?php } ?>
              </td>
              <td><?php echo date('d-m-Y', strtotime($row['created_at'])); ?></td>
              <td>
                <?php if ($row['status'] === 'Approved') { ?>
                <a href="receipt.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm" target="_blank"><i class="fas fa-file-invoice"></i> Receipt</a>
                <?php } else { echo '-'; } ?>
              </td>
            </tr>
            <?php } ?>
          <?php } else { ?>
            <tr><td colspan="9" class="text-danger">No Booking Found</td></tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="card-footer">
    <a href="total_users.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    <a href="dashboard.php" class="btn btn-primary"><i class="fas fa-home"></i> Dashboard</a>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
