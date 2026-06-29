<?php
// header.php — Admin Dashboard HTML layout
// Expects: $page_title, $csrf_token to be set by init.php
?>
<!DOCTYPE html>
<html lang="<?= get_lang_code() ?>" dir="<?= get_lang_dir() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'Dashboard') ?> — Tangier Vibes Admin</title>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/components.css">
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
                <span>Overview</span>
            </a>
            <a href="posts.php" class="sidebar_link <?= $sidebar_page === 'posts.php' || $sidebar_page === 'add_post.php' || $sidebar_page === 'edit_post.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-file-lines" aria-hidden="true"></i>
                <span>Posts</span>
            </a>
            <a href="notifications.php" class="sidebar_link <?= $sidebar_page === 'notifications.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-bell" aria-hidden="true"></i>
                <span>Notifications</span>
            </a>
            <a href="comments.php" class="sidebar_link <?= $sidebar_page === 'comments.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-comments" aria-hidden="true"></i>
                <span>Comments</span>
            </a>
            <a href="messages.php" class="sidebar_link <?= $sidebar_page === 'messages.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                <span>Messages</span>
            </a>
            <a href="users.php" class="sidebar_link <?= $sidebar_page === 'users.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-users" aria-hidden="true"></i>
                <span>Users</span>
            </a>
            <a href="categories.php" class="sidebar_link <?= $sidebar_page === 'categories.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-tags" aria-hidden="true"></i>
                <span>Categories</span>
            </a>
            <a href="settings.php" class="sidebar_link <?= $sidebar_page === 'settings.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-gear" aria-hidden="true"></i>
                <span>Settings</span>
            </a>
        </nav>

        <div class="sidebar_footer">
            <a href="../pages/index.php" class="sidebar_link">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                <span>Back to Site</span>
            </a>
            <form action="../pages/logout.php" method="POST" class="inline_form">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <button type="submit" class="sidebar_link logout_btn">
                    <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="dashboard_main">

        <!-- Topbar -->
        <header class="dashboard_topbar">
            <div class="flex_row">
                <button class="mobile_menu_btn" id="mobileMenuBtn" aria-label="Toggle menu">
                    <i class="fa-solid fa-bars" aria-hidden="true"></i>
                </button>
                <h1><?= htmlspecialchars($page_title ?? 'Dashboard') ?></h1>
            </div>
            <div class="topbar_right">
                <a href="../pages/index.php" class="topbar_back">
                    <i class="fa-solid fa-eye" aria-hidden="true"></i> View Site
                </a>
                <span class="topbar_user">
                    <i class="fa-solid fa-circle-user" aria-hidden="true"></i>
                    <?= htmlspecialchars($_SESSION['user_name']) ?>
                </span>
            </div>
        </header>

        <!-- Content -->
        <div class="dashboard_content">
