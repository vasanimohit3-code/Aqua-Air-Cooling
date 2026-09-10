<?php
require_once 'includes/config.php';

if(session_status()==PHP_SESSION_NONE)
{
    session_start();
}
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
        <link href="css/style.css?v=1" rel="stylesheet">
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

<div class="container-xxl py-5">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.5s">
                    <div class="h-100">
                        <h1 class="display-6 mb-5">Welcome To AC Installation & Cooling Service Center ⭐</h1>
                        <div class="row g-4 mb-4">
                            <div class="col-sm-6">
                                <div class="d-flex align-items-center">
                                    <img class="flex-shrink-0 me-3" src="img/icon/icon-07-primary.png" alt="">
                                    <h5 class="mb-0">Expert Technician</h5>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="d-flex align-items-center">
                                    <img class="flex-shrink-0 me-3" src="img/icon/icon-09-primary.png" alt="">
                                    <h5 class="mb-0">Best Quality Services</h5>
                                </div>
                            </div>
                        </div>
                        <p class="mb-4">We provide professional AC servicing, cleaning, maintenance, and repairs to keep your cooling system efficient and dependable. Our expert technicians ensure optimal performance, improved air quality, and reduced energy costs, helping you stay comfortable in every season.</p>
                        <div class="border-top mt-4 pt-4">
                            <div class="row g-3">
                                <div class="col-12 col-sm-6">
                                    <div class="d-flex align-items-center">
                                        <div class="btn-lg-square bg-primary rounded-circle me-3 flex-shrink-0">
                                            <i class="fa fa-phone-alt text-white"></i>
                                        </div>
                                        <h6 class="mb-0 fw-bold">+91 6354911971</h6>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="d-flex align-items-center">
                                        <div class="btn-lg-square bg-primary rounded-circle me-3 flex-shrink-0">
                                            <i class="fa fa-envelope text-white"></i>
                                        </div>
                                        <h6 class="mb-0 fw-bold text-break">aquaaircooling@gmail.com</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="row g-3">
                        <div class="col-6 text-end">
                            <img class="img-fluid w-75 wow zoomIn" data-wow-delay="0.1s" src="img/about-1.jpg" style="margin-top: 25%;">
                        </div>
                        <div class="col-6 text-start">
                            <img class="img-fluid w-100 wow zoomIn" data-wow-delay="0.3s" src="img/m.jpg">
                        </div>
                        <div class="col-6 text-end">
                            <img class="img-fluid w-50 wow zoomIn" data-wow-delay="0.5s" src="img/about-2.jpg">
                        </div>
                        <div class="col-6 text-start">
                            <img class="img-fluid w-75 wow zoomIn" data-wow-delay="0.7s" src="img/about-4.jpg">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


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