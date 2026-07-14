<?php
    global $is_admin, $conn;
    $csrf_token = $csrf_token ?? get_csrf_token();

    // AJAX: mark today's notifications as read (called from JS on dropdown open)
    if (isset($_POST['ajax_notif_read'])) {
        header('Content-Type: application/json');
        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'error' => 'invalid csrf']);
            exit;
        }
        try {
            $uid = current_user_id();
            if ($is_admin) {
                $conn->prepare("UPDATE activity_log SET is_read=1 WHERE is_read=0 AND DATE(created_at) = CURDATE() AND action_type NOT IN ('draft_saved') AND (user_id IS NULL OR user_id IN (SELECT id_user FROM users WHERE role='user'))")->execute();
            } else {
                $conn->prepare("UPDATE activity_log al SET al.is_read=1 WHERE al.is_read=0 AND DATE(al.created_at)=CURDATE() AND al.action_type NOT IN ('draft_saved') AND al.user_id!=:uid AND ((al.action_type IN ('post_approved','post_rejected','post_deleted') AND al.entity_type='post' AND EXISTS(SELECT 1 FROM posts p WHERE p.id_post=al.entity_id AND p.id_user=:uid2)) OR (al.action_type='comment_added' AND al.entity_type='comment' AND EXISTS(SELECT 1 FROM comments c JOIN posts p ON c.id_post=p.id_post WHERE c.id_comment=al.entity_id AND p.id_user=:uid3)))")->execute([':uid'=>$uid,':uid2'=>$uid,':uid3'=>$uid]);
            }
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false]);
        }
        exit;
    }

    // Notification type icons and colors (mirrors the mapping on notifications.php)
    $notif_type_info = [
        'post_created'     => ['icon' => 'fa-solid fa-plus',          'color' => '#10B981', 'bg' => '#D1FAE5'],
        'post_submitted'   => ['icon' => 'fa-solid fa-paper-plane',   'color' => '#3B82F6', 'bg' => '#DBEAFE'],
        'post_approved'    => ['icon' => 'fa-solid fa-check-circle',  'color' => '#059669', 'bg' => '#D1FAE5'],
        'post_rejected'    => ['icon' => 'fa-solid fa-ban',           'color' => '#DC2626', 'bg' => '#FEE2E2'],
        'post_updated'     => ['icon' => 'fa-solid fa-pen',           'color' => '#7C3AED', 'bg' => '#EDE9FE'],
        'post_deleted'     => ['icon' => 'fa-solid fa-trash',         'color' => '#EF4444', 'bg' => '#FEE2E2'],
        'comment_added'    => ['icon' => 'fa-solid fa-comment',       'color' => '#0047AB', 'bg' => '#E8F0FE'],
        'user_registered'  => ['icon' => 'fa-solid fa-user-plus',     'color' => '#7C3AED', 'bg' => '#EDE9FE'],
        'message_received' => ['icon' => 'fa-solid fa-envelope',      'color' => '#D97706', 'bg' => '#FEF3C7'],
    ];

    $notif_type_labels = [
        'post_created'     => 'New post',
        'post_submitted'   => 'Post submitted',
        'post_approved'    => 'Post approved',
        'post_rejected'    => 'Post rejected',
        'post_updated'     => 'Post updated',
        'post_deleted'     => 'Post deleted',
        'comment_added'    => 'New comment',
        'user_registered'  => 'New user',
        'message_received' => 'New message',
    ];

    // Today's notification count (for the badge)
    $unread_today = 0;
    $today_notifs = [];
    $uid = current_user_id();
    try {
        if ($is_admin) {
            $cnt_stmt = $conn->prepare("SELECT COUNT(*) FROM activity_log WHERE is_read=0 AND DATE(created_at) = CURDATE() AND action_type NOT IN ('draft_saved') AND (user_id IS NULL OR user_id IN (SELECT id_user FROM users WHERE role='user'))");
            $cnt_stmt->execute();
        } else {
            $cnt_stmt = $conn->prepare("SELECT COUNT(*) FROM activity_log al WHERE is_read=0 AND DATE(created_at)=CURDATE() AND action_type NOT IN ('draft_saved') AND al.user_id!=:uid AND ((al.action_type IN ('post_approved','post_rejected','post_deleted') AND al.entity_type='post' AND EXISTS(SELECT 1 FROM posts p WHERE p.id_post=al.entity_id AND p.id_user=:uid2)) OR (al.action_type='comment_added' AND al.entity_type='comment' AND EXISTS(SELECT 1 FROM comments c JOIN posts p ON c.id_post=p.id_post WHERE c.id_comment=al.entity_id AND p.id_user=:uid3)))");
            $cnt_stmt->execute([':uid'=>$uid,':uid2'=>$uid,':uid3'=>$uid]);
        }
        $unread_today = (int)$cnt_stmt->fetchColumn();

        // Latest 5 notifications from today
        if ($is_admin) {
            $nq = $conn->prepare("SELECT id_activity, action_type, description, is_read, created_at FROM activity_log WHERE DATE(created_at) = CURDATE() AND action_type NOT IN ('draft_saved') AND (user_id IS NULL OR user_id IN (SELECT id_user FROM users WHERE role='user')) ORDER BY created_at DESC LIMIT 5");
            $nq->execute();
        } else {
            $nq = $conn->prepare("SELECT al.id_activity, al.action_type, al.description, al.is_read, al.created_at FROM activity_log al WHERE DATE(al.created_at)=CURDATE() AND al.action_type NOT IN ('draft_saved') AND al.user_id!=:uid AND ((al.action_type IN ('post_approved','post_rejected','post_deleted') AND al.entity_type='post' AND EXISTS(SELECT 1 FROM posts p WHERE p.id_post=al.entity_id AND p.id_user=:uid2)) OR (al.action_type='comment_added' AND al.entity_type='comment' AND EXISTS(SELECT 1 FROM comments c JOIN posts p ON c.id_post=p.id_post WHERE c.id_comment=al.entity_id AND p.id_user=:uid3))) ORDER BY al.created_at DESC LIMIT 5");
            $nq->execute([':uid'=>$uid,':uid2'=>$uid,':uid3'=>$uid]);
        }
        $today_notifs = $nq->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log('Notification query failed: ' . $e->getMessage());
    }
