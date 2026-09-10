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
            onclick="openAquaMobileMenu(event)"
            aria-label="Toggle navigation">

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
<!-- DYNAMIC MOBILE MENU DRAWER (OFFCANVAS) -->
<!-- ========================================================= -->
<div id="aquaDrawerBackdrop" class="aqua-drawer-backdrop" onclick="closeAquaMobileMenu()"></div>

<div id="aquaMobileDrawer" class="aqua-mobile-drawer">
    <!-- Drawer Header -->
    <div class="aqua-drawer-header">
        <a href="index.php" class="aqua-drawer-brand">
            <div class="drawer-logo-icon">❄</div>
            <div class="drawer-logo-text">
                <span>Aqua Air</span>
                <small>COOLING</small>
            </div>
        </a>
        <button type="button" class="aqua-drawer-close" onclick="closeAquaMobileMenu()" aria-label="Close Menu">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- User Profile or Welcome Card -->
    <div class="aqua-drawer-user-card">
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="drawer-user-info">
                <div class="drawer-avatar">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="drawer-user-text">
                    <span class="drawer-greeting">Welcome back,</span>
                    <strong class="drawer-username"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Customer'); ?></strong>
                </div>
            </div>
            <div class="drawer-user-actions">
                <a href="user_dashboard.php" class="drawer-chip-btn">
                    <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                </a>
                <a href="my_bookings.php" class="drawer-chip-btn">
                    <i class="fas fa-calendar-check me-1"></i> My Bookings
                </a>
            </div>
        <?php else: ?>
            <div class="drawer-user-info">
                <div class="drawer-avatar guest">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="drawer-user-text">
                    <strong class="drawer-username">Aqua Air Cooling</strong>
                    <span class="drawer-greeting">⭐ Trusted AC Services In Rajkot</span>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Dynamic Navigation Buttons -->
    <div class="aqua-drawer-menu">
        <div class="drawer-section-title">Navigation Menu</div>

        <!-- Home -->
        <a href="index.php" class="drawer-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">
            <div class="drawer-nav-icon bg-primary-soft">
                <i class="fas fa-home"></i>
            </div>
            <div class="drawer-nav-text">
                <span class="title">Home</span>
                <span class="desc">Main homepage & overview</span>
            </div>
            <i class="fas fa-chevron-right drawer-chevron"></i>
        </a>

        <!-- Book Service (Highlighted) -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="booking.php" class="drawer-nav-item highlight-booking <?php echo (basename($_SERVER['PHP_SELF']) == 'booking.php') ? 'active' : ''; ?>">
                <div class="drawer-nav-icon bg-accent-soft">
                    <i class="fas fa-calendar-plus"></i>
                </div>
                <div class="drawer-nav-text">
                    <span class="title">Book AC Service <span class="badge-instant">60 Min</span></span>
                    <span class="desc">Book repair, cleaning, gas refill</span>
                </div>
                <i class="fas fa-chevron-right drawer-chevron"></i>
            </a>
        <?php else: ?>
            <a href="javascript:void(0);" onclick="closeAquaMobileMenu(); showLoginMessage();" class="drawer-nav-item highlight-booking">
                <div class="drawer-nav-icon bg-accent-soft">
                    <i class="fas fa-calendar-plus"></i>
                </div>
                <div class="drawer-nav-text">
                    <span class="title">Book AC Service <span class="badge-instant">Instant</span></span>
                    <span class="desc">Login required to book service</span>
                </div>
                <i class="fas fa-chevron-right drawer-chevron"></i>
            </a>
        <?php endif; ?>

        <!-- Services -->
        <a href="services.php" class="drawer-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'services.php') ? 'active' : ''; ?>">
            <div class="drawer-nav-icon bg-info-soft">
                <i class="fas fa-tools"></i>
            </div>
            <div class="drawer-nav-text">
                <span class="title">Our Services</span>
                <span class="desc">Installation, Jet Wash, Gas, AMC</span>
            </div>
            <i class="fas fa-chevron-right drawer-chevron"></i>
        </a>

        <!-- If user logged in: Dashboard & My Bookings -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="user_dashboard.php" class="drawer-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'user_dashboard.php') ? 'active' : ''; ?>">
                <div class="drawer-nav-icon bg-success-soft">
                    <i class="fas fa-tachometer-alt"></i>
                </div>
                <div class="drawer-nav-text">
                    <span class="title">User Dashboard</span>
                    <span class="desc">Manage profile & booking status</span>
                </div>
                <i class="fas fa-chevron-right drawer-chevron"></i>
            </a>

            <a href="my_bookings.php" class="drawer-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'my_bookings.php') ? 'active' : ''; ?>">
                <div class="drawer-nav-icon bg-purple-soft">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="drawer-nav-text">
                    <span class="title">My Bookings History</span>
                    <span class="desc">Track technician & receipts</span>
                </div>
                <i class="fas fa-chevron-right drawer-chevron"></i>
            </a>
        <?php endif; ?>

        <!-- Portfolio -->
        <a href="portfolio.php" class="drawer-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'portfolio.php') ? 'active' : ''; ?>">
            <div class="drawer-nav-icon bg-warning-soft">
                <i class="fas fa-images"></i>
            </div>
            <div class="drawer-nav-text">
                <span class="title">Portfolio</span>
                <span class="desc">Live photos of our completed work</span>
            </div>
            <i class="fas fa-chevron-right drawer-chevron"></i>
        </a>

        <!-- About Us -->
        <a href="about.php" class="drawer-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'about.php') ? 'active' : ''; ?>">
            <div class="drawer-nav-icon bg-blue-soft">
                <i class="fas fa-info-circle"></i>
            </div>
            <div class="drawer-nav-text">
                <span class="title">About Aqua Air</span>
                <span class="desc">Company info, team & guarantees</span>
            </div>
            <i class="fas fa-chevron-right drawer-chevron"></i>
        </a>

        <!-- Contact -->
        <a href="contact.php" class="drawer-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'contact.php') ? 'active' : ''; ?>">
            <div class="drawer-nav-icon bg-teal-soft">
                <i class="fas fa-headset"></i>
            </div>
            <div class="drawer-nav-text">
                <span class="title">Contact Support</span>
                <span class="desc">Get in touch with technician</span>
            </div>
            <i class="fas fa-chevron-right drawer-chevron"></i>
        </a>

        <!-- Terms & Condition -->
        <a href="Terms&Condition.php" class="drawer-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'Terms&Condition.php') ? 'active' : ''; ?>">
            <div class="drawer-nav-icon bg-secondary-soft">
                <i class="fas fa-file-contract"></i>
            </div>
            <div class="drawer-nav-text">
                <span class="title">Terms & Conditions</span>
                <span class="desc">Service policies, warranty & rules</span>
            </div>
            <i class="fas fa-chevron-right drawer-chevron"></i>
        </a>
    </div>

    <!-- Drawer Footer Actions -->
    <div class="aqua-drawer-footer">
        <div class="drawer-contact-buttons">
            <a href="tel:+916354911971" class="drawer-btn-call">
                <i class="fas fa-phone-alt"></i> Call Now
            </a>
            <a href="https://wa.me/916354911971?text=Hello%20Aqua%20Air%20Cooling" target="_blank" class="drawer-btn-wa">
                <i class="fab fa-whatsapp"></i> WhatsApp
            </a>
        </div>
        <div class="drawer-auth-btn-wrap">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="logout.php" class="drawer-auth-btn logout">
                    <i class="fas fa-sign-out-alt me-2"></i> Sign Out / Logout
                </a>
            <?php else: ?>
                <a href="login.php" class="drawer-auth-btn login">
                    <i class="fas fa-user-circle me-2"></i> Customer Login / Sign Up
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>




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
        border: 2px solid rgba(2, 132, 199, 0.4) !important;
        border-radius: 10px !important;
        padding: 6px 10px !important;
        background: #f0f9ff !important;
        transition: all 0.2s ease !important;
    }
    .navbar-toggler:focus,
    .navbar-toggler:active {
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.25) !important;
        outline: none !important;
    }
}

