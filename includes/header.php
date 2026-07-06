<?php

require_once __DIR__ . '/security.php';
require_once __DIR__ . '/lang.php';

check_session_timeout();

// ensure CSRF token exists for forms
get_csrf_token();

?>
<a href="#main_content" class="skip-link"><?= __('skip_to_content') ?></a>

<header class="site_header">


        <div class="header_nav_desktop">
                    <!-- logo  -->
                    <a href="../pages/index.php" class="logo">
                        <img src="../assets/images/logo.png" alt="Tangier Vibes Logo" class="logo_img logo_header">
                    </a>

                     <!-- links  desktop-->

                    <ul class="nav_links desktop">
                        <li><a href="../pages/index.php" class="nav_link"><?= __('nav_home') ?></a></li>
                        <li><a href="../pages/explore.php" class="nav_link"><?= __('nav_explore') ?></a></li>
                        <li><a href="../pages/about.php" class="nav_link"><?= __('nav_about') ?></a></li>
                        <li><a href="../pages/contact.php" class="nav_link"><?= __('nav_contact') ?></a></li>
                    </ul>

                    <!-- search desktop -->
                    <div class="search_desktop">
                        <form action="../pages/explore.php" method="GET" class="search_desktop_form">
                            <i class="fa-solid fa-magnifying-glass search_icon" aria-hidden="true"></i>
                            <input type="text" name="q" placeholder="<?= __('search_placeholder') ?>" value="<?= htmlspecialchars(trim($_GET['q'] ?? '')); ?>">
                            <?php if (!empty(trim($_GET['q'] ?? ''))): ?>
                                <a href="../pages/explore.php" class="search_clear_icon" aria-label="<?= __('search_clear') ?>"><i class="fa-solid fa-xmark"></i></a>
                            <?php endif; ?>
                        </form>

                    </div>

                    
                    <!-- auth links desktop -->
                    <div class="auth_actions_desktop">

                            <?php if (isset($_SESSION['id_user'])): ?>

                            <div class="user_dropdown">
                                <button class="user_dropdown_trigger" data-user-dropdown>
                                    <span class="user_avatar"><?= strtoupper(htmlspecialchars(substr($_SESSION['user_name'], 0, 1))) ?></span>
                                    <span class="user_name_text"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                                    <i class="fa-solid fa-chevron-down lang_chevron" aria-hidden="true"></i>
                                </button>
                                <div class="user_dropdown_menu">
                                    <div class="user_dropdown_header">
                                        <span class="user_avatar large"><?= strtoupper(htmlspecialchars(substr($_SESSION['user_name'], 0, 1))) ?></span>
                                        <div>
                                            <div class="user_dropdown_name"><?= htmlspecialchars($_SESSION['user_name']) ?></div>
                                            <div class="user_dropdown_role"><?= $_SESSION['role'] === 'admin' ? __('admin_label') : __('dashboard_label') ?></div>
                                        </div>
                                    </div>
                                    <div class="user_dropdown_divider"></div>
                                    <?php if ($_SESSION['role'] === 'admin'): ?>
                                    <a href="../dashboard/index.php" class="user_dropdown_item">
                                        <i class="fa-solid fa-gauge-high" aria-hidden="true"></i> <?= __('auth_dashboard') ?>
                                    </a>
                                    <a href="../dashboard/posts.php" class="user_dropdown_item">
                                        <i class="fa-solid fa-file-lines" aria-hidden="true"></i> <?= __('dashboard_posts_title') ?>
                                    </a>
                                    <a href="../dashboard/comments.php" class="user_dropdown_item">
                                        <i class="fa-solid fa-comments" aria-hidden="true"></i> <?= __('dashboard_comments') ?>
                                    </a>
                                    <a href="../dashboard/messages.php" class="user_dropdown_item">
                                        <i class="fa-solid fa-envelope" aria-hidden="true"></i> <?= __('dashboard_messages') ?>
                                    </a>
                                    <a href="../dashboard/users.php" class="user_dropdown_item">
                                        <i class="fa-solid fa-users" aria-hidden="true"></i> <?= __('dashboard_users') ?>
                                    </a>
                                    <div class="user_dropdown_divider"></div>
                                    <?php endif; ?>
                                    <form action="../pages/logout.php" method="POST" class="user_dropdown_form">
                                        <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
                                        <button type="submit" class="user_dropdown_item user_dropdown_logout">
                                            <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i> <?= __('auth_logout') ?>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <?php else: ?>

                            <a href="#" class="join_btn" data-auth-toggle><?= __('auth_join') ?></a>

                            <?php endif; ?>
                    </div>

                    <!-- language switcher desktop -->
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

                        <!-- menu open  -->
                    <div class="menu">
                        <button class="menu_btn" id="menu_btn" aria-label="<?= __('menu_toggle_aria') ?>">
                            <i class="fa-solid fa-bars" aria-hidden="true"></i>
                        </button>
                    </div>
                        


        </div>
        <!-- mobile menu -->
        <div class="header_nav_mobile">

                    <!-- close menu  -->
                    <div class="mobile_menu_header">
                        <button class="close_menu" id="close_menu" aria-label="<?= __('menu_close_aria') ?>">
                                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                        </button>
                    </div>




                    <!-- search mobile -->
                    <div class="search_mobile">
                        <form action="../pages/explore.php" method="GET" class="search_mobile_form">
                            <i class="fa-solid fa-magnifying-glass search_icon" aria-hidden="true"></i>
                            <input type="text" name="q" placeholder="<?= __('search_placeholder') ?>" value="<?= htmlspecialchars(trim($_GET['q'] ?? '')); ?>">
                            <?php if (!empty(trim($_GET['q'] ?? ''))): ?>
                                <a href="../pages/explore.php" class="search_clear_icon" aria-label="<?= __('search_clear') ?>"><i class="fa-solid fa-xmark"></i></a>
                            <?php endif; ?>
                        </form>

                    </div>

                    

                     <!-- links mobile -->

                    <ul class="nav_links mobile">
                        <li><a href="../pages/index.php" class="nav_link"><?= __('nav_home') ?></a></li>
                        <li><a href="../pages/explore.php" class="nav_link"><?= __('nav_explore') ?></a></li>
                        <li><a href="../pages/about.php" class="nav_link"><?= __('nav_about') ?></a></li>
                        <li><a href="../pages/contact.php" class="nav_link"><?= __('nav_contact') ?></a></li>
                    </ul>





                    <!-- auth links mobile -->
                    <div class="auth_actions_mobile">
                            <?php if (isset($_SESSION['id_user'])): ?>

                            <div class="mobile_user_header">
                                <span class="user_avatar large"><?= strtoupper(htmlspecialchars(substr($_SESSION['user_name'], 0, 1))) ?></span>
                                <span class="mobile_user_name"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                            </div>
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                            <a href="../dashboard/index.php" class="join_btn">
                                <i class="fa-solid fa-gauge-high" aria-hidden="true"></i> <?= __('auth_dashboard') ?>
                            </a>
                            <?php endif; ?>
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

                    <!-- language switcher mobile -->
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