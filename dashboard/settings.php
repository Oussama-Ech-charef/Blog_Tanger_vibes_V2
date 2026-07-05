<?php
$page_title = 'Settings';
require_once __DIR__ . '/init.php';
$message = ''; $message_type = '';

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

$ai = $conn->prepare("SELECT user_name, email, created_at FROM users WHERE id_user=:id");
$ai->execute([':id'=>$_SESSION['id_user']]);
$admin_info = $ai->fetch(PDO::FETCH_ASSOC);

require_once __DIR__ . '/inc/header.php';
?>

<?php render_notification($message, $message_type); ?>

<div class="grid_2col">
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
