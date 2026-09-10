<?php
require_once 'includes/config.php';
require_once 'includes/mail_functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Login Check
if (!isset($_SESSION['user_id'])) {
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script><style>body{font-family:sans-serif;background:#f5f7fb;}</style></head><body>';
    echo "<script>
    Swal.fire({
        icon: 'info',
        title: 'Login Required',
        text: 'Please login or create an account first to book a service.',
        confirmButtonColor: '#0d6efd',
        confirmButtonText: 'Go to Login'
    }).then(() => {
        window.location.href='login.php';
    });
    </script></body></html>";
    exit();
}

$user_id = $_SESSION['user_id'];
$user_email = '';
$user_full_name = '';
$user_first_name = '';
$user_last_name = '';
$user_mobile = '';
$user_address = '';

$user_query = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
if($user_row = mysqli_fetch_assoc($user_query)) {
    $user_email = isset($user_row['email']) ? $user_row['email'] : '';
    $user_full_name = isset($user_row['name']) ? $user_row['name'] : (isset($user_row['first_name']) ? $user_row['first_name'] : '');
}

// Fetch latest details from past bookings if available
$past_b_q = mysqli_query($conn, "SELECT first_name, last_name, mobile, address FROM bookings WHERE user_id='$user_id' ORDER BY id DESC LIMIT 1");
if ($past_b_q && $past_b_row = mysqli_fetch_assoc($past_b_q)) {
    $user_first_name = $past_b_row['first_name'] ?? '';
    $user_last_name  = $past_b_row['last_name'] ?? '';
    $user_mobile     = $past_b_row['mobile'] ?? '';
    $user_address    = $past_b_row['address'] ?? '';
}

if (empty($user_first_name) && !empty($user_full_name)) {
    $name_parts = explode(' ', trim($user_full_name), 2);
    $user_first_name = $name_parts[0];
    $user_last_name  = $name_parts[1] ?? '';
}

// Dynamic Services from Database
$booking_services = [];
$booking_service_catalog = [];
$booking_service_details = [];

$srv_q = mysqli_query($conn, "SELECT name, price, estimated_time, warranty FROM services WHERE is_active=1 ORDER BY display_order ASC, id ASC");
if ($srv_q && mysqli_num_rows($srv_q) > 0) {
    while ($srow = mysqli_fetch_assoc($srv_q)) {
        $sname = $srow['name'];
        $sprice = (float)$srow['price'];
        $booking_services[] = $sname;
        $booking_service_catalog[$sname] = $sprice;
        $booking_service_details[$sname] = [
            'price' => $sprice,
            'estTime' => !empty($srow['estimated_time']) ? $srow['estimated_time'] : '45-60 Mins Arrival',
            'warranty' => !empty($srow['warranty']) ? $srow['warranty'] : '30 Days Service Guarantee'
        ];
    }
} else {
    // Fallback defaults
    $booking_service_catalog = [
        "AC Installation" => 1200,
        "Copper Pipe" => 425,
        "AC Insulation" => 60,
        "Packing Tape" => 25,
        "Havells 4 Core Cable (2.5 sq mm)" => 60,
        "AC Repair" => 900,
        "AC Gas Refilling" => 3250,
        "AC Maintenance" => 600,
        "AC Cleaning" => 800,
        "AC Stand" => 1000,
        "AC Uninstallation" => 1200,
        "Annual Maintenance Contract (AMC)" => 5000
    ];
    $booking_services = array_keys($booking_service_catalog);
    foreach ($booking_service_catalog as $k => $v) {
        $booking_service_details[$k] = [
            'price' => $v,
            'estTime' => '45-60 Mins Arrival',
            'warranty' => '30 Days Guarantee'
        ];
    }
}

// Build Rich AC Service Cards for Visual Selection
$booking_service_cards = [];
foreach ($booking_services as $srvName) {
    $sPrice = (float)($booking_service_catalog[$srvName] ?? 0);
    $sEst = $booking_service_details[$srvName]['estTime'] ?? '45-60 Mins Arrival';
    $sWarr = $booking_service_details[$srvName]['warranty'] ?? '30 Days Guarantee';

    $sIcon = 'fas fa-tools';
    $sSubtitle = 'Professional AC Service';
    $upper = strtoupper($srvName);
    if (strpos($upper, 'CLEAN') !== false || strpos($upper, 'WASH') !== false) {
        $sIcon = 'fas fa-spray-can';
        $sSubtitle = 'Deep Jet Foam Coil Cleaning';
    } elseif (strpos($upper, 'GAS') !== false || strpos($upper, 'REFILL') !== false) {
        $sIcon = 'fas fa-gas-pump';
        $sSubtitle = 'Pressure Leak Check & 100% Pure Gas';
    } elseif (strpos($upper, 'UNINSTALL') !== false) {
        $sIcon = 'fas fa-box-archive';
        $sSubtitle = 'Safe Gas Locking & Removal';
    } elseif (strpos($upper, 'INSTALL') !== false || strpos($upper, 'STAND') !== false) {
        $sIcon = 'fas fa-screwdriver-wrench';
        $sSubtitle = 'Standard Indoor & Outdoor Mounting';
    } elseif (strpos($upper, 'MAINTEN') !== false || strpos($upper, 'AMC') !== false) {
        $sIcon = 'fas fa-calendar-check';
        $sSubtitle = 'Annual Maintenance & Health Check';
    } elseif (strpos($upper, 'REPAIR') !== false) {
        $sIcon = 'fas fa-wrench';
        $sSubtitle = 'Expert Multi-Point AC Troubleshooting';
    } elseif (strpos($upper, 'PIPE') !== false || strpos($upper, 'INSULAT') !== false || strpos($upper, 'CABLE') !== false || strpos($upper, 'TAPE') !== false) {
        $sIcon = 'fas fa-toolbox';
        $sSubtitle = 'Heavy Duty AC Material & Piping';
    }

    $booking_service_cards[] = [
        'name' => $srvName,
        'price' => $sPrice,
        'estTime' => $sEst,
        'warranty' => $sWarr,
        'icon' => $sIcon,
        'subtitle' => $sSubtitle
    ];
}

// Fetch Booking Settings from DB
$b_settings = [
    'hero_badge' => '⭐ Trusted AC Service Since 2024',
    'hero_title' => 'Book Your AC Service In Just 2 Minutes',
    'hero_text' => "Fast • Reliable • Affordable\n\nBook AC Installation, Repair, Cleaning, Gas Refilling, AMC and Maintenance Service Online.",
    'promo_title' => 'Professional AC Service At Your Doorstep',
    'rating_text' => '4.9/5 (1,450+ Happy Clients)',
    'ticker_text' => '14 bookings completed today in your area!',
    'arrival_guarantee_gu' => 'જો તમારી બુકિંગ Approved થશે ત્યારબાદ 60 મિનિટમાં ટેકનિશિયન તમારા ઘરે આવશે.',
    'arrival_guarantee_en' => '(Once your booking is approved by Admin, our expert technician will arrive at your home within 60 minutes.)',
    'max_coupon_discount' => 10
];
$b_set_q = mysqli_query($conn, "SELECT * FROM booking_settings WHERE id = 1");
if ($b_set_q && mysqli_num_rows($b_set_q) > 0) {
    $bs_row = mysqli_fetch_assoc($b_set_q);
    if ($bs_row) {
        foreach ($bs_row as $k => $v) {
            if ($v !== null && $v !== '') {
                $b_settings[$k] = $v;
            }
        }
    }
}

// Fetch Active AC Brands from DB
$booking_brands = [];
$brands_q = mysqli_query($conn, "SELECT name FROM ac_brands WHERE is_active = 1 ORDER BY display_order ASC, id ASC");
if ($brands_q && mysqli_num_rows($brands_q) > 0) {
    while ($br_row = mysqli_fetch_assoc($brands_q)) {
        $booking_brands[] = $br_row['name'];
    }
} else {
    $booking_brands = ['Voltas', 'Daikin', 'LG', 'Samsung', 'Blue Star', 'Hitachi', 'Carrier', 'Panasonic', 'Lloyd', 'Godrej', 'Haier', 'Mitsubishi Electric', 'O General', 'IFB', 'Whirlpool', 'Other'];
}

// Fetch Active Original Spare Parts from DB with Rich Metadata
$booking_parts = [];
$booking_parts_catalog = [];

$parts_q = mysqli_query($conn, "SELECT name, price FROM ac_original_parts WHERE is_active = 1 ORDER BY display_order ASC, id ASC");
$raw_parts_source = [];
if ($parts_q && mysqli_num_rows($parts_q) > 0) {
    while ($pr_row = mysqli_fetch_assoc($parts_q)) {
        $raw_parts_source[] = ['name' => $pr_row['name'], 'price' => (float)$pr_row['price']];
    }
} else {
    $default_fallback_parts = [
        ['COMPRESSOR PCB - 8250', 8250],
        ['OUTDOOR FAN MOTOR - 1900', 1900],
        ['INDOOR FAN MOTOR - 2680', 2680],
        ['COMPRESSOR - 10000', 10000],
        ['FAN BLED - 850', 850],
        ['CSR POWER WIRE - 450', 450],
        ['INDOOR PCB - 4500', 4500],
        ['V - SPRING - 900', 900],
        ['H - SPRING - 900', 900],
        ['BASE - 4600', 4600],
        ['DRAIN PIPE - 220', 220],
        ['BLOWER - 850', 850],
        ['COOLING COIL - 3800', 3800],
        ['ROOM SENSOR - 950', 950]
    ];
    foreach ($default_fallback_parts as $dfp) {
        $raw_parts_source[] = ['name' => $dfp[0], 'price' => (float)$dfp[1]];
    }
}

foreach ($raw_parts_source as $rp) {
    $raw_name = $rp['name'];
    if ($raw_name === 'None (Service Only)' || $raw_name === 'None / General Service Only' || $raw_name === 'None') continue;
    
    $booking_parts[] = $raw_name;
    $price = (float)$rp['price'];
    if ($price <= 0 && preg_match('/[-:]\s*([0-9]+)/', $raw_name, $m)) {
        $price = (float)$m[1];
    }
    
    // Intelligent Metadata Categorization
    $upper = strtoupper($raw_name);
    $category = 'fittings';
    $cat_label = 'Body & Piping';
    $icon = 'fas fa-cog';
    $display_title = $raw_name;
    $subtitle = '100% Genuine AC Component';
    $badge = '6M Warranty';

    if (strpos($upper, 'COMPRESSOR PCB') !== false) {
        $display_title = 'Compressor Inverter PCB Board';
        $category = 'electronics';
        $cat_label = 'PCB & Electronics';
        $icon = 'fas fa-microchip';
        $subtitle = 'OEM Inverter Controller & Heat Sink';
        $badge = 'High Demand';
    } elseif (strpos($upper, 'INDOOR PCB') !== false) {
        $display_title = 'Indoor Main Motherboard PCB';
        $category = 'electronics';
        $cat_label = 'PCB & Electronics';
        $icon = 'fas fa-microchip';
        $subtitle = 'Display, Logic & Sensor Control Board';
    } elseif (strpos($upper, 'ROOM SENSOR') !== false) {
        $display_title = 'Precision Room & Coil Sensor';
        $category = 'electronics';
        $cat_label = 'PCB & Electronics';
        $icon = 'fas fa-temperature-low';
        $subtitle = 'Dual Thermistor Temperature Probe';
    } elseif (strpos($upper, 'CSR POWER WIRE') !== false || strpos($upper, 'POWER WIRE') !== false) {
        $display_title = 'CSR Power Terminal Wiring Harness';
        $category = 'electronics';
        $cat_label = 'PCB & Electronics';
        $icon = 'fas fa-bolt';
        $subtitle = 'Heavy Duty Silicon Insulated Wire';
    } elseif (strpos($upper, 'OUTDOOR FAN MOTOR') !== false) {
        $display_title = 'Outdoor Condenser Fan Motor';
        $category = 'motors';
        $cat_label = 'Motors & Airflow';
        $icon = 'fas fa-fan';
        $subtitle = 'Weatherproof High-Speed Copper Motor';
    } elseif (strpos($upper, 'INDOOR FAN MOTOR') !== false) {
        $display_title = 'Indoor Blower Fan Motor';
        $category = 'motors';
        $cat_label = 'Motors & Airflow';
        $icon = 'fas fa-fan';
        $subtitle = 'Ultra-Silent Multi-Speed Copper Motor';
    } elseif (strpos($upper, 'FAN BLED') !== false || strpos($upper, 'FAN BLADE') !== false) {
        $display_title = 'Outdoor Propeller Fan Blade';
        $category = 'motors';
        $cat_label = 'Motors & Airflow';
        $icon = 'fas fa-wind';
        $subtitle = 'Dynamic Balanced Aerodynamic Blade';
    } elseif (strpos($upper, 'BLOWER') !== false) {
        $display_title = 'Cross-Flow Blower Roller Wheel';
        $category = 'motors';
        $cat_label = 'Motors & Airflow';
        $icon = 'fas fa-arrows-spin';
        $subtitle = 'Balanced Noise-Free Cylindrical Wheel';
    } elseif (strpos($upper, 'COMPRESSOR') !== false) {
        $display_title = 'High Efficiency Rotary Compressor';
        $category = 'cooling';
        $cat_label = 'Cooling Core';
        $icon = 'fas fa-cogs';
        $subtitle = 'Heavy Duty Gas-Charged Cooling Heart';
        $badge = 'Core Component';
    } elseif (strpos($upper, 'COOLING COIL') !== false) {
        $display_title = 'Pure Copper Evaporator Cooling Coil';
        $category = 'cooling';
        $cat_label = 'Cooling Core';
        $icon = 'fas fa-snowflake';
        $subtitle = 'Hydrophilic Anti-Rust Blue Fins';
    } elseif (strpos($upper, 'DRAIN PIPE') !== false) {
        $display_title = 'Flexible Water Drain Pipe';
        $category = 'fittings';
        $cat_label = 'Body & Piping';
        $icon = 'fas fa-water';
        $subtitle = 'Reinforced Leak-Proof UV Pipe (3M)';
    } elseif (strpos($upper, 'BASE') !== false) {
        $display_title = 'Outdoor Chassis Bottom Base Tray';
        $category = 'fittings';
        $cat_label = 'Body & Piping';
        $icon = 'fas fa-layer-group';
        $subtitle = 'Galvanized Anti-Rust Heavy Gauge Sheet';
    } elseif (strpos($upper, 'V - SPRING') !== false || strpos($upper, 'V-SPRING') !== false) {
        $display_title = 'Vertical Anti-Vibration Damper Spring';
        $category = 'fittings';
        $cat_label = 'Body & Piping';
        $icon = 'fas fa-shield-alt';
        $subtitle = 'Compressor Sound & Vibration Absorber';
    } elseif (strpos($upper, 'H - SPRING') !== false || strpos($upper, 'H-SPRING') !== false) {
        $display_title = 'Horizontal Stabilizer Support Spring';
        $category = 'fittings';
        $cat_label = 'Body & Piping';
        $icon = 'fas fa-shield-alt';
        $subtitle = 'Heavy Duty Mounting Suspension Spring';
    }

    $booking_parts_catalog[] = [
        'raw_name'      => $raw_name,
        'title'         => $display_title,
        'subtitle'      => $subtitle,
        'price'         => $price,
        'category'      => $category,
        'cat_label'     => $cat_label,
        'icon'          => $icon,
        'badge'         => $badge
    ];
}

// Build Authoritative Fast Lookup Maps & Dynamic Categories
$parts_price_map = [];
$parts_categories_map = [
    'all' => ['label' => 'All Parts', 'count' => count($booking_parts_catalog)]
];
foreach ($booking_parts_catalog as $pt_item) {
    $parts_price_map[$pt_item['raw_name']] = (float)$pt_item['price'];
    $parts_price_map[$pt_item['title']] = (float)$pt_item['price'];
    $cat_k = $pt_item['category'];
    if (!isset($parts_categories_map[$cat_k])) {
        $parts_categories_map[$cat_k] = ['label' => $pt_item['cat_label'], 'count' => 0];
    }
    $parts_categories_map[$cat_k]['count']++;
}

// Fetch Contact Info for Help Box
$contact_help = [
    'phone' => '+91 6354911971',
    'whatsapp' => '916354911971'
];
$c_info_q = mysqli_query($conn, "SELECT phone, whatsapp_number FROM contact_info WHERE id = 1");
if ($c_info_q && $ci_row = mysqli_fetch_assoc($c_info_q)) {
    if (!empty($ci_row['phone'])) $contact_help['phone'] = $ci_row['phone'];
    if (!empty($ci_row['whatsapp_number'])) $contact_help['whatsapp'] = $ci_row['whatsapp_number'];
}

// Request Mystery Coupon Handler (AJAX Version - NO PAGE REFRESH!)
if(isset($_POST['ajax_request_coupon'])) {
    header('Content-Type: application/json');
    date_default_timezone_set('Asia/Kolkata');
    
    $max_disc = max(1, intval($b_settings['max_coupon_discount'] ?? 10));
    $created_at = time();
    $rand_percent = rand(1, $max_disc);
    $rand_code = "AQUA-" . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 5));
    $expires_at = $created_at + 3600; // 60 Minutes Validity
    $click_time_str = date('h:i:s A', $created_at);
    $exp_time_str = date('h:i:s A', $expires_at);

    if(!isset($_SESSION['user_coupons'])) {
        $_SESSION['user_coupons'] = array();
    }
    $_SESSION['user_coupons'][$rand_code] = array(
        'percent' => $rand_percent,
        'expires_at' => $expires_at,
        'created_at' => $created_at
    );

    $mail_sent = false;
    if(!empty($user_email)) {
        $mail_sent = sendCouponMail($user_email, $user_full_name, $rand_code, $rand_percent, $created_at);
    }

    echo json_encode([
        'success' => true,
        'code' => $rand_code,
        'percent' => $rand_percent,
        'expires_at' => $expires_at,
        'click_time_str' => $click_time_str,
        'exp_time_str' => $exp_time_str,
        'email' => $user_email,
        'mail_sent' => $mail_sent,
        'message' => $mail_sent 
            ? "A mystery <strong>$rand_percent% OFF</strong> discount code has been sent to your email (<strong>$user_email</strong>)." 
            : "Your mystery <strong>$rand_percent% OFF</strong> discount coupon code is ready!"
    ]);
    exit();
}

