<?php
require_once __DIR__ . '/includes/auth.php';
requireAdminLogin();

$page_title = 'Manage Services';
$active_page = 'services';

$msg = '';
$msg_type = '';

// Ensure table exists & seed default data if empty
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'services'");
if (mysqli_num_rows($table_check) == 0) {
    $create_sql = "CREATE TABLE IF NOT EXISTS `services` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `slug` varchar(100) NOT NULL,
      `name` varchar(255) NOT NULL,
      `badge` varchar(255) NOT NULL DEFAULT 'PROFESSIONAL AC SERVICE',
      `title` varchar(255) NOT NULL,
      `image` varchar(255) NOT NULL DEFAULT 'img/installation.jpg',
      `icon` varchar(255) NOT NULL DEFAULT 'img/icon/icon-01-light.png',
      `price` decimal(10,2) NOT NULL DEFAULT 0.00,
      `price_text` varchar(100) NOT NULL DEFAULT '',
      `short_desc` text NOT NULL,
      `description` text NOT NULL,
      `features` text DEFAULT NULL,
      `estimated_time` varchar(100) NOT NULL DEFAULT '60 Mins Arrival',
      `warranty` varchar(100) NOT NULL DEFAULT '30 Days Guarantee',
      `display_order` int(11) NOT NULL DEFAULT 0,
      `is_active` tinyint(1) NOT NULL DEFAULT 1,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
      PRIMARY KEY (`id`),
      UNIQUE KEY `slug` (`slug`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    mysqli_query($conn, $create_sql);
}

// Seed default rows if empty
$count_check = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM services");
$count_row = ($count_check && mysqli_num_rows($count_check) > 0) ? mysqli_fetch_assoc($count_check) : ['cnt' => 0];
if (empty($count_row['cnt'])) {
    $seed_sql = "INSERT INTO `services` (`id`, `slug`, `name`, `badge`, `title`, `image`, `icon`, `price`, `price_text`, `short_desc`, `description`, `features`, `estimated_time`, `warranty`, `display_order`, `is_active`) VALUES
    (1, 'ac_installation', 'AC Installation', 'PROFESSIONAL AC SERVICE', 'Professional AC Installation Service', 'img/installation.jpg', 'img/icon/icon-01-light.png', 1200.00, 'Starting From ₹1200', 'Professional AC installation with proper fitting and testing.', 'Get professional AC installation with proper fitting, accurate positioning and complete testing. Our technicians ensure that your air conditioner is installed safely and performs efficiently.', 'Professional AC installation\nProper indoor and outdoor unit fitting\nCopper pipe connection\nElectrical connection checking\nCooling performance testing\nLeakage and safety inspection', '60-90 Mins Arrival', '90 Days Warranty', 1, 1),
    (2, 'ac_pipe', 'Copper Pipe', 'AC COPPER PIPE SERVICE', 'Professional AC Copper Pipe Service', 'img/pipe.webp', 'img/icon/icon-02-light.png', 425.00, 'Starting From ₹425', 'Quality copper pipe installation for reliable AC performance.', 'High-quality copper pipe installation and replacement for reliable AC performance and proper refrigerant flow.', 'High quality copper pipe\nProfessional pipe fitting\nProper refrigerant flow\nLeakage checking\nAccurate pipe measurement\nProfessional installation', '30 Mins Arrival', 'Heavy Duty Material', 2, 1),
    (3, 'ac_insulation', 'AC Insulation', 'AC INSULATION SERVICE', 'Professional AC Insulation Service', 'img/punch.jpeg', 'img/icon/icon-03-light.png', 60.00, 'Starting From ₹60', 'Professional AC insulation to improve cooling efficiency.', 'Professional AC pipe insulation service to protect pipes, reduce condensation and improve cooling efficiency.', 'Quality insulation material\nProper pipe covering\nCondensation protection\nHeat protection\nProfessional fitting\nLong-lasting insulation', '15 Mins Arrival', 'Standard Material', 3, 1),
    (4, 'ac_tape', 'Packing Tape', 'AC PACKING TAPE SERVICE', 'Professional AC Packing Tape Service', 'img/tape.jpeg', 'img/icon/icon-04-light.png', 25.00, 'Starting From ₹25', 'Quality packing tape service for safe and proper AC installation.', 'Reliable packing tape service for secure AC pipe wrapping and professional finishing.', 'Strong packing tape\nProfessional wrapping\nSecure pipe covering\nClean finishing\nWeather protection\nDurable application', '10 Mins Arrival', 'High Density Tape', 4, 1),
    (5, 'ac_cable', 'Havells 4 Core Cable (2.5 sq mm)', 'AC ELECTRICAL CABLE SERVICE', 'Havells 4 Core Cable Installation', 'img/cable.webp', 'img/icon/icon-05-light.png', 60.00, 'Starting From ₹60', 'Reliable electrical cable installation for AC systems.', 'Professional AC electrical cable installation using quality Havells 4 Core 2.5 sq mm cable.', 'Havells 4 Core Cable\n2.5 sq mm cable\nProfessional electrical connection\nSafety inspection\nProper cable routing\nConnection testing', '15 Mins Arrival', 'Genuine Havells Wire', 5, 1),
    (6, 'ac_repair', 'AC Repair', 'PROFESSIONAL AC REPAIR', 'Professional AC Repair Service', 'img/repair.jpg', 'img/icon/icon-06-light.png', 900.00, 'Starting From ₹900', 'Professional AC repair and fault diagnosis service.', 'Professional AC repair service for cooling problems, unusual sounds, electrical issues and other AC faults.', 'Complete AC inspection\nCooling problem diagnosis\nElectrical checking\nComponent inspection\nFault identification\nProfessional repair', '45-60 Mins Arrival', '30 Days Repair Warranty', 6, 1),
    (7, 'ac_gas', 'AC Gas Refilling', 'AC GAS SERVICE', 'Professional AC Gas Refilling Service', 'img/gas.jpeg', 'img/icon/icon-01-light.png', 3250.00, 'Starting From ₹3250', 'AC gas checking and refilling for better cooling.', 'Professional AC gas checking and refilling service to restore proper cooling performance.', 'Gas pressure checking\nLeakage inspection\nProfessional gas refilling\nCooling performance test\nPipe connection checking\nComplete AC testing', '45 Mins Arrival', '60 Days Leak Guarantee', 7, 1),
    (8, 'ac_maintenance', 'AC Maintenance', 'AC MAINTENANCE SERVICE', 'Professional AC Maintenance Service', 'img/maintenance.jpg', 'img/icon/icon-02-light.png', 600.00, 'Starting From ₹600', 'Regular AC maintenance to improve performance and life.', 'Complete AC maintenance service to keep your air conditioner efficient, clean and reliable.', 'Complete AC inspection\nCooling performance check\nElectrical inspection\nIndoor unit checking\nOutdoor unit checking\nPerformance testing', '45 Mins Arrival', '30 Days Service Guarantee', 8, 1),
    (9, 'ac_cleaning', 'AC Cleaning', 'PROFESSIONAL AC CLEANING', 'Professional AC Cleaning Service', 'img/cleaning.jpg', 'img/icon/icon-03-light.png', 800.00, 'Starting From ₹800', 'Complete AC cleaning for cleaner and better cooling.', 'Professional AC cleaning service to remove dust, dirt and buildup and improve cooling performance.', 'Indoor unit cleaning\nFilter cleaning\nCoil cleaning\nDust removal\nDrainage checking\nCooling performance testing', '45-60 Mins Arrival', 'Deep Jet Foam Clean', 9, 1),
    (10, 'ac_stand', 'AC Stand', 'AC STAND SERVICE', 'Professional AC Stand Installation', 'img/stand.jpeg', 'img/icon/icon-04-light.png', 1000.00, 'Starting From ₹1000', 'Strong and reliable AC stand installation.', 'Strong and reliable AC outdoor unit stand installation with proper alignment and safety checking.', 'Strong AC stand\nProper wall fitting\nAccurate alignment\nHeavy load support\nSafety checking\nProfessional installation', '30 Mins Arrival', 'Rust-Proof Metal Stand', 10, 1),
    (11, 'ac_uninstallation', 'AC Uninstallation', 'AC UNINSTALLATION SERVICE', 'Professional AC Uninstallation Service', 'img/uninstallation.jpg', 'img/icon/icon-05-light.png', 1200.00, 'Starting From ₹1200', 'Safe and professional AC uninstallation service.', 'Safe and professional AC uninstallation service with gas pumping down and careful unit packing.', 'Safe AC uninstallation\nRefrigerant pump down\nElectrical disconnection\nPipe and bracket removal\nSafe handling of indoor and outdoor unit\nCleaning service area', '45 Mins Arrival', 'Safe Removal Guarantee', 11, 1),
    (12, 'ac_amc', 'Annual Maintenance Contract (AMC)', 'ANNUAL MAINTENANCE CONTRACT', 'AC Annual Maintenance Contract (AMC)', 'img/amc.png', 'img/icon/icon-06-light.png', 5000.00, 'Starting From ₹5000', 'Complete annual AC maintenance for worry-free service.', 'Comprehensive AC Annual Maintenance Contract covering regular servicing, priority breakdown response and complete system maintenance throughout the year.', 'Multiple scheduled service visits\nPriority breakdown support\nDeep cleaning & chemical wash\nGas pressure checking & optimization\nElectrical and safety checkup\nDiscount on spare parts', '1 Year Contract', '1 Year Complete Protection', 12, 1);";
    mysqli_query($conn, $seed_sql);
}

// Function to generate slug
function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '_', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '_');
    $text = preg_replace('~-+~', '_', $text);
    $text = strtolower($text);
    return empty($text) ? 'service_' . time() : $text;
}

