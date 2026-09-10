<?php
require_once __DIR__ . '/includes/auth.php';
requireAdminLogin();

$page_title = 'Dynamic Admin Dashboard';
$active_page = 'dashboard';

// --- DATA QUERIES --- //

// Total Users
$userQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users");
$userData = mysqli_fetch_assoc($userQuery);
$total_users = (int)($userData['total'] ?? 0);

// Users registered in last 7 days
$newUsersQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$newUsersData = mysqli_fetch_assoc($newUsersQuery);
$newUsersThisWeek = (int)($newUsersData['total'] ?? 0);

// Total Bookings
$bookingQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM bookings");
$bookingData = mysqli_fetch_assoc($bookingQuery);
$totalBookings = (int)($bookingData['total'] ?? 0);

// Status Breakdown
$approvedQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM bookings WHERE status IN ('Approved', 'Completed')");
$approvedData = mysqli_fetch_assoc($approvedQuery);
$totalApproved = (int)($approvedData['total'] ?? 0);

$pendingQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM bookings WHERE status='Pending'");
$pendingData = mysqli_fetch_assoc($pendingQuery);
$totalPending = (int)($pendingData['total'] ?? 0);

$rejectedQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM bookings WHERE status='Rejected'");
$rejectedData = mysqli_fetch_assoc($rejectedQuery);
$totalRejected = (int)($rejectedData['total'] ?? 0);

// Today's Bookings
$todayBookingsQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM bookings WHERE DATE(created_at) = CURDATE()");
$todayBookingsData = mysqli_fetch_assoc($todayBookingsQuery);
$todayBookings = (int)($todayBookingsData['total'] ?? 0);