// Booking Save
if (isset($_POST['booking'])) {

    $user_id =
        (int)$_SESSION['user_id'];


    /* Basic booking data */

    $first_name =
        trim($_POST['first_name'] ?? '');

    $last_name =
        trim($_POST['last_name'] ?? '');

    $mobile =
        trim($_POST['mobile'] ?? '');

    $email =
        trim($_POST['email'] ?? '');

    $address =
        trim($_POST['address'] ?? '');

    $service_type =
        trim($_POST['service_type'] ?? '');

    $company_type =
        trim($_POST['company_type'] ?? 'Other');

    $original_part =
        trim($_POST['original_part'] ?? 'None (Service Only)');

    /* =========================================
       CHECK AT LEAST ONE ITEM (SERVICE OR PART)
    ========================================= */
    $has_service = (!empty($service_type) && $service_type !== 'None (Spare Part Only)' && $service_type !== 'None');
    $has_part = (!empty($original_part) && $original_part !== 'None (Service Only)' && $original_part !== 'None / General Service Only' && $original_part !== 'None');

    if (!$has_service && !$has_part) {
        $booking_error_msg = "Please select at least an AC Service or an Original Spare Part.";
    }

    if ($has_part && !$has_service) {
        $service_type = "Spare Part Only";
    } elseif (!$has_part && $has_service) {
        $original_part = "None (Service Only)";
    }

    /* =========================================
       AUTHORITATIVE SERVICE & PART PRICE LOOKUP
    ========================================= */
    $service_catalog = $booking_service_catalog;

    // Service Price
    $service_price = 0;
    if (isset($service_catalog[$service_type])) {
        $service_price = (float)$service_catalog[$service_type];
    }

    // Original Part Price extraction from selected part name (Authoritative DB lookup)
    $part_price = 0;
    if (!empty($original_part) && $original_part !== 'None (Service Only)' && $original_part !== 'None / General Service Only' && $original_part !== 'None') {
        if (isset($parts_price_map[$original_part])) {
            $part_price = (float)$parts_price_map[$original_part];
        } elseif (preg_match('/[-:]\s*([0-9]+)\s*$/', $original_part, $pmatch)) {
            $part_price = (float)$pmatch[1];
        } elseif (preg_match('/([0-9]+)/', $original_part, $pmatch)) {
            $part_price = (float)$pmatch[0];
        }
    }

    // Combined Gross Price (Service + Part)
    $price = $service_price + $part_price;
    if ($price <= 0) {
        $price = (float)($_POST['price'] ?? 0);
    }

    /* =========================================
       COUPON DATA & VERIFICATION
    ========================================= */
    $coupon_code = trim($_POST['coupon_code'] ?? '');
    $discount_percent = (float)($_POST['discount_percent'] ?? 0);

    if ($discount_percent < 0) {
        $discount_percent = 0;
    }
    if ($discount_percent > 10) {
        $discount_percent = 10;
    }

    if (!empty($coupon_code) && $discount_percent > 0) {
        $discount_amount = round($price * ($discount_percent / 100), 2);
        $final_price = round($price - $discount_amount, 2);
    } else {
        $coupon_code = '';
        $discount_percent = 0;
        $discount_amount = 0;
        $final_price = $price;
    }


    /* =========================================
       INSERT BOOKING
    ========================================= */

    $sql = "

        INSERT INTO bookings
        (
            user_id,
            first_name,
            last_name,
            mobile,
            email,
            address,
            service_type,
            price,
            coupon_code,
            discount_percent,
            discount_amount,
            final_price,
            company_type,
            original_part
        )

        VALUES
        (
            '$user_id',
            '" . mysqli_real_escape_string($conn, $first_name) . "',
            '" . mysqli_real_escape_string($conn, $last_name) . "',
            '" . mysqli_real_escape_string($conn, $mobile) . "',
            '" . mysqli_real_escape_string($conn, $email) . "',
            '" . mysqli_real_escape_string($conn, $address) . "',
            '" . mysqli_real_escape_string($conn, $service_type) . "',
            '$price',
            '" . mysqli_real_escape_string($conn, $coupon_code) . "',
            '$discount_percent',
            '$discount_amount',
            '$final_price',
            '" . mysqli_real_escape_string($conn, $company_type) . "',
            '" . mysqli_real_escape_string($conn, $original_part) . "'
        )

    ";


    /* =========================================
       SAVE
    ========================================= */

    if (mysqli_query($conn, $sql)) {
        $new_booking_id = mysqli_insert_id($conn);
        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Booking Confirmed</title><script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script><script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script><style>body{font-family:sans-serif;background:#0f172a;}</style></head><body>';
        echo "<script>
            document.addEventListener('DOMContentLoaded', () => {
                if (typeof confetti === 'function') {
                    confetti({ particleCount: 140, spread: 80, origin: { y: 0.6 } });
                    setTimeout(() => { confetti({ particleCount: 70, angle: 60, spread: 55, origin: { x: 0 } }); }, 250);
                    setTimeout(() => { confetti({ particleCount: 70, angle: 120, spread: 55, origin: { x: 1 } }); }, 400);
                }
                Swal.fire({
                    icon: 'success',
                    title: '🎉 Booking Placed Successfully!',
                    html: '<div style=\"text-align:center; padding: 4px;\">' +
                          '<p style=\"font-size: 15px; color: #475569; margin-bottom: 15px;\">Your AC service booking <strong>#$new_booking_id</strong> has been registered successfully!</p>' +
                          '<div style=\"background: linear-gradient(135deg, #e0f2fe 0%, #ecfdf5 100%); border: 1.5px solid #7dd3fc; border-radius: 16px; padding: 16px; text-align: left; box-shadow: 0 4px 15px rgba(2,132,199,0.12);\">' +
                          '<div style=\"color: #0369a1; font-weight: 800; font-size: 14px; margin-bottom: 6px; display: flex; align-items: center; gap: 8px;\">' +
                          '<span style=\"background:#0284c7; color:#fff; border-radius:50%; width:24px; height:24px; display:inline-flex; align-items:center; justify-content:center; font-size:12px;\">⏱️</span>' +
                          '<span>Doorstep Arrival Guarantee:</span>' +
                          '</div>' +
                          '<p style=\"margin: 0; color: #075985; font-size: 15px; font-weight: 800; line-height: 1.5;\">' +
                          'જો તમારી બુકિંગ Approved થશે ત્યારબાદ 60 મિનિટમાં ટેકનિશિયન તમારા ઘરે આવશે.' +
                          '</p>' +
                          '<p style=\"margin: 6px 0 0 0; color: #475569; font-size: 12.5px; line-height: 1.4;\">' +
                          '(Once your booking is approved by Admin, our expert technician will arrive at your home within 60 minutes.)' +
                          '</p>' +
                          '</div>' +
                          '</div>',
                    confirmButtonColor: '#059669',
                    confirmButtonText: '<i class=\"fas fa-list-check me-1\"></i> View My Bookings',
                    allowOutsideClick: false
                }).then(() => {
                    window.location = 'my_bookings.php';
                });
            });
        </script></body></html>";
        exit();
    } else {
        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script><style>body{font-family:sans-serif;background:#f5f7fb;}</style></head><body>';
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Booking Failed',
                text: 'Could not process your booking. Please try again.',
                confirmButtonColor: '#d33',
                confirmButtonText: 'Try Again'
            }).then(() => {
                window.history.back();
            });
        </script></body></html>";
        exit();
    }
}
?>

