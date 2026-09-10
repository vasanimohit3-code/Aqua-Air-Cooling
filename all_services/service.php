<?php

require_once '../includes/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/* =========================================================
   LOGIN STATUS
========================================================= */

$is_logged_in = isset($_SESSION['user_id']);


/* =========================================================
   CURRENT SERVICE
========================================================= */

$service_key = $_GET['service'] ?? 'ac_installation';


/* =========================================================
   DYNAMIC SERVICES DATA FROM DATABASE
========================================================= */

$services = [];
$db_services_query = mysqli_query($conn, "SELECT * FROM services WHERE is_active = 1 ORDER BY display_order ASC, id ASC");

if ($db_services_query && mysqli_num_rows($db_services_query) > 0) {
    while ($row = mysqli_fetch_assoc($db_services_query)) {
        $slug = $row['slug'];
        
        // Parse image path for all_services folder
        $img_src = $row['image'];
        if (strpos($img_src, 'http') !== 0 && strpos($img_src, '../') !== 0) {
            $img_src = '../' . $img_src;
        }

        // Price display
        $price_disp = !empty($row['price_text']) ? $row['price_text'] : ('Starting From ₹' . number_format($row['price'], 0));

        // Parse features
        $raw_features = $row['features'] ?? '';
        $features_list = [];
        if (!empty($raw_features)) {
            $json_f = json_decode($raw_features, true);
            if (is_array($json_f)) {
                $features_list = $json_f;
            } else {
                $lines = explode("\n", $raw_features);
                foreach ($lines as $ln) {
                    $trimmed = trim($ln, " \t\n\r\0\x0B-•*");
                    if (!empty($trimmed)) {
                        $features_list[] = $trimmed;
                    }
                }
            }
        }
        if (empty($features_list)) {
            $features_list = [
                'Professional ' . $row['name'] . ' service',
                'Certified and experienced technicians',
                'Safety and efficiency inspection',
                'Tested cooling performance',
                '100% Satisfaction Guarantee'
            ];
        }

        // Build Process steps
        $process_list = [
            ['icon' => 'fa-phone', 'title' => 'Book Service', 'text' => 'Book your ' . $row['name'] . ' online or by call.'],
            ['icon' => 'fa-user-cog', 'title' => 'Expert Visit', 'text' => 'Our technician visits your location within ' . (!empty($row['estimated_time']) ? $row['estimated_time'] : '60 Mins') . '.'],
            ['icon' => 'fa-tools', 'title' => 'Professional Work', 'text' => 'Quality ' . $row['name'] . ' performed with precision.'],
            ['icon' => 'fa-check-circle', 'title' => 'Final Testing', 'text' => 'Complete performance check with ' . (!empty($row['warranty']) ? $row['warranty'] : 'warranty guarantee') . '.']
        ];

        $services[$slug] = [
            'id' => $row['id'],
            'slug' => $slug,
            'name' => $row['name'],
            'badge' => !empty($row['badge']) ? $row['badge'] : 'PROFESSIONAL AC SERVICE',
            'title' => !empty($row['title']) ? $row['title'] : ('Professional ' . $row['name'] . ' Service'),
            'image' => $img_src,
            'icon' => $row['icon'],
            'price' => $price_disp,
            'raw_price' => (float)$row['price'],
            'description' => $row['description'],
            'short' => $row['short_desc'],
            'features' => $features_list,
            'process' => $process_list,
            'estimated_time' => $row['estimated_time'],
            'warranty' => $row['warranty']
        ];
    }
}

