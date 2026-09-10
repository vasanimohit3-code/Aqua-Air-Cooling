<?php
if (!isset($active_page)) {
    $active_page = '';
}

function adminNavActive($page, $active_page)
{
    return $page === $active_page ? ' active' : '';
}

// Fetch Dynamic Sidebar Counts safely
$side_pending_bookings = 0;
$side_total_bookings = 0;
$side_total_users = 0;
$side_total_admins = 0;
$side_active_terms = 0;

if (isset($conn) && $conn) {
    // Pending Bookings Count
    $pb_res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM bookings WHERE status='Pending'");
    if ($pb_res && $pb_row = mysqli_fetch_assoc($pb_res)) {
        $side_pending_bookings = (int)$pb_row['total'];
    }

    // Total Bookings Count
    $tb_res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM bookings");
    if ($tb_res && $tb_row = mysqli_fetch_assoc($tb_res)) {
        $side_total_bookings = (int)$tb_row['total'];
    }

    // Total Users Count
    $tu_res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users");
    if ($tu_res && $tu_row = mysqli_fetch_assoc($tu_res)) {
        $side_total_users = (int)$tu_row['total'];
    }

    // Total Admins Count
    $ta_res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM admin");
    if ($ta_res && $ta_row = mysqli_fetch_assoc($ta_res)) {
        $side_total_admins = (int)$ta_row['total'];
    }

    // Terms Count
    $tt_res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM terms_conditions WHERE is_active=1");
    if ($tt_res && $tt_row = mysqli_fetch_assoc($tt_res)) {
        $side_active_terms = (int)$tt_row['total'];
    }

    // Services Count
    $side_active_services = 0;
    $srv_res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM services WHERE is_active=1");
    if ($srv_res && $srv_row = mysqli_fetch_assoc($srv_res)) {
        $side_active_services = (int)$srv_row['total'];
    }
}

$admin_display_name = htmlspecialchars(getAdminName());
$admin_initial = strtoupper(substr($admin_display_name, 0, 1));
?>

