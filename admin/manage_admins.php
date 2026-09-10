<?php
require_once __DIR__ . '/includes/auth.php';
requireAdminLogin();

$page_title = 'Manage Admins';
$active_page = 'manage_admins';

$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
    || isset($_GET['ajax']) 
    || isset($_POST['ajax']);

// Direct create handling on manage_admins.php (supporting both AJAX and regular POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['create']) || isset($_POST['name']))) {
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($username) || empty($password)) {
        $msg = 'Please fill in all required fields.';
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $msg]);
            exit();
        }
        $_SESSION['flash_alert'] = ['title' => 'Missing Fields', 'text' => $msg, 'icon' => 'warning'];
        header('Location: manage_admins.php');
        exit();
    } elseif ($password !== $confirm_password) {
        $msg = 'Password and Confirm Password do not match.';
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $msg]);
            exit();
        }
        $_SESSION['flash_alert'] = ['title' => 'Password Mismatch', 'text' => $msg, 'icon' => 'warning'];
        header('Location: manage_admins.php');
        exit();
    } elseif (strlen($password) < 4) {
        $msg = 'Password must be at least 4 characters long.';
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $msg]);
            exit();
        }
        $_SESSION['flash_alert'] = ['title' => 'Short Password', 'text' => $msg, 'icon' => 'warning'];
        header('Location: manage_admins.php');
        exit();
    } else {
        $safe_name = mysqli_real_escape_string($conn, $name);
        $safe_username = mysqli_real_escape_string($conn, $username);
        $check = mysqli_query($conn, "SELECT id FROM admin WHERE username='$safe_username' LIMIT 1");

        if ($check && mysqli_num_rows($check) > 0) {
            $msg = "Username '{$username}' already exists. Please choose another username.";
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => $msg]);
                exit();
            }
            $_SESSION['flash_alert'] = ['title' => 'Username Exists', 'text' => $msg, 'icon' => 'error'];
            header('Location: manage_admins.php');
            exit();
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO admin(name, username, password) VALUES('$safe_name', '$safe_username', '$hash')";

            if (mysqli_query($conn, $sql)) {
                $new_id = mysqli_insert_id($conn);
                $success_text = "New administrator account '{$name}' (@{$username}) has been created successfully.";
                if ($is_ajax) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'status'   => 'success',
                        'message'  => $success_text,
                        'id'       => $new_id,
                        'name'     => $name,
                        'username' => $username
                    ]);
                    exit();
                }

                $_SESSION['flash_alert'] = [
                    'title' => '🎉 Admin Created!',
                    'text'  => $success_text,
                    'icon'  => 'success'
                ];
                header('Location: manage_admins.php');
                exit();
            }

            $msg = 'Database error: Could not save administrator.';
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => $msg]);
                exit();
            }
            $_SESSION['flash_alert'] = ['title' => 'Error', 'text' => $msg, 'icon' => 'error'];
            header('Location: manage_admins.php');
            exit();
        }
    }
}

$result = mysqli_query($conn, 'SELECT * FROM admin ORDER BY id DESC');
$total_admins = $result ? mysqli_num_rows($result) : 0;
$current_logged_id = (int)$_SESSION['admin_id'];

$extra_js = '';
if (isset($_SESSION['flash_alert'])) {
    $fa = $_SESSION['flash_alert'];
    $extra_js .= "<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: " . json_encode($fa['title']) . ",
                text: " . json_encode($fa['text']) . ",
                icon: " . json_encode($fa['icon']) . ",
                confirmButtonColor: '#0d6efd'
            });
        }
    });
    </script>";
    unset($_SESSION['flash_alert']);
}

require_once __DIR__ . '/includes/header.php';
?>

