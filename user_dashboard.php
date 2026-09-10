<?php
require_once 'includes/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Valued Customer';
$user_email = $_SESSION['user_email'] ?? '';

// Total Bookings
$total = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM bookings WHERE user_id='$user_id'"))['total'] ?? 0;

// Pending
$pending = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM bookings WHERE user_id='$user_id' AND status='Pending'"))['total'] ?? 0;

// Approved
$approved = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM bookings WHERE user_id='$user_id' AND status='Approved'"))['total'] ?? 0;

// Completed
$completed = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM bookings WHERE user_id='$user_id' AND status='Completed'"))['total'] ?? 0;

// Rejected
$rejected = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM bookings WHERE user_id='$user_id' AND status='Rejected'"))['total'] ?? 0;

// Recent 6 Bookings
$recent = mysqli_query($conn,
"SELECT * FROM bookings
WHERE user_id='$user_id'
ORDER BY id DESC
LIMIT 6");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard | Aqua Air Cooling</title>
    
    <!-- Google Fonts & Font Awesome -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>

    <style>
        :root {
            --brand-primary: #0284c7;
            --brand-dark: #06164f;
            --brand-accent: #ff7f0e;
            --bg-canvas: #f8fafc;
            --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background: #f1f5f9;
            color: #1e293b;
            min-height: 100vh;
        }

        /* Top Header Navbar */
        .user-topbar {
            background: linear-gradient(135deg, #040e36 0%, #06164f 100%);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }

        .brand-logo-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #ffffff;
        }

        .brand-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: linear-gradient(135deg, #0284c7, #2563eb);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.4);
        }

        .brand-name {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 19px;
            letter-spacing: -0.3px;
        }

        .nav-btn {
            border-radius: 10px;
            font-weight: 600;
            font-size: 13.5px;
            padding: 8px 16px;
            transition: all 0.25s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-nav-light {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        .btn-nav-light:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #38bdf8;
            transform: translateY(-1px);
        }

        .btn-nav-book {
            background: linear-gradient(90deg, #ff7f0e, #e06b00);
            color: #ffffff;
            border: none;
            box-shadow: 0 4px 14px rgba(255, 127, 14, 0.35);
        }

        .btn-nav-book:hover {
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(255, 127, 14, 0.5);
        }

        /* Hero Welcome Banner */
        .welcome-hero {
            background: linear-gradient(135deg, #06164f 0%, #0c257a 50%, #0284c7 100%);
            border-radius: 24px;
            padding: 32px 36px;
            color: #ffffff;
            position: relative;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(6, 22, 79, 0.25);
            margin-bottom: 32px;
        }

        .welcome-hero::after {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 240px;
            height: 240px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
            pointer-events: none;
        }

        .user-avatar-pill {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #38bdf8, #0284c7);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 800;
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(56, 189, 248, 0.4);
            border: 3px solid rgba(255, 255, 255, 0.2);
            flex-shrink: 0;
        }

        .live-clock-badge {
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        /* Dynamic Stats Cards */
        .stat-card-dynamic {
            background: #ffffff;
            border-radius: 20px;
            padding: 24px 20px;
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: var(--card-shadow);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .stat-card-dynamic:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
        }

        .stat-card-dynamic::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
        }

        .stat-card-dynamic.blue::before { background: #0284c7; }
        .stat-card-dynamic.amber::before { background: #f59e0b; }
        .stat-card-dynamic.cyan::before { background: #06b6d4; }
        .stat-card-dynamic.green::before { background: #10b981; }

        .stat-icon-wrap {
            width: 54px;
            height: 54px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }

        .stat-icon-wrap.blue { background: #e0f2fe; color: #0284c7; }
        .stat-icon-wrap.amber { background: #fef3c7; color: #d97706; }
        .stat-icon-wrap.cyan { background: #cffafe; color: #0891b2; }
        .stat-icon-wrap.green { background: #d1fae5; color: #059669; }

        .stat-number {
            font-family: 'Outfit', sans-serif;
            font-size: 28px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.1;
        }

        .stat-label {
            color: #64748b;
            font-size: 13.5px;
            font-weight: 600;
            margin-top: 2px;
        }

        /* Glassmorphism Section Card */
        .dashboard-main-card {
            background: #ffffff;
            border-radius: 24px;
            border: 1px solid #e2e8f0;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .card-header-custom {
            padding: 20px 28px;
            background: #ffffff;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        .card-header-title {
            font-family: 'Outfit', sans-serif;
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
        }

        /* Quick Service Shortcut Badges */
        .service-shortcut-btn {
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 16px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 14px;
            text-decoration: none;
            color: #1e293b;
            transition: all 0.25s ease;
        }

        .service-shortcut-btn:hover {
            border-color: #0284c7;
            background: #f0f9ff;
            color: #0284c7;
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(2, 132, 199, 0.12);
        }

        .shortcut-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            flex-shrink: 0;
        }

        /* Recent Bookings Table */
        .table-custom-responsive {
            margin: 0;
        }

        .table-custom-responsive th {
            background: #f8fafc;
            color: #475569;
            font-weight: 700;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 14px 20px;
            border-top: none;
            border-bottom: 1.5px solid #e2e8f0;
        }

        .table-custom-responsive td {
            padding: 16px 20px;
            vertical-align: middle;
            color: #1e293b;
            font-weight: 600;
            font-size: 14px;
            border-bottom: 1px solid #f1f5f9;
        }

        .table-custom-responsive tr:hover td {
            background: #f8fafc;
        }

        .status-badge-dynamic {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12.5px;
            font-weight: 700;
        }

        .status-badge-dynamic.pending { background: #fef3c7; color: #b45309; }
        .status-badge-dynamic.approved { background: #e0f2fe; color: #0369a1; }
        .status-badge-dynamic.completed { background: #d1fae5; color: #047857; }
        .status-badge-dynamic.rejected { background: #fee2e2; color: #b91c1c; }

        .btn-table-action {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 13px;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-table-action:hover {
            color: #ffffff;
            transform: scale(1.1);
        }

        /* Mobile Responsive Media Queries */
        @media (max-width: 768px) {
            .brand-name {
                font-size: 15px;
            }
            .brand-icon {
                width: 32px;
                height: 32px;
                font-size: 15px;
                border-radius: 9px;
            }
            .nav-btn {
                padding: 6px 10px;
                font-size: 12px;
                border-radius: 8px;
            }
            .welcome-hero {
                padding: 20px 18px;
                border-radius: 18px;
                margin-bottom: 20px;
            }
            .user-avatar-pill {
                width: 46px;
                height: 46px;
                font-size: 20px;
            }
            .welcome-hero h2 {
                font-size: 20px !important;
            }
            .stat-card-dynamic {
                padding: 14px 12px;
                border-radius: 16px;
                gap: 10px;
            }
            .stat-icon-wrap {
                width: 40px;
                height: 40px;
                border-radius: 12px;
                font-size: 17px;
            }
            .stat-number {
                font-size: 20px;
            }
            .stat-label {
                font-size: 11.5px;
            }
            .dashboard-main-card {
                border-radius: 18px;
            }
            .card-header-custom {
                padding: 16px 18px;
            }
            .table-custom-responsive {
                min-width: 650px;
            }
            .table-responsive {
                -webkit-overflow-scrolling: touch;
            }
        }
    </style>
</head>
<body>

    <!-- Sticky Topbar Navigation -->
    <header class="user-topbar py-2">
        <div class="container d-flex justify-content-between align-items-center">
            
            <a href="index.php" class="brand-logo-wrap">
                <div class="brand-icon">
                    <i class="fas fa-snowflake"></i>
                </div>
                <div>
                    <div class="brand-name">Aqua Air Cooling</div>
                    <small style="font-size: 10px; color: #94a3b8; display: block; margin-top: -3px;">Customer Portal</small>
                </div>
            </a>

            <div class="d-flex align-items-center gap-1 gap-sm-2">
                <a href="index.php" class="nav-btn btn-nav-light" title="Home Page">
                    <i class="fas fa-house"></i> <span>Home</span>
                </a>
                <a href="my_bookings.php" class="nav-btn btn-nav-light" title="My Bookings">
                    <i class="fas fa-list-check"></i> <span class="d-none d-sm-inline">My Bookings</span><span class="d-inline d-sm-none">Bookings</span>
                </a>
                <a href="booking.php" class="nav-btn btn-nav-book" title="Book New Service">
                    <i class="fas fa-plus"></i> <span class="d-none d-sm-inline">Book Service</span><span class="d-inline d-sm-none">Book</span>
                </a>
                <a href="logout.php" class="nav-btn btn-outline-danger text-white ms-1" style="background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.4);" title="Sign Out">
                    <i class="fas fa-power-off"></i>
                </a>
            </div>

        </div>
    </header>

    <main class="container py-4">

        <!-- Welcome Hero Banner -->
        <div class="welcome-hero">
            <div class="row align-items-center">
                <div class="col-lg-8 col-md-7 d-flex align-items-center gap-3">
                    <div class="user-avatar-pill">
                        <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                    </div>
                    <div>
                        <div class="live-clock-badge mb-2">
                            <i class="far fa-calendar-alt text-warning"></i>
                            <span id="currentDateDisplay">Loading Date...</span>
                            <span class="mx-1">•</span>
                            <i class="far fa-clock text-info"></i>
                            <span id="currentTimeDisplay">--:--</span>
                        </div>
                        <h2 class="mb-1 fw-bold" style="font-family:'Outfit',sans-serif; font-size:26px;">
                            <span id="greetingTime">Welcome</span>, <?php echo htmlspecialchars($user_name); ?>! 👋
                        </h2>
                        <p class="mb-0 text-white-50" style="font-size:14px;">
                            Manage your doorstep AC service appointments, track status & download invoices.
                        </p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-5 text-md-end mt-3 mt-md-0">
                    <a href="booking.php" class="btn btn-warning fw-bold px-4 py-2 w-100 w-md-auto" style="border-radius:12px; box-shadow: 0 6px 18px rgba(255,127,14,0.4);">
                        <i class="fas fa-calendar-plus me-1"></i> Book New Service
                    </a>
                </div>
            </div>
        </div>

        <!-- 4 Stats Cards Grid -->
        <div class="row g-2 g-sm-3 mb-4">
            
            <div class="col-6 col-xl-3">
                <div class="stat-card-dynamic blue">
                    <div class="stat-icon-wrap blue">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div>
                        <div class="stat-number"><?php echo $total; ?></div>
                        <div class="stat-label">Total Bookings</div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="stat-card-dynamic amber">
                    <div class="stat-icon-wrap amber">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div>
                        <div class="stat-number"><?php echo $pending; ?></div>
                        <div class="stat-label">Pending Approval</div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="stat-card-dynamic cyan">
                    <div class="stat-icon-wrap cyan">
                        <i class="fas fa-screwdriver-wrench"></i>
                    </div>
                    <div>
                        <div class="stat-number"><?php echo $approved; ?></div>
                        <div class="stat-label">Approved / Active</div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="stat-card-dynamic green">
                    <div class="stat-icon-wrap green">
                        <i class="fas fa-circle-check"></i>
                    </div>
                    <div>
                        <div class="stat-number"><?php echo $completed; ?></div>
                        <div class="stat-label">Completed & Paid</div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Quick Service Booking Short-cuts -->
        <div class="dashboard-main-card">
            <div class="card-header-custom">
                <h3 class="card-header-title">
                    <i class="fas fa-bolt text-warning"></i> Quick Service Booking
                </h3>
                <span class="text-muted" style="font-size: 13px;">Book instant doorstep AC services</span>
            </div>
            <div class="p-4">
                <div class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <a href="booking.php" class="service-shortcut-btn">
                            <div class="shortcut-icon text-primary">
                                <i class="fas fa-tools"></i>
                            </div>
                            <div>
                                <div class="fw-bold" style="font-size:14.5px;">AC Installation</div>
                                <small class="text-muted">Standard Rate: ₹ 1,200</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <a href="booking.php" class="service-shortcut-btn">
                            <div class="shortcut-icon text-danger">
                                <i class="fas fa-wrench"></i>
                            </div>
                            <div>
                                <div class="fw-bold" style="font-size:14.5px;">AC Repair</div>
                                <small class="text-muted">Standard Rate: ₹ 900</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <a href="booking.php" class="service-shortcut-btn">
                            <div class="shortcut-icon text-info">
                                <i class="fas fa-wind"></i>
                            </div>
                            <div>
                                <div class="fw-bold" style="font-size:14.5px;">Gas Refilling</div>
                                <small class="text-muted">Standard Rate: ₹ 3,250</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <a href="booking.php" class="service-shortcut-btn">
                            <div class="shortcut-icon text-success">
                                <i class="fas fa-award"></i>
                            </div>
                            <div>
                                <div class="fw-bold" style="font-size:14.5px;">1-Year AMC Plan</div>
                                <small class="text-muted">Complete Care: ₹ 5,000</small>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Bookings Table -->
        <div class="dashboard-main-card">
            <div class="card-header-custom">
                <h3 class="card-header-title">
                    <i class="fas fa-clock-rotate-left text-primary"></i> Recent Service Requests
                </h3>
                <a href="my_bookings.php" class="btn btn-sm btn-outline-primary fw-bold" style="border-radius:8px;">
                    View All Bookings <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
            
            <div class="table-responsive">
                <table class="table table-custom-responsive align-middle">
                    <thead>
                        <tr>
                            <th># Booking ID</th>
                            <th>Service Requested</th>
                            <th>AC Brand</th>
                            <th>Original Parts</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recent && mysqli_num_rows($recent) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($recent)): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-light text-dark border px-2 py-1" style="font-family:'Outfit',sans-serif; font-size:13px;">
                                            #<?php echo $row['id']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['service_type']); ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1" style="font-size: 11.5px;">
                                            <?php echo htmlspecialchars($row['company_type']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($row['original_part']) && $row['original_part'] !== 'None (Service Only)' && $row['original_part'] !== 'None / General Service Only' && $row['original_part'] !== 'None'): ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1" style="font-size: 11.5px;">
                                                <i class="fas fa-cogs me-1"></i><?php echo htmlspecialchars($row['original_part']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted small">None (Service Only)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $display_price = !empty($row['final_price']) ? $row['final_price'] : $row['price'];
                                        ?>
                                        <span class="fw-bold text-dark" style="font-size: 14.5px;">
                                            ₹ <?php echo number_format($display_price, 2); ?>
                                        </span>
                                        <?php if (!empty($row['discount_amount']) && $row['discount_amount'] > 0): ?>
                                            <small class="badge bg-success-subtle text-success ms-1" style="font-size:10px;">
                                                <?php echo (int)$row['discount_percent']; ?>% OFF
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        if ($row['status'] == "Completed") {
                                            echo "<span class='status-badge-dynamic completed'><i class='fas fa-check-circle'></i> Completed</span>";
                                        } elseif ($row['status'] == "Approved") {
                                            echo "<span class='status-badge-dynamic approved'><i class='fas fa-screwdriver-wrench'></i> Approved</span>";
                                        } elseif ($row['status'] == "Pending") {
                                            echo "<span class='status-badge-dynamic pending'><i class='far fa-clock'></i> Pending</span>";
                                        } else {
                                            echo "<span class='status-badge-dynamic rejected'><i class='fas fa-circle-xmark'></i> Rejected</span>";
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo !empty($row['booking_date']) ? date('d M Y', strtotime($row['booking_date'])) : 'Today'; ?>
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($row['status'] == 'Approved' || $row['status'] == 'Completed'): ?>
                                            <a href="receipt.php?id=<?php echo $row['id']; ?>" class="btn-table-action bg-primary" title="View & Print Receipt">
                                                <i class="fas fa-file-invoice"></i>
                                            </a>
                                            <a href="user_receipt_pdf.php?id=<?php echo $row['id']; ?>" class="btn-table-action bg-danger ms-1" title="Download PDF Receipt">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">In Review</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="fas fa-calendar-xmark text-muted fa-3x mb-3 d-block"></i>
                                    <h5 class="fw-bold text-dark mb-1">No Bookings Yet</h5>
                                    <p class="text-muted small mb-3">You haven't requested any AC services yet.</p>
                                    <a href="booking.php" class="btn btn-sm btn-primary px-3 py-2" style="border-radius:10px;">
                                        <i class="fas fa-plus me-1"></i> Book Your First Service
                                    </a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <script>
    // Live Clock & Dynamic Greeting
    function updateClock() {
        const now = new Date();
        
        // Date
        const dateOptions = { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' };
        document.getElementById('currentDateDisplay').innerText = now.toLocaleDateString('en-US', dateOptions);
        
        // Time
        let hours = now.getHours();
        let minutes = now.getMinutes();
        let ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12;
        minutes = minutes < 10 ? '0' + minutes : minutes;
        document.getElementById('currentTimeDisplay').innerText = hours + ':' + minutes + ' ' + ampm;

        // Dynamic Greeting
        const greetingElem = document.getElementById('greetingTime');
        const currentHour = now.getHours();
        if (currentHour < 12) {
            greetingElem.innerText = 'Good Morning';
        } else if (currentHour < 17) {
            greetingElem.innerText = 'Good Afternoon';
        } else {
            greetingElem.innerText = 'Good Evening';
        }
    }

    updateClock();
    setInterval(updateClock, 1000);
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>