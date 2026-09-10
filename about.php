<?php

require_once 'includes/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/* =========================================================
   ABOUT PAGE DYNAMIC SETTINGS
========================================================= */

$about = [

    /* ===============================
       GENERAL
    =============================== */

    'page_title' => 'About Us | Aqua Air Cooling',

    'primary_color' => '#0d6efd',

    'secondary_color' => '#eaf4ff',

    'dark_color' => '#07164f',

    'text_color' => '#667085',

    'white_color' => '#ffffff',

    'light_bg' => '#f7fbff',


    /* ===============================
       HERO
    =============================== */

    'hero_badge' => 'ABOUT AQUA AIR COOLING',

    'hero_title' =>
        'Keeping Your Space Cool, Comfortable & Carefree',

    'hero_text' =>
        'Aqua Air Cooling provides reliable AC installation, repair, maintenance and servicing solutions with professional support and affordable pricing.',

    'hero_image' => 'img/r1.png',

    'hero_button' => 'Book A Service',

    'hero_button_link' => 'booking.php',


    /* ===============================
       ABOUT INTRO
    =============================== */

    'intro_badge' => 'ABOUT OUR COMPANY',

    'intro_title' =>
        'Your Trusted AC Service Partner',

    'intro_text' =>
        'At Aqua Air Cooling, we believe that a properly maintained air conditioner is the key to a comfortable and healthy indoor environment. Our team focuses on reliable service, transparent pricing and customer satisfaction.',

    'intro_image' => 'img/r2.jpg',


    /* ===============================
       DYNAMIC STATISTICS
    =============================== */

    'support_number' => '24/7',

    'support_title' => 'Customer Support',

    'satisfaction_number' => '100%',

    'satisfaction_title' => 'Customer Satisfaction',

    'experience_number' => '10+',

    'experience_title' => 'Years Experience',


    /* ===============================
       WHY CHOOSE US
    =============================== */

    'why_badge' => 'WHY CHOOSE US',

    'why_title' =>
        'Why Customers Choose Aqua Air Cooling',

    'why_text' =>
        'We combine professional expertise, quick response and honest pricing to provide AC services you can depend on.',


    /* ===============================
       FEATURE 1
    =============================== */

    'feature_1_icon' => 'fa-user-check',

    'feature_1_title' => 'Experienced Professionals',

    'feature_1_text' =>
        'Our trained technicians provide dependable AC installation, repair and maintenance services.',


    /* ===============================
       FEATURE 2
    =============================== */

    'feature_2_icon' => 'fa-tags',

    'feature_2_title' => 'Affordable Pricing',

    'feature_2_text' =>
        'Get quality AC services at transparent and reasonable prices without unnecessary hidden charges.',


    /* ===============================
       FEATURE 3
    =============================== */

    'feature_3_icon' => 'fa-bolt',

    'feature_3_title' => 'Fast Response',

    'feature_3_text' =>
        'We understand the importance of a working AC and aim to respond quickly to your service requirements.',


    /* ===============================
       FEATURE 4
    =============================== */

    'feature_4_icon' => 'fa-headset',

    'feature_4_title' => 'Reliable Support',

    'feature_4_text' =>
        'Our team is available to assist you with AC service, maintenance and support whenever you need us.'

];


/* =========================================================
   DYNAMIC BOOKING COUNT
========================================================= */

$service_count = 0;


/*
   Your booking table is:
   booking

   We are counting all booking records.
*/

if (isset($conn)) {

    $query = "SELECT COUNT(*) AS total FROM booking";

    $result = mysqli_query($conn, $query);

    if ($result) {

        $row = mysqli_fetch_assoc($result);

        $service_count = (int)$row['total'];

    }

}


/* =========================================================
   FORMAT SERVICE COUNT
========================================================= */

if ($service_count >= 1000) {

    $service_display =
        number_format($service_count / 1000, 1) . 'K+';

} else {

    $service_display =
        $service_count . '+';

}


/* =========================================================
   DYNAMIC STATISTICS ARRAY
========================================================= */

$statistics = [

    [
        'number' => $service_display,
        'title' => 'Services Completed',
        'icon' => 'fa-tools'
    ],

    [
        'number' => $about['support_number'],
        'title' => $about['support_title'],
        'icon' => 'fa-headset'
    ],

    [
        'number' => $about['satisfaction_number'],
        'title' => $about['satisfaction_title'],
        'icon' => 'fa-smile'
    ],

    [
        'number' => $about['experience_number'],
        'title' => $about['experience_title'],
        'icon' => 'fa-award'
    ]

];


