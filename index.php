<?php


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

    <?php require 'includes/header.php'; ?>
    
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
                <a href="explore.php" class="hero_btn_primary">
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

        <div class="places_grid">
        <a href="#" class="place_card">
                <img src="assets/img/home.jpg" class="place_card_img" alt="plage achakare">
                <div class="place_card_overlay">
                    <span class="place_card_category">
                        <i class="fa-solid fa-location-dot"></i> Beache
                    </span>
                    <h3 class="place_card_name">Plage Achakare</h3>
                    <p class="place_card_views">
                        <i class="fa-solid fa-eye"></i> 10,000 views
                    </p>
                    <span class="place_card_btn">Explore <i class="fa-solid fa-arrow-right"></i></span>
                </div>
            </a>
          
            
        </div>

        <div class="section_footer">
            <a href="explore.php" class="view_all_link">
                View All Places <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
    </section>

    
    
    <?php require 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
    
</body>
</html>

    