/* ========================================================= */
/* DYNAMIC MOBILE DRAWER MENU STYLING                       */
/* ========================================================= */
.aqua-drawer-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    z-index: 99998;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1), visibility 0.3s;
}

.aqua-drawer-backdrop.open {
    opacity: 1;
    visibility: visible;
}

.aqua-mobile-drawer {
    position: fixed;
    top: 0;
    right: 0;
    width: 86%;
    max-width: 360px;
    height: 100%;
    height: 100dvh;
    background: #ffffff;
    z-index: 99999;
    box-shadow: -10px 0 35px rgba(0, 0, 0, 0.25);
    display: flex;
    flex-direction: column;
    transform: translateX(105%);
    transition: transform 0.32s cubic-bezier(0.16, 1, 0.3, 1);
    overflow: hidden;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
}

.aqua-mobile-drawer.open {
    transform: translateX(0);
}

/* Drawer Header */
.aqua-drawer-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
    color: #ffffff;
    flex-shrink: 0;
}

.aqua-drawer-brand {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
    color: #ffffff !important;
}

.drawer-logo-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.22);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: #ffffff;
    box-shadow: inset 0 0 8px rgba(255, 255, 255, 0.2);
}

.drawer-logo-text {
    display: flex;
    flex-direction: column;
}

