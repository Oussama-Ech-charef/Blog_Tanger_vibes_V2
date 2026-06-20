<?php
$page_title = 'Notifications';
require_once __DIR__ . '/init.php';

// Mark all as read
if (isset($_POST['mark_all_read']) && validate_csrf_token($_POST['csrf_token'] ?? '')) {
    $conn->prepare("UPDATE user_notifications SET is_read=1 WHERE id_user=:uid")->execute([':uid' => $uid]);
    header('Location: notifications.php');
    exit();
}

// Mark single as read (redirect to link)
if (isset($_POST['mark_read']) && is_numeric($_POST['mark_read'])) {
    if (validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $nid = (int)$_POST['mark_read'];
        $s = $conn->prepare("SELECT link, id_user FROM user_notifications WHERE id_notification=:id");
        $s->execute([':id' => $nid]);
        $n = $s->fetch(PDO::FETCH_ASSOC);
        if ($n && (int)$n['id_user'] === $uid) {
            $conn->prepare("UPDATE user_notifications SET is_read=1 WHERE id_notification=:id")->execute([':id' => $nid]);
            if (!empty($n['link'])) {
                $link = $n['link'];
                $parsed = parse_url($link);
                if (isset($parsed['scheme']) || isset($parsed['host'])) {
                    $link = 'notifications.php';
                }
                header('Location: ' . $link);
                exit();
            }
        }
    }
    header('Location: notifications.php');
    exit();
}

// Fetch notifications
$notifs = $conn->prepare("SELECT * FROM user_notifications WHERE id_user=:uid ORDER BY created_at DESC LIMIT 50");
$notifs->execute([':uid' => $uid]);
$notifications = $notifs->fetchAll(PDO::FETCH_ASSOC);

$unread_count = 0;
foreach ($notifications as $n) {
    if (!$n['is_read']) $unread_count++;
}

require_once __DIR__ . '/inc/header.php';
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <div>
        <h2 style="margin:0;font-size:20px;font-weight:700;">
            <i class="fa-solid fa-bell" style="color:var(--db-primary);margin-right:8px;" aria-hidden="true"></i> Notifications
        </h2>
        <p style="margin:4px 0 0;font-size:13px;color:var(--db-text-secondary);">
            <?= $unread_count ?> unread notification<?= $unread_count !== 1 ? 's' : '' ?>
        </p>
    </div>
    <?php if ($unread_count > 0): ?>
    <form method="POST" action="notifications.php">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <button type="submit" name="mark_all_read" class="btn btn_secondary btn_sm" style="display:inline-flex;align-items:center;gap:6px;">
            <i class="fa-solid fa-check-double" aria-hidden="true"></i> Mark All as Read
        </button>
    </form>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card_body" style="padding:0;">
        <?php if (!empty($notifications)): ?>
            <?php foreach ($notifications as $n): ?>
            <div style="display:flex;align-items:flex-start;gap:14px;padding:16px 24px;border-bottom:1px solid var(--db-card-border);<?= !$n['is_read'] ? 'background:#F8FAFC;' : '' ?>">
                <div style="flex-shrink:0;width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;
                    <?php
                    $type_colors = [
                        'post_approved' => 'background:#D1FAE5;color:#065F46;',
                        'post_rejected' => 'background:#FEE2E2;color:#991B1B;',
                        'new_comment' => 'background:#DBEAFE;color:#1E40AF;',
                        'system' => 'background:#FEF3C7;color:#92400E;',
                    ];
                    echo $type_colors[$n['type']] ?? 'background:#F1F5F9;color:#475569;';
                    ?>">
                    <?php
                    $type_icons = [
                        'post_approved' => '<i class="fa-solid fa-check" aria-hidden="true"></i>',
                        'post_rejected' => '<i class="fa-solid fa-ban" aria-hidden="true"></i>',
                        'new_comment' => '<i class="fa-solid fa-comment" aria-hidden="true"></i>',
                        'system' => '<i class="fa-solid fa-info" aria-hidden="true"></i>',
                    ];
                    echo $type_icons[$n['type']] ?? '<i class="fa-solid fa-circle" aria-hidden="true"></i>';
                    ?>
                </div>
                <div style="flex:1;min-width:0;">
                    <p style="margin:0;font-size:14px;color:var(--db-text-primary);"><?= htmlspecialchars($n['message']) ?></p>
                    <p style="margin:4px 0 0;font-size:12px;color:var(--db-text-secondary);"><?= time_ago($n['created_at']) ?></p>
                </div>
                <div style="flex-shrink:0;">
                    <?php if (!$n['is_read']): ?>
                    <form method="POST" action="notifications.php" style="display:inline;">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="mark_read" value="<?= $n['id_notification'] ?>">
                        <button type="submit" class="btn_small btn_secondary" style="border:none;cursor:pointer;">
                            <i class="fa-solid fa-check" aria-hidden="true"></i> Read
                        </button>
                    </form>
                    <?php endif; ?>
                    <?php if (!empty($n['link']) && $n['is_read']): ?>
                    <a href="<?= htmlspecialchars($n['link']) ?>" class="btn_small btn_secondary" style="text-decoration:none;">
                        <i class="fa-solid fa-eye" aria-hidden="true"></i> View
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="text-align:center;padding:60px 20px;color:var(--db-text-secondary);">
                <i class="fa-solid fa-bell" style="font-size:40px;display:block;margin-bottom:16px;color:var(--db-text-muted);" aria-hidden="true"></i>
                <h3 style="font-size:16px;margin:0 0 4px;">No notifications</h3>
                <p style="font-size:13px;margin:0;">You'll be notified when your posts are approved, rejected, or when someone comments.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