/* =========================================================
   FEATURES ARRAY
========================================================= */

$features = [

    [
        'icon' => $about['feature_1_icon'],
        'title' => $about['feature_1_title'],
        'text' => $about['feature_1_text']
    ],

    [
        'icon' => $about['feature_2_icon'],
        'title' => $about['feature_2_title'],
        'text' => $about['feature_2_text']
    ],

    [
        'icon' => $about['feature_3_icon'],
        'title' => $about['feature_3_title'],
        'text' => $about['feature_3_text']
    ],

    [
        'icon' => $about['feature_4_icon'],
        'title' => $about['feature_4_title'],
        'text' => $about['feature_4_text']
    ]

];

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="utf-8">

    <title>
        <?php echo htmlspecialchars($about['page_title']); ?>
    </title>

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="description"
        content="Aqua Air Cooling - Professional AC installation, repair, maintenance and service."
    >


    <!-- Favicon -->

    <link
        href="img/favicon.ico"
        rel="icon"
    >


    <!-- Google Fonts -->

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
        href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@400;600;700;800&family=Roboto:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >


    <!-- Font Awesome -->

    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css"
        rel="stylesheet"
    >


    <!-- Bootstrap -->

    <link
        href="css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Main CSS -->

    <link
        href="css/style.css"
        rel="stylesheet"
    >


<style>

/* =========================================================
   ROOT
========================================================= */

:root {

    --primary: <?php echo $about['primary_color']; ?>;

    --secondary: <?php echo $about['secondary_color']; ?>;

    --dark: <?php echo $about['dark_color']; ?>;

    --text: <?php echo $about['text_color']; ?>;

    --white: <?php echo $about['white_color']; ?>;

    --light: <?php echo $about['light_bg']; ?>;

}


/* =========================================================
   GENERAL
========================================================= */

* {
    box-sizing: border-box;
}

body {

    font-family: 'Roboto', sans-serif;

    overflow-x: hidden;

}

.about-page {

    width: 100%;

}


/* =========================================================
   HERO
========================================================= */

.about-hero {

    position: relative;

    min-height: 580px;

    display: flex;

    align-items: center;

    overflow: hidden;

    background:
        linear-gradient(
            110deg,
            var(--dark) 0%,
            #0d2d72 55%,
            var(--primary) 100%
        );

}

.about-hero::before {

    content: "";

    position: absolute;

    width: 450px;

    height: 450px;

    border-radius: 50%;

    background: rgba(255,255,255,0.06);

    top: -180px;

    right: -100px;

}

.about-hero::after {

    content: "";

    position: absolute;

    width: 300px;

    height: 300px;

    border-radius: 50%;

    background: rgba(255,255,255,0.05);

    bottom: -150px;

    left: -100px;

}

.hero-container {

    position: relative;

    z-index: 2;

}

.hero-content {

    padding: 80px 0;

}

.hero-badge {

    display: inline-block;

    padding: 8px 18px;

    border-radius: 50px;

    background: rgba(255,255,255,0.14);

    border: 1px solid rgba(255,255,255,0.25);

    color: var(--white);

    font-size: 13px;

    font-weight: 700;

    letter-spacing: 1px;

    margin-bottom: 20px;

}

.hero-title {

    color: var(--white);

    font-family: 'Roboto Slab', serif;

    font-size: 52px;

    line-height: 1.15;

    font-weight: 800;

    margin-bottom: 25px;

}

.hero-text {

    color: rgba(255,255,255,0.86);

    font-size: 17px;

    line-height: 1.8;

    max-width: 650px;

    margin-bottom: 30px;

}


/* =========================================================
   HERO BUTTON
========================================================= */

.hero-button {

    display: inline-flex;

    align-items: center;

    gap: 10px;

    padding: 13px 27px;

    background: var(--white);

    color: var(--primary);

    border-radius: 50px;

    text-decoration: none;

    font-weight: 700;

    transition: 0.3s;

}

.hero-button:hover {

    color: var(--primary);

    transform: translateY(-3px);

    box-shadow:
        0 12px 30px
        rgba(0,0,0,0.20);

}


/* =========================================================
   HERO IMAGE
========================================================= */

.hero-image-box {

    position: relative;

    height: 500px;

    border-radius: 30px;

    overflow: hidden;

    box-shadow:
        0 25px 60px
        rgba(0,0,0,0.25);

    border:
        5px solid
        rgba(255,255,255,0.15);

}

