<?php
require_once __DIR__ . '/includes/auth.php';
requireAdminLogin();

$page_title = 'Contact Info & Settings';
$active_page = 'contact_settings';

$msg = '';
$msg_type = '';

// Ensure table exists & seed default data if empty
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'contact_info'");
if (!$table_check || mysqli_num_rows($table_check) == 0) {
    $create_sql = "CREATE TABLE IF NOT EXISTS `contact_info` (
      `id` int(11) NOT NULL DEFAULT 1,
      `page_heading` varchar(255) NOT NULL DEFAULT 'Contact Us',
      `page_subheading` varchar(255) NOT NULL DEFAULT 'We are available for AC Installation, Repair & Maintenance Services.',
      `address` text NOT NULL,
      `phone` varchar(50) NOT NULL DEFAULT '+91 6354911971',
      `email` varchar(100) NOT NULL DEFAULT 'aquaaircoolling@gmail.com',
      `working_hours` text NOT NULL,
      `services_text` text NOT NULL,
      `whatsapp_number` varchar(50) NOT NULL DEFAULT '916354911971',
      `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;";
    mysqli_query($conn, $create_sql);
}

// Ensure columns exist
$col_check1 = mysqli_query($conn, "SHOW COLUMNS FROM contact_info LIKE 'about_heading'");
if ($col_check1 && mysqli_num_rows($col_check1) == 0) {
    @mysqli_query($conn, "ALTER TABLE contact_info ADD COLUMN about_heading VARCHAR(255) DEFAULT 'Welcome To AC Installation & Cooling Service Center ⭐'");
}
$col_check2 = mysqli_query($conn, "SHOW COLUMNS FROM contact_info LIKE 'about_desc'");
if ($col_check2 && mysqli_num_rows($col_check2) == 0) {
    @mysqli_query($conn, "ALTER TABLE contact_info ADD COLUMN about_desc TEXT NULL");
}

$count_check = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM contact_info");
$count_row = ($count_check && mysqli_num_rows($count_check) > 0) ? mysqli_fetch_assoc($count_check) : ['cnt' => 0];
if (empty($count_row['cnt'])) {
    $seed_sql = "INSERT INTO `contact_info` (`id`, `page_heading`, `page_subheading`, `address`, `phone`, `email`, `working_hours`, `services_text`, `whatsapp_number`, `about_heading`, `about_desc`) VALUES
    (1, 'Contact Us', 'We are available for AC Installation, Repair & Maintenance Services.', '123 Main Street,\nRajkot, Gujarat', '+91 6354911971', 'aquaaircoolling@gmail.com', 'Monday - Saturday\n8:00 AM - 8:00 PM', 'AC Installation, Repair, Maintenance, Gas Filling & General AC Service.', '916354911971', 'Welcome To AC Installation & Cooling Service Center ⭐', 'We provide professional AC servicing, cleaning, maintenance, and repairs to keep your cooling system efficient and dependable. Our expert technicians ensure optimal performance, improved air quality, and reduced energy costs, helping you stay comfortable in every season.')
    ON DUPLICATE KEY UPDATE `phone` = VALUES(`phone`);";
    mysqli_query($conn, $seed_sql);
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $page_heading = mysqli_real_escape_string($conn, trim($_POST['page_heading'] ?? 'Contact Us'));
    $page_subheading = mysqli_real_escape_string($conn, trim($_POST['page_subheading'] ?? ''));
    $address = mysqli_real_escape_string($conn, trim($_POST['address'] ?? ''));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone'] ?? ''));
    $email = mysqli_real_escape_string($conn, trim($_POST['email'] ?? ''));
    $working_hours = mysqli_real_escape_string($conn, trim($_POST['working_hours'] ?? ''));
    $services_text = mysqli_real_escape_string($conn, trim($_POST['services_text'] ?? ''));
    $whatsapp_number = mysqli_real_escape_string($conn, trim($_POST['whatsapp_number'] ?? ''));
    $about_heading = mysqli_real_escape_string($conn, trim($_POST['about_heading'] ?? 'Welcome To AC Installation & Cooling Service Center ⭐'));
    $about_desc = mysqli_real_escape_string($conn, trim($_POST['about_desc'] ?? ''));

    // Clean whatsapp number (digits only)
    $clean_whatsapp = preg_replace('/[^0-9]/', '', $whatsapp_number);
    if (empty($clean_whatsapp)) {
        $clean_whatsapp = '916354911971';
    }

    $update_sql = "INSERT INTO contact_info 
        (id, page_heading, page_subheading, address, phone, email, working_hours, services_text, whatsapp_number, about_heading, about_desc) 
        VALUES 
        (1, '$page_heading', '$page_subheading', '$address', '$phone', '$email', '$working_hours', '$services_text', '$clean_whatsapp', '$about_heading', '$about_desc')
        ON DUPLICATE KEY UPDATE 
        page_heading = VALUES(page_heading),
        page_subheading = VALUES(page_subheading),
        address = VALUES(address),
        phone = VALUES(phone),
        email = VALUES(email),
        working_hours = VALUES(working_hours),
        services_text = VALUES(services_text),
        whatsapp_number = VALUES(whatsapp_number),
        about_heading = VALUES(about_heading),
        about_desc = VALUES(about_desc)";

    if (mysqli_query($conn, $update_sql)) {
        $msg = "Contact Information & Homepage Settings updated successfully!";
        $msg_type = "success";
    } else {
        $msg = "Error updating contact info: " . mysqli_error($conn);
        $msg_type = "danger";
    }
}

// Fetch current details
$contact = [
    'page_heading' => 'Contact Us',
    'page_subheading' => 'We are available for AC Installation, Repair & Maintenance Services.',
    'address' => "123 Main Street,\nRajkot, Gujarat",
    'phone' => '+91 6354911971',
    'email' => 'aquaaircoolling@gmail.com',
    'working_hours' => "Monday - Saturday\n8:00 AM - 8:00 PM",
    'services_text' => 'AC Installation, Repair, Maintenance, Gas Filling & General AC Service.',
    'whatsapp_number' => '916354911971',
    'about_heading' => 'Welcome To AC Installation & Cooling Service Center ⭐',
    'about_desc' => 'We provide professional AC servicing, cleaning, maintenance, and repairs to keep your cooling system efficient and dependable. Our expert technicians ensure optimal performance, improved air quality, and reduced energy costs, helping you stay comfortable in every season.'
];

$contact_query = mysqli_query($conn, "SELECT * FROM contact_info WHERE id = 1");
if ($contact_query && mysqli_num_rows($contact_query) > 0) {
    $db_contact = mysqli_fetch_assoc($contact_query);
    if ($db_contact) {
        foreach ($db_contact as $k => $v) {
            if ($v !== null && $v !== '') {
                $contact[$k] = $v;
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<?php if (!empty($msg)): ?>
    <div class="alert alert-<?php echo $msg_type; ?> alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas <?php echo $msg_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> me-2"></i>
        <?php echo $msg; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Footer Dynamic Info Banner -->
<div class="alert border-0 rounded-3 mb-4 py-3 px-4" style="background: linear-gradient(135deg,#eff6ff,#dbeafe); border-left: 5px solid #3b82f6 !important;">
    <div class="d-flex align-items-center gap-3">
        <span style="font-size:28px;">🦶</span>
        <div>
            <h6 class="fw-bold mb-1" style="color:#1d4ed8;">Footer Dynamic Info</h6>
            <p class="mb-0 small" style="color:#1e40af;">
                <strong>Email, Phone Number અને Address</strong> — આ ત્રણ fields admin panel (Contact &amp; Settings) માં save થતાં ही user website ના <strong>footer</strong> માં automatically update થઈ જશે.
                <a href="../index.php#footer" target="_blank" class="fw-bold ms-1" style="color:#1d4ed8;">Footer Preview <i class="fas fa-external-link-alt"></i></a>
            </p>
        </div>
    </div>
</div>


<div class="row">
    <!-- Edit Form Column -->
    <div class="col-lg-7 mb-4">
        <div class="card card-primary card-outline shadow-sm rounded-3">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold text-primary">
                    <i class="fas fa-edit me-2"></i> Edit Contact Information
                    <span class="badge ms-2 rounded-pill" style="background:#dbeafe;color:#1d4ed8;font-size:11px;">🦶 Updates Footer</span>
                </h5>
                <a href="../contact.php" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill">
                    <i class="fas fa-external-link-alt me-1"></i> Preview Live Page
                </a>
            </div>

            <form method="POST" action="manage_contact.php">
                <div class="card-body">
                    <div class="row g-3">

                        <!-- Page Main Heading -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Page Heading</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-heading"></i></span>
                                </div>
                                <input type="text" class="form-control" name="page_heading" 
                                       value="<?php echo htmlspecialchars($contact['page_heading']); ?>" required>
                            </div>
                        </div>

                        <!-- Phone Number -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Primary Phone Number <span class="badge bg-primary-subtle text-primary" style="font-size:10px;">🦶 Footer</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-phone-alt"></i></span>
                                </div>
                                <input type="text" class="form-control" name="phone" 
                                       value="<?php echo htmlspecialchars($contact['phone']); ?>" required>
                            </div>
                            <small class="text-primary"><i class="fas fa-info-circle me-1"></i>Footer ma aa number show thase.</small>
                        </div>

                        <!-- Email Address -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Official Email Address <span class="badge bg-primary-subtle text-primary" style="font-size:10px;">🦶 Footer</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                </div>
                                <input type="email" class="form-control" name="email" 
                                       value="<?php echo htmlspecialchars($contact['email']); ?>" required>
                            </div>
                            <small class="text-primary"><i class="fas fa-info-circle me-1"></i>Footer ma aa email show thase.</small>
                        </div>

                        <!-- WhatsApp Number -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">WhatsApp Receiver Number</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fab fa-whatsapp text-success"></i></span>
                                </div>
                                <input type="text" class="form-control" name="whatsapp_number" 
                                       value="<?php echo htmlspecialchars($contact['whatsapp_number']); ?>" 
                                       placeholder="e.g. 916354911971 (with country code)" required>
                            </div>
                            <small class="text-muted">Messages sent from the contact form will be delivered to this WhatsApp number.</small>
                        </div>

                        <!-- Page Subheading -->
                        <div class="col-12">
                            <label class="form-label fw-bold">Page Subheading</label>
                            <textarea class="form-control" name="page_subheading" rows="2"><?php echo htmlspecialchars($contact['page_subheading']); ?></textarea>
                        </div>

                        <!-- Office Address -->
                        <div class="col-12">
                            <label class="form-label fw-bold">Office Address <span class="badge bg-primary-subtle text-primary" style="font-size:10px;">🦶 Footer</span></label>
                            <textarea class="form-control" name="address" rows="3" required><?php echo htmlspecialchars($contact['address']); ?></textarea>
                            <small class="text-primary"><i class="fas fa-info-circle me-1"></i>Footer ma aa address show thase. Multiple lines allowed.</small>
                        </div>

                        <!-- Working Hours -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Working Hours</label>
                            <textarea class="form-control" name="working_hours" rows="3" required><?php echo htmlspecialchars($contact['working_hours']); ?></textarea>
                        </div>

                        <!-- Services Summary Text -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Services Short Summary</label>
                            <textarea class="form-control" name="services_text" rows="3" required><?php echo htmlspecialchars($contact['services_text']); ?></textarea>
                        </div>

                        <!-- Homepage Welcome Section Divider -->
                        <div class="col-12 mt-4 pt-3 border-top">
                            <h6 class="fw-bold text-primary mb-3">
                                <i class="fas fa-home me-2"></i> Homepage Welcome Section (index.php)
                                <span class="badge ms-2 rounded-pill" style="background:#e0f2fe;color:#0284c7;font-size:11px;">🏠 Live on Homepage</span>
                            </h6>
                        </div>

                        <!-- Welcome Section Heading -->
                        <div class="col-12">
                            <label class="form-label fw-bold">Welcome Section Heading</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-heading"></i></span>
                                </div>
                                <input type="text" class="form-control" name="about_heading" 
                                       value="<?php echo htmlspecialchars($contact['about_heading'] ?? 'Welcome To AC Installation & Cooling Service Center ⭐'); ?>" required>
                            </div>
                            <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Homepage (index.php) ma 'Welcome To AC Installation...' vado title aano use kare chhe.</small>
                        </div>

                        <!-- Welcome Section Description -->
                        <div class="col-12">
                            <label class="form-label fw-bold">Welcome Section Description Text</label>
                            <textarea class="form-control" name="about_desc" rows="4" required><?php echo htmlspecialchars($contact['about_desc'] ?? 'We provide professional AC servicing, cleaning, maintenance, and repairs to keep your cooling system efficient and dependable. Our expert technicians ensure optimal performance, improved air quality, and reduced energy costs, helping you stay comfortable in every season.'); ?></textarea>
                            <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Homepage (index.php) ma 'We provide professional AC servicing...' vado paragraph aano use kare chhe.</small>
                        </div>

                    </div>
                </div>

                <div class="card-footer bg-white border-top text-end py-3">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                        <i class="fas fa-save me-1"></i> Save Contact Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Live Preview Card Column -->
    <div class="col-lg-5 mb-4">
        <div class="card card-info card-outline shadow-sm rounded-3">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold text-info">
                    <i class="fas fa-eye me-2"></i> Live Preview
                </h5>
                <a href="../index.php" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill">
                    <i class="fas fa-globe me-1"></i> View Footer Live
                </a>
            </div>
            <div class="card-body p-0">

                <!-- Contact Page Preview -->
                <div class="p-3 border-bottom">
                    <p class="text-muted small fw-bold mb-2"><i class="fas fa-file-alt me-1"></i> Contact Page Info</p>
                    <div class="p-3 bg-light rounded-3 border">
                        <h6 class="fw-bold text-primary mb-1"><?php echo htmlspecialchars($contact['page_heading']); ?></h6>
                        <small class="text-muted d-block"><?php echo htmlspecialchars($contact['page_subheading']); ?></small>
                    </div>
                </div>

                <!-- FOOTER PREVIEW (Mini mockup) -->
                <div class="p-3">
                    <p class="text-muted small fw-bold mb-2"><i class="fas fa-shoe-prints me-1"></i> Footer Preview (Live) <span class="badge bg-success rounded-pill" style="font-size:10px;">Dynamic</span></p>
                    <div class="rounded-3 p-3" style="background:linear-gradient(145deg,#040e36,#06164f);">

                        <!-- Email -->
                        <div class="d-flex align-items-start gap-2 mb-2">
                            <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width:32px;height:32px;background:rgba(255,255,255,0.12);">
                                <i class="fas fa-envelope" style="color:#ff7f0e;font-size:13px;"></i>
                            </div>
                            <div>
                                <small class="d-block" style="color:#8ba3c2;font-size:10px;">Email</small>
                                <span style="color:#e2eaf6;font-size:12px;font-weight:600;"><?php echo htmlspecialchars($contact['email']); ?></span>
                            </div>
                        </div>

                        <!-- Phone -->
                        <div class="d-flex align-items-start gap-2 mb-2">
                            <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width:32px;height:32px;background:rgba(255,255,255,0.12);">
                                <i class="fas fa-phone-alt" style="color:#ff7f0e;font-size:13px;"></i>
                            </div>
                            <div>
                                <small class="d-block" style="color:#8ba3c2;font-size:10px;">Phone</small>
                                <span style="color:#e2eaf6;font-size:12px;font-weight:600;"><?php echo htmlspecialchars($contact['phone']); ?></span>
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="d-flex align-items-start gap-2">
                            <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width:32px;height:32px;background:rgba(255,255,255,0.12);">
                                <i class="fas fa-map-marker-alt" style="color:#ff7f0e;font-size:13px;"></i>
                            </div>
                            <div>
                                <small class="d-block" style="color:#8ba3c2;font-size:10px;">Address</small>
                                <span style="color:#e2eaf6;font-size:12px;font-weight:600;"><?php echo nl2br(htmlspecialchars($contact['address'])); ?></span>
                            </div>
                        </div>

                    </div>
                    <p class="text-center mt-2 mb-0"><small class="text-success"><i class="fas fa-sync-alt me-1"></i>Save karta footer update thase!</small></p>
                </div>

                <!-- Other fields preview -->
                <div class="list-group list-group-flush border-top">
                    <!-- Working Hours -->
                    <div class="list-group-item d-flex align-items-start py-2">
                        <div class="me-3 p-2 bg-primary-subtle text-primary rounded-3">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <strong class="d-block text-dark" style="font-size:12px;">Working Hours</strong>
                            <span class="text-muted" style="font-size:11px;"><?php echo nl2br(htmlspecialchars($contact['working_hours'])); ?></span>
                        </div>
                    </div>

                    <!-- WhatsApp -->
                    <div class="list-group-item d-flex align-items-start py-2 bg-success-subtle">
                        <div class="me-3 p-2 bg-success text-white rounded-3">
                            <i class="fab fa-whatsapp"></i>
                        </div>
                        <div>
                            <strong class="d-block text-success" style="font-size:12px;">WhatsApp Destination</strong>
                            <span class="text-success" style="font-size:11px;font-weight:600;">+<?php echo htmlspecialchars($contact['whatsapp_number']); ?></span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

