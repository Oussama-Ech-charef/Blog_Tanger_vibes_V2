<?php
$page_title = 'Settings';
require_once __DIR__ . '/init.php';
$message = ''; $message_type = '';

function get_setting($conn, $key, $default = '') {
    static $cache = [];
    if (!isset($cache[$key])) {
        $s = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key=:k");
        $s->execute([':k'=>$key]);
        $v = $s->fetchColumn();
        $cache[$key] = $v !== false ? $v : $default;
    }
    return $cache[$key];
}
function update_setting($conn, $key, $value) {
    $conn->prepare("INSERT INTO settings (setting_key,setting_value) VALUES (:k,:v) ON DUPLICATE KEY UPDATE setting_value=:v2")
         ->execute([':k'=>$key,':v'=>$value,':v2'=>$value]);
}

// Save settings
if (isset($_POST['update_settings']) && validate_csrf_token($_POST['csrf_token']??'')) {
    $sn = trim($_POST['site_name'] ?? '');
    if (!empty($sn)) {
        update_setting($conn, 'site_name', $sn);
        update_setting($conn, 'site_description', trim($_POST['site_description']??''));
        update_setting($conn, 'admin_email', trim($_POST['admin_email']??''));
        update_setting($conn, 'posts_per_page', (string)((int)($_POST['posts_per_page']??6)));
        $message = 'Settings saved.'; $message_type = 'success';
    } else { $message = 'Site name required.'; $message_type = 'error'; }
}

// Change password
if (isset($_POST['change_password']) && validate_csrf_token($_POST['csrf_token']??'')) {
    $cur = $_POST['current_password']??''; $new = $_POST['new_password']??''; $conf = $_POST['confirm_password']??'';
    $s = $conn->prepare("SELECT password FROM users WHERE id_user=:id");
    $s->execute([':id'=>$_SESSION['id_user']]);
    $hash = $s->fetchColumn();
    if (!password_verify($cur, $hash)) { $message = 'Current password incorrect.'; $message_type = 'error'; }
    elseif (strlen($new) < 6) { $message = 'Min 6 characters.'; $message_type = 'error'; }
    elseif ($new !== $conf) { $message = 'Passwords do not match.'; $message_type = 'error'; }
    else {
        $conn->prepare("UPDATE users SET password=:p WHERE id_user=:id")->execute([':p'=>password_hash($new, PASSWORD_DEFAULT), ':id'=>$_SESSION['id_user']]);
        $message = 'Password changed.'; $message_type = 'success';
    }
}

// Upload logo
if (isset($_POST['upload_logo']) && validate_csrf_token($_POST['csrf_token']??'') && isset($_FILES['logo'])) {
    $errs = validate_uploaded_image($_FILES['logo']);
    if (empty($errs)) {
        $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        $fn = 'logo_' . bin2hex(random_bytes(8)) . '.' . $ext;
        if (move_uploaded_file($_FILES['logo']['tmp_name'], __DIR__.'/../assets/images/'.$fn)) {
            update_setting($conn, 'logo_path', 'assets/images/'.$fn);
            $message = 'Logo uploaded.'; $message_type = 'success';
        } else { $message = 'Upload failed.'; $message_type = 'error'; }
    } else { $message = implode(' ', $errs); $message_type = 'error'; }
}

$site_name = get_setting($conn, 'site_name', 'Tangier Vibes');
$site_desc = get_setting($conn, 'site_description', '');
$admin_email = get_setting($conn, 'admin_email', '');
$posts_per_page = (int)get_setting($conn, 'posts_per_page', '6');
$logo_path = get_setting($conn, 'logo_path', 'assets/images/logo.png');

$ai = $conn->prepare("SELECT user_name, email, created_at FROM users WHERE id_user=:id");
$ai->execute([':id'=>$_SESSION['id_user']]);
$admin_info = $ai->fetch(PDO::FETCH_ASSOC);

