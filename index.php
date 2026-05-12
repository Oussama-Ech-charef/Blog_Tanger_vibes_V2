<?php
session_start();
require_once 'config/db_connection.php';
require_once 'includes/Post.php';


$database = new Database();
$db = $database->getConnection();

$postObj = new Post($db);
$posts = $postObj->getHomePosts();


$current_page = basename($_SERVER['PHP_SELF']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tangier Vibes - Home</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/home.css">
</head>
<body>

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
                <li><a href="#" class="nav_link">Top Places</a></li>
                <li><a href="pages/explore.php" class="nav_link <?php echo ($current_page == 'explore.php') ? 'active' : ''; ?>">Explore</a></li>
                <li><a href="#" class="nav_link"><i class="fa-regular fa-heart"></i> Favorites</a></li>
            </ul>

            <div class="header_search_container desktop_only">
                <form class="header_search_form" action="pages/explore.php" method="GET">
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
                                <a href="pages/dashboard.php" class="dropdown_item"><i class="fa-solid fa-gauge"></i> Dashboard</a>
                                <a href="pages/logout.php" class="dropdown_item logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                            </div>
                        </div>


                        <?php else: ?>

                    
                    
                        <div class="welcome_group">
                            <span class="welcome_text">Welcome, <strong><?= htmlspecialchars($_SESSION['full_name']); ?></strong></span>
                        </div>
                        <a href="pages/logout.php " class="logout_btn">Logout</a>
                        <?php endif; ?>
                        
                    <?php else: ?>
                    <a href="pages/login.php" class="login_btn">Login</a>
                    <a href="pages/register.php" class="join_btn">Register</a>
                    <?php endif; ?>
            </div> 


            <div class="mobile_triggers">
                
                <button class="mobile_icon_btn" id="mobileMenuTrigger"><i class="fa-solid fa-bars"></i></button>
            </div>

    </nav>

    

    <div class="mobile_nav_overlay" id="mobileNav">
        <div class="mobile_nav_content">
            <div class="mobile_nav_header">
                <a href="index.php" class="logo">
                    <div class="logo_icon">
                        <i class="fa-solid fa-compass"></i>
                    </div>

                    <span class="logo_text">Tangier <span class="highlight">Vibes</span></span>
                </a>
                <button id="closeMenu"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <div class="mobile_search_in_menu">
                <form action="pages/explore.php" method="GET">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" name="search" placeholder="Search..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                </form>
            </div>

            <ul class="mobile_menu_links">
                <li><a href="../index.php" class="nav_link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>"><i class="fa-solid fa-house "></i> Home</a></li>
                <li><a href="#" class="nav_link <?php echo ($current_page == 'topplaces.php') ? 'active' : ''; ?>"><i class="fa-solid fa-house "></i> Top Places</a></li>
                <li><a href="pages/explore.php" class="nav_link <?php echo ($current_page == 'explore.php') ? 'active' : ''; ?>"><i class="fa-solid fa-compass"></i> Explore</a></li>
                <li><a href="#" class="nav_link <?php echo ($current_page == 'favorites.php') ? 'active' : ''; ?>"><i class="fa-solid fa-compass"></i> Favorites</a></li>
                
                <hr class="menu_divider">

                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="mobile_user_info">
                        
                        <i class="fa-regular fa-circle-user"></i>
                        <span class="name"><?= htmlspecialchars($_SESSION['full_name']) ?></span>
                        <span class="role"><?= htmlspecialchars($_SESSION['role']) ?></span>
                    </li>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li><a href="pages/dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
                    <?php endif; ?>
                    <li><a href="pages/logout.php" class="logout_link"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
                <?php else: ?>
                    <li><a href="pages/login.php"><i class="fa-solid fa-right-to-bracket"></i> Login</a></li>
                    <li><a href="pages/register.php"><i class="fa-solid fa-user-plus"></i> Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>







</header>
    
     <section class="hero_section">

        <img src="assets/img/home.jpg" alt="Tangier - backgrond image hero" class="hero_bg_img" loading="lazy">

        <div class="hero_overlay"></div>

        <div class="hero_content">

            <p class="hero_label">WELCOME TO YOUR GATEWAY TO AFRICA</p>

            <h1 class="hero_title">
                Experience the Magic<br>
                of <span class="hero_highlight">Tangier</span>
            </h1>

            <p class="hero_desc">
                Discover hidden beaches, legendary cafes, exquisite<br>
                restaurants, and historic landmarks in the Pearl of the North.
            </p>

            <div class="hero_btns">
                <a href="pages/explore.php" class="hero_btn_primary">
                    Start Exploring
                </a>

                <a href="#" class="hero_btn_outline">
                    Top Picks <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>

        </div>

    </section>


    <section class="latest_section">
        <div class="section_header">
            <h2 class="section_title">Latest Places</h2>
            <p class="section_subtitle">The newest additions to TangierVibes</p>
        </div>


        <div class="swipe_hint">
            <i class="fa-solid fa-arrows-left-right"></i> Swipe to explore
        </div>

        <div class="places_grid">

         <?php if(!empty($posts)): ?>
                <?php foreach($posts as $post): ?>
                    
        
                <a href="pages/post_detail.php?id=<?= $post['id']; ?>" class="place_card">
                        <img src="<?php echo htmlspecialchars($post['image']); ?>" class="place_card_img" alt="<?=   htmlspecialchars($post['title']); ?>" loading="lazy">
                        <div class="place_card_overlay">
                            <span class="place_card_category"> <i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($post['cat_name']); ?></span>
                            <h3 class="place_card_name"><?=   htmlspecialchars($post['title']); ?></h3>
                            <p class="place_card_location">
                                <i class="fa-solid fa-location-dot"></i> Tangier, Morocco
                            </p>
                            <span class="place_card_btn">Explore <i class="fa-solid fa-arrow-right"></i></span>
                        </div>
                    </a>

                    <?php endforeach; ?>
            <?php else: ?>
                <p>No places found.</p>
            <?php endif; ?>
        
          
            
        </div>

        <div class="section_footer">
            <a href="pages/explore.php" class="view_all_link">
                View All Places <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
    </section>

    
    
    <?php require 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
    
</body>
</html>

    

