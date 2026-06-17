<?php
session_start();
require '../config/connection.php';
require_once '../includes/security.php';

send_security_headers();

// stats
$pub_stmt = $conn->prepare("select count(*) from posts where status = 'published'");
$pub_stmt->execute();
$published_count = (int)$pub_stmt->fetchColumn();

$cat_stmt = $conn->prepare("select count(*) from categories");
$cat_stmt->execute();
$category_count = (int)$cat_stmt->fetchColumn();

$user_stmt = $conn->prepare("select count(*) from users");
$user_stmt->execute();
$user_count = (int)$user_stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Tangier Vibes</title>
    <meta name="description" content="Learn about Tangier Vibes, your ultimate guide to discovering the magic, culture, and coastal beauty of Tangier, Morocco.">
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link rel="apple-touch-icon" href="../assets/images/logo.png">
    <meta property="og:title" content="About Us - Tangier Vibes">
    <meta property="og:description" content="Learn about Tangier Vibes, your ultimate guide to discovering the magic, culture, and coastal beauty of Tangier, Morocco.">
    <meta property="og:image" content="../assets/images/logo.png">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://tanger.lovestoblog.com/about.php">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/about.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
</head>
<body>

<?php require '../includes/header.php'; ?>

<main class="about_page">

    <!-- hero -->
    <section class="about_head">
        <span class="about_label">
            <i class="fa-solid fa-info-circle"></i>
            About Tangier Vibes
        </span>
        <h1>Discover Tangier through stories, places,<br>culture and local experiences.</h1>
        <p>
            Your ultimate guide to exploring the vibrant city of Tangier — from hidden beaches and historic landmarks to legendary cafes and exquisite restaurants.
        </p>
    </section>

    <!-- about -->
    <section class="about_section">
        <h2 class="section_title">What is Tangier Vibes?</h2>
        <p class="section_desc">
            A platform built for travelers, locals, and anyone who loves Tangier.
        </p>
        <div class="about_text">
            <p>
                Tangier Vibes is a community-driven tourism blog that showcases everything this magnificent city has to offer. We bring together authentic stories, practical guides, and insider recommendations — all curated by people who know and love Tangier.
            </p>
            <p>
                Founded with the mission to make Tangier more accessible, our platform enables anyone to share their experiences, discover new places, and connect with the city's rich culture, history, and natural beauty.
            </p>
            <p>
                <strong>Our mission</strong> is to create the most comprehensive and trustworthy digital guide to Tangier. <strong>Our vision</strong> is a world where every visitor to Tangier feels like a local — guided by real stories from real people.
            </p>
        </div>
    </section>

    <!-- features -->
    <section class="about_section section_center">
        <h2 class="section_title">Explore Everything Tangier</h2>
        <p class="section_desc">
            From coastal escapes to cultural treasures — discover what makes Tangier unforgettable.
        </p>

        <div class="features_grid">
            <div class="feature_card">
                <div class="feature_icon"><i class="fa-solid fa-map-location-dot"></i></div>
                <h3>Discover Places</h3>
                <p>Uncover the most iconic spots and hidden gems across Tangier, from the Medina to the Caves of Hercules.</p>
            </div>

            <div class="feature_card">
                <div class="feature_icon"><i class="fa-solid fa-landmark"></i></div>
                <h3>Explore Local Culture</h3>
                <p>Immerse yourself in Tangier's unique blend of Moroccan, European, and African traditions.</p>
            </div>

            <div class="feature_card">
                <div class="feature_icon"><i class="fa-solid fa-utensils"></i></div>
                <h3>Find Restaurants</h3>
                <p>From street food stalls to fine dining — taste the best flavors Tangier has to offer.</p>
            </div>

            <div class="feature_card">
                <div class="feature_icon"><i class="fa-solid fa-book-open"></i></div>
                <h3>Learn Tangier History</h3>
                <p>Dive into the rich past of the Pearl of the North, shaped by Phoenicians, Romans, and countless civilizations.</p>
            </div>

            <div class="feature_card">
                <div class="feature_icon"><i class="fa-solid fa-pen-fancy"></i></div>
                <h3>Share Experiences</h3>
                <p>Write about your favorite places and share your Tangier stories with a growing community.</p>
            </div>

            <div class="feature_card">
                <div class="feature_icon"><i class="fa-solid fa-users"></i></div>
                <h3>Community Content</h3>
                <p>Discover authentic recommendations written by locals and travelers who know Tangier best.</p>
            </div>
        </div>
    </section>

    <!-- why choose us -->
    <section class="about_section">
        <h2 class="section_title">Why Choose Tangier Vibes</h2>
        <p class="section_desc">
            We make discovering Tangier simple, trustworthy, and enjoyable.
        </p>

        <div class="why_grid">
            <div class="why_item">
                <div class="why_icon"><i class="fa-solid fa-shield-check"></i></div>
                <div>
                    <h3>Trusted Content</h3>
                    <p>Every post is reviewed by our team to ensure quality and accuracy. You can trust what you read.</p>
                </div>
            </div>

            <div class="why_item">
                <div class="why_icon"><i class="fa-solid fa-compass"></i></div>
                <div>
                    <h3>Easy Navigation</h3>
                    <p>Browse by category, search by keyword, and find exactly what you are looking for in seconds.</p>
                </div>
            </div>

            <div class="why_item">
                <div class="why_icon"><i class="fa-solid fa-star"></i></div>
                <div>
                    <h3>Local Recommendations</h3>
                    <p>Get insider tips from people who live, work, and explore Tangier every day.</p>
                </div>
            </div>

            <div class="why_item">
                <div class="why_icon"><i class="fa-solid fa-mobile-screen"></i></div>
                <div>
                    <h3>Modern Experience</h3>
                    <p>Enjoy a fast, responsive interface that works beautifully on your phone, tablet, or desktop.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- stats -->
    <section class="about_section section_center">
        <h2 class="section_title">Tangier Vibes by the Numbers</h2>
        <p class="section_desc">
            Our growing community of contributors and explorers.
        </p>

        <div class="stats_grid">
            <div class="stat_card">
                <div class="stat_icon"><i class="fa-solid fa-newspaper"></i></div>
                <strong><?= $published_count; ?></strong>
                <span>Published Posts</span>
            </div>

            <div class="stat_card">
                <div class="stat_icon"><i class="fa-solid fa-layer-group"></i></div>
                <strong><?= $category_count; ?></strong>
                <span>Categories</span>
            </div>

            <div class="stat_card">
                <div class="stat_icon"><i class="fa-solid fa-users"></i></div>
                <strong><?= $user_count; ?></strong>
                <span>Registered Users</span>
            </div>
        </div>
    </section>

    <!-- cta -->
    <section class="cta_section">
        <h2>Explore Tangier Today</h2>
        <p>Start your journey through the Pearl of the North.</p>
        <a href="explore.php" class="cta_btn">
            Explore Posts <i class="fa-solid fa-arrow-right"></i>
        </a>
    </section>

</main>

<?php require '../includes/footer.php'; ?>
<script src="../assets/js/main.js"></script>
</body>
</html>
