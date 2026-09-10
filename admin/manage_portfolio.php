<?php
require_once __DIR__ . '/includes/auth.php';
requireAdminLogin();

$page_title    = 'Manage Portfolio';
$active_page   = 'portfolio';
$msg           = '';
$msg_type      = '';
$edit_item     = null;

// ── Upload helper ──────────────────────────────────────────────
function uploadPortfolioImage($file_key, $old_path = '') {
    $upload_dir = __DIR__ . '/../img/portfolio/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

    if (empty($_FILES[$file_key]['name'])) return $old_path; // keep old

    $file      = $_FILES[$file_key];
    $ext       = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed   = ['jpg','jpeg','png','webp','gif'];
    if (!in_array($ext, $allowed))  return ['error' => 'Only JPG, PNG, WEBP, GIF allowed.'];
    if ($file['size'] > 5 * 1024 * 1024) return ['error' => 'Max file size 5 MB.'];

    $new_name  = 'portfolio_' . time() . '_' . rand(100,999) . '.' . $ext;
    $dest      = $upload_dir . $new_name;

    if (!move_uploaded_file($file['tmp_name'], $dest)) return ['error' => 'Upload failed.'];

    // Delete old image
    if ($old_path && file_exists(__DIR__ . '/../' . $old_path)) {
        @unlink(__DIR__ . '/../' . $old_path);
    }
    return 'img/portfolio/' . $new_name;
}

// ── Handle DELETE ─────────────────────────────────────────────
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $dr = mysqli_query($conn, "SELECT image_path FROM portfolio_items WHERE id=$del_id");
    if ($dr && $row = mysqli_fetch_assoc($dr)) {
        if ($row['image_path'] && file_exists(__DIR__ . '/../' . $row['image_path'])) {
            @unlink(__DIR__ . '/../' . $row['image_path']);
        }
    }
    mysqli_query($conn, "DELETE FROM portfolio_items WHERE id=$del_id");
    $msg = 'Portfolio item deleted.'; $msg_type = 'success';
}

// ── Handle TOGGLE ACTIVE ──────────────────────────────────────
if (isset($_GET['toggle'])) {
    $tid = (int)$_GET['toggle'];
    mysqli_query($conn, "UPDATE portfolio_items SET is_active = 1 - is_active WHERE id=$tid");
    header('Location: manage_portfolio.php'); exit;
}

// ── Handle ADD / EDIT form ────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $edit_id    = (int)($_POST['edit_id'] ?? 0);
    $title      = mysqli_real_escape_string($conn, trim($_POST['title'] ?? ''));
    $description= mysqli_real_escape_string($conn, trim($_POST['description'] ?? ''));
    $category   = mysqli_real_escape_string($conn, trim($_POST['category'] ?? 'General'));
    $disp_order = (int)($_POST['display_order'] ?? 0);
    $is_active  = isset($_POST['is_active']) ? 1 : 0;

    // Handle image upload
    $old_path   = mysqli_real_escape_string($conn, trim($_POST['old_image'] ?? ''));
    $img_result = uploadPortfolioImage('portfolio_image', $old_path);
    if (is_array($img_result) && isset($img_result['error'])) {
        $msg = $img_result['error']; $msg_type = 'danger';
    } else {
        $image_path = mysqli_real_escape_string($conn, $img_result);

        if ($edit_id > 0) {
            $sql = "UPDATE portfolio_items SET title='$title', description='$description',
                    category='$category', display_order=$disp_order, is_active=$is_active"
                 . ($image_path ? ", image_path='$image_path'" : "")
                 . " WHERE id=$edit_id";
            mysqli_query($conn, $sql);
            $msg = 'Portfolio item updated!'; $msg_type = 'success';
        } else {
            if (empty($image_path)) { $msg = 'Please upload an image.'; $msg_type = 'danger'; }
            else {
                mysqli_query($conn, "INSERT INTO portfolio_items (title, description, category, image_path, display_order, is_active)
                    VALUES ('$title','$description','$category','$image_path',$disp_order,$is_active)");
                $msg = 'Portfolio item added!'; $msg_type = 'success';
            }
        }
    }
}

