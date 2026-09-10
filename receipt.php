<?php
require_once 'includes/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: my_bookings.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$booking_id = (int)$_GET['id'];

$query = "SELECT * FROM bookings
          WHERE id='$booking_id'
          AND user_id='$user_id'";

$result = mysqli_query($conn, $query);

function sweetAlertAndRedirect($title, $text, $icon, $redirectUrl = 'my_bookings.php') {
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script><style>body{font-family:sans-serif;background:#0f172a;}</style></head><body>';
    echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            title: " . json_encode($title) . ",
            text: " . json_encode($text) . ",
            icon: " . json_encode($icon) . ",
            confirmButtonColor: '#0284c7',
            confirmButtonText: 'Back to Bookings'
        }).then(() => {
            window.location = " . json_encode($redirectUrl) . ";
        });
    });
    </script></body></html>";
    exit();
}

if (mysqli_num_rows($result) == 0) {
    sweetAlertAndRedirect('Receipt Not Found', 'The requested receipt could not be found.', 'error', 'my_bookings.php');
}

$row = mysqli_fetch_assoc($result);

/* User Wise Booking Number */
$countQuery = "SELECT id FROM bookings
               WHERE user_id='$user_id'
               AND id < '$booking_id'
               ORDER BY id ASC";

$countResult = mysqli_query($conn, $countQuery);
$booking_no = mysqli_num_rows($countResult) + 1;