.drawer-logo-text span {
    font-size: 18px;
    font-weight: 800;
    line-height: 1.1;
    color: #ffffff;
    letter-spacing: -0.5px;
}

.drawer-logo-text small {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 2px;
    color: #bae6fd;
    text-transform: uppercase;
}

.aqua-drawer-close {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    color: #ffffff;
    font-size: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
}

.aqua-drawer-close:hover,
.aqua-drawer-close:active {
    background: rgba(255, 255, 255, 0.35);
    transform: scale(1.08);
}

/* User Card */
.aqua-drawer-user-card {
    padding: 14px 18px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    flex-shrink: 0;
}

.drawer-user-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.drawer-avatar {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: #dbeafe;
    color: #0284c7;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
    box-shadow: 0 2px 6px rgba(2, 132, 199, 0.15);
}

.drawer-avatar.guest {
    background: #e0f2fe;
    color: #0369a1;
}

.drawer-user-text {
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.drawer-greeting {
    font-size: 11px;
    color: #64748b;
    font-weight: 600;
}

.drawer-username {
    font-size: 14px;
    font-weight: 700;
    color: #0f172a;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.drawer-user-actions {
    display: flex;
    gap: 8px;
    margin-top: 10px;
}

.drawer-chip-btn {
    flex: 1;
    padding: 7px 10px;
    border-radius: 8px;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #0284c7 !important;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    text-align: center;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    transition: all 0.2s ease;
}

.drawer-chip-btn:hover {
    background: #f0f9ff;
    border-color: #0284c7;
}

/* Drawer Navigation Items */
.aqua-drawer-menu {
    flex: 1;
    overflow-y: auto;
    padding: 12px 14px;
    -webkit-overflow-scrolling: touch;
}

.drawer-section-title {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #94a3b8;
    padding: 6px 10px 8px;
}

.drawer-nav-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    border-radius: 12px;
    text-decoration: none;
    margin-bottom: 6px;
    transition: all 0.2s ease;
    background: #ffffff;
    border: 1px solid transparent;
}

.drawer-nav-item:hover,
.drawer-nav-item:active {
    background: #f8fafc;
    border-color: #e2e8f0;
}

.drawer-nav-item.active {
    background: #eff6ff;
    border-color: #bfdbfe;
    border-left: 4px solid #0284c7;
}

.drawer-nav-item.active .title {
    color: #0284c7;
}

.drawer-nav-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    flex-shrink: 0;
    transition: transform 0.2s ease;
}

.drawer-nav-item:hover .drawer-nav-icon {
    transform: scale(1.08);
}

