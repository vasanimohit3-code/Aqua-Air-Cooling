<?php

require_once 'includes/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/* =========================================================
   DYNAMIC SERVICES DATA FROM DATABASE
========================================================= */

$services = [];
$services_query = mysqli_query($conn, "SELECT * FROM services WHERE is_active = 1 ORDER BY display_order ASC, id ASC");

if ($services_query && mysqli_num_rows($services_query) > 0) {
    while ($row = mysqli_fetch_assoc($services_query)) {
        $services[] = [
            'id' => $row['slug'],
            'slug' => $row['slug'],
            'name' => $row['name'],
            'image' => $row['image'],
            'icon' => $row['icon'],
            'short' => $row['short_desc'],
            'price' => $row['price'],
            'price_text' => $row['price_text'],
            'file' => 'all_services/service.php?service=' . urlencode($row['slug'])
        ];
    }
} else {
    // Fallback if table not populated yet
    $services = [
        [
            'id' => 'ac_installation',
            'slug' => 'ac_installation',
            'name' => 'AC Installation',
            'image' => 'img/installation.jpg',
            'icon' => 'img/icon/icon-01-light.png',
            'short' => 'Professional AC installation with proper fitting and testing.',
            'file' => 'all_services/service.php?service=ac_installation'
        ]
    ];
}
?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="utf-8">

    <title>Aqua Air Cooling | Services</title>

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <link
        href="img/favicon.ico"
        rel="icon"
    >

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@400;600;700;800&family=Roboto:wght@400;500;700&display=swap"
        rel="stylesheet"
    >

    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css"
        rel="stylesheet"
    >

    <link
        href="css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="css/style.css"
        rel="stylesheet"
    >

    <style>


        .services-page {
    background:
        linear-gradient(
            rgba(240, 248, 255, 0.30),
            rgba(240, 248, 255, 0.99)
        ),
        url("img/sbg.png");
     padding: 80px 0;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed;
}

        .services-heading {
            max-width: 700px;
            margin: auto;
            text-align: center;
            margin-bottom: 50px;
        }

        .services-heading span {
            color: #0d6efd;
            font-weight: 700;
            letter-spacing: 1.5px;
            font-size: 35px;
        }

        .services-heading h1 {
            color: #07164f;
            font-family: 'Roboto Slab', serif;
            font-weight: 800;
            font-size: 42px;
            margin: 12px 0;
        }

        .services-heading p {
            color: #667085;
            line-height: 1.7;
        }

        .service-card {
            height: 100%;
            background: #ffffff;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 10px 35px rgba(0,0,0,0.08);
            transition: 0.35s;
        }

        .service-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 18px 45px rgba(13,110,253,0.16);
        }

        .service-card-image {
            width: 100%;
            height: 230px;
            object-fit: cover;

        }

        .service-card-content {
            padding: 22px;
            background: #07164f;
        }

        .service-icon {
            width: 55px;
            height: 55px;
            background: #0d6efd;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }

        .service-icon img {
            width: 32px;
        }

        .service-title {
            color: white;
            font-family: 'Roboto Slab', serif;
            font-size: 20px;
            font-weight: 700;
            min-height: 50px;
        }

        .service-description {
            color: white;
            font-size: 14px;
            line-height: 1.7;
            min-height: 48px;
        }

        .service-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 10px;
            padding: 10px 20px;
            background: #0d6efd;
            color: #ffffff;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
        }

        .service-button:hover {
            background: #084298;
            color: #ffffff;
        }

        @media(max-width:767px) {

            .services-page {
                padding: 45px 15px;
            }

            .services-heading span {
                font-size: 16px;
                letter-spacing: 1px;
            }

            .services-heading h1 {
                font-size: clamp(24px, 6vw, 32px);
            }

            .service-card-image {
                height: 200px;
            }

        }

    </style>

</head>

<body>

<?php include 'includes/navbar.php'; ?>

<?php include 'includes/topbar.php'; ?>


<section class="services-page">

    <div class="container">


        <!-- HEADING -->

        <div class="services-heading">

            <span>OUR SERVICES</span>
            
            <h1>
                Professional AC Services
            </h1>

            <p>
                We provide reliable AC installation, repair,
                maintenance and related services for your home
                and business.
            </p>

        </div>


        <!-- SERVICES -->

        <div class="row g-4">


            <?php foreach ($services as $service): ?>


                <div class="col-lg-4 col-md-6">


                    <div class="service-card">


                        <!-- IMAGE -->

                        <img
                            src="<?php echo htmlspecialchars($service['image']); ?>"
                            alt="<?php echo htmlspecialchars($service['name']); ?>"
                            class="service-card-image"
                        >


                        <div class="service-card-content">


                            <!-- ICON -->

                            <div class="service-icon">

                                <img
                                    src="<?php echo htmlspecialchars($service['icon']); ?>"
                                    alt=""
                                >

                            </div>


                            <!-- TITLE -->

                            <h3 class="service-title">

                                <?php
                                echo htmlspecialchars($service['name']);
                                ?>

                            </h3>


                            <!-- DESCRIPTION -->

                            <p class="service-description">

                                <?php
                                echo htmlspecialchars($service['short']);
                                ?>

                            </p>


                            <!-- BUTTON -->

                            <a
                                href="<?php echo htmlspecialchars($service['file']); ?>"
                                class="service-button">
                                View Details
                                <i class="fas fa-arrow-right"></i>
                            </a>


                        </div>

                    </div>


                </div>


            <?php endforeach; ?>


        </div>

    </div>

</section>


<?php include 'includes/footer.php'; ?>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"
></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>