/* Fallback if DB empty */
if (empty($services)) {
    $services['ac_installation'] = [
        'id' => 1,
        'slug' => 'ac_installation',
        'name' => 'AC Installation',
        'badge' => 'PROFESSIONAL AC SERVICE',
        'title' => 'Professional AC Installation Service',
        'image' => '../img/installation.jpg',
        'price' => 'Starting From ₹1200',
        'raw_price' => 1200,
        'description' => 'Get professional AC installation with proper fitting, accurate positioning and complete testing.',
        'features' => [
            'Professional AC installation',
            'Proper indoor and outdoor unit fitting',
            'Copper pipe connection',
            'Leakage and safety inspection'
        ],
        'process' => [
            ['icon'=>'fa-phone','title'=>'Book Service','text'=>'Contact us and book your AC installation service.'],
            ['icon'=>'fa-user-cog','title'=>'Expert Visit','text'=>'Our technician visits your location.'],
            ['icon'=>'fa-tools','title'=>'Installation','text'=>'The AC is installed with proper fitting.'],
            ['icon'=>'fa-check-circle','title'=>'Final Testing','text'=>'We test cooling and overall performance.']
        ]
    ];
}


/* =========================================================
   CHECK SERVICE
========================================================= */

if (!isset($services[$service_key])) {
    $first_key = array_key_first($services);
    $service_key = $first_key ?: 'ac_installation';
}

$service = $services[$service_key];

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>
<?php echo htmlspecialchars($service['name']); ?>
| Aqua Air Cooling
</title>

<link href="../css/bootstrap.min.css" rel="stylesheet">

<link
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
rel="stylesheet"
>

<link
href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@400;600;700;800&family=Roboto:wght@400;500;600;700&display=swap"
rel="stylesheet"
>

<style>

:root {
    --primary:#0d6efd;
    --dark:#07164f;
    --light:#f5faff;
    --text:#667085;
}

* {
    box-sizing:border-box;
}

body {
    margin:0;
    font-family:'Roboto',sans-serif;
    background:#fff;
    color:#222;
}

/* =====================================================
   BACK TO SERVICES BUTTON
===================================================== */

.back-service-wrapper {
    background: skyblue;
    padding: 15px 0;
}

.back-service-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;

    padding: 12px 24px;

    background: brown;
    color: #ffffff;

    border-radius: 30px;

    text-decoration: none;

    font-size: 15px;
    font-weight: 700;

    box-shadow: 0 5px 15px rgba(0,191,255,0.25);

    transition: all 0.3s ease;
}

.back-service-btn:hover {
    background: black;
    color: #ffffff;

    transform: translateX(-4px);

    box-shadow: 0 8px 20px rgba(0,143,209,0.30);
}

.back-service-btn i {
    font-size: 14px;
}

/* =========================================================
   SERVICE NAVBAR
========================================================= */

.service-navbar {
    width:100%;
    background:#07164f;
    box-shadow:0 5px 20px rgba(0,0,0,.12);
    position:sticky;
    top:0;
    z-index:9999;
}

.service-navbar .container {
    display:flex;
    align-items:center;
    gap:8px;
    overflow-x:auto;
    flex-wrap:nowrap;
    -webkit-overflow-scrolling:touch;
    padding-top:10px;
    padding-bottom:10px;
    scroll-behavior: smooth;
    scrollbar-width: thin;
}

.service-navbar .container::-webkit-scrollbar {
    height:4px;
}

.service-navbar .container::-webkit-scrollbar-thumb {
    background:#0d6efd;
    border-radius:10px;
}

.service-nav-btn {
    flex:0 0 auto;
    display:inline-flex;
    align-items:center;
    gap:7px;
    padding:10px 16px;
    border-radius:30px;
    background:rgba(255,255,255,.10);
    color:#fff;
    text-decoration:none;
    font-size:14px;
    font-weight:600;
    border:1px solid rgba(255,255,255,.15);
    transition:.3s;
}

.service-nav-btn:hover,
.service-nav-btn.active {
    background:#0d6efd;
    color:#fff;
    transform:translateY(-2px);
}


/* =========================================================
   HERO
========================================================= */