.hero-image {

    width: 100%;

    height: 100%;

    object-fit: cover;

    transition: 0.6s;

}

.hero-image-box:hover .hero-image {

    transform: scale(1.05);

}


/* =========================================================
   INTRO
========================================================= */

.intro-section {

    padding: 100px 0;

    background: var(--white);

}

.intro-image-box {

    position: relative;

    height: 520px;

    overflow: hidden;

    border-radius: 25px;

    box-shadow:
        0 15px 40px
        rgba(0,0,0,0.12);

}

.intro-image {

    width: 100%;

    height: 100%;

    object-fit: cover;

    transition: 0.5s;

}

.intro-image-box:hover .intro-image {

    transform: scale(1.04);

}

.intro-content {

    padding-left: 35px;

}

.section-badge {

    display: inline-block;

    color: var(--primary);

    font-weight: 700;

    font-size: 13px;

    letter-spacing: 1.5px;

    margin-bottom: 12px;

}

.section-title {

    color: var(--dark);

    font-family: 'Roboto Slab', serif;

    font-size: 42px;

    line-height: 1.25;

    font-weight: 800;

    margin-bottom: 20px;

}

.section-text {

    color: var(--text);

    font-size: 16px;

    line-height: 1.8;

}


/* =========================================================
   STATISTICS
========================================================= */

.stats-section {

    padding: 55px 0;

     background: #38bdf8;

}

.stat-card {

    text-align: center;

    padding: 25px 15px;

    position: relative;

    transition: 0.3s;

}

.stat-card:hover {

    transform: translateY(-6px);

}

.stat-icon {

    width: 58px;

    height: 58px;

    margin: 0 auto 14px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 50%;

    background: var(--secondary);

    color: var(--primary);

    font-size: 22px;

    transition: 0.3s;

}

.stat-card:hover .stat-icon {

    background: var(--primary);

    color: var(--white);

}

.stat-number {

    color: #010A35;

    font-family: 'Roboto Slab', serif;

    font-size: 42px;

    font-weight: 800;

    margin-bottom: 5px;

}

.stat-title {

    color: #010A35;
    font-size: 15px;

    font-weight: 600;

}


/* =========================================================
   WHY CHOOSE
========================================================= */

.why-section {

    padding: 100px 0;

    background: var(--white);

}

.why-heading {

    text-align: center;

    max-width: 750px;

    margin: 0 auto 55px;

}


/* =========================================================
   FEATURE
========================================================= */

.feature-card {

    height: 100%;

    padding: 35px 28px;

    border-radius: 18px;

    background: silver;

    border: 1px solid #e8eef6;

    transition: 0.35s;

}

.feature-card:hover {

    transform: translateY(-8px);

    border-color: var(--primary);

    box-shadow:
        0 18px 45px
        rgba(13,110,253,0.12);

}

.feature-icon {

    width: 65px;

    height: 65px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 16px;

    background: var(--secondary);

    color: var(--primary);

    font-size: 25px;

    margin-bottom: 22px;

    transition: 0.3s;

}

.feature-card:hover .feature-icon {

    background: var(--primary);

    color: var(--white);

    transform: rotate(-5deg);

}

.feature-title {

    color: var(--dark);

    font-family: 'Roboto Slab', serif;

    font-size: 20px;

    font-weight: 700;

    margin-bottom: 12px;

}

.feature-text {

    color: #010A35;

    font-size: 15px;

    line-height: 1.7;

    margin: 0;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 991px) {

    .hero-title {

        font-size: 42px;

    }

    .hero-image-box {

        height: 400px;

        margin-bottom: 50px;

    }

    .intro-content {

        padding-left: 0;

        margin-top: 30px;

    }

    .intro-image-box {

        height: 430px;

    }

    .section-title {

        font-size: 36px;

    }

}


@media (max-width: 767px) {

    .about-hero {

        min-height: auto;

    }

    .hero-content {

        padding: 60px 0 30px;

    }

    .hero-title {

        font-size: 34px;

    }

    .hero-text {

        font-size: 15px;

    }

    .hero-image-box {

        height: 320px;

        margin-bottom: 50px;

    }

    .intro-section,
    .why-section {

        padding: 65px 0;

    }

    .intro-image-box {

        height: 320px;

    }

    .section-title {

        font-size: 30px;

    }

    .stat-number {

        font-size: 34px;

    }

    .feature-card {

        padding: 28px 22px;

    }

}


