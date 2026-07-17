<?php

require_once '../config/connection.php';
require_once '../includes/security.php';
require_once '../includes/lang.php';
require_once '../includes/helpers.php';

send_security_headers();

// Page cache for anonymous users
if (page_cache_try()) exit;

// Get latest published posts
$stmt = $conn->prepare("
    SELECT posts.*, categories.cat_name, users.user_name
    FROM posts
    INNER JOIN categories ON posts.id_category = categories.id_category
    INNER JOIN users ON posts.id_user = users.id_user
    WHERE posts.status = :status
    ORDER BY posts.created_at DESC
    LIMIT 3
");

$stmt->execute([':status' => STATUS_PUBLISHED]);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="<?= get_lang_code() ?>" dir="<?= get_lang_dir() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tangier Vibes - <?= __('home_page_title') ?></title>
    <meta name="description" content="<?= __('home_meta_desc') ?>">
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link rel="apple-touch-icon" href="../assets/images/logo.png">
    <meta property="og:title" content="Tangier Vibes - <?= __('home_og_title') ?>">
    <meta property="og:description" content="<?= __('home_og_desc') ?>">
    <meta property="og:image" content="../assets/images/logo.png">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://tanger.lovestoblog.com/">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../<?= asset_version('assets/css/public.min.css') ?>">
</head>
<body>

<?php require '../includes/header.php'; ?>

    <main id="main_content">
        <!-- Hero section -->
        <section class="hero_section">
            <picture>
                <source srcset="../assets/images/home_1920.jpg 1920w, ../assets/images/home_1200.jpg 1200w, ../assets/images/home_768.jpg 768w, ../assets/images/home_480.jpg 480w" sizes="100vw" type="image/jpeg">
                <img src="../assets/images/home_1920.jpg" alt="<?= __('site_name') ?>" width="1920" height="1280" fetchpriority="high">
            </picture>
            <div class="hero_shadow"></div>

            <div class="hero_content">
                <p class="hero_label motion-reveal"><?= __('hero_label') ?></p>
                <h1 class="hero_title motion-reveal"><?= __('hero_title') ?></h1>
                <p class="hero_desc motion-reveal"><?= __('hero_desc') ?></p>
                <div class="hero_btns motion-reveal">
                    <a href="explore.php" class="btn_explor">
                        <?= __('hero_btn') ?>
                    </a>
                </div>
            </div>
        </section>

        <!-- Latest posts section -->
        <section class="latest_section">
            <div class="section_header motion-reveal">
                <h2 class="title"><?= __('latest_title') ?></h2>
                <p class="description"><?= __('latest_desc') ?></p>
            </div>

            <div class="grid_place">
                <?php if (!empty($posts)): ?>
                    <?php foreach ($posts as $post): ?>
                        <?= render_post_card($post, 'latest_read_more') ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="description"><?= __('latest_no_posts') ?></p>
                <?php endif; ?>
            </div>

            <div class="footer_section motion-reveal">
                <a href="explore.php" class="view_explor">
                    <?= __('latest_view_all') ?> <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                </a>
            </div>
        </section>

    </main>

<?php require '../includes/footer.php'; ?>
    <script src="../<?= asset_version('assets/js/public.min.js') ?>"></script>
<?php if (isset($_GET['login'])): ?>
<script>
(function() {
    function openAuthModal() {
        var toggle = document.querySelector('[data-auth-toggle]');
        if (toggle) toggle.click();
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', openAuthModal);
    } else {
        openAuthModal();
    }
})();
</script>
<?php endif; ?>
</body>
</html>