.drawer-nav-text {
    flex: 1;
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.drawer-nav-text .title {
    font-size: 13.5px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 6px;
    line-height: 1.2;
}

.drawer-nav-text .desc {
    font-size: 11px;
    color: #64748b;
    margin-top: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.drawer-chevron {
    font-size: 11px;
    color: #cbd5e1;
    transition: transform 0.2s ease, color 0.2s ease;
}

.drawer-nav-item:hover .drawer-chevron {
    transform: translateX(3px);
    color: #0284c7;
}

/* Icon Palette Soft Colors */
.bg-primary-soft   { background: #e0f2fe; color: #0284c7; }
.bg-accent-soft    { background: #ffedd5; color: #ea580c; }
.bg-info-soft      { background: #e0f7fa; color: #00838f; }
.bg-success-soft   { background: #dcfce7; color: #16a34a; }
.bg-purple-soft    { background: #f3e8ff; color: #9333ea; }
.bg-warning-soft   { background: #fef3c7; color: #d97706; }
.bg-blue-soft      { background: #ede9fe; color: #6366f1; }
.bg-teal-soft      { background: #ccfbf1; color: #0f766e; }
.bg-secondary-soft { background: #f1f5f9; color: #475569; }

/* Special Highlighting for Book Service */
.drawer-nav-item.highlight-booking {
    background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
    border: 1px solid #fed7aa;
}

.drawer-nav-item.highlight-booking:hover {
    background: linear-gradient(135deg, #ffedd5 0%, #fed7aa 100%);
}

.badge-instant {
    background: #ea580c;
    color: #ffffff;
    font-size: 9.5px;
    font-weight: 800;
    padding: 2px 6px;
    border-radius: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: inline-block;
}

/* Drawer Footer */
.aqua-drawer-footer {
    padding: 12px 16px 16px;
    background: #ffffff;
    border-top: 1px solid #e2e8f0;
    display: flex;
    flex-direction: column;
    gap: 10px;
    flex-shrink: 0;
}

.drawer-contact-buttons {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}

.drawer-btn-call {
    padding: 9px 12px;
    border-radius: 10px;
    background: #0284c7;
    color: #ffffff !important;
    font-size: 12.5px;
    font-weight: 700;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    box-shadow: 0 3px 8px rgba(2, 132, 199, 0.25);
    transition: all 0.2s ease;
}

.drawer-btn-call:hover {
    background: #0369a1;
    color: #ffffff !important;
}

.drawer-btn-wa {
    padding: 9px 12px;
    border-radius: 10px;
    background: #16a34a;
    color: #ffffff !important;
    font-size: 12.5px;
    font-weight: 700;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    box-shadow: 0 3px 8px rgba(22, 163, 74, 0.25);
    transition: all 0.2s ease;
}

.drawer-btn-wa:hover {
    background: #15803d;
    color: #ffffff !important;
}

.drawer-auth-btn-wrap {
    width: 100%;
}

.drawer-auth-btn {
    width: 100%;
    padding: 10px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    transition: all 0.2s ease;
}

.drawer-auth-btn.login {
    background: #0f172a;
    color: #ffffff !important;
}

.drawer-auth-btn.login:hover {
    background: #1e293b;
}

.drawer-auth-btn.logout {
    background: #fee2e2;
    color: #dc2626 !important;
    border: 1px solid #fecaca;
}

.drawer-auth-btn.logout:hover {
    background: #fecaca;
}

@media (min-width: 992px) {
    .aqua-drawer-backdrop,
    .aqua-mobile-drawer {
        display: none !important;
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

// Dynamic Mobile Drawer Open & Close Functions
function openAquaMobileMenu(e) {
    if (e) {
        try { e.preventDefault(); e.stopPropagation(); } catch(err) {}
    }
    var drawer = document.getElementById('aquaMobileDrawer');
    var backdrop = document.getElementById('aquaDrawerBackdrop');
    if (drawer && backdrop) {
        drawer.classList.add('open');
        backdrop.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    return false;
}

function closeAquaMobileMenu() {
    var drawer = document.getElementById('aquaMobileDrawer');
    var backdrop = document.getElementById('aquaDrawerBackdrop');
    if (drawer && backdrop) {
        drawer.classList.remove('open');
        backdrop.classList.remove('open');
        document.body.style.overflow = '';
    }
}

// Close on ESC key
document.addEventListener("keydown", function(e) {
    if (e.key === "Escape") {
        closeAquaMobileMenu();
    }
});
</script>