?>
<!DOCTYPE html>
<html lang="<?= get_lang_code() ?>" dir="<?= get_lang_dir() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? __('dashboard_label')) ?> — Tangier Vibes Admin</title>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../<?= asset_version('assets/css/public.min.css') ?>">
    <link rel="stylesheet" href="../<?= asset_version('assets/css/dashboard.min.css') ?>">
    <script>
var Lang = {
    csrfToken: <?= json_encode($csrf_token) ?>,
    confirmApprove: <?= json_encode(__('js_confirm_approve')) ?>,
    confirmDeletePost: <?= json_encode(__('js_confirm_delete_post')) ?>,
    confirmDeleteCat: <?= json_encode(__('categories_delete_confirm')) ?>,
    confirmBulkDelete: <?= json_encode(__('comments_confirm_bulk_delete')) ?>,
    confirmRejectComment: <?= json_encode(__('comments_confirm_reject')) ?>,
    confirmDeleteComment: <?= json_encode(__('comments_confirm_delete')) ?>,
    confirmDemote: <?= json_encode(__('users_confirm_demote')) ?>,
    confirmPromote: <?= json_encode(__('users_confirm_promote')) ?>,
    confirmDeactivate: <?= json_encode(__('users_confirm_deactivate')) ?>,
    confirmActivate: <?= json_encode(__('users_confirm_activate')) ?>,
    confirmDeleteUser: <?= json_encode(__('users_confirm_delete')) ?>,
    confirmDeleteMessage: <?= json_encode(__('messages_confirm_delete')) ?>,
    rejectModalTitle: <?= json_encode(__('js_reject_modal_title')) ?>,
    editorLinkUrl: <?= json_encode(__('editor_link_url')) ?>,
    editorImageUrl: <?= json_encode(__('editor_image_url')) ?>,
};
    </script>
</head>
<body class="dashboard_body">

<div class="sidebar_overlay" id="sidebarOverlay"></div>

