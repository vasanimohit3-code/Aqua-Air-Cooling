<?php
require_once 'includes/config.php';

if(session_status()==PHP_SESSION_NONE)
{
    session_start();
}

// Auto ensure columns exist in contact_info table
$col_check1 = @mysqli_query($conn, "SHOW COLUMNS FROM contact_info LIKE 'about_heading'");
if ($col_check1 && mysqli_num_rows($col_check1) == 0) {
    @mysqli_query($conn, "ALTER TABLE contact_info ADD COLUMN about_heading VARCHAR(255) DEFAULT 'Welcome To AC Installation & Cooling Service Center ⭐'");
}
$col_check2 = @mysqli_query($conn, "SHOW COLUMNS FROM contact_info LIKE 'about_desc'");
if ($col_check2 && mysqli_num_rows($col_check2) == 0) {
    @mysqli_query($conn, "ALTER TABLE contact_info ADD COLUMN about_desc TEXT NULL");
}

// Fetch dynamic contact and welcome section info from database
$contact_q = mysqli_query($conn, "SELECT * FROM contact_info WHERE id = 1 LIMIT 1");
$contact_info = ($contact_q && mysqli_num_rows($contact_q) > 0) ? mysqli_fetch_assoc($contact_q) : [];

$phone_display = !empty($contact_info['phone']) ? $contact_info['phone'] : '+91 6354911971';
$phone_clean = preg_replace('/[^0-9+]/', '', $phone_display);

$email_display = !empty($contact_info['email']) ? $contact_info['email'] : 'aquaaircooling@gmail.com';

$whatsapp_raw = !empty($contact_info['whatsapp_number']) ? $contact_info['whatsapp_number'] : '916354911971';
$whatsapp_clean = preg_replace('/[^0-9]/', '', $whatsapp_raw);

$about_heading = !empty($contact_info['about_heading']) 
    ? $contact_info['about_heading'] 
    : 'Welcome To AC Installation & Cooling Service Center ⭐';

$about_desc = !empty($contact_info['about_desc']) 
    ? $contact_info['about_desc'] 
    : (!empty($contact_info['page_subheading']) 
        ? $contact_info['page_subheading'] 
        : 'We provide professional AC servicing, cleaning, maintenance, and repairs to keep your cooling system efficient and dependable. Our expert technicians ensure optimal performance, improved air quality, and reduced energy costs, helping you stay comfortable in every season.');
