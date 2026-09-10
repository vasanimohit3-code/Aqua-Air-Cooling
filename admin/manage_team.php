<?php
require_once __DIR__ . '/includes/auth.php';
requireAdminLogin();

$page_title  = 'Manage Team Members';
$active_page = 'team';
$msg         = '';
$msg_type    = '';
$edit_item   = null;

// ── Upload helper ──────────────────────────────────────────────
function uploadTeamPhoto($file_key, $old_path = '') {
    $upload_dir = __DIR__ . '/../img/team/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

    if (empty($_FILES[$file_key]['name'])) return $old_path;

    $file    = $_FILES[$file_key];
    $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','webp','gif'];
    if (!in_array($ext, $allowed)) return ['error' => 'Only JPG, PNG, WEBP, GIF allowed.'];
    if ($file['size'] > 5 * 1024 * 1024) return ['error' => 'Max file size 5 MB.'];

    $new_name = 'team_' . time() . '_' . rand(100,999) . '.' . $ext;
    $dest     = $upload_dir . $new_name;
    if (!move_uploaded_file($file['tmp_name'], $dest)) return ['error' => 'Upload failed.'];

    if ($old_path && file_exists(__DIR__ . '/../' . $old_path)) @unlink(__DIR__ . '/../' . $old_path);
    return 'img/team/' . $new_name;
}

$member_types = [
    'malik'   => ['label' => 'Owner',     'color' => '#7c3aed', 'icon' => 'fa-crown'],
    'manager' => ['label' => 'Manager',   'color' => '#0284c7', 'icon' => 'fa-user-tie'],
    'karigar' => ['label' => 'Employee',  'color' => '#059669', 'icon' => 'fa-tools'],
];

// ── DELETE ────────────────────────────────────────────────────
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $dr = mysqli_query($conn, "SELECT photo_path FROM team_members WHERE id=$del_id");
    if ($dr && $row = mysqli_fetch_assoc($dr)) {
        if ($row['photo_path'] && file_exists(__DIR__ . '/../' . $row['photo_path']))
            @unlink(__DIR__ . '/../' . $row['photo_path']);
    }
    mysqli_query($conn, "DELETE FROM team_members WHERE id=$del_id");
    $msg = 'Team member deleted.'; $msg_type = 'success';
}

// ── TOGGLE ACTIVE ─────────────────────────────────────────────
if (isset($_GET['toggle'])) {
    $tid = (int)$_GET['toggle'];
    mysqli_query($conn, "UPDATE team_members SET is_active = 1 - is_active WHERE id=$tid");
    header('Location: manage_team.php'); exit;
}

// ── ADD / EDIT ────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $edit_id     = (int)($_POST['edit_id'] ?? 0);
    $name        = mysqli_real_escape_string($conn, trim($_POST['name'] ?? ''));
    $designation = mysqli_real_escape_string($conn, trim($_POST['designation'] ?? ''));
    $member_type = in_array($_POST['member_type'] ?? '', array_keys($member_types)) ? $_POST['member_type'] : 'karigar';
    $description = mysqli_real_escape_string($conn, trim($_POST['description'] ?? ''));
    $phone       = mysqli_real_escape_string($conn, trim($_POST['phone'] ?? ''));
    $disp_order  = (int)($_POST['display_order'] ?? 0);
    $is_active   = isset($_POST['is_active']) ? 1 : 0;
    $old_path    = mysqli_real_escape_string($conn, trim($_POST['old_photo'] ?? ''));

    if (empty($name)) { $msg = 'Name is required.'; $msg_type = 'danger'; }
    else {
        $photo_result = uploadTeamPhoto('team_photo', $old_path);
        if (is_array($photo_result) && isset($photo_result['error'])) {
            $msg = $photo_result['error']; $msg_type = 'danger';
        } else {
            $photo_path = mysqli_real_escape_string($conn, $photo_result);
            if ($edit_id > 0) {
                $sql = "UPDATE team_members SET name='$name', designation='$designation',
                        member_type='$member_type', description='$description', phone='$phone',
                        display_order=$disp_order, is_active=$is_active"
                     . ($photo_path ? ", photo_path='$photo_path'" : "")
                     . " WHERE id=$edit_id";
                mysqli_query($conn, $sql);
                $msg = 'Team member updated!'; $msg_type = 'success';
            } else {
                mysqli_query($conn, "INSERT INTO team_members
                    (name, designation, member_type, description, phone, photo_path, display_order, is_active)
                    VALUES ('$name','$designation','$member_type','$description','$phone',
                    " . ($photo_path ? "'$photo_path'" : "NULL") . ",$disp_order,$is_active)");
                $msg = 'Team member added!'; $msg_type = 'success';
            }
        }
    }
}

