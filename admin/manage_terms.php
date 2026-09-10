<?php
require_once __DIR__ . '/includes/auth.php';
requireAdminLogin();

$page_title = 'Manage Terms & Conditions';
$active_page = 'terms';

$msg = '';
$msg_type = '';

// Ensure table exists & seed default data if empty
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'terms_conditions'");
if (mysqli_num_rows($table_check) == 0) {
    $create_sql = "CREATE TABLE IF NOT EXISTS `terms_conditions` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `section_number` int(11) NOT NULL DEFAULT 1,
      `title` varchar(255) NOT NULL,
      `content` text NOT NULL,
      `icon` varchar(100) NOT NULL DEFAULT 'fas fa-file-contract',
      `is_active` tinyint(1) NOT NULL DEFAULT 1,
      `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    mysqli_query($conn, $create_sql);
}

// Seed default rows if empty
$count_check = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM terms_conditions");
$count_row = mysqli_fetch_assoc($count_check);
if ($count_row['cnt'] == 0) {
    $seed_sql = "INSERT INTO `terms_conditions` (`section_number`, `title`, `content`, `icon`, `is_active`) VALUES
    (1, 'Service Booking', 'Customers must provide accurate personal information while booking AC services. Incorrect information may delay or cancel the service.', 'fas fa-calendar-check', 1),
    (2, 'Service Charges', 'Inspection charges may apply. The final service cost depends on the type of repair, spare parts used, and the condition of the AC unit.', 'fas fa-file-invoice-dollar', 1),
    (3, 'Warranty', 'Warranty is applicable only on eligible repairs and replaced spare parts. Physical damage, misuse, or unauthorized repairs are not covered.', 'fas fa-shield-alt', 1),
    (4, 'Customer Responsibilities', 'Customers must provide safe and easy access to the AC unit. Electricity and water supply should be available during the service.', 'fas fa-user-check', 1),
    (5, 'Cancellation Policy', 'Service bookings may be cancelled before the technician reaches the location. Cancellation after technician arrival may incur a visiting charge.', 'fas fa-ban', 1),
    (6, 'Payments', 'Payment must be completed immediately after the service. We accept Cash, UPI, Debit/Credit Cards, and Online Payments.', 'fas fa-credit-card', 1),
    (7, 'Liability', 'Aqua Air Cooling is not responsible for any pre-existing damage, electrical issues, or manufacturer defects in the AC unit.', 'fas fa-exclamation-triangle', 1),
    (8, 'Privacy', 'Customer information is kept confidential and is used only for booking, service updates, and customer support.', 'fas fa-user-shield', 1),
    (9, 'Changes to Terms', 'Aqua Air Cooling reserves the right to modify these Terms & Conditions at any time without prior notice.', 'fas fa-edit', 1);";
    mysqli_query($conn, $seed_sql);
}

// Handle Form Submissions (Add / Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $section_number = intval($_POST['section_number'] ?? 1);
    $title = mysqli_real_escape_string($conn, trim($_POST['title'] ?? ''));
    $content = mysqli_real_escape_string($conn, trim($_POST['content'] ?? ''));
    $icon = mysqli_real_escape_string($conn, trim($_POST['icon'] ?? 'fas fa-file-contract'));
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (empty($title) || empty($content)) {
        $msg = "Title and Content cannot be empty.";
        $msg_type = "danger";
    } else {
        if ($action === 'add') {
            $sql = "INSERT INTO terms_conditions (section_number, title, content, icon, is_active) VALUES ($section_number, '$title', '$content', '$icon', $is_active)";
            if (mysqli_query($conn, $sql)) {
                $msg = "New section added successfully!";
                $msg_type = "success";
            } else {
                $msg = "Error adding section: " . mysqli_error($conn);
                $msg_type = "danger";
            }
        } elseif ($action === 'edit') {
            $term_id = intval($_POST['term_id'] ?? 0);
            $sql = "UPDATE terms_conditions SET section_number = $section_number, title = '$title', content = '$content', icon = '$icon', is_active = $is_active WHERE id = $term_id";
            if (mysqli_query($conn, $sql)) {
                $msg = "Section updated successfully!";
                $msg_type = "success";
            } else {
                $msg = "Error updating section: " . mysqli_error($conn);
                $msg_type = "danger";
            }
        }
    }
}

// Handle Actions (Delete / Toggle)
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $term_id = intval($_GET['id'] ?? 0);

    if ($action === 'delete' && $term_id > 0) {
        if (mysqli_query($conn, "DELETE FROM terms_conditions WHERE id = $term_id")) {
            $msg = "Term section deleted successfully.";
            $msg_type = "success";
        }
    } elseif ($action === 'toggle' && $term_id > 0) {
        if (mysqli_query($conn, "UPDATE terms_conditions SET is_active = IF(is_active=1,0,1) WHERE id = $term_id")) {
            $msg = "Status toggled successfully.";
            $msg_type = "success";
        }
    }
}

// Fetch term for editing if requested
$edit_term = null;
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $edit_res = mysqli_query($conn, "SELECT * FROM terms_conditions WHERE id = $edit_id");
    if ($edit_res && mysqli_num_rows($edit_res) > 0) {
        $edit_term = mysqli_fetch_assoc($edit_res);
    }
}

// Fetch all terms
$terms_result = mysqli_query($conn, "SELECT * FROM terms_conditions ORDER BY section_number ASC, id ASC");

require_once __DIR__ . '/includes/header.php';
?>

<?php if (!empty($msg)): ?>
  <script>
  document.addEventListener('DOMContentLoaded', function() {
      Swal.fire({
          icon: '<?php echo $msg_type === 'success' ? 'success' : 'error'; ?>',
          title: '<?php echo $msg_type === 'success' ? 'Success!' : 'Notice'; ?>',
          text: <?php echo json_encode($msg); ?>,
          confirmButtonColor: '#0d6efd',
          timer: 3000,
          timerProgressBar: true
      });
  });
  </script>
  <div class="alert alert-<?php echo $msg_type; ?> alert-dismissible fade show" role="alert">
    <i class="fas fa-info-circle mr-2"></i> <?php echo htmlspecialchars($msg); ?>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>
<?php endif; ?>

<div class="row">
  <!-- Form Column -->
  <div class="col-md-4 mb-4">
    <div class="card card-primary shadow-sm">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas <?php echo $edit_term ? 'fa-edit' : 'fa-plus-circle'; ?> mr-1"></i>
          <?php echo $edit_term ? 'Edit Section' : 'Add New Section'; ?>
        </h3>
      </div>

      <form method="POST" action="manage_terms.php">
        <input type="hidden" name="action" value="<?php echo $edit_term ? 'edit' : 'add'; ?>">
        <?php if ($edit_term): ?>
          <input type="hidden" name="term_id" value="<?php echo $edit_term['id']; ?>">
        <?php endif; ?>

        <div class="card-body">
          <div class="form-group">
            <label>Section Number</label>
            <input type="number" name="section_number" class="form-control" value="<?php echo $edit_term ? $edit_term['section_number'] : (mysqli_num_rows($terms_result) + 1); ?>" required min="1">
          </div>

          <div class="form-group">
            <label>Section Title</label>
            <input type="text" name="title" class="form-control" placeholder="e.g. Service Booking" value="<?php echo htmlspecialchars($edit_term['title'] ?? ''); ?>" required>
          </div>

          <div class="form-group">
            <label>FontAwesome Icon Class</label>
            <input type="text" name="icon" class="form-control" placeholder="e.g. fas fa-calendar-check" value="<?php echo htmlspecialchars($edit_term['icon'] ?? 'fas fa-file-contract'); ?>">
            <small class="form-text text-muted">Example: <code>fas fa-shield-alt</code>, <code>fas fa-credit-card</code></small>
          </div>

          <div class="form-group">
            <label>Detailed Content</label>
            <textarea name="content" class="form-control" rows="5" placeholder="Enter terms content here..." required><?php echo htmlspecialchars($edit_term['content'] ?? ''); ?></textarea>
          </div>

          <div class="form-group form-check">
            <input type="checkbox" name="is_active" class="form-check-input" id="isActiveCheck" <?php echo (!$edit_term || $edit_term['is_active'] == 1) ? 'checked' : ''; ?>>
            <label class="form-check-input-label" for="isActiveCheck"> Active (Visible on User Side)</label>
          </div>
        </div>

        <div class="card-footer d-flex justify-content-between">
          <button type="submit" class="btn btn-success">
            <i class="fas fa-save mr-1"></i> <?php echo $edit_term ? 'Update Section' : 'Save Section'; ?>
          </button>
          <?php if ($edit_term): ?>
            <a href="manage_terms.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
          <?php endif; ?>
        </div>
      </form>
    </div>
  </div>

  <!-- Table Column -->
  <div class="col-md-8 mb-4">
    <div class="card card-dark shadow-sm">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-file-contract mr-1"></i> Terms & Conditions List</h3>
      </div>

      <div class="card-body table-responsive p-0">
        <table class="table table-bordered table-hover">
          <thead class="thead-dark">
            <tr>
              <th width="50">#</th>
              <th>Icon & Title</th>
              <th>Content Snippet</th>
              <th width="90">Status</th>
              <th width="140">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            mysqli_data_seek($terms_result, 0);
            if ($terms_result && mysqli_num_rows($terms_result) > 0) { 
              while ($row = mysqli_fetch_assoc($terms_result)) {
            ?>
              <tr>
                <td class="font-weight-bold text-center"><?php echo $row['section_number']; ?></td>
                <td>
                  <i class="<?php echo htmlspecialchars($row['icon']); ?> text-primary mr-1"></i>
                  <strong><?php echo htmlspecialchars($row['title']); ?></strong>
                </td>
                <td class="small text-muted">
                  <?php echo htmlspecialchars(mb_strimwidth($row['content'], 0, 80, "...")); ?>
                </td>
                <td class="text-center">
                  <a href="manage_terms.php?action=toggle&id=<?php echo $row['id']; ?>" class="badge badge-<?php echo $row['is_active'] ? 'success' : 'secondary'; ?>">
                    <?php echo $row['is_active'] ? 'Active' : 'Inactive'; ?>
                  </a>
                </td>
                <td class="text-center text-nowrap" style="white-space: nowrap;">
                  <div class="d-inline-flex align-items-center justify-content-center" style="gap: 4px;">
                    <a href="manage_terms.php?edit_id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm" title="Edit"><i class="fas fa-edit"></i></a>
                    <a href="manage_terms.php?action=delete&id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm btn-confirm-action" data-title="Delete Section?" data-text="Are you sure you want to delete '<?php echo htmlspecialchars($row['title']); ?>'?" data-icon="warning" data-confirm-btn="Yes, Delete" data-confirm-color="#dc3545" title="Delete"><i class="fas fa-trash"></i></a>
                  </div>
                </td>
              </tr>
            <?php 
              } 
            } else { 
            ?>
              <tr>
                <td colspan="5" class="text-center text-danger font-weight-bold">No Terms & Conditions Found.</td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
