<?php
if (!isset($page_title)) {
    $page_title = 'Dashboard';
}
if (!isset($active_page)) {
    $active_page = '';
}

// Fetch dynamic notification data for header
$header_pending_count = 0;
$header_pending_list = [];
$header_today_count = 0;

if (isset($conn) && $conn) {
    // Pending Bookings list
    $hp_res = mysqli_query($conn, "SELECT id, first_name, last_name, service_type, created_at FROM bookings WHERE status='Pending' ORDER BY id DESC LIMIT 4");
    if ($hp_res) {
        while ($hp_item = mysqli_fetch_assoc($hp_res)) {
            $header_pending_list[] = $hp_item;
        }
    }
    // Total Pending count
    $hpc_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM bookings WHERE status='Pending'");
    if ($hpc_res && $crow = mysqli_fetch_assoc($hpc_res)) {
        $header_pending_count = (int)$crow['total'];
    }
    // Today's Bookings count
    $htc_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM bookings WHERE DATE(created_at) = CURDATE()");
    if ($htc_res && $trow = mysqli_fetch_assoc($htc_res)) {
        $header_today_count = (int)$trow['total'];
    }
}

$current_admin_name = htmlspecialchars(getAdminName());
$current_admin_initial = strtoupper(substr($current_admin_name, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Aqua Air Cooling | <?php echo htmlspecialchars($page_title); ?></title>
  
  <!-- Fonts & Icons -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <?php if (!empty($extra_css)) { echo $extra_css; } ?>

  <style>
html, body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    background: #f1f5f9;
    color: #1e293b;
    overflow-x: hidden !important;
    max-width: 100vw !important;
    width: 100% !important;
}

.wrapper {
    overflow-x: hidden !important;
    max-width: 100vw !important;
}

.main-sidebar, .sidebar {
    overflow-x: hidden !important;
}

h1, h2, h3, h4, h5, h6, .brand-text {
    font-family: 'Outfit', 'Plus Jakarta Sans', sans-serif;
}

.content-wrapper {
    background: linear-gradient(135deg, #f0f6ff 0%, #f8fafc 100%);
    min-height: calc(100vh - 68px);
    overflow-x: hidden !important;
}

/* ================= DYNAMIC GLASSMORPHIC NAVBAR ================= */
.main-header.dynamic-navbar {
    background: linear-gradient(90deg, #075985 0%, #0284c7 40%, #1d4ed8 100%) !important;
    height: 68px;
    display: flex;
    align-items: center;
    border: none;
    padding: 0 16px;
    box-shadow: 0 4px 25px rgba(2, 132, 199, 0.28);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    position: sticky;
    top: 0;
    z-index: 1030;
}

.dynamic-navbar .nav-link {
    color: #ffffff !important;
    font-weight: 600;
    font-size: 14px;
    display: flex;
    align-items: center;
    padding: 8px 12px !important;
    border-radius: 10px;
    transition: all 0.2s ease;
}

.dynamic-navbar .nav-link:hover {
    background: rgba(255, 255, 255, 0.15) !important;
    color: #ffffff !important;
    transform: translateY(-1px);
}

/* Navbar Action Icon Buttons */
.nav-icon-btn {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.12);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #ffffff !important;
    font-size: 15px;
    margin: 0 3px;
    position: relative;
    transition: all 0.25s ease;
    border: 1px solid rgba(255, 255, 255, 0.18);
}

.nav-icon-btn:hover {
    background: rgba(255, 255, 255, 0.25) !important;
    transform: scale(1.06);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Live Header IST Clock Pill */
.header-clock-pill {
    background: rgba(255, 255, 255, 0.14);
    border: 1px solid rgba(255, 255, 255, 0.25);
    border-radius: 50px;
    padding: 6px 14px;
    color: #ffffff;
    font-size: 12.5px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.header-clock-dot {
    width: 8px;
    height: 8px;
    background: #22c55e;
    border-radius: 50%;
    box-shadow: 0 0 8px #22c55e;
    animation: livePulse 1.8s infinite;
}

@keyframes livePulse {
    0% { transform: scale(0.95); opacity: 0.8; }
    50% { transform: scale(1.2); opacity: 1; box-shadow: 0 0 12px #22c55e; }
    100% { transform: scale(0.95); opacity: 0.8; }
}

/* Notification Bell Pulsing Badge */
.bell-badge {
    position: absolute;
    top: -4px;
    right: -4px;
    font-size: 10px;
    font-weight: 800;
    padding: 2px 6px;
    border-radius: 50px;
    background: #ef4444;
    color: #ffffff;
    border: 2px solid #0284c7;
    animation: bellShake 3s infinite ease-in-out;
}

@keyframes bellShake {
    0%, 85%, 100% { transform: rotate(0deg); }
    88% { transform: rotate(15deg); }
    92% { transform: rotate(-15deg); }
    96% { transform: rotate(10deg); }
}

/* Dropdown Menu Glassmorphism Styling */
.dynamic-dropdown-menu {
    border: 1px solid rgba(255, 255, 255, 0.8) !important;
    border-radius: 16px !important;
    box-shadow: 0 15px 35px rgba(15, 23, 42, 0.18), 0 2px 8px rgba(0,0,0,0.04) !important;
    background: rgba(255, 255, 255, 0.96) !important;
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    padding: 10px !important;
    margin-top: 10px !important;
    animation: dropIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes dropIn {
    0% { opacity: 0; transform: translateY(-8px) scale(0.96); }
    100% { opacity: 1; transform: translateY(0) scale(1); }
}

.dynamic-dropdown-item {
    border-radius: 10px;
    padding: 8px 12px;
    font-size: 13px;
    font-weight: 500;
    color: #334155 !important;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 10px;
}

.dynamic-dropdown-item:hover {
    background: #f0f9ff !important;
    color: #0284c7 !important;
    transform: translateX(3px);
}

.dropdown-header-custom {
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: #64748b;
    padding: 6px 12px 4px;
}

/* Admin Profile Trigger Pill in Navbar */
.admin-profile-pill {
    background: rgba(255, 255, 255, 0.16);
    border: 1px solid rgba(255, 255, 255, 0.28);
    border-radius: 50px;
    padding: 4px 14px 4px 6px;
    display: flex;
    align-items: center;
    gap: 8px;
    color: #ffffff;
    cursor: pointer;
    transition: all 0.2s ease;
}

.admin-profile-pill:hover {
    background: rgba(255, 255, 255, 0.25);
}

.header-avatar-circle {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, #38bdf8, #2563eb);
    color: #ffffff;
    font-weight: 800;
    font-size: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid rgba(255, 255, 255, 0.5);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
}

/* Content Header & Breadcrumb */
.content-header {
    padding: 18px 16px 8px;
}

.content-header h1 {
    font-size: 24px;
    font-weight: 800;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 10px;
}

.breadcrumb {
    background: rgba(255, 255, 255, 0.6);
    backdrop-filter: blur(8px);
    border-radius: 50px;
    padding: 6px 16px;
    border: 1px solid rgba(255, 255, 255, 0.8);
    box-shadow: 0 2px 8px rgba(0,0,0,0.03);
}

.breadcrumb-item a {
    color: #0284c7;
    font-weight: 600;
}

.card {
    border: none;
    border-radius: 18px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
}

.main-footer {
    border-top: 2px solid #e2e8f0;
    background: #ffffff;
    font-size: 13px;
}
</style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <!-- ================= DYNAMIC NAVBAR ================= -->
  <nav class="main-header navbar navbar-expand dynamic-navbar">
    
    <!-- Left Navbar Links -->
    <ul class="navbar-nav align-items-center">
      <li class="nav-item">
        <a class="nav-link nav-icon-btn" data-widget="pushmenu" href="#" role="button" title="Toggle Sidebar">
            <i class="fas fa-bars"></i>
        </a>
      </li>
      <li class="nav-item d-none d-md-inline-block ms-2">
        <a href="dashboard.php" class="nav-link text-white font-weight-bold">
            <i class="fas fa-home me-1"></i> Dashboard
        </a>
      </li>
      <li class="nav-item d-none d-lg-inline-block">
        <a href="../index.php" target="_blank" class="nav-link text-white-50" title="Open Customer Website">
            <i class="fas fa-external-link-alt me-1"></i> Live Site
        </a>
      </li>
    </ul>

    <!-- Right Navbar Links -->
    <ul class="navbar-nav ml-auto align-items-center gap-2">

      <!-- Dynamic Live IST Date & Time Pill -->
      <li class="nav-item d-none d-md-inline-block">
        <div class="header-clock-pill">
            <span class="header-clock-dot"></span>
            <span id="navLiveDate" style="color: #e0f2fe; font-size: 12px; font-weight: 500;">--</span>
            <span style="opacity:0.4; font-size: 11px;">|</span>
            <span id="navLiveTime" class="fw-bold" style="letter-spacing: 0.5px;">00:00:00 AM</span>
        </div>
      </li>

      <!-- Dynamic Quick Actions Plus Menu Dropdown -->
      <li class="nav-item dropdown">
        <a class="nav-link nav-icon-btn" data-toggle="dropdown" href="#" title="Quick Creation & Tools">
            <i class="fas fa-plus"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right dynamic-dropdown-menu" style="min-width: 220px;">
            <span class="dropdown-header-custom"><i class="fas fa-bolt text-warning me-1"></i> Quick Actions</span>
            <div class="dropdown-divider my-1"></div>
            <a href="bookings.php" class="dropdown-item dynamic-dropdown-item">
                <i class="fas fa-calendar-check text-primary"></i> Manage Bookings
            </a>
            <a href="create_admin.php" class="dropdown-item dynamic-dropdown-item">
                <i class="fas fa-user-plus text-success"></i> Add New Admin
            </a>
            <a href="manage_terms.php" class="dropdown-item dynamic-dropdown-item">
                <i class="fas fa-file-contract text-info"></i> Edit Terms & Policy
            </a>
            <a href="revenue_pdf.php" target="_blank" class="dropdown-item dynamic-dropdown-item">
                <i class="fas fa-file-pdf text-danger"></i> Export Revenue PDF
            </a>
        </div>
      </li>

      <!-- Dynamic Real-time Notifications Bell Dropdown -->
      <li class="nav-item dropdown">
        <a class="nav-link nav-icon-btn" data-toggle="dropdown" href="#" title="Notifications">
            <i class="fas fa-bell"></i>
            <?php if ($header_pending_count > 0): ?>
                <span class="bell-badge"><?php echo $header_pending_count; ?></span>
            <?php endif; ?>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right dynamic-dropdown-menu" style="min-width: 290px;">
            <div class="d-flex align-items-center justify-content-between px-2 py-1">
                <span class="dropdown-header-custom mb-0"><i class="fas fa-bell text-primary me-1"></i> Booking Notifications</span>
                <span class="badge bg-primary rounded-pill px-2"><?php echo $header_pending_count; ?> Pending</span>
            </div>
            <div class="dropdown-divider my-1"></div>

            <?php if (!empty($header_pending_list)): ?>
                <?php foreach ($header_pending_list as $noti): 
                    $cust_name = htmlspecialchars(trim($noti['first_name'] . ' ' . $noti['last_name']));
                    $serv = htmlspecialchars($noti['service_type']);
                    $time_ago = date('d M, h:i A', strtotime($noti['created_at']));
                ?>
                <a href="booking_approve.php?id=<?php echo $noti['id']; ?>" class="dropdown-item dynamic-dropdown-item py-2">
                    <div class="bg-warning-subtle text-warning rounded-circle p-2 d-flex align-items-center justify-content-center" style="width:32px; height:32px; flex-shrink:0;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="w-100 overflow-hidden">
                        <strong class="d-block text-truncate text-dark" style="font-size:12.5px;"><?php echo $cust_name; ?></strong>
                        <small class="text-muted d-block text-truncate" style="font-size:11px;"><?php echo $serv; ?> • <span class="text-primary"><?php echo $time_ago; ?></span></small>
                    </div>
                </a>
                <div class="dropdown-divider my-0"></div>
                <?php endforeach; ?>
                <a href="bookings.php" class="dropdown-item text-center small text-primary fw-bold py-2">
                    <i class="fas fa-list-check me-1"></i> View All <?php echo $header_pending_count; ?> Pending Bookings
                </a>
            <?php else: ?>
                <div class="text-center py-3 text-muted">
                    <i class="fas fa-check-circle text-success fs-4 mb-1 d-block"></i>
                    <small class="fw-bold">All Bookings Up to Date!</small>
                </div>
            <?php endif; ?>
        </div>
      </li>

      <!-- Fullscreen Toggle Button -->
      <li class="nav-item d-none d-sm-inline-block">
        <a class="nav-link nav-icon-btn" data-widget="fullscreen" href="#" role="button" title="Toggle Fullscreen">
            <i class="fas fa-expand-arrows-alt"></i>
        </a>
      </li>

      <!-- Dynamic Admin User Profile Dropdown Pill -->
      <li class="nav-item dropdown">
        <div class="admin-profile-pill" data-toggle="dropdown" role="button">
            <div class="header-avatar-circle"><?php echo $current_admin_initial; ?></div>
            <div class="d-none d-sm-flex flex-column text-start">
                <span class="fw-bold text-white lh-1" style="font-size: 13px;"><?php echo $current_admin_name; ?></span>
                <small class="text-white-50" style="font-size: 10px;">Super Admin</small>
            </div>
            <i class="fas fa-chevron-down text-white-50 ms-1" style="font-size: 10px;"></i>
        </div>

        <div class="dropdown-menu dropdown-menu-md dropdown-menu-right dynamic-dropdown-menu" style="min-width: 230px;">
            <div class="px-3 py-2 text-center border-bottom mb-2">
                <div class="header-avatar-circle mx-auto mb-2" style="width:45px; height:45px; font-size:18px;"><?php echo $current_admin_initial; ?></div>
                <strong class="d-block text-dark"><?php echo $current_admin_name; ?></strong>
                <span class="badge bg-success-subtle text-success rounded-pill px-2 py-0" style="font-size: 10px;">🟢 Online Administrator</span>
            </div>

            <a href="manage_admins.php" class="dropdown-item dynamic-dropdown-item">
                <i class="fas fa-user-cog text-primary"></i> Admin Team Settings
            </a>
            <a href="create_admin.php" class="dropdown-item dynamic-dropdown-item">
                <i class="fas fa-user-plus text-info"></i> Create Admin User
            </a>
            <a href="../index.php" target="_blank" class="dropdown-item dynamic-dropdown-item">
                <i class="fas fa-globe text-success"></i> Customer Portal
            </a>
            <div class="dropdown-divider my-1"></div>
            <a href="admin_logout.php" class="dropdown-item dynamic-dropdown-item text-danger fw-bold btn-confirm-action" data-title="Logout Administrator?" data-text="Are you sure you want to end your current session?">
                <i class="fas fa-sign-out-alt text-danger"></i> Sign Out Session
            </a>
        </div>
      </li>

    </ul>
  </nav>

  <!-- Include Sidebar -->
  <?php require_once __DIR__ . '/sidebar.php'; ?>

  <!-- Content Wrapper -->
  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2 align-items-center">
          <div class="col-sm-6">
            <h1 class="m-0">
                <i class="fas fa-layer-group text-primary me-2" style="font-size: 20px;"></i>
                <?php echo htmlspecialchars($page_title); ?>
            </h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right mb-0">
              <li class="breadcrumb-item"><a href="dashboard.php"><i class="fas fa-home me-1"></i> Admin</a></li>
              <li class="breadcrumb-item active"><?php echo htmlspecialchars($page_title); ?></li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">

<!-- Real-time Navbar Date & Time Updater Script -->
<script>
function updateNavTime() {
    const timeElem = document.getElementById('navLiveTime');
    const dateElem = document.getElementById('navLiveDate');
    const now = new Date();

    if (timeElem) {
        timeElem.innerText = now.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true
        });
    }

    if (dateElem) {
        dateElem.innerText = now.toLocaleDateString('en-US', {
            weekday: 'short',
            day: 'numeric',
            month: 'short',
            year: 'numeric'
        });
    }
}
setInterval(updateNavTime, 1000);
updateNavTime();
</script>