// ── Load edit ─────────────────────────────────────────────────
if (isset($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    $er  = mysqli_query($conn, "SELECT * FROM team_members WHERE id=$eid");
    if ($er) $edit_item = mysqli_fetch_assoc($er);
}

// ── Fetch all members grouped by type ─────────────────────────
$all_members = [];
$mq = mysqli_query($conn, "SELECT * FROM team_members ORDER BY display_order ASC, id ASC");
while ($r = mysqli_fetch_assoc($mq)) $all_members[$r['member_type']][] = $r;

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<style>
.team-card { border-radius:16px; overflow:hidden; box-shadow:0 4px 18px rgba(0,0,0,.07); background:#fff; transition:transform .25s; }
.team-card:hover { transform:translateY(-4px); }
.team-photo { width:80px; height:80px; border-radius:50%; object-fit:cover; border:3px solid #e2e8f0; }
.member-type-badge { font-size:11px; font-weight:700; padding:4px 10px; border-radius:50px; }
.section-heading { font-size:13px; font-weight:800; text-transform:uppercase; letter-spacing:.8px; padding:6px 14px; border-radius:8px; display:inline-block; }
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
                    <i class="fas fa-<?= $edit_item ? 'user-edit' : 'user-plus' ?> me-2"></i>
                    <?= $edit_item ? 'Edit Member' : 'Add Team Member' ?>
                </h5>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="card-body">
                    <input type="hidden" name="edit_id"   value="<?= $edit_item['id'] ?? 0 ?>">
                    <input type="hidden" name="old_photo" value="<?= htmlspecialchars($edit_item['photo_path'] ?? '') ?>">

                    <!-- Member Type (Section) -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Vibhag (Type) <span class="text-danger">*</span></label>
                        <div class="d-flex gap-2 flex-wrap">
                            <?php foreach ($member_types as $key => $mt): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="member_type"
                                       id="type_<?= $key ?>" value="<?= $key ?>"
                                       <?= (($edit_item['member_type'] ?? 'karigar') === $key) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-bold" for="type_<?= $key ?>"
                                       style="color:<?= $mt['color'] ?>;">
                                    <i class="fas <?= $mt['icon'] ?> me-1"></i><?= $mt['label'] ?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Name -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name"
                               value="<?= htmlspecialchars($edit_item['name'] ?? '') ?>"
                               placeholder="e.g. Mohit Patel" required>
                    </div>

                    <!-- Designation -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Designation / Haudo</label>
                        <input type="text" class="form-control" name="designation"
                               value="<?= htmlspecialchars($edit_item['designation'] ?? '') ?>"
                               placeholder="e.g. Head Technician, Owner, Service Manager">
                    </div>

                    <!-- Phone -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Phone <small class="text-muted">(optional)</small></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            <input type="text" class="form-control" name="phone"
                                   value="<?= htmlspecialchars($edit_item['phone'] ?? '') ?>"
                                   placeholder="+91 XXXXXXXXXX">
                        </div>
                    </div>

                    <!-- Photo -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            Photo <?= $edit_item ? '<small class="text-muted">(leave blank to keep)</small>' : '' ?>
                        </label>
                        <?php if ($edit_item && $edit_item['photo_path']): ?>
                        <div class="mb-2">
                            <img src="../<?= htmlspecialchars($edit_item['photo_path']) ?>"
                                 class="team-photo" alt="">
                        </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" name="team_photo"
                               accept="image/*" id="teamPhotoInput">
                        <img id="teamPhotoPreview" style="display:none;width:80px;height:80px;border-radius:50%;object-fit:cover;margin-top:8px;" alt="">
                        <small class="text-muted">Max 5MB. JPG/PNG/WEBP.</small>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Short Bio <small class="text-muted">(optional)</small></label>
                        <textarea class="form-control" name="description" rows="2"
                                  placeholder="Experience, specialty, etc."><?= htmlspecialchars($edit_item['description'] ?? '') ?></textarea>
                    </div>

                    <!-- Display Order -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Display Order</label>
                        <input type="number" class="form-control" name="display_order"
                               value="<?= $edit_item['display_order'] ?? 0 ?>" min="0">
                    </div>

                    <!-- Active -->
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="isActive"
                               <?= ($edit_item['is_active'] ?? 1) ? 'checked' : '' ?>>
                        <label class="form-check-label fw-bold" for="isActive">Show on Website</label>
                    </div>
                </div>
                <div class="card-footer bg-white text-end py-3">
                    <?php if ($edit_item): ?>
                        <a href="manage_team.php" class="btn btn-secondary rounded-pill me-2">Cancel</a>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        <i class="fas fa-save me-1"></i> <?= $edit_item ? 'Update' : 'Add Member' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== TEAM GRID (Grouped by Type) ===== -->
    <div class="col-lg-8 mb-4">
        <div class="card shadow-sm rounded-3">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold text-dark">
                    <i class="fas fa-users text-primary me-2"></i> Our Team
                    <span class="badge bg-primary ms-2"><?= array_sum(array_map('count', $all_members)) ?></span>
                </h5>
                <a href="../portfolio.php#team" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill">
                    <i class="fas fa-external-link-alt me-1"></i> View Live
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($all_members)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-users fa-3x mb-3 opacity-25"></i>
                        <p>No team members yet. Add your first member!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($member_types as $type_key => $type_info): ?>
                        <?php if (empty($all_members[$type_key])) continue; ?>
                        <div class="mb-4">
                            <div class="mb-3">
                                <span class="section-heading text-white"
                                      style="background:<?= $type_info['color'] ?>;">
                                    <i class="fas <?= $type_info['icon'] ?> me-1"></i>
                                    <?= $type_info['label'] ?>
                                    <span class="badge bg-white ms-2" style="color:<?= $type_info['color'] ?>;font-size:11px;">
                                        <?= count($all_members[$type_key]) ?>
                                    </span>
                                </span>
                            </div>
                            <div class="row g-3">
                                <?php foreach ($all_members[$type_key] as $member): ?>
                                <div class="col-md-6">
                                    <div class="team-card p-3 <?= $member['is_active'] ? '' : 'opacity-50' ?>">
                                        <div class="d-flex align-items-center gap-3 mb-2">
                                            <?php if ($member['photo_path']): ?>
                                                <img src="../<?= htmlspecialchars($member['photo_path']) ?>"
                                                     class="team-photo" alt="<?= htmlspecialchars($member['name']) ?>"
                                                     onerror="this.src='../img/m.jpg'">
                                            <?php else: ?>
                                                <div class="team-photo d-flex align-items-center justify-content-center text-white fw-bold"
                                                     style="background:<?= $type_info['color'] ?>;font-size:24px;flex-shrink:0;">
                                                    <?= strtoupper(substr($member['name'],0,1)) ?>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="fw-bold text-dark" style="font-size:14px;">
                                                    <?= htmlspecialchars($member['name']) ?>
                                                </div>
                                                <small class="text-muted d-block">
                                                    <?= htmlspecialchars($member['designation']) ?>
                                                </small>
                                                <?php if ($member['phone']): ?>
                                                <small class="text-primary">
                                                    <i class="fas fa-phone me-1" style="font-size:10px;"></i>
                                                    <?= htmlspecialchars($member['phone']) ?>
                                                </small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php if ($member['description']): ?>
                                        <p class="text-muted mb-2" style="font-size:12px;line-height:1.4;">
                                            <?= htmlspecialchars($member['description']) ?>
                                        </p>
                                        <?php endif; ?>
                                        <div class="d-flex gap-1">
                                            <a href="manage_team.php?edit=<?= $member['id'] ?>"
                                               class="btn btn-warning btn-sm rounded-pill flex-fill" style="font-size:11px;">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="manage_team.php?toggle=<?= $member['id'] ?>"
                                               class="btn btn-outline-secondary btn-sm rounded-pill" style="font-size:11px;" title="Show/Hide">
                                                <i class="fas fa-eye<?= $member['is_active'] ? '-slash' : '' ?>"></i>
                                            </a>
                                            <a href="manage_team.php?delete=<?= $member['id'] ?>"
                                               class="btn btn-danger btn-sm rounded-pill" style="font-size:11px;"
                                               onclick="return confirm('Delete <?= htmlspecialchars($member['name']) ?>?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('teamPhotoInput')?.addEventListener('change', function() {
    const preview = document.getElementById('teamPhotoPreview');
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; };
        reader.readAsDataURL(this.files[0]);
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