<!DOCTYPE html>
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
    <link href="css/style.css" rel="stylesheet">
    <link href="css/booking.css" rel="stylesheet">

</head>
<body>
<?php include 'includes/navbar.php'; ?>
<?php include 'includes/topbar.php'; ?>

<!-- ================= Hero Section ================= -->
<section class="booking-hero">

    <div class="container">

        <div class="row align-items-center">

            <div class="col-lg-7">

                <span class="hero-badge">
                    <?php echo htmlspecialchars($b_settings['hero_badge']); ?>
                </span>

                <h1 class="hero-title mt-4">
                    <?php echo htmlspecialchars($b_settings['hero_title']); ?>
                </h1>

                <p class="hero-text mt-4">
                    <?php echo nl2br(htmlspecialchars($b_settings['hero_text'])); ?>
                </p>

            </div>

            <div class="col-lg-5 text-center">

                <img src="img/bookimg3.jpg"
                     class="img-fluid hero-image">

            </div>

        </div>

    </div>

</section>

  <!-- Contact Start -->
<div class="container-xxl py-5">
    <div class="container">
        <div class="row g-5 align-items-center">

            <!-- Booking Form -->
            <div class="col-lg-7 wow fadeIn" data-wow-delay="0.1s">

                <div class="booking-card position-relative">

                    <!-- Live Top Availability Badge -->
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                        <div class="booking-live-header">
                            <span class="radar-dot"></span>
                            <span>Engineers Active • 60-Min Doorstep Service in Rajkot</span>
                        </div>
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 fw-bold" style="font-size: 11px;">
                            <i class="fas fa-bolt text-warning me-1"></i> Instant Booking
                        </span>
                    </div>

                    <h2 class="booking-title mb-1">
                        <i class="fas fa-calendar-check text-primary me-2"></i>
                        Fast AC Service Booking
                    </h2>

                    <p class="booking-subtitle text-muted mb-3">
                        Select your service & enter address for immediate technician dispatch.
                    </p>

                    <!-- Interactive 3-Step Process Bar -->
                    <div class="booking-steps-bar">
                        <div class="step-item active" id="stepIndicator1">
                            <span class="step-num"><i class="fas fa-user"></i></span>
                            <span class="step-label">1. Your Details</span>
                        </div>
                        <div class="step-line"></div>
                        <div class="step-item" id="stepIndicator2">
                            <span class="step-num"><i class="fas fa-tools"></i></span>
                            <span class="step-label">2. AC Service</span>
                        </div>
                        <div class="step-line"></div>
                        <div class="step-item" id="stepIndicator3">
                            <span class="step-num"><i class="fas fa-receipt"></i></span>
                            <span class="step-label">3. Confirm</span>
                        </div>
                    </div>

                    <form method="POST" action="" class="dynamic-booking-form" id="mainBookingForm">

                    <div class="row g-3">

                        <!-- First Name -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="name" name="first_name" value="<?php echo htmlspecialchars($user_first_name); ?>" placeholder="First Name" oninput="checkFormSteps()" required>
                                <label for="name"><i class="fas fa-user text-primary me-1"></i> First Name</label>
                            </div>
                        </div>

                        <!-- Last Name -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="lastname" name="last_name" value="<?php echo htmlspecialchars($user_last_name); ?>" placeholder="Last Name" oninput="checkFormSteps()" required>
                                <label for="lastname"><i class="fas fa-user-tag text-primary me-1"></i> Last Name</label>
                            </div>
                        </div>

                        <!-- Mobile Number -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="tel" class="form-control" id="mobile" name="mobile" value="<?php echo htmlspecialchars($user_mobile); ?>" placeholder="Mobile Number" pattern="[0-9]{10}" 
                                        maxlength="10" oninput="checkFormSteps()" required>
                                <label for="mobile"><i class="fas fa-phone-alt text-primary me-1"></i> Mobile Number (10 Digits)</label>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user_email); ?>" placeholder="Your Email" oninput="checkFormSteps()" required>
                                <label for="email"><i class="fas fa-envelope text-primary me-1"></i> Email Address</label>
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="col-12">
                            <div class="form-floating">
                                <textarea class="form-control" id="address" placeholder="Address" name="address" style="height:90px" oninput="checkFormSteps()" required><?php echo htmlspecialchars($user_address); ?></textarea>
                                <label for="address"><i class="fas fa-map-marker-alt text-primary me-1"></i> Doorstep Service Address (Area, Flat, Street)</label>
                            </div>
                        </div>

                        <!-- AC Brand / Company Dropdown -->
                        <div class="col-12">
                            <div class="form-floating">
                                <select class="form-select" id="acCompany" name="company_type" onchange="updateBrandInfo(); checkFormSteps();" required>
                                    <option value="">-- Select AC Brand / Company (e.g. Daikin, Voltas, LG, Hitachi, Carrier) --</option>
                                    <?php foreach ($booking_brands as $brand): ?>
                                        <option value="<?php echo htmlspecialchars($brand); ?>">
                                            <?php echo htmlspecialchars($brand); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="acCompany"><i class="fas fa-snowflake text-primary me-1"></i> AC Brand / Company</label>
                            </div>
                        </div>

                        <!-- ========================================================
                             DEDICATED SECTION: AC SERVICES (DIRECTLY VISIBLE CARDS)
                             ======================================================== -->
                        <div class="col-12">
                            <div class="services-dedicated-box" id="servicesExperienceBox">
                                <!-- Section Top Header -->
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="services-header-icon-box">
                                            <i class="fas fa-tools"></i>
                                        </div>
                                        <div>
                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                <h5 class="mb-0 fw-bold text-dark font-15">
                                                    Select AC Service Type
                                                </h5>
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-10">Service or Part</span>
                                            </div>
                                            <small class="text-muted font-11 d-block mt-0.5">
                                                <i class="fas fa-clock text-primary me-1"></i> 60-Min Doorstep Arrival • 30 Days Guarantee • Verified Technicians
                                            </small>
                                        </div>
                                    </div>
                                    <!-- Current Selected Service Status Pill -->
                                    <div class="services-status-pill" id="serviceStatusPill">
                                        <i class="fas fa-info-circle text-muted me-1"></i>
                                        <span id="serviceStatusText" class="font-12 fw-bold text-muted">Select Service (Or Spare Part Below)</span>
                                    </div>
                                </div>

                                <!-- Quick Live Search Bar for Services -->
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                    <small class="text-muted font-11 fw-bold">
                                        <i class="fas fa-hand-pointer text-primary me-1"></i> Click on a service to select (Optional if part chosen):
                                    </small>
                                    <div class="position-relative" style="min-width: 200px; max-width: 260px; flex-grow: 1;">
                                        <input type="text" class="form-control form-control-sm font-11 pe-4 rounded-pill" id="servicesSearchBox"
                                               placeholder="🔍 Search service..." oninput="searchServicesInput(this.value)">
                                        <span id="servicesSearchClear" class="position-absolute end-0 top-50 translate-middle-y me-2 text-muted"
                                              style="display:none; cursor:pointer; font-size:12px;" onclick="clearServicesSearch()">✕</span>
                                    </div>
                                </div>

                                <!-- Directly Visible Interactive Services Cards Grid -->
                                <div class="services-mini-grid" id="servicesCardsGrid">
                                    <!-- Dedicated No Service Chip (Spare Part Purchase Only) -->
                                    <div class="service-mini-chip service-none-chip"
                                         id="serviceNoneChip"
                                         data-service-name="None (Spare Part Only)"
                                         data-title="no service none spare part only"
                                         data-price="0"
                                         data-time="Direct Delivery"
                                         data-warranty="N/A"
                                         onclick="selectServiceCard('None (Spare Part Only)')">
                                        <div class="chip-icon-box bg-light text-muted">
                                            <i class="fas fa-ban chip-icon"></i>
                                        </div>
                                        <div class="chip-content">
                                            <div class="chip-name">No Service (Spare Part Only)</div>
                                            <div class="chip-meta">
                                                <span class="chip-time-tag bg-light text-muted">No Labor Charge</span>
                                                <span class="chip-warranty-tag bg-light text-muted">Parts Supply Only</span>
                                            </div>
                                        </div>
                                        <div class="chip-right">
                                            <span class="chip-price text-muted border-light">₹0</span>
                                            <span class="chip-check-circle"><i class="fas fa-check"></i></span>
                                        </div>
                                    </div>

                                    <?php 
                                    $preselected_service = $_GET['service_name'] ?? '';
                                    foreach ($booking_service_cards as $sc): 
                                        $is_active = ($preselected_service === $sc['name']) ? 'active' : '';
                                    ?>
                                        <div class="service-mini-chip <?php echo $is_active; ?>"
                                             data-service-name="<?php echo htmlspecialchars($sc['name']); ?>"
                                             data-title="<?php echo htmlspecialchars(strtolower($sc['name'] . ' ' . $sc['subtitle'])); ?>"
                                             data-price="<?php echo (float)$sc['price']; ?>"
                                             data-time="<?php echo htmlspecialchars($sc['estTime']); ?>"
                                             data-warranty="<?php echo htmlspecialchars($sc['warranty']); ?>"
                                             onclick="selectServiceCard('<?php echo htmlspecialchars(addslashes($sc['name'])); ?>')">
                                            <div class="chip-icon-box">
                                                <i class="<?php echo htmlspecialchars($sc['icon']); ?> chip-icon"></i>
                                            </div>
                                            <div class="chip-content">
                                                <div class="chip-name"><?php echo htmlspecialchars($sc['name']); ?></div>
                                                <div class="chip-meta">
                                                    <span class="chip-time-tag"><i class="far fa-clock me-1"></i><?php echo htmlspecialchars($sc['estTime']); ?></span>
                                                    <span class="chip-warranty-tag"><i class="fas fa-shield-alt me-1"></i><?php echo htmlspecialchars($sc['warranty']); ?></span>
                                                </div>
                                            </div>
                                            <div class="chip-right">
                                                <span class="chip-price">₹<?php echo number_format($sc['price']); ?></span>
                                                <span class="chip-check-circle"><i class="fas fa-check"></i></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="text-center py-2 text-muted small" id="servicesNoResults" style="display: none;">
                                    <i class="fas fa-search me-1"></i> No services match your search.
                                </div>
                            </div>
                            <!-- Hidden Input for native form POST -->
                            <input type="hidden" id="service_type" name="service_type" value="<?php echo htmlspecialchars($preselected_service); ?>">
                        </div>

                        <!-- ========================================================
                             DEDICATED SECTION: AC ORIGINAL SPARE PARTS (DIRECTLY VISIBLE)
                             ======================================================== -->
                        <div class="col-12">
                            <div class="parts-dedicated-box" id="partsExperienceBox">
                                <!-- Section Top Header -->
                                <div class="parts-dedicated-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="parts-header-icon-box">
                                            <i class="fas fa-cogs"></i>
                                        </div>
                                        <div>
                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                <h5 class="mb-0 fw-bold text-dark font-15">
                                                    AC Original Spare Parts
                                                </h5>
                                                <span class="badge bg-light text-muted border rounded-pill font-10">Optional</span>
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-10" id="brandDynamicBadge" style="display: none;">
                                                    <i class="fas fa-snowflake me-1"></i><span id="brandDynamicBadgeText">Verified OEM</span>
                                                </span>
                                            </div>
                                            <small class="text-muted font-11 d-block mt-0.5" id="partsBrandDynamicNotice">
                                                <i class="fas fa-shield-alt text-success me-1"></i> 100% Genuine OEM components • 6 Months Replacement Warranty
                                            </small>
                                        </div>
                                    </div>
                                    <!-- Current Selection Pill -->
                                    <div class="parts-status-pill" id="partsCurrentStatusPill">
                                        <i class="fas fa-check-circle text-success me-1"></i>
                                        <span id="partsStatusText" class="font-12 fw-bold text-dark">Service Only (No Extra Part)</span>
                                    </div>
                                </div>

                                <!-- Dynamic Active Selected Part Card (when selected) -->
                                <div class="part-selected-rich-card mb-3" id="partActiveBar" style="display: none;">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="part-selected-icon-box" id="activeBarIconBox">
                                            <i class="fas fa-cogs text-primary fs-5" id="activeBarIcon"></i>
                                        </div>
                                        <div>
                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                <strong class="text-dark font-13" id="activeBarPartTitle">Part Added</strong>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle font-10 rounded-pill"><i class="fas fa-shield-alt me-1"></i>6M Warranty</span>
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-10 rounded-pill" id="activeBarBrandTag">100% OEM</span>
                                            </div>
                                            <small class="text-muted font-11 d-block mt-0.5" id="activeBarSubtitle">Genuine OEM replacement component</small>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 ms-auto flex-wrap">
                                        <span class="badge bg-primary rounded-pill px-2.5 py-1.5 font-12 fw-bold" id="activeBarPartPrice">₹0</span>
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill font-11 px-2.5 py-1 fw-bold" onclick="setPartsMode('none')" title="Remove part and keep service only">
                                            ✕ Remove Part
                                        </button>
                                    </div>
                                </div>

                                <!-- Quick Live Search Bar -->
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                    <small class="text-muted font-11 fw-bold">
                                        <i class="fas fa-hand-pointer text-primary me-1"></i> Select part from list below:
                                    </small>
                                    <div class="position-relative" style="min-width: 220px; max-width: 300px; flex-grow: 1;">
                                        <input type="text" class="form-control form-control-sm font-11 pe-4 rounded-pill" id="partsSearchBox"
                                               placeholder="🔍 Search spare parts..." oninput="searchPartsInput(this.value)">
                                        <span id="partsSearchClear" class="position-absolute end-0 top-50 translate-middle-y me-2 text-muted"
                                              style="display:none; cursor:pointer; font-size:12px;" onclick="clearPartsSearch()">✕</span>
                                    </div>
                                </div>

                                <!-- Directly Visible Interactive Parts Cards Grid -->
                                <div class="parts-mini-grid" id="partsCardsGrid">
                                    <!-- Dedicated Service Only First Chip -->
                                    <div class="part-mini-chip service-only-chip active"
                                         id="serviceOnlyChip"
                                         data-raw="None (Service Only)"
                                         data-title="service only general none no spare part"
                                         data-cat="all"
                                         data-price="0"
                                         onclick="setPartsMode('none')">
                                        <div class="chip-icon-box bg-success-subtle text-success">
                                            <i class="fas fa-check-circle chip-icon"></i>
                                        </div>
                                        <div class="chip-content">
                                            <div class="chip-name">Service Only (No Part)</div>
                                            <div class="chip-meta">
                                                <span class="chip-brand-tag bg-light text-muted">Standard</span>
                                                <span class="chip-warranty-tag">Labor Only</span>
                                            </div>
                                        </div>
                                        <div class="chip-right">
                                            <span class="chip-price text-success border-success-subtle">₹0</span>
                                            <span class="chip-check-circle"><i class="fas fa-check"></i></span>
                                        </div>
                                    </div>

                                    <!-- All Database Spare Parts Chips -->
                                    <?php foreach ($booking_parts_catalog as $pt): ?>
                                        <div class="part-mini-chip"
                                             data-raw="<?php echo htmlspecialchars($pt['raw_name']); ?>"
                                             data-title="<?php echo htmlspecialchars(strtolower($pt['title'] . ' ' . $pt['raw_name'] . ' ' . $pt['subtitle'])); ?>"
                                             data-cat="<?php echo htmlspecialchars($pt['category']); ?>"
                                             data-price="<?php echo (float)$pt['price']; ?>"
                                             data-icon="<?php echo htmlspecialchars($pt['icon']); ?>"
                                             data-display-title="<?php echo htmlspecialchars($pt['title']); ?>"
                                             data-subtitle="<?php echo htmlspecialchars($pt['subtitle']); ?>"
                                             onclick="selectBrandChip('<?php echo htmlspecialchars(addslashes($pt['raw_name'])); ?>')">
                                            <div class="chip-icon-box">
                                                <i class="<?php echo htmlspecialchars($pt['icon']); ?> chip-icon"></i>
                                            </div>
                                            <div class="chip-content">
                                                <div class="chip-name"><?php echo htmlspecialchars($pt['title']); ?></div>
                                                <div class="chip-meta">
                                                    <span class="chip-brand-tag" data-part-brand>OEM Part</span>
                                                    <span class="chip-warranty-tag">6M Warranty</span>
                                                </div>
                                            </div>
                                            <div class="chip-right">
                                                <span class="chip-price">+₹<?php echo number_format($pt['price']); ?></span>
                                                <span class="chip-check-circle"><i class="fas fa-check"></i></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="text-center py-2 text-muted small" id="partsNoResults" style="display: none;">
                                    <i class="fas fa-search me-1"></i> No parts match your search.
                                </div>
                            </div>
                            <input type="hidden" id="selectedOriginalPart" name="original_part" value="None (Service Only)">
                        </div>

                        <!-- Live Dynamic Service Preview Badge (Inside Form) -->
                        <div class="col-12">
                            <div class="live-service-summary-card" id="formServiceSummaryBox">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-primary rounded-circle p-2"><i class="fas fa-check text-white"></i></span>
                                        <div>
                                            <strong class="text-dark d-block" id="formSummaryServiceName">AC Installation</strong>
                                            <small class="text-muted">Standard Doorstep AC Service</small>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <span class="service-summary-pill" id="formSummaryTime"><i class="fas fa-clock text-primary"></i> 45 Mins Arrival</span>
                                        <span class="service-summary-pill" id="formSummaryWarranty"><i class="fas fa-shield-alt text-success"></i> 30 Days Guarantee</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Mobile-Only Dedicated Coupon Slot (BEFORE Service Charge on Mobile Screens < 992px) -->
                        <div class="col-12 d-block d-lg-none" id="mobileCouponSlot">
                            <!-- Coupon card dynamically relocated here on mobile devices -->
                        </div>

                        <!-- Multi-Price Display System: Service Charge, Original Part Price, Total Amount -->
                        <div class="col-12">
                            <div class="row g-3">
                                <!-- Service Charge Card -->
                                <div class="col-md-4">
                                    <div class="price-box-card service-price-box">
                                        <small><i class="fas fa-wrench me-1"></i> Service Charge</small>
                                        <h3 id="servicePriceDisplay">₹ 0</h3>
                                        <span class="price-box-tag text-primary">Standard Rate</span>
                                    </div>
                                </div>

                                <!-- Original Part Price Card -->
                                <div class="col-md-4">
                                    <div class="price-box-card part-price-box">
                                        <small><i class="fas fa-cogs me-1"></i> Original Part Price</small>
                                        <h3 id="partPriceDisplay">₹ 0</h3>
                                        <span class="price-box-tag text-danger" id="partStatusTag">100% Genuine Part</span>
                                    </div>
                                </div>

                                <!-- Total Estimated Amount Card -->
                                <div class="col-md-4">
                                    <div class="price-box-card total-price-box">
                                        <small><i class="fas fa-calculator me-1"></i> Total Estimated Amount</small>
                                        <h2 id="priceDisplay">₹ 0</h2>
                                        <span class="price-box-tag text-warning" id="totalDiscountNote">Service + Part</span>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" id="price" name="price">
                        </div>

                        <!-- Submit Buttons -->
                        <div class="col-12 d-flex flex-wrap align-items-center gap-3 mt-3">
                            <!-- Book Now -->
                            <button class="btn btn-book-submit flex-grow-1 py-3" name="booking" type="submit" id="btnSubmitBooking">
                                <i class="fas fa-calendar-check me-2"></i>
                                Confirm & Book Service Now
                            </button>

                            <!-- User Dashboard -->
                            <a href="user_dashboard.php" class="btn btn-dashboard-link py-3">
                                <i class="fas fa-user-circle me-2"></i>
                                User Dashboard
                            </a>
                        </div>

                    </div>

                    <input type="hidden" name="coupon_code" id="couponCodeHidden" value="">
                    <input type="hidden" name="discount_percent" id="discountPercentHidden" value="0">
                    <input type="hidden" name="discount_amount" id="discountAmountHidden" value="0">
                    <input type="hidden" name="final_price" id="finalPriceHidden" value="0">

                    </form>
                </div>
            </div>

            <!-- Aqua Air Cooling Dynamic Interactive Side Card (Beside the Form) -->
            <div class="col-lg-5 wow fadeIn" data-wow-delay="0.3s">

                <!-- Desktop Dedicated Coupon Slot (Visible on Laptop / PC >= 992px) -->
                <div id="desktopCouponSlot" class="d-none d-lg-block mb-4">
                    <!-- 1. Dedicated Mystery Coupon Card -->
                    <div class="side-coupon-prominent-card" id="mysteryCouponCard">
                        <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                            <div class="d-flex align-items-center gap-2">
                                <span class="side-coupon-badge-icon">🎁</span>
                                <div>
                                    <strong class="text-dark d-block" style="font-size: 14px;">Mystery Coupon (1% to 10% OFF)</strong>
                                    <small class="text-muted" style="font-size: 11px;">Instant discount sent to: <strong><?php echo htmlspecialchars($user_email); ?></strong></small>
                                </div>
                            </div>
                            <button type="button" id="sendCouponBtn" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" style="font-size: 11px;" onclick="requestCouponViaAjax()" disabled title="Please select a service first">
                                <i class="fas fa-paper-plane me-1"></i> Send Code
                            </button>
                        </div>

                        <!-- Coupon Code Input & Verification Row -->
                        <div class="input-group input-group-sm mt-2">
                            <input type="text" id="couponCodeInput" class="form-control text-uppercase fw-bold border-primary" placeholder="Select a service first" style="letter-spacing: 1.5px; height: 38px;" disabled>
                            <button type="button" id="applyCouponBtn" class="btn btn-primary px-3 fw-bold" onclick="verifyAndApplyCoupon()" style="height: 38px;" disabled title="Please select a service first">
                                Apply Code
                            </button>
                        </div>

                        <!-- Applied Coupon Status & Remove Button -->
                        <div id="appliedCouponStatus" class="applied-coupon-box mt-2 p-2 bg-success-subtle border border-success-subtle rounded-3" style="display: none !important;">
                            <span class="text-success fw-bold" style="font-size: 12px;">
                                <i class="fas fa-circle-check me-1"></i> Coupon <strong id="appliedCodeText"></strong> Applied (<span id="appliedPercentText"></span>% OFF)
                            </span>
                            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 fw-bold" style="font-size:11px;" onclick="removeCoupon(true)">
                                <i class="fas fa-times me-1"></i> Remove
                            </button>
                        </div>

                        <!-- Live 60-Minute Reverse Countdown Timer Badge -->
                        <div id="couponCountdownBox" style="display:none;" class="alert alert-warning border-warning py-2 px-3 mt-2 mb-0 rounded-3 text-center small shadow-sm">
                            <i class="fas fa-stopwatch text-danger me-1 animate-pulse"></i>
                            <span>60-Min Reverse Countdown:</span>
                            <strong id="couponTimerText" class="text-danger fs-6 ms-1 fw-bold">59:59 Mins</strong>
                        </div>

                        <div class="d-flex align-items-center justify-content-between mt-2 pt-1">
                            <small class="text-muted" style="font-size: 10px;"><i class="fas fa-check-circle text-success me-1"></i> Instant 1% to 10% Discount</small>
                            <small class="text-primary fw-bold" style="font-size: 10px;">Valid 60 Mins Only</small>
                        </div>
                    </div>
                </div>

                <script>
                function adaptCouponPosition() {
                    var card = document.getElementById('mysteryCouponCard');
                    var mobileSlot = document.getElementById('mobileCouponSlot');
                    var desktopSlot = document.getElementById('desktopCouponSlot');
                    if (!card || !mobileSlot || !desktopSlot) return;
                    if (window.innerWidth < 992) {
                        if (card.parentNode !== mobileSlot) {
                            mobileSlot.appendChild(card);
                        }
                    } else {
                        if (card.parentNode !== desktopSlot) {
                            desktopSlot.appendChild(card);
                        }
                    }
                }
                adaptCouponPosition();
                window.addEventListener('resize', adaptCouponPosition);
                document.addEventListener('DOMContentLoaded', adaptCouponPosition);
                </script>

                <div class="side-promo-card dynamic-glow-card">

                    <!-- Top Brand Header -->
                    <div class="promo-header">
                        <div class="brand-tag">
                            <span class="snowflake-icon">❄️</span>
                            <span class="brand-name">AQUA AIR COOLING</span>
                        </div>
                        <span class="live-badge">
                            <span class="pulse-dot"></span> <span id="liveTechCount"></span> 
                        </span>
                    </div>

                    <!-- Main Content Body -->
                    <div class="promo-body">
                        <!-- Hero Headings -->
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h3 class="promo-title mb-1">
                                    <?php echo htmlspecialchars($b_settings['promo_title']); ?>
                                </h3>
                                <div class="rating-badge">
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star-half-alt text-warning"></i>
                                    <strong><?php echo htmlspecialchars($b_settings['rating_text']); ?></strong>
                                </div>
                            </div>
                        </div>

                        <!-- Live Ticker Bar -->
                        <div class="live-ticker-bar mt-2">
                            <i class="fas fa-fire-alt text-danger me-2 animate-bounce"></i>
                            <span><?php echo htmlspecialchars($b_settings['ticker_text']); ?></span>
                        </div>

                        <!-- Dynamic Selected Brand Badge (Updates when AC Brand chosen) -->
                        <div id="dynamicBrandBadge" class="dynamic-brand-box" style="display: none;">
                            <div class="d-flex align-items-center gap-2">
                                <span class="brand-icon-chip"><i class="fas fa-tools"></i></span>
                                <div>
                                    <small class="text-uppercase text-muted fw-bold d-block" style="font-size:10px;">Brand Specialization</small>
                                    <strong id="selectedBrandText" class="text-primary">Voltas Certified Engineers</strong>
                                </div>
                            </div>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1" style="font-size: 11px;">100% Genuine Parts</span>
                        </div>

                        <!-- Dynamic Selected Service Live Info Box -->
                        <div id="dynamicServiceInfo" class="dynamic-service-box" style="display: none;">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="dynamic-badge"><i class="fas fa-bolt text-warning me-1"></i> Live Estimate Breakdown</span>
                                <span class="badge bg-primary rounded-pill px-2" id="dynServiceWarranty">30 Days Warranty</span>
                            </div>

                            <div class="d-flex align-items-center justify-content-between my-2 pb-2 border-bottom">
                                <div>
                                    <span class="dyn-service-name d-block text-dark fw-bold" id="dynServiceName">AC Service</span>
                                    <small class="text-muted" id="dynServiceTime"><i class="far fa-clock me-1"></i> 45 Mins Arrival</small>
                                </div>
                                <div class="text-end">
                                    <span class="dyn-service-price d-block text-primary fw-bold fs-6" id="dynServicePrice">₹ 0</span>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between my-2 pb-2 border-bottom" id="dynPartRow" style="display:none;">
                                <div>
                                    <span class="dyn-service-name d-block text-danger fw-bold" id="dynPartName" style="font-size: 13px;">Original Part</span>
                                    <small class="text-muted"><i class="fas fa-shield-alt text-success me-1"></i> 100% Genuine Part</small>
                                </div>
                                <div class="text-end">
                                    <span class="dyn-service-price d-block text-danger fw-bold fs-6" id="dynPartPrice">₹ 0</span>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between mt-2 pt-1">
                                <strong class="text-dark">Total Net Payable:</strong>
                                <div class="text-end">
                                    <span class="fs-5 fw-bold text-success d-block" id="dynTotalPrice">₹ 0</span>
                                    <span class="discount-tag" id="discountTag" style="display:none;">🎉 ₹100 Coupon Applied</span>
                                </div>
                            </div>
                        </div>

                        <!-- Default Tip Banner when no service is selected -->
                        <div id="dynamicServiceTip" class="dynamic-tip-box">
                            <div class="me-3 fs-4"><i class="fas fa-magic text-primary"></i></div>
                            <div>
                                <strong class="d-block text-dark">Instant Live Estimator</strong>
                                <span>Select a service on the form to view instant details & special discounts!</span>
                            </div>
                        </div>






                        <!-- 4 Core Features Grid (ASCII Layout Direct Match) -->
                        <div class="features-grid my-3">
                            <div class="feature-item">
                                <div class="feature-icon">👨‍🔧</div>
                                <div class="feature-info">
                                    <strong>Expert Technicians</strong>
                                    <small>50+ Certified Engineers</small>
                                </div>
                            </div>

                            <div class="feature-item">
                                <div class="feature-icon">⚡</div>
                                <div class="feature-info">
                                    <strong>Quick Service</strong>
                                    <small>60-Min Doorstep Arrival</small>
                                </div>
                            </div>

                            <div class="feature-item">
                                <div class="feature-icon">💰</div>
                                <div class="feature-info">
                                    <strong>Transparent Pricing</strong>
                                    <small>Fixed Standard Rates</small>
                                </div>
                            </div>

                            <div class="feature-item">
                                <div class="feature-icon">🛡️</div>
                                <div class="feature-info">
                                    <strong>Warranty Included</strong>
                                    <small>30-Day Money Back</small>
                                </div>
                            </div>
                        </div>

                        <!-- Need Help Contact Box -->
                        <div class="help-box mt-3">
                            <div class="help-left">
                                <div class="help-head-icon">🎧</div>
                                <div>
                                    <div class="help-title">Need Instant Help?</div>
                                    <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $contact_help['phone']); ?>" class="help-phone"><?php echo htmlspecialchars($contact_help['phone']); ?></a>
                                    <div class="help-brand-large">AQUA AIR COOLING</div>
                                </div>
                            </div>
                            <div class="help-actions">
                                <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $contact_help['phone']); ?>" class="help-btn call-btn" title="Call Now">
                                    <i class="fas fa-phone-alt"></i>
                                </a>
                                <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $contact_help['whatsapp']); ?>?text=Hello%20Aqua%20Air%20Cooling,%20I%20need%20AC%20Service" target="_blank" class="help-btn whatsapp-btn" title="WhatsApp Support">
                                    <i class="fab fa-whatsapp"></i>
                                </a>
                            </div>
                        </div>


                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<!-- Contact End -->
