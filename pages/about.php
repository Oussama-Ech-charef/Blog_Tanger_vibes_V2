<?php

require_once '../config/connection.php';
require_once '../includes/security.php';
require_once '../includes/lang.php';

send_security_headers();

// stats
$pub_stmt = $conn->prepare("SELECT COUNT(*) FROM posts WHERE status = :pub_status");
$pub_stmt->execute([':pub_status' => STATUS_PUBLISHED]);
$published_count = (int)$pub_stmt->fetchColumn();

$cat_stmt = $conn->prepare("SELECT COUNT(*) FROM categories");
$cat_stmt->execute();
$category_count = (int)$cat_stmt->fetchColumn();

$user_stmt = $conn->prepare("SELECT COUNT(*) FROM users");
$user_stmt->execute();
$user_count = (int)$user_stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="<?= get_lang_code() ?>" dir="<?= get_lang_dir() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('about_page_title') ?> - Tangier Vibes</title>
    <meta name="description" content="<?= __('about_meta_desc') ?>">
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link rel="apple-touch-icon" href="../assets/images/logo.png">
    <meta property="og:title" content="<?= __('about_og_title') ?> - Tangier Vibes">
    <meta property="og:description" content="<?= __('about_og_desc') ?>">
    <meta property="og:image" content="../assets/images/logo.png">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://tanger.lovestoblog.com/about.php">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/about.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <link rel="stylesheet" href="../assets/css/rtl.css">
    <link rel="stylesheet" href="../assets/css/components.css">
</head>
<body>

<?php require '../includes/header.php'; ?>

<main class="about_page" id="main_content">

    <!-- hero -->
    <section class="about_head">
        <span class="about_label">
            <i class="fa-solid fa-info-circle" aria-hidden="true"></i>
            <?= __('about_label') ?>
        </span>
        <h1><?= __('about_title') ?></h1>
        <p>
            <?= __('about_desc') ?>
        </p>
    </section>

    <!-- about -->
    <section class="about_section">
        <h2 class="section_title"><?= __('about_section_title') ?></h2>
        <p class="section_desc">
            <?= __('about_section_desc') ?>
        </p>
        <div class="about_text">
            <p>
                <?= __('about_text_p1') ?>
            </p>
            <p>
                <?= __('about_text_p2') ?>
            </p>
            <p>
                <?= __('about_text_p3') ?>
            </p>
        </div>
    </section>

    <!-- features -->
    <section class="about_section section_center">
        <h2 class="section_title"><?= __('about_features_title') ?></h2>
        <p class="section_desc">
            <?= __('about_features_desc') ?>
        </p>

        <div class="features_grid">
            <div class="feature_card">
                <div class="feature_icon"><i class="fa-solid fa-map-location-dot" aria-hidden="true"></i></div>
                <h3><?= __('about_feature_discover') ?></h3>
                <p><?= __('about_feature_discover_desc') ?></p>
            </div>

            <div class="feature_card">
                <div class="feature_icon"><i class="fa-solid fa-landmark" aria-hidden="true"></i></div>
                <h3><?= __('about_feature_culture') ?></h3>
                <p><?= __('about_feature_culture_desc') ?></p>
            </div>

            <div class="feature_card">
                <div class="feature_icon"><i class="fa-solid fa-utensils" aria-hidden="true"></i></div>
                <h3><?= __('about_feature_restaurants') ?></h3>
                <p><?= __('about_feature_restaurants_desc') ?></p>
            </div>

            <div class="feature_card">
                <div class="feature_icon"><i class="fa-solid fa-book-open" aria-hidden="true"></i></div>
                <h3><?= __('about_feature_history') ?></h3>
                <p><?= __('about_feature_history_desc') ?></p>
            </div>

            <div class="feature_card">
                <div class="feature_icon"><i class="fa-solid fa-pen-fancy" aria-hidden="true"></i></div>
                <h3><?= __('about_feature_share') ?></h3>
                <p><?= __('about_feature_share_desc') ?></p>
            </div>

            <div class="feature_card">
                <div class="feature_icon"><i class="fa-solid fa-users" aria-hidden="true"></i></div>
                <h3><?= __('about_feature_community') ?></h3>
                <p><?= __('about_feature_community_desc') ?></p>
            </div>
        </div>
    </section>

    <!-- why choose us -->
    <section class="about_section">
        <h2 class="section_title"><?= __('about_why_title') ?></h2>
        <p class="section_desc">
            <?= __('about_why_desc') ?>
        </p>

        <div class="why_grid">
            <div class="why_item">
                <div class="why_icon"><i class="fa-solid fa-shield-check" aria-hidden="true"></i></div>
                <div>
                    <h3><?= __('about_why_trusted') ?></h3>
                    <p><?= __('about_why_trusted_desc') ?></p>
                </div>
            </div>

            <div class="why_item">
                <div class="why_icon"><i class="fa-solid fa-compass" aria-hidden="true"></i></div>
                <div>
                    <h3><?= __('about_why_navigation') ?></h3>
                    <p><?= __('about_why_navigation_desc') ?></p>
                </div>
            </div>

            <div class="why_item">
                <div class="why_icon"><i class="fa-solid fa-star" aria-hidden="true"></i></div>
                <div>
                    <h3><?= __('about_why_local') ?></h3>
                    <p><?= __('about_why_local_desc') ?></p>
                </div>
            </div>

            <div class="why_item">
                <div class="why_icon"><i class="fa-solid fa-mobile-screen" aria-hidden="true"></i></div>
                <div>
                    <h3><?= __('about_why_modern') ?></h3>
                    <p><?= __('about_why_modern_desc') ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- stats -->
    <section class="about_section section_center">
        <h2 class="section_title"><?= __('about_stats_title') ?></h2>
        <p class="section_desc">
            <?= __('about_stats_desc') ?>
        </p>

        <div class="stats_grid">
            <div class="stat_card">
                <div class="stat_icon"><i class="fa-solid fa-newspaper" aria-hidden="true"></i></div>
                <strong><?= $published_count; ?></strong>
                <span><?= __('about_stats_posts') ?></span>
            </div>

            <div class="stat_card">
                <div class="stat_icon"><i class="fa-solid fa-layer-group" aria-hidden="true"></i></div>
                <strong><?= $category_count; ?></strong>
                <span><?= __('about_stats_categories') ?></span>
            </div>

            <div class="stat_card">
                <div class="stat_icon"><i class="fa-solid fa-users" aria-hidden="true"></i></div>
                <strong><?= $user_count; ?></strong>
                <span><?= __('about_stats_users') ?></span>
            </div>
        </div>
    </section>

    <!-- cta -->
    <section class="cta_section">
        <h2><?= __('about_cta_title') ?></h2>
        <p><?= __('about_cta_desc') ?></p>
        <a href="explore.php" class="cta_btn">
            <?= __('about_cta_btn') ?> <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
        </a>
    </section>

</main>

<?php require '../includes/footer.php'; ?>
<script src="../assets/js/main.js"></script>
</body>
</html>