<div class="dashboard_layout">

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar_logo">
            <img src="../assets/images/logo.png" alt="Tangier Vibes">
            <span>Tangier Vibes</span>
        </div>

        <nav class="sidebar_nav">
            <?php $sidebar_page = basename($_SERVER['PHP_SELF']); ?>
            <a href="index.php" class="sidebar_link <?= $sidebar_page === 'index.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-chart-pie" aria-hidden="true"></i>
                <span><?= __('sidebar_overview') ?></span>
            </a>
            <?php if ($is_admin): ?>
            <a href="posts.php" class="sidebar_link <?= $sidebar_page === 'posts.php' || $sidebar_page === 'add_post.php' || $sidebar_page === 'edit_post.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-file-lines" aria-hidden="true"></i>
                <span><?= __('sidebar_posts') ?></span>
            </a>
            <a href="notifications.php" class="sidebar_link <?= $sidebar_page === 'notifications.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-bell" aria-hidden="true"></i>
                <span><?= __('sidebar_notifications') ?></span>
            </a>
            <a href="comments.php" class="sidebar_link <?= $sidebar_page === 'comments.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-comments" aria-hidden="true"></i>
                <span><?= __('sidebar_comments') ?></span>
            </a>
            <a href="messages.php" class="sidebar_link <?= $sidebar_page === 'messages.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                <span><?= __('sidebar_messages') ?></span>
            </a>
            <a href="users.php" class="sidebar_link <?= $sidebar_page === 'users.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-users" aria-hidden="true"></i>
                <span><?= __('sidebar_users') ?></span>
            </a>
            <a href="categories.php" class="sidebar_link <?= $sidebar_page === 'categories.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-tags" aria-hidden="true"></i>
                <span><?= __('sidebar_categories') ?></span>
            </a>
            <?php else: ?>
            <a href="posts.php" class="sidebar_link <?= $sidebar_page === 'posts.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-file-lines" aria-hidden="true"></i>
                <span><?= __('sidebar_my_posts') ?></span>
            </a>
            <a href="add_post.php" class="sidebar_link <?= $sidebar_page === 'add_post.php' || $sidebar_page === 'edit_post.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-plus" aria-hidden="true"></i>
                <span><?= __('sidebar_add_post') ?></span>
            </a>
            <?php endif; ?>
            <a href="settings.php" class="sidebar_link <?= $sidebar_page === 'settings.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-gear" aria-hidden="true"></i>
                <span><?= __('sidebar_settings') ?></span>
            </a>
        </nav>

        <div class="sidebar_footer">
            <a href="../pages/index.php" class="sidebar_link">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                <span><?= __('sidebar_back_to_site') ?></span>
            </a>
            <form action="../pages/logout.php" method="POST" class="inline_form">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <button type="submit" class="sidebar_link logout_btn">
                    <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
                    <span><?= __('sidebar_logout') ?></span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="dashboard_main">

        <!-- Topbar -->
        <header class="dashboard_topbar">
            <div class="flex_row">
                <button class="mobile_menu_btn" id="mobileMenuBtn" aria-label="<?= __('topbar_mobile_menu_aria') ?>">
                    <i class="fa-solid fa-bars" aria-hidden="true"></i>
                </button>
                <h1><?= htmlspecialchars($page_title ?? __('dashboard_label')) ?></h1>
            </div>
            <div class="topbar_right">
                <a href="../pages/index.php" class="topbar_back">
                    <i class="fa-solid fa-eye" aria-hidden="true"></i> <?= __('topbar_view_site') ?>
                </a>
                <div class="notif_bell_wrap">
                    <button class="notif_bell_btn" id="notifBellBtn" aria-label="Toggle notifications">
                        <i class="fa-solid fa-bell" aria-hidden="true"></i>
                        <?php if ($unread_today > 0): ?>
                        <span class="notif_badge"><?= $unread_today ?></span>
                        <?php endif; ?>
                    </button>
                    <div class="notif_dropdown" id="notifDropdown">
                        <div class="notif_dropdown_header">
                            <span class="notif_dropdown_title">Today</span>
                        </div>
                        <div class="notif_dropdown_body">
                            <?php if (!empty($today_notifs)): ?>
                                <?php foreach ($today_notifs as $n): ?>
                                <?php $ti = $notif_type_info[$n['action_type']] ?? ['icon' => 'fa-solid fa-bell', 'color' => '#64748B', 'bg' => '#F1F5F9']; ?>
                                <?php $tl = $notif_type_labels[$n['action_type']] ?? 'Notification'; ?>
                                <div class="notif_dropdown_item <?= !$n['is_read'] ? 'unread' : '' ?>" data-id="<?= $n['id_activity'] ?>">
                                    <span class="notif_dd_icon" style="background: <?= $ti['bg'] ?>">
                                        <i class="<?= $ti['icon'] ?>" style="color: <?= $ti['color'] ?>"></i>
                                    </span>
                                    <div class="notif_dd_content">
                                        <div class="notif_dd_title"><?= htmlspecialchars($tl) ?></div>
                                        <div class="notif_dd_desc"><?= htmlspecialchars(truncate_text($n['description'], 60)) ?></div>
                                        <div class="notif_dd_time"><?= time_ago($n['created_at']) ?></div>
                                    </div>
                                    <?php if (!$n['is_read']): ?>
                                    <span class="notif_dd_dot"></span>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                            <div class="notif_dropdown_empty">
                                <i class="fa-solid fa-bell-slash"></i>
                                <p>No notifications today.</p>
                            </div>
                            <?php endif; ?>
                        </div>
                        <a href="notifications.php" class="notif_dropdown_footer">
                            View all notifications
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <span class="topbar_user">
                    <i class="fa-solid fa-circle-user" aria-hidden="true"></i>
                    <?= htmlspecialchars($_SESSION['user_name']) ?>
                </span>
            </div>
        </header>

        <!-- Content -->
        <div class="dashboard_content">