<?php include 'includes/footer.php'; ?>

<script>

const validUserCoupons = <?php echo json_encode(isset($_SESSION['user_coupons']) ? $_SESSION['user_coupons'] : new stdClass()); ?>;
const serviceDetailsMap = <?php echo json_encode($booking_service_details); ?>;
const bookingPartsData = <?php echo json_encode($booking_parts_catalog); ?>;
const bookingPartsPriceMap = <?php echo json_encode($parts_price_map); ?>;

let baseUnitPrice = 0;
let couponApplied = false;
let appliedCouponCode = "";
let appliedDiscountPercent = 0;

document.addEventListener("DOMContentLoaded", function() {
    let serviceSelect = document.getElementById("service_type");
    if (serviceSelect && serviceSelect.value) {
        selectServiceCard(serviceSelect.value);
    }
    updateBrandInfo();
    let hiddenPart = document.getElementById("selectedOriginalPart");
    if (hiddenPart && hiddenPart.value && hiddenPart.value !== "None (Service Only)" && hiddenPart.value !== "None") {
        selectBrandChip(hiddenPart.value);
    }

    // Form submit validation: User must select at least ONE (Service OR Spare Part)
    let bookingForm = document.getElementById("mainBookingForm");
    if (bookingForm) {
        bookingForm.addEventListener("submit", function(e) {
            let srvInput = document.getElementById("service_type")?.value.trim() || "";
            let partInput = document.getElementById("selectedOriginalPart")?.value.trim() || "";

            let hasService = (srvInput !== "" && srvInput !== "None (Spare Part Only)" && srvInput !== "None");
            let hasPart = (partInput !== "" && partInput !== "None (Service Only)" && partInput !== "None / General Service Only" && partInput !== "None");

            if (!hasService && !hasPart) {
                e.preventDefault();
                let srvBox = document.getElementById("servicesExperienceBox");
                if (srvBox) {
                    srvBox.scrollIntoView({ behavior: "smooth", block: "center" });
                    srvBox.classList.add("brand-highlight");
                    setTimeout(() => { srvBox.classList.remove("brand-highlight"); }, 1200);
                }
                if (typeof Swal === "function") {
                    Swal.fire({
                        icon: "warning",
                        title: "Selection Required",
                        text: "Please select at least an AC Service OR an AC Spare Part to proceed with your booking!",
                        confirmButtonColor: "#0284c7"
                    });
                } else {
                    alert("Please select at least an AC Service OR an AC Spare Part to proceed!");
                }
                return false;
            }
        });
    }
});