.service-hero {
    background:linear-gradient(120deg,#07164f,#0d6efd);
    padding:80px 0;
    color:#fff;
}

.service-badge {
    display:inline-block;
    padding:8px 18px;
    border-radius:50px;
    background:rgba(255,255,255,.12);
    border:1px solid rgba(255,255,255,.25);
    font-size:13px;
    font-weight:700;
    letter-spacing:1.5px;
    margin-bottom:20px;
}

.service-hero h1 {
    font-family:'Roboto Slab',serif;
    font-size:48px;
    font-weight:800;
    line-height:1.2;
    margin-bottom:20px;
}

.service-hero p {
    font-size:17px;
    line-height:1.8;
    color:rgba(255,255,255,.88);
    max-width:650px;
}

.service-price {
    display:inline-block;
    margin-top:20px;
    background:#fff;
    color:#0d6efd;
    padding:12px 22px;
    border-radius:50px;
    font-weight:700;
}

.service-hero-image {
    width:100%;
    height:420px;
    object-fit:cover;
    border-radius:25px;
    border:5px solid rgba(255,255,255,.15);
    box-shadow:0 20px 50px rgba(0,0,0,.25);
}


/* =========================================================
   ABOUT
========================================================= */

.service-about {
    padding:90px 0;
    background:skyblue;
}

.section-badge {
    color:var(--primary);
    font-size:13px;
    font-weight:700;
    letter-spacing:1.5px;
}

.section-title {
    color:var(--dark);
    font-family:'Roboto Slab',serif;
    font-size:40px;
    font-weight:800;
    margin:12px 0 20px;
}

.section-text {
    color:var(--text);
    font-size:16px;
    line-height:1.8;
}

.feature-list {
    margin-top:25px;
    padding:0;
    list-style:none;
}

.feature-list li {
    display:flex;
    align-items:center;
    gap:12px;
    margin-bottom:15px;
    color:#333;
    font-weight:500;
}

.feature-list i {
    width:28px;
    height:28px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:#eaf4ff;
    color:var(--primary);
    border-radius:50%;
    font-size:13px;
}


/* =========================================================
   PROCESS
========================================================= */

.process-section {
    padding:90px 0;
    background:silver;
}

.process-heading {
    text-align:center;
    max-width:700px;
    margin:0 auto 50px;
}

.process-card {
    background:#fff;
    padding:30px 25px;
    height:100%;
    border-radius:18px;
    border:1px solid #e8eef6;
    text-align:center;
    transition:.3s;
}

.process-card:hover {
    transform:translateY(-7px);
    box-shadow:0 15px 40px rgba(13,110,253,.12);
}

.process-icon {
    width:65px;
    height:65px;
    margin:0 auto 20px;
    display:flex;
    align-items:center;
    justify-content:center;
    border-radius:50%;
    background:#eaf4ff;
    color:var(--primary);
    font-size:25px;
}

.process-card h4 {
    font-family:'Roboto Slab',serif;
    color:var(--dark);
    font-weight:700;
    font-size:20px;
}

.process-card p {
    color:var(--text);
    font-size:14px;
    line-height:1.7;
}


/* =========================================================
   CTA
========================================================= */

.service-cta {
    padding:80px 20px;
    background:linear-gradient(120deg,#0d6efd,#084298);
    text-align:center;
    color:#fff;
}

.service-cta h2 {
    font-family:'Roboto Slab',serif;
    font-size:38px;
    font-weight:800;
    margin-bottom:15px;
}

.service-cta p {
    max-width:650px;
    margin:0 auto;
    color:rgba(255,255,255,.85);
    line-height:1.7;
}


/* =========================================================
   BOOK BUTTON
========================================================= */

.book-button {
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:10px;
    margin-top:25px;
    padding:13px 27px;
    border-radius:50px;
    background:#fff;
    color:#0d6efd;
    text-decoration:none;
    font-weight:700;
    border:none;
    cursor:pointer;
    transition:.3s;
}

.book-button:hover {
    transform:translateY(-3px);
    color:#0d6efd;
    box-shadow:0 10px 30px rgba(0,0,0,.2);
}


/* =========================================================
   LOGIN POPUP
========================================================= */

.login-popup-overlay {
    display:none;
    position:fixed;
    inset:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,.65);
    z-index:999999;
    align-items:center;
    justify-content:center;
    padding:20px;
}

.login-popup-box {
    position:relative;
    width:100%;
    max-width:450px;
    background:#fff;
    padding:40px 30px;
    border-radius:18px;
    text-align:center;
    box-shadow:0 20px 60px rgba(0,0,0,.30);
    animation:loginPopupShow .25s ease;
}

@keyframes loginPopupShow {
    from {
        opacity:0;
        transform:scale(.8);
    }
    to {
        opacity:1;
        transform:scale(1);
    }
}

.login-popup-close {
    position:absolute;
    top:12px;
    right:18px;
    width:35px;
    height:35px;
    border:none;
    background:transparent;
    font-size:28px;
    color:#777;
    cursor:pointer;
}

.login-popup-close:hover {
    color:#000;
}

.login-popup-icon {
    width:70px;
    height:70px;
    margin:0 auto 18px;
    border-radius:50%;
    background:#eaf4ff;
    display:flex;
    align-items:center;
    justify-content:center;
}

.login-popup-icon i {
    font-size:30px;
    color:#0d6efd;
}

.login-popup-box h2 {
    margin-bottom:12px;
    color:#222;
    font-family:'Roboto Slab',serif;
    font-size:30px;
    font-weight:800;
}

.login-popup-box p {
    color:#666;
    font-size:16px;
    line-height:1.6;
    margin-bottom:25px;
}

.login-popup-buttons {
    display:flex;
    justify-content:center;
    gap:12px;
}

.login-popup-login,
.login-popup-cancel {
    border:none;
    padding:11px 25px;
    border-radius:8px;
    font-size:15px;
    font-weight:600;
    cursor:pointer;
    text-decoration:none;
    transition:.25s;
}

.login-popup-login {
    background:#0d6efd;
    color:#fff;
}

.login-popup-login:hover {
    background:#084298;
    color:#fff;
}

.login-popup-cancel {
    background:#e9ecef;
    color:#333;
}

.login-popup-cancel:hover {
    background:#d6d8db;
}


/* =========================================================
   MOBILE
========================================================= */

@media(max-width:767px) {

    .service-navbar .container {
        padding-left:12px;
        padding-right:12px;
    }

    .service-nav-btn {
        font-size:13px;
        padding:9px 14px;
    }

    .service-hero {
        padding:55px 0;
    }

    .service-hero h1 {
        font-size:34px;
    }

    .service-hero-image {
        height:300px;
        margin-top:35px;
    }

    .service-about,
    .process-section {
        padding:60px 0;
    }

    .section-title {
        font-size:30px;
    }

    .service-cta h2 {
        font-size:30px;
    }
}

@media(max-width:480px) {

    .service-hero h1 {
        font-size: clamp(22px, 6.5vw, 28px);
    }

    .service-hero-image {
        height: 230px;
        margin-top: 25px;
    }

    .back-service-btn {
        padding: 9px 18px;
        font-size: 13.5px;
    }

    .login-popup-box {
        padding:35px 20px;
    }

    .login-popup-box h2 {
        font-size:25px;
    }

    .login-popup-buttons {
        flex-direction:column;
    }

    .login-popup-login,
    .login-popup-cancel {
        width:100%;
    }
}

</style>

</head>

<body>

<div class="back-service-wrapper">

    <div class="container">

        <a
            href="../services.php"
            class="back-service-btn"
        >
            <i class="fas fa-arrow-left"></i>
            Back to All Services
        </a>

    </div>

</div>


<!-- =========================================================
     SERVICE NAVBAR
========================================================= -->

<nav class="service-navbar">

<div class="container">

<?php foreach ($services as $key => $navService): ?>

<a
    href="service.php?service=<?php echo urlencode($key); ?>"
    class="service-nav-btn <?php echo ($key === $service_key) ? 'active' : ''; ?>"
>

<?php

$icons = [
    'ac_installation' => 'fa-snowflake',
    'ac_pipe' => 'fa-circle',
    'ac_insulation' => 'fa-shield-halved',
    'ac_tape' => 'fa-tape',
    'ac_cable' => 'fa-bolt',
    'ac_repair' => 'fa-tools',
    'ac_gas' => 'fa-wind',
    'ac_maintenance' => 'fa-cog',
    'ac_cleaning' => 'fa-broom',
    'ac_stand' => 'fa-layer-group',
    'ac_uninstallation' => 'fa-times-circle',
    'ac_amc' => 'fa-calendar-check'
];

?>

<i class="fas <?php echo isset($icons[$key]) ? $icons[$key] : 'fa-tools'; ?>"></i>

<?php echo htmlspecialchars($navService['name']); ?>

</a>

<?php endforeach; ?>

</div>

</nav>


<!-- =========================================================
     HERO
========================================================= -->

<section class="service-hero">

<div class="container">

<div class="row align-items-center g-5">

<div class="col-lg-6">

<span class="service-badge">

<?php echo htmlspecialchars($service['badge']); ?>

</span>

<h1>

<?php echo htmlspecialchars($service['title']); ?>

</h1>

<p>

<?php echo htmlspecialchars($service['description']); ?>

</p>

<span class="service-price">

<?php echo htmlspecialchars($service['price']); ?>

</span>


<!-- TOP BOOK BUTTON -->

<br>

<button
    type="button"
    class="book-button"
    onclick="handleBooking()"
>

Book This Service

<i class="fas fa-arrow-right"></i>

</button>

</div>


<div class="col-lg-6">

<img
    src="<?php echo htmlspecialchars($service['image']); ?>"
    alt="<?php echo htmlspecialchars($service['name']); ?>"
    class="service-hero-image"
>

</div>

</div>

</div>

</section>


<!-- =========================================================
     ABOUT SERVICE
========================================================= -->

<section class="service-about">

<div class="container">

<div class="row align-items-center">

<div class="col-lg-6">

<span class="section-badge">

ABOUT THIS SERVICE

</span>

<h2 class="section-title">

<?php echo htmlspecialchars($service['name']); ?>

</h2>

<p class="section-text">

<?php echo htmlspecialchars($service['description']); ?>

</p>


<ul class="feature-list">

<?php foreach ($service['features'] as $feature): ?>

<li>

<i class="fas fa-check"></i>

<?php echo htmlspecialchars($feature); ?>

</li>

<?php endforeach; ?>

</ul>

</div>


<div class="col-lg-6 mt-5 mt-lg-0">

<div
style="
background:#f5faff;
padding:40px;
border-radius:25px;
"
>

<h3
style="
color:#07164f;
font-family:'Roboto Slab';
font-weight:700;
margin-bottom:20px;
"
>

Why Choose Our Service?

</h3>

<p class="section-text">

Our experienced technicians use professional
tools and proper techniques to provide reliable
AC service. We focus on quality work,
customer satisfaction and long-lasting results.

</p>

</div>

</div>

</div>

</div>

</section>


<!-- =========================================================
     PROCESS
========================================================= -->

<section class="process-section">

<div class="container">

<div class="process-heading">

<span class="section-badge">

OUR PROCESS

</span>

<h2 class="section-title">

Simple & Professional Service

</h2>

<p class="section-text">

We follow a simple process to make your
service experience smooth and hassle-free.

</p>

</div>


<div class="row g-4">

<?php foreach ($service['process'] as $process): ?>

<div class="col-lg-3 col-md-6">

<div class="process-card">

<div class="process-icon">

<i class="fas <?php echo htmlspecialchars($process['icon']); ?>"></i>

</div>

<h4>

<?php echo htmlspecialchars($process['title']); ?>

</h4>

<p>

<?php echo htmlspecialchars($process['text']); ?>

</p>

</div>

</div>

<?php endforeach; ?>

</div>

</div>

</section>


<!-- =========================================================
     CTA
========================================================= -->

<section class="service-cta">

<div class="container">

<h2>

Need

<?php echo htmlspecialchars($service['name']); ?>?

</h2>

<p>

Book your service today and let our
professional technicians take care of your AC.

</p>


<!-- BOTTOM BOOK BUTTON -->

<button
    type="button"
    class="book-button"
    onclick="handleBooking()"
>

Book This Service

<i class="fas fa-arrow-right"></i>

</button>

</div>

</section>


<!-- =========================================================
     LOGIN POPUP
========================================================= -->

<div
id="loginPopup"
class="login-popup-overlay"
>

<div class="login-popup-box">


<button
type="button"
class="login-popup-close"
onclick="closeLoginPopup()"
aria-label="Close"
>

&times;

</button>


<div class="login-popup-icon">

<i class="fas fa-lock"></i>

</div>


<h2>

Login Required

</h2>


<p>

Please login to your account before booking

<strong>

<?php echo htmlspecialchars($service['name']); ?>

</strong>.

</p>


<div class="login-popup-buttons">


<a
href="../login.php"
class="login-popup-login"
>

<i class="fas fa-user"></i>

&nbsp; Login

</a>


<button
type="button"
class="login-popup-cancel"
onclick="closeLoginPopup()"
>

Cancel

</button>


</div>

</div>

</div>


<script src="../js/bootstrap.bundle.min.js"></script>

<script>

/* =========================================================
   BOOKING
========================================================= */

function handleBooking()
{

<?php if ($is_logged_in): ?>

    window.location.href = "../booking.php?service_name=" + encodeURIComponent("<?php echo addslashes($service['name']); ?>");

<?php else: ?>

    showLoginPopup();

<?php endif; ?>

}


/* =========================================================
   SHOW POPUP
========================================================= */

function showLoginPopup()
{

    const popup = document.getElementById("loginPopup");

    if (popup)
    {
        popup.style.display = "flex";
        document.body.style.overflow = "hidden";
    }

}


/* =========================================================
   CLOSE POPUP
========================================================= */

function closeLoginPopup()
{

    const popup = document.getElementById("loginPopup");

    if (popup)
    {
        popup.style.display = "none";
        document.body.style.overflow = "";
    }

}


/* =========================================================
   OUTSIDE CLICK
========================================================= */

document.addEventListener("DOMContentLoaded", function()
{

    const popup = document.getElementById("loginPopup");

    if (popup)
    {

        popup.addEventListener("click", function(event)
        {

            if (event.target === popup)
            {
                closeLoginPopup();
            }

        });

    }

});


/* =========================================================
   ESC
========================================================= */

document.addEventListener("keydown", function(event)
{

    if (event.key === "Escape")
    {
        closeLoginPopup();
    }

});

</script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const navbar = document.querySelector(
        ".service-navbar .container"
    );

    const activeButton = document.querySelector(
        ".service-nav-btn.active"
    );

    if (!navbar || !activeButton) {
        return;
    }

    const navbarWidth = navbar.clientWidth;

    const buttonLeft = activeButton.offsetLeft;

    const buttonWidth = activeButton.offsetWidth;

    let scrollPosition =
        buttonLeft -
        (navbarWidth / 2) +
        (buttonWidth / 2);

    const maxScroll =
        navbar.scrollWidth -
        navbar.clientWidth;

    scrollPosition = Math.max(
        0,
        Math.min(
            scrollPosition,
            maxScroll
        )
    );

    navbar.scrollTo({
        left: scrollPosition,
        behavior: "smooth"
    });

});
</script>

</body>

</html>