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
                <img src="../assets/images/logo.png" alt="Tangier Vibes Logo" class="logo_img" style="height:40px;width:auto;">
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
                <a href="#" class="social_icon" title="Facebook" aria-label="Facebook">
                    <i class="fa-brands fa-facebook-f"></i>
                </a>
                <a href="#" class="social_icon" title="Instagram" aria-label="Instagram">
                    <i class="fa-brands fa-instagram"></i>
                </a>
                <a href="#" class="social_icon" title="X (Twitter)" aria-label="X">
                    <i class="fa-brands fa-x-twitter"></i>
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
