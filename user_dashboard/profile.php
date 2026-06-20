<?php
$page_title = 'Profile';
require_once __DIR__ . '/init.php';
$errors = [];
$success = '';

// Fetch current user data
try {
    $s = $conn->prepare("SELECT user_name, email, avatar FROM users WHERE id_user=:id");
    $s->execute([':id' => $uid]);
    $user = $s->fetch(PDO::FETCH_ASSOC);
    $avatar_col = true;
} catch (PDOException $e) {
    $s = $conn->prepare("SELECT user_name, email FROM users WHERE id_user=:id");
    $s->execute([':id' => $uid]);
    $user = $s->fetch(PDO::FETCH_ASSOC);
    $user['avatar'] = null;
    $avatar_col = false;
}
if (!$user) {
    header('Location: ../pages/index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $new_name = trim($_POST['user_name'] ?? '');
    $new_email = trim($_POST['email'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) $errors[] = 'Invalid request.';
    if (empty($new_name)) $errors[] = 'Username is required.';
    if (empty($new_email)) $errors[] = 'Email is required.';
    elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format.';

    // Check email uniqueness
    if ($new_email !== $user['email']) {
        $c = $conn->prepare("SELECT COUNT(*) FROM users WHERE email=:e AND id_user!=:id");
        $c->execute([':e' => $new_email, ':id' => $uid]);
        if ((int)$c->fetchColumn() > 0) $errors[] = 'Email already in use.';
    }

    // Avatar upload
    $avatar_path = null;
    if (!empty($_FILES['avatar']['name']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES['avatar']['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime, $allowed)) {
            $errors[] = 'Avatar must be JPEG, PNG or WebP.';
        } elseif ($_FILES['avatar']['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Avatar must be under 2MB.';
        } else {
            $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
            $fn = 'avatar_' . $uid . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], __DIR__ . '/../assets/uploads/' . $fn)) {
                $avatar_path = 'assets/uploads/' . $fn;
                // Delete old avatar
                if (!empty($user['avatar']) && file_exists(__DIR__ . '/../' . $user['avatar'])) {
                    @unlink(__DIR__ . '/../' . $user['avatar']);
                }
            } else { $errors[] = 'Failed to upload avatar.'; }
        }
    }

    // Password change
    if (!empty($new_password)) {
        if (empty($current_password)) $errors[] = 'Current password is required to set a new password.';
        elseif ($new_password !== $confirm_password) $errors[] = 'New passwords do not match.';
        elseif (strlen($new_password) < 6) $errors[] = 'New password must be at least 6 characters.';
        else {
            // Verify current password
            $pw = $conn->prepare("SELECT password FROM users WHERE id_user=:id");
            $pw->execute([':id' => $uid]);
            $hash = $pw->fetchColumn();
            if (!password_verify($current_password, $hash)) $errors[] = 'Current password is incorrect.';
        }
    }

    if (empty($errors)) {
        try {
            $sql = "UPDATE users SET user_name=:n, email=:e";
            $params = [':n' => $new_name, ':e' => $new_email, ':id' => $uid];

            if ($avatar_path && $avatar_col) {
                $sql .= ", avatar=:av";
                $params[':av'] = $avatar_path;
            } elseif ($avatar_path && !$avatar_col) {
                $errors[] = 'Avatar upload is not available yet. Run the database migration.';
            }

            if (!empty($new_password)) {
                $sql .= ", password=:pw";
                $params[':pw'] = password_hash($new_password, PASSWORD_DEFAULT);
            }

            $sql .= " WHERE id_user=:id";
            $conn->prepare($sql)->execute($params);

            // Update session
            $_SESSION['user_name'] = $new_name;
            if ($avatar_path) {
                $_SESSION['user_avatar'] = $avatar_path;
            }

            $success = 'Profile updated successfully.';
            $user['user_name'] = $new_name;
            $user['email'] = $new_email;
            if ($avatar_path) $user['avatar'] = $avatar_path;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $errors[] = 'Database error.';
        }
    }
}

require_once __DIR__ . '/inc/header.php';
?>

<?php if (!empty($success)): render_notification($success, 'success'); endif; ?>
<?php if (!empty($errors)): render_notification(implode(' | ', array_map('htmlspecialchars', $errors)), 'error'); endif; ?>

<div class="card">
    <div class="card_header">
        <h2><i class="fa-solid fa-user" style="color:var(--db-primary);margin-right:8px;" aria-hidden="true"></i> Edit Profile</h2>
    </div>
    <div class="card_body">
        <form method="POST" action="profile.php" enctype="multipart/form-data" style="max-width:600px;">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="update_profile" value="1">

            <!-- Avatar -->
            <div class="form_group" style="margin-bottom:24px;">
                <label style="display:block;font-weight:600;font-size:14px;color:var(--db-text-primary);margin-bottom:8px;">Avatar</label>
                <div style="display:flex;align-items:center;gap:16px;">
                    <?php if (!empty($user['avatar'])): ?>
                        <img src="../<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar" class="avatar_preview">
                    <?php else: ?>
                        <div class="avatar_placeholder"><?= avatar_initials($user['user_name']) ?></div>
                    <?php endif; ?>
                    <div>
                        <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp">
                        <span style="font-size:12px;color:var(--db-text-muted);display:block;margin-top:4px;">JPEG, PNG or WebP. Max 2MB.</span>
                    </div>
                </div>
            </div>

            <div class="form_group" style="margin-bottom:20px;">
                <label for="user_name" style="display:block;font-weight:600;font-size:14px;color:var(--db-text-primary);margin-bottom:6px;">Username</label>
                <input type="text" id="user_name" name="user_name" value="<?= htmlspecialchars($user['user_name']) ?>" required style="width:100%;padding:10px 14px;border:1px solid var(--db-input-border);border-radius:var(--db-input-radius);font-size:14px;font-family:inherit;box-sizing:border-box;">
            </div>

            <div class="form_group" style="margin-bottom:20px;">
                <label for="email" style="display:block;font-weight:600;font-size:14px;color:var(--db-text-primary);margin-bottom:6px;">Email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required style="width:100%;padding:10px 14px;border:1px solid var(--db-input-border);border-radius:var(--db-input-radius);font-size:14px;font-family:inherit;box-sizing:border-box;">
            </div>

            <hr style="border:none;border-top:1px solid var(--db-card-border);margin:24px 0;">

            <h3 style="font-size:14px;font-weight:600;color:var(--db-text-primary);margin:0 0 16px;">Change Password (leave blank to keep current)</h3>

            <div class="form_group" style="margin-bottom:20px;">
                <label for="current_password" style="display:block;font-weight:600;font-size:14px;color:var(--db-text-primary);margin-bottom:6px;">Current Password</label>
                <input type="password" id="current_password" name="current_password" style="width:100%;padding:10px 14px;border:1px solid var(--db-input-border);border-radius:var(--db-input-radius);font-size:14px;font-family:inherit;box-sizing:border-box;">
            </div>

            <div class="form_row" style="display:flex;gap:16px;">
                <div class="form_group" style="flex:1;margin-bottom:20px;">
                    <label for="new_password" style="display:block;font-weight:600;font-size:14px;color:var(--db-text-primary);margin-bottom:6px;">New Password</label>
                    <input type="password" id="new_password" name="new_password" style="width:100%;padding:10px 14px;border:1px solid var(--db-input-border);border-radius:var(--db-input-radius);font-size:14px;font-family:inherit;box-sizing:border-box;">
                </div>
                <div class="form_group" style="flex:1;margin-bottom:20px;">
                    <label for="confirm_password" style="display:block;font-weight:600;font-size:14px;color:var(--db-text-primary);margin-bottom:6px;">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" style="width:100%;padding:10px 14px;border:1px solid var(--db-input-border);border-radius:var(--db-input-radius);font-size:14px;font-family:inherit;box-sizing:border-box;">
                </div>
            </div>

            <div class="form_actions" style="margin-top:24px;">
                <button type="submit" class="btn btn_primary"><i class="fa-solid fa-save" aria-hidden="true"></i> Save Changes</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
