
<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$current_page = basename($_SERVER['PHP_SELF']);



?>




<header class="site_header">



    <nav class="header_nav">


            <!-- logo desktop -->
            <a href="../index.php" class="logo">
                <div class="logo_icon">
                    <i class="fa-solid fa-compass"></i>
                </div>

                <span class="logo_text">Tangier <span class="highlight">Vibes</span></span>
            </a>

                <!-- nav links desktop -->
            <ul class="nav_links desktop_only">
                <li><a href="<?= ($current_page == 'index.php') ? 'index.php' : '../index.php' ?>" class="nav_link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">Home</a></li>
                <li><a href="#" class="nav_link <?php echo ($current_page == 'top_places.php') ? 'active' : ''; ?>">Top Places</a></li>
                <li><a href="<?= ($current_page == 'index.php') ? 'pages/explore.php' : 'explore.php' ?>" class="nav_link <?php echo ($current_page == 'explore.php') ? 'active' : ''; ?>">Explore</a></li>
                <li><a href="#" class="nav_link <?php echo ($current_page == 'favorites.php') ? 'active' : ''; ?>"><i class="fa-regular fa-heart"></i> Favorites</a></li>
            </ul>

            <div class="header_search_container desktop_only">
                <form class="header_search_form" action="<?= ($current_page == 'index.php') ? 'pages/explore.php' : 'explore.php' ?>" method="GET">
                    <i class="fa-solid fa-magnifying-glass search_icon"></i>
                    <input type="text" name="search" placeholder="Search places..." class="header_search_input" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>" >
                </form>
            </div>


            <div class="auth_actions desktop_only">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                    
            
                        <div class="user_profile_dropdown">
                            <div class="profile_trigger" id="profileTrigger">
                                <i class="fa-regular fa-circle-user profile_icon"></i>
                                <i class="fa-solid fa-chevron-down dropdown_arrow"></i>
                            </div>
                            <div class="dropdown_menu" id="dropdownMenu">
                                <div class="dropdown_header">
                                    <span class="user_name"><?= htmlspecialchars($_SESSION['full_name']); ?></span>
                                    <span class="user_role">Administrator</span>
                                </div>
                                <hr>
                                <a href="<?= ($current_page == 'index.php') ? 'pages/dashboard.php' : 'dashboard.php' ?>" class="dropdown_item"><i class="fa-solid fa-gauge"></i> Dashboard</a>
                                <a href="<?= ($current_page == 'index.php') ? 'pages/logout.php' : 'logout.php' ?>" class="dropdown_item logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                            </div>
                        </div>


                        <?php else: ?>

                    
                    
                        <div class="welcome_group">
                            <span class="welcome_text">Welcome, <strong><?= htmlspecialchars($_SESSION['full_name']); ?></strong></span>
                        </div>
                        <a href="<?= ($current_page == 'index.php') ? 'pages/logout.php' : 'logout.php' ?>" class="logout_btn">Logout</a>
                        <?php endif; ?>
                        
                    <?php else: ?>
                    <a href="../pages/login.php" class="login_btn">Login</a>
                    <a href="../pages/register.php" class="join_btn">Register</a>
                    <?php endif; ?>
            </div> 


            <div class="mobile_triggers">
                
                <button class="mobile_icon_btn" id="mobileMenuTrigger"><i class="fa-solid fa-bars"></i></button>
            </div>

    </nav>

    

    <div class="mobile_nav_overlay" id="mobileNav">
        <div class="mobile_nav_content">
            <div class="mobile_nav_header">
                <a href="../index.php" class="logo">
                    <div class="logo_icon">
                        <i class="fa-solid fa-compass"></i>
                    </div>

                    <span class="logo_text">Tangier <span class="highlight">Vibes</span></span>
                </a>
                <button id="closeMenu"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <div class="mobile_search_in_menu">
                <form action="<?= ($current_page == 'index.php') ? 'pages/explore.php' : 'explore.php' ?>" method="GET">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" name="search" placeholder="Search..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                </form>
            </div>

            <ul class="mobile_menu_links">
                <li><a href="../index.php" class="nav_link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>"><i class="fa-solid fa-house "></i> Home</a></li>
                <li><a href="#" class="nav_link <?php echo ($current_page == 'topplaces.php') ? 'active' : ''; ?>"><i class="fa-solid fa-house "></i> Top Places</a></li>
                <li><a href="../pages/explore.php" class="nav_link <?php echo ($current_page == 'explore.php') ? 'active' : ''; ?>"><i class="fa-solid fa-compass"></i> Explore</a></li>
                <li><a href="#" class="nav_link <?php echo ($current_page == 'favorites.php') ? 'active' : ''; ?>"><i class="fa-solid fa-compass"></i> Favorites</a></li>
                
                <hr class="menu_divider">

                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="mobile_user_info">
                        
                        <i class="fa-regular fa-circle-user"></i>
                        <span class="name"><?= htmlspecialchars($_SESSION['full_name']) ?></span>
                        <span class="role"><?= htmlspecialchars($_SESSION['role']) ?></span>
                    </li>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li><a href="../pages/dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
                    <?php endif; ?>
                    <li><a href="../pages/logout.php" class="logout_link"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
                <?php else: ?>
                    <li><a href="../pages/login.php"><i class="fa-solid fa-right-to-bracket"></i> Login</a></li>
                    <li><a href="../pages/register.php"><i class="fa-solid fa-user-plus"></i> Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>







</header>