// Interactive Dedicated AC Services Logic
function selectServiceCard(serviceName) {
    let selectElem = document.getElementById("service_type");
    if (!selectElem) return;

    // Toggle behavior: if clicking already selected service, toggle off
    let currentVal = selectElem.value;
    if (currentVal === serviceName && serviceName !== "None (Spare Part Only)") {
        serviceName = "None (Spare Part Only)";
    }

    let isNone = (serviceName === "None (Spare Part Only)" || serviceName === "");
    selectElem.value = isNone ? "" : serviceName;

    let pickedPrice = 0;

    document.querySelectorAll(".service-mini-chip").forEach(card => {
        let cardSrv = card.getAttribute("data-service-name");
        if (cardSrv === serviceName || (isNone && card.id === "serviceNoneChip")) {
            card.classList.add("active");
            pickedPrice = Number(card.getAttribute("data-price")) || 0;
        } else {
            card.classList.remove("active");
        }
    });

    let statusPill = document.getElementById("serviceStatusPill");
    let statusText = document.getElementById("serviceStatusText");
    if (statusPill && statusText) {
        if (!isNone) {
            statusPill.classList.add("has-service");
            statusPill.innerHTML = '<i class="fas fa-check-circle text-primary me-1"></i> <span id="serviceStatusText" class="font-12 fw-bold text-primary">' + serviceName + ' (₹' + Number(pickedPrice).toLocaleString() + ')</span>';
        } else {
            statusPill.classList.remove("has-service");
            statusPill.innerHTML = '<i class="fas fa-info-circle text-muted me-1"></i> <span id="serviceStatusText" class="font-12 fw-bold text-muted">No Service (Spare Part Only)</span>';
        }
    }

    showPrice();
    checkFormSteps();

    // Pulse highlight service price card
    let srvBox = document.querySelector(".service-price-box");
    if (srvBox) {
        srvBox.style.transform = "scale(1.04)";
        setTimeout(() => { srvBox.style.transform = ""; }, 300);
    }
}

function searchServicesInput(query) {
    let clearBtn = document.getElementById("servicesSearchClear");
    if (clearBtn) {
        clearBtn.style.display = query.trim().length > 0 ? "block" : "none";
    }
    applyServicesFiltering(query.toLowerCase().trim());
}

function clearServicesSearch() {
    let box = document.getElementById("servicesSearchBox");
    let clearBtn = document.getElementById("servicesSearchClear");
    if (box) box.value = "";
    if (clearBtn) clearBtn.style.display = "none";
    applyServicesFiltering("");
}

function applyServicesFiltering(query) {
    let visibleCount = 0;
    document.querySelectorAll(".service-mini-chip").forEach(card => {
        let cardTitle = (card.getAttribute("data-title") || "").toLowerCase();
        let matchesQuery = (!query || cardTitle.includes(query));

        if (matchesQuery) {
            card.style.display = "flex";
            visibleCount++;
        } else {
            card.style.display = "none";
        }
    });

    let noRes = document.getElementById("servicesNoResults");
    if (noRes) {
        noRes.style.display = visibleCount === 0 ? "block" : "none";
    }
}

