<?php
// Start session before using session variables
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!-- Navbar Start -->
<nav class="navbar navbar-expand-lg bg-white navbar-light px-4 px-lg-5">

    <!-- Logo -->
    <a href="index.php" class="navbar-brand aqua-logo">
    <div class="aqua-logo-icon">❄</div>

    <div class="aqua-logo-text">
        <span>Aqua Air</span>
        <small>COOLING</small>
    </div>
    </a>

    <!-- Mobile Toggle -->
    <button type="button"
            class="navbar-toggler"
            data-bs-toggle="collapse"
            data-bs-target="#navbarCollapse">

        <span class="navbar-toggler-icon"></span>

    </button>


    <!-- Navbar -->
    <div class="collapse navbar-collapse" id="navbarCollapse">

        <!-- Menu -->
        <div class="navbar-nav mx-auto">

            <!-- Home -->
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>"
               href="index.php">
                Home
            </a>


            <!-- About -->
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>"
               href="about.php">
                About
            </a>


            <!-- Services -->
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'services.php' ? 'active' : ''; ?>"
               href="services.php">
                Services
            </a>


            <!-- Booking -->
            <?php if (isset($_SESSION['user_id'])): ?>

                <!-- User Logged In -->
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'booking.php' ? 'active' : ''; ?>"
                   href="booking.php">
                    Booking
                </a>

            <?php else: ?>

                <!-- User Not Logged In -->
                <a class="nav-link"
                   href="javascript:void(0);"
                   onclick="showLoginMessage();">
                    Booking
                </a>

            <?php endif; ?>


            <!-- Portfolio -->
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'portfolio.php' ? 'active' : ''; ?>"
               href="portfolio.php">
                Portfolio
            </a>


            <!-- Contact -->
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>"
               href="contact.php">
                Contact
            </a>
                

            <!-- Terms & Condition -->
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'Terms&Condition.php' ? 'active' : ''; ?>"
                href="Terms&Condition.php">
                Terms & Condition
                
            </a>
        </div>


        <!-- Login / Logout Button -->
        <div class="d-flex align-items-center mx-3 my-3 my-lg-0">

            <?php if (isset($_SESSION['user_id'])): ?>

                <!-- Logged In User -->

                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>

            <?php else: ?>

                <!-- Not Logged In -->

                <a href="login.php"
                   class="btn btn-primary rounded-pill px-4 py-2 login-btn"
                   style="display:inline-flex !important; opacity:1 !important; visibility:visible !important;">

                    <i class="fas fa-user me-2"></i>
                    Login

                </a>

            <?php endif; ?>

        </div>


        <!-- Social Icons -->
        <div class="social-icons">

    <a href="#" class="social-btn facebook" title="Facebook">
        <i class="fab fa-facebook-f"></i>
    </a>

    <a href="#" class="social-btn whatsapp" title="WhatsApp">
        <i class="fab fa-whatsapp"></i>
    </a>

    <a href="#" class="social-btn youtube" title="YouTube">
        <i class="fab fa-youtube"></i>
    </a>

    <a href="#" class="social-btn instagram" title="Instagram">
        <i class="fab fa-instagram"></i>
    </a>

</div>

    </div>

</nav>
<!-- Navbar End -->



<!-- ========================================================= -->
<!-- LOGIN REQUIRED POPUP -->
<!-- ========================================================= -->

<div id="loginMessage" class="login-message-overlay">

    <div class="login-message-box">

        <!-- Close Button -->
        <button type="button"
                class="close-message"
                onclick="closeLoginMessage();">

            &times;

        </button>


        <!-- Lock Icon -->
        <div class="login-icon">

            <i class="fas fa-lock"></i>

        </div>


        <!-- Title -->
        <h3>
            Login Required
        </h3>


        <!-- Dynamic Message -->
        <p id="loginMessageText">
            Please login to your account before booking a service.
        </p>


        <!-- Buttons -->
        <div class="login-message-buttons">

            <a href="login.php"
               class="login-popup-btn">

                <i class="fas fa-user me-2"></i>
                Login

            </a>


            <button type="button"
                    class="cancel-popup-btn"
                    onclick="closeLoginMessage();">

                Cancel

            </button>

        </div>

    </div>

