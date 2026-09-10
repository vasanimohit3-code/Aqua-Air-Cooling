<?php
require_once __DIR__ . '/includes/auth.php';
requireAdminLogin();

$page_title = 'Create Admin';
$active_page = 'create_admin';

$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
    || isset($_GET['ajax']) 
    || isset($_POST['ajax']);

$extra_js = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['create']) || $is_ajax)) {
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
        $extra_js = "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Fields',
                text: " . json_encode($msg) . ",
                confirmButtonColor: '#0d6efd'
            });
        });
        </script>";
    } elseif ($password !== $confirm_password) {
        $msg = 'Password and Confirm Password do not match.';
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $msg]);
            exit();
        }
        $extra_js = "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'warning',
                title: 'Password Mismatch',
                text: " . json_encode($msg) . ",
                confirmButtonColor: '#0d6efd'
            });
        });
        </script>";
    } elseif (strlen($password) < 4) {
        $msg = 'Password must be at least 4 characters long.';
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $msg]);
            exit();
        }
        $extra_js = "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'warning',
                title: 'Short Password',
                text: " . json_encode($msg) . ",
                confirmButtonColor: '#0d6efd'
            });
        });
        </script>";
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
            $extra_js = "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Username Exists',
                    text: " . json_encode($msg) . ",
                    confirmButtonColor: '#0d6efd'
                });
            });
            </script>";
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
                    'title' => 'Admin Created!',
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
            $extra_js = "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: " . json_encode($msg) . ",
                    confirmButtonColor: '#0d6efd'
                });
            });
            </script>";
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-6 col-md-8">
    <div class="card shadow-sm border-0 rounded-3">
      <div class="card-header bg-gradient-primary text-white py-3">
        <h3 class="card-title font-weight-bold mb-0">
          <i class="fas fa-user-plus mr-2"></i> Create New Administrator
        </h3>
      </div>
      <form id="standaloneCreateAdminForm" method="POST">
        <div class="card-body p-4">
          <div class="form-group mb-3">
            <label class="font-weight-bold text-dark">Full Name <span class="text-danger">*</span></label>
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text bg-light"><i class="fas fa-user text-primary"></i></span>
              </div>
              <input type="text" name="name" id="admin_full_name" class="form-control" placeholder="e.g. John Doe" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
            </div>
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold text-dark">Username <span class="text-danger">*</span></label>
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text bg-light"><i class="fas fa-at text-primary"></i></span>
              </div>
              <input type="text" name="username" id="admin_username" class="form-control" placeholder="e.g. johndoe_admin" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>
            <small class="text-muted">Username must be unique.</small>
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold text-dark">Password <span class="text-danger">*</span></label>
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text bg-light"><i class="fas fa-lock text-primary"></i></span>
              </div>
              <input type="password" name="password" id="admin_password" class="form-control" placeholder="Enter secure password" required minlength="4">
              <div class="input-group-append">
                <button type="button" class="btn btn-outline-secondary toggle-pass-btn" data-target="#admin_password">
                  <i class="fas fa-eye"></i>
                </button>
              </div>
            </div>
          </div>

          <div class="form-group mb-4">
            <label class="font-weight-bold text-dark">Confirm Password <span class="text-danger">*</span></label>
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text bg-light"><i class="fas fa-shield-alt text-primary"></i></span>
              </div>
              <input type="password" name="confirm_password" id="admin_confirm_password" class="form-control" placeholder="Re-enter password" required minlength="4">
              <div class="input-group-append">
                <button type="button" class="btn btn-outline-secondary toggle-pass-btn" data-target="#admin_confirm_password">
                  <i class="fas fa-eye"></i>
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="card-footer bg-light px-4 py-3 d-flex justify-content-between align-items-center">
          <a href="manage_admins.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Back to Admins
          </a>
          <button type="submit" name="create" id="btnSubmitAdmin" class="btn btn-success px-4 font-weight-bold">
            <i class="fas fa-user-plus mr-1"></i> Create Administrator
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<!-- PLACED AFTER FOOTER SO JQUERY AND SWEETALERT2 ARE GUARANTEED LOADED -->
<script>
$(document).ready(function() {
    // Password toggle
    $('.toggle-pass-btn').on('click', function(e) {
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

    // Dynamic AJAX form submit
    $('#standaloneCreateAdminForm').on('submit', function(e) {
        e.preventDefault();
        var p1 = $('#admin_password').val();
        var p2 = $('#admin_confirm_password').val();

        if (p1 !== p2) {
            Swal.fire({
                icon: 'warning',
                title: 'Password Mismatch',
                text: 'Password and Confirm Password do not match.',
                confirmButtonColor: '#0d6efd'
            });
            return;
        }

        var formData = new FormData(this);
        formData.append('ajax', '1');
        formData.append('create', '1');

        var $btn = $('#btnSubmitAdmin');
        var originalBtnHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Creating...');

        fetch('create_admin.php', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            $btn.prop('disabled', false).html(originalBtnHtml);

            if (data.status === 'success') {
                Swal.fire({
                    title: '🎉 Admin Created!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonColor: '#28a745',
                    confirmButtonText: 'View All Admins'
                }).then(function() {
                    window.location = 'manage_admins.php';
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Creation Failed',
                    text: data.message || 'An error occurred. Please try again.',
                    confirmButtonColor: '#0d6efd'
                });
            }
        })
        .catch(function(err) {
            $btn.prop('disabled', false).html(originalBtnHtml);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Network or server error. Please try again.',
                confirmButtonColor: '#0d6efd'
            });
        });
    });
});
</script>