@media (max-width: 480px) {

    .hero-title {

        font-size: 29px;

    }

    .hero-image-box {

        height: 280px;

    }

    .section-title {

        font-size: 27px;

    }

    .intro-image-box {

        height: 280px;

    }

}

</style>

</head>


<body>


<?php include 'includes/navbar.php'; ?>


<?php include 'includes/topbar.php'; ?>


<main class="about-page">


<!-- =========================================================
     HERO SECTION
========================================================= -->

<section class="about-hero">

    <div class="container hero-container">

        <div class="row align-items-center">

            <div class="col-lg-6">

                <div class="hero-content">

                    <span class="hero-badge">

                        <?php
                        echo htmlspecialchars(
                            $about['hero_badge']
                        );
                        ?>

                    </span>


                    <h1 class="hero-title">

                        <?php
                        echo htmlspecialchars(
                            $about['hero_title']
                        );
                        ?>

                    </h1>


                    <p class="hero-text">

                        <?php
                        echo htmlspecialchars(
                            $about['hero_text']
                        );
                        ?>

                    </p>


              

                </div>

            </div>


            <div class="col-lg-6">

                <div class="hero-image-box">

                    <img
                        src="<?php echo htmlspecialchars($about['hero_image']); ?>"
                        alt="Aqua Air Cooling"
                        class="hero-image"
                    >

                </div>

            </div>

        </div>

    </div>

</section>



<!-- =========================================================
     INTRO SECTION
========================================================= -->

<section class="intro-section">

    <div class="container">

        <div class="row align-items-center g-5">


            <div class="col-lg-6">

                <div class="intro-image-box">

                    <img
                        src="<?php echo htmlspecialchars($about['intro_image']); ?>"
                        alt="Professional AC Service"
                        class="intro-image"
                    >

                </div>

            </div>


            <div class="col-lg-6">

                <div class="intro-content">

                    <span class="section-badge">

                        <?php
                        echo htmlspecialchars(
                            $about['intro_badge']
                        );
                        ?>

                    </span>


                    <h2 class="section-title">

                        <?php
                        echo htmlspecialchars(
                            $about['intro_title']
                        );
                        ?>

                    </h2>


                    <p class="section-text">

                        <?php
                        echo htmlspecialchars(
                            $about['intro_text']
                        );
                        ?>

                    </p>

                </div>

            </div>


        </div>

    </div>

</section>



<!-- =========================================================
     DYNAMIC STATISTICS
========================================================= -->

<section class="stats-section">

    <div class="container">

        <div class="row align-items-center">

            <?php foreach ($statistics as $stat): ?>

                <div class="col-6 col-lg-3">

                    <div class="stat-card">

                        <div class="stat-icon">

                            <i
                                class="fas <?php echo htmlspecialchars($stat['icon']); ?>"
                            ></i>

                        </div>


                        <div class="stat-number">

                            <?php
                            echo htmlspecialchars(
                                $stat['number']
                            );
                            ?>

                        </div>


                        <div class="stat-title">

                            <?php
                            echo htmlspecialchars(
                                $stat['title']
                            );
                            ?>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    </div>

</section>



<!-- =========================================================
     WHY CHOOSE US
========================================================= -->

<section class="why-section">

    <div class="container">


        <div class="why-heading">

            <span class="section-badge">

                <?php
                echo htmlspecialchars(
                    $about['why_badge']
                );
                ?>

            </span>


            <h2 class="section-title">

                <?php
                echo htmlspecialchars(
                    $about['why_title']
                );
                ?>

            </h2>


            <p class="section-text">

                <?php
                echo htmlspecialchars(
                    $about['why_text']
                );
                ?>

            </p>

        </div>



        <div class="row g-4">

            <?php foreach ($features as $feature): ?>

                <div class="col-md-6 col-lg-3">

                    <div class="feature-card">


                        <div class="feature-icon">

                            <i
                                class="fas <?php echo htmlspecialchars($feature['icon']); ?>"
                            ></i>

                        </div>


                        <h4 class="feature-title">

                            <?php
                            echo htmlspecialchars(
                                $feature['title']
                            );
                            ?>

                        </h4>


                        <p class="feature-text">

                            <?php
                            echo htmlspecialchars(
                                $feature['text']
                            );
                            ?>

                        </p>


                    </div>

                </div>

            <?php endforeach; ?>

        </div>


    </div>

</section>


</main>


<?php include 'includes/footer.php'; ?>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>