// Handle Form Submissions (Add / Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $name = mysqli_real_escape_string($conn, trim($_POST['name'] ?? ''));
    $custom_slug = trim($_POST['slug'] ?? '');
    $slug = !empty($custom_slug) ? slugify($custom_slug) : slugify($name);
    $slug = mysqli_real_escape_string($conn, $slug);

    $badge = mysqli_real_escape_string($conn, trim($_POST['badge'] ?? 'PROFESSIONAL AC SERVICE'));
    $title = mysqli_real_escape_string($conn, trim($_POST['title'] ?? ''));
    if (empty($title)) {
        $title = "Professional " . $name . " Service";
    }

    $price = (float)($_POST['price'] ?? 0);
    $price_text = mysqli_real_escape_string($conn, trim($_POST['price_text'] ?? ''));
    if (empty($price_text) && $price > 0) {
        $price_text = "Starting From ₹" . number_format($price, 0);
    }

    $short_desc = mysqli_real_escape_string($conn, trim($_POST['short_desc'] ?? ''));
    $description = mysqli_real_escape_string($conn, trim($_POST['description'] ?? ''));
    $features = mysqli_real_escape_string($conn, trim($_POST['features'] ?? ''));
    $estimated_time = mysqli_real_escape_string($conn, trim($_POST['estimated_time'] ?? '45-60 Mins Arrival'));
    $warranty = mysqli_real_escape_string($conn, trim($_POST['warranty'] ?? '30 Days Warranty'));
    $display_order = intval($_POST['display_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $icon = mysqli_real_escape_string($conn, trim($_POST['icon'] ?? 'img/icon/icon-01-light.png'));

    // Handle Image: Upload or select existing
    $image_path = trim($_POST['existing_image'] ?? 'img/installation.jpg');

    if (isset($_FILES['service_image']) && $_FILES['service_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['service_image']['tmp_name'];
        $fileName = $_FILES['service_image']['name'];
        $fileSize = $_FILES['service_image']['size'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (in_array($fileExtension, $allowedExtensions)) {
            $newFileName = 'service_' . time() . '_' . rand(100, 999) . '.' . $fileExtension;
            $uploadFileDir = __DIR__ . '/../img/';
            $dest_path = $uploadFileDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $image_path = 'img/' . $newFileName;
            }
        }
    } elseif (!empty($_POST['selected_preset_image'])) {
        $image_path = trim($_POST['selected_preset_image']);
    }

    $image = mysqli_real_escape_string($conn, $image_path);

    if (empty($name)) {
        $msg = "Service Name cannot be empty.";
        $msg_type = "danger";
    } else {
        if ($action === 'add') {
            // Check if slug already exists
            $check_slug = mysqli_query($conn, "SELECT id FROM services WHERE slug='$slug'");
            if (mysqli_num_rows($check_slug) > 0) {
                $slug .= '_' . time();
            }

            $sql = "INSERT INTO services 
                (slug, name, badge, title, image, icon, price, price_text, short_desc, description, features, estimated_time, warranty, display_order, is_active) 
                VALUES 
                ('$slug', '$name', '$badge', '$title', '$image', '$icon', '$price', '$price_text', '$short_desc', '$description', '$features', '$estimated_time', '$warranty', $display_order, $is_active)";

            if (mysqli_query($conn, $sql)) {
                $msg = "New service '<strong>" . htmlspecialchars($name) . "</strong>' added successfully!";
                $msg_type = "success";
            } else {
                $msg = "Error adding service: " . mysqli_error($conn);
                $msg_type = "danger";
            }
        } elseif ($action === 'edit') {
            $service_id = intval($_POST['service_id'] ?? 0);
            
            // Check if slug collision with another record
            $check_slug = mysqli_query($conn, "SELECT id FROM services WHERE slug='$slug' AND id != $service_id");
            if (mysqli_num_rows($check_slug) > 0) {
                $slug .= '_' . time();
            }

            $sql = "UPDATE services SET 
                slug = '$slug',
                name = '$name',
                badge = '$badge',
                title = '$title',
                image = '$image',
                icon = '$icon',
                price = '$price',
                price_text = '$price_text',
                short_desc = '$short_desc',
                description = '$description',
                features = '$features',
                estimated_time = '$estimated_time',
                warranty = '$warranty',
                display_order = $display_order,
                is_active = $is_active
                WHERE id = $service_id";

            if (mysqli_query($conn, $sql)) {
                $msg = "Service '<strong>" . htmlspecialchars($name) . "</strong>' updated successfully!";
                $msg_type = "success";
            } else {
                $msg = "Error updating service: " . mysqli_error($conn);
                $msg_type = "danger";
            }
        }
    }
}

// Handle Actions (Delete / Toggle)
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $service_id = intval($_GET['id'] ?? 0);

    if ($action === 'delete' && $service_id > 0) {
        // Fetch service name for notice
        $s_info = mysqli_query($conn, "SELECT name FROM services WHERE id = $service_id");
        $s_name = "Service";
        if ($s_row = mysqli_fetch_assoc($s_info)) {
            $s_name = $s_row['name'];
        }

        if (mysqli_query($conn, "DELETE FROM services WHERE id = $service_id")) {
            $msg = "Service '<strong>" . htmlspecialchars($s_name) . "</strong>' deleted successfully.";
            $msg_type = "success";
        } else {
            $msg = "Error deleting service: " . mysqli_error($conn);
            $msg_type = "danger";
        }
    } elseif ($action === 'toggle' && $service_id > 0) {
        if (mysqli_query($conn, "UPDATE services SET is_active = IF(is_active=1,0,1) WHERE id = $service_id")) {
            $msg = "Service active status updated.";
            $msg_type = "success";
        }
    }
}

// Fetch single service for edit modal or form
$edit_service = null;
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $res = mysqli_query($conn, "SELECT * FROM services WHERE id = $edit_id");
    if ($res && mysqli_num_rows($res) > 0) {
        $edit_service = mysqli_fetch_assoc($res);
    }
}

