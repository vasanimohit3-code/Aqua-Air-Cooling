<?php
require_once __DIR__ . '/includes/auth.php';
requireAdminLogin();

$page_title = 'Total Users';
$active_page = 'total_users';

$query = "
SELECT
    users.id,
    users.name,
    users.email,
    users.created_at,
    COUNT(bookings.id) AS total_bookings
FROM users
LEFT JOIN bookings ON users.id = bookings.user_id
GROUP BY users.id
ORDER BY users.id DESC
";

$result = mysqli_query($conn, $query);

require_once __DIR__ . '/includes/header.php';
?>

<div class="card">
  <div class="card-header bg-primary">
    <h3 class="card-title"><i class="fas fa-users mr-1"></i> Total Registered Users</h3>
  </div>
  <div class="card-body table-responsive p-0">
    <table class="table table-bordered table-hover text-center">
      <thead class="thead-dark">
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Email</th>
          <th>Total Bookings</th>
          <th>Joined Date</th>
          <th width="120">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result && mysqli_num_rows($result) > 0) { ?>
          <?php while ($row = mysqli_fetch_assoc($result)) { ?>
          <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo htmlspecialchars($row['name']); ?></td>
            <td><?php echo htmlspecialchars($row['email']); ?></td>
            <td><span class="badge badge-info"><?php echo $row['total_bookings']; ?></span></td>
            <td><?php echo date('d-m-Y', strtotime($row['created_at'])); ?></td>
            <td>
              <a href="users_view.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> View</a>
            </td>
          </tr>
          <?php } ?>
        <?php } else { ?>
          <tr><td colspan="6" class="text-danger font-weight-bold">No Users Found</td></tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
  <div class="card-footer">
    <a href="dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
