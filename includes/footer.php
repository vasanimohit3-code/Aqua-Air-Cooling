<?php
/* ==========================================================================
   DYNAMIC FOOTER CONFIGURATION & COLOR SETTINGS (USER SIDE)
   ========================================================================== */

// Color Palette & Styling Variables (Change any value below to update footer theme dynamically)
$footer_bg_color       = $footer_bg_color       ?? '#06164f';
$footer_bg_gradient    = $footer_bg_gradient    ?? 'linear-gradient(145deg, #040e36 0%, #06164f 50%, #081d66 100%)';
$footer_heading_color  = $footer_heading_color  ?? '#ffffff';
$footer_text_color     = $footer_text_color     ?? '#b5c4d9';
$footer_accent_color   = $footer_accent_color   ?? '#ff7f0e';
$footer_accent_hover   = $footer_accent_hover   ?? '#e06b00';
$footer_link_color     = $footer_link_color     ?? '#d0dceb';
$footer_link_hover    = $footer_link_hover    ?? '#ff7f0e';
$footer_card_bg        = $footer_card_bg        ?? 'rgba(255, 255, 255, 0.06)';
$footer_card_border    = $footer_card_border    ?? 'rgba(255, 255, 255, 0.12)';
$footer_bottom_bg      = $footer_bottom_bg      ?? 'rgba(0, 0, 0, 0.3)';

// Content & Links Variables
$footer_company_name   = $footer_company_name   ?? 'Aqua Air Cooling';
$footer_company_desc   = $footer_company_desc   ?? 'At Aqua Air Cooling Service Center, we provide expert AC repair, installation, and maintenance services you can trust. With skilled technicians, quick response time, and 24/7 support, we ensure your cooling systems run efficiently at all times.';

// --- DYNAMIC CONTACT INFO FROM DATABASE (Admin Panel → Contact & Settings) ---
// Defaults (used as fallback if DB is not available)
$footer_address        = 'Rajkot, Gujarat, IND';
$footer_phone          = '+91 6354911971';
$footer_email          = 'aquaaircoolling@gmail.com';
$footer_whatsapp_num   = '916354911971'; // digits only for WhatsApp link

// Fetch live values from contact_info table managed via Admin Panel
if (isset($conn) && $conn) {
    $ft_contact_q = mysqli_query($conn, "SELECT address, phone, email, whatsapp_number FROM contact_info WHERE id = 1 LIMIT 1");
    if ($ft_contact_q && mysqli_num_rows($ft_contact_q) > 0) {
        $ft_row = mysqli_fetch_assoc($ft_contact_q);
        if (!empty($ft_row['address']))          $footer_address      = $ft_row['address'];
        if (!empty($ft_row['phone']))            $footer_phone        = $ft_row['phone'];
        if (!empty($ft_row['email']))            $footer_email        = $ft_row['email'];
        if (!empty($ft_row['whatsapp_number']))  $footer_whatsapp_num = preg_replace('/[^0-9]/', '', $ft_row['whatsapp_number']);
    }
}
// Build WhatsApp direct link from phone number
$footer_whatsapp_url_direct = 'https://wa.me/' . $footer_whatsapp_num;
// --- END DYNAMIC CONTACT INFO ---

$footer_whatsapp_group = $footer_whatsapp_group ?? 'https://chat.whatsapp.com/C8ye3hVVccn8tyvelTOtBx?s=cl&p=a&mlu=4';

$footer_facebook_url   = $footer_facebook_url   ?? 'https://www.facebook.com/share/18z7GyHusb/';
$footer_whatsapp_url   = $footer_whatsapp_url   ?? $footer_whatsapp_url_direct;
$footer_youtube_url    = $footer_youtube_url    ?? 'https://youtube.com/@mohitvasani-h1o?si=g-HMKwqvI_nd19kb';
$footer_instagram_url  = $footer_instagram_url  ?? 'https://www.instagram.com/mohit__8986?igsh=eXI3eGN5Nm8wcnFr';

$footer_copyright_name = $footer_copyright_name ?? 'Aqua Air Cooling';
$footer_developer_name = $footer_developer_name ?? 'Aqua Group';
?>

