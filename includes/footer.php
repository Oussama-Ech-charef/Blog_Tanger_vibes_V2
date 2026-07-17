<?php
// Ensure DB connection for dynamic categories
if (!isset($conn) && file_exists(__DIR__ . '/../config/connection.php')) {
    require_once __DIR__ . '/../config/connection.php';
}
require_once __DIR__ . '/lang.php';

$footer_categories = [];
if (isset($conn)) {
    try {
        $cat_stmt = $conn->prepare("select id_category, cat_name from categories order by cat_name asc");
        $cat_stmt->execute();
        $footer_categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Footer categories: " . $e->getMessage());
    }
}
?>

<footer class="footer">

    <div class="footer_top">

        <!-- brand -->
        <div class="footer_brand">
            <a href="../pages/index.php" class="logo">
                <img src="../assets/images/logo.png" alt="<?= __('site_logo_alt') ?>" class="logo_img logo_header">
            </a>
            <p class="footer_desc">
                <?= __('footer_desc') ?>
            </p>
        </div>

        <!-- explore -->
        <div class="footer_col">
            <h4 class="footer_title"><?= __('footer_explore_title') ?></h4>
            <ul class="footer_links">
                <li><a href="../pages/index.php" class="footer_link"><?= __('nav_home') ?></a></li>
                <li><a href="../pages/explore.php" class="footer_link"><?= __('nav_explore') ?></a></li>
                <li><a href="../pages/about.php" class="footer_link"><?= __('nav_about') ?></a></li>
                <li><a href="../pages/contact.php" class="footer_link"><?= __('nav_contact') ?></a></li>
            </ul>
        </div>

        <!-- categories -->
        <div class="footer_col">
            <h4 class="footer_title"><?= __('footer_categories_title') ?></h4>
            <ul class="footer_links">
                <?php if (!empty($footer_categories)): ?>
                    <?php foreach ($footer_categories as $cat): ?>
                        <li>
                            <a href="../pages/explore.php?category=<?= (int)$cat['id_category']; ?>" class="footer_link">
                                <?= htmlspecialchars($cat['cat_name']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li><span class="footer_link"><?= __('footer_no_categories') ?></span></li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- follow us -->
        <div class="footer_col">
            <h4 class="footer_title"><?= __('footer_follow_title') ?></h4>
            <div class="footer_icons">
                <a href="#" class="social_icon" data-social="facebook" data-tooltip="<?= __('social_facebook') ?>" aria-label="<?= __('social_facebook') ?>">
                    <i class="fa-brands fa-facebook-f" aria-hidden="true"></i>
                </a>
                <a href="#" class="social_icon" data-social="instagram" data-tooltip="<?= __('social_instagram') ?>" aria-label="<?= __('social_instagram') ?>">
                    <i class="fa-brands fa-instagram" aria-hidden="true"></i>
                </a>
                <a href="#" class="social_icon" data-social="x" data-tooltip="<?= __('social_x') ?>" aria-label="<?= __('social_x') ?>">
                    <i class="fa-brands fa-x-twitter" aria-hidden="true"></i>
                </a>
            </div>
        </div>

    </div>

    <!-- bottom bar -->
    <div class="footer_bottom">
        <div class="footer_bottom_inner">
            <p class="footer_copy">
                <?= __('footer_copyright', date('Y')) ?>
            </p>
            <ul class="footer_bottom_links">
                <li><a href="#" class="footer_bottom_link"><?= __('footer_privacy') ?></a></li>
                <li><a href="#" class="footer_bottom_link"><?= __('footer_terms') ?></a></li>
                <li><a href="../pages/contact.php" class="footer_bottom_link"><?= __('footer_contact') ?></a></li>
            </ul>
        </div>
    </div>

</footer>

<?php require_once __DIR__ . '/auth_modal.php'; ?>
<script>
var Lang = {
    imgUnavailableTitle: <?= json_encode(__('img_unavailable_title')) ?>,
    imgUnavailableDesc: <?= json_encode(__('img_unavailable_desc')) ?>,
    imgUnavailableHint: <?= json_encode(__('img_unavailable_hint')) ?>,
    authCreatedFallback: <?= json_encode(__('auth_js_created_fallback')) ?>,
    authErrorFallback: <?= json_encode(__('auth_js_error_fallback')) ?>,
};
</script>
<script type="importmap">
{
    "imports": {
        "motion": "https://cdn.jsdelivr.net/npm/motion@11/+esm"
    }
}
</script>
<script type="module" src="../<?= asset_version('assets/js/animations.min.js') ?>"></script>