</div>



<!-- ========================================================= -->
<!-- LOGIN POPUP CSS -->
<!-- ========================================================= -->

<style>

/* Mobile Responsive Navbar Menu Styling */
@media (max-width: 991.98px) {
    #navbarCollapse.collapse.show {
        display: block !important;
        background: #ffffff !important;
        padding: 15px 20px !important;
        border-radius: 12px !important;
        margin-top: 10px !important;
        box-shadow: 0 10px 30px rgba(0,0,0,0.12) !important;
        border: 1px solid #e2e8f0 !important;
    }
    #navbarCollapse .navbar-nav .nav-link {
        padding: 12px 16px !important;
        font-size: 16px !important;
        font-weight: 600 !important;
        color: #1e293b !important;
        border-bottom: 1px solid #f1f5f9 !important;
        display: block !important;
        text-align: left !important;
    }
    #navbarCollapse .navbar-nav .nav-link:hover,
    #navbarCollapse .navbar-nav .nav-link.active {
        color: #0d6efd !important;
        background: #f8fafc !important;
        border-radius: 8px !important;
    }
    .navbar-toggler {
        cursor: pointer;
        z-index: 9999;
    }
}

.login-message-overlay {

    display: none;

    position: fixed;

    top: 0;
    left: 0;

    width: 100%;
    height: 100%;

    background: rgba(0, 0, 0, 0.60);

    z-index: 999999;

    justify-content: center;
    align-items: center;

}


.login-message-box {

    position: relative;

    width: 90%;
    max-width: 420px;

    background: #ffffff;

    padding: 35px 30px;

    border-radius: 15px;

    text-align: center;

    box-shadow: 0 10px 40px rgba(0,0,0,0.25);

    animation: loginPopupAnimation 0.25s ease;

}


@keyframes loginPopupAnimation {

    from {

        opacity: 0;

        transform: scale(0.80);

    }

    to {

        opacity: 1;

        transform: scale(1);

    }

}


.close-message {

    position: absolute;

    top: 8px;
    right: 15px;

    border: none;

    background: transparent;

    font-size: 30px;

    color: #777;

    cursor: pointer;

}


.close-message:hover {

    color: #000;

}


.login-icon {

    width: 65px;
    height: 65px;

    margin: 0 auto 15px;

    border-radius: 50%;

    background: #eaf4ff;

    display: flex;

    align-items: center;

    justify-content: center;

}


.login-icon i {

    font-size: 28px;

    color: #0d6efd;

}


.login-message-box h3 {

    margin-bottom: 10px;

    color: #222;

    font-weight: 600;

}


.login-message-box p {

    color: #666;

    margin-bottom: 25px;

    line-height: 1.6;

}


.login-message-buttons {

    display: flex;

    justify-content: center;

    gap: 10px;

}


.login-popup-btn,
.cancel-popup-btn {

    padding: 10px 25px;

    border-radius: 7px;

    border: none;

    text-decoration: none;

    cursor: pointer;

    font-size: 15px;

    transition: 0.2s;

}


.login-popup-btn {

    background: #0d6efd;

    color: #ffffff;

}


.login-popup-btn:hover {

    background: #0b5ed7;

    color: #ffffff;

}


.cancel-popup-btn {

    background: #e9ecef;

    color: #333;

}


.cancel-popup-btn:hover {

    background: #d6d8db;

}


@media (max-width: 480px) {

    .login-message-box {

        padding: 30px 20px;

    }


    .login-message-buttons {

        flex-direction: column;

    }


    .login-popup-btn,
    .cancel-popup-btn {

        width: 100%;

    }

}

.logout-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;

    padding: 12px 25px;

    background: #e63946;
    color: #ffffff;

    border-radius: 35px;

    text-decoration: none;

    font-size: 16px;
    font-weight: 600;

    white-space: nowrap;

    min-width: 130px;
}

.logout-btn i {
    font-size: 16px;
    margin: 0;
}

.logout-btn:hover {
    background: #c92f3c;
    color: #ffffff;
}

/* ==============================
   DYNAMIC GLASS SOCIAL BUTTONS
   ============================== */

