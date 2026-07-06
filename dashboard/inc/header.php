<?php
    $csrf_token = $csrf_token ?? get_csrf_token();
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
    <?php
    // Base CSS files loaded on every dashboard page
    $db_css = [
        'dashboard-variables.css',
        'dashboard-layout.css',
        'dashboard-sidebar.css',
        'dashboard-header.css',
        'dashboard-buttons.css',
        'dashboard-utilities.css',
        'dashboard-alerts.css',
    ];

    // Page-specific CSS files
    $current_file = basename($_SERVER['PHP_SELF']);
    switch ($current_file) {
        case 'index.php':
            $db_css[] = 'dashboard-overview.css';
            break;
        case 'posts.php':
            $db_css[] = 'dashboard-tables.css';
            $db_css[] = 'dashboard-forms.css';
            $db_css[] = 'dashboard-modals.css';
            $db_css[] = 'dashboard-pagination.css';
            break;
        case 'add_post.php':
            $db_css[] = 'dashboard-add-post.css';
            $db_css[] = 'dashboard-editor.css';
            break;
        case 'edit_post.php':
            $db_css[] = 'dashboard-add-post.css';
            $db_css[] = 'dashboard-editor.css';
            $db_css[] = 'dashboard-forms.css';
            $db_css[] = 'dashboard-tables.css';
            break;
        case 'comments.php':
            $db_css[] = 'dashboard-tables.css';
            $db_css[] = 'dashboard-forms.css';
            $db_css[] = 'dashboard-pagination.css';
            break;
        case 'categories.php':
            $db_css[] = 'dashboard-tables.css';
            $db_css[] = 'dashboard-forms.css';
            $db_css[] = 'dashboard-modals.css';
            $db_css[] = 'dashboard-pagination.css';
            break;
        case 'users.php':
            $db_css[] = 'dashboard-tables.css';
            $db_css[] = 'dashboard-forms.css';
            $db_css[] = 'dashboard-pagination.css';
            break;
        case 'messages.php':
            $db_css[] = 'dashboard-tables.css';
            $db_css[] = 'dashboard-forms.css';
            $db_css[] = 'dashboard-pagination.css';
            break;
        case 'notifications.php':
            $db_css[] = 'dashboard-forms.css';
            $db_css[] = 'dashboard-notifications.css';
            break;
        case 'settings.php':
            $db_css[] = 'dashboard-forms.css';
            break;
        case 'preview.php':
            $db_css[] = 'dashboard-forms.css';
            $db_css[] = 'dashboard-modals.css';
            break;
    }

    foreach ($db_css as $css_file) {
        echo '    <link rel="stylesheet" href="../assets/css/dashboard/' . $css_file . '">' . "\n";
    }
    ?>
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/rtl.css">
    <script>
var Lang = {
    confirmApprove: '<?= __('js_confirm_approve') ?>',
    confirmDeletePost: '<?= __('js_confirm_delete_post') ?>',
    confirmDeleteCat: '<?= __('categories_delete_confirm') ?>',
    confirmBulkDelete: '<?= __('comments_confirm_bulk_delete') ?>',
    confirmRejectComment: '<?= __('comments_confirm_reject') ?>',
    confirmDeleteComment: '<?= __('comments_confirm_delete') ?>',
    confirmDemote: '<?= __('users_confirm_demote') ?>',
    confirmPromote: '<?= __('users_confirm_promote') ?>',
    confirmDeactivate: '<?= __('users_confirm_deactivate') ?>',
    confirmActivate: '<?= __('users_confirm_activate') ?>',
    confirmDeleteUser: '<?= __('users_confirm_delete') ?>',
    confirmDeleteMessage: '<?= __('messages_confirm_delete') ?>',
    rejectModalTitle: '<?= __('js_reject_modal_title') ?>',
    editorLinkUrl: '<?= __('editor_link_url') ?>',
    editorImageUrl: '<?= __('editor_image_url') ?>',
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
                <span class="topbar_user">
                    <i class="fa-solid fa-circle-user" aria-hidden="true"></i>
                    <?= htmlspecialchars($_SESSION['user_name']) ?>
                </span>
            </div>
        </header>

        <!-- Content -->
        <div class="dashboard_content">