<!-- Dynamic & Interactive Admin Sidebar -->
<aside class="main-sidebar sidebar-dark-primary elevation-4 dynamic-main-sidebar">

    <!-- Brand Header with Glowing Ice Effect -->
    <a href="dashboard.php" class="brand-link dynamic-brand-header">
        <div class="brand-logo-glow">
            <i class="fas fa-snowflake fa-spin-hover"></i>
        </div>

        <div class="d-flex flex-column">
            <span class="brand-text font-weight-bold">
                Aqua Air Cooling
            </span>
            <small class="brand-subtext">Admin Control System</small>
        </div>
    </a>

    <!-- Sidebar Scroll Area -->
    <div class="sidebar">

        <!-- Dynamic Admin Profile Card with Online Pulse -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center dynamic-user-card">
            <div class="image position-relative">
                <div class="admin-avatar-glow">
                    <?php echo $admin_initial; ?>
                </div>
                <span class="admin-online-dot" title="Online & Active"></span>
            </div>

            <div class="info ms-2">
                <a href="manage_admins.php" class="d-block admin-profile-name">
                    <?php echo $admin_display_name; ?>
                </a>
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0" style="font-size: 10px;">
                    <i class="fas fa-shield-alt me-1"></i> Super Admin
                </span>
            </div>
        </div>

        <!-- Dynamic Quick Menu Live Filter Input -->
        <div class="form-inline px-2 mb-2">
            <div class="input-group input-group-sm w-100 sidebar-search-box">
                <input class="form-control form-control-sidebar" type="search" placeholder="🔍 Quick Menu Search..." id="sidebarMenuFilter" aria-label="Search">
            </div>
        </div>
        <!-- Navigation Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" id="adminSidebarNav" data-widget="treeview" role="menu" data-accordion="false">

                <!-- ===== 1. MAIN DASHBOARD ===== -->
                <li class="nav-header sidebar-section-header">
                    <i class="fas fa-tachometer-alt me-1"></i> MAIN DASHBOARD
                </li>

                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link<?php echo adminNavActive('dashboard', $active_page); ?>">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>
                            Dashboard Overview
                            <span class="badge bg-info right">Live</span>
                        </p>
                    </a>
                </li>

                <!-- ===== 2. BOOKING MANAGEMENT ===== -->
                <li class="nav-header sidebar-section-header">
                    <i class="fas fa-calendar-check me-1"></i> BOOKING MANAGEMENT
                </li>

                <!-- Manage Bookings with Live Pending Alert Badge -->
                <li class="nav-item">
                    <a href="bookings.php" class="nav-link<?php echo adminNavActive('bookings', $active_page); ?>">
                        <i class="nav-icon fas fa-calendar-check"></i>
                        <p>
                            Manage Bookings
                            <?php if ($side_pending_bookings > 0): ?>
                                <span class="badge bg-danger right pulse-badge" title="<?php echo $side_pending_bookings; ?> Pending Bookings">
                                    <?php echo $side_pending_bookings; ?> Pending
                                </span>
                            <?php else: ?>
                                <span class="badge bg-primary-subtle text-primary right">
                                    <?php echo $side_total_bookings; ?>
                                </span>
                            <?php endif; ?>
                        </p>
                    </a>
                </li>

                <!-- Total Bookings Analytics -->
                <li class="nav-item">
                    <a href="total_bookings.php" class="nav-link<?php echo adminNavActive('total_bookings', $active_page); ?>">
                        <i class="nav-icon fas fa-list-alt"></i>
                        <p>
                            Bookings Analytics
                            <span class="badge bg-secondary right"><?php echo $side_total_bookings; ?></span>
                        </p>
                    </a>
                </li>

                <!-- Revenue Reports -->
                <li class="nav-item">
                    <a href="total_revenue.php" class="nav-link<?php echo adminNavActive('total_revenue', $active_page); ?>">
                        <i class="nav-icon fas fa-chart-line"></i>
                        <p>
                            Revenue Reports
                            <span class="badge bg-success right">₹ Net</span>
                        </p>
                    </a>
                </li>

                <!-- ===== 3. SERVICES & SETTINGS ===== -->
                <li class="nav-header sidebar-section-header">
                    <i class="fas fa-cogs me-1"></i> SERVICES & SETTINGS
                </li>

                <!-- Manage Services -->
                <li class="nav-item">
                    <a href="manage_services.php" class="nav-link<?php echo adminNavActive('services', $active_page); ?>">
                        <i class="nav-icon fas fa-cubes"></i>
                        <p>
                            Manage Services
                            <span class="badge bg-primary-subtle text-primary right"><?php echo isset($side_active_services) ? $side_active_services : 0; ?></span>
                        </p>
                    </a>
                </li>

                <!-- Booking Form & AC Brands -->
                <li class="nav-item">
                    <a href="manage_booking_form.php" class="nav-link<?php echo adminNavActive('booking_form_settings', $active_page); ?>">
                        <i class="nav-icon fas fa-sliders-h"></i>
                        <p>Booking &amp; AC Brands</p>
                    </a>
                </li>

                <!-- Terms & Conditions -->
                <li class="nav-item">
                    <a href="manage_terms.php" class="nav-link<?php echo adminNavActive('terms', $active_page); ?>">
                        <i class="nav-icon fas fa-file-contract"></i>
                        <p>
                            Terms & Conditions
                            <span class="badge bg-info-subtle text-info right"><?php echo $side_active_terms; ?></span>
                        </p>
                    </a>
                </li>

                <!-- Contact & Footer Settings -->
                <li class="nav-item">
                    <a href="manage_contact.php" class="nav-link<?php echo adminNavActive('contact_settings', $active_page); ?>">
                        <i class="nav-icon fas fa-address-book"></i>
                        <p>
                            Contact & Settings
                            <span class="badge bg-success-subtle text-success right">Footer</span>
                        </p>
                    </a>
                </li>

                <!-- Portfolio Photos -->
                <li class="nav-item">
                    <a href="manage_portfolio.php" class="nav-link<?php echo adminNavActive('portfolio', $active_page); ?>">
                        <i class="nav-icon fas fa-images"></i>
                        <p>Portfolio Photos</p>
                    </a>
                </li>

                <!-- Team Members -->
                <li class="nav-item">
                    <a href="manage_team.php" class="nav-link<?php echo adminNavActive('team', $active_page); ?>">
                        <i class="nav-icon fas fa-user-friends"></i>
                        <p>Team Members</p>
                    </a>
                </li>

                <!-- ===== 4. USER MANAGEMENT ===== -->
                <li class="nav-header sidebar-section-header">
                    <i class="fas fa-users me-1"></i> USER MANAGEMENT
                </li>

                <!-- All Customer Accounts -->
                <li class="nav-item">
                    <a href="users.php" class="nav-link<?php echo adminNavActive('users', $active_page); ?>">
                        <i class="nav-icon fas fa-users"></i>
                        <p>
                            All Customers
                            <span class="badge bg-primary right"><?php echo $side_total_users; ?></span>
                        </p>
                    </a>
                </li>

                <!-- ===== 5. ADMIN MANAGEMENT ===== -->
                <li class="nav-header sidebar-section-header">
                    <i class="fas fa-user-shield me-1"></i> ADMIN MANAGEMENT
                </li>

                <!-- Create New Admin -->
                <li class="nav-item">
                    <a href="create_admin.php" class="nav-link<?php echo adminNavActive('create_admin', $active_page); ?>">
                        <i class="nav-icon fas fa-user-plus"></i>
                        <p>Create New Admin</p>
                    </a>
                </li>

                <!-- Manage Admins -->
                <li class="nav-item">
                    <a href="manage_admins.php" class="nav-link<?php echo adminNavActive('manage_admins', $active_page); ?>">
                        <i class="nav-icon fas fa-user-shield"></i>
                        <p>
                            Manage Admin Team
                            <span class="badge bg-warning text-dark right"><?php echo $side_total_admins; ?></span>
                        </p>
                    </a>
                </li>

                <!-- ===== 6. USER WEBSITE ===== -->
                <li class="nav-header sidebar-section-header">
                    <i class="fas fa-globe me-1"></i> USER WEBSITE
                </li>

                <li class="nav-item">
                    <a href="../index.php" target="_blank" class="nav-link nav-link-external">
                        <i class="nav-icon fas fa-home"></i>
                        <p>Home Page <i class="fas fa-external-link-alt right" style="font-size:10px;opacity:.6;"></i></p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="../services.php" target="_blank" class="nav-link nav-link-external">
                        <i class="nav-icon fas fa-snowflake"></i>
                        <p>Services Page <i class="fas fa-external-link-alt right" style="font-size:10px;opacity:.6;"></i></p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="../booking.php" target="_blank" class="nav-link nav-link-external">
                        <i class="nav-icon fas fa-tools"></i>
                        <p>Booking Page <i class="fas fa-external-link-alt right" style="font-size:10px;opacity:.6;"></i></p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="../contact.php" target="_blank" class="nav-link nav-link-external">
                        <i class="nav-icon fas fa-phone-alt"></i>
                        <p>Contact Page <i class="fas fa-external-link-alt right" style="font-size:10px;opacity:.6;"></i></p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="../about.php" target="_blank" class="nav-link nav-link-external">
                        <i class="nav-icon fas fa-info-circle"></i>
                        <p>About Page <i class="fas fa-external-link-alt right" style="font-size:10px;opacity:.6;"></i></p>
                    </a>
                </li>

                <!-- LOGOUT -->
                <li class="nav-item mt-3 mb-2">
                    <a href="admin_logout.php" class="nav-link bg-danger text-white btn-confirm-action" style="border-radius: 12px;" data-title="Logout Session?" data-text="Are you sure you want to end your administrator session?">
                        <i class="nav-icon fas fa-sign-out-alt text-white"></i>
                        <p class="font-weight-bold">End Session</p>
                    </a>
                </li>

            </ul>
        </nav>

        <!-- Dynamic Live System Health Bar at Sidebar Bottom -->
        <div class="sidebar-system-status mt-3 mb-3 p-3 mx-2">
            <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="system-status-title">
                    <span class="status-live-dot"></span> System Live
                </span>
                <small class="text-white-50" style="font-size: 10px;">v2.4 Pro</small>
            </div>
            <div class="progress" style="height: 5px; background: rgba(255,255,255,0.15); border-radius: 10px;">
                <div class="progress-bar bg-info progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%;"></div>
            </div>
            <div class="d-flex justify-content-between mt-2 text-white-50" style="font-size: 11px;">
                <span>⚡ High Performance</span>
                <span class="text-success font-weight-bold">Active</span>
            </div>
        </div>

    </div>