// Total Revenue
$revenueQuery = mysqli_query($conn, "
    SELECT SUM(CASE WHEN final_price IS NOT NULL AND final_price > 0 THEN final_price ELSE price END) AS total
    FROM bookings
    WHERE status IN ('Approved', 'Completed')
");
$revenueData = mysqli_fetch_assoc($revenueQuery);
$totalRevenue = (float)($revenueData['total'] ?? 0);

// Today's Revenue
$todayRevQuery = mysqli_query($conn, "
    SELECT SUM(CASE WHEN final_price IS NOT NULL AND final_price > 0 THEN final_price ELSE price END) AS total
    FROM bookings
    WHERE status IN ('Approved', 'Completed') AND DATE(created_at) = CURDATE()
");
$todayRevData = mysqli_fetch_assoc($todayRevQuery);
$todayRevenue = (float)($todayRevData['total'] ?? 0);

// Average Booking Value
$avgBookingValue = $totalApproved > 0 ? round($totalRevenue / $totalApproved) : 0;

// Approval Rate %
$approvalRate = $totalBookings > 0 ? round(($totalApproved / $totalBookings) * 100, 1) : 0;

// Most Popular Service
$popServiceQuery = mysqli_query($conn, "
    SELECT service_type, COUNT(*) as cnt 
    FROM bookings 
    WHERE service_type != '' 
    GROUP BY service_type 
    ORDER BY cnt DESC 
    LIMIT 1
");
$popServiceRow = mysqli_fetch_assoc($popServiceQuery);
$topServiceName = $popServiceRow['service_type'] ?? 'AC Service';
$topServiceCount = (int)($popServiceRow['cnt'] ?? 0);

// Most Popular AC Brand
$popBrandQuery = mysqli_query($conn, "
    SELECT company_type, COUNT(*) as cnt 
    FROM bookings 
    WHERE company_type != '' 
    GROUP BY company_type 
    ORDER BY cnt DESC 
    LIMIT 1
");
$popBrandRow = mysqli_fetch_assoc($popBrandQuery);
$topBrandName = $popBrandRow['company_type'] ?? 'Voltas';
$topBrandCount = (int)($popBrandRow['cnt'] ?? 0);

// Monthly Bookings for Last 6 Months (Chart 1)
$monthlyLabels = [];
$monthlyApprovedCounts = [];
$monthlyPendingCounts = [];
$monthlyRevenue = [];

for ($i = 5; $i >= 0; $i--) {
    $monthStart = date('Y-m-01', strtotime("-$i months"));
    $monthEnd = date('Y-m-t', strtotime("-$i months"));
    $monthLabel = date('M Y', strtotime("-$i months"));
    $monthlyLabels[] = $monthLabel;

    $mAppQ = mysqli_query($conn, "SELECT COUNT(*) as c, SUM(CASE WHEN final_price IS NOT NULL AND final_price > 0 THEN final_price ELSE price END) as rev FROM bookings WHERE status='Approved' AND created_at BETWEEN '$monthStart 00:00:00' AND '$monthEnd 23:59:59'");
    $mAppRow = mysqli_fetch_assoc($mAppQ);
    $monthlyApprovedCounts[] = (int)($mAppRow['c'] ?? 0);
    $monthlyRevenue[] = (float)($mAppRow['rev'] ?? 0);

    $mPenQ = mysqli_query($conn, "SELECT COUNT(*) as c FROM bookings WHERE status='Pending' AND created_at BETWEEN '$monthStart 00:00:00' AND '$monthEnd 23:59:59'");
    $mPenRow = mysqli_fetch_assoc($mPenQ);
    $monthlyPendingCounts[] = (int)($mPenRow['c'] ?? 0);
}

// Price Comparison Data (Original Price vs Discount Price) for Last 6 Months
$monthlyOriginalPrice = [];
$monthlyDiscountPrice = [];

for ($i = 5; $i >= 0; $i--) {
    $monthStart = date('Y-m-01', strtotime("-$i months"));
    $monthEnd   = date('Y-m-t',  strtotime("-$i months"));

    // Average original price (price column)
    $mOrigQ = mysqli_query($conn, "SELECT AVG(price) as avg_orig FROM bookings WHERE price > 0 AND created_at BETWEEN '$monthStart 00:00:00' AND '$monthEnd 23:59:59'");
    $mOrigRow = mysqli_fetch_assoc($mOrigQ);
    $monthlyOriginalPrice[] = round((float)($mOrigRow['avg_orig'] ?? 0), 2);

    // Average discount / final price (final_price column, fallback to price if null)
    $mDiscQ = mysqli_query($conn, "SELECT AVG(CASE WHEN final_price IS NOT NULL AND final_price > 0 THEN final_price ELSE price END) as avg_disc FROM bookings WHERE created_at BETWEEN '$monthStart 00:00:00' AND '$monthEnd 23:59:59'");
    $mDiscRow = mysqli_fetch_assoc($mDiscQ);
    $monthlyDiscountPrice[] = round((float)($mDiscRow['avg_disc'] ?? 0), 2);
}

// Service Type Distribution (Chart 2)
$serviceLabels = [];
$serviceCounts = [];
$serviceQuery = mysqli_query($conn, "
    SELECT service_type, COUNT(*) as total 
    FROM bookings 
    WHERE service_type != '' 
    GROUP BY service_type 
    ORDER BY total DESC 
    LIMIT 6
");
while ($sRow = mysqli_fetch_assoc($serviceQuery)) {
    $serviceLabels[] = $sRow['service_type'];
    $serviceCounts[] = (int)$sRow['total'];
}

// AC Brands Distribution (Chart 3)
$brandLabels = [];
$brandCounts = [];
$brandQuery = mysqli_query($conn, "
    SELECT company_type, COUNT(*) as total 
    FROM bookings 
    WHERE company_type != '' 
    GROUP BY company_type 
    ORDER BY total DESC 
    LIMIT 6
");
while ($bRow = mysqli_fetch_assoc($brandQuery)) {
    $brandLabels[] = $bRow['company_type'];
    $brandCounts[] = (int)$bRow['total'];
}

// Recent 7 Bookings
$recentBookings = mysqli_query($conn, "SELECT * FROM bookings ORDER BY id DESC LIMIT 7");

// Recent 5 Registered Users
$recentUsers = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC LIMIT 5");

require_once __DIR__ . '/includes/header.php';
?>

<!-- Dynamic Animated Background & Glassmorphic Dashboard Styles -->
<style>
/* Background Canvas Animation Container */
#dynamicBgCanvas {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    z-index: 0;
    pointer-events: none;
    opacity: 0.75;
}

/* Ambient Glowing Background Orbs */
.ambient-glow-wrapper {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    z-index: 0;
    pointer-events: none;
}
.ambient-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.45;
    animation: floatGlow 20s infinite alternate ease-in-out;
}
.orb-1 {
    width: 450px;
    height: 450px;
    background: radial-gradient(circle, #0ea5e9, #2563eb);
    top: -100px;
    right: -100px;
}
.orb-2 {
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, #06b6d4, #3b82f6);
    bottom: -120px;
    left: 10%;
    animation-duration: 25s;
    animation-delay: -5s;
}
.orb-3 {
    width: 350px;
    height: 350px;
    background: radial-gradient(circle, #8b5cf6, #ec4899);
    top: 40%;
    right: 25%;
    opacity: 0.25;
    animation-duration: 18s;
    animation-delay: -8s;
}

@keyframes floatGlow {
    0% { transform: translate(0, 0) scale(1) rotate(0deg); }
    50% { transform: translate(60px, 40px) scale(1.15) rotate(180deg); }
    100% { transform: translate(-40px, 70px) scale(0.95) rotate(360deg); }
}

/* Base Content Layer above canvas */
.content-wrapper {
    position: relative;
    z-index: 1;
    background: linear-gradient(135deg, rgba(240, 246, 255, 0.88), rgba(248, 250, 255, 0.95)) !important;
}

/* Glassmorphism Cards */
.glass-card {
    background: rgba(255, 255, 255, 0.85) !important;
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.8) !important;
    border-radius: 20px !important;
    box-shadow: 0 10px 30px rgba(14, 165, 233, 0.08), 0 1px 3px rgba(0,0,0,0.03) !important;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
    overflow: hidden;
}

.glass-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 16px 36px rgba(14, 165, 233, 0.15), 0 4px 12px rgba(0,0,0,0.05) !important;
}

/* Hero Welcome Banner */
.hero-admin-banner {
    background: linear-gradient(135deg, #0284c7 0%, #2563eb 50%, #1d4ed8 100%);
    border-radius: 24px;
    color: #ffffff;
    padding: 28px 32px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 15px 35px rgba(37, 99, 235, 0.28);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.hero-admin-banner::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 350px;
    height: 350px;
    background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 70%);
    border-radius: 50%;
    pointer-events: none;
}

.hero-admin-banner .banner-icon {
    font-size: 55px;
    opacity: 0.9;
    animation: pulseIcon 3s infinite ease-in-out;
}

@keyframes pulseIcon {
    0%, 100% { transform: scale(1); filter: drop-shadow(0 0 10px rgba(255,255,255,0.3)); }
    50% { transform: scale(1.08); filter: drop-shadow(0 0 20px rgba(255,255,255,0.6)); }
}

/* Live IST Clock Badge */
.live-clock-badge {
    background: rgba(255, 255, 255, 0.18);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 50px;
    padding: 8px 18px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 600;
    color: #ffffff;
    letter-spacing: 0.5px;
}

.live-pulse-dot {
    width: 9px;
    height: 9px;
    background-color: #22c55e;
    border-radius: 50%;
    display: inline-block;
    box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
    animation: livePulse 1.8s infinite;
}

@keyframes livePulse {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(34, 197, 94, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
}

/* Metric Cards */
.stat-metric-card {
    border-radius: 20px;
    padding: 22px;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: all 0.3s ease;
    border: 1px solid rgba(255, 255, 255, 0.85);
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(14px);
    position: relative;
    overflow: hidden;
}

.stat-metric-card::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 6px;
    height: 100%;
    border-radius: 20px 0 0 20px;
}

.stat-blue::after { background: linear-gradient(180deg, #0ea5e9, #2563eb); }
.stat-green::after { background: linear-gradient(180deg, #10b981, #059669); }
.stat-amber::after { background: linear-gradient(180deg, #f59e0b, #d97706); }
.stat-purple::after { background: linear-gradient(180deg, #8b5cf6, #6d28d9); }

.stat-icon-bubble {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    transition: all 0.3s ease;
}

.stat-blue .stat-icon-bubble { background: rgba(14, 165, 233, 0.12); color: #0284c7; }
.stat-green .stat-icon-bubble { background: rgba(16, 185, 129, 0.12); color: #059669; }
.stat-amber .stat-icon-bubble { background: rgba(245, 158, 11, 0.12); color: #d97706; }
.stat-purple .stat-icon-bubble { background: rgba(139, 92, 246, 0.12); color: #7c3aed; }

.stat-metric-card:hover .stat-icon-bubble {
    transform: rotate(10deg) scale(1.1);
}

.stat-number {
    font-size: 28px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.2;
}

.stat-label {
    font-size: 13px;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.6px;
}

.stat-badge {
    font-size: 11px;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 50px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

/* Quick Action Buttons */
.quick-action-btn {
    border-radius: 14px;
    padding: 12px 18px;
    font-weight: 600;
    font-size: 13px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.25s ease;
    border: none;
    text-decoration: none !important;
}

.quick-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.12);
}

/* Custom Table Styling */
.dynamic-table {
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
}
.dynamic-table thead th {
    background: #f8fafc;
    color: #475569;
    font-weight: 700;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    border-top: none;
    border-bottom: 2px solid #e2e8f0;
    padding: 14px 16px;
}
.dynamic-table tbody td {
    padding: 14px 16px;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
    font-size: 13.5px;
}
.dynamic-table tbody tr:hover td {
    background: rgba(240, 249, 255, 0.7);
}

/* User Avatar Initials */
.user-avatar-circle {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: linear-gradient(135deg, #38bdf8, #2563eb);
    color: #ffffff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 14px;
    box-shadow: 0 3px 8px rgba(37, 99, 235, 0.2);
}

/* Live search input */
.table-search-input {
    border-radius: 50px;
    padding: 8px 18px 8px 38px;
    border: 1.5px solid #cbd5e1;
    font-size: 13px;
    background: #ffffff;
    transition: all 0.2s ease;
}
.table-search-input:focus {
    border-color: #0284c7;
    box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
}

/* Chart Canvas Container */
.chart-container-box {
    position: relative;
    height: 280px;
    width: 100%;
}
</style>

<!-- Canvas for Dynamic Background Animation -->
<canvas id="dynamicBgCanvas"></canvas>

<!-- Ambient Glowing Orbs -->
<div class="ambient-glow-wrapper">
    <div class="ambient-orb orb-1"></div>
    <div class="ambient-orb orb-2"></div>
    <div class="ambient-orb orb-3"></div>
</div>

<!-- ================= HERO WELCOME BANNER ================= -->
<div class="row mb-4">
    <div class="col-12">
        <div class="hero-admin-banner">
            <div class="row align-items-center">
                <div class="col-lg-8 col-md-7">
                    <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                        <span class="badge bg-white text-primary rounded-pill px-3 py-1 fw-bold" style="font-size: 12px;">
                            <i class="fas fa-shield-alt me-1"></i> Super Admin Panel
                        </span>
                        <span class="badge bg-success-subtle text-white border border-white-50 rounded-pill px-3 py-1 fw-bold" style="font-size: 12px; background: rgba(34, 197, 94, 0.25) !important;">
                            <span class="live-pulse-dot" style="width:7px; height:7px; margin-right:4px;"></span> Active Session
                        </span>
                    </div>

                    <h1 class="h2 font-weight-bold mb-2 text-white" id="greetingTitle">
                        Welcome back, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Administrator'); ?>! ❄️
                    </h1>
                    <p class="mb-3 text-white-50" style="font-size: 15px;">
                        Real-time overview of Aqua Air Cooling AC services, active bookings, customers and revenue performance.
                    </p>

                    <!-- Quick Toolbar inside Banner -->
                    <div class="d-flex flex-wrap gap-2 pt-1">
                        <a href="bookings.php" class="btn btn-light btn-sm rounded-pill px-3 py-2 fw-bold text-primary shadow-sm">
                            <i class="fas fa-calendar-check me-1"></i> Manage Bookings (<?php echo $totalBookings; ?>)
                        </a>
                        <a href="users.php" class="btn btn-outline-light btn-sm rounded-pill px-3 py-2 fw-bold">
                            <i class="fas fa-users me-1"></i> Users (<?php echo $total_users; ?>)
                        </a>
                        <a href="revenue_pdf.php" target="_blank" class="btn btn-outline-light btn-sm rounded-pill px-3 py-2 fw-bold">
                            <i class="fas fa-file-pdf me-1"></i> Revenue PDF
                        </a>
                        <a href="../index.php" target="_blank" class="btn btn-warning btn-sm rounded-pill px-3 py-2 fw-bold shadow-sm">
                            <i class="fas fa-external-link-alt me-1"></i> Live Website
                        </a>
                    </div>
                </div>

                <div class="col-lg-4 col-md-5 text-center text-md-right mt-3 mt-md-0">
                    <div class="banner-icon mb-2">❄️🌬️</div>
                    <div class="badge bg-info-subtle text-white border border-white-50 rounded-pill px-3 py-2 small">
                        <i class="fas fa-bolt text-warning me-1"></i> <strong><?php echo $todayBookings; ?> New Bookings</strong> Today
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ================= PENDING NOTIFICATION ALERT (IF ANY) ================= -->
<?php if ($totalPending > 0): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-warning border-warning shadow-sm rounded-4 p-3 d-flex align-items-center justify-content-between flex-wrap gap-2" style="background: rgba(254, 243, 199, 0.9); backdrop-filter: blur(10px);">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-warning text-dark rounded-circle p-2 px-3 fw-bold fs-5 shadow-sm">
                    <i class="fas fa-bell"></i>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold text-dark">
                        ⚠️ <?php echo $totalPending; ?> Booking<?php echo $totalPending > 1 ? 's are' : ' is'; ?> Waiting for Your Approval!
                    </h6>
                    <small class="text-muted">
                        Customers receive a 60-minute doorstep arrival notice once approved. Please review and assign technician time.
                    </small>
                </div>
            </div>
            <a href="bookings.php" class="btn btn-warning rounded-pill px-4 py-2 fw-bold text-dark shadow-sm">
                <i class="fas fa-check-circle me-1"></i> Review Pending Bookings
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ================= 4 CORE METRIC CARDS ================= -->
<div class="row g-3 mb-4">

    <!-- Total Revenue Card -->
    <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
        <div class="glass-card stat-metric-card stat-green">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <span class="stat-label">Total Revenue</span>
                    <div class="stat-number mt-1">
                        ₹ <span class="counter-val" data-target="<?php echo $totalRevenue; ?>"><?php echo number_format($totalRevenue); ?></span>
                    </div>
                </div>
                <div class="stat-icon-bubble">
                    <i class="fas fa-wallet"></i>
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                <span class="stat-badge bg-success-subtle text-success">
                    <i class="fas fa-arrow-up"></i> ₹ <?php echo number_format($todayRevenue); ?> Today
                </span>
                <a href="total_revenue.php" class="small fw-bold text-success text-decoration-none">
                    Details <i class="fas fa-chevron-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Total Bookings Card -->
    <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
        <div class="glass-card stat-metric-card stat-blue">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <span class="stat-label">Total Bookings</span>
                    <div class="stat-number mt-1">
                        <span class="counter-val" data-target="<?php echo $totalBookings; ?>"><?php echo $totalBookings; ?></span>
                    </div>
                </div>
                <div class="stat-icon-bubble">
                    <i class="fas fa-calendar-check"></i>
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                <span class="stat-badge bg-primary-subtle text-primary">
                    <i class="fas fa-check-double"></i> <?php echo $totalApproved; ?> Approved (<?php echo $approvalRate; ?>%)
                </span>
                <a href="total_bookings.php" class="small fw-bold text-primary text-decoration-none">
                    View All <i class="fas fa-chevron-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Total Users Card -->
    <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
        <div class="glass-card stat-metric-card stat-purple">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <span class="stat-label">Registered Users</span>
                    <div class="stat-number mt-1">
                        <span class="counter-val" data-target="<?php echo $total_users; ?>"><?php echo $total_users; ?></span>
                    </div>
                </div>
                <div class="stat-icon-bubble">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                <span class="stat-badge bg-purple-subtle text-purple" style="background:#ede9fe; color:#6d28d9;">
                    <i class="fas fa-user-plus"></i> +<?php echo $newUsersThisWeek; ?> This Week
                </span>
                <a href="total_users.php" class="small fw-bold text-purple text-decoration-none" style="color:#6d28d9;">
                    Manage <i class="fas fa-chevron-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Pending / Action Card -->
    <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
        <div class="glass-card stat-metric-card stat-amber">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <span class="stat-label">Pending Approval</span>
                    <div class="stat-number mt-1 text-warning">
                        <span class="counter-val" data-target="<?php echo $totalPending; ?>"><?php echo $totalPending; ?></span>
                    </div>
                </div>
                <div class="stat-icon-bubble">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                <span class="stat-badge bg-warning-subtle text-warning">
                    <i class="fas fa-times-circle"></i> <?php echo $totalRejected; ?> Rejected
                </span>
                <a href="bookings.php" class="small fw-bold text-warning text-decoration-none">
                    Review Now <i class="fas fa-chevron-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>

</div>

<!-- ================= 2 COLUMN DYNAMIC CHARTS ================= -->
<div class="row mb-4">

    <!-- Monthly Bookings & Revenue Trend Chart (Area Chart) -->
    <div class="col-lg-8 mb-4">
        <div class="glass-card card h-100 p-3">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <div>
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-chart-line text-primary me-2"></i> Bookings & Revenue Growth
                    </h5>
                    <small class="text-muted">Monthly performance over the last 6 months</small>
                </div>
                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 fw-bold">
                    <i class="fas fa-signal me-1"></i> Live Analytics
                </span>
            </div>

            <div class="chart-container-box">
                <canvas id="monthlyTrendChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Booking Status & Conversion Donut -->
    <div class="col-lg-4 mb-4">
        <div class="glass-card card h-100 p-3">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <div>
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-chart-pie text-success me-2"></i> Booking Status
                    </h5>
                    <small class="text-muted">Approval & Conversion Rate</small>
                </div>
                <span class="badge bg-success rounded-pill px-2 py-1"><?php echo $approvalRate; ?>% Rate</span>
            </div>

            <div class="chart-container-box" style="height: 220px;">
                <canvas id="statusDonutChart"></canvas>
            </div>

            <!-- Custom Status Legend Cards -->
            <div class="row g-2 mt-2 pt-2 border-top text-center">
                <div class="col-4">
                    <small class="text-muted d-block">Approved</small>
                    <strong class="text-success"><?php echo $totalApproved; ?></strong>
                </div>
                <div class="col-4">
                    <small class="text-muted d-block">Pending</small>
                    <strong class="text-warning"><?php echo $totalPending; ?></strong>
                </div>
                <div class="col-4">
                    <small class="text-muted d-block">Rejected</small>
                    <strong class="text-danger"><?php echo $totalRejected; ?></strong>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ================= SECONDARY CHARTS & KPI ROW ================= -->
<div class="row mb-4">

    <!-- Top Services Breakdown (Doughnut) -->
    <div class="col-lg-6 mb-4">
        <div class="glass-card card h-100 p-3">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <div>
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-tools text-info me-2"></i> Popular Services Distribution
                    </h5>
                    <small class="text-muted">Top requested AC services</small>
                </div>
                <span class="badge bg-info-subtle text-info rounded-pill px-2 py-1">
                    Top: <?php echo htmlspecialchars($topServiceName); ?>
                </span>
            </div>
            <div class="chart-container-box">
                <canvas id="servicesChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Top AC Brands Market Share (Bar Chart) -->
    <div class="col-lg-6 mb-4">
        <div class="glass-card card h-100 p-3">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <div>
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-snowflake text-primary me-2"></i> AC Brands Market Share
                    </h5>
                    <small class="text-muted">Brand volume comparison</small>
                </div>
                <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-1">
                    Top: <?php echo htmlspecialchars($topBrandName); ?>
                </span>
            </div>
            <div class="chart-container-box">
                <canvas id="brandsChart"></canvas>
            </div>
        </div>
    </div>

</div>

<!-- ================= PRICE COMPARISON LINE CHART ================= -->
<div class="row mb-4">
    <div class="col-12">
        <div class="glass-card card p-3">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-tags text-warning me-2"></i> Original Price vs Discount Price Comparison
                    </h5>
                    <small class="text-muted">Monthly average comparison — Original Price <span style="color:#f59e0b;">●</span> vs Discount/Final Price <span style="color:#10b981;">●</span> (last 6 months)</small>
                </div>
                <span class="badge rounded-pill px-3 py-2 fw-bold" style="background: linear-gradient(135deg,#fef3c7,#fde68a); color:#92400e;">
                    <i class="fas fa-percent me-1"></i> Price Difference Analysis
                </span>
            </div>

            <div class="chart-container-box" style="height: 300px;">
                <canvas id="priceComparisonChart"></canvas>
            </div>

            <!-- Summary row below chart -->
            <div class="row g-3 mt-2 pt-2 border-top">
                <div class="col-md-4 text-center">
                    <small class="text-muted d-block fw-semibold">Avg. Original Price</small>
                    <strong class="text-warning fs-6">₹<?php echo number_format(array_sum($monthlyOriginalPrice) / max(count(array_filter($monthlyOriginalPrice)), 1), 2); ?></strong>
                </div>
                <div class="col-md-4 text-center">
                    <strong class="text-muted d-block fw-semibold">Avg. Discount Price</strong>
                    <strong class="text-success fs-6">₹<?php echo number_format(array_sum($monthlyDiscountPrice) / max(count(array_filter($monthlyDiscountPrice)), 1), 2); ?></strong>
                </div>
                <div class="col-md-4 text-center">
                    <small class="text-muted d-block fw-semibold">Avg. Savings</small>
                    <?php
                        $avgOrig = array_sum($monthlyOriginalPrice) / max(count(array_filter($monthlyOriginalPrice)), 1);
                        $avgDisc = array_sum($monthlyDiscountPrice) / max(count(array_filter($monthlyDiscountPrice)), 1);
                        $savings = $avgOrig - $avgDisc;
                        $savingsPct = $avgOrig > 0 ? round(($savings / $avgOrig) * 100, 1) : 0;
                    ?>
                    <strong class="text-primary fs-6">₹<?php echo number_format($savings, 2); ?> <span class="badge bg-success-subtle text-success" style="font-size:11px;"><?php echo $savingsPct; ?>% off</span></strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ================= RECENT BOOKINGS LIVE TABLE & QUICK ACTIVITY ================= -->
<div class="row mb-4">

    <!-- Recent Bookings Table -->
    <div class="col-lg-8 mb-4">
        <div class="glass-card card h-100 p-3">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-receipt text-primary me-2"></i> Recent Service Bookings
                    </h5>
                    <small class="text-muted">Latest customer requests with real-time status</small>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <input type="text" id="bookingLiveSearch" class="form-control form-control-sm table-search-input" placeholder="🔍 Search customer or service...">
                    <a href="bookings.php" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold">
                        View All
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="dynamic-table align-middle" id="recentBookingsTable">
                    <thead>
                        <tr>
                            <th>Ref #</th>
                            <th>Customer</th>
                            <th>Service & Brand</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recentBookings && mysqli_num_rows($recentBookings) > 0): ?>
                            <?php while ($bk = mysqli_fetch_assoc($recentBookings)): 
                                $bk_name = trim($bk['first_name'] . ' ' . $bk['last_name']);
                                $initials = strtoupper(substr($bk['first_name'] ?? 'U', 0, 1) . substr($bk['last_name'] ?? 'S', 0, 1));
                                $status = $bk['status'];
                                $badgeClass = 'bg-warning text-dark';
                                if ($status === 'Approved') $badgeClass = 'bg-success';
                                elseif ($status === 'Rejected') $badgeClass = 'bg-danger';

                                $final_price = isset($bk['final_price']) && $bk['final_price'] > 0 ? (float)$bk['final_price'] : (float)$bk['price'];
                            ?>
                            <tr>
                                <td>
                                    <strong class="text-primary">#<?php echo $bk['id']; ?></strong><br>
                                    <small class="text-muted" style="font-size:11px;"><?php echo date('d M, h:i A', strtotime($bk['created_at'])); ?></small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="user-avatar-circle"><?php echo $initials; ?></div>
                                        <div>
                                            <strong class="d-block text-dark mb-1"><?php echo htmlspecialchars($bk_name); ?></strong>
                                            <small class="text-muted d-flex align-items-center gap-2"><i class="fas fa-phone-alt text-primary" style="font-size: 11px;"></i> <?php echo htmlspecialchars($bk['mobile']); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-1 mb-1">
                                        <?php echo htmlspecialchars($bk['service_type']); ?>
                                    </span>
                                    <small class="d-block text-muted">Brand: <strong><?php echo htmlspecialchars($bk['company_type']); ?></strong></small>
                                </td>
                                <td>
                                    <strong class="text-success fs-6">₹ <?php echo number_format($final_price); ?></strong>
                                    <?php if (!empty($bk['coupon_code'])): ?>
                                        <br><span class="badge bg-light text-success border border-success-subtle" style="font-size: 10px;">🎟️ <?php echo htmlspecialchars($bk['coupon_code']); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo $badgeClass; ?> rounded-pill px-3 py-1">
                                        <?php echo $status; ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="booking_view.php?id=<?php echo $bk['id']; ?>" class="btn btn-outline-primary rounded-pill px-2" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($status === 'Pending'): ?>
                                            <a href="booking_approve.php?id=<?php echo $bk['id']; ?>" class="btn btn-success rounded-pill px-2 ms-1" title="Approve Booking">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        <?php elseif ($status === 'Approved'): ?>
                                            <a href="receipt.php?id=<?php echo $bk['id']; ?>" class="btn btn-outline-success rounded-pill px-2 ms-1" title="Print Receipt">
                                                <i class="fas fa-print"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="fas fa-calendar-times fs-2 mb-2 d-block text-muted"></i>
                                    No bookings recorded yet.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Sidebar: Recent Users & System Quick Info -->
    <div class="col-lg-4 mb-4">
        
        <!-- Recent Users Card -->
        <div class="glass-card card p-3 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="fas fa-user-friends text-purple me-2" style="color:#7c3aed;"></i> Recent Customers
                </h5>
                <a href="users.php" class="small fw-bold text-primary">View All</a>
            </div>

            <div class="list-group list-group-flush">
                <?php if ($recentUsers && mysqli_num_rows($recentUsers) > 0): ?>
                    <?php while ($u = mysqli_fetch_assoc($recentUsers)): 
                        $uInit = strtoupper(substr($u['name'] ?? 'U', 0, 2));
                    ?>
                    <div class="list-group-item px-0 py-2 border-0 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <div class="user-avatar-circle" style="background: linear-gradient(135deg, #a855f7, #6366f1);"><?php echo $uInit; ?></div>
                            <div>
                                <strong class="d-block text-dark small"><?php echo htmlspecialchars($u['name']); ?></strong>
                                <small class="text-muted" style="font-size: 11px;"><?php echo htmlspecialchars($u['email']); ?></small>
                            </div>
                        </div>
                        <small class="text-muted" style="font-size: 11px;">
                            <?php echo date('d M', strtotime($u['created_at'])); ?>
                        </small>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-muted small text-center my-3">No registered customers yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Administration Hub Card -->
        <div class="glass-card card p-3">
            <h5 class="fw-bold mb-3 pb-2 border-bottom text-dark">
                <i class="fas fa-sliders-h text-primary me-2"></i> Admin Quick Hub
            </h5>

            <div class="d-grid gap-2">
                <a href="create_admin.php" class="btn btn-outline-primary btn-sm rounded-pill text-start px-3 py-2">
                    <i class="fas fa-user-plus me-2 text-primary"></i> Create New Administrator
                </a>
                <a href="manage_terms.php" class="btn btn-outline-info btn-sm rounded-pill text-start px-3 py-2">
                    <i class="fas fa-file-contract me-2 text-info"></i> Edit Terms & Conditions
                </a>
                <a href="revenue_pdf.php" target="_blank" class="btn btn-outline-success btn-sm rounded-pill text-start px-3 py-2">
                    <i class="fas fa-file-invoice-dollar me-2 text-success"></i> Download Full Revenue Report
                </a>
                <a href="admin_logout.php" class="btn btn-outline-danger btn-sm rounded-pill text-start px-3 py-2 btn-confirm-action" data-title="Logout?" data-text="Are you sure you want to end your administrator session?">
                    <i class="fas fa-sign-out-alt me-2 text-danger"></i> Sign Out Session
                </a>
            </div>
        </div>

    </div>

</div>

<!-- Include Chart.js CDN for dynamic charts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// ================= 1. DYNAMIC ANIMATED BACKGROUND CANVAS ================= //
(function initDynamicBackground() {
    const canvas = document.getElementById('dynamicBgCanvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');

    let width, height;
    let particles = [];
    const particleCount = 45;

    function resize() {
        width = canvas.width = window.innerWidth;
        height = canvas.height = window.innerHeight;
    }
    window.addEventListener('resize', resize);
    resize();

    class Particle {
        constructor() {
            this.x = Math.random() * width;
            this.y = Math.random() * height;
            this.radius = Math.random() * 2.5 + 1;
            this.speedX = (Math.random() - 0.5) * 0.6;
            this.speedY = (Math.random() - 0.5) * 0.6;
            this.hue = Math.random() > 0.5 ? 205 : 190; // Sky blue / cyan tones
            this.alpha = Math.random() * 0.4 + 0.15;
        }

        update() {
            this.x += this.speedX;
            this.y += this.speedY;

            if (this.x < 0 || this.x > width) this.speedX *= -1;
            if (this.y < 0 || this.y > height) this.speedY *= -1;
        }

        draw() {
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
            ctx.fillStyle = `hsla(${this.hue}, 90%, 55%, ${this.alpha})`;
            ctx.shadowBlur = 10;
            ctx.shadowColor = `hsla(${this.hue}, 90%, 55%, 0.8)`;
            ctx.fill();
        }
    }

    for (let i = 0; i < particleCount; i++) {
        particles.push(new Particle());
    }

    function animate() {
        ctx.clearRect(0, 0, width, height);

        // Draw connecting nodes
        for (let i = 0; i < particles.length; i++) {
            for (let j = i + 1; j < particles.length; j++) {
                const dx = particles[i].x - particles[j].x;
                const dy = particles[i].y - particles[j].y;
                const dist = Math.sqrt(dx * dx + dy * dy);

                if (dist < 130) {
                    ctx.beginPath();
                    ctx.moveTo(particles[i].x, particles[i].y);
                    ctx.lineTo(particles[j].x, particles[j].y);
                    ctx.strokeStyle = `rgba(14, 165, 233, ${0.15 * (1 - dist / 130)})`;
                    ctx.lineWidth = 1;
                    ctx.stroke();
                }
            }
        }

        // Draw particles
        particles.forEach(p => {
            p.update();
            p.draw();
        });

        requestAnimationFrame(animate);
    }
    animate();
})();

// ================= 2. SMART DYNAMIC GREETING ================= //
function updateLiveGreeting() {
    const greetingElem = document.getElementById('greetingTitle');
    if (greetingElem) {
        const now = new Date();
        const hour = now.getHours();
        let greeting = "Good Evening";
        let emoji = "🌆";
        if (hour < 12) {
            greeting = "Good Morning";
            emoji = "🌅";
        } else if (hour < 17) {
            greeting = "Good Afternoon";
            emoji = "☀️";
        }
        greetingElem.innerHTML = `${greeting}, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Administrator'); ?>! ${emoji}`;
    }
}
updateLiveGreeting();

// ================= 3. STATS COUNTUP ANIMATION ================= //
document.addEventListener('DOMContentLoaded', () => {
    const counters = document.querySelectorAll('.counter-val');
    counters.forEach(counter => {
        const target = parseFloat(counter.getAttribute('data-target')) || 0;
        const duration = 1200;
        const startTime = performance.now();

        function updateCount(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            // Ease out cubic
            const easeOut = 1 - Math.pow(1 - progress, 3);
            const currentVal = Math.floor(easeOut * target);

            counter.innerText = currentVal.toLocaleString();

            if (progress < 1) {
                requestAnimationFrame(updateCount);
            } else {
                counter.innerText = target.toLocaleString();
            }
        }
        requestAnimationFrame(updateCount);
    });
});

// ================= 4. DYNAMIC CHARTS (CHART.JS) ================= //
document.addEventListener('DOMContentLoaded', () => {
    // 1. Monthly Area Chart
    const trendCtx = document.getElementById('monthlyTrendChart');
    if (trendCtx) {
        const gradientBookings = trendCtx.getContext('2d').createLinearGradient(0, 0, 0, 250);
        gradientBookings.addColorStop(0, 'rgba(14, 165, 233, 0.45)');
        gradientBookings.addColorStop(1, 'rgba(14, 165, 233, 0.0)');

        const gradientRevenue = trendCtx.getContext('2d').createLinearGradient(0, 0, 0, 250);
        gradientRevenue.addColorStop(0, 'rgba(16, 185, 129, 0.4)');
        gradientRevenue.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($monthlyLabels); ?>,
                datasets: [
                    {
                        label: 'Approved Bookings',
                        data: <?php echo json_encode($monthlyApprovedCounts); ?>,
                        borderColor: '#0284c7',
                        backgroundColor: gradientBookings,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#0284c7',
                        pointBorderColor: '#ffffff',
                        pointHoverRadius: 7,
                        borderWidth: 3,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Revenue (₹)',
                        data: <?php echo json_encode($monthlyRevenue); ?>,
                        borderColor: '#10b981',
                        backgroundColor: gradientRevenue,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#10b981',
                        pointBorderColor: '#ffffff',
                        pointHoverRadius: 7,
                        borderWidth: 3,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top', labels: { usePointStyle: true, font: { weight: 'bold' } } }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        title: { display: true, text: 'Bookings Count' }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        beginAtZero: true,
                        grid: { drawOnChartArea: false },
                        title: { display: true, text: 'Revenue (₹)' }
                    }
                }
            }
        });
    }

    // 2. Status Donut Chart
    const statusCtx = document.getElementById('statusDonutChart');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Approved', 'Pending', 'Rejected'],
                datasets: [{
                    data: [<?php echo $totalApproved; ?>, <?php echo $totalPending; ?>, <?php echo $totalRejected; ?>],
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                    borderWidth: 3,
                    borderColor: '#ffffff',
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }

    // 3. Services Distribution Doughnut
    const servicesCtx = document.getElementById('servicesChart');
    if (servicesCtx) {
        new Chart(servicesCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(!empty($serviceLabels) ? $serviceLabels : ['AC Installation', 'AC Repair', 'Gas Refilling', 'AC Cleaning']); ?>,
                datasets: [{
                    data: <?php echo json_encode(!empty($serviceCounts) ? $serviceCounts : [10, 8, 5, 4]); ?>,
                    backgroundColor: ['#0284c7', '#06b6d4', '#3b82f6', '#8b5cf6', '#10b981', '#f59e0b'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 } } }
                }
            }
        });
    }

    // 4. AC Brands Bar Chart
    const brandsCtx = document.getElementById('brandsChart');
    if (brandsCtx) {
        new Chart(brandsCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(!empty($brandLabels) ? $brandLabels : ['Voltas', 'Daikin', 'LG', 'Samsung', 'Blue Star', 'Hitachi']); ?>,
                datasets: [{
                    label: 'Bookings',
                    data: <?php echo json_encode(!empty($brandCounts) ? $brandCounts : [12, 9, 8, 6, 5, 3]); ?>,
                    backgroundColor: 'rgba(2, 132, 199, 0.75)',
                    borderColor: '#0284c7',
                    borderRadius: 8,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // 5. Price Comparison Line Chart (Original Price vs Discount Price)
    const priceCompCtx = document.getElementById('priceComparisonChart');
    if (priceCompCtx) {
        const ctx2d = priceCompCtx.getContext('2d');

        const gradOrig = ctx2d.createLinearGradient(0, 0, 0, 300);
        gradOrig.addColorStop(0, 'rgba(245, 158, 11, 0.40)');
        gradOrig.addColorStop(1, 'rgba(245, 158, 11, 0.02)');

        const gradDisc = ctx2d.createLinearGradient(0, 0, 0, 300);
        gradDisc.addColorStop(0, 'rgba(16, 185, 129, 0.40)');
        gradDisc.addColorStop(1, 'rgba(16, 185, 129, 0.02)');

        new Chart(priceCompCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($monthlyLabels); ?>,
                datasets: [
                    {
                        label: 'Original Price (₹)',
                        data: <?php echo json_encode($monthlyOriginalPrice); ?>,
                        borderColor: '#f59e0b',
                        backgroundColor: gradOrig,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#f59e0b',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 9,
                        borderWidth: 3
                    },
                    {
                        label: 'Discount / Final Price (₹)',
                        data: <?php echo json_encode($monthlyDiscountPrice); ?>,
                        borderColor: '#10b981',
                        backgroundColor: gradDisc,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#10b981',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 9,
                        borderWidth: 3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            pointStyleWidth: 14,
                            font: { weight: '700', size: 13 },
                            padding: 20
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ' ' + context.dataset.label + ': ₹' + context.parsed.y.toLocaleString('en-IN', { minimumFractionDigits: 2 });
                            },
                            afterBody: function(tooltipItems) {
                                const orig = tooltipItems.find(t => t.datasetIndex === 0);
                                const disc = tooltipItems.find(t => t.datasetIndex === 1);
                                if (orig && disc) {
                                    const diff = orig.parsed.y - disc.parsed.y;
                                    const pct  = orig.parsed.y > 0 ? ((diff / orig.parsed.y) * 100).toFixed(1) : 0;
                                    return diff > 0
                                        ? ['', ' 💰 Savings: ₹' + diff.toLocaleString('en-IN', {minimumFractionDigits:2}) + ' (' + pct + '% off)']
                                        : [];
                                }
                                return [];
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { weight: '600' } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        title: { display: true, text: 'Average Price (₹)', font: { weight: '700' } },
                        ticks: {
                            callback: function(val) {
                                return '₹' + val.toLocaleString('en-IN');
                            }
                        }
                    }
                }
            }
        });
    }
});

// ================= 5. LIVE SEARCH FILTER ON RECENT BOOKINGS ================= //
document.getElementById('bookingLiveSearch')?.addEventListener('keyup', function() {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('#recentBookingsTable tbody tr');

    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
