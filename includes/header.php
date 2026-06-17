<?php

require_once __DIR__ . '/security.php';

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
                        <li><a href="../pages/index.php" class="nav_link">Home</a></li>
                        <li><a href="../pages/explore.php" class="nav_link">Explore</a></li>
                        <li><a href="../pages/about.php" class="nav_link">About</a></li>
                        <li><a href="../pages/contact.php" class="nav_link">Contact</a></li>
                    </ul>

                    <!-- search desktop -->
                    <div class="search_desktop">
                        <form action="../pages/explore.php" method="GET" class="search_desktop_form">
                            <i class="fa-solid fa-magnifying-glass search_icon"></i>
                            <input type="text" name="q" placeholder="Search..." value="<?= htmlspecialchars(trim($_GET['q'] ?? '')); ?>">
                            <?php if (!empty(trim($_GET['q'] ?? ''))): ?>
                                <a href="../pages/explore.php" class="search_clear_icon"><i class="fa-solid fa-xmark"></i></a>
                            <?php endif; ?>
                        </form>

                    </div>

                    
                    <!-- auth links -->
                    <div class="auth_actions_desktop">


                            <?php if (isset($_SESSION['id_user'])): ?>

                            <div class="dashboard_logout">
                                <a href="../pages/dashboard.php" class="dashboard">Dashboard</a>
                                <a href="../pages/logout.php" class="logout">Logout</a>
                            </div>
                            <?php else: ?>

                            <div class="login_register">
                                <a href="../pages/login.php" class="login_link">Login</a>
                                <a href="../pages/register.php" class="register_link ">Register</a>
                            </div>

                            <?php endif; ?>
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
                            <input type="text" name="q" placeholder="Search..." value="<?= htmlspecialchars(trim($_GET['q'] ?? '')); ?>">
                            <?php if (!empty(trim($_GET['q'] ?? ''))): ?>
                                <a href="../pages/explore.php" class="search_clear_icon"><i class="fa-solid fa-xmark"></i></a>
                            <?php endif; ?>
                        </form>

                    </div>

                    

                     <!-- links mobile -->

                    <ul class="nav_links mobile">
                        <li><a href="../pages/index.php" class="nav_link">Home</a></li>
                        <li><a href="../pages/explore.php" class="nav_link">Explore</a></li>
                        <li><a href="../pages/about.php" class="nav_link">About</a></li>
                        <li><a href="../pages/contact.php" class="nav_link">Contact</a></li>
                    </ul>





                    <!-- auth links mobile -->
                    <div class="auth_actions_mobile">
                            <?php if (isset($_SESSION['id_user'])): ?>

                            <div class="dashboard_logout">
                                <a href="../pages/dashboard.php" class="dashboard">Dashboard</a>
                                <a href="../pages/logout.php" class="logout">Logout</a>
                            </div>
                            <?php else: ?>


                            <div class="login_register">
                                <a href="../pages/login.php" class="login_link">Login</a>
                                <a href="../pages/register.php" class="register_link ">Register</a>
                            </div>
                            <?php endif; ?>
                           
                    </div>


                    


        </div>

        



</header>