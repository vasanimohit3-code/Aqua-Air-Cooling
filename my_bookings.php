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
$user_name = $_SESSION['user_name'] ?? 'Customer';

$query = "SELECT * FROM bookings
          WHERE user_id='$user_id'
          ORDER BY id DESC";

$result = mysqli_query($conn, $query);
$total_bookings = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings History | Aqua Air Cooling</title>
    
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

        /* Page Banner */
        .page-header-card {
            background: linear-gradient(135deg, #06164f 0%, #0c257a 50%, #0284c7 100%);
            border-radius: 20px;
            padding: 24px 30px;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            box-shadow: 0 10px 30px rgba(6, 22, 79, 0.18);
            margin-bottom: 24px;
        }

        .page-title {
            font-family: 'Outfit', sans-serif;
            font-size: 24px;
            font-weight: 800;
            margin: 0;
        }

        /* Filter Controls */
        .controls-card {
            background: #ffffff;
            border-radius: 18px;
            padding: 16px 20px;
            border: 1px solid #e2e8f0;
            box-shadow: var(--card-shadow);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 14px;
        }

        .filter-pills-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .filter-pill {
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            color: #475569;
            font-size: 13px;
            font-weight: 600;
            padding: 6px 14px;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .filter-pill:hover, .filter-pill.active {
            background: #0284c7;
            color: #ffffff;
            border-color: #0284c7;
        }

        .search-box-wrap {
            position: relative;
            min-width: 250px;
        }

        .search-box-input {
            width: 100%;
            height: 40px;
            border-radius: 12px;
            border: 1.5px solid #cbd5e1;
            padding: 0 14px 0 38px;
            font-size: 13.5px;
            outline: none;
            transition: all 0.2s ease;
        }

        .search-box-input:focus {
            border-color: #0284c7;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
        }

        .search-icon-pos {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 14px;
        }

        /* Modern Table Card */
        .bookings-table-card {
            background: #ffffff;
            border-radius: 22px;
            border: 1px solid #e2e8f0;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .table-custom-responsive th {
            background: #f8fafc;
            color: #475569;
            font-weight: 700;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 14px 18px;
            border-top: none;
            border-bottom: 1.5px solid #e2e8f0;
        }

        .table-custom-responsive td {
            padding: 16px 18px;
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
            font-size: 12px;
            font-weight: 700;
        }

        .status-badge-dynamic.pending { background: #fef3c7; color: #b45309; }
        .status-badge-dynamic.approved { background: #e0f2fe; color: #0369a1; }
        .status-badge-dynamic.completed { background: #d1fae5; color: #047857; }
        .status-badge-dynamic.rejected { background: #fee2e2; color: #b91c1c; }

        .btn-action-view {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s ease;
        }

        .btn-action-view:hover {
            transform: translateY(-1px);
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
            .page-header-card {
                padding: 18px 16px;
                border-radius: 16px;
            }
            .page-title {
                font-size: 20px;
            }
            .controls-card {
                padding: 12px 14px;
                border-radius: 14px;
            }
            .filter-pills-wrap {
                width: 100%;
                overflow-x: auto;
                flex-wrap: nowrap;
                padding-bottom: 4px;
                -webkit-overflow-scrolling: touch;
            }
            .filter-pill {
                flex-shrink: 0;
                font-size: 12px;
                padding: 5px 11px;
            }
            .search-box-wrap {
                width: 100% !important;
                min-width: 0 !important;
            }
            .search-box-input {
                font-size: 16px;
            }
            .table-custom-responsive {
                min-width: 680px;
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
                <a href="user_dashboard.php" class="nav-btn btn-nav-light" title="Dashboard">
                    <i class="fas fa-arrow-left"></i> <span class="d-none d-sm-inline">Dashboard</span>
                </a>
                <a href="booking.php" class="nav-btn btn-nav-book">
                    <i class="fas fa-plus"></i> <span class="d-none d-sm-inline">New Booking</span><span class="d-inline d-sm-none">Book</span>
                </a>
                <a href="logout.php" class="nav-btn btn-outline-danger text-white ms-1" style="background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.4);" title="Sign Out">
                    <i class="fas fa-power-off"></i>
                </a>
            </div>

        </div>
    </header>

    <main class="container py-4">

        <!-- Page Header -->
        <div class="page-header-card">
            <div>
                <h1 class="page-title"><i class="fas fa-calendar-days me-2 text-warning"></i> My Bookings History</h1>
                <p class="mb-0 text-white-50 mt-1" style="font-size: 13.5px;">
                    Track doorstep status, technician schedules & download official receipts.
                </p>
            </div>
            <div>
                <a href="booking.php" class="btn btn-warning fw-bold px-3 py-2" style="border-radius:10px;">
                    <i class="fas fa-plus me-1"></i> Book New Service
                </a>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="controls-card">
            <div class="filter-pills-wrap">
                <span class="text-muted fw-bold small me-1"><i class="fas fa-filter"></i> Filter:</span>
                <button class="filter-pill active" onclick="filterTable('all', this)">All (<?php echo $total_bookings; ?>)</button>
                <button class="filter-pill" onclick="filterTable('Pending', this)">Pending</button>
                <button class="filter-pill" onclick="filterTable('Approved', this)">Approved</button>
                <button class="filter-pill" onclick="filterTable('Completed', this)">Completed</button>
                <button class="filter-pill" onclick="filterTable('Rejected', this)">Rejected</button>
            </div>

            <div class="search-box-wrap">
                <i class="fas fa-search search-icon-pos"></i>
                <input type="text" id="tableSearch" class="search-box-input" placeholder="Search service, company..." onkeyup="searchTable()">
            </div>
        </div>

        <!-- Bookings Table Card -->
        <div class="bookings-table-card">
            <div class="table-responsive">
                <table class="table table-custom-responsive align-middle" id="bookingsTable">
                    <thead>
                        <tr>
                            <th># Booking ID</th>
                            <th>Service Requested</th>
                            <th>AC Brand</th>
                            <th>Original Parts</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Scheduled Date</th>
                            <th class="text-center">Receipt & Invoice</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && mysqli_num_rows($result) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                                <tr data-status="<?php echo htmlspecialchars($row['status']); ?>">
                                    <td>
                                        <span class="badge bg-light text-dark border px-2 py-1" style="font-family:'Outfit',sans-serif; font-size:13px;">
                                            #<?php echo $row['id']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['service_type']); ?></div>
                                        <?php if (!empty($row['coupon_code'])): ?>
                                            <small class="text-primary"><i class="fas fa-tag"></i> Coupon: <?php echo htmlspecialchars($row['coupon_code']); ?></small>
                                        <?php endif; ?>
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
                                            <div style="font-size: 11px; color: #059669;">
                                                Saved ₹ <?php echo number_format($row['discount_amount'], 2); ?> (<?php echo (int)$row['discount_percent']; ?>%)
                                            </div>
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
                                        <div class="text-dark fw-bold" style="font-size:13px;">
                                            <?php echo !empty($row['booking_date']) ? date('d M Y', strtotime($row['booking_date'])) : 'Today'; ?>
                                        </div>
                                        <?php if (!empty($row['booking_time'])): ?>
                                            <small class="text-muted"><i class="far fa-clock me-1"></i><?php echo htmlspecialchars($row['booking_time']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($row['status'] == 'Approved' || $row['status'] == 'Completed'): ?>
                                            <a href="receipt.php?id=<?php echo $row['id']; ?>" class="btn-action-view btn-primary">
                                                <i class="fas fa-file-invoice"></i> View
                                            </a>
                                            <a href="user_receipt_pdf.php?id=<?php echo $row['id']; ?>" class="btn-action-view btn-danger ms-1">
                                                <i class="fas fa-file-pdf"></i> PDF
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary py-1 px-2" style="font-size:11.5px;">
                                                <i class="fas fa-lock me-1"></i> After Approval
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="fas fa-calendar-xmark text-muted fa-3x mb-3 d-block"></i>
                                    <h5 class="fw-bold text-dark mb-1">No Bookings Found</h5>
                                    <p class="text-muted small mb-3">You haven't requested any AC services yet.</p>
                                    <a href="booking.php" class="btn btn-primary px-4 py-2" style="border-radius:10px;">
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
    // Live Status Filter Pills
    function filterTable(status, pill) {
        document.querySelectorAll('.filter-pill').forEach(p => p.classList.remove('active'));
        pill.classList.add('active');

        const rows = document.querySelectorAll('#bookingsTable tbody tr');
        rows.forEach(row => {
            const rowStatus = row.getAttribute('data-status');
            if (!rowStatus) return; // Skip empty row
            if (status === 'all' || rowStatus.toLowerCase() === status.toLowerCase()) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Live Instant Search
    function searchTable() {
        const query = document.getElementById('tableSearch').value.toLowerCase();
        const rows = document.querySelectorAll('#bookingsTable tbody tr');
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            if (text.includes(query)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>