// ── Load edit item ────────────────────────────────────────────
if (isset($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    $er  = mysqli_query($conn, "SELECT * FROM portfolio_items WHERE id=$eid");
    if ($er) $edit_item = mysqli_fetch_assoc($er);
}

// ── Fetch all portfolio items ──────────────────────────────────
$items_q = mysqli_query($conn, "SELECT * FROM portfolio_items ORDER BY display_order ASC, id DESC");
$all_items = [];
while ($r = mysqli_fetch_assoc($items_q)) $all_items[] = $r;

// ── Get distinct categories ────────────────────────────────────
$cat_q = mysqli_query($conn, "SELECT DISTINCT category FROM portfolio_items ORDER BY category");
$categories = [];
while ($c = mysqli_fetch_assoc($cat_q)) $categories[] = $c['category'];

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<style>
.portfolio-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px,1fr)); gap: 18px; }
.portfolio-card { border-radius: 14px; overflow: hidden; box-shadow: 0 4px 18px rgba(0,0,0,.08); transition: transform .25s; background:#fff; }
.portfolio-card:hover { transform: translateY(-4px); }
.portfolio-card img { width:100%; height:160px; object-fit:cover; display:block; }
.portfolio-card-body { padding: 10px 12px 12px; }
.type-badge { font-size:11px; font-weight:700; padding:3px 9px; border-radius:50px; }
.inactive-overlay { opacity: 0.45; }
.upload-preview { width:100%; height:160px; object-fit:cover; border-radius:10px; display:none; margin-top:8px; }
</style>

<?php if ($msg): ?>
<div class="alert alert-<?= $msg_type ?> alert-dismissible fade show" role="alert">
    <i class="fas <?= $msg_type==='success'?'fa-check-circle':'fa-exclamation-triangle' ?> me-2"></i>
    <?= htmlspecialchars($msg) ?>
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
</div>
<?php endif; ?>

<div class="row">

    <!-- ===== ADD / EDIT FORM ===== -->
    <div class="col-lg-4 mb-4">
        <div class="card card-primary card-outline shadow-sm rounded-3">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 fw-bold text-primary">
                    <i class="fas fa-<?= $edit_item ? 'edit' : 'plus-circle' ?> me-2"></i>
                    <?= $edit_item ? 'Edit Portfolio Item' : 'Add New Photo' ?>
                </h5>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="card-body">
                    <input type="hidden" name="edit_id"   value="<?= $edit_item['id'] ?? 0 ?>">
                    <input type="hidden" name="old_image" value="<?= htmlspecialchars($edit_item['image_path'] ?? '') ?>">

                    <!-- Title -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Title / Caption</label>
                        <input type="text" class="form-control" name="title"
                               value="<?= htmlspecialchars($edit_item['title'] ?? '') ?>"
                               placeholder="e.g. AC Installation at Shop">
                    </div>

                    <!-- Category -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Category</label>
                        <input type="text" class="form-control" name="category" list="cat-list"
                               value="<?= htmlspecialchars($edit_item['category'] ?? 'General') ?>"
                               placeholder="e.g. Installation, Repair, Gas Filling…">
                        <datalist id="cat-list">
                            <option value="Installation">
                            <option value="Repair">
                            <option value="Gas Filling">
                            <option value="Cleaning">
                            <option value="Maintenance">
                            <option value="General">
                        </datalist>
                    </div>

                    <!-- Image Upload -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            Photo <?= $edit_item ? '<small class="text-muted">(leave blank to keep existing)</small>' : '<span class="text-danger">*</span>' ?>
                        </label>
                        <?php if ($edit_item && $edit_item['image_path']): ?>
                            <div class="mb-2">
                                <img src="../<?= htmlspecialchars($edit_item['image_path']) ?>"
                                     class="rounded-3 w-100" style="height:130px;object-fit:cover;" alt="">
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" name="portfolio_image"
                               accept="image/*" id="portfolioImageInput"
                               <?= $edit_item ? '' : 'required' ?>>
                        <img id="portfolioPreview" class="upload-preview" alt="Preview">
                        <small class="text-muted">Max 5MB. JPG, PNG, WEBP, GIF.</small>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description <small class="text-muted">(optional)</small></label>
                        <textarea class="form-control" name="description" rows="2"
                                  placeholder="Short note about the work done…"><?= htmlspecialchars($edit_item['description'] ?? '') ?></textarea>
                    </div>

                    <!-- Display Order -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Display Order</label>
                        <input type="number" class="form-control" name="display_order"
                               value="<?= $edit_item['display_order'] ?? 0 ?>" min="0">
                        <small class="text-muted">Lower number = shown first.</small>
                    </div>

                    <!-- Active -->
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="is_active" id="isActive"
                               <?= ($edit_item['is_active'] ?? 1) ? 'checked' : '' ?>>
                        <label class="form-check-label fw-bold" for="isActive">Show on Website</label>
                    </div>
                </div>
                <div class="card-footer bg-white text-end py-3">
                    <?php if ($edit_item): ?>
                        <a href="manage_portfolio.php" class="btn btn-secondary rounded-pill me-2">Cancel</a>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        <i class="fas fa-save me-1"></i> <?= $edit_item ? 'Update' : 'Add Photo' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== PORTFOLIO GRID ===== -->
    <div class="col-lg-8 mb-4">
        <div class="card shadow-sm rounded-3">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="card-title mb-0 fw-bold text-dark">
                    <i class="fas fa-images text-primary me-2"></i>
                    All Portfolio Photos
                    <span class="badge bg-primary ms-2"><?= count($all_items) ?></span>
                </h5>
                <a href="../portfolio.php" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill">
                    <i class="fas fa-external-link-alt me-1"></i> View Live
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($all_items)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-images fa-3x mb-3 opacity-25"></i>
                        <p>No portfolio photos yet. Add your first photo!</p>
                    </div>
                <?php else: ?>
                    <!-- Filter Tabs -->
                    <div class="mb-3 d-flex flex-wrap gap-1" id="filterTabs">
                        <button class="btn btn-primary btn-sm rounded-pill filter-btn active" data-cat="all">All</button>
                        <?php foreach ($categories as $cat): ?>
                            <button class="btn btn-outline-secondary btn-sm rounded-pill filter-btn" data-cat="<?= htmlspecialchars($cat) ?>">
                                <?= htmlspecialchars($cat) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <div class="portfolio-grid" id="portfolioGrid">
                        <?php foreach ($all_items as $item): ?>
                        <div class="portfolio-card <?= $item['is_active'] ? '' : 'inactive-overlay' ?>"
                             data-cat="<?= htmlspecialchars($item['category']) ?>">
                            <div style="position:relative;">
                                <img src="../<?= htmlspecialchars($item['image_path']) ?>"
                                     alt="<?= htmlspecialchars($item['title']) ?>"
                                     onerror="this.src='../img/feature.jpg'">
                                <span class="position-absolute top-0 end-0 m-2 type-badge
                                      <?= $item['is_active'] ? 'bg-success text-white' : 'bg-danger text-white' ?>">
                                    <?= $item['is_active'] ? 'Live' : 'Hidden' ?>
                                </span>
                            </div>
                            <div class="portfolio-card-body">
                                <div class="fw-bold text-dark" style="font-size:13px;line-height:1.3;">
                                    <?= htmlspecialchars($item['title'] ?: 'Untitled') ?>
                                </div>
                                <small class="text-muted d-block mb-2"><?= htmlspecialchars($item['category']) ?></small>
                                <div class="d-flex gap-1">
                                    <a href="manage_portfolio.php?edit=<?= $item['id'] ?>"
                                       class="btn btn-warning btn-sm rounded-pill flex-fill" style="font-size:11px;">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="manage_portfolio.php?toggle=<?= $item['id'] ?>"
                                       class="btn btn-outline-secondary btn-sm rounded-pill" style="font-size:11px;" title="Show/Hide">
                                        <i class="fas fa-eye<?= $item['is_active'] ? '-slash' : '' ?>"></i>
                                    </a>
                                    <a href="manage_portfolio.php?delete=<?= $item['id'] ?>"
                                       class="btn btn-danger btn-sm rounded-pill" style="font-size:11px;"
                                       onclick="return confirm('Delete this photo?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<script>
// Image preview before upload
document.getElementById('portfolioImageInput')?.addEventListener('change', function() {
    const preview = document.getElementById('portfolioPreview');
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; };
        reader.readAsDataURL(this.files[0]);
    }
});

// Category filter tabs
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active','btn-primary'));
        document.querySelectorAll('.filter-btn').forEach(b => { b.classList.add('btn-outline-secondary'); });
        this.classList.remove('btn-outline-secondary');
        this.classList.add('active','btn-primary');

        const cat = this.dataset.cat;
        document.querySelectorAll('.portfolio-card').forEach(card => {
            card.style.display = (cat === 'all' || card.dataset.cat === cat) ? '' : 'none';
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