function showPrice() {
    let serviceSelect = document.getElementById("service_type");
    let service = serviceSelect ? serviceSelect.value : "";
    let price = 0;
    let estTime = "";
    let warranty = "";

    if (service && serviceDetailsMap[service]) {
        price = Number(serviceDetailsMap[service].price) || 0;
        estTime = serviceDetailsMap[service].estTime || "45-60 Mins Arrival";
        warranty = serviceDetailsMap[service].warranty || "30 Days Guarantee";
    }

    baseUnitPrice = price;

    let partPrice = getSelectedPartPrice();
    let grossTotal = baseUnitPrice + partPrice;

    let sendBtn = document.getElementById("sendCouponBtn");
    let applyBtn = document.getElementById("applyCouponBtn");
    let couponInput = document.getElementById("couponCodeInput");
    let tag = document.getElementById("discountTag");

    // Inside Form Dynamic Summary Box
    let formSumBox = document.getElementById("formServiceSummaryBox");
    let formSumName = document.getElementById("formSummaryServiceName");
    let formSumTime = document.getElementById("formSummaryTime");
    let formSumWarranty = document.getElementById("formSummaryWarranty");

    if (formSumBox) {
        if (service && price > 0) {
            formSumBox.style.display = 'block';
            if (formSumName) formSumName.innerText = service;
            if (formSumTime) formSumTime.innerHTML = '<i class="fas fa-clock text-primary"></i> ' + estTime;
            if (formSumWarranty) formSumWarranty.innerHTML = '<i class="fas fa-shield-alt text-success"></i> ' + warranty;
        } else if (partPrice > 0) {
            formSumBox.style.display = 'block';
            if (formSumName) formSumName.innerText = "Genuine OEM Spare Part Order";
            if (formSumTime) formSumTime.innerHTML = '<i class="fas fa-truck text-primary"></i> Same-Day Doorstep Supply';
            if (formSumWarranty) formSumWarranty.innerHTML = '<i class="fas fa-shield-alt text-success"></i> 6 Months Replacement Warranty';
        } else {
            formSumBox.style.display = 'none';
        }
    }

    // Enable/Disable coupon controls based on total price (Service OR Part)
    if(grossTotal > 0) {
        if(sendBtn) {
            sendBtn.disabled = false;
            sendBtn.title = "Send coupon code to your email";
        }
        if(couponInput && !couponApplied) {
            couponInput.disabled = false;
            couponInput.placeholder = "ENTER COUPON CODE";
        }
        if(applyBtn && !couponApplied) {
            applyBtn.disabled = false;
            applyBtn.title = "Apply entered coupon code";
        }
    } else {
        // Reset coupon if neither service nor part is selected
        couponApplied = false;
        appliedCouponCode = "";
        appliedDiscountPercent = 0;
        if(sendBtn) {
            sendBtn.disabled = true;
            sendBtn.title = "Please select a service or spare part first";
        }
        if(applyBtn) {
            applyBtn.disabled = true;
            applyBtn.title = "Please select a service or spare part first";
            applyBtn.innerHTML = "Apply Code";
            applyBtn.classList.remove("btn-success");
            applyBtn.classList.add("btn-primary");
        }
        if(couponInput) {
            couponInput.disabled = true;
            couponInput.placeholder = "Select a service or part first";
            couponInput.value = "";
        }
        if(tag) tag.style.display = "none";
    }

    recalculateTotal();

    // Update dynamic side card info
    let dynamicBox = document.getElementById("dynamicServiceInfo");
    let dynamicTip = document.getElementById("dynamicServiceTip");
    if(dynamicBox && dynamicTip) {
        if(service && price > 0) {
            dynamicBox.style.display = 'block';
            dynamicTip.style.display = 'none';
            document.getElementById("dynServiceName").innerText = service;
            document.getElementById("dynServiceTime").innerHTML = '<i class="far fa-clock me-1"></i> ' + estTime;
            document.getElementById("dynServiceWarranty").innerText = warranty;
        } else if(partPrice > 0) {
            dynamicBox.style.display = 'block';
            dynamicTip.style.display = 'none';
            document.getElementById("dynServiceName").innerText = "Spare Part Supply (No Labor)";
            document.getElementById("dynServiceTime").innerHTML = '<i class="fas fa-truck me-1"></i> Same-Day Delivery';
            document.getElementById("dynServiceWarranty").innerText = "6 Months OEM Warranty";
        } else {
            dynamicBox.style.display = 'none';
            dynamicTip.style.display = 'flex';
        }
    }
    checkFormSteps();
}

// Interactive Dedicated AC Spare Parts Logic
function setPartsMode(mode) {
    let hiddenInput = document.getElementById("selectedOriginalPart");
    let activeBar = document.getElementById("partActiveBar");
    let statusPill = document.getElementById("partsCurrentStatusPill");
    let serviceOnlyChip = document.getElementById("serviceOnlyChip");

    if (mode === 'none') {
        if (hiddenInput) hiddenInput.value = "None (Service Only)";
        if (activeBar) activeBar.style.display = "none";

        if (statusPill) {
            statusPill.classList.remove("has-part");
            statusPill.innerHTML = '<i class="fas fa-check-circle text-success me-1"></i> <span id="partsStatusText" class="font-12 fw-bold text-dark">Service Only (No Extra Part)</span>';
        }

        // Deactivate all spare parts chips, activate Service Only chip
        document.querySelectorAll(".part-mini-chip").forEach(card => card.classList.remove("active"));
        if (serviceOnlyChip) serviceOnlyChip.classList.add("active");
    }

    recalculateTotal();
    checkFormSteps();
}

function selectBrandChip(rawName) {
    let hiddenInput = document.getElementById("selectedOriginalPart");
    let activeBar = document.getElementById("partActiveBar");
    let activeBarTitle = document.getElementById("activeBarPartTitle");
    let activeBarPrice = document.getElementById("activeBarPartPrice");
    let activeBarSubtitle = document.getElementById("activeBarSubtitle");
    let activeBarIcon = document.getElementById("activeBarIcon");
    let statusPill = document.getElementById("partsCurrentStatusPill");
    let serviceOnlyChip = document.getElementById("serviceOnlyChip");

    if (!rawName || rawName === "None (Service Only)" || rawName === "None / General Service Only" || rawName === "None") {
        setPartsMode('none');
        return;
    }

    if (hiddenInput) hiddenInput.value = rawName;

    let pickedTitle = rawName;
    let pickedSubtitle = "Genuine OEM replacement component";
    let pickedPrice = (typeof bookingPartsPriceMap !== "undefined" && bookingPartsPriceMap[rawName]) ? bookingPartsPriceMap[rawName] : 0;
    let pickedIcon = "fas fa-cogs";

    // Deactivate service-only chip
    if (serviceOnlyChip) serviceOnlyChip.classList.remove("active");

    // Highlight active chip and extract metadata
    document.querySelectorAll(".part-mini-chip").forEach(card => {
        if (card.getAttribute("data-raw") === rawName) {
            card.classList.add("active");
            pickedTitle = card.getAttribute("data-display-title") || card.querySelector(".chip-name")?.innerText || rawName;
            pickedSubtitle = card.getAttribute("data-subtitle") || pickedSubtitle;
            let cardPrice = Number(card.getAttribute("data-price"));
            if (cardPrice > 0) pickedPrice = cardPrice;
            pickedIcon = card.getAttribute("data-icon") || pickedIcon;
        } else {
            card.classList.remove("active");
        }
    });

    if (activeBar) {
        if (activeBarTitle) activeBarTitle.innerText = pickedTitle;
        if (activeBarSubtitle) activeBarSubtitle.innerText = pickedSubtitle;
        if (activeBarPrice) activeBarPrice.innerText = "+₹" + Number(pickedPrice).toLocaleString();
        if (activeBarIcon) activeBarIcon.className = pickedIcon + " text-primary fs-5";
        activeBar.style.display = "flex";
    }

    if (statusPill) {
        statusPill.classList.add("has-part");
        let shortTitle = pickedTitle.length > 20 ? (pickedTitle.substring(0, 18) + '..') : pickedTitle;
        statusPill.innerHTML = '<i class="fas fa-check-circle text-success me-1"></i> <span id="partsStatusText" class="font-12 fw-bold text-success">' + shortTitle + ' (+₹' + Number(pickedPrice).toLocaleString() + ')</span>';
    }

    // Dynamic brand tag sync
    let brandSelect = document.getElementById("acCompany");
    let activeBarBrandTag = document.getElementById("activeBarBrandTag");
    if (activeBarBrandTag) {
        let bVal = brandSelect ? brandSelect.value.trim() : "";
        if (bVal && bVal !== "Other" && bVal !== "None / General Service Only" && bVal !== "None") {
            let cleanB = bVal.replace(/\s+AC$/i, '');
            activeBarBrandTag.innerHTML = '<i class="fas fa-check-circle text-primary me-1"></i> ' + cleanB + ' OEM';
        } else {
            activeBarBrandTag.innerHTML = '<i class="fas fa-check-circle text-primary me-1"></i> OEM Genuine';
        }
    }

    recalculateTotal();
    checkFormSteps();

    // Subtle pulse highlight
    let partBox = document.querySelector(".part-price-box");
    if (partBox) {
        partBox.style.transform = "scale(1.04)";
        setTimeout(() => { partBox.style.transform = ""; }, 300);
    }
}

function searchPartsInput(query) {
    let clearBtn = document.getElementById("partsSearchClear");
    if (clearBtn) {
        clearBtn.style.display = query.trim().length > 0 ? "block" : "none";
    }
    applyPartsFiltering(query.toLowerCase().trim());
}

function clearPartsSearch() {
    let box = document.getElementById("partsSearchBox");
    let clearBtn = document.getElementById("partsSearchClear");
    if (box) box.value = "";
    if (clearBtn) clearBtn.style.display = "none";
    applyPartsFiltering("");
}

function applyPartsFiltering(query) {
    let visibleCount = 0;
    document.querySelectorAll(".part-mini-chip").forEach(card => {
        let cardTitle = (card.getAttribute("data-title") || "").toLowerCase();
        let matchesQuery = (!query || cardTitle.includes(query));

        if (matchesQuery) {
            card.style.display = "flex";
            visibleCount++;
        } else {
            card.style.display = "none";
        }
    });

    let noRes = document.getElementById("partsNoResults");
    if (noRes) {
        noRes.style.display = visibleCount === 0 ? "block" : "none";
    }
}

// Dynamic 3-Step Process Indicator Tracking
function checkFormSteps() {
    let name = document.getElementById("name")?.value.trim() || "";
    let mobile = document.getElementById("mobile")?.value.trim() || "";
    let address = document.getElementById("address")?.value.trim() || "";
    let srvInput = document.getElementById("service_type")?.value.trim() || "";
    let partInput = document.getElementById("selectedOriginalPart")?.value.trim() || "";
    let brand = document.getElementById("acCompany")?.value || "";

    let hasService = (srvInput !== "" && srvInput !== "None (Spare Part Only)" && srvInput !== "None");
    let hasPart = (partInput !== "" && partInput !== "None (Service Only)" && partInput !== "None / General Service Only" && partInput !== "None");

    let step1 = document.getElementById("stepIndicator1");
    let step2 = document.getElementById("stepIndicator2");
    let step3 = document.getElementById("stepIndicator3");

    // Step 1: Customer Info
    let isStep1Done = (name.length > 1 && mobile.length === 10 && address.length > 3);
    if (isStep1Done) {
        step1?.classList.add("completed");
        step1?.classList.remove("active");
        step2?.classList.add("active");
    } else {
        step1?.classList.remove("completed");
        step1?.classList.add("active");
        step2?.classList.remove("active", "completed");
    }

    // Step 2: Brand + (Service OR Part)
    let isStep2Done = (brand !== "" && (hasService || hasPart));
    if (isStep1Done && isStep2Done) {
        step2?.classList.add("completed");
        step2?.classList.remove("active");
        step3?.classList.add("active", "completed");
    } else if (!isStep1Done) {
        step2?.classList.remove("active", "completed");
        step3?.classList.remove("active", "completed");
    } else {
        step2?.classList.remove("completed");
        step2?.classList.add("active");
        step3?.classList.remove("active", "completed");
    }
}

function getSelectedPartPrice() {
    let partInput = document.getElementById("selectedOriginalPart");
    if (!partInput || !partInput.value) return 0;
    let val = partInput.value.trim();
    if (val === "None (Service Only)" || val === "None / General Service Only" || val === "None") return 0;
    if (typeof bookingPartsPriceMap !== "undefined" && bookingPartsPriceMap[val]) {
        return Number(bookingPartsPriceMap[val]);
    }
    let match = val.match(/[-:]\s*([0-9]+)\s*$/);
    if (match && match[1]) {
        return Number(match[1]);
    }
    let matchAny = val.match(/([0-9]+)/);
    if (matchAny && matchAny[1]) {
        return Number(matchAny[1]);
    }
    return 0;
}