<style>
.admin-card {
    border-radius: 12px;
    border: none;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    background: #ffffff;
    overflow: hidden;
}
.admin-table thead th {
    background: #1e293b;
    color: #f8fafc;
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    border: none;
    vertical-align: middle;
}
.admin-table td {
    vertical-align: middle;
    font-size: 0.92rem;
    padding: 12px 16px;
}
.admin-avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0284c7, #2563eb);
    color: #fff;
    font-weight: 700;
    font-size: 15px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-right: 10px;
    box-shadow: 0 2px 8px rgba(37,99,235,0.25);
}
.badge-current-user {
    background-color: #e0f2fe;
    color: #0369a1;
    font-size: 0.75rem;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 50px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.row-created-highlight {
    animation: highlightGreen 2.5s ease-out;
}
.row-deleted-highlight {
    background-color: #fee2e2 !important;
    transition: all 0.3s ease;
}
@keyframes highlightGreen {
    0% { background-color: #dcfce7; }
    50% { background-color: #bbf7d0; }
    100% { background-color: transparent; }
}
.modal-header-gradient {
    background: linear-gradient(135deg, #1e3a8a, #2563eb);
    color: #fff;
    border-top-left-radius: calc(0.3rem - 1px);
    border-top-right-radius: calc(0.3rem - 1px);
}
.modal-header-gradient .close {
    color: #fff;
    opacity: 0.85;
    text-shadow: none;
}
.modal-header-gradient .close:hover {
    opacity: 1;
}
.password-toggle-btn {
    cursor: pointer;
    border-top-right-radius: 4px !important;
    border-bottom-right-radius: 4px !important;
}
</style>

<div class="container-fluid">
  <!-- Top Banner / Header -->
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
      <h3 class="font-weight-bold text-dark mb-1">
        <i class="fas fa-user-shield text-primary mr-2"></i> Administrator Accounts
      </h3>
      <p class="text-muted mb-0">Manage system administrators with full dynamic control.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
      <span class="badge badge-info px-3 py-2 mr-2" style="font-size: 0.9rem;">
        Total: <strong id="totalAdminCounter"><?php echo $total_admins; ?></strong> Admins
      </span>
      <!-- Dynamic Popup Trigger Button -->
      <button type="button" class="btn btn-success font-weight-bold shadow-sm px-3" data-toggle="modal" data-target="#createAdminModal" id="openCreateModalBtn">
        <i class="fas fa-user-plus mr-1"></i> Create Admin
      </button>
    </div>
  </div>

  <!-- Main Card with Table -->
  <div class="card admin-card mb-4">
    <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center border-0">
      <h5 class="card-title font-weight-bold mb-0 text-dark">
        <i class="fas fa-list-alt text-secondary mr-2"></i> Authorized Administrators
      </h5>
      <div class="card-tools" style="min-width: 250px;">
        <div class="input-group input-group-sm">
          <input type="text" id="adminSearchInput" class="form-control" placeholder="Search by name or username...">
          <div class="input-group-append">
            <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
          </div>
        </div>
      </div>
    </div>

    <div class="card-body table-responsive p-0">
      <table class="table admin-table table-hover mb-0 text-center" id="adminsTable">
        <thead>
          <tr>
            <th style="width: 80px;">ID</th>
            <th class="text-left" style="min-width: 200px;">Administrator Name</th>
            <th style="min-width: 160px;">Username</th>
            <th style="width: 160px;">Status</th>
            <th style="width: 140px;">Action</th>
          </tr>
        </thead>
        <tbody id="adminsTableBody">
          <?php if ($result && mysqli_num_rows($result) > 0) { ?>
            <?php while ($row = mysqli_fetch_assoc($result)) { 
              $is_me = ($row['id'] == $current_logged_id);
              $initial = strtoupper(substr($row['name'], 0, 1));
            ?>
            <tr data-admin-id="<?php echo $row['id']; ?>" class="admin-row">
              <td class="font-weight-bold text-muted">#<?php echo $row['id']; ?></td>
              <td class="text-left">
                <div class="d-flex align-items-center">
                  <div class="admin-avatar"><?php echo $initial; ?></div>
                  <div>
                    <strong class="text-dark admin-name-text"><?php echo htmlspecialchars($row['name']); ?></strong>
                    <?php if ($is_me) { ?>
                      <span class="badge badge-current-user ml-2">
                        <i class="fas fa-check-circle"></i> You
                      </span>
                    <?php } ?>
                  </div>
                </div>
              </td>
              <td>
                <span class="badge badge-light border px-2 py-1 admin-user-text font-weight-bold text-secondary">
                  @<?php echo htmlspecialchars($row['username']); ?>
                </span>
              </td>
              <td>
                <span class="badge badge-success px-2 py-1"><i class="fas fa-shield-alt mr-1"></i> Active</span>
              </td>
              <td>
                <?php if ($is_me) { ?>
                  <button type="button" class="btn btn-outline-secondary btn-sm btn-delete-disabled" 
                          data-toggle="tooltip" title="You cannot delete your own logged in account." 
                          onclick="Swal.fire({ icon: 'warning', title: 'Action Not Allowed', text: 'You cannot delete your own active administrator account.', confirmButtonColor: '#0d6efd' });">
                    <i class="fas fa-trash-alt"></i> Delete
                  </button>
                <?php } else { ?>
                  <a href="admin_delete.php?id=<?php echo $row['id']; ?>" class="btn btn-outline-danger btn-sm btn-delete-admin" 
                     data-id="<?php echo $row['id']; ?>" 
                     data-name="<?php echo htmlspecialchars($row['name']); ?>" 
                     data-username="<?php echo htmlspecialchars($row['username']); ?>">
                    <i class="fas fa-trash-alt mr-1"></i> Delete
                  </a>
                <?php } ?>
              </td>
            </tr>
            <?php } ?>
          <?php } else { ?>
            <tr id="noAdminRow"><td colspan="5" class="py-4 text-danger font-weight-bold">No Administrators Found</td></tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ======================================================== -->
<!-- DYNAMIC POPUP MODAL: CREATE ADMIN                       -->
<!-- ======================================================== -->
<div class="modal fade" id="createAdminModal" tabindex="-1" role="dialog" aria-labelledby="createAdminModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
      <div class="modal-header modal-header-gradient py-3">
        <h5 class="modal-title font-weight-bold" id="createAdminModalLabel">
          <i class="fas fa-user-plus mr-2"></i> Create New Administrator
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form id="dynamicCreateAdminForm" action="manage_admins.php" method="POST" autocomplete="off">
        <div class="modal-body p-4">
          <!-- Full Name -->
          <div class="form-group mb-3">
            <label class="font-weight-bold text-dark">Full Name <span class="text-danger">*</span></label>
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text bg-light"><i class="fas fa-user text-primary"></i></span>
              </div>
              <input type="text" name="name" id="modal_admin_name" class="form-control" placeholder="e.g. Mohit Patel" required>
            </div>
          </div>

          <!-- Username -->
          <div class="form-group mb-3">
            <label class="font-weight-bold text-dark">Username <span class="text-danger">*</span></label>
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text bg-light"><i class="fas fa-at text-primary"></i></span>
              </div>
              <input type="text" name="username" id="modal_admin_username" class="form-control" placeholder="e.g. mohit_admin" required>
            </div>
            <small class="text-muted">Unique login username for this administrator.</small>
          </div>

          <!-- Password -->
          <div class="form-group mb-3">
            <label class="font-weight-bold text-dark">Password <span class="text-danger">*</span></label>
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text bg-light"><i class="fas fa-lock text-primary"></i></span>
              </div>
              <input type="password" name="password" id="modal_admin_password" class="form-control" placeholder="Enter secure password" required minlength="4">
              <div class="input-group-append">
                <button type="button" class="btn btn-outline-secondary password-toggle-btn" data-target="#modal_admin_password">
                  <i class="fas fa-eye"></i>
                </button>
              </div>
            </div>
          </div>

          <!-- Confirm Password -->
          <div class="form-group mb-2">
            <label class="font-weight-bold text-dark">Confirm Password <span class="text-danger">*</span></label>
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text bg-light"><i class="fas fa-shield-alt text-primary"></i></span>
              </div>
              <input type="password" name="confirm_password" id="modal_admin_confirm_password" class="form-control" placeholder="Re-enter password" required minlength="4">
              <div class="input-group-append">
                <button type="button" class="btn btn-outline-secondary password-toggle-btn" data-target="#modal_admin_confirm_password">
                  <i class="fas fa-eye"></i>
                </button>
              </div>
            </div>
            <small id="passwordMatchMessage" class="form-text mt-1"></small>
          </div>
        </div>

        <div class="modal-footer bg-light px-4 py-3 border-top-0 d-flex justify-content-between">
          <button type="button" class="btn btn-secondary px-3" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Cancel
          </button>
          <button type="submit" name="create" id="btnModalSubmitAdmin" class="btn btn-success font-weight-bold px-4">
            <i class="fas fa-check-circle mr-1"></i> Create Administrator
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<!-- PLACED AFTER FOOTER SO JQUERY, BOOTSTRAP, AND SWEETALERT2 ARE GUARANTEED LOADED -->
<script>
$(document).ready(function() {
    var currentLoggedInAdminId = <?php echo $current_logged_id; ?>;

    // Toggle Password Visibility
    $(document).on('click', '.password-toggle-btn', function(e) {
        e.preventDefault();
        var targetSelector = $(this).data('target');
        var $input = $(targetSelector);
        var $icon = $(this).find('i');
        if ($input.attr('type') === 'password') {
            $input.attr('type', 'text');
            $icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            $input.attr('type', 'password');
            $icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Real-time password match feedback
    $('#modal_admin_confirm_password, #modal_admin_password').on('keyup', function() {
        var p1 = $('#modal_admin_password').val();
        var p2 = $('#modal_admin_confirm_password').val();
        var $msg = $('#passwordMatchMessage');
        if (p2.length === 0) {
            $msg.text('').removeClass('text-success text-danger');
        } else if (p1 === p2) {
            $msg.text('✓ Passwords match').removeClass('text-danger').addClass('text-success font-weight-bold');
        } else {
            $msg.text('✗ Passwords do not match').removeClass('text-success').addClass('text-danger font-weight-bold');
        }
    });

    // Live search filter in table
    $('#adminSearchInput').on('keyup', function() {
        var query = $(this).val().toLowerCase().trim();
        var visibleCount = 0;
        $('.admin-row').each(function() {
            var name = $(this).find('.admin-name-text').text().toLowerCase();
            var user = $(this).find('.admin-user-text').text().toLowerCase();
            if (name.indexOf(query) !== -1 || user.indexOf(query) !== -1) {
                $(this).show();
                visibleCount++;
            } else {
                $(this).hide();
            }
        });
        if (visibleCount === 0) {
            if ($('#noAdminSearchRow').length === 0) {
                $('#adminsTableBody').append('<tr id="noAdminSearchRow"><td colspan="5" class="py-4 text-muted">No matching administrators found</td></tr>');
            }
        } else {
            $('#noAdminSearchRow').remove();
        }
    });

    // Helper: update total counters and empty state
    function updateAdminState() {
        var rows = $('#adminsTableBody tr.admin-row');
        var count = rows.length;
        $('#totalAdminCounter').text(count);
        if (count === 0) {
            if ($('#noAdminRow').length === 0) {
                $('#adminsTableBody').html('<tr id="noAdminRow"><td colspan="5" class="py-4 text-danger font-weight-bold">No Administrators Found</td></tr>');
            }
        } else {
            $('#noAdminRow').remove();
        }
    }

    // ========================================================
    // 1. DYNAMIC POPUP: CREATE ADMIN (AJAX + SWEETALERT2)
    // ========================================================
    $('#dynamicCreateAdminForm').on('submit', function(e) {
        e.preventDefault();

        var name = $('#modal_admin_name').val().trim();
        var username = $('#modal_admin_username').val().trim();
        var password = $('#modal_admin_password').val();
        var confirmPassword = $('#modal_admin_confirm_password').val();

        if (!name || !username || !password) {
            Swal.fire({
                icon: 'warning',
                title: 'Required Fields',
                text: 'Please fill in all required fields.',
                confirmButtonColor: '#0d6efd'
            });
            return;
        }

        if (password.length < 4) {
            Swal.fire({
                icon: 'warning',
                title: 'Password Too Short',
                text: 'Password must be at least 4 characters long.',
                confirmButtonColor: '#0d6efd'
            });
            return;
        }

        if (password !== confirmPassword) {
            Swal.fire({
                icon: 'warning',
                title: 'Password Mismatch',
                text: 'Password and Confirm Password do not match.',
                confirmButtonColor: '#0d6efd'
            });
            return;
        }

        var $btn = $('#btnModalSubmitAdmin');
        var originalBtnHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Creating Admin...');

        var formData = new FormData(this);
        formData.append('ajax', '1');
        formData.append('create', '1');

        fetch('manage_admins.php', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            $btn.prop('disabled', false).html(originalBtnHtml);

            if (data.status === 'success') {
                // Hide modal
                $('#createAdminModal').modal('hide');
                $('#dynamicCreateAdminForm')[0].reset();
                $('#passwordMatchMessage').text('').removeClass('text-success text-danger');

                // Dynamic Success Popup
                Swal.fire({
                    title: '🎉 Admin Created Successfully!',
                    html: `Administrator <strong>${data.name}</strong> (<code>@${data.username}</code>) has been added to the system.`,
                    icon: 'success',
                    confirmButtonColor: '#28a745',
                    timer: 2500,
                    timerProgressBar: true
                });

                // Remove "No Admin Found" row if present
                $('#noAdminRow').remove();

                // Dynamically prepend new row into table
                var initial = data.name.charAt(0).toUpperCase();
                var newRowHtml = `
                <tr data-admin-id="${data.id}" class="admin-row row-created-highlight">
                  <td class="font-weight-bold text-muted">#${data.id}</td>
                  <td class="text-left">
                    <div class="d-flex align-items-center">
                      <div class="admin-avatar">${initial}</div>
                      <div>
                        <strong class="text-dark admin-name-text">${data.name}</strong>
                        <span class="badge badge-success ml-2" style="font-size:0.7rem;">New</span>
                      </div>
                    </div>
                  </td>
                  <td>
                    <span class="badge badge-light border px-2 py-1 admin-user-text font-weight-bold text-secondary">
                      @${data.username}
                    </span>
                  </td>
                  <td>
                    <span class="badge badge-success px-2 py-1"><i class="fas fa-shield-alt mr-1"></i> Active</span>
                  </td>
                  <td>
                    <a href="admin_delete.php?id=${data.id}" class="btn btn-outline-danger btn-sm btn-delete-admin" 
                       data-id="${data.id}" 
                       data-name="${data.name}" 
                       data-username="${data.username}">
                      <i class="fas fa-trash-alt mr-1"></i> Delete
                    </a>
                  </td>
                </tr>
                `;
                $('#adminsTableBody').prepend(newRowHtml);
                updateAdminState();
            } else {
                // Dynamic Error / Warning Popup
                Swal.fire({
                    icon: 'error',
                    title: 'Creation Failed',
                    text: data.message || 'Unable to create administrator.',
                    confirmButtonColor: '#0d6efd'
                });
            }
        })
        .catch(function(err) {
            $btn.prop('disabled', false).html(originalBtnHtml);
            Swal.fire({
                icon: 'error',
                title: 'Server Error',
                text: 'Could not connect to the server. Please try again.',
                confirmButtonColor: '#0d6efd'
            });
        });
    });

    // ========================================================
    // 2. DYNAMIC POPUP: DELETE ADMIN (AJAX + SWEETALERT2)
    // ========================================================
    $(document).on('click', '.btn-delete-admin', function(e) {
        e.preventDefault();
        var adminId = $(this).data('id');
        var adminName = $(this).data('name');
        var adminUser = $(this).data('username');
        var $row = $(this).closest('tr');

        // Block self deletion
        if (parseInt(adminId) === currentLoggedInAdminId) {
            Swal.fire({
                icon: 'warning',
                title: 'Action Not Allowed',
                text: 'You cannot delete your own logged-in administrator account!',
                confirmButtonColor: '#0d6efd'
            });
            return;
        }

        // Dynamic Confirmation Popup
        Swal.fire({
            title: 'Delete Administrator?',
            html: `Are you sure you want to delete administrator <strong>${adminName}</strong> (<code>@${adminUser}</code>)?<br><br><span class="text-danger small font-weight-bold"><i class="fas fa-exclamation-triangle mr-1"></i> This action cannot be reversed!</span>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash-alt mr-1"></i> Yes, Delete Admin',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            focusCancel: true
        }).then(function(result) {
            if (result.isConfirmed) {
                // Dynamic Loading Popup
                Swal.fire({
                    title: 'Deleting Administrator...',
                    text: 'Please wait while account is being removed.',
                    allowOutsideClick: false,
                    didOpen: function() {
                        Swal.showLoading();
                    }
                });

                // Send AJAX Delete Request
                fetch('admin_delete.php?id=' + encodeURIComponent(adminId) + '&ajax=1', {
                    method: 'GET',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    if (data.status === 'success') {
                        // Dynamic Success Popup
                        Swal.fire({
                            title: '🎉 Deleted Successfully!',
                            text: data.message,
                            icon: 'success',
                            confirmButtonColor: '#28a745',
                            timer: 2000,
                            timerProgressBar: true
                        });

                        // Highlight red and fade out dynamically
                        $row.addClass('row-deleted-highlight');
                        setTimeout(function() {
                            $row.fadeOut(350, function() {
                                $(this).remove();
                                updateAdminState();
                            });
                        }, 250);
                    } else {
                        // Dynamic Error Popup
                        Swal.fire({
                            title: 'Deletion Failed',
                            text: data.message || 'Unable to delete administrator.',
                            icon: 'error',
                            confirmButtonColor: '#0d6efd'
                        });
                    }
                })
                .catch(function(err) {
                    Swal.fire({
                        title: 'Server Error',
                        text: 'Failed to communicate with server. Please try again.',
                        icon: 'error',
                        confirmButtonColor: '#0d6efd'
                    });
                });
            }
        });
    });
});
</script>
