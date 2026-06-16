<?php
session_start();
 require '../config/connection.php';
 require_once '../includes/security.php';

 send_security_headers();

 // get latest posts
 $stmt = $conn->prepare("
        select posts.*, categories.cat_name, users.user_name
        from posts
        inner join categories on posts.id_category = categories.id_category
        inner join users on posts.id_user = users.id_user
        where posts.status = 'published'
        order by posts.created_at desc
        limit 3
 ");

 $stmt->execute();

 $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);



?>









<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tangier Vibes - Explore Tangier, Morocco</title>
    <meta name="description" content="Discover the best of Tangier, Morocco. Beaches, restaurants, culture, hotels, and hidden gems curated by locals.">
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link rel="apple-touch-icon" href="../assets/images/logo.png">
    <meta property="og:title" content="Tangier Vibes - Explore Tangier, Morocco">
    <meta property="og:description" content="Your ultimate guide to discovering the magic, culture, and coastal beauty of Tangier, Morocco.">
    <meta property="og:image" content="../assets/images/logo.png">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://tanger.lovestoblog.com/">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/home.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
</head>
<body>


<?php require '../includes/header.php'; ?>

        <!-- hero -->
        <section class="hero_section">
            <picture>
                <source srcset="../assets/images/home_1920.jpg 1920w, ../assets/images/home_1200.jpg 1200w, ../assets/images/home_768.jpg 768w, ../assets/images/home_480.jpg 480w" sizes="100vw" type="image/jpeg">
                <img src="../assets/images/home.jpg" alt="Tangier Vibes" width="1920" height="1280" fetchpriority="high">
            </picture>
            <div class="hero_shadow"></div>


            <div class="hero_content">
               
                <p class="hero_label">WELCOME TO YOUR GATEWAY TO AFRICA</p>
                <h1 class="hero_title">Experience the Magic<br> of <span class="hero_highlight">Tangier</span></h1>

                <p class="hero_desc">Discover hidden beaches, legendary cafes, exquisite<br>restaurants, and historic landmarks in the Pearl of the North.</p>
                
                <div class="hero_btns">

                    <a href="explore.php" class="btn_explor">
                        Start Exploring
                    </a>

                </div>
            </div>
        </section>


        <!-- latest posts -->
        <section class="latest_section">
            <div class="section_header">
                <h2 class="title">Latest Places</h2>
                <p class="description">The newest additions to TangierVibes</p>
            </div>



            <div class="grid_place">
                    <?php if (!empty($posts)): ?>
                        <?php foreach ($posts as $post): ?>

                            <!-- post card -->
                            <a href="detail.php?id=<?= $post['id_post']; ?>" class="card_place">

                                <img     src="<?= htmlspecialchars($post['image']); ?>"     alt="<?= htmlspecialchars($post['title']); ?>"     loading="lazy">

                                <div class="card_content">

                                    <span class="category">
                                        <i class="fa-solid fa-layer-group"></i>
                                        <?= htmlspecialchars($post['cat_name']); ?>
                                    </span>

                                    <h3 class="title">
                                        <?= htmlspecialchars($post['title']); ?>
                                    </h3>

                                    <p class="location">
                                        <i class="fa-solid fa-user"></i>
                                        By <?= htmlspecialchars($post['user_name'] ?? 'Admin'); ?>
                                    </p>

                                    <p class="location">
                                        <i class="fa-solid fa-calendar-days"></i>
                                        <?= date('M d, Y', strtotime($post['created_at'])); ?>
                                    </p>

                                    <span class="btn">
                                        Read More <i class="fa-solid fa-arrow-right"></i>
                                    </span>

                                </div>


                            </a>

                        <?php endforeach; ?>
                    <?php else: ?>

                        <p class="description">No published places yet.</p>

                    <?php endif; ?>
                </div>

            


                <div class="footer_section">

                    <a href="explore.php" class="view_explor">

                        View All Places <i class="fa-solid fa-arrow-right"></i>

                    </a>

                </div>

        </section>


<?php require '../includes/footer.php' ?>
    <script src="../assets/js/main.js"></script>
</body>
</html>