.social-icons {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-left: 18px;
}

.social-btn {
    position: relative;
    width: 44px;
    height: 44px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 14px;

    /* Glass Effect */
    background: linear-gradient(135deg, #e0f2fe, #ffffff);
    border: 1px solid #bae6fd;
    box-shadow: 0 5px 15px rgba(14, 165, 233, 0.12);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);

    border: 1px solid rgba(255, 255, 255, 0.7);

    box-shadow:
        0 5px 15px rgba(0, 0, 0, 0.08),
        inset 0 1px 1px rgba(255, 255, 255, 0.8);

    text-decoration: none;

    transition:
        transform 0.35s ease,
        box-shadow 0.35s ease,
        background 0.35s ease;
}

/* Icon */
.social-btn i {
    font-size: 18px;
    transition: all 0.35s ease;
}

/* ==============================
   FACEBOOK
   ============================== */

.social-btn.facebook {
    color: #1877f2;
}

.social-btn.facebook:hover {
    background: rgba(24, 119, 242, 0.15);

    box-shadow:
        0 10px 25px rgba(24, 119, 242, 0.30),
        inset 0 1px 2px rgba(255, 255, 255, 0.9);

    transform: translateY(-5px) scale(1.05);
}

.social-btn.facebook:hover i {
    transform: scale(1.2);
}


/* ==============================
   WHATSAPP
   ============================== */

.social-btn.whatsapp {
    color: #25d366;
}

.social-btn.whatsapp:hover {
    background: rgba(37, 211, 102, 0.15);

    box-shadow:
        0 10px 25px rgba(37, 211, 102, 0.30),
        inset 0 1px 2px rgba(255, 255, 255, 0.9);

    transform: translateY(-5px) scale(1.05);
}

.social-btn.whatsapp:hover i {
    transform: scale(1.2) rotate(8deg);
}


/* ==============================
   YOUTUBE
   ============================== */

.social-btn.youtube {
    color: #ff0000;
}

.social-btn.youtube:hover {
    background: rgba(255, 0, 0, 0.12);

    box-shadow:
        0 10px 25px rgba(255, 0, 0, 0.28),
        inset 0 1px 2px rgba(255, 255, 255, 0.9);

    transform: translateY(-5px) scale(1.05);
}

.social-btn.youtube:hover i {
    transform: scale(1.2);
}


/* ==============================
   INSTAGRAM
   ============================== */

.social-btn.instagram {
    color: #e4405f;
}

.social-btn.instagram:hover {
    background: rgba(228, 64, 95, 0.13);

    box-shadow:
        0 10px 25px rgba(228, 64, 95, 0.30),
        inset 0 1px 2px rgba(255, 255, 255, 0.9);

    transform: translateY(-5px) scale(1.05);
}

.social-btn.instagram:hover i {
    transform: scale(1.2) rotate(-8deg);
}


/* ==============================
   GLASS SHINE EFFECT
   ============================== */

.social-btn::before {
    content: "";

    position: absolute;

    top: 2px;
    left: 8px;

    width: 22px;
    height: 8px;

    border-radius: 50%;

    background: rgba(255, 255, 255, 0.55);

    filter: blur(4px);

    opacity: 0.7;

    pointer-events: none;
}


/* ==============================
   CLICK EFFECT
   ============================== */

.social-btn:active {
    transform: scale(0.92);
}


/* ==============================
   MOBILE
   ============================== */

@media (max-width: 768px) {

    .social-icons {
        gap: 8px;
        margin-left: 10px;
    }

    .social-btn {
        width: 40px;
        height: 40px;
        border-radius: 12px;
    }

    .social-btn i {
        font-size: 16px;
    }

}

/* =========================================
   PREMIUM DYNAMIC NAVBAR
========================================= */

.navbar-nav .nav-link {
    position: relative;

    padding: 10px 17px !important;
    margin: 0 3px;

    border-radius: 14px;

    color: #0b1740 !important;
    font-weight: 500;

    overflow: hidden;

    transition:
        color 0.35s ease,
        transform 0.35s ease,
        box-shadow 0.35s ease,
        background 0.35s ease;
}


