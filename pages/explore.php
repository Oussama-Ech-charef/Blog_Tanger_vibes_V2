<?php
require_once '../config/db_connection.php';
require_once '../includes/Post.php';

$database = new Database();
$db = $database->getConnection();

$postObj = new Post($db);


$search = isset($_GET['search']) ? $_GET['search'] : null;
$category_id = isset($_GET['cat']) ? $_GET['cat'] : null;



$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$limit = 6;

$offset = ($page - 1) * $limit;


$categories = $postObj->getCategories();
$posts = $postObj->filterPosts($category_id, $search, $limit, $offset);


$total_posts = $postObj->countPosts($category_id, $search);
$total_pages = ceil($total_posts / $limit);



$current_category_name = "All Places";
if ($category_id) {
    foreach ($categories as $cat) {
        if ($cat['id'] == $category_id) {
            $current_category_name = $cat['name'];
            break;
        }
    }
}




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
    <link rel="stylesheet" href="../assets/css/explore.css?v=1.3">
</head>
<body>

    <?php require '../includes/header.php'; ?>

    <main>
        <!-- Explore Hero -->
        <section class="explore_hero">
            <img src="../assets/img/explore.jpg" alt="Tangier Panorama" class="explore_hero_img">
            <div class="explore_hero_content">
                <h1 class="explore_hero_title">Explore Tangier</h1>
                <p class="explore_hero_desc">Discover the magic of the Pearl of the North, from historic landmarks to hidden beaches.</p>
            </div>
        </section>

        <!-- Filters Section -->
        <section class="filters_container">
            <div class="category_filters">
                <a href="explore.php" class="cat_filter_btn <?= !$category_id ? 'active' : '' ?>">
                    <i class="fa-solid fa-border-all"></i> All
                </a>
                <?php foreach($categories as $cat): ?>
                    <a href="explore.php?cat=<?= $cat['id'] ?><?= $search ? '&search='.urlencode($search) : '' ?>" 
                       class="cat_filter_btn <?= $category_id == $cat['id'] ? 'active' : '' ?>">
                        <i class="fa-solid fa-<?= getCategoryIcon($cat['name']) ?>"></i> <?= htmlspecialchars($cat['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <?php if ($search): ?>
                <div class="search_status">
                    Showing results for: <strong>"<?= htmlspecialchars($search) ?>"</strong> 
                    in <strong><?= htmlspecialchars($current_category_name) ?></strong>
                </div>
            <?php endif; ?>
        </section>

        <!-- Results Grid -->
        <section class="explore_results">
            <?php if (!empty($posts)): ?>
                <div class="places_grid" id="posts-grid">
                    <?php foreach($posts as $post): ?>
                        <a href="post_detail.php?id=<?= $post['id']; ?>" class="place_card">
                            <img src="<?= htmlspecialchars($post['image']); ?>" class="place_card_img" alt="<?= htmlspecialchars($post['title']); ?>">
                            <div class="place_card_overlay">
                                <span class="place_card_category">
                                    <i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($post['cat_name']); ?>
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
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>&cat=<?= $category_id ?>&search=<?= urlencode($search) ?>" class="pagination_btn prev">
                            <i class="fas fa-arrow-left"></i> Previous
                        </a>
                    <?php endif; ?>

                    <?php if ($total_pages > 0): ?>
                        <span class="page_info">
                            <?= $page ?> / <?= $total_pages ?>
                        </span>
                    <?php endif; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?= $page + 1 ?>&cat=<?= $category_id ?>&search=<?= urlencode($search) ?>" class="pagination_btn next">
                            Next <i class="fas fa-arrow-right"></i>
                        </a>
                    <?php endif; ?>
                </div>

            <?php else: ?>
                <div class="no_results">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <h3>No places found</h3>
                    <p>Try adjusting your search or category filters to find what you're looking for.</p>
                    <br>
                    <a href="explore.php" class="hero_btn_primary">Reset All Filters</a>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <?php require '../includes/footer.php'; ?>

    <script src="../assets/js/main.js"></script>
</body>
</html>

<?php
function getCategoryIcon($cat_name) {
    $icons = [
        'Hotels' => 'fa-hotel',
        'Restaurants' => 'fa-utensils',
        'Cafes' => 'fa-mug-hot',
        'Beaches' => 'fa-umbrella-beach',
        'Parks' => 'fa-tree',
        'Museums' => 'fa-museum',
        'Historical Sites' => 'fa-monument'
    ];
    return $icons[$cat_name] ?? 'fa-location-dot';
}
?>
