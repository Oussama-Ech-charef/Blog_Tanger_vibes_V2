<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);
?>

<header class="site_header">
    <nav class="header_nav">

        <a href="../pages/index.php" class="logo">
            <div class="logo_icon"><i class="fa-solid fa-compass"></i></div>
            <span class="logo_text">Tangier <span class="highlight">Vibes</span></span>
        </a>

        <!-- Desktop Navigation -->
        <ul class="nav_links desktop_only">
            <li><a href="index.php" class="nav_link <?= ($current_page == 'index.php') ? 'active' : ''; ?>">Home</a></li>
            <li><a href="explore.php" class="nav_link <?= ($current_page == 'explore.php') ? 'active' : ''; ?>">Explore</a></li>
            <li><a href="#" class="nav_link"><i class="fa-regular fa-heart"></i> Favorites</a></li>
        </ul>

        <div class="header_search_container desktop_only">
            <form class="header_search_form" action="#" method="GET">
                <i class="fa-solid fa-magnifying-glass search_icon"></i>
                <input type="text" name="search" placeholder="Search places..." class="header_search_input">
            </form>
        </div>

        <div class="auth_actions desktop_only">
            <?php if (isset($_SESSION['user_id'])): ?>
                
                <div class="user_profile_dropdown">
                    
                    <div class="profile_trigger" id="profileTrigger">
                        <div class="profile_icon_container">
                            <i class="fa-regular fa-circle-user"></i>
                        </div>
                    </div>
                    
                    <div class="dropdown_menu" id="dropdownMenu">
                        <div class="dropdown_header">
                            <span class="user_name"><?= htmlspecialchars($_SESSION['full_name']); ?></span>
                            <span class="user_role"><?= ucfirst($_SESSION['role']); ?></span>
                        </div>
                        <hr class="dropdown_divider">
                        
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <a href="#" class="dropdown_item"><i class="fa-solid fa-gauge"></i> Dashboard</a>
                        <?php endif; ?>
                        
                        <a href="logout.php" class="dropdown_item logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                    </div>
                </div>

            <?php else: ?>
                
                <a href="login.php" class="login_link">Login</a>
                <a href="register.php" class="register_btn">Register</a>
            <?php endif; ?>
        </div> 

       
        <div class="mobile_triggers">
            <button class="mobile_icon_btn" id="mobileMenuTrigger" aria-label="Open Menu">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>

    </nav>

    <!-- Mobile Navigation Overlay -->
    <div class="mobile_nav_overlay" id="mobileNav">
        <div class="mobile_nav_content">
            
          
            <div class="mobile_header">
                <div class="logo">
                    <div class="logo_icon"><i class="fa-solid fa-compass"></i></div>
                    <span class="logo_text">Tangier <span class="highlight">Vibes</span></span>
                </div>
                <button class="close_mobile_btn" id="closeMobileNav"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <div class="mobile_search_box">
                <form action="#" method="GET">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" name="search" placeholder="Search...">
                </form>
            </div>

            <ul class="mobile_links_list">
                <li class="<?= ($current_page == 'index.php') ? 'active' : ''; ?>"><a href="index.php"> Home</a></li>
                <li class="<?= ($current_page == 'explore.php') ? 'active' : ''; ?>"><a href="explore.php"> Explore</a> </li>
                <li><a href="#">Favorites</a> </li>
            </ul>

            
            <div class="mobile_footer_section">
                <?php if (isset($_SESSION['user_id'])): ?>
                    
                    
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <a href="" class="mobile_admin_link"><i class="fa-solid fa-gauge"></i> Dashboard</a>
                    <?php endif; ?>

                    <div class="mobile_user_card">
                        <i class="fa-solid fa-circle-user"></i>
                        <div class="user_info">
                            <span class="name"><?= htmlspecialchars($_SESSION['full_name']); ?></span>
                            <span class="role"><?= $_SESSION['role']; ?></span>
                        </div>
                    </div>
                    
                    <a href="../pages/logout.php" class="mobile_logout_btn">
                        <i class="fa-solid fa-right-from-bracket"></i> Logout
                    </a>
                <?php else: ?>
                    <div class="auth_mobile_btns">
                        <a href="login.php" class="mobile_login_btn">Login</a>
                        <a href="register.php" class="mobile_register_btn">Register</a>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</header>
