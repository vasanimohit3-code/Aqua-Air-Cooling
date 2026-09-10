<?php
require_once __DIR__ . '/includes/auth.php';
requireAdminLogin();

$page_title = 'Booking Form, Brands & Parts';
$active_page = 'booking_form_settings';

$msg = '';
$msg_type = '';

// 1. Ensure `ac_brands` table exists & has default 16 brands
$table_check1 = mysqli_query($conn, "SHOW TABLES LIKE 'ac_brands'");
if (!$table_check1 || mysqli_num_rows($table_check1) == 0) {
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `ac_brands` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(100) NOT NULL,
      `is_active` tinyint(1) NOT NULL DEFAULT 1,
      `display_order` int(11) NOT NULL DEFAULT 0,
      PRIMARY KEY (`id`),
      UNIQUE KEY `name` (`name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
}

$default_brands = [
    'Voltas', 'Daikin', 'LG', 'Samsung', 'Blue Star', 'Hitachi', 
    'Carrier', 'Panasonic', 'Lloyd', 'Godrej', 'Haier', 
    'Mitsubishi Electric', 'O General', 'IFB', 'Whirlpool', 'Other'
];

$count_check1 = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM ac_brands");
$count_row1 = ($count_check1 && mysqli_num_rows($count_check1) > 0) ? mysqli_fetch_assoc($count_check1) : ['cnt' => 0];
if (empty($count_row1['cnt'])) {
    $order = 1;
    foreach ($default_brands as $b) {
        mysqli_query($conn, "INSERT IGNORE INTO `ac_brands` (`name`, `is_active`, `display_order`) VALUES ('" . mysqli_real_escape_string($conn, $b) . "', 1, $order)");
        $order++;
    }
}

// 2. Ensure `ac_original_parts` table exists & has default 15 parts
$table_check_p = mysqli_query($conn, "SHOW TABLES LIKE 'ac_original_parts'");
if (!$table_check_p || mysqli_num_rows($table_check_p) == 0) {
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `ac_original_parts` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(100) NOT NULL,
      `price` decimal(10,2) NOT NULL DEFAULT 0.00,
      `is_active` tinyint(1) NOT NULL DEFAULT 1,
      `display_order` int(11) NOT NULL DEFAULT 0,
      PRIMARY KEY (`id`),
      UNIQUE KEY `name` (`name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
}

$default_parts = [
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
    ['ROOM SENSOR - 950', 950],
    ['None (Service Only)', 0]
];

$count_check_p = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM ac_original_parts");
$count_row_p = ($count_check_p && mysqli_num_rows($count_check_p) > 0) ? mysqli_fetch_assoc($count_check_p) : ['cnt' => 0];
if (empty($count_row_p['cnt'])) {
    $p_order = 1;
    foreach ($default_parts as $p) {
        $p_price = (float)$p[1];
        mysqli_query($conn, "INSERT IGNORE INTO `ac_original_parts` (`name`, `price`, `is_active`, `display_order`) VALUES ('" . mysqli_real_escape_string($conn, $p[0]) . "', '$p_price', 1, $p_order)");
        $p_order++;
    }
}

// Reload Default Brands Action
if (isset($_GET['action']) && $_GET['action'] === 'load_default_brands') {
    mysqli_query($conn, "TRUNCATE TABLE `ac_brands`");
    $order = 1;
    foreach ($default_brands as $b) {
        mysqli_query($conn, "INSERT INTO `ac_brands` (`name`, `is_active`, `display_order`) VALUES ('" . mysqli_real_escape_string($conn, $b) . "', 1, $order)");
        $order++;
    }
    $msg = "All 16 Default AC Brands loaded successfully!";
    $msg_type = "success";
}

// Reload Default Parts Action
if (isset($_GET['action']) && $_GET['action'] === 'load_default_parts') {
    mysqli_query($conn, "TRUNCATE TABLE `ac_original_parts`");
    $p_order = 1;
    foreach ($default_parts as $p) {
        $p_price = (float)$p[1];
        mysqli_query($conn, "INSERT INTO `ac_original_parts` (`name`, `price`, `is_active`, `display_order`) VALUES ('" . mysqli_real_escape_string($conn, $p[0]) . "', '$p_price', 1, $p_order)");
        $p_order++;
    }
    $msg = "All 15 Original Spare Parts & Prices loaded successfully!";
    $msg_type = "success";
}

// Handle AC Brand Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['brand_action'])) {
    $action = $_POST['brand_action'];
    $name = mysqli_real_escape_string($conn, trim($_POST['brand_name'] ?? ''));
    $display_order = intval($_POST['display_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if ($action === 'add_brand' && !empty($name)) {
        if (mysqli_query($conn, "INSERT INTO ac_brands (name, is_active, display_order) VALUES ('$name', $is_active, $display_order)")) {
            $msg = "AC Brand '<strong>" . htmlspecialchars($name) . "</strong>' added successfully!";
            $msg_type = "success";
        }
    } elseif ($action === 'edit_brand') {
        $brand_id = intval($_POST['brand_id'] ?? 0);
        if ($brand_id > 0 && !empty($name)) {
            if (mysqli_query($conn, "UPDATE ac_brands SET name='$name', display_order=$display_order, is_active=$is_active WHERE id=$brand_id")) {
                $msg = "AC Brand updated successfully!";
                $msg_type = "success";
            }
        }
    }
}

// Handle Original Part Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['part_action'])) {
    $action = $_POST['part_action'];
    $name = mysqli_real_escape_string($conn, trim($_POST['part_name'] ?? ''));
    $price = (float)($_POST['part_price'] ?? 0);
    $display_order = intval($_POST['display_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if ($action === 'add_part' && !empty($name)) {
        if (mysqli_query($conn, "INSERT INTO ac_original_parts (name, price, is_active, display_order) VALUES ('$name', '$price', $is_active, $display_order)")) {
            $msg = "Original Part '<strong>" . htmlspecialchars($name) . "</strong>' added successfully!";
            $msg_type = "success";
        }
    } elseif ($action === 'edit_part') {
        $part_id = intval($_POST['part_id'] ?? 0);
        if ($part_id > 0 && !empty($name)) {
            if (mysqli_query($conn, "UPDATE ac_original_parts SET name='$name', price='$price', display_order=$display_order, is_active=$is_active WHERE id=$part_id")) {
                $msg = "Original Part updated successfully!";
                $msg_type = "success";
            }
        }
    }
}

// Handle GET actions for brands/parts (delete / toggle)
if (isset($_GET['brand_action'])) {
    $b_act = $_GET['brand_action'];
    $b_id = intval($_GET['id'] ?? 0);
    if ($b_act === 'delete' && $b_id > 0) {
        mysqli_query($conn, "DELETE FROM ac_brands WHERE id = $b_id");
        $msg = "AC Brand removed successfully.";
        $msg_type = "success";
    } elseif ($b_act === 'toggle' && $b_id > 0) {
        mysqli_query($conn, "UPDATE ac_brands SET is_active = IF(is_active=1,0,1) WHERE id = $b_id");
        $msg = "AC Brand status updated.";
        $msg_type = "success";
    }
}

if (isset($_GET['part_action'])) {
    $p_act = $_GET['part_action'];
    $p_id = intval($_GET['id'] ?? 0);
    if ($p_act === 'delete' && $p_id > 0) {
        mysqli_query($conn, "DELETE FROM ac_original_parts WHERE id = $p_id");
        $msg = "Original Part removed successfully.";
        $msg_type = "success";
    } elseif ($p_act === 'toggle' && $p_id > 0) {
        mysqli_query($conn, "UPDATE ac_original_parts SET is_active = IF(is_active=1,0,1) WHERE id = $p_id");
        $msg = "Original Part status updated.";
        $msg_type = "success";
    }
}

// Handle Booking Settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_booking_settings'])) {
    $hero_badge = mysqli_real_escape_string($conn, trim($_POST['hero_badge'] ?? ''));
    $hero_title = mysqli_real_escape_string($conn, trim($_POST['hero_title'] ?? ''));
    $hero_text = mysqli_real_escape_string($conn, trim($_POST['hero_text'] ?? ''));
    $promo_title = mysqli_real_escape_string($conn, trim($_POST['promo_title'] ?? ''));
    $rating_text = mysqli_real_escape_string($conn, trim($_POST['rating_text'] ?? ''));
    $ticker_text = mysqli_real_escape_string($conn, trim($_POST['ticker_text'] ?? ''));
    $arrival_guarantee_gu = mysqli_real_escape_string($conn, trim($_POST['arrival_guarantee_gu'] ?? ''));
    $arrival_guarantee_en = mysqli_real_escape_string($conn, trim($_POST['arrival_guarantee_en'] ?? ''));
    $max_coupon_discount = intval($_POST['max_coupon_discount'] ?? 10);

    $upd_set = "INSERT INTO booking_settings 
        (id, hero_badge, hero_title, hero_text, promo_title, rating_text, ticker_text, arrival_guarantee_gu, arrival_guarantee_en, max_coupon_discount) 
        VALUES 
        (1, '$hero_badge', '$hero_title', '$hero_text', '$promo_title', '$rating_text', '$ticker_text', '$arrival_guarantee_gu', '$arrival_guarantee_en', $max_coupon_discount)
        ON DUPLICATE KEY UPDATE 
        hero_badge = VALUES(hero_badge),
        hero_title = VALUES(hero_title),
        hero_text = VALUES(hero_text),
        promo_title = VALUES(promo_title),
        rating_text = VALUES(rating_text),
        ticker_text = VALUES(ticker_text),
        arrival_guarantee_gu = VALUES(arrival_guarantee_gu),
        arrival_guarantee_en = VALUES(arrival_guarantee_en),
        max_coupon_discount = VALUES(max_coupon_discount)";

    if (mysqli_query($conn, $upd_set)) {
        $msg = "Booking page settings & guarantee texts saved successfully!";
        $msg_type = "success";
    }
}

