<?php
require_once 'includes/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page_title = 'Portfolio & Our Team | Aqua Air Cooling';

// ── Auto-create & Seed tables if empty ───────────────────────────
$pf_check = mysqli_query($conn, "SHOW TABLES LIKE 'portfolio_items'");
if (mysqli_num_rows($pf_check) == 0) {
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `portfolio_items` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `title` varchar(255) NOT NULL DEFAULT '',
      `description` text DEFAULT NULL,
      `image_path` varchar(500) NOT NULL,
      `category` varchar(100) NOT NULL DEFAULT 'General',
      `display_order` int(11) NOT NULL DEFAULT 0,
      `is_active` tinyint(1) NOT NULL DEFAULT 1,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
}
$pf_cnt_q = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM portfolio_items");
if ($pf_cnt_q && mysqli_fetch_assoc($pf_cnt_q)['cnt'] == 0) {
    mysqli_query($conn, "INSERT INTO `portfolio_items` (`title`, `description`, `category`, `image_path`, `display_order`, `is_active`) VALUES
    ('Split AC Outdoor Installation', 'Precision bracket mounting and vibration-dampened installation for high-rise apartments.', 'Installation', 'img/installation.jpg', 1, 1),
    ('Inverter PCB Diagnostics & Repair', 'Advanced motherboard level circuit testing and original compressor inverter PCB repair.', 'Repair', 'img/repair.jpg', 2, 1),
    ('Deep Jet Foam Cleaning', 'High pressure chemical foam wash for cooling coil, blower wheel and drain tray.', 'Cleaning', 'img/cleaning.jpg', 3, 1),
    ('Seasonal Preventive Maintenance', 'Complete 18-point cooling checkup, electrical terminal tightening and filter sanitization.', 'Maintenance', 'img/maintenance.jpg', 4, 1),
    ('Eco-Friendly Refrigerant Gas Refill', 'Nitrogen pressure leak detection, flare nut seal and pure R32/R410A gas top-up.', 'Gas Filling', 'img/gas.jpeg', 5, 1),
    ('Safe AC Uninstallation & Relocation', 'Professional refrigerant pump down into compressor and damage-free wall bracket removal.', 'Installation', 'img/uninstallation.jpg', 6, 1);");
}

$tm_check = mysqli_query($conn, "SHOW TABLES LIKE 'team_members'");
if (mysqli_num_rows($tm_check) == 0) {
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `team_members` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(255) NOT NULL,
      `designation` varchar(255) NOT NULL DEFAULT '',
      `member_type` enum('malik','manager','karigar') NOT NULL DEFAULT 'karigar',
      `photo_path` varchar(500) DEFAULT NULL,
      `description` text DEFAULT NULL,
      `phone` varchar(50) DEFAULT NULL,
      `display_order` int(11) NOT NULL DEFAULT 0,
      `is_active` tinyint(1) NOT NULL DEFAULT 1,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
}
$tm_cnt_q = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM team_members");
if ($tm_cnt_q && mysqli_fetch_assoc($tm_cnt_q)['cnt'] == 0) {
    mysqli_query($conn, "INSERT INTO `team_members` (`name`, `designation`, `member_type`, `photo_path`, `description`, `phone`, `display_order`, `is_active`) VALUES
    ('Mohit Patel', 'Founder & Master HVAC Specialist', 'malik', 'img/about-2.jpg', 'Over 8+ years of expertise in high-efficiency cooling, VRF and residential AC systems.', '+91 6354911971', 1, 1),
    ('Rajesh Sharma', 'Service & Operations Manager', 'manager', 'img/about-4.jpg', 'Manages fast technician dispatch, transparent billing and prompt customer satisfaction.', '+91 9876543210', 2, 1),
    ('Suresh Kumar', 'Senior AC Repair Technician', 'karigar', 'img/about-1.jpg', 'Expert in inverter PCB troubleshooting, compressor replacements and copper brazing.', '+91 9823456781', 3, 1),
    ('Amit Verma', 'Installation & Jet Cleaning Specialist', 'karigar', 'img/repair.jpg', 'Certified technician specializing in aesthetic indoor piping and deep jet servicing.', '+91 9812345672', 4, 1);");
}

// ── Fetch portfolio items ─────────────────────────────────────
$portfolio_items = [];
$portfolio_q = mysqli_query($conn, "SELECT * FROM portfolio_items WHERE is_active=1 ORDER BY display_order ASC, id DESC");
if ($portfolio_q) while ($r = mysqli_fetch_assoc($portfolio_q)) $portfolio_items[] = $r;

// Distinct categories with dynamic item counts
$categories = ['All'];
$category_counts = ['All' => count($portfolio_items)];
foreach ($portfolio_items as $item) {
    $c = trim($item['category']);
    if (!in_array($c, $categories)) $categories[] = $c;
    $category_counts[$c] = ($category_counts[$c] ?? 0) + 1;
}

// ── Fetch team members grouped by type ────────────────────────
$team = ['malik' => [], 'manager' => [], 'karigar' => []];
$all_team_members = [];
$team_q = mysqli_query($conn, "SELECT * FROM team_members WHERE is_active=1 ORDER BY display_order ASC, id ASC");
if ($team_q) {
    while ($r = mysqli_fetch_assoc($team_q)) {
        $team[$r['member_type']][] = $r;
        $all_team_members[] = $r;
    }
}

$member_types = [
    'malik'   => ['label' => 'Owner',           'icon' => 'fa-crown',    'color' => '#7c3aed', 'bg' => 'rgba(124,58,237,.08)'],
    'manager' => ['label' => 'Service Manager', 'icon' => 'fa-user-tie', 'color' => '#0284c7', 'bg' => 'rgba(2,132,199,.08)'],
    'karigar' => ['label' => 'Employee',        'icon' => 'fa-tools',    'color' => '#059669', 'bg' => 'rgba(5,150,105,.08)'],
];

// ── Fetch active services for matching ────────────────────────
$active_services_list = [];
$srv_q = mysqli_query($conn, "SELECT name, price FROM services WHERE is_active=1 ORDER BY display_order ASC, id ASC");
if ($srv_q) {
    while ($srow = mysqli_fetch_assoc($srv_q)) {
        $active_services_list[] = $srow;
    }
}

function matchServiceForPortfolio($title, $category, $active_services_list) {
    $text = strtoupper($title . ' ' . $category);
    foreach ($active_services_list as $srv) {
        $sname = $srv['name'];
        $sUpper = strtoupper($sname);
        if (strpos($text, 'CLEAN') !== false && (strpos($sUpper, 'CLEAN') !== false || strpos($sUpper, 'WASH') !== false)) {
            return $sname;
        }
        if (strpos($text, 'GAS') !== false && (strpos($sUpper, 'GAS') !== false || strpos($sUpper, 'REFILL') !== false)) {
            return $sname;
        }
        if (strpos($text, 'UNINSTALL') !== false && strpos($sUpper, 'UNINSTALL') !== false) {
            return $sname;
        }
        if (strpos($text, 'INSTALL') !== false && strpos($sUpper, 'INSTALL') !== false) {
            return $sname;
        }
        if (strpos($text, 'MAINTEN') !== false && strpos($sUpper, 'MAINTEN') !== false) {
            return $sname;
        }
        if (strpos($text, 'REPAIR') !== false && strpos($sUpper, 'REPAIR') !== false) {
            return $sname;
        }
    }
    return !empty($active_services_list) ? $active_services_list[0]['name'] : 'AC Repair';
}

// ── Dynamic Live Stats ─────────────────────────────────────────
$total_bookings_count = 5420;
$b_cnt_q = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM bookings");
if ($b_cnt_q && $b_row = mysqli_fetch_assoc($b_cnt_q)) {
    $total_bookings_count += (int)$b_row['cnt'];
}
$total_team_count = count($all_team_members);

// ── Dynamic Contact Info ──────────────────────────────────────
$contact_phone = '+91 6354911971';
$contact_whatsapp = '916354911971';
$c_info_q = mysqli_query($conn, "SELECT phone, whatsapp_number FROM contact_info WHERE id = 1");
if ($c_info_q && $ci_row = mysqli_fetch_assoc($c_info_q)) {
    if (!empty($ci_row['phone'])) $contact_phone = $ci_row['phone'];
    if (!empty($ci_row['whatsapp_number'])) $contact_whatsapp = $ci_row['whatsapp_number'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <meta name="description" content="View real AC service work gallery and meet Mohit Patel & expert team at Aqua Air Cooling.">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@400;600;800&family=Roboto:wght@400;500;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"> 

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
    <style>
    * { box-sizing: border-box; }
    body { font-family: 'Inter', sans-serif; background: #f8fafc; color: #1e293b; }

    /* ─── HERO ─────────────────────────────────────────────── */
    .portfolio-hero {
        background: linear-gradient(135deg, #071033 0%, #0369a1 50%, #0284c7 100%);
        padding: 95px 0 75px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .portfolio-hero::before {
        content: '';
        position: absolute; inset: 0;
        background: radial-gradient(circle at 20% 30%, rgba(56, 189, 248, 0.25) 0%, transparent 50%);
        pointer-events: none;
    }
    .portfolio-hero h1 { font-size: clamp(2.1rem, 5vw, 3.4rem); font-weight: 800; color: #fff; position: relative; }
    .portfolio-hero p { color: rgba(255,255,255,.9); font-size: 1.15rem; max-width: 620px; margin: 0 auto; position: relative; }

    /* ─── LIVE STATS BAR ─────────────────────────────────────── */
    .stats-bar-card {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        border: 1px solid #e2e8f0;
        margin-top: -45px;
        position: relative;
        z-index: 10;
        padding: 22px 24px;
    }
    .stat-item {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 8px 12px;
    }
    .stat-icon-box {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        background: #e0f2fe;
        color: #0284c7;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
    }
    .stat-number {
        font-size: 1.4rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
    }
    .stat-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #64748b;
    }

    /* ─── SEARCH & FILTER TABS ───────────────────────────────── */
    .portfolio-controls {
        padding: 40px 0 20px;
    }
    .filter-tabs { display: flex; flex-wrap: wrap; gap: 8px; justify-content: center; margin-bottom: 20px; }
    .filter-tab {
        padding: 8px 20px; border-radius: 50px; border: 1.5px solid #e2e8f0;
        background: #fff; cursor: pointer; font-weight: 700; font-size: 13.5px;
        color: #475569; transition: all .2s cubic-bezier(0.16, 1, 0.3, 1);
        display: inline-flex; align-items: center; gap: 6px;
    }
    .filter-tab:hover {
        border-color: #0284c7; color: #0284c7; background: #f0f9ff;
    }
    .filter-tab.active {
        background: #0284c7; border-color: #0284c7; color: #fff;
        box-shadow: 0 4px 14px rgba(2,132,199,.35);
    }
    .filter-tab .badge {
        font-size: 11px;
        font-weight: 700;
    }
    .filter-tab.active .badge {
        background: rgba(255,255,255,0.25) !important;
        color: #ffffff !important;
    }

    .portfolio-search-wrap {
        max-width: 440px;
        margin: 0 auto 20px;
        position: relative;
    }
    .portfolio-search-input {
        border-radius: 50px;
        padding: 12px 20px 12px 44px;
        border: 1.5px solid #cbd5e1;
        font-size: 14px;
        box-shadow: 0 3px 12px rgba(0,0,0,0.03);
        transition: all 0.2s ease;
    }
    .portfolio-search-input:focus {
        border-color: #0284c7;
        box-shadow: 0 4px 16px rgba(2, 132, 199, 0.18);
    }
    .portfolio-search-icon {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
    }

    /* ─── PORTFOLIO GRID ─────────────────────────────────────── */
    .portfolio-section { padding: 0 0 70px; }
    .portfolio-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
        gap: 24px;
    }
    .portfolio-card-item {
        border-radius: 18px; overflow: hidden; background: #fff;
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.06);
        border: 1px solid #f1f5f9;
        transition: all .3s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        flex-direction: column;
    }
    .portfolio-card-item:hover {
        transform: translateY(-7px);
        box-shadow: 0 16px 36px rgba(2,132,199,.15);
        border-color: #bae6fd;
    }
    .portfolio-img-wrap { position: relative; overflow: hidden; height: 215px; cursor: pointer; }
    .portfolio-img-wrap img { width: 100%; height: 100%; object-fit: cover; transition: transform .4s ease; }
    .portfolio-card-item:hover .portfolio-img-wrap img { transform: scale(1.08); }
    .portfolio-overlay {
        position: absolute; inset: 0;
        background: linear-gradient(0deg, rgba(7,16,51,.85) 0%, rgba(7,16,51,0.2) 60%, transparent 100%);
        opacity: 0; transition: opacity .3s; display: flex; align-items: center; justify-content: center;
        gap: 12px;
    }
    .portfolio-card-item:hover .portfolio-overlay { opacity: 1; }
    .overlay-action-btn {
        width: 44px; height: 44px; border-radius: 50%; background: #ffffff; color: #0284c7;
        display: flex; align-items: center; justify-content: center; font-size: 16px;
        box-shadow: 0 4px 14px rgba(0,0,0,0.3); transition: transform 0.2s ease;
    }
    .overlay-action-btn:hover { transform: scale(1.12); color: #0369a1; }
    .portfolio-cat-badge {
        position: absolute; top: 12px; left: 12px;
        background: rgba(2,132,199,.92); backdrop-filter: blur(4px); color: #fff;
        font-size: 11.5px; font-weight: 700; padding: 4px 12px; border-radius: 50px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.15);
    }
    .portfolio-body { padding: 18px 20px; flex-grow: 1; display: flex; flex-direction: column; }
    .portfolio-title { font-weight: 800; color: #0f172a; font-size: 16px; margin-bottom: 6px; line-height: 1.35; }
    .portfolio-desc { color: #64748b; font-size: 13px; line-height: 1.55; margin-bottom: 14px; flex-grow: 1; }
    .portfolio-card-footer {
        padding-top: 12px;
        border-top: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }

    /* ─── LIGHTBOX ───────────────────────────────────────────── */
    .lightbox-overlay {
        position: fixed; inset: 0; background: rgba(0,0,0,.92);
        z-index: 9999; display: none; align-items: center; justify-content: center;
        padding: 20px;
    }
    .lightbox-overlay.active { display: flex; }
    .lightbox-inner { position: relative; max-width: 850px; width: 100%; background: #0f172a; border-radius: 16px; overflow: hidden; }
    .lightbox-inner img { width: 100%; max-height: 65vh; object-fit: contain; background: #000; }
    .lightbox-close {
        position: absolute; top: 14px; right: 16px;
        background: rgba(255,255,255,.2); border: none; color: #fff; width: 38px; height: 38px;
        border-radius: 50%; font-size: 18px; cursor: pointer; z-index: 10;
        display: flex; align-items: center; justify-content: center; transition: all 0.2s ease;
    }
    .lightbox-close:hover { background: #ef4444; color: #fff; }
    .lightbox-content-box { padding: 18px 22px; color: #fff; display: flex; align-items: center; justify-content: space-between; flex-wrap: gap; gap: 16px; background: #0f172a; }

    /* ─── DYNAMIC TEAM SECTION ───────────────────────────────── */
    .team-section { padding: 80px 0 90px; background: #ffffff; border-top: 1px solid #e2e8f0; }
    .section-title { text-align: center; margin-bottom: 35px; }
    .section-title h2 { font-size: clamp(1.8rem,4vw,2.6rem); font-weight: 800; color: #0f172a; }
    .section-title p { color: #64748b; font-size: 1.05rem; max-width: 580px; margin: 10px auto 0; }

    .team-filter-tabs { display: flex; flex-wrap: wrap; gap: 8px; justify-content: center; margin-bottom: 35px; }
    .team-filter-tab {
        padding: 8px 20px; border-radius: 50px; border: 1.5px solid #e2e8f0;
        background: #fff; cursor: pointer; font-weight: 700; font-size: 13.5px;
        color: #475569; transition: all .2s ease; display: inline-flex; align-items: center; gap: 6px;
    }
    .team-filter-tab:hover {
        border-color: #0284c7; color: #0284c7; background: #f0f9ff;
    }
    .team-filter-tab.active {
        background: #0284c7; border-color: #0284c7; color: #fff;
        box-shadow: 0 4px 14px rgba(2,132,199,.3);
    }
    .team-filter-tab.active .badge {
        background: rgba(255,255,255,0.25) !important;
        color: #fff !important;
    }

    .team-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(265px, 1fr));
        gap: 26px;
    }

    .team-card {
        border-radius: 22px; background: #fff; text-align: center; padding: 32px 22px 24px;
        box-shadow: 0 6px 24px rgba(15, 23, 42, 0.05); transition: all .3s cubic-bezier(0.16, 1, 0.3, 1);
        border: 1.5px solid #f1f5f9; position: relative; display: flex; flex-direction: column;
    }
    .team-card:hover { transform: translateY(-7px); box-shadow: 0 16px 36px rgba(0,0,0,.1); border-color: #cbd5e1; }

    .team-card.founder-card {
        border: 2px solid #a855f7;
        background: linear-gradient(180deg, #faf5ff 0%, #ffffff 100%);
        box-shadow: 0 8px 28px rgba(168, 85, 247, 0.12);
    }
    .founder-badge-top {
        position: absolute;
        top: -12px;
        left: 50%;
        transform: translateX(-50%);
        background: linear-gradient(135deg, #7c3aed 0%, #9333ea 100%);
        color: #fff;
        font-size: 11px;
        font-weight: 800;
        padding: 3px 14px;
        border-radius: 50px;
        box-shadow: 0 3px 10px rgba(124, 58, 237, 0.35);
        white-space: nowrap;
    }

    .team-avatar-wrap { position: relative; display: inline-block; margin-bottom: 16px; margin-top: 4px; }
    .team-avatar {
        width: 110px; height: 110px; border-radius: 50%;
        object-fit: cover; border: 4px solid #fff;
        box-shadow: 0 8px 22px rgba(0,0,0,.12);
    }
    .team-avatar-fallback {
        width: 110px; height: 110px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 40px; font-weight: 800; color: #fff; border: 4px solid #fff;
        box-shadow: 0 8px 22px rgba(0,0,0,.15); margin: 0 auto;
    }
    .team-type-pill {
        position: absolute; bottom: -6px; left: 50%; transform: translateX(-50%);
        font-size: 10.5px; font-weight: 800; padding: 2.5px 11px; border-radius: 50px;
        white-space: nowrap; color: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.18);
    }
    .team-name { font-weight: 800; color: #0f172a; font-size: 17.5px; margin-bottom: 4px; }
    .team-designation { font-weight: 700; font-size: 13px; margin-bottom: 8px; }
    .team-meta-badges {
        display: flex;
        justify-content: center;
        gap: 6px;
        margin-bottom: 12px;
        flex-wrap: wrap;
    }
    .team-status-tag {
        font-size: 10.5px;
        font-weight: 700;
        color: #16a34a;
        background: #dcfce7;
        padding: 2px 8px;
        border-radius: 50px;
    }
    .team-rating-tag {
        font-size: 10.5px;
        font-weight: 700;
        color: #d97706;
        background: #fef3c7;
        padding: 2px 8px;
        border-radius: 50px;
    }
    .team-bio { color: #64748b; font-size: 12.5px; line-height: 1.55; margin-bottom: 16px; flex-grow: 1; }
    .team-action-buttons {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding-top: 12px;
        border-top: 1px solid #f1f5f9;
    }
    .team-btn-call {
        background: #f1f5f9;
        color: #0f172a;
        font-size: 12px;
        font-weight: 700;
        padding: 7px 14px;
        border-radius: 50px;
        text-decoration: none;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
    }
    .team-btn-call:hover {
        background: #0284c7;
        color: #fff;
    }
    .team-btn-wa {
        background: #25d366;
        color: #fff;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        text-decoration: none;
        transition: transform 0.2s ease;
    }
    .team-btn-wa:hover {
        transform: scale(1.12);
        color: #fff;
    }

    /* ─── EMPTY STATE ─────────────────────────────────────────── */
    .empty-state { text-align: center; padding: 60px 20px; color: #94a3b8; }
    .empty-state i { font-size: 48px; margin-bottom: 14px; display: block; opacity: .35; }

    /* ─── RESPONSIVE ─────────────────────────────────────────── */
    @media (max-width: 768px) {
        .stat-item { border-bottom: 1px solid #f1f5f9; padding-bottom: 14px; }
        .stat-item:last-child { border-bottom: none; }
        .portfolio-hero { padding: 50px 0 45px; }
        .stats-bar-card { margin-top: -25px; padding: 16px; border-radius: 16px; }
        .portfolio-search-wrap { max-width: 100%; margin: 0 10px 20px; }
        .portfolio-search-input { font-size: 16px; }
    }
    @media (max-width: 576px) {
        .portfolio-grid { grid-template-columns: 1fr; gap: 18px; }
        .team-grid { grid-template-columns: 1fr; }
        .filter-tabs { justify-content: flex-start; overflow-x: auto; flex-wrap: nowrap; padding-bottom: 8px; -webkit-overflow-scrolling: touch; }
        .filter-tab { flex-shrink: 0; }
        .team-filter-tabs { justify-content: flex-start; overflow-x: auto; flex-wrap: nowrap; padding-bottom: 8px; -webkit-overflow-scrolling: touch; }
        .team-filter-tab { flex-shrink: 0; }
    }
    </style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>
<?php include 'includes/topbar.php'; ?>

<!-- ═══════════════ HERO ═══════════════ -->
<section class="portfolio-hero">
    <div class="container">
        <div class="d-inline-flex align-items-center gap-2 mb-3 px-4 py-2 rounded-pill text-white fw-bold"
             style="background:rgba(255,255,255,.14);font-size:13px;backdrop-filter:blur(6px);">
            <i class="fas fa-snowflake text-warning"></i> Certified HVAC Work Gallery & Team
        </div>
        <h1>Our AC Work Portfolio & Team</h1>
        <p class="mt-3">
            Real AC installation, PCB circuit repairs, gas charging, and deep foam jet servicing executed by <strong>Mohit Patel & Aqua Air Cooling Team</strong>.
        </p>
    </div>
</section>

<!-- ═══════════════ DYNAMIC LIVE STATS BAR ═══════════════ -->
<div class="container">
    <div class="stats-bar-card">
        <div class="row g-3">
            <div class="col-md-3 col-6">
                <div class="stat-item">
                    <div class="stat-icon-box">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div>
                        <div class="stat-number"><?= number_format($total_bookings_count) ?>+</div>
                        <div class="stat-label">Happy Customers Serviced</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-item">
                    <div class="stat-icon-box bg-success-subtle text-success">
                        <i class="fas fa-camera-retro"></i>
                    </div>
                    <div>
                        <div class="stat-number"><?= count($portfolio_items) ?>+ Projects</div>
                        <div class="stat-label">Live Work Portfolio</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-item">
                    <div class="stat-icon-box bg-purple-subtle" style="background:#f3e8ff;color:#7c3aed;">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div>
                        <div class="stat-number"><?= $total_team_count ?> Specialists</div>
                        <div class="stat-label">Certified Employee Team</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-item">
                    <div class="stat-icon-box bg-warning-subtle text-warning">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div>
                        <div class="stat-number">100% OEM</div>
                        <div class="stat-label">Original Parts & Warranty</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════ PORTFOLIO SECTION ═══════════════ -->
<section class="portfolio-section">
    <div class="container">

        <!-- Controls: Search & Filter Tabs -->
        <div class="portfolio-controls text-center">
            <!-- Live Search Bar -->
            <div class="portfolio-search-wrap">
                <i class="fas fa-search portfolio-search-icon"></i>
                <input type="text" class="form-control portfolio-search-input" id="portfolioSearchBox"
                       placeholder="🔍 Search work by keyword (e.g. PCB, Split, Gas, Jet, Wash)..."
                       oninput="filterPortfolio()">
            </div>

            <!-- Filter Tabs with Dynamic Counts -->
            <?php if (count($categories) > 1): ?>
            <div class="filter-tabs" id="filterTabs">
                <button class="filter-tab active" data-filter="all">
                    All Work <span class="badge bg-light text-dark rounded-pill ms-1"><?= $category_counts['All'] ?></span>
                </button>
                <?php foreach ($categories as $cat): if ($cat === 'All') continue; ?>
                <button class="filter-tab" data-filter="<?= htmlspecialchars($cat) ?>">
                    <?= htmlspecialchars($cat) ?>
                    <span class="badge bg-light text-muted rounded-pill ms-1"><?= $category_counts[$cat] ?? 0 ?></span>
                </button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Grid -->
        <?php if (empty($portfolio_items)): ?>
        <div class="empty-state">
            <i class="fas fa-images"></i>
            <h5>Portfolio coming soon!</h5>
            <p>We are adding our work photos. Check back soon.</p>
        </div>
        <?php else: ?>
        <div class="portfolio-grid" id="portfolioGrid">
            <?php foreach ($portfolio_items as $item): ?>
            <div class="portfolio-card-item"
                 data-cat="<?= htmlspecialchars($item['category']) ?>"
                 data-search="<?= htmlspecialchars(strtolower($item['title'] . ' ' . $item['category'] . ' ' . $item['description'])) ?>">
                
                <div class="portfolio-img-wrap"
                     onclick="openLightbox('<?= htmlspecialchars($item['image_path']) ?>', '<?= htmlspecialchars(addslashes($item['title'])) ?>', '<?= htmlspecialchars(addslashes($item['description'] ?? '')) ?>', '<?= htmlspecialchars(addslashes($item['category'])) ?>')">
                    <img src="<?= htmlspecialchars($item['image_path']) ?>"
                         alt="<?= htmlspecialchars($item['title']) ?>"
                         loading="lazy"
                         onerror="this.src='img/feature.jpg'">
                    <span class="portfolio-cat-badge"><?= htmlspecialchars($item['category']) ?></span>
                    <div class="portfolio-overlay">
                        <span class="overlay-action-btn" title="Zoom Photo">
                            <i class="fas fa-expand-alt"></i>
                        </span>
                    </div>
                </div>

                <div class="portfolio-body">
                    <?php if ($item['title']): ?>
                    <div class="portfolio-title"><?= htmlspecialchars($item['title']) ?></div>
                    <?php endif; ?>

                    <?php if ($item['description']): ?>
                    <div class="portfolio-desc"><?= htmlspecialchars($item['description']) ?></div>
                    <?php endif; ?>

                    <div class="portfolio-card-footer">
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-11">
                            <i class="fas fa-check-circle me-1"></i> Verified AC Job
                        </span>
                        <span class="badge bg-light text-muted border rounded-pill font-11">
                            <i class="fas fa-shield-alt text-primary me-1"></i> OEM Standard
                        </span>
                    </div>
                </div>

            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center py-5 text-muted" id="portfolioNoResults" style="display: none;">
            <i class="fas fa-search fa-3x mb-3 opacity-25"></i>
            <h5>No work found matching your search.</h5>
            <p>Try searching for a different keyword like "Installation", "Gas", or "Cleaning".</p>
        </div>
        <?php endif; ?>

    </div>
</section>

<!-- ═══════════════ LIGHTBOX MODAL ═══════════════ -->
<div class="lightbox-overlay" id="lightboxOverlay" onclick="closeLightbox()">
    <button class="lightbox-close" onclick="closeLightbox()"><i class="fas fa-times"></i></button>
    <div class="lightbox-inner" onclick="event.stopPropagation()">
        <img id="lightboxImg" src="" alt="">
        <div class="lightbox-content-box">
            <div>
                <span class="badge bg-primary rounded-pill mb-1 font-11" id="lightboxCat">Category</span>
                <h5 class="mb-1 text-white fw-bold" id="lightboxTitle">Project Title</h5>
                <small class="text-light opacity-75" id="lightboxDesc">Project description</small>
            </div>
            <div>
                <a id="lightboxWaBtn" href="https://wa.me/<?= preg_replace('/[^0-9]/','',$contact_whatsapp) ?>" target="_blank" class="btn btn-success rounded-pill px-3 py-2 font-13 fw-bold text-nowrap">
                    <i class="fab fa-whatsapp me-1"></i> WhatsApp Inquiry
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════ DYNAMIC OUR TEAM SECTION ═══════════════ -->
<section class="team-section" id="team">
    <div class="container">

        <div class="section-title">
            <span class="badge px-3 py-2 rounded-pill mb-3 fw-bold"
                  style="background:#dbeafe;color:#1d4ed8;font-size:13px;">
                <i class="fas fa-users me-1"></i> Certified AC Specialists
            </span>
            <h2>Meet Our Expert Team</h2>
            <p>Experienced HVAC engineers, certified employees, and service managers ready for fast doorstep AC care.</p>
        </div>

        <!-- Dynamic Team Controls: Search & Category Tabs -->
        <div class="text-center mb-4">
            <!-- Search Team -->
            <div class="portfolio-search-wrap mb-3">
                <i class="fas fa-search portfolio-search-icon"></i>
                <input type="text" class="form-control portfolio-search-input" id="teamSearchBox"
                       placeholder="🔍 Search technician by name, role, or specialty..."
                       oninput="filterTeamMembers()">
            </div>

            <!-- Team Filter Tabs -->
            <div class="team-filter-tabs" id="teamFilterTabs">
                <button class="team-filter-tab active" data-type="all">
                    All Team Members <span class="badge bg-light text-dark rounded-pill ms-1"><?= $total_team_count ?></span>
                </button>
                <button class="team-filter-tab" data-type="malik">
                    👑 Owner <span class="badge bg-light text-muted rounded-pill ms-1"><?= count($team['malik']) ?></span>
                </button>
                <button class="team-filter-tab" data-type="manager">
                    👔 Service Managers <span class="badge bg-light text-muted rounded-pill ms-1"><?= count($team['manager']) ?></span>
                </button>
                <button class="team-filter-tab" data-type="karigar">
                    🔧 Employees <span class="badge bg-light text-muted rounded-pill ms-1"><?= count($team['karigar']) ?></span>
                </button>
            </div>
        </div>

        <?php if (empty($all_team_members)): ?>
        <div class="empty-state">
            <i class="fas fa-user-friends"></i>
            <h5>Team profiles coming soon!</h5>
            <p>We are updating our technician roster.</p>
        </div>
        <?php else: ?>

        <!-- Team Grid -->
        <div class="team-grid" id="teamGrid">
            <?php foreach ($all_team_members as $member): 
                $m_type = $member['member_type'];
                $t_info = $member_types[$m_type] ?? [
                    'label' => ucfirst($m_type),
                    'icon'  => 'fa-user',
                    'color' => '#0284c7'
                ];
                $is_malik = ($m_type === 'malik');
                $phone_clean = preg_replace('/[^0-9]/', '', $member['phone'] ?: $contact_phone);
                $search_text = strtolower($member['name'] . ' ' . $member['designation'] . ' ' . $t_info['label'] . ' ' . $member['description']);
            ?>
            <div class="team-card-wrapper"
                 data-type="<?= htmlspecialchars($m_type) ?>"
                 data-search="<?= htmlspecialchars($search_text) ?>">

                <div class="team-card <?= $is_malik ? 'founder-card' : '' ?> h-100">
                    <?php if ($is_malik): ?>
                    <div class="founder-badge-top">
                        <i class="fas fa-crown text-warning me-1"></i> Owner & Master Specialist
                    </div>
                    <?php endif; ?>

                    <div class="team-avatar-wrap">
                        <?php if (!empty($member['photo_path']) && file_exists($member['photo_path'])): ?>
                            <img src="<?= htmlspecialchars($member['photo_path']) ?>"
                                 class="team-avatar" alt="<?= htmlspecialchars($member['name']) ?>">
                        <?php else: ?>
                            <div class="team-avatar-fallback"
                                 style="background:linear-gradient(135deg,<?= $t_info['color'] ?>,<?= $t_info['color'] ?>99);">
                                <?= strtoupper(substr($member['name'],0,1)) ?>
                            </div>
                        <?php endif; ?>
                        <span class="team-type-pill" style="background:<?= $t_info['color'] ?>;">
                            <i class="fas <?= $t_info['icon'] ?> me-1"></i><?= $t_info['label'] ?>
                        </span>
                    </div>

                    <div class="team-name"><?= htmlspecialchars($member['name']) ?></div>

                    <?php if ($member['designation']): ?>
                    <div class="team-designation" style="color:<?= $t_info['color'] ?>;">
                        <?= htmlspecialchars($member['designation']) ?>
                    </div>
                    <?php endif; ?>

                    <div class="team-meta-badges">
                        <span class="team-status-tag">
                            <i class="fas fa-circle font-9 me-1"></i> Available Today
                        </span>
                        <span class="team-rating-tag">
                            <i class="fas fa-star text-warning me-1"></i> 4.9 Rating
                        </span>
                    </div>

                    <?php if ($member['description']): ?>
                    <div class="team-bio"><?= htmlspecialchars($member['description']) ?></div>
                    <?php endif; ?>

                    <div class="team-action-buttons mt-auto">
                        <?php if ($member['phone']): ?>
                        <a href="tel:<?= preg_replace('/[^0-9+]/', '', $member['phone']) ?>"
                           class="team-btn-call" title="Call directly">
                            <i class="fas fa-phone-alt me-1.5 text-primary"></i> <?= htmlspecialchars($member['phone']) ?>
                        </a>
                        <a href="https://wa.me/<?= $phone_clean ?>?text=Hello%20<?= urlencode($member['name']) ?>,%20I%20saw%20your%20profile%20on%20Aqua%20Air%20Cooling%20and%20want%20to%20consult%20for%20AC%20service"
                           target="_blank" class="team-btn-wa" title="WhatsApp Chat">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <?php endif; ?>
                    </div>

                </div>

            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center py-5 text-muted" id="teamNoResults" style="display: none;">
            <i class="fas fa-user-slash fa-3x mb-3 opacity-25"></i>
            <h5>No technicians match your search.</h5>
            <p>Try searching for a different role or name.</p>
        </div>

        <?php endif; ?>

    </div>
</section>

<!-- ═══════════════ DIRECT BOOKING CTA BANNER ═══════════════ -->
<section class="py-5" style="background: #f1f5f9;">
    <div class="container">
        <div class="p-5 text-center text-white rounded-4 shadow-lg position-relative overflow-hidden"
             style="background: linear-gradient(135deg, #071033 0%, #0369a1 100%);">
            <h2 class="text-white fw-bold mb-3">Ready for Professional AC Service?</h2>
            <p class="text-light opacity-90 mx-auto mb-4" style="max-width: 600px; font-size: 15px;">
                Contact Mohit Patel & certified expert technicians for 60-minute doorstep AC installation, deep jet wash, or PCB repair with 100% genuine parts warranty.
            </p>
            <div class="d-flex justify-content-center align-items-center gap-3 flex-wrap">
                <a href="https://wa.me/<?= preg_replace('/[^0-9]/','',$contact_whatsapp) ?>?text=Hello%20Aqua%20Air%20Cooling,%20I%20want%20to%20consult%20for%20AC%20service"
                   target="_blank" class="btn btn-success btn-lg rounded-pill px-4 py-2.5 fw-bold shadow">
                    <i class="fab fa-whatsapp me-2"></i> Chat on WhatsApp
                </a>
                <a href="tel:<?= preg_replace('/[^0-9+]/','',$contact_phone) ?>" class="btn btn-outline-light btn-lg rounded-pill px-4 py-2.5 fw-bold">
                    <i class="fas fa-phone-alt me-2"></i> Call: <?= htmlspecialchars($contact_phone) ?>
                </a>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>

<script>
// ── Portfolio Live Filtering & Search ────────────────────────
let currentCategory = 'all';

document.querySelectorAll('.filter-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        currentCategory = this.dataset.filter;
        filterPortfolio();
    });
});

function filterPortfolio() {
    const searchVal = (document.getElementById('portfolioSearchBox')?.value || '').toLowerCase().trim();
    let visibleCount = 0;

    document.querySelectorAll('.portfolio-card-item').forEach(item => {
        const itemCat = item.dataset.cat;
        const itemSearch = item.dataset.search || '';

        const matchesCat = (currentCategory === 'all' || itemCat === currentCategory);
        const matchesSearch = (!searchVal || itemSearch.includes(searchVal));

        if (matchesCat && matchesSearch) {
            item.style.display = 'flex';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });

    const noRes = document.getElementById('portfolioNoResults');
    if (noRes) {
        noRes.style.display = visibleCount === 0 ? 'block' : 'none';
    }
}

// ── Team Members Live Filtering & Search ─────────────────────
let currentTeamType = 'all';

document.querySelectorAll('.team-filter-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.team-filter-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        currentTeamType = this.dataset.type;
        filterTeamMembers();
    });
});

function filterTeamMembers() {
    const searchVal = (document.getElementById('teamSearchBox')?.value || '').toLowerCase().trim();
    let visibleCount = 0;

    document.querySelectorAll('.team-card-wrapper').forEach(wrapper => {
        const type = wrapper.dataset.type;
        const searchData = wrapper.dataset.search || '';

        const matchesType = (currentTeamType === 'all' || type === currentTeamType);
        const matchesSearch = (!searchVal || searchData.includes(searchVal));

        if (matchesType && matchesSearch) {
            wrapper.style.display = 'block';
            visibleCount++;
        } else {
            wrapper.style.display = 'none';
        }
    });

    const noRes = document.getElementById('teamNoResults');
    if (noRes) {
        noRes.style.display = visibleCount === 0 ? 'block' : 'none';
    }
}

// ── Lightbox ─────────────────────────────────────────────────
function openLightbox(src, title, desc, cat) {
    document.getElementById('lightboxImg').src = src;
    document.getElementById('lightboxTitle').textContent = title || 'AC Service Project';
    document.getElementById('lightboxDesc').textContent = desc || 'Quality AC service completed by Aqua Air Cooling expert team.';
    document.getElementById('lightboxCat').textContent = cat || 'General';
    document.getElementById('lightboxOverlay').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    document.getElementById('lightboxOverlay').classList.remove('active');
    document.getElementById('lightboxImg').src = '';
    document.body.style.overflow = '';
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
