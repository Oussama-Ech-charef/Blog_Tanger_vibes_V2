<?php

require_once __DIR__ . '/security.php';
require_once __DIR__ . '/lang.php';

check_session_timeout();

get_csrf_token();

$unread_count = 0;
if (isset($_SESSION['id_user'])) {
    require_once __DIR__ . '/../config/connection.php';
    try {
        $uid = (int)$_SESSION['id_user'];
        $role = $_SESSION['role'] ?? 'user';
        if ($role === 'admin') {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM activity_log WHERE is_read=0 AND action_type NOT IN ('draft_saved') AND (user_id IS NULL OR user_id IN (SELECT id_user FROM users WHERE role='user'))");
            $stmt->execute();
        } else {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM activity_log al WHERE is_read=0 AND action_type NOT IN ('draft_saved') AND al.user_id!=:uid AND ((al.action_type IN ('post_approved','post_rejected','post_deleted') AND al.entity_type='post' AND EXISTS(SELECT 1 FROM posts p WHERE p.id_post=al.entity_id AND p.id_user=:uid2)) OR (al.action_type='comment_added' AND al.entity_type='comment' AND EXISTS(SELECT 1 FROM comments c JOIN posts p ON c.id_post=p.id_post WHERE c.id_comment=al.entity_id AND p.id_user=:uid3)))");
            $stmt->execute([':uid'=>$uid, ':uid2'=>$uid, ':uid3'=>$uid]);
        }
        $unread_count = (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        $unread_count = 0;
    }
}

?>
<a href="#main_content" class="skip-link"><?= __('skip_to_content') ?></a>

<header class="site_header">

    <div class="header_nav_desktop">

        <a href="../pages/index.php" class="logo">
            <img src="../assets/images/logo.png" alt="<?= __('site_logo_alt') ?>" class="logo_img logo_header">
        </a>

        <ul class="nav_links desktop">
            <li><a href="../pages/index.php" class="nav_link"><?= __('nav_home') ?></a></li>
            <li><a href="../pages/explore.php" class="nav_link"><?= __('nav_explore') ?></a></li>
            <li><a href="../pages/about.php" class="nav_link"><?= __('nav_about') ?></a></li>
            <li><a href="../pages/contact.php" class="nav_link"><?= __('nav_contact') ?></a></li>
        </ul>

        <div class="search_desktop">
            <form action="../pages/explore.php" method="GET" class="search_desktop_form">
                <i class="fa-solid fa-magnifying-glass search_icon" aria-hidden="true"></i>
                <input type="text" name="q" placeholder="<?= __('search_placeholder') ?>" value="<?= htmlspecialchars(trim($_GET['q'] ?? '')); ?>">
                <?php if (!empty(trim($_GET['q'] ?? ''))): ?>
                    <a href="../pages/explore.php" class="search_clear_icon" aria-label="<?= __('search_clear') ?>"><i class="fa-solid fa-xmark"></i></a>
                <?php endif; ?>
            </form>
        </div>

        <div class="auth_actions_desktop">

            <?php if (isset($_SESSION['id_user'])): ?>

            <div class="user_dropdown">
                <button class="user_dropdown_trigger" data-user-dropdown aria-haspopup="true" aria-expanded="false">
                    <span class="user_avatar"><?= strtoupper(htmlspecialchars(substr($_SESSION['user_name'], 0, 1))) ?></span>
                    <span class="user_name_text"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                    <i class="fa-solid fa-chevron-down lang_chevron" aria-hidden="true"></i>
                </button>
                <div class="user_dropdown_menu" role="menu">
                    <div class="user_dropdown_header">
                        <span class="user_avatar large"><?= strtoupper(htmlspecialchars(substr($_SESSION['user_name'], 0, 1))) ?></span>
                        <div class="user_dropdown_identity">
                            <div class="user_dropdown_name"><?= htmlspecialchars($_SESSION['user_name']) ?></div>
                            <div class="user_dropdown_role"><?= htmlspecialchars($_SESSION['role']) === 'admin' ? __('admin_label') : __('dashboard_label') ?></div>
                        </div>
                    </div>
                    <div class="user_dropdown_divider"></div>
                    <div class="user_dropdown_items">
                        <a href="../dashboard/index.php" class="user_dropdown_item" role="menuitem">
                            <i class="fa-solid fa-gauge-high" aria-hidden="true"></i>
                            <span><?= __('auth_dashboard') ?></span>
                        </a>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                        <a href="../dashboard/posts.php" class="user_dropdown_item" role="menuitem">
                            <i class="fa-solid fa-file-lines" aria-hidden="true"></i>
                            <span><?= __('dashboard_posts_title') ?></span>
                        </a>
                        <?php else: ?>
                        <a href="../dashboard/posts.php" class="user_dropdown_item" role="menuitem">
                            <i class="fa-solid fa-file-lines" aria-hidden="true"></i>
                            <span><?= __('sidebar_my_posts') ?></span>
                        </a>
                        <?php endif; ?>
                        <a href="../dashboard/notifications.php" class="user_dropdown_item" role="menuitem">
                            <i class="fa-solid fa-bell" aria-hidden="true"></i>
                            <span><?= __('sidebar_notifications') ?></span>
                            <?php if ($unread_count > 0): ?>
                            <span class="user_dropdown_badge"><?= min($unread_count, 99) . ($unread_count > 99 ? '+' : '') ?></span>
                            <?php endif; ?>
                        </a>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                        <a href="../dashboard/comments.php" class="user_dropdown_item" role="menuitem">
                            <i class="fa-solid fa-comments" aria-hidden="true"></i>
                            <span><?= __('dashboard_comments') ?></span>
                        </a>
                        <?php endif; ?>
                        <a href="../dashboard/settings.php" class="user_dropdown_item" role="menuitem">
                            <i class="fa-solid fa-gear" aria-hidden="true"></i>
                            <span><?= __('sidebar_settings') ?></span>
                        </a>
                    </div>
                    <div class="user_dropdown_divider"></div>
                    <form action="../pages/logout.php" method="POST" class="user_dropdown_form">
                        <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
                        <button type="submit" class="user_dropdown_item user_dropdown_logout" role="menuitem">
                            <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
                            <span><?= __('auth_logout') ?></span>
                        </button>
                    </form>
                </div>
            </div>

            <?php else: ?>
            <a href="#" class="join_btn" data-auth-toggle><?= __('auth_join') ?></a>
            <?php endif; ?>
        </div>

        <div class="lang_dropdown desktop_switcher">
            <button class="lang_trigger" aria-label="<?= __('lang_select_aria') ?>" data-lang-dropdown>
                <i class="fa-solid fa-globe lang_globe" aria-hidden="true"></i>
                <span class="lang_current"><?= strtoupper(get_lang_code()) ?></span>
                <i class="fa-solid fa-chevron-down lang_chevron" aria-hidden="true"></i>
            </button>
            <div class="lang_menu">
                <a href="<?= lang_url('en') ?>" class="lang_option <?= get_lang_code() === 'en' ? ' active' : '' ?>" data-lang="en"><?= __('lang_en') ?></a>
                <a href="<?= lang_url('fr') ?>" class="lang_option <?= get_lang_code() === 'fr' ? ' active' : '' ?>" data-lang="fr"><?= __('lang_fr') ?></a>
                <a href="<?= lang_url('ar') ?>" class="lang_option <?= get_lang_code() === 'ar' ? ' active' : '' ?>" data-lang="ar"><?= __('lang_ar') ?></a>
            </div>
        </div>

        <div class="menu">
            <button class="menu_btn" id="menu_btn" aria-label="<?= __('menu_toggle_aria') ?>">
                <i class="fa-solid fa-bars" aria-hidden="true"></i>
            </button>
        </div>

    </div>

    <div class="header_nav_mobile">

        <div class="mobile_menu_header">
            <button class="close_menu" id="close_menu" aria-label="<?= __('menu_close_aria') ?>">
                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </button>
        </div>

        <div class="search_mobile">
            <form action="../pages/explore.php" method="GET" class="search_mobile_form">
                <i class="fa-solid fa-magnifying-glass search_icon" aria-hidden="true"></i>
                <input type="text" name="q" placeholder="<?= __('search_placeholder') ?>" value="<?= htmlspecialchars(trim($_GET['q'] ?? '')); ?>">
                <?php if (!empty(trim($_GET['q'] ?? ''))): ?>
                    <a href="../pages/explore.php" class="search_clear_icon" aria-label="<?= __('search_clear') ?>"><i class="fa-solid fa-xmark"></i></a>
                <?php endif; ?>
            </form>
        </div>

        <ul class="nav_links mobile">
            <li><a href="../pages/index.php" class="nav_link"><?= __('nav_home') ?></a></li>
            <li><a href="../pages/explore.php" class="nav_link"><?= __('nav_explore') ?></a></li>
            <li><a href="../pages/about.php" class="nav_link"><?= __('nav_about') ?></a></li>
            <li><a href="../pages/contact.php" class="nav_link"><?= __('nav_contact') ?></a></li>
        </ul>

        <div class="auth_actions_mobile">
            <?php if (isset($_SESSION['id_user'])): ?>

            <div class="mobile_user_header">
                <span class="user_avatar large"><?= strtoupper(htmlspecialchars(substr($_SESSION['user_name'], 0, 1))) ?></span>
                <div class="mobile_user_identity">
                    <span class="mobile_user_name"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                    <span class="mobile_user_role"><?= htmlspecialchars($_SESSION['role']) === 'admin' ? __('admin_label') : __('dashboard_label') ?></span>
                </div>
            </div>
            <div class="mobile_user_links">
                <a href="../dashboard/index.php" class="mobile_user_link">
                    <i class="fa-solid fa-gauge-high" aria-hidden="true"></i> <?= __('auth_dashboard') ?>
                </a>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="../dashboard/posts.php" class="mobile_user_link">
                    <i class="fa-solid fa-file-lines" aria-hidden="true"></i> <?= __('dashboard_posts_title') ?>
                </a>
                <?php else: ?>
                <a href="../dashboard/posts.php" class="mobile_user_link">
                    <i class="fa-solid fa-file-lines" aria-hidden="true"></i> <?= __('sidebar_my_posts') ?>
                </a>
                <?php endif; ?>
                <a href="../dashboard/notifications.php" class="mobile_user_link">
                    <i class="fa-solid fa-bell" aria-hidden="true"></i> <?= __('sidebar_notifications') ?>
                    <?php if ($unread_count > 0): ?>
                    <span class="user_dropdown_badge"><?= min($unread_count, 99) . ($unread_count > 99 ? '+' : '') ?></span>
                    <?php endif; ?>
                </a>
                <a href="../dashboard/settings.php" class="mobile_user_link">
                    <i class="fa-solid fa-gear" aria-hidden="true"></i> <?= __('sidebar_settings') ?>
                </a>
            </div>
            <form action="../pages/logout.php" method="POST" class="inline_form mobile_logout_form">
                <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
                <button type="submit" class="logout mobile_logout_btn">
                    <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i> <?= __('auth_logout') ?>
                </button>
            </form>

            <?php else: ?>
            <a href="#" class="join_btn" data-auth-toggle><?= __('auth_join') ?></a>
            <?php endif; ?>
        </div>

        <div class="lang_dropdown mobile_switcher">
            <button class="lang_trigger" aria-label="<?= __('lang_select_aria') ?>" data-lang-dropdown>
                <i class="fa-solid fa-globe lang_globe" aria-hidden="true"></i>
                <span class="lang_current"><?= strtoupper(get_lang_code()) ?></span>
                <i class="fa-solid fa-chevron-down lang_chevron" aria-hidden="true"></i>
            </button>
            <div class="lang_menu">
                <a href="<?= lang_url('en') ?>" class="lang_option <?= get_lang_code() === 'en' ? ' active' : '' ?>" data-lang="en"><?= __('lang_en') ?></a>
                <a href="<?= lang_url('fr') ?>" class="lang_option <?= get_lang_code() === 'fr' ? ' active' : '' ?>" data-lang="fr"><?= __('lang_fr') ?></a>
                <a href="<?= lang_url('ar') ?>" class="lang_option <?= get_lang_code() === 'ar' ? ' active' : '' ?>" data-lang="ar"><?= __('lang_ar') ?></a>
            </div>
        </div>

    </div>

</header>