<!-- Dynamic Footer CSS Styles -->
<style>
  :root {
    --ft-bg-color: <?php echo $footer_bg_color; ?>;
    --ft-bg-gradient: <?php echo $footer_bg_gradient; ?>;
    --ft-heading-color: <?php echo $footer_heading_color; ?>;
    --ft-text-color: <?php echo $footer_text_color; ?>;
    --ft-accent-color: <?php echo $footer_accent_color; ?>;
    --ft-accent-hover: <?php echo $footer_accent_hover; ?>;
    --ft-link-color: <?php echo $footer_link_color; ?>;
    --ft-link-hover: <?php echo $footer_link_hover; ?>;
    --ft-card-bg: <?php echo $footer_card_bg; ?>;
    --ft-card-border: <?php echo $footer_card_border; ?>;
    --ft-bottom-bg: <?php echo $footer_bottom_bg; ?>;
  }

  .dynamic-footer {
    background: var(--ft-bg-gradient);
    color: var(--ft-text-color);
    position: relative;
    overflow: hidden;
    font-family: 'Roboto', sans-serif;
  }

  .dynamic-footer::before {
    content: '';
    position: absolute;
    top: -100px;
    right: -100px;
    width: 300px;
    height: 300px;
    background: rgba(255, 127, 14, 0.05);
    border-radius: 50%;
    pointer-events: none;
  }

  .dynamic-footer .footer-title {
    color: var(--ft-heading-color);
    font-family: 'Roboto Slab', serif;
    font-weight: 700;
    margin-bottom: 1.25rem;
    position: relative;
    display: inline-block;
  }

  .dynamic-footer .footer-title::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: -6px;
    width: 40px;
    height: 3px;
    background: var(--ft-accent-color);
    border-radius: 2px;
  }

  .dynamic-footer .footer-brand-title {
    color: var(--ft-heading-color);
    font-family: 'Roboto Slab', serif;
    font-weight: 800;
    font-size: 1.75rem;
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
  }

  .dynamic-footer .footer-brand-title:hover {
    color: var(--ft-heading-color);
  }

  .dynamic-footer .footer-desc {
    color: var(--ft-text-color);
    line-height: 1.7;
    font-size: 0.95rem;
  }

  .dynamic-footer .contact-item {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 14px;
    color: var(--ft-text-color);
    transition: transform 0.25s ease, color 0.25s ease;
  }

  .dynamic-footer .contact-item:hover {
    transform: translateX(4px);
    color: var(--ft-heading-color);
  }

  .dynamic-footer .contact-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: var(--ft-card-bg);
    border: 1px solid var(--ft-card-border);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--ft-accent-color);
    font-size: 14px;
    flex-shrink: 0;
  }

  .dynamic-footer .footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
  }

  .dynamic-footer .footer-links li {
    margin-bottom: 10px;
  }

  .dynamic-footer .footer-link-item {
    color: var(--ft-link-color);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 0.95rem;
    transition: all 0.25s ease;
  }

  .dynamic-footer .footer-link-item i {
    font-size: 12px;
    color: var(--ft-accent-color);
    transition: transform 0.25s ease;
  }

  .dynamic-footer .footer-link-item:hover {
    color: var(--ft-link-hover);
    transform: translateX(5px);
  }

  .dynamic-footer .footer-link-item:hover i {
    transform: translateX(3px);
  }

  .dynamic-footer .newsletter-box {
    background: var(--ft-card-bg);
    border: 1px solid var(--ft-card-border);
    border-radius: 16px;
    padding: 20px;
    backdrop-filter: blur(10px);
  }

  .dynamic-footer .btn-join-whatsapp {
    background: #25D366;
    color: #ffffff;
    border: none;
    border-radius: 10px;
    padding: 10px 20px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    text-decoration: none;
    box-shadow: 0 4px 12px rgba(37, 211, 102, 0.25);
  }

  .dynamic-footer .btn-join-whatsapp:hover {
    background: #20ba5a;
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(37, 211, 102, 0.4);
  }

  .dynamic-footer .social-btn {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: var(--ft-card-bg);
    border: 1px solid var(--ft-card-border);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    margin-right: 8px;
    transition: all 0.3s ease;
    text-decoration: none;
  }

  .dynamic-footer .social-btn.facebook { color: #0d6efd; }
  .dynamic-footer .social-btn.whatsapp { color: #25d366; }
  .dynamic-footer .social-btn.youtube  { color: #ff0000; }
  .dynamic-footer .social-btn.instagram{ color: #e1306c; }

  .dynamic-footer .social-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 18px rgba(0, 0, 0, 0.25);
  }

  .dynamic-footer .social-btn.facebook:hover { background: #0d6efd; color: #ffffff; border-color: #0d6efd; }
  .dynamic-footer .social-btn.whatsapp:hover { background: #25d366; color: #ffffff; border-color: #25d366; }
  .dynamic-footer .social-btn.youtube:hover  { background: #ff0000; color: #ffffff; border-color: #ff0000; }
  .dynamic-footer .social-btn.instagram:hover{ background: linear-gradient(45deg, #f09433, #dc2743, #bc1888); color: #ffffff; border-color: #e1306c; }

  .dynamic-footer .copyright-bar {
    background: var(--ft-bottom-bg);
    border-top: 1px solid var(--ft-card-border);
    padding: 20px 0;
    margin-top: 50px;
    font-size: 0.9rem;
  }

  .dynamic-footer .copyright-bar a {
    color: var(--ft-accent-color);
    text-decoration: none;
    font-weight: 600;
  }

  @media (max-width: 768px) {
    .dynamic-footer {
      text-align: center;
    }
    .dynamic-footer .footer-title::after {
      left: 50%;
      transform: translateX(-50%);
    }
    .dynamic-footer .footer-brand-title {
      justify-content: center;
      font-size: 1.5rem;
    }
    .dynamic-footer .contact-item {
      justify-content: center;
    }
    .dynamic-footer .footer-link-item {
      justify-content: center;
    }
    .dynamic-footer .social-btn {
      margin: 0 4px;
    }
    .dynamic-footer .d-flex.align-items-center.mt-3 {
      justify-content: center;
    }
    .dynamic-footer .copyright-bar {
      margin-top: 30px;
      padding: 15px 0;
      font-size: 0.8rem;
    }
  }
</style>

<!-- Dynamic User Side Footer -->
<footer class="dynamic-footer mt-5 pt-5">
  <div class="container py-4">
    <div class="row g-4">
      
      <!-- Company Branding & Description -->
      <div class="col-lg-4 col-md-6 mb-4">
        <a href="index.php" class="footer-brand-title mb-3">
          <i class="fas fa-snowflake text-primary me-2"></i>
          <span><?php echo htmlspecialchars($footer_company_name); ?></span>
        </a>
        <p class="footer-desc mb-4">
          <?php echo htmlspecialchars($footer_company_desc); ?>
        </p>
        
        <!-- Social Buttons -->
        <div class="d-flex align-items-center mt-3">
          <a class="social-btn facebook" href="<?php echo htmlspecialchars($footer_facebook_url); ?>" target="_blank" title="Facebook">
            <i class="fab fa-facebook-f"></i>
          </a>
          <a class="social-btn whatsapp" href="<?php echo htmlspecialchars($footer_whatsapp_url); ?>" target="_blank" title="WhatsApp">
            <i class="fab fa-whatsapp"></i>
          </a>
          <a class="social-btn youtube" href="<?php echo htmlspecialchars($footer_youtube_url); ?>" target="_blank" title="YouTube">
            <i class="fab fa-youtube"></i>
          </a>
          <a class="social-btn instagram" href="<?php echo htmlspecialchars($footer_instagram_url); ?>" target="_blank" title="Instagram">
            <i class="fab fa-instagram"></i>
          </a>
        </div>
      </div>

      <!-- Get In Touch -->
      <div class="col-lg-3 col-md-6 mb-4">
        <h5 class="footer-title">Get In Touch</h5>
        <div class="contact-item">
          <div class="contact-icon"><i class="fas fa-map-marker-alt"></i></div>
          <span><?php echo htmlspecialchars($footer_address); ?></span>
        </div>
        <div class="contact-item">
          <div class="contact-icon"><i class="fas fa-phone-alt"></i></div>
          <span><?php echo htmlspecialchars($footer_phone); ?></span>
        </div>
        <div class="contact-item">
          <div class="contact-icon"><i class="fas fa-envelope"></i></div>
          <span><?php echo htmlspecialchars($footer_email); ?></span>
        </div>
      </div>

      <!-- Quick Links -->
      <div class="col-lg-2 col-md-6 mb-4">
        <h5 class="footer-title">Quick Links</h5>
        <ul class="footer-links">
          <li><a class="footer-link-item" href="index.php"><i class="fas fa-chevron-right"></i> Home</a></li>
          <li><a class="footer-link-item" href="about.php"><i class="fas fa-chevron-right"></i> About Us</a></li>
          <li><a class="footer-link-item" href="services.php"><i class="fas fa-chevron-right"></i> Services</a></li>
          <li><a class="footer-link-item" href="contact.php"><i class="fas fa-chevron-right"></i> Contact Us</a></li>
          <li><a class="footer-link-item" href="Terms&Condition.php"><i class="fas fa-chevron-right"></i> Terms & Conditions</a></li>
        </ul>
      </div>

      <!-- Newsletter / WhatsApp Community Box -->
      <div class="col-lg-3 col-md-6 mb-4">
        <h5 class="footer-title">Join Our Community</h5>
        <div class="newsletter-box">
          <p class="small text-light mb-3">
            Join our official WhatsApp group to get instant updates, offers & service alerts.
          </p>
          <a href="<?php echo htmlspecialchars($footer_whatsapp_group); ?>" target="_blank" class="btn-join-whatsapp w-100 justify-content-center">
            <i class="fab fa-whatsapp"></i> Join WhatsApp Group
          </a>
        </div>
      </div>

    </div>
  </div>

  <!-- Copyright Bar -->
  <div class="copyright-bar">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-md-6 text-center text-md-start mb-2 mb-md-0">
          &copy; <?php echo date('Y'); ?> <a><?php echo htmlspecialchars($footer_copyright_name); ?></a>. All Rights Reserved.
        </div>
        <div class="col-md-6 text-center text-md-end">
          Developed By <a><?php echo htmlspecialchars($footer_developer_name); ?></a>
        </div>
      </div>
    </div>
  </div>
</footer>
<!-- SweetAlert2 & Canvas Confetti Libraries -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>

<!-- Dynamic User-Side Popup Styling -->
<style>
/* Modern Popup Card */
.swal2-popup {
    border-radius: 20px !important;
    padding: 24px 20px !important;
    background: #ffffff !important;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2) !important;
    font-family: 'Roboto', 'Plus Jakarta Sans', sans-serif !important;
}

.swal2-title {
    font-size: 20px !important;
    font-weight: 800 !important;
    color: #0f172a !important;
}
    padding-top: 10px !important;
}

.swal2-html-container {
    font-size: 14.5px !important;
    color: #475569 !important;
    line-height: 1.6 !important;
    margin-top: 12px !important;
}

/* Animated Icons */
.swal2-icon {
    border-width: 3px !important;
    transform: scale(1.1);
    margin-top: 10px !important;
}

.swal2-icon.swal2-success {
    border-color: #10b981 !important;
    color: #10b981 !important;
    box-shadow: 0 0 20px rgba(16, 185, 129, 0.25);
}

.swal2-icon.swal2-warning {
    border-color: #f59e0b !important;
    color: #f59e0b !important;
    box-shadow: 0 0 20px rgba(245, 158, 11, 0.25);
}

.swal2-icon.swal2-error {
    border-color: #ef4444 !important;
    color: #ef4444 !important;
    box-shadow: 0 0 20px rgba(239, 68, 68, 0.25);
}

.swal2-icon.swal2-info {
    border-color: #0284c7 !important;
    color: #0284c7 !important;
    box-shadow: 0 0 20px rgba(2, 132, 199, 0.25);
}

/* Dynamic Action Buttons */
.swal2-actions {
    margin-top: 24px !important;
    gap: 10px !important;
}

.swal2-styled.swal2-confirm {
    border-radius: 50px !important;
    padding: 12px 32px !important;
    font-weight: 700 !important;
    font-size: 14px !important;
    letter-spacing: 0.3px !important;
    box-shadow: 0 8px 20px rgba(13, 110, 253, 0.3) !important;
    transition: all 0.25s ease !important;
}

.swal2-styled.swal2-confirm:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 12px 25px rgba(13, 110, 253, 0.45) !important;
}

.swal2-styled.swal2-cancel {
    border-radius: 50px !important;
    padding: 12px 28px !important;
    font-weight: 600 !important;
    font-size: 14px !important;
    transition: all 0.25s ease !important;
}
</style>
<!-- Dynamic Footer End -->