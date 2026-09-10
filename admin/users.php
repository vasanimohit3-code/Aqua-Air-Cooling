<?php
require_once __DIR__ . '/includes/auth.php';
requireAdminLogin();

$page_title = 'Manage Users';
$active_page = 'users';

$result = mysqli_query($conn, 'SELECT * FROM users ORDER BY id DESC');

require_once __DIR__ . '/includes/header.php';
?>

<div class="card">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-users mr-1"></i> Registered Users</h3>
  </div>
  <div class="card-body table-responsive p-0">
    <table class="table table-bordered table-hover text-center">
      <thead class="thead-dark">
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Email</th>
          <th width="150">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result && mysqli_num_rows($result) > 0) { ?>
          <?php while ($row = mysqli_fetch_assoc($result)) { ?>
          <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo htmlspecialchars($row['name']); ?></td>
            <td><?php echo htmlspecialchars($row['email']); ?></td>
            <td class="text-nowrap" style="white-space: nowrap;">
              <div class="d-inline-flex align-items-center justify-content-center" style="gap: 4px;">
                <a href="users_view.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> View</a>
                <a href="user_delete.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm btn-confirm-action" data-title="Delete User?" data-text="Are you sure you want to delete user '<?php echo htmlspecialchars($row['name']); ?>'?" data-icon="warning" data-confirm-btn="Yes, Delete" data-confirm-color="#dc3545"><i class="fas fa-trash"></i> Delete</a>
              </div>
            </td>
          </tr>
          <?php } ?>
        <?php } else { ?>
          <tr><td colspan="4" class="text-danger font-weight-bold">No Users Found</td></tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
  <div class="card-footer">
    <a href="dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
