<?php
require_once 'includes/config.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Auto-create table & seed default terms if not exists
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

// Fetch active terms from database
$terms_query = "SELECT * FROM terms_conditions WHERE is_active = 1 ORDER BY section_number ASC, id ASC";
$terms_result = mysqli_query($conn, $terms_query);

// Fetch last updated timestamp
$updated_res = mysqli_query($conn, "SELECT MAX(updated_at) as last_updated FROM terms_conditions WHERE is_active = 1");
$last_updated_row = mysqli_fetch_assoc($updated_res);
$last_updated = (!empty($last_updated_row['last_updated'])) ? date('F d, Y', strtotime($last_updated_row['last_updated'])) : date('F d, Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Terms & Conditions - Aqua Air Cooling</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Aqua Air Cooling Terms & Conditions, Service Policy, Booking Rules" name="description">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@400;600;800&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet"> 

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">

    <style>
        body {
            background-color: #f4f7fb;
            font-family: 'Roboto', sans-serif;
        }

        .terms-hero {
            background: linear-gradient(135deg, #06164f 0%, #0d6efd 100%);
            color: #ffffff;
            padding: 60px 0 50px 0;
            border-radius: 0 0 30px 30px;
            margin-bottom: 40px;
            box-shadow: 0 10px 30px rgba(13, 110, 253, 0.15);
        }

        .terms-hero h1 {
            font-family: 'Roboto Slab', serif;
            font-weight: 800;
            font-size: 2.75rem;
        }

        .terms-card {
            background: #ffffff;
            border-radius: 18px;
            border: 1px solid rgba(0, 0, 0, 0.06);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.04);
            padding: 28px;
            margin-bottom: 24px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .terms-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 35px rgba(13, 110, 253, 0.1);
            border-color: rgba(13, 110, 253, 0.2);
        }

        .terms-badge {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            background: rgba(13, 110, 253, 0.08);
            color: #0d6efd;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            font-weight: 700;
            flex-shrink: 0;
            margin-top: 3px;
        }

        .terms-card-title {
            color: #06164f;
            font-family: 'Roboto Slab', serif;
            font-weight: 700;
            font-size: 1.35rem;
            margin: 0;
        }

        .terms-card-text {
            color: #526075;
            font-size: 1rem;
            line-height: 1.75;
            margin-top: 12px;
            margin-bottom: 0;
        }

        .search-box {
            max-width: 600px;
            margin: -25px auto 40px auto;
            position: relative;
            z-index: 10;
        }

        .search-input {
            height: 55px;
            border-radius: 50px;
            padding-left: 55px;
            padding-right: 25px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            font-size: 1rem;
        }

        .search-icon {
            position: absolute;
            left: 22px;
            top: 50%;
            transform: translateY(-50%);
            color: #0d6efd;
            font-size: 1.2rem;
        }

        .contact-box {
            background: linear-gradient(135deg, #ff7f0e 0%, #ff5500 100%);
            color: #ffffff;
            border-radius: 20px;
            padding: 35px;
            box-shadow: 0 10px 30px rgba(255, 127, 14, 0.2);
        }

        .contact-box a {
            color: #ffffff;
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .terms-hero {
                padding: 40px 0 35px 0;
                border-radius: 0 0 20px 20px;
                margin-bottom: 25px;
            }
            .terms-hero h1 {
                font-size: clamp(1.8rem, 5vw, 2.2rem) !important;
            }
            .terms-card {
                padding: 20px 16px;
                border-radius: 14px;
            }
            .search-box {
                margin: -20px 10px 25px 10px;
            }
            .search-input {
                font-size: 16px;
                height: 48px;
            }
        }

        @media print {
            .navbar, .topbar, .terms-hero button, .search-box, footer, .contact-box {
                display: none !important;
            }
            .terms-card {
                box-shadow: none !important;
                border: 1px solid #ccc !important;
                break-inside: avoid;
            }
            body {
                background: #fff !important;
            }
        }
    </style>
</head>

<body>
    <?php include 'includes/navbar.php'; ?>
    <?php include 'includes/topbar.php'; ?>

    <!-- Terms Hero Banner -->
    <section class="terms-hero text-center">
        <div class="container">
            <span class="badge bg-white text-primary px-3 py-2 rounded-pill mb-3 shadow-sm">
                <i class="fas fa-shield-alt me-1"></i> Official Policy
            </span>
            <h1 class="display-5 mb-3">Terms & Conditions</h1>
            <p class="lead opacity-90 mb-4 mx-auto" style="max-width: 650px;">
                Please review our terms of service and operating policies carefully before booking or using our AC services.
            </p>
            <div class="d-flex align-items-center justify-content-center gap-3 flex-wrap">
                <span class="small opacity-75">
                    <i class="fas fa-calendar-alt me-1"></i> Last Updated: <?php echo htmlspecialchars($last_updated); ?>
                </span>
                <span class="opacity-50">|</span>
                <a href="download_terms_pdf.php" class="btn btn-warning text-dark fw-bold btn-sm rounded-pill px-3 shadow-sm">
                    <i class="fas fa-file-pdf me-1"></i> Download PDF
                </a>
                <button onclick="window.print()" class="btn btn-outline-light btn-sm rounded-pill px-3">
                    <i class="fas fa-print me-1"></i> Print Page
                </button>
            </div>
        </div>
    </section>

    <!-- Main Container -->
    <div class="container pb-5">

        <!-- Search Box -->
        <div class="search-box">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="termsSearch" onkeyup="filterTerms()" class="form-control search-input" placeholder="Search terms (e.g. Booking, Warranty, Payment, Cancel)...">
        </div>

        <!-- Terms Sections List -->
        <div class="row" id="termsList">
            <?php if ($terms_result && mysqli_num_rows($terms_result) > 0): ?>
                <?php while ($term = mysqli_fetch_assoc($terms_result)): ?>
                    <div class="col-12 term-item">
                        <div class="terms-card">
                            <div class="d-flex align-items-start gap-3">
                                <div class="terms-badge">
                                    <i class="<?php echo htmlspecialchars($term['icon'] ?: 'fas fa-file-contract'); ?>"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                        <h3 class="terms-card-title">
                                            <span class="text-primary me-1"><?php echo intval($term['section_number']); ?>.</span>
                                            <?php echo htmlspecialchars($term['title']); ?>
                                        </h3>
                                    </div>
                                    <p class="terms-card-text">
                                        <?php echo nl2br(htmlspecialchars($term['content'])); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-info-circle text-muted display-4 mb-3"></i>
                    <p class="text-muted fs-5">No Terms & Conditions available at the moment.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Contact Section Box -->
        <div class="contact-box mt-4">
            <div class="row align-items-center">
                <div class="col-md-8 mb-3 mb-md-0">
                    <h4 class="fw-bold mb-2 text-white"><i class="fas fa-headset me-2"></i> Have Questions About Our Terms?</h4>
                    <p class="mb-0 text-white-50">
                        Our customer support team is available 24/7 to assist you with any questions regarding service policies, bookings, or warranties.
                    </p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="contact.php" class="btn btn-light text-dark fw-bold px-4 py-2 rounded-pill shadow-sm">
                        <i class="fas fa-paper-plane me-1"></i> Contact Support
                    </a>
                </div>
            </div>
        </div>

    </div>

    <?php include 'includes/footer.php'; ?>

    <!-- Realtime Search Script -->
    <script>
        function filterTerms() {
            const input = document.getElementById('termsSearch').value.toLowerCase();
            const items = document.getElementsByClassName('term-item');

            for (let i = 0; i < items.length; i++) {
                const text = items[i].innerText.toLowerCase();
                if (text.includes(input)) {
                    items[i].style.display = "";
                } else {
                    items[i].style.display = "none";
                }
            }
        }
    </script>
</body>
</html>