</aside>

<!-- Dynamic Sidebar Modern CSS & Glass Animations -->
<style>
/* Dynamic Sidebar Shell with Ambient Glow & Gradient */
.dynamic-main-sidebar {
    background: linear-gradient(180deg, #070d19 0%, #0b1426 50%, #030712 100%) !important;
    border-right: 1px solid rgba(255, 255, 255, 0.08) !important;
    box-shadow: 5px 0 25px rgba(0, 0, 0, 0.3) !important;
    overflow-x: hidden !important;
}

.dynamic-main-sidebar .sidebar {
    overflow-x: hidden !important;
    overflow-y: auto !important;
    padding-left: 0 !important;
    padding-right: 0 !important;
}

/* Brand Header */
.dynamic-brand-header {
    height: 68px;
    display: flex !important;
    align-items: center;
    padding: 0 16px !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
    background: rgba(15, 23, 42, 0.95) !important;
    backdrop-filter: blur(12px);
    transition: all 0.3s ease;
    overflow: hidden;
}

.brand-logo-glow {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    border-radius: 12px;
    background: linear-gradient(135deg, #0ea5e9, #2563eb);
    color: #ffffff;
    font-size: 20px;
    box-shadow: 0 4px 15px rgba(14, 165, 233, 0.4);
    transition: all 0.3s ease;
    flex-shrink: 0;
}

.dynamic-brand-header:hover .brand-logo-glow {
    transform: rotate(15deg) scale(1.05);
    box-shadow: 0 6px 20px rgba(14, 165, 233, 0.6);
}

.fa-spin-hover {
    transition: transform 0.5s ease;
}
.dynamic-brand-header:hover .fa-spin-hover {
    transform: rotate(180deg);
}

.brand-text {
    color: #ffffff !important;
    font-size: 15px;
    letter-spacing: 0.3px;
    font-weight: 700;
}

.brand-subtext {
    color: #38bdf8;
    font-size: 10px;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    font-weight: 600;
}

/* User Card with Avatar Glow & Dot */
.dynamic-user-card {
    border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
    padding: 10px 12px;
    background: rgba(255, 255, 255, 0.03);
    border-radius: 14px;
    margin: 12px 8px !important;
    transition: all 0.25s ease;
    overflow: hidden;
}

.dynamic-user-card:hover {
    background: rgba(255, 255, 255, 0.07);
}

.admin-avatar-glow {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: linear-gradient(135deg, #0284c7, #2563eb);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-weight: 800;
    font-size: 16px;
    border: 2px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 4px 12px rgba(2, 132, 199, 0.35);
    flex-shrink: 0;
}

.admin-online-dot {
    position: absolute;
    bottom: -2px;
    right: -2px;
    width: 11px;
    height: 11px;
    background: #10b981;
    border: 2px solid #0b1426;
    border-radius: 50%;
    box-shadow: 0 0 8px rgba(16, 185, 129, 0.8);
    animation: livePulse 2s infinite;
}

.admin-profile-name {
    color: #ffffff !important;
    font-size: 13.5px;
    font-weight: 700;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Sidebar Search Box */
.sidebar-search-box .form-control-sidebar {
    background: rgba(255, 255, 255, 0.06) !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 10px !important;
    color: #ffffff !important;
    font-size: 12px;
    padding: 8px 12px;
    transition: all 0.2s ease;
    width: 100%;
}

.sidebar-search-box .form-control-sidebar:focus {
    background: rgba(255, 255, 255, 0.12) !important;
    border-color: #0ea5e9 !important;
    box-shadow: 0 0 10px rgba(14, 165, 233, 0.3) !important;
}

/* Navigation Links */
.nav-sidebar {
    overflow-x: hidden !important;
    width: 100% !important;
    padding: 0 !important;
}

.nav-sidebar .nav-item {
    margin-bottom: 2px;
    width: 100%;
}

.nav-sidebar .nav-link {
    margin: 2px 8px !important;
    border-radius: 10px;
    color: #cbd5e1 !important;
    padding: 9px 12px !important;
    font-size: 13.5px;
    font-weight: 500;
    transition: all 0.2s ease;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
}

.nav-sidebar .nav-icon {
    width: 20px;
    margin-right: 8px;
    font-size: 14.5px;
    color: #64748b;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

/* Hover State */
.nav-sidebar .nav-link:hover {
    background: rgba(14, 165, 233, 0.12) !important;
    color: #ffffff !important;
    padding-left: 14px !important;
}

.nav-sidebar .nav-link:hover .nav-icon {
    color: #38bdf8 !important;
}

/* Active State with Neon Glow Border */
.nav-sidebar .nav-link.active {
    background: linear-gradient(90deg, #0284c7 0%, #2563eb 100%) !important;
    color: #ffffff !important;
    font-weight: 700 !important;
    box-shadow: 0 4px 18px rgba(37, 99, 235, 0.45) !important;
    border-left: 4px solid #38bdf8 !important;
}

.nav-sidebar .nav-link.active .nav-icon {
    color: #ffffff !important;
}

/* Nav Section Header — Base */
.nav-header {
    color: #475569 !important;
    font-size: 10px !important;
    font-weight: 800;
    letter-spacing: 1px;
    padding: 12px 14px 4px !important;
}

/* Enhanced Section Headers with Icon + Color accent */
.nav-header.sidebar-section-header {
    color: #94a3b8 !important;
    font-size: 10.5px !important;
    font-weight: 800;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    padding: 14px 12px 5px !important;
    margin-top: 4px;
    display: flex;
    align-items: center;
    gap: 5px;
    position: relative;
}

.nav-header.sidebar-section-header::before {
    content: '';
    position: absolute;
    left: 10px;
    right: 10px;
    top: 0;
    height: 1px;
    background: rgba(255, 255, 255, 0.06);
}

.nav-header.sidebar-section-header:first-child::before {
    display: none;
}

/* Section-specific icon colors */
li.nav-header.sidebar-section-header:nth-of-type(1) { color: #38bdf8 !important; } /* Dashboard  */
li.nav-header.sidebar-section-header:nth-of-type(2) { color: #f59e0b !important; } /* Booking    */
li.nav-header.sidebar-section-header:nth-of-type(3) { color: #a78bfa !important; } /* Services   */
li.nav-header.sidebar-section-header:nth-of-type(4) { color: #34d399 !important; } /* Users      */
li.nav-header.sidebar-section-header:nth-of-type(5) { color: #fb923c !important; } /* Admin      */
li.nav-header.sidebar-section-header:nth-of-type(6) { color: #60a5fa !important; } /* Website    */

/* External link style for User Website section */
.nav-link-external {
    opacity: 0.85;
}
.nav-link-external:hover {
    opacity: 1;
}

/* Pulsing Badge for Pending alerts */
.pulse-badge {
    animation: badgePulse 1.8s infinite;
    box-shadow: 0 0 10px rgba(239, 68, 68, 0.6);
    font-weight: 800;
}

@keyframes badgePulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.08); opacity: 0.85; }
}

/* System Status Mini Widget */
.sidebar-system-status {
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 14px;
    margin-left: 8px !important;
    margin-right: 8px !important;
    overflow: hidden;
}

.system-status-title {
    color: #e2e8f0;
    font-size: 11px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 6px;
}

.status-live-dot {
    width: 7px;
    height: 7px;
    background: #10b981;
    border-radius: 50%;
    box-shadow: 0 0 6px #10b981;
}
</style>

<!-- Live Client-side Search on Sidebar Menu Items -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('sidebarMenuFilter');
    if (!searchInput) return;

    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        const navItems = document.querySelectorAll('#adminSidebarNav .nav-item');
        const navHeaders = document.querySelectorAll('#adminSidebarNav .nav-header');

        if (query === '') {
            navItems.forEach(item => item.style.display = '');
            navHeaders.forEach(h => h.style.display = '');
            return;
        }

        navHeaders.forEach(h => h.style.display = 'none');

        navItems.forEach(item => {
            const link = item.querySelector('.nav-link');
            if (link) {
                const text = link.innerText.toLowerCase();
                item.style.display = text.includes(query) ? '' : 'none';
            }
        });
    });
});
</script>