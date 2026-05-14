<?php

session_start();
require_once '../config/db_connection.php';
require_once '../includes/Post.php';

$database = new Database();
$db = $database->getConnection();

$postObj = new Post($db);






$posts = $postObj->allposts();














?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explore Tangier - Tangier Vibes</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <link rel="stylesheet" href="../assets/css/home.css">
    <link rel="stylesheet" href="../assets/css/explore.css">
</head>
<body>

    <?php require '../includes/header.php'; ?>

    <main>
        <!-- Explore Hero -->
        <section class="explore_hero">
            <img src="../assets/img/explore.jpg" alt="Tangier Panorama" class="explore_hero_img" loading="lazy">
            <div class="explore_hero_content">
                <h1 class="explore_hero_title">Explore Tangier</h1>
                <p class="explore_hero_desc">Discover the magic of the Pearl of the North, from historic landmarks to hidden beaches.</p>
            </div>
        </section>

        <section class="filters_container">
            <div class="category_filters">
                <a href="#" class="cat_filter_btn active">All</a>
                <a href="#" class="cat_filter_btn">Beaches</a>
                <a href="#" class="cat_filter_btn">Cafes</a>
                <a href="#" class="cat_filter_btn">Historical Sites</a>
                <a href="#" class="cat_filter_btn">Hotels</a>
                <a href="#" class="cat_filter_btn">Museums</a>
                <a href="#" class="cat_filter_btn">Parks</a>
                <a href="#" class="cat_filter_btn">Restaurants</a>
            </div>
        </section>

        <!-- Results Grid -->
        <section class="explore_results">
            <?php if (!empty($posts)): ?>
                <div class="places_grid" id="posts-grid">
                    <?php foreach($posts as $post): ?>
                        <a href="post_detail.php?id=<?= $post['id']; ?>" class="place_card">
                            <img src="<?= htmlspecialchars($post['image']); ?>" class="place_card_img" alt="<?= htmlspecialchars($post['title']); ?>" loading="lazy">
                            <div class="place_card_overlay">
                                <span class="place_card_category">
                                    <i class="fa-solid fa-location-dot"></i> Tangier Spot
                                </span>
                                <h3 class="place_card_name"><?= htmlspecialchars($post['title']); ?></h3>
                                <p class="place_card_location">
                                    <i class="fa-solid fa-location-dot"></i> Tangier, Morocco
                                </p>
                                <span class="place_card_btn">Explore <i class="fa-solid fa-arrow-right"></i></span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>

               <div class="pagination_container">

                    <div class="pagination_btn next">
                           <i class="fas fa-arrow-left"></i> previous 
                        </div>
                    <span class="page_info">1 / 3</span>

                    <div class="pagination_btn next">
                        Next <i class="fas fa-arrow-right"></i>
                    </div>
                </div>

            
            <?php endif; ?>
        </section>
    </main>

    <?php require '../includes/footer.php'; ?>

    <script src="../assets/js/main.js"></script>
</body>
</html>