/* Only Approved & Completed Bookings */
if ($row['status'] != "Approved" && $row['status'] != "Completed") {
    sweetAlertAndRedirect('Receipt Not Available', 'Receipt is available only after your booking has been approved or completed by the administrator.', 'info', 'my_bookings.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Invoice #<?php echo $row['id']; ?> | Aqua Air Cooling</title>
    
    <!-- Google Fonts & Font Awesome -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background: #f1f5f9;
            color: #1e293b;
            padding: 30px 16px;
            min-height: 100vh;
        }

        .receipt-card {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
            overflow: hidden;
            position: relative;
        }

        .receipt-header {
            background: linear-gradient(135deg, #040e36 0%, #06164f 60%, #0284c7 100%);
            color: #ffffff !important;
            padding: 32px 36px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
        }

        .receipt-title {
            font-family: 'Outfit', sans-serif;
            font-size: 28px;
            font-weight: 800;
            color: #ffffff !important;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
            letter-spacing: -0.5px;
            margin: 0;
            display: inline-block;
        }

        .receipt-subtitle {
            color: #e2e8f0 !important;
            font-size: 13.5px;
            font-weight: 500;
            display: block;
            margin-top: 4px;
        }

        .invoice-pill {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            padding: 6px 16px;
            border-radius: 20px;
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 14px;
        }

        .receipt-body {
            padding: 36px;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 20px;
        }

        .meta-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .meta-val {
            font-size: 14.5px;
            font-weight: 700;
            color: #0f172a;
        }

        .table-invoice {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 30px;
        }

        .table-invoice th {
            background: #06164f;
            color: #ffffff;
            font-weight: 700;
            font-size: 13px;
            padding: 12px 18px;
            text-transform: uppercase;
        }

        .table-invoice th:first-child { border-top-left-radius: 10px; }
        .table-invoice th:last-child { border-top-right-radius: 10px; }

        .table-invoice td {
            padding: 14px 18px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
            color: #1e293b;
        }

        .status-stamp {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 8px;
            font-weight: 800;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-stamp.paid {
            background: #dcfce7;
            color: #15803d;
            border: 1.5px solid #86efac;
        }

        .status-stamp.approved {
            background: #e0f2fe;
            color: #0369a1;
            border: 1.5px solid #7dd3fc;
        }

        .actions-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #e2e8f0;
        }

        @media print {
            body { background: #fff; padding: 0; }
            .receipt-card { box-shadow: none; border: none; max-width: 100%; }
            .actions-bar, .user-topbar { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="receipt-card">
        
        <!-- Receipt Top Header -->
        <div class="receipt-header">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="fas fa-snowflake fa-lg" style="color: #38bdf8; text-shadow: 0 0 10px rgba(56, 189, 248, 0.6);"></i>
                    <h1 class="receipt-title">Aqua Air Cooling</h1>
                </div>
                <span class="receipt-subtitle">Official Customer Invoice & Service Receipt</span>
            </div>
            <div>
                <span class="invoice-pill">
                    <i class="fas fa-file-lines me-1 text-warning"></i> INVOICE #<?php echo $row['id']; ?>
                </span>
            </div>
        </div>

        <div class="receipt-body">
            
            <!-- Metadata Cards -->
            <div class="meta-grid">
                <div>
                    <div class="meta-label">Customer Name</div>
                    <div class="meta-val"><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></div>
                </div>
                <div>
                    <div class="meta-label">Phone & Email</div>
                    <div class="meta-val"><?php echo htmlspecialchars($row['mobile']); ?></div>
                    <small class="text-muted" style="font-size:12px;"><?php echo htmlspecialchars($row['email']); ?></small>
                </div>
                <div>
                    <div class="meta-label">Service Date & Time</div>
                    <div class="meta-val"><?php echo !empty($row['booking_date']) ? date('d M Y', strtotime($row['booking_date'])) : date('d M Y', strtotime($row['created_at'])); ?></div>
                    <small class="text-muted" style="font-size:12px;"><?php echo htmlspecialchars($row['booking_time'] ?? 'Standard Visit'); ?></small>
                </div>
                <div>
                    <div class="meta-label">Service Address</div>
                    <div class="meta-val" style="font-size:13px;"><?php echo htmlspecialchars($row['address']); ?></div>
                </div>
            </div>

            <!-- Service Table Breakdown -->
            <table class="table-invoice">
                <thead>
                    <tr>
                        <th>Item / Service Description</th>
                        <th>AC Brand</th>
                        <th>Original Spare Part</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong class="text-dark"><?php echo htmlspecialchars($row['service_type']); ?></strong>
                            <div class="text-muted small">Standard doorstep AC service with professional engineer</div>
                        </td>
                        <td>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1"><?php echo htmlspecialchars($row['company_type']); ?></span>
                        </td>
                        <td>
                            <?php if (!empty($row['original_part']) && $row['original_part'] !== 'None (Service Only)' && $row['original_part'] !== 'None / General Service Only' && $row['original_part'] !== 'None'): ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fas fa-cogs me-1"></i><?php echo htmlspecialchars($row['original_part']); ?></span>
                            <?php else: ?>
                                <span class="badge bg-light text-muted border">None (Service Only)</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end fw-bold">
                            ₹ <?php echo number_format($row['price'], 2); ?>
                        </td>
                    </tr>
                    
                    <?php if (!empty($row['coupon_code']) && !empty($row['discount_amount']) && $row['discount_amount'] > 0): ?>
                    <tr>
                        <td colspan="3" class="text-end">
                            <span class="badge bg-success-subtle text-success border border-success me-1">
                                <i class="fas fa-tag"></i> Coupon <?php echo htmlspecialchars($row['coupon_code']); ?> (<?php echo (int)$row['discount_percent']; ?>% OFF)
                            </span>
                            <span class="text-muted fw-bold">Promo Discount:</span>
                        </td>
                        <td class="text-end text-danger fw-bold">
                            - ₹ <?php echo number_format($row['discount_amount'], 2); ?>
                        </td>
                    </tr>
                    <?php endif; ?>

                    <tr style="background:#f8fafc;">
                        <td colspan="3" class="text-end">
                            <strong class="text-dark" style="font-size: 16px;">Total Amount Payable / Paid:</strong>
                        </td>
                        <td class="text-end">
                            <?php $final_payable = !empty($row['final_price']) ? $row['final_price'] : $row['price']; ?>
                            <strong class="text-success" style="font-size: 19px; font-family:'Outfit',sans-serif;">
                                ₹ <?php echo number_format($final_payable, 2); ?>
                            </strong>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Status Banner & Notes -->
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 p-3 mb-4 rounded-3" style="background:#f8fafc; border:1px solid #e2e8f0;">
                <div>
                    <span class="meta-label d-block">Payment & Service Status</span>
                    <?php if ($row['status'] == 'Completed'): ?>
                        <span class="status-stamp paid">
                            <i class="fas fa-check-double me-1"></i> Paid & Completed (<?php echo htmlspecialchars($row['payment_mode'] ?? 'Cash'); ?>)
                        </span>
                    <?php else: ?>
                        <span class="status-stamp approved">
                            <i class="fas fa-screwdriver-wrench me-1"></i> Approved (Technician Scheduled)
                        </span>
                    <?php endif; ?>
                </div>

                <div class="text-md-end">
                    <small class="text-muted d-block">Issued by Aqua Air Cooling Center</small>
                    <small class="text-muted">Support: +91 6354911971 | Rajkot, IND</small>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="actions-bar">
                <a href="my_bookings.php" class="btn btn-secondary px-3 py-2" style="border-radius:10px;">
                    <i class="fas fa-arrow-left me-1"></i> Back to My Bookings
                </a>

                <div class="d-flex gap-2">
                    <button onclick="window.print()" class="btn btn-primary px-3 py-2" style="border-radius:10px;">
                        <i class="fas fa-print me-1"></i> Print Receipt
                    </button>
                    <a href="user_receipt_pdf.php?id=<?php echo $row['id']; ?>" class="btn btn-danger px-3 py-2" style="border-radius:10px;">
                        <i class="fas fa-file-pdf me-1"></i> Download PDF
                    </a>
                </div>
            </div>

        </div>

    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        if (typeof confetti === 'function') {
            confetti({ particleCount: 70, spread: 60, origin: { y: 0.6 } });
        }
    });
    </script>
</body>
</html>