<?php

require_once __DIR__ . '/security.php';
require_once __DIR__ . '/lang.php';

check_session_timeout();

// ensure CSRF token exists for forms
get_csrf_token();


?>

<header class="site_header">


        <div class="header_nav_desktop">
                    <!-- logo  -->
                    <a href="../pages/index.php" class="logo">
                        <img src="../assets/images/logo.png" alt="Tangier Vibes Logo" class="logo_img" style="height:40px;width:auto;">
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
                            <i class="fa-solid fa-magnifying-glass search_icon"></i>
                            <input type="text" name="q" placeholder="<?= __('search_placeholder') ?>" value="<?= htmlspecialchars(trim($_GET['q'] ?? '')); ?>">
                            <?php if (!empty(trim($_GET['q'] ?? ''))): ?>
                                <a href="../pages/explore.php" class="search_clear_icon"><i class="fa-solid fa-xmark"></i></a>
                            <?php endif; ?>
                        </form>

                    </div>

                    
                    <!-- auth links -->
                    <div class="auth_actions_desktop">

                            <?php if (isset($_SESSION['id_user'])): ?>

                            <div class="dashboard_logout">
                                <a href="../pages/dashboard.php" class="dashboard"><?= __('auth_dashboard') ?></a>
                                <a href="../pages/logout.php" class="logout"><?= __('auth_logout') ?></a>
                            </div>
                            <?php else: ?>

                            <a href="#" class="join_btn" data-auth-toggle><?= __('auth_join') ?></a>

                            <?php endif; ?>
                    </div>

                    <!-- language switcher desktop -->
                    <div class="lang_dropdown desktop_switcher">
                        <button class="lang_trigger" aria-label="Select language" data-lang-dropdown>
                            <i class="fa-solid fa-globe lang_globe"></i>
                            <span class="lang_current"><?= strtoupper(get_lang_code()) ?></span>
                            <i class="fa-solid fa-chevron-down lang_chevron"></i>
                        </button>
                        <div class="lang_menu">
                            <a href="<?= lang_url('en') ?>" class="lang_option <?= get_lang_code() === 'en' ? ' active' : '' ?>" data-lang="en">English</a>
                            <a href="<?= lang_url('fr') ?>" class="lang_option <?= get_lang_code() === 'fr' ? ' active' : '' ?>" data-lang="fr">Français</a>
                            <a href="<?= lang_url('ar') ?>" class="lang_option <?= get_lang_code() === 'ar' ? ' active' : '' ?>" data-lang="ar">العربية</a>
                        </div>
                    </div>

                        <!-- menu open  -->
                    <div class="menu">
                        <button class="menu_btn" id="menu_btn">
                            <i class="fa-solid fa-bars"></i>
                        </button>
                    </div>
                        


        </div>
        <!-- mobile menu -->
        <div class="header_nav_mobile">

                    <!-- close menu  -->
                    <div class="mobile_menu_header">
                        <button class="close_menu" id="close_menu" aria-label="Fermer le menu">
                                    <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>




                    <!-- search mobile -->
                    <div class="search_mobile">
                        <form action="../pages/explore.php" method="GET" class="search_mobile_form">
                            <i class="fa-solid fa-magnifying-glass search_icon"></i>
                            <input type="text" name="q" placeholder="<?= __('search_placeholder') ?>" value="<?= htmlspecialchars(trim($_GET['q'] ?? '')); ?>">
                            <?php if (!empty(trim($_GET['q'] ?? ''))): ?>
                                <a href="../pages/explore.php" class="search_clear_icon"><i class="fa-solid fa-xmark"></i></a>
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

                            <div class="dashboard_logout">
                                <a href="../pages/dashboard.php" class="dashboard"><?= __('auth_dashboard') ?></a>
                                <a href="../pages/logout.php" class="logout"><?= __('auth_logout') ?></a>
                            </div>
                            <?php else: ?>

                            <a href="#" class="join_btn" data-auth-toggle><?= __('auth_join') ?></a>

                            <?php endif; ?>
                            
                    </div>

                    <!-- language switcher mobile -->
                    <div class="lang_dropdown mobile_switcher">
                        <button class="lang_trigger" aria-label="Select language" data-lang-dropdown>
                            <i class="fa-solid fa-globe lang_globe"></i>
                            <span class="lang_current"><?= strtoupper(get_lang_code()) ?></span>
                            <i class="fa-solid fa-chevron-down lang_chevron"></i>
                        </button>
                        <div class="lang_menu">
                            <a href="<?= lang_url('en') ?>" class="lang_option <?= get_lang_code() === 'en' ? ' active' : '' ?>" data-lang="en">English</a>
                            <a href="<?= lang_url('fr') ?>" class="lang_option <?= get_lang_code() === 'fr' ? ' active' : '' ?>" data-lang="fr">Français</a>
                            <a href="<?= lang_url('ar') ?>" class="lang_option <?= get_lang_code() === 'ar' ? ' active' : '' ?>" data-lang="ar">العربية</a>
                        </div>
                    </div>


        </div>

        



</header>