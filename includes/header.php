
<?php
$current_page = basename($_SERVER['PHP_SELF']);

?>




<header class="site_header">



    <nav class="header_nav">


            <!-- logo desktop -->
            <a href="index.php" class="logo">
                <div class="logo_icon">
                    <i class="fa-solid fa-compass"></i>
                </div>

                <span class="logo_text">Tangier <span class="highlight">Vibes</span></span>
            </a>

                <!-- nav links desktop -->
            <ul class="nav_links desktop_only">
                <li><a href="index.php" class="nav_link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">Home</a></li>
                <li><a href="#" class="nav_link <?php echo ($current_page == 'top_places.php') ? 'active' : ''; ?>">Top Places</a></li>
                <li><a href="explore.php" class="nav_link <?php echo ($current_page == 'explore.php') ? 'active' : ''; ?>">Explore</a></li>
                <li><a href="#" class="nav_link <?php echo ($current_page == 'favorites.php') ? 'active' : ''; ?>"><i class="fa-regular fa-heart"></i> Favorites</a></li>
            </ul>

            <div class="header_search_container desktop_only">
                <form class="header_search_form">
                    <i class="fa-solid fa-magnifying-glass search_icon"></i>
                    <input type="text" name="search" placeholder="Search places..." class="header_search_input" >
                </form>
            </div>


            <div class="auth_actions desktop_only">
            
                    
            
                        <div class="user_profile_dropdown">
                            <div class="profile_trigger" id="profileTrigger">
                                <i class="fa-regular fa-circle-user profile_icon"></i>
                                <i class="fa-solid fa-chevron-down dropdown_arrow"></i>
                            </div>
                            <div class="dropdown_menu" id="dropdownMenu">
                                <div class="dropdown_header">
                                    <span class="user_name">oussama</span>
                                    <span class="user_role">Administrator</span>
                                </div>
                                <hr>
                                <a href="#" class="dropdown_item"><i class="fa-solid fa-gauge"></i> Dashboard</a>
                                <a href="#" class="dropdown_item logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                            </div>
                        </div>

                    
                    
                        <div class="welcome_group">
                            <span class="welcome_text">Welcome, <strong>oussama</strong></span>
                        </div>
                        <a href="logout.php" class="logout_btn">Logout</a>

                        
                    
                    <a href="#" class="login_btn">Login</a>
                    <a href="#" class="join_btn">Register</a>
            
            </div> 


            <div class="mobile_triggers">
                <button class="mobile_icon_btn" id="mobileSearchTrigger"><i class="fa-solid fa-magnifying-glass"></i></button>
                <button class="mobile_icon_btn" id="mobileProfileTrigger"><i class="fa-regular fa-circle-user"></i></button>
                <button class="mobile_icon_btn" id="mobileMenuTrigger"><i class="fa-solid fa-bars"></i></button>
            </div>

    </nav>

    <div class="mobile_search_overlay" id="mobileSearchBar">
        <div class="search_container_inner">
            <form class="mobile_search_form">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="search" placeholder="Search..." class="mobile_input">
                <button type="button" id="closeSearch"><i class="fa-solid fa-xmark"></i></button>
            </form>
        </div>
    </div>

    <div class="mobile_nav_overlay" id="mobileNav">
        <div class="mobile_nav_content">
            <div class="mobile_nav_header">
                <span class="logo_text">Tangier <span class="highlight">Vibes</span></span>
                <button id="closeMenu"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <ul class="mobile_menu_links">
                <li><a href="index.php" class="nav_link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>"><i class="fa-solid fa-house "></i> Home</a></li>
                <li><a href="#" class="nav_link <?php echo ($current_page == 'top_places.php') ? 'active' : ''; ?>"><i class="fa-solid fa-star"></i> Top Places</a></li>
                <li><a href="explore.php" class="nav_link <?php echo ($current_page == 'explore.php') ? 'active' : ''; ?>"><i class="fa-solid fa-compass"></i> Explore</a></li>
                <li><a href="#" class="nav_link <?php echo ($current_page == 'favorites.php') ? 'active' : ''; ?>"><i class="fa-regular fa-heart"></i> Favorites</a></li>
            </ul>
        </div>
    </div>



    <div class="mobile_profile_overlay" id="mobileProfileMenu">
        <div class="mobile_profile_content">
            <div class="profile_menu_header">
                <h3>Profile</h3>
                <button id="closeProfile"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="profile_menu_body">
               
                    <div class="user_info_card">
                        <i class="fa-regular fa-circle-user"></i>
                        <div class="info_text">
                            <span class="name">oussama</span>
                            <span class="role">user</span>
                        </div>
                    </div>
                   
                        <a href="#" class="profile_link"><i class="fa-solid fa-gauge"></i> Dashboard</a>
                    
                    <a href="#" class="profile_link logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                        
                    <a href="#" class="profile_link"><i class="fa-solid fa-right-to-bracket"></i> Login</a>
                    <a href="#" class="profile_link"><i class="fa-solid fa-user-plus"></i> Register</a>
                    
            </div>
        </div>
    </div>







</header>