?><!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Aqua Air Cooling</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

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
    <link href="css/style.css?v=2" rel="stylesheet">

    <style>
    /* Modern Dynamic About Section Styles */
    .about-welcome-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: linear-gradient(135deg, rgba(13, 110, 253, 0.08) 0%, rgba(13, 202, 240, 0.12) 100%);
        color: #0d6efd;
        font-size: 0.85rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        padding: 6px 16px;
        border-radius: 50px;
        border: 1px solid rgba(13, 110, 253, 0.2);
        margin-bottom: 14px;
    }

    .about-section-heading {
        font-size: 2.1rem;
        font-weight: 800;
        line-height: 1.25;
        color: #0b1f44;
    }

    @media (max-width: 767px) {
        .about-section-heading {
            font-size: 1.55rem;
        }
    }

    .about-desc-text {
        font-size: 1.02rem;
        line-height: 1.75;
        color: #52617b;
    }

    /* Feature highlight cards */
    .about-feature-card {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 18px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        transition: all 0.3s ease;
    }
    .about-feature-card:hover {
        background: #ffffff;
        border-color: #0d6efd;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(13, 110, 253, 0.08);
    }
    .about-feature-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #0d6efd, #0284c7);
        color: #fff;
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    /* Interactive Contact Action Cards */
    .about-contact-card {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 12px 16px;
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 14px;
        text-decoration: none !important;
        transition: all 0.25s ease;
    }
    .about-contact-card:hover, .about-contact-card:active {
        background: #f0f7ff;
        border-color: #0d6efd;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(13, 110, 253, 0.12);
    }
    .about-contact-icon {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #0d6efd 0%, #0052cc 100%);
        color: #ffffff;
        font-size: 1.15rem;
        flex-shrink: 0;
        transition: transform 0.25s ease;
    }
    .about-contact-card:hover .about-contact-icon {
        transform: scale(1.08);
    }
    .about-contact-card.email-card .about-contact-icon {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    }
    .about-contact-meta {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        margin-bottom: 2px;
    }
    .about-contact-val {
        font-size: 0.98rem;
        font-weight: 700;
        color: #0b1f44;
        word-break: break-all;
        margin-bottom: 0;
    }

    /* CTA Buttons */
    .btn-about-book {
        background: linear-gradient(135deg, #0d6efd 0%, #0052cc 100%);
        color: #ffffff !important;
        font-weight: 700;
        border-radius: 50px;
        padding: 12px 26px;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 6px 18px rgba(13, 110, 253, 0.3);
        transition: all 0.3s ease;
        text-decoration: none !important;
    }
    .btn-about-book:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 24px rgba(13, 110, 253, 0.4);
    }
    .btn-about-whatsapp {
        background: linear-gradient(135deg, #25d366 0%, #128c7e 100%);
        color: #ffffff !important;
        font-weight: 700;
        border-radius: 50px;
        padding: 12px 24px;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 6px 18px rgba(37, 211, 102, 0.3);
        transition: all 0.3s ease;
        text-decoration: none !important;
    }
    .btn-about-whatsapp:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 24px rgba(37, 211, 102, 0.4);
    }

    /* 2x2 Balanced Photo Grid Collage */
    .about-photo-grid-wrap {
        position: relative;
        padding: 10px;
    }
    .about-photo-tile {
        position: relative;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        background: #e2e8f0;
        height: 185px;
        transition: all 0.35s ease;
    }
    @media (min-width: 992px) {
        .about-photo-tile {
            height: 220px;
        }
    }
    .about-photo-tile img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform 0.45s ease;
    }
    .about-photo-tile:hover {
        box-shadow: 0 14px 32px rgba(13, 110, 253, 0.2);
        transform: translateY(-4px);
    }
    .about-photo-tile:hover img {
        transform: scale(1.08);
    }

    /* Floating Center Experience Badge */
    .about-center-badge {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: linear-gradient(135deg, #07164f 0%, #0d6efd 100%);
        color: #ffffff;
        padding: 14px 20px;
        border-radius: 20px;
        box-shadow: 0 14px 35px rgba(7, 22, 79, 0.4);
        border: 4px solid #ffffff;
        text-align: center;
        z-index: 6;
        min-width: 140px;
        animation: floatBadge 3.5s ease-in-out infinite alternate;
    }
    @keyframes floatBadge {
        0% { transform: translate(-50%, -50%) translateY(0px); }
        100% { transform: translate(-50%, -50%) translateY(-6px); }
    }
    .about-center-badge .badge-number {
        font-size: 1.65rem;
        font-weight: 800;
        line-height: 1;
        color: #ffc107;
    }
    .about-center-badge .badge-text {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 4px;
        margin-bottom: 0;
        opacity: 0.9;
    }
    .about-center-badge .badge-stars {
        font-size: 0.75rem;
        color: #ffc107;
        letter-spacing: 1px;
        margin-top: 2px;
    }
    </style>
</head>
<body>
    <!-- Spinner Start -->
    <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-grow text-primary" role="status"></div>
    </div>
    <!-- Spinner End -->

    <?php include 'includes/navbar.php'; ?>
    <?php include 'includes/topbar.php'; ?>

    <div class="container-fluid p-0 mb-4 mb-lg-5">
        <div id="header-carousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img class="w-100" src="img/carousel-5.png" alt="Aqua Air Cooling Banner">
                    <div class="carousel-caption">
                        <div class="container">
                            <div class="row justify-content-center">
                                <div class="col-lg-12 pt-3 pt-md-5">
                                    <h1 class="display-4 text-white mb-3 mb-md-4 animated slideInDown">Your Trusted Cooling Partner ⭐</h1>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Feature Section (2x2 on Mobile, 4x1 on Desktop) -->
    <div class="container position-relative feature-floating-box" style="z-index: 10;">
        <div class="row bg-white rounded-3 shadow-lg py-3 py-md-4 g-3 align-items-center">

            <!-- Quick Support -->
            <div class="col-6 col-lg-3 text-center border-end-md">
                <i class="fa fa-headset fa-2x fa-md-3x text-primary mb-2 mb-md-3"></i>
                <h5 class="fw-bold mb-1">Quick Support</h5>
                <p class="text-muted small mb-0">We are always here</p>
            </div>

            <!-- Best Technicians -->
            <div class="col-6 col-lg-3 text-center border-end-md">
                <i class="fa fa-user-cog fa-2x fa-md-3x text-primary mb-2 mb-md-3"></i>
                <h5 class="fw-bold mb-1">Best Technicians</h5>
                <p class="text-muted small mb-0">Experts & Trained</p>
            </div>

            <!-- Affordable Price -->
            <div class="col-6 col-lg-3 text-center border-end-md">
                <i class="fa fa-tags fa-2x fa-md-3x text-primary mb-2 mb-md-3"></i>
                <h5 class="fw-bold mb-1">Affordable Price</h5>
                <p class="text-muted small mb-0">Best value for money</p>
            </div>

            <!-- 100% Guarantee -->
            <div class="col-6 col-lg-3 text-center">
                <i class="fa fa-shield-alt fa-2x fa-md-3x text-primary mb-2 mb-md-3"></i>
                <h5 class="fw-bold mb-1">100% Guarantee</h5>
                <p class="text-muted small mb-0">Satisfaction assured</p>
            </div>

        </div>
    </div>
    
    <!-- Carousel End -->

    <!-- Welcome / About Section Start (Dynamic from Database) -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="row g-5 align-items-center">
                <!-- Left Content Column -->
                <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.2s">
                    <div class="h-100">
                        <!-- Top Dynamic Pill Badge -->
                        <div class="about-welcome-badge">
                            <i class="fas fa-snowflake"></i>
                            <span>Trusted AC Service Center in Rajkot</span>
                        </div>

                        <!-- Dynamic Title (Managed from Admin Panel / Database) -->
                        <h1 class="about-section-heading mb-4">
                            <?php echo htmlspecialchars($about_heading); ?>
                        </h1>

                        <!-- Two Feature Cards -->
                        <div class="row g-3 mb-4">
                            <div class="col-sm-6">
                                <div class="about-feature-card">
                                    <div class="about-feature-icon">
                                        <i class="fas fa-user-check"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0 text-dark">Expert Technicians</h6>
                                        <small class="text-muted">Certified &amp; Background Verified</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="about-feature-card">
                                    <div class="about-feature-icon">
                                        <i class="fas fa-shield-alt"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0 text-dark">Quality Services</h6>
                                        <small class="text-muted">100% Genuine Spare Parts</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Dynamic Description Paragraph -->
                        <p class="about-desc-text mb-4">
                            <?php echo nl2br(htmlspecialchars($about_desc)); ?>
                        </p>

                        <!-- Dynamic Clickable Contact Cards -->
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-sm-6">
                                <a href="tel:<?php echo $phone_clean; ?>" class="about-contact-card shadow-sm" title="Call Us Directly">
                                    <div class="about-contact-icon">
                                        <i class="fa fa-phone-alt"></i>
                                    </div>
                                    <div>
                                        <div class="about-contact-meta">Call For Service</div>
                                        <div class="about-contact-val"><?php echo htmlspecialchars($phone_display); ?></div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-12 col-sm-6">
                                <a href="mailto:<?php echo htmlspecialchars($email_display); ?>" class="about-contact-card email-card shadow-sm" title="Send Official Email">
                                    <div class="about-contact-icon">
                                        <i class="fa fa-envelope"></i>
                                    </div>
                                    <div>
                                        <div class="about-contact-meta">Email Support</div>
                                        <div class="about-contact-val"><?php echo htmlspecialchars($email_display); ?></div>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <!-- Quick Action CTA Buttons -->
                        <div class="d-flex flex-wrap gap-3 align-items-center pt-2">
                            <a href="booking.php" class="btn-about-book">
                                <i class="fas fa-calendar-check"></i> Book AC Service
                            </a>
                            <a href="https://wa.me/<?php echo $whatsapp_clean; ?>?text=<?php echo urlencode('Hello Aqua Air Cooling, I want to book an AC service.'); ?>" target="_blank" class="btn-about-whatsapp">
                                <i class="fab fa-whatsapp"></i> WhatsApp Us
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Right 2x2 Photo Collage with Center Floating Experience Badge -->
                <div class="col-lg-6 wow zoomIn" data-wow-delay="0.3s">
                    <div class="about-photo-grid-wrap">
                        <div class="row g-3">
                            <!-- Image 1 -->
                            <div class="col-6">
                                <div class="about-photo-tile">
                                    <img src="img/about-1.jpg" alt="AC Technician Service" loading="lazy">
                                </div>
                            </div>
                            <!-- Image 2 -->
                            <div class="col-6">
                                <div class="about-photo-tile">
                                    <img src="img/m.jpg" alt="Air Conditioner Maintenance" loading="lazy">
                                </div>
                            </div>
                            <!-- Image 3 -->
                            <div class="col-6">
                                <div class="about-photo-tile">
                                    <img src="img/about-2.jpg" alt="AC Outdoor Unit Repair" loading="lazy">
                                </div>
                            </div>
                            <!-- Image 4 -->
                            <div class="col-6">
                                <div class="about-photo-tile">
                                    <img src="img/about-4.jpg" alt="AC Cleaning & Servicing" loading="lazy">
                                </div>
                            </div>
                        </div>

                        <!-- Center Floating Experience Badge -->
                        <div class="about-center-badge">
                            <div class="badge-number">10+</div>
                            <div class="badge-text">Years Experience</div>
                            <div class="badge-stars">★★★★★</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Welcome / About Section End -->


    <!-- Back to Top -->
    <a href="" class="btn btn-lg btn-primary btn-lg-square rounded-circle back-to-top"><i class="bi bi-arrow-up"></i></a>


    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/wow/wow.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>
     <?php include 'includes/footer.php'; ?>
</body>

</html>