/* =========================================
   MOVING GLASS SHINE
========================================= */

.navbar-nav .nav-link::before {
    content: "";

    position: absolute;

    top: 0;
    left: -120%;

    width: 70%;
    height: 100%;

    background: linear-gradient(
        120deg,
        transparent,
        rgba(255,255,255,0.8),
        transparent
    );

    transform: skewX(-25deg);

    transition: left 0.6s ease;

    pointer-events: none;
}


/* Shine animation */

.navbar-nav .nav-link:hover::before {
    left: 140%;
}


/* =========================================
   HOVER
========================================= */

.navbar-nav .nav-link:hover {

    color: #0284c7 !important;

    background: linear-gradient(
        135deg,
        #e0f7ff,
        #ffffff
    );

    transform: translateY(-4px);

    box-shadow:
        0 8px 20px rgba(14,165,233,0.18),
        inset 0 1px 2px rgba(255,255,255,0.9);
}


/* =========================================
   ACTIVE BUTTON
========================================= */

.navbar-nav .nav-link.active {

    color: #0284c7 !important;

    background: linear-gradient(
        135deg,
        #dff6ff,
        #ffffff
    );

    box-shadow:
        0 6px 18px rgba(14,165,233,0.18);
}


/* =========================================
   ACTIVE ANIMATED LINE
========================================= */

.navbar-nav .nav-link.active::after {

    content: "";

    position: absolute;

    bottom: 4px;
    left: 50%;

    width: 8px;
    height: 3px;

    border-radius: 20px;

    background: #0ea5e9;

    transform: translateX(-50%);

    animation: navIndicator 1.5s ease-in-out infinite;
}


@keyframes navIndicator {

    0% {
        width: 8px;
        opacity: 0.5;
    }

    50% {
        width: 28px;
        opacity: 1;
    }

    100% {
        width: 8px;
        opacity: 0.5;
    }
}


/* =========================================
   CLICK EFFECT
========================================= */

.navbar-nav .nav-link:active {
    transform: scale(0.92);
}


/* =========================================
   NAVBAR GAP
========================================= */

.navbar-nav {
    gap: 5px;
}

.navbar {
    transition: all 0.4s ease;
}

.navbar.scrolled {
    background: rgba(255,255,255,0.88) !important;

    backdrop-filter: blur(15px);
    -webkit-backdrop-filter: blur(15px);

    box-shadow:
        0 8px 30px rgba(0,0,0,0.08);

    border-bottom: 1px solid rgba(14,165,233,0.12);
}

.dynamic-logo {
    display: flex;
    align-items: center;
    text-decoration: none;
}

.dynamic-logo img {
    width: 500*px;
    height: auto;
    display: block;

    transition: all 0.4s ease;
}

/* Dynamic hover */
.dynamic-logo:hover img {
    transform: scale(1.04) translateY(-2px);

    filter:
        drop-shadow(0 8px 15px rgba(14, 165, 233, 0.25));
}

/* Mobile */
@media (max-width: 768px) {
    .dynamic-logo img {
        width: 180px;
    }
}

.aqua-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none !important;
}

/* Icon */
.aqua-logo-icon {
    width: 60px;
    height: 60px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 15px;

    font-size: 29px;
    color: #0ea5e9;

    background: linear-gradient(
        145deg,
        #e0f7ff,
        #ffffff
    );

    border: 1px solid #bae6fd;

    box-shadow:
        0 6px 18px rgba(14,165,233,.18),
        inset 0 1px 3px rgba(255,255,255,.9);

    transition: .4s ease;
}

/* Text */
.aqua-logo-text {
    display: flex;
    flex-direction: column;
    line-height: 1;
}