// Stats
$total_srv_res = mysqli_query($conn, "SELECT COUNT(*) as total, SUM(CASE WHEN is_active=1 THEN 1 ELSE 0 END) as active_cnt, MIN(price) as min_p, MAX(price) as max_p, AVG(price) as avg_p FROM services");
$stats = mysqli_fetch_assoc($total_srv_res);
$total_services = (int)($stats['total'] ?? 0);
$active_services = (int)($stats['active_cnt'] ?? 0);
$inactive_services = $total_services - $active_services;
$min_price = (float)($stats['min_p'] ?? 0);
$max_price = (float)($stats['max_p'] ?? 0);

// Fetch all services for list
$services_list = mysqli_query($conn, "SELECT * FROM services ORDER BY display_order ASC, id ASC");

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

            <!-- Metric Cards -->
            <div class="row g-3 mb-4">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-gradient-primary shadow-sm rounded-4 text-white p-3">
                        <div class="inner">
                            <h3 class="fw-bold mb-1"><?php echo $total_services; ?></h3>
                            <p class="mb-0 text-white-50 font-weight-bold">Total Services</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-tools"></i>
                        </div>
                        <span class="small-box-footer text-white-50">Website Offerings</span>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-gradient-success shadow-sm rounded-4 text-white p-3">
                        <div class="inner">
                            <h3 class="fw-bold mb-1"><?php echo $active_services; ?></h3>
                            <p class="mb-0 text-white-50 font-weight-bold">Active On Website</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-eye"></i>
                        </div>
                        <span class="small-box-footer text-white-50">Visible to Customers</span>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-gradient-warning shadow-sm rounded-4 text-white p-3">
                        <div class="inner">
                            <h3 class="fw-bold mb-1"><?php echo $inactive_services; ?></h3>
                            <p class="mb-0 text-white-50 font-weight-bold">Draft / Hidden</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-eye-slash"></i>
                        </div>
                        <span class="small-box-footer text-white-50">Hidden from Frontend</span>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-gradient-info shadow-sm rounded-4 text-white p-3">
                        <div class="inner">
                            <h3 class="fw-bold mb-1">₹<?php echo number_format($min_price, 0); ?> - ₹<?php echo number_format($max_price, 0); ?></h3>
                            <p class="mb-0 text-white-50 font-weight-bold">Price Range</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-tags"></i>
                        </div>
                        <span class="small-box-footer text-white-50">Standard Pricing</span>
                    </div>
                </div>
            </div>

            <!-- Action Bar -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-list-ul me-1 text-secondary"></i> All Services Catalog
                    </h5>
                    <small class="text-muted">Directly manage prices, images, descriptions and booking options.</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="../services.php" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm">
                        <i class="fas fa-external-link-alt me-1"></i> Preview Live Services Page
                    </a>
                    <button class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" type="button" data-toggle="collapse" data-target="#serviceFormCollapse" aria-expanded="<?php echo $edit_service ? 'true' : 'false'; ?>" aria-controls="serviceFormCollapse">
                        <i class="fas <?php echo $edit_service ? 'fa-pen' : 'fa-plus-circle'; ?> me-1"></i>
                        <?php echo $edit_service ? 'Edit Service Form' : 'Add New Service'; ?>
                    </button>
                </div>
            </div>

            <!-- Add / Edit Service Form (Collapsible / Dynamic) -->
            <div class="collapse <?php echo $edit_service ? 'show' : ''; ?> mb-4" id="serviceFormCollapse">
                <div class="card card-primary card-outline shadow-sm rounded-3">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                        <h5 class="card-title mb-0 fw-bold text-primary">
                            <i class="fas <?php echo $edit_service ? 'fa-edit' : 'fa-plus-circle'; ?> me-2"></i>
                            <?php echo $edit_service ? 'Edit Service: ' . htmlspecialchars($edit_service['name']) : 'Add New Service To Website'; ?>
                        </h5>
                        <?php if ($edit_service): ?>
                            <a href="manage_services.php" class="btn btn-sm btn-outline-secondary rounded-pill">
                                <i class="fas fa-times me-1"></i> Cancel Edit
                            </a>
                        <?php endif; ?>
                    </div>
                    <form method="POST" action="manage_services.php" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="<?php echo $edit_service ? 'edit' : 'add'; ?>">
                        <?php if ($edit_service): ?>
                            <input type="hidden" name="service_id" value="<?php echo $edit_service['id']; ?>">
                            <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($edit_service['image']); ?>">
                        <?php endif; ?>

                        <div class="card-body">
                            <div class="row g-3">
                                <!-- Service Name -->
                                <div class="col-md-5">
                                    <label class="form-label fw-bold">Service Name <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-tools"></i></span>
                                        </div>
                                        <input type="text" class="form-control" name="name" id="service_name_input" required 
                                               value="<?php echo htmlspecialchars($edit_service['name'] ?? ''); ?>" 
                                               placeholder="e.g. AC Deep Jet Cleaning">
                                    </div>
                                </div>

                                <!-- Service Slug (URL Key) -->
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Service Slug / Key <small class="text-muted">(auto-formatted)</small></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-link"></i></span>
                                        </div>
                                        <input type="text" class="form-control" name="slug" id="service_slug_input" 
                                               value="<?php echo htmlspecialchars($edit_service['slug'] ?? ''); ?>" 
                                               placeholder="e.g. ac_deep_jet_cleaning">
                                    </div>
                                </div>

                                <!-- Display Order -->
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Display Order</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-sort-numeric-down"></i></span>
                                        </div>
                                        <input type="number" class="form-control" name="display_order" 
                                               value="<?php echo htmlspecialchars($edit_service['display_order'] ?? ($total_services + 1)); ?>">
                                    </div>
                                </div>

                                <!-- Base Price -->
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Standard Price (₹) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">₹</span>
                                        </div>
                                        <input type="number" step="0.01" class="form-control" name="price" required 
                                               value="<?php echo htmlspecialchars($edit_service['price'] ?? '500'); ?>" 
                                               placeholder="e.g. 800">
                                    </div>
                                </div>

                                <!-- Price Display Text -->
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Price Display Tag</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                        </div>
                                        <input type="text" class="form-control" name="price_text" 
                                               value="<?php echo htmlspecialchars($edit_service['price_text'] ?? ''); ?>" 
                                               placeholder="e.g. Starting From ₹800">
                                    </div>
                                </div>

                                <!-- Badge Tag -->
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Badge / Category Tag</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-ribbon"></i></span>
                                        </div>
                                        <input type="text" class="form-control" name="badge" 
                                               value="<?php echo htmlspecialchars($edit_service['badge'] ?? 'PROFESSIONAL AC SERVICE'); ?>" 
                                               placeholder="e.g. PROFESSIONAL AC SERVICE">
                                    </div>
                                </div>

                                <!-- Full Service Title -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Detailed Page Title</label>
                                    <input type="text" class="form-control" name="title" 
                                           value="<?php echo htmlspecialchars($edit_service['title'] ?? ''); ?>" 
                                           placeholder="e.g. Professional AC Deep Jet Foam Cleaning Service">
                                </div>

                                <!-- Estimated Arrival / Duration -->
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Arrival / Service Time</label>
                                    <input type="text" class="form-control" name="estimated_time" 
                                           value="<?php echo htmlspecialchars($edit_service['estimated_time'] ?? '45-60 Mins Arrival'); ?>" 
                                           placeholder="e.g. 45-60 Mins Arrival">
                                </div>

                                <!-- Warranty / Guarantee -->
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Warranty / Guarantee</label>
                                    <input type="text" class="form-control" name="warranty" 
                                           value="<?php echo htmlspecialchars($edit_service['warranty'] ?? '30 Days Service Guarantee'); ?>" 
                                           placeholder="e.g. 30 Days Warranty">
                                </div>

                                <!-- Short Description -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Short Description <small class="text-muted">(for service cards on homepage & services.php)</small></label>
                                    <textarea class="form-control" name="short_desc" rows="3" placeholder="Brief summary of service..."><?php echo htmlspecialchars($edit_service['short_desc'] ?? ''); ?></textarea>
                                </div>

                                <!-- Full Description -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Detailed Description <small class="text-muted">(for single service details page)</small></label>
                                    <textarea class="form-control" name="description" rows="3" placeholder="Comprehensive explanation of what is included..."><?php echo htmlspecialchars($edit_service['description'] ?? ''); ?></textarea>
                                </div>

                                <!-- Features List -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Key Features & Highlights <small class="text-muted">(1 feature per line)</small></label>
                                    <textarea class="form-control" name="features" rows="4" placeholder="Indoor unit cleaning&#10;Outdoor unit checking&#10;Cooling test&#10;Leakage inspection"><?php echo htmlspecialchars($edit_service['features'] ?? ''); ?></textarea>
                                </div>

                                <!-- Image & Icon Selection -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Service Image</label>
                                    <div class="d-flex align-items-center gap-3 mb-2">
                                        <?php 
                                        $preview_img = !empty($edit_service['image']) ? '../' . $edit_service['image'] : '../img/installation.jpg';
                                        ?>
                                        <img src="<?php echo htmlspecialchars($preview_img); ?>" id="imagePreviewBox" class="rounded border shadow-sm" style="width: 80px; height: 80px; object-fit: cover;">
                                        <div class="flex-grow-1">
                                            <input type="file" class="form-control" name="service_image" accept="image/*" onchange="previewSelectedImage(this)">
                                            <small class="text-muted d-block mt-1">Upload new image (.jpg, .png, .webp)</small>
                                        </div>
                                    </div>

                                    <div class="row g-2 mt-1">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Or Select Preset Image</label>
                                            <select class="form-select form-select-sm" name="selected_preset_image" onchange="presetImagePicked(this.value)">
                                                <option value="">-- Keep Current / Uploaded --</option>
                                                <option value="img/installation.jpg">img/installation.jpg (Installation)</option>
                                                <option value="img/repair.jpg">img/repair.jpg (Repair)</option>
                                                <option value="img/cleaning.jpg">img/cleaning.jpg (Cleaning)</option>
                                                <option value="img/maintenance.jpg">img/maintenance.jpg (Maintenance)</option>
                                                <option value="img/gas.jpeg">img/gas.jpeg (Gas Refill)</option>
                                                <option value="img/pipe.webp">img/pipe.webp (Copper Pipe)</option>
                                                <option value="img/stand.jpeg">img/stand.jpeg (AC Stand)</option>
                                                <option value="img/uninstallation.jpg">img/uninstallation.jpg (Uninstallation)</option>
                                                <option value="img/amc.png">img/amc.png (AMC)</option>
                                                <option value="img/cable.webp">img/cable.webp (Cable)</option>
                                                <option value="img/tape.jpeg">img/tape.jpeg (Tape)</option>
                                                <option value="img/punch.jpeg">img/punch.jpeg (Insulation)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Icon Path / Preset</label>
                                            <select class="form-select form-select-sm" name="icon">
                                                <?php 
                                                $curr_icon = $edit_service['icon'] ?? 'img/icon/icon-01-light.png';
                                                for ($ic = 1; $ic <= 6; $ic++):
                                                    $ic_path = sprintf('img/icon/icon-%02d-light.png', $ic);
                                                    $sel = ($curr_icon === $ic_path) ? 'selected' : '';
                                                ?>
                                                    <option value="<?php echo $ic_path; ?>" <?php echo $sel; ?>>Icon <?php echo $ic; ?> (<?php echo $ic_path; ?>)</option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Status Toggle Switch -->
                                <div class="col-12 mt-3 pt-3 border-top d-flex justify-content-between align-items-center">
                                    <div class="form-check form-switch custom-switch">
                                        <input class="form-check-input custom-control-input" type="checkbox" id="isActiveSwitch" name="is_active" value="1" 
                                               <?php echo (!isset($edit_service) || $edit_service['is_active'] == 1) ? 'checked' : ''; ?>>
                                        <label class="form-check-label custom-control-label fw-bold ms-2" for="isActiveSwitch">
                                            Display Live On Website (Active)
                                        </label>
                                    </div>

                                    <div>
                                        <?php if ($edit_service): ?>
                                            <a href="manage_services.php" class="btn btn-secondary rounded-pill px-4 me-2">Cancel</a>
                                        <?php endif; ?>
                                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                                            <i class="fas fa-save me-1"></i> <?php echo $edit_service ? 'Update Service' : 'Save & Publish Service'; ?>
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Services Table Card -->
            <div class="card card-outline card-primary shadow-sm rounded-3">
                <div class="card-header bg-white py-3">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="card-title fw-bold text-dark mb-0">
                                <i class="fas fa-table me-2 text-primary"></i> Current Website Services List (<?php echo $total_services; ?>)
                            </h5>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group input-group-sm float-end" style="max-width: 300px;">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-search"></i></span>
                                </div>
                                <input type="text" id="serviceSearchInput" class="form-control border-start-0" placeholder="Filter services by name, price, tag...">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0 table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0" id="servicesDataTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;" class="text-center">#</th>
                                <th style="width: 80px;">Image</th>
                                <th>Service Name & Slug</th>
                                <th>Badge & Details</th>
                                <th style="width: 130px;">Price</th>
                                <th>Arrival & Warranty</th>
                                <th style="width: 100px;" class="text-center">Status</th>
                                <th style="width: 150px;" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($services_list) > 0): ?>
                                <?php $idx = 1; while ($row = mysqli_fetch_assoc($services_list)): ?>
                                    <tr class="service-row-item">
                                        <td class="text-center font-weight-bold text-muted"><?php echo $idx++; ?></td>
                                        <td>
                                            <img src="../<?php echo htmlspecialchars($row['image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($row['name']); ?>" 
                                                 class="rounded shadow-xs border" 
                                                 style="width: 60px; height: 50px; object-fit: cover;"
                                                 onerror="this.src='../img/installation.jpg'">
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark font-weight-bold" style="font-size: 15px;">
                                                <?php echo htmlspecialchars($row['name']); ?>
                                            </div>
                                            <small class="text-muted d-block">
                                                <code>slug: <?php echo htmlspecialchars($row['slug']); ?></code> | Order: <?php echo $row['display_order']; ?>
                                            </small>
                                            <small class="text-secondary d-block mt-1 text-truncate" style="max-width: 280px;" title="<?php echo htmlspecialchars($row['short_desc']); ?>">
                                                <?php echo htmlspecialchars($row['short_desc']); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-1 mb-1">
                                                <?php echo htmlspecialchars($row['badge']); ?>
                                            </span>
                                            <small class="d-block text-muted text-truncate" style="max-width: 250px;">
                                                <?php echo htmlspecialchars($row['title']); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-success font-weight-bold" style="font-size: 16px;">
                                                ₹<?php echo number_format($row['price'], 2); ?>
                                            </div>
                                            <?php if (!empty($row['price_text'])): ?>
                                                <small class="badge bg-light text-dark border"><?php echo htmlspecialchars($row['price_text']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="small"><i class="fas fa-clock text-info me-1"></i> <?php echo htmlspecialchars($row['estimated_time']); ?></div>
                                            <div class="small text-muted"><i class="fas fa-shield-alt text-warning me-1"></i> <?php echo htmlspecialchars($row['warranty']); ?></div>
                                        </td>
                                        <td class="text-center">
                                            <a href="manage_services.php?action=toggle&id=<?php echo $row['id']; ?>" 
                                               class="badge <?php echo $row['is_active'] == 1 ? 'badge-success' : 'badge-secondary'; ?> p-2 text-decoration-none shadow-xs"
                                               title="Click to toggle active/inactive">
                                                <i class="fas <?php echo $row['is_active'] == 1 ? 'fa-check-circle' : 'fa-ban'; ?> me-1"></i>
                                                <?php echo $row['is_active'] == 1 ? 'Active' : 'Inactive'; ?>
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <!-- Live View Link -->
                                                <a href="../all_services/service.php?service=<?php echo urlencode($row['slug']); ?>" target="_blank" class="btn btn-outline-info" title="View Single Service Page">
                                                    <i class="fas fa-external-link-alt"></i>
                                                </a>
                                                <!-- Edit Button -->
                                                <a href="manage_services.php?edit_id=<?php echo $row['id']; ?>" class="btn btn-outline-primary" title="Edit Service">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <!-- Delete Button with SweetAlert -->
                                                <button type="button" class="btn btn-outline-danger btn-delete-service" 
                                                        data-id="<?php echo $row['id']; ?>" 
                                                        data-name="<?php echo htmlspecialchars($row['name']); ?>" 
                                                        title="Delete Service">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="fas fa-box-open fa-3x mb-3 text-secondary"></i>
                                        <p class="mb-0">No services found in database.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<!-- SweetAlert2 & Helper JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Auto format slug on name input if adding
const nameInput = document.getElementById('service_name_input');
const slugInput = document.getElementById('service_slug_input');
if (nameInput && slugInput && slugInput.value === '') {
    nameInput.addEventListener('input', function() {
        slugInput.value = this.value
            .toLowerCase()
            .replace(/[^\w ]+/g, '')
            .replace(/ +/g, '_');
    });
}

// Image upload preview
function previewSelectedImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imagePreviewBox').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function presetImagePicked(val) {
    if (val) {
        document.getElementById('imagePreviewBox').src = '../' + val;
    }
}

// Search filter in table
document.getElementById('serviceSearchInput')?.addEventListener('keyup', function() {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('#servicesDataTable tbody tr.service-row-item');
    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});

// Delete confirmation
document.querySelectorAll('.btn-delete-service').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        const name = this.getAttribute('data-name');

        Swal.fire({
            title: 'Delete Service?',
            html: `Are you sure you want to delete <strong>${name}</strong>?<br><small class="text-danger">This will remove the service from the website and booking forms.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash me-1"></i> Yes, Delete it',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `manage_services.php?action=delete&id=${id}`;
            }
        });
    });
});
</script>