// Fetch single brand/part for edit
$edit_brand = null;
if (isset($_GET['edit_brand_id'])) {
    $eb_id = intval($_GET['edit_brand_id']);
    $eb_res = mysqli_query($conn, "SELECT * FROM ac_brands WHERE id = $eb_id");
    if ($eb_res && mysqli_num_rows($eb_res) > 0) $edit_brand = mysqli_fetch_assoc($eb_res);
}

$edit_part = null;
if (isset($_GET['edit_part_id'])) {
    $ep_id = intval($_GET['edit_part_id']);
    $ep_res = mysqli_query($conn, "SELECT * FROM ac_original_parts WHERE id = $ep_id");
    if ($ep_res && mysqli_num_rows($ep_res) > 0) $edit_part = mysqli_fetch_assoc($ep_res);
}

// Fetch all AC brands & parts
$brands_res = mysqli_query($conn, "SELECT * FROM ac_brands ORDER BY display_order ASC, id ASC");
$total_brands = $brands_res ? mysqli_num_rows($brands_res) : 0;

$parts_res = mysqli_query($conn, "SELECT * FROM ac_original_parts ORDER BY display_order ASC, id ASC");
$total_parts = $parts_res ? mysqli_num_rows($parts_res) : 0;

// Fetch settings
$settings_res = mysqli_query($conn, "SELECT * FROM booking_settings WHERE id = 1");
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
if ($settings_res && mysqli_num_rows($settings_res) > 0) {
    $db_s = mysqli_fetch_assoc($settings_res);
    if ($db_s) {
        foreach ($db_s as $k => $v) {
            if ($v !== null && $v !== '') $b_settings[$k] = $v;
        }
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<!-- Alerts -->
<?php if (!empty($msg)): ?>
    <div class="alert alert-<?php echo $msg_type; ?> alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas <?php echo $msg_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> me-2"></i>
        <?php echo $msg; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<div class="row">
    <!-- 1. AC Brands Management (16 Brands) -->
    <div class="col-lg-6 mb-4">
        <div class="card card-primary card-outline shadow-sm rounded-3">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="card-title mb-0 fw-bold text-primary">
                    <i class="fas fa-snowflake me-2"></i> AC Brands / Companies (<?php echo $total_brands; ?>)
                </h5>
                <a href="manage_booking_form.php?action=load_default_brands" class="btn btn-outline-primary btn-sm rounded-pill" onclick="return confirm('Reload 16 standard AC brands?');">
                    <i class="fas fa-sync-alt me-1"></i> Reload 16 Brands
                </a>
            </div>

            <!-- Add / Edit Brand Form -->
            <div class="card-body bg-light border-bottom py-3">
                <form method="POST" action="manage_booking_form.php">
                    <input type="hidden" name="brand_action" value="<?php echo $edit_brand ? 'edit_brand' : 'add_brand'; ?>">
                    <?php if ($edit_brand): ?><input type="hidden" name="brand_id" value="<?php echo $edit_brand['id']; ?>"><?php endif; ?>

                    <div class="row g-2 align-items-center">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold mb-1">Brand Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="brand_name" required 
                                   value="<?php echo htmlspecialchars($edit_brand['name'] ?? ''); ?>" 
                                   placeholder="e.g. Voltas, Daikin, LG">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold mb-1">Order</label>
                            <input type="number" class="form-control form-control-sm" name="display_order" 
                                   value="<?php echo htmlspecialchars($edit_brand['display_order'] ?? ($total_brands + 1)); ?>">
                        </div>
                        <div class="col-md-3 pt-3 d-flex gap-1">
                            <button type="submit" class="btn btn-primary btn-sm rounded-pill w-100">
                                <i class="fas <?php echo $edit_brand ? 'fa-check' : 'fa-plus'; ?> me-1"></i>
                                <?php echo $edit_brand ? 'Update' : 'Add Brand'; ?>
                            </button>
                            <?php if ($edit_brand): ?><a href="manage_booking_form.php" class="btn btn-secondary btn-sm rounded-pill">Cancel</a><?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Brands Table -->
            <div class="card-body p-0 table-responsive" style="max-height: 380px; overflow-y: auto;">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th style="width: 45px;" class="text-center">#</th>
                            <th>Brand Name</th>
                            <th style="width: 70px;" class="text-center">Order</th>
                            <th style="width: 90px;" class="text-center">Status</th>
                            <th style="width: 95px;" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($brands_res && mysqli_num_rows($brands_res) > 0): ?>
                            <?php $b_idx = 1; while ($brow = mysqli_fetch_assoc($brands_res)): ?>
                                <tr>
                                    <td class="text-center text-muted fw-bold"><?php echo $b_idx++; ?></td>
                                    <td><strong class="text-dark"><i class="fas fa-snowflake text-primary me-2"></i><?php echo htmlspecialchars($brow['name']); ?></strong></td>
                                    <td class="text-center"><span class="badge bg-light text-dark border"><?php echo $brow['display_order']; ?></span></td>
                                    <td class="text-center">
                                        <a href="manage_booking_form.php?brand_action=toggle&id=<?php echo $brow['id']; ?>" class="badge <?php echo $brow['is_active'] == 1 ? 'badge-success' : 'badge-secondary'; ?> p-2 text-decoration-none">
                                            <?php echo $brow['is_active'] == 1 ? 'Active' : 'Hidden'; ?>
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="manage_booking_form.php?edit_brand_id=<?php echo $brow['id']; ?>" class="btn btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                            <a href="manage_booking_form.php?brand_action=delete&id=<?php echo $brow['id']; ?>" class="btn btn-outline-danger" onclick="return confirm('Delete this brand?');"><i class="fas fa-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center py-3 text-muted">No AC Brands found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 2. AC Original Spare Parts Management (15 Parts) -->
    <div class="col-lg-6 mb-4">
        <div class="card card-danger card-outline shadow-sm rounded-3">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="card-title mb-0 fw-bold text-danger">
                    <i class="fas fa-cogs me-2"></i> Original Spare Parts & Prices (<?php echo $total_parts; ?>)
                </h5>
                <a href="manage_booking_form.php?action=load_default_parts" class="btn btn-outline-danger btn-sm rounded-pill" onclick="return confirm('Reload 15 standard spare parts?');">
                    <i class="fas fa-sync-alt me-1"></i> Reload 15 Parts
                </a>
            </div>

            <!-- Add / Edit Part Form -->
            <div class="card-body bg-light border-bottom py-3">
                <form method="POST" action="manage_booking_form.php">
                    <input type="hidden" name="part_action" value="<?php echo $edit_part ? 'edit_part' : 'add_part'; ?>">
                    <?php if ($edit_part): ?><input type="hidden" name="part_id" value="<?php echo $edit_part['id']; ?>"><?php endif; ?>

                    <div class="row g-2 align-items-center">
                        <div class="col-md-5">
                            <label class="form-label small fw-bold mb-1">Part Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="part_name" required 
                                   value="<?php echo htmlspecialchars($edit_part['name'] ?? ''); ?>" 
                                   placeholder="e.g. COMPRESSOR PCB - 8250">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold mb-1">Price (₹)</label>
                            <input type="number" class="form-control form-control-sm" name="part_price" step="1" 
                                   value="<?php echo htmlspecialchars($edit_part['price'] ?? 0); ?>" placeholder="0">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold mb-1">Order</label>
                            <input type="number" class="form-control form-control-sm" name="display_order" 
                                   value="<?php echo htmlspecialchars($edit_part['display_order'] ?? ($total_parts + 1)); ?>">
                        </div>
                        <div class="col-md-2 pt-3 d-flex gap-1">
                            <button type="submit" class="btn btn-danger btn-sm rounded-pill w-100">
                                <?php echo $edit_part ? 'Update' : 'Add Part'; ?>
                            </button>
                            <?php if ($edit_part): ?><a href="manage_booking_form.php" class="btn btn-secondary btn-sm rounded-pill">Cancel</a><?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Parts Table -->
            <div class="card-body p-0 table-responsive" style="max-height: 380px; overflow-y: auto;">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th style="width: 45px;" class="text-center">#</th>
                            <th>Part Description</th>
                            <th style="width: 70px;" class="text-center">Order</th>
                            <th style="width: 90px;" class="text-center">Status</th>
                            <th style="width: 95px;" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($parts_res && mysqli_num_rows($parts_res) > 0): ?>
                            <?php $p_idx = 1; while ($prow = mysqli_fetch_assoc($parts_res)): ?>
                                <tr>
                                    <td class="text-center text-muted fw-bold"><?php echo $p_idx++; ?></td>
                                    <td><strong class="text-dark"><i class="fas fa-cog text-danger me-2"></i><?php echo htmlspecialchars($prow['name']); ?></strong></td>
                                    <td class="text-center"><span class="badge bg-light text-dark border"><?php echo $prow['display_order']; ?></span></td>
                                    <td class="text-center">
                                        <a href="manage_booking_form.php?part_action=toggle&id=<?php echo $prow['id']; ?>" class="badge <?php echo $prow['is_active'] == 1 ? 'badge-success' : 'badge-secondary'; ?> p-2 text-decoration-none">
                                            <?php echo $prow['is_active'] == 1 ? 'Active' : 'Hidden'; ?>
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="manage_booking_form.php?edit_part_id=<?php echo $prow['id']; ?>" class="btn btn-outline-danger" title="Edit"><i class="fas fa-edit"></i></a>
                                            <a href="manage_booking_form.php?part_action=delete&id=<?php echo $prow['id']; ?>" class="btn btn-outline-secondary" onclick="return confirm('Delete this part?');"><i class="fas fa-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center py-3 text-muted">No Parts found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 3. Booking Page Headings, Side Promo Card & Guarantee Texts -->
    <div class="col-12 mb-4">
        <div class="card card-info card-outline shadow-sm rounded-3">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 fw-bold text-info">
                    <i class="fas fa-sliders-h me-2"></i> Booking Page Texts, Guarantees & Tickers
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="manage_booking_form.php">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Hero Banner Badge</label>
                            <input type="text" class="form-control" name="hero_badge" 
                                   value="<?php echo htmlspecialchars($b_settings['hero_badge']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Hero Main Title (H1)</label>
                            <input type="text" class="form-control" name="hero_title" 
                                   value="<?php echo htmlspecialchars($b_settings['hero_title']); ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small">Hero Subtitle / Description</label>
                            <textarea class="form-control" name="hero_text" rows="2"><?php echo htmlspecialchars($b_settings['hero_text']); ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Side Promo Title</label>
                            <input type="text" class="form-control" name="promo_title" 
                                   value="<?php echo htmlspecialchars($b_settings['promo_title']); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Rating & Trust Text</label>
                            <input type="text" class="form-control" name="rating_text" 
                                   value="<?php echo htmlspecialchars($b_settings['rating_text']); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Max Coupon Discount (%)</label>
                            <input type="number" class="form-control" name="max_coupon_discount" min="0" max="100" 
                                   value="<?php echo htmlspecialchars($b_settings['max_coupon_discount']); ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small">Live Ticker / Status Bar Text</label>
                            <input type="text" class="form-control" name="ticker_text" 
                                   value="<?php echo htmlspecialchars($b_settings['ticker_text']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-success">Arrival Guarantee (Gujarati)</label>
                            <textarea class="form-control" name="arrival_guarantee_gu" rows="2"><?php echo htmlspecialchars($b_settings['arrival_guarantee_gu']); ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-primary">Arrival Guarantee (English)</label>
                            <textarea class="form-control" name="arrival_guarantee_en" rows="2"><?php echo htmlspecialchars($b_settings['arrival_guarantee_en']); ?></textarea>
                        </div>
                        <div class="col-12 text-end pt-2">
                            <button type="submit" name="save_booking_settings" class="btn btn-info rounded-pill px-4 text-white shadow-sm">
                                <i class="fas fa-save me-2"></i> Save Page Settings
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