.aqua-logo-text span {
    font-size: 40px;
    font-weight: 800;

    background: linear-gradient(
        90deg,
        #07143f,
        #0284c7
    );

    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.aqua-logo-text small {
    margin-top: 5px;

    font-size: 17px;
    font-weight: 700;

    letter-spacing: 4px;
    color: #0ea5e9;
}

/* Hover */
.aqua-logo:hover .aqua-logo-icon {
    transform: translateY(-4px) rotate(-8deg) scale(1.08);

    box-shadow:
        0 10px 25px rgba(14,165,233,.30),
        0 0 18px rgba(56,189,248,.20);
}

.aqua-logo:hover .aqua-logo-text span {
    letter-spacing: .3px;
}

/* =========================================
   RESPONSIVE NAVBAR (MOBILE & TABLET)
   ========================================= */
@media (max-width: 991.98px) {
    .navbar {
        padding: 8px 16px !important;
    }

    .aqua-logo {
        gap: 8px;
    }

    .aqua-logo-icon {
        width: 44px;
        height: 44px;
        font-size: 22px;
        border-radius: 12px;
    }

    .aqua-logo-text span {
        font-size: 26px;
    }

    .aqua-logo-text small {
        font-size: 11px;
        letter-spacing: 2px;
        margin-top: 2px;
    }

    .navbar-toggler {
        border: 1px solid rgba(14,165,233,0.3);
        border-radius: 10px;
        padding: 6px 10px;
        outline: none !important;
        box-shadow: none !important;
    }

    .navbar-collapse {
        background: #ffffff;
        border-radius: 16px;
        padding: 16px 12px;
        margin-top: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        border: 1px solid rgba(14,165,233,0.15);
    }

    .navbar-nav {
        gap: 4px;
        width: 100%;
    }

    .navbar-nav .nav-link {
        width: 100%;
        text-align: left;
        padding: 10px 16px !important;
        border-radius: 10px;
        margin: 2px 0;
        font-size: 14px;
    }

    .navbar-nav .nav-link.active::after {
        left: 16px;
        transform: none;
    }

    .login-btn, .logout-btn {
        width: 100%;
        justify-content: center;
        margin: 8px 0;
    }

    .social-icons {
        justify-content: center;
        width: 100%;
        margin-top: 10px;
    }
}

@media (max-width: 479.98px) {
    .navbar {
        padding: 6px 10px !important;
    }

    .aqua-logo-icon {
        width: 38px;
        height: 38px;
        font-size: 18px;
        border-radius: 10px;
    }

    .aqua-logo-text span {
        font-size: 21px;
    }

    .aqua-logo-text small {
        font-size: 9.5px;
        letter-spacing: 1.5px;
    }
}
</style>



<!-- ========================================================= -->
<!-- LOGIN POPUP JAVASCRIPT -->
<!-- ========================================================= -->

<script>

function showLoginMessage() {

    const popup = document.getElementById("loginMessage");

    popup.style.display = "flex";

}


function closeLoginMessage() {

    const popup = document.getElementById("loginMessage");

    popup.style.display = "none";

}


/* Close popup when clicking outside */

document.getElementById("loginMessage").addEventListener("click", function(event) {

    if (event.target === this) {

        closeLoginMessage();

    }

});


/* Close popup with ESC key */

document.addEventListener("keydown", function(event) {

    if (event.key === "Escape") {

        closeLoginMessage();

    }

});

window.addEventListener("scroll", function () {

    const navbar = document.querySelector(".navbar");

    if (window.scrollY > 30) {
        navbar.classList.add("scrolled");
    } else {
        navbar.classList.remove("scrolled");
    }

});

// Reliable Mobile Navbar Toggle
document.addEventListener("DOMContentLoaded", function () {
    const toggler = document.querySelector(".navbar-toggler");
    const collapse = document.getElementById("navbarCollapse");

    if (toggler && collapse) {
        toggler.addEventListener("click", function (e) {
            e.preventDefault();
            e.stopPropagation();
            collapse.classList.toggle("show");
        });

        // Close navbar when clicking any navigation link
        const navLinks = collapse.querySelectorAll(".nav-link");
        navLinks.forEach(function (link) {
            link.addEventListener("click", function () {
                if (window.innerWidth < 992) {
                    collapse.classList.remove("show");
                }
            });
        });

        // Close navbar when tapping outside
        document.addEventListener("click", function (e) {
            if (window.innerWidth < 992 && collapse.classList.contains("show")) {
                if (!collapse.contains(e.target) && !toggler.contains(e.target)) {
                    collapse.classList.remove("show");
                }
            }
        });
    }
});

</script>