function recalculateTotal() {
    let partPrice = getSelectedPartPrice();
    let grossTotal = baseUnitPrice + partPrice;

    if(grossTotal === 0) {
        document.getElementById("price").value = 0;
        document.getElementById("discountPercentHidden").value = 0;
        document.getElementById("discountAmountHidden").value = 0;
        document.getElementById("finalPriceHidden").value = 0;
        document.getElementById("servicePriceDisplay").innerText = "₹ 0";
        document.getElementById("partPriceDisplay").innerText = "₹ 0";
        document.getElementById("priceDisplay").innerText = "₹ 0";
        if(document.getElementById("dynServicePrice")) {
            document.getElementById("dynServicePrice").innerText = "₹ 0";
        }
        if(document.getElementById("dynPartPrice")) {
            document.getElementById("dynPartPrice").innerText = "₹ 0";
        }
        if(document.getElementById("dynTotalPrice")) {
            document.getElementById("dynTotalPrice").innerText = "₹ 0";
        }
        return;
    }

    let serviceDiscount = (couponApplied && appliedDiscountPercent > 0) ? Math.round(baseUnitPrice * (appliedDiscountPercent / 100)) : 0;
    let discountedServicePrice = Math.max(0, baseUnitPrice - serviceDiscount);

    let discount = (couponApplied && appliedDiscountPercent > 0) ? Math.round(grossTotal * (appliedDiscountPercent / 100)) : 0;
    let finalPrice = Math.max(0, grossTotal - discount);

    let tag = document.getElementById("discountTag");
    if(tag && couponApplied) {
        tag.innerText = "🎉 " + appliedCouponCode + " (" + appliedDiscountPercent + "% OFF = -₹" + discount + ")";
        tag.style.display = "inline-block";
    }

    // Update Form Displays
    if (couponApplied && appliedDiscountPercent > 0 && baseUnitPrice > 0) {
        document.getElementById("servicePriceDisplay").innerHTML = '<span class="text-muted text-decoration-line-through me-1" style="font-size:16px; font-weight:600;">₹ ' + baseUnitPrice.toLocaleString() + '</span><span class="text-primary fw-bold">₹ ' + discountedServicePrice.toLocaleString() + '</span>';
    } else {
        document.getElementById("servicePriceDisplay").innerText = "₹ " + baseUnitPrice.toLocaleString();
    }

    document.getElementById("partPriceDisplay").innerText = "₹ " + partPrice.toLocaleString();
    document.getElementById("priceDisplay").innerText = "₹ " + finalPrice.toLocaleString();

    let partTag = document.getElementById("partStatusTag");
    if (partTag) {
        partTag.innerText = (partPrice > 0) ? "100% Genuine OEM Part" : "No Part Selected";
    }

    let totalNote = document.getElementById("totalDiscountNote");
    if (totalNote) {
        if (couponApplied) {
            totalNote.innerText = "🎉 " + appliedDiscountPercent + "% Coupon Discount Applied!";
        } else if (partPrice > 0 && baseUnitPrice > 0) {
            totalNote.innerText = "Service (₹" + baseUnitPrice.toLocaleString() + ") + Part (₹" + partPrice.toLocaleString() + ")";
        } else {
            totalNote.innerText = "Total Net Charge";
        }
    }

    // Update Side Card Breakdown
    if(document.getElementById("dynServicePrice")) {
        if (couponApplied && appliedDiscountPercent > 0 && baseUnitPrice > 0) {
            document.getElementById("dynServicePrice").innerHTML = '<span class="text-muted text-decoration-line-through me-1" style="font-size:12px; font-weight:normal;">₹ ' + baseUnitPrice.toLocaleString() + '</span><span class="text-primary fw-bold">₹ ' + discountedServicePrice.toLocaleString() + '</span>';
        } else {
            document.getElementById("dynServicePrice").innerText = "₹ " + baseUnitPrice.toLocaleString();
        }
    }
    
    let dynPartRow = document.getElementById("dynPartRow");
    let dynPartName = document.getElementById("dynPartName");
    let dynPartPrice = document.getElementById("dynPartPrice");
    let partInput = document.getElementById("selectedOriginalPart");

    if (dynPartRow && dynPartName && dynPartPrice) {
        if (partPrice > 0 && partInput && partInput.value && partInput.value !== "None (Service Only)" && partInput.value !== "None") {
            dynPartRow.style.display = "flex";
            let brandSelect = document.getElementById("acCompany");
            let bVal = brandSelect ? brandSelect.value.trim() : "";
            let brandPrefix = (bVal && bVal !== "Other" && bVal !== "None / General Service Only" && bVal !== "None") ? (bVal.replace(/\s+AC$/i, '') + " ") : "";
            
            let displayTitle = partInput.value;
            if (typeof bookingPartsData !== "undefined" && Array.isArray(bookingPartsData)) {
                let found = bookingPartsData.find(p => p.raw_name === partInput.value);
                if (found && found.title) displayTitle = found.title;
            }
            dynPartName.innerText = (brandPrefix ? brandPrefix + "• " : "") + displayTitle;
            dynPartPrice.innerText = "₹ " + Number(partPrice).toLocaleString();
        } else {
            dynPartRow.style.display = "none";
        }
    }

    if(document.getElementById("dynTotalPrice")) {
        document.getElementById("dynTotalPrice").innerText = "₹ " + finalPrice.toLocaleString();
    }

    // Hidden Inputs for Backend
    document.getElementById("price").value = grossTotal;
    document.getElementById("discountPercentHidden").value = couponApplied ? appliedDiscountPercent : 0;
    document.getElementById("discountAmountHidden").value = couponApplied ? discount : 0;
    document.getElementById("finalPriceHidden").value = finalPrice;
}

function verifyAndApplyCoupon() {

    let serviceSelect = document.getElementById("service_type");
    let couponInput = document.getElementById("couponCodeInput");
    let btn = document.getElementById("applyCouponBtn");
    let tag = document.getElementById("discountTag");

    /* =========================================
       REQUIRE SERVICE
    ========================================= */

    if (
        !serviceSelect ||
        !serviceSelect.value ||
        typeof baseUnitPrice === "undefined" ||
        baseUnitPrice === 0
    ) {

        if (typeof Swal === "function") {

            Swal.fire({
                icon: "warning",
                title: "Select Service First",
                text: "Please select a Service Type before applying a coupon code!",
                confirmButtonColor: "#0d6efd"
            });

        } else {

            alert(
                "Please select a Service Type before applying a coupon code!"
            );
        }

        return;
    }


    /* =========================================
       GET COUPON
    ========================================= */

    let enteredCode = couponInput
        ? couponInput.value.trim().toUpperCase()
        : "";


    if (!enteredCode) {

        if (typeof Swal === "function") {

            Swal.fire({
                icon: "error",
                title: "Enter Coupon Code",
                text: "Please enter your coupon code first!",
                confirmButtonColor: "#0d6efd"
            });

        } else {

            alert("Please enter your coupon code first!");
        }

        return;
    }


    /* =========================================
       CHECK COUPON
    ========================================= */

    if (
        typeof validUserCoupons === "undefined" ||
        !validUserCoupons.hasOwnProperty(enteredCode)
    ) {

        if (typeof Swal === "function") {

            Swal.fire({
                icon: "error",
                title: "Invalid Coupon Code",
                text:
                    'The coupon code "' +
                    enteredCode +
                    '" is invalid or does not exist.',
                confirmButtonColor: "#d33"
            });

        } else {

            alert("Invalid Coupon Code!");
        }

        return;
    }


    /* =========================================
       GET COUPON DATA
    ========================================= */

    let couponObj = validUserCoupons[enteredCode];

    let percent =
        typeof couponObj === "object"
            ? parseFloat(couponObj.percent)
            : parseFloat(couponObj);

    let expiresAt =
        typeof couponObj === "object"
            ? parseInt(couponObj.expires_at || 0)
            : 0;


    /* =========================================
       VALIDATE PERCENT
    ========================================= */

    if (
        isNaN(percent) ||
        percent < 1 ||
        percent > 10
    ) {

        if (typeof Swal === "function") {

            Swal.fire({
                icon: "error",
                title: "Invalid Discount",
                text: "Coupon discount must be between 1% and 10%.",
                confirmButtonColor: "#d33"
            });

        }

        return;
    }


    /* =========================================
       60 MINUTE EXPIRY
    ========================================= */

    let nowTs =
        Math.floor(Date.now() / 1000);


    if (
        expiresAt > 0 &&
        nowTs > expiresAt
    ) {

        if (typeof Swal === "function") {

            Swal.fire({
                icon: "error",
                title: "⌛ Coupon Code Expired!",
                html:
                    "The coupon code <strong>" +
                    enteredCode +
                    "</strong> has expired.<br><br>" +
                    "<small class='text-danger fw-bold'>" +
                    "Coupons are valid for 60 Minutes ONLY." +
                    "</small>",
                confirmButtonColor: "#d33"
            });

        } else {

            alert(
                "This coupon code has expired!"
            );
        }

        return;
    }


    /* =========================================
       APPLY COUPON
    ========================================= */

    appliedDiscountPercent = percent;
    appliedCouponCode = enteredCode;
    couponApplied = true;


    /* =========================================
       CALCULATE DISCOUNT
    ========================================= */

    let originalPrice =
        parseFloat(baseUnitPrice) || 0;


    let discountAmount =
        Math.round(
            originalPrice *
            (percent / 100)
        );


    let finalPrice =
        originalPrice -
        discountAmount;


    /* =========================================
       SAVE INTO HIDDEN FORM FIELDS
    ========================================= */

    let couponHidden =
        document.getElementById(
            "couponCodeHidden"
        );

    let discountPercentHidden =
        document.getElementById(
            "discountPercentHidden"
        );

    let discountAmountHidden =
        document.getElementById(
            "discountAmountHidden"
        );

    let finalPriceHidden =
        document.getElementById(
            "finalPriceHidden"
        );


    if (couponHidden) {
        couponHidden.value =
            enteredCode;
    }


    if (discountPercentHidden) {
        discountPercentHidden.value =
            percent;
    }


    if (discountAmountHidden) {
        discountAmountHidden.value =
            discountAmount;
    }


    if (finalPriceHidden) {
        finalPriceHidden.value =
            finalPrice;
    }


    /* =========================================
       BUTTON & STATUS
    ========================================= */

    if (btn) {
        btn.innerHTML = '<i class="fas fa-check me-1"></i> Applied (' + percent + '% OFF)';
        btn.classList.remove("btn-primary");
        btn.classList.add("btn-success");
        btn.disabled = true;
    }

    if (couponInput) {
        couponInput.disabled = true;
    }

    let appliedStatus = document.getElementById("appliedCouponStatus");
    if (appliedStatus) {
        let codeElem = document.getElementById("appliedCodeText");
        let percentElem = document.getElementById("appliedPercentText");
        if (codeElem) codeElem.innerText = enteredCode;
        if (percentElem) percentElem.innerText = percent;
        appliedStatus.style.setProperty("display", "flex", "important");
    }

    if (tag) {
        tag.style.display = "inline-block";
    }

    /* =========================================
       RECALCULATE EXISTING TOTAL
    ========================================= */

    if (typeof recalculateTotal === "function") {
        recalculateTotal();
    }

    /* =========================================
       SUCCESS MESSAGE WITH CONFETTI
    ========================================= */

    if (typeof Swal === "function") {
        if (typeof confetti === "function") {
            confetti({ particleCount: 100, spread: 70, origin: { y: 0.6 } });
        }

        Swal.fire({
            icon: "success",
            title: "🎉 " + percent + "% Discount Applied!",
            html: '<div style="text-align:center;">' +
                  '<p style="color:#475569; font-size:14px; margin-bottom:12px;">Coupon <strong style="color:#0284c7; background:#e0f2fe; padding:2px 8px; border-radius:6px;">' + enteredCode + '</strong> applied successfully!</p>' +
                  '<div style="background:#f8fafc; border:1.5px solid #e2e8f0; border-radius:14px; padding:12px 16px; text-align:left; font-size:13.5px;">' +
                  '<div style="display:flex; justify-content:space-between; margin-bottom:6px;"><span style="color:#64748b;">Original Price:</span><span style="text-decoration:line-through; color:#94a3b8;">₹ ' + originalPrice.toFixed(2) + '</span></div>' +
                  '<div style="display:flex; justify-content:space-between; margin-bottom:6px;"><span style="color:#059669; font-weight:600;">Promo Discount (' + percent + '% OFF):</span><span style="color:#dc2626; font-weight:700;">- ₹ ' + discountAmount.toFixed(2) + '</span></div>' +
                  '<div style="border-top:1px solid #cbd5e1; margin:6px 0;"></div>' +
                  '<div style="display:flex; justify-content:space-between; align-items:center;"><span style="font-weight:700; color:#0f172a;">Final Amount:</span><span style="font-size:18px; font-weight:800; color:#059669;">₹ ' + finalPrice.toFixed(2) + '</span></div>' +
                  '</div>' +
                  '</div>',
            confirmButtonColor: "#0284c7",
            confirmButtonText: '<i class="fas fa-check me-1"></i> Awesome, Continue'
        });
    }

}