require_once __DIR__ . '/inc/header.php';
?>

<?php render_notification($message, $message_type); ?>

<div class="grid_2col">
    <div class="card">
        <div class="card_header"><h2><i class="fa-solid fa-globe icon_primary" aria-hidden="true"></i>Site Settings</h2></div>
        <div class="card_body">
            <form method="POST" action="settings.php">
                <input type="hidden" name="csrf_token" value="<?=$csrf_token?>"><input type="hidden" name="update_settings" value="1">
                <div class="form_group"><label for="site_name">Site Name</label><input type="text" id="site_name" name="site_name" value="<?=htmlspecialchars($site_name)?>" required maxlength="255"></div>
                <div class="form_group"><label for="site_description">Description</label><textarea id="site_description" name="site_description" maxlength="500"><?=htmlspecialchars($site_desc)?></textarea><span class="form_hint">Used in meta tags.</span></div>
                <div class="form_group"><label for="admin_email">Admin Email</label><input type="email" id="admin_email" name="admin_email" value="<?=htmlspecialchars($admin_email)?>"></div>
                <div class="form_group"><label for="posts_per_page">Posts Per Page</label><input type="number" id="posts_per_page" name="posts_per_page" value="<?=$posts_per_page?>" min="3" max="50"></div>
                <button type="submit" class="btn btn_primary"><i class="fa-solid fa-save" aria-hidden="true"></i> Save Settings</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card_header"><h2><i class="fa-solid fa-image icon_primary" aria-hidden="true"></i>Site Logo</h2></div>
        <div class="card_body">
            <div style="margin-bottom:20px;"><p style="font-size:14px;color:var(--db-text-secondary);margin:0 0 12px;">Current:</p><img src="../<?=htmlspecialchars($logo_path)?>" alt="Logo" style="max-height:60px;border-radius:8px;border:1px solid var(--db-card-border);padding:8px;"></div>
            <form method="POST" action="settings.php" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?=$csrf_token?>"><input type="hidden" name="upload_logo" value="1">
                <div class="form_group"><label for="logo">Upload New Logo</label><input type="file" id="logo" name="logo" accept="image/png,image/jpeg,image/webp" required></div>
                <button type="submit" class="btn btn_primary"><i class="fa-solid fa-upload" aria-hidden="true"></i> Upload</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card_header"><h2><i class="fa-solid fa-user icon_primary" aria-hidden="true"></i>Admin Profile</h2></div>
        <div class="card_body">
            <?php if ($admin_info): ?>
            <div style="display:grid;gap:16px;">
                <div><div class="detail_label">Username</div><div class="detail_value"><?=htmlspecialchars($admin_info['user_name'])?></div></div>
                <div><div class="detail_label">Email</div><div class="detail_value"><?=htmlspecialchars($admin_info['email'])?></div></div>
                <div><div class="detail_label">Member Since</div><div class="detail_value"><?=date('F j, Y',strtotime($admin_info['created_at']))?></div></div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card_header"><h2><i class="fa-solid fa-lock icon_primary" aria-hidden="true"></i>Change Password</h2></div>
        <div class="card_body">
            <form method="POST" action="settings.php">
                <input type="hidden" name="csrf_token" value="<?=$csrf_token?>"><input type="hidden" name="change_password" value="1">
                <div class="form_group"><label for="current_password">Current</label><input type="password" id="current_password" name="current_password" required autocomplete="current-password"></div>
                <div class="form_row">
                    <div class="form_group"><label for="new_password">New Password</label><input type="password" id="new_password" name="new_password" required minlength="6" autocomplete="new-password"></div>
                    <div class="form_group"><label for="confirm_password">Confirm</label><input type="password" id="confirm_password" name="confirm_password" required minlength="6" autocomplete="new-password"></div>
                </div>
                <button type="submit" class="btn btn_primary"><i class="fa-solid fa-key" aria-hidden="true"></i> Change Password</button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