function removeCoupon(showNotification = true) {
    couponApplied = false;
    appliedCouponCode = "";
    appliedDiscountPercent = 0;

    let couponHidden = document.getElementById("couponHidden");
    let discountPercentHidden = document.getElementById("discountPercentHidden");
    let discountAmountHidden = document.getElementById("discountAmountHidden");
    let finalPriceHidden = document.getElementById("finalPriceHidden");

    if (couponHidden) couponHidden.value = "";
    if (discountPercentHidden) discountPercentHidden.value = "0";
    if (discountAmountHidden) discountAmountHidden.value = "0";
    if (finalPriceHidden) finalPriceHidden.value = baseUnitPrice;

    let btn = document.getElementById("applyCouponBtn");
    let couponInput = document.getElementById("couponCodeInput");
    let tag = document.getElementById("discountTag");
    let appliedStatus = document.getElementById("appliedCouponStatus");

    if (btn) {
        btn.innerHTML = "Apply Code";
        btn.classList.remove("btn-success");
        btn.classList.add("btn-primary");
        btn.disabled = false;
    }

    if (couponInput) {
        couponInput.disabled = false;
        couponInput.value = "";
        couponInput.placeholder = "ENTER COUPON CODE";
        couponInput.focus();
    }

    if (appliedStatus) {
        appliedStatus.style.setProperty("display", "none", "important");
    }

    if (tag) {
        tag.style.display = "none";
    }

    recalculateTotal();

    if (showNotification && typeof Swal === "function") {
        Swal.fire({
            icon: "info",
            title: "Coupon Removed",
            text: "Coupon discount removed. Original price has been restored.",
            confirmButtonColor: "#0284c7"
        });
    }
}

let couponTimerInterval = null;

function startCouponCountdown(expiresAt) {
    if(couponTimerInterval) clearInterval(couponTimerInterval);
    let box = document.getElementById("couponCountdownBox");
    let timerText = document.getElementById("couponTimerText");
    if(!box || !timerText) return;

    box.style.display = "block";

    function updateTimer() {
        let now = Math.floor(Date.now() / 1000);
        let remaining = expiresAt - now;

        if(remaining <= 0) {
            clearInterval(couponTimerInterval);
            timerText.innerHTML = "<span class='badge bg-danger ms-1'>🔴 EXPIRED</span>";
            box.classList.remove("alert-warning", "border-warning");
            box.classList.add("alert-danger", "border-danger");

            if (couponApplied) {
                removeCoupon(false);
                if (typeof Swal === "function") {
                    Swal.fire({
                        icon: "warning",
                        title: "Coupon Expired",
                        text: "Your 60-minute mystery coupon code has expired and was removed.",
                        confirmButtonColor: "#ef4444"
                    });
                }
            }
            return;
        }

        let mins = Math.floor(remaining / 60);
        let secs = remaining % 60;
        let minsStr = mins < 10 ? "0" + mins : mins;
        let secsStr = secs < 10 ? "0" + secs : secs;

        timerText.innerText = minsStr + ":" + secsStr + " Mins";
    }

    updateTimer();
    couponTimerInterval = setInterval(updateTimer, 1000);
}

function requestCouponViaAjax() {
    let serviceSelect = document.getElementById("service_type");
    if (!serviceSelect || !serviceSelect.value || typeof baseUnitPrice === "undefined" || baseUnitPrice === 0) {
        if (typeof Swal === "function") {
            Swal.fire({
                icon: "warning",
                title: "Select Service First",
                text: "કૃપા કરીને કૂપન કોડ મેળવતા પહેલા AC Service પસંદ કરો (Please select a Service Type first)!",
                confirmButtonColor: "#0d6efd"
            });
        } else {
            alert("Please select a Service Type before requesting a coupon code!");
        }
        if (serviceSelect) serviceSelect.focus();
        return;
    }

    let btn = document.getElementById("sendCouponBtn");
    let originalHtml = btn ? btn.innerHTML : "Send Code to Email";
    if(btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Sending...';
    }

    let formData = new FormData();
    formData.append('ajax_request_coupon', '1');

    fetch('booking.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(btn) {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }

        if(data && data.success) {
            // Register generated coupon code and expiry in JS validator object
            validUserCoupons[data.code] = {
                percent: data.percent,
                expires_at: data.expires_at
            };

            // Start Live 60-Minute Reverse Countdown Timer on Screen!
            startCouponCountdown(data.expires_at);

            // Enable coupon input for MANUAL entry by user
            let couponInput = document.getElementById("couponCodeInput");
            if(couponInput && !couponApplied) {
                couponInput.value = ""; // Keep empty for manual entry
                couponInput.disabled = false;
                couponInput.placeholder = "ENTER COUPON CODE";
                couponInput.focus();
            }

            let applyBtn = document.getElementById("applyCouponBtn");
            if(applyBtn && !couponApplied) {
                applyBtn.disabled = false;
                applyBtn.title = "Apply entered coupon code";
            }

            if(typeof Swal === 'function') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: '📩 ' + data.percent + '% OFF Coupon Sent!',
                    html: '<span style="font-size:12.5px; color:#475569;">Check your email, enter the code in the box below and click <strong>Apply Code</strong>!</span>',
                    showConfirmButton: false,
                    timer: 5000,
                    timerProgressBar: true
                });
            } else {
                alert(data.message);
            }
        }
    })
    .catch(error => {
        if(btn) {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
        console.error('Coupon request error:', error);
    });
}



function updateBrandInfo() {
    let brandSelect = document.getElementById("acCompany");
    let brandBox = document.getElementById("dynamicBrandBadge");
    let brandText = document.getElementById("selectedBrandText");
    let brandNotice = document.getElementById("partsBrandDynamicNotice");
    let brandBadge = document.getElementById("brandDynamicBadge");
    let brandBadgeText = document.getElementById("brandDynamicBadgeText");
    let partsBox = document.getElementById("partsExperienceBox");
    let activeBarBrandTag = document.getElementById("activeBarBrandTag");

    let brandVal = brandSelect ? brandSelect.value.trim() : "";

    if (brandVal && brandVal !== "None / General Service Only" && brandVal !== "None" && brandVal !== "Other") {
        let cleanBrandName = brandVal.replace(/\s+AC$/i, '');
        if (brandNotice) {
            brandNotice.innerHTML = '<i class="fas fa-snowflake text-primary me-1"></i> Showing 100% Genuine OEM components verified for <strong class="text-primary">' + cleanBrandName + ' AC</strong> • 6M Warranty';
        }
        if (brandBadge && brandBadgeText) {
            brandBadgeText.innerText = cleanBrandName + " Verified";
            brandBadge.style.display = "inline-flex";
        }
        if (activeBarBrandTag) {
            activeBarBrandTag.innerHTML = '<i class="fas fa-check-circle text-primary me-1"></i> ' + cleanBrandName + ' OEM';
        }

        // Dynamically update brand label on all catalog chips
        document.querySelectorAll("[data-part-brand]").forEach(el => {
            el.innerText = cleanBrandName + " OEM";
        });

        if (partsBox) {
            partsBox.classList.add("brand-highlight");
            setTimeout(() => { partsBox.classList.remove("brand-highlight"); }, 600);
        }

        if (brandText) {
            brandText.innerText = cleanBrandName + " Certified Engineers";
        }
        if (brandBox) {
            brandBox.style.display = "flex";
        }
    } else {
        if (brandNotice) {
            brandNotice.innerHTML = '<i class="fas fa-shield-alt text-success me-1"></i> 100% Genuine OEM Factory Components • 6M Warranty';
        }
        if (brandBadge) {
            brandBadge.style.display = "none";
        }
        if (activeBarBrandTag) {
            activeBarBrandTag.innerHTML = '<i class="fas fa-check-circle text-primary me-1"></i> OEM Genuine';
        }
        document.querySelectorAll("[data-part-brand]").forEach(el => {
            el.innerText = "OEM Part";
        });
        if (brandBox) {
            brandBox.style.display = "none";
        }
    }

    // Refresh dynamic side card row if a part is already selected
    recalculateTotal();
}


</script>

<?php if(isset($coupon_sent_msg)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if(typeof Swal === 'function') {
        Swal.fire({
            icon: 'info',
            title: '📩 Mystery Coupon Sent!',
            html: '<?php echo addslashes($coupon_sent_msg); ?>',
            confirmButtonColor: '#0d6efd'
        });
    }
});
</script>
<?php endif; ?>


<?php if(isset($booking_success_flag) && $booking_success_flag): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fire Confetti rain
    if(typeof confetti === 'function') {
        confetti({
            particleCount: 120,
            spread: 80,
            origin: { y: 0.6 }
        });
    }

    // Launch SweetAlert2 Dynamic Booking Success Modal
    Swal.fire({
        title: '<div style="color:#0d6efd; font-weight:800; font-size:24px;"><i class="fas fa-check-circle text-success me-2"></i>Booking Confirmed!</div>',
        html: `
            <div class="text-start p-2" style="font-family: inherit;">
                <p class="text-center text-muted mb-3">
                    Thank you <strong><?php echo htmlspecialchars($booking_success_data['name']); ?></strong>! Your AC service request has been received.
                </p>

                <div class="card border-0 bg-light rounded-4 p-3 mb-3 shadow-sm">
                    <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                        <span class="text-muted"><i class="fas fa-hashtag text-primary me-1"></i> Booking Ref:</span>
                        <strong class="text-primary">#AQUA-<?php echo $booking_success_data['id']; ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted"><i class="fas fa-tools text-primary me-1"></i> Service:</span>
                        <strong class="text-dark"><?php echo htmlspecialchars($booking_success_data['service']); ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted"><i class="fas fa-snowflake text-primary me-1"></i> AC Brand:</span>
                        <strong class="text-dark"><?php echo htmlspecialchars($booking_success_data['company']); ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted"><i class="fas fa-tag text-primary me-1"></i> Estimated Charge:</span>
                        <strong class="text-success fs-5">₹ <?php echo number_format($booking_success_data['price']); ?></strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                        <span class="text-muted"><i class="fas fa-signal text-success me-1"></i> Live Status:</span>
                        <span class="badge bg-success rounded-pill px-3 py-2">🟢 Technician Assigned</span>
                    </div>
                </div>

                <div class="alert alert-info py-2 px-3 small text-center mb-0 rounded-3">
                    <i class="fas fa-truck text-primary me-1"></i> <?php echo htmlspecialchars($b_settings['arrival_guarantee_gu']); ?><br><small class="text-muted"><?php echo htmlspecialchars($b_settings['arrival_guarantee_en']); ?></small>
                </div>
            </div>
        `,
        showCancelButton: true,
        showDenyButton: true,
        confirmButtonColor: '#0d6efd',
        denyButtonColor: '#25d366',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-user-circle me-1"></i> User Dashboard',
        denyButtonText: '<i class="fab fa-whatsapp me-1"></i> WhatsApp Status',
        cancelButtonText: '<i class="fas fa-file-invoice me-1"></i> View Receipt',
        allowOutsideClick: false
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'user_dashboard.php';
        } else if (result.isDenied) {
            window.location.href = 'https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $contact_help['whatsapp']); ?>?text=Hello%20Aqua%20Air%20Cooling,%20I%20have%20booked%20service%20Ref%20AQUA-<?php echo $booking_success_data['id']; ?>';
        } else {
            window.location.href = 'receipt.php?id=<?php echo $booking_success_data['id']; ?>';
        }
    });
});
</script>
<?php endif; ?>