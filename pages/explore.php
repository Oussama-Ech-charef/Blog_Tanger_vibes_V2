<?php
session_start();
require '../config/connection.php';
require_once '../includes/security.php';
require_once '../includes/pagination.php';
require_once '../includes/lang.php';

send_security_headers();

$per_page = 6;
$page = get_valid_page();
$category_id = $_GET['category'] ?? null;
$keyword = trim($_GET['q'] ?? '');

// Redirect empty searches
if (isset($_GET['q']) && $keyword === '') {
    $redirect_params = [];
    if (!empty($_GET['category'])) {
        $redirect_params['category'] = $_GET['category'];
    }
    if (!empty($redirect_params)) {
        header('Location: explore.php?' . http_build_query($redirect_params));
    } else {
        header('Location: explore.php');
    }
    exit();
}

$query_params = [];

if (!empty($category_id)) {
    $query_params['category'] = $category_id;
}
if (!empty($keyword)) {
    $query_params['q'] = $keyword;
}

// Build WHERE clauses dynamically
$where = "posts.status = '" . STATUS_PUBLISHED . "'";
$params = [];

if (!empty($category_id)) {
    $where .= " AND posts.id_category = :category_id";
    $params[':category_id'] = $category_id;
}

if (!empty($keyword)) {
    $like_kw = '%' . $keyword . '%';
    $where .= " AND (posts.title LIKE :kw_title OR categories.cat_name LIKE :kw_cat)";
    $params[':kw_title'] = $like_kw;
    $params[':kw_cat'] = $like_kw;
}

// Count
$count_sql = "
    select count(*) from posts
    inner join categories on posts.id_category = categories.id_category
    where $where
";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->execute($params);
$total_records = (int)$count_stmt->fetchColumn();

$total_pages = get_total_pages($total_records, $per_page);
$current_page = min($page, $total_pages);

// Redirect if page was invalid
if ($current_page !== $page) {
    $redirect_params = $query_params;
    $redirect_params['page'] = $current_page;
    header('Location: explore.php?' . http_build_query($redirect_params));
    exit();
}

$offset = get_offset($current_page, $per_page);

$stmt = $conn->prepare("select * from categories order by cat_name asc");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Data query
$data_sql = "
    select posts.*, categories.cat_name, users.user_name
    from posts
    inner join categories on posts.id_category = categories.id_category
    inner join users on posts.id_user = users.id_user
    where $where
    order by posts.created_at desc
    limit :lim offset :off
";
$stmt = $conn->prepare($data_sql);

foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val, PDO::PARAM_STR);
}
$stmt->bindValue(':lim', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':off', $offset, PDO::PARAM_INT);
$stmt->execute();

$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$has_search = !empty($keyword);
$has_filter = !empty($category_id);

?>








<!DOCTYPE html>
<html lang="<?= get_lang_code() ?>" dir="<?= get_lang_dir() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explore Tangier - Tangier Vibes</title>
    <meta name="description" content="Browse all published places in Tangier. Filter by category and discover beaches, restaurants, culture, and more.">
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link rel="apple-touch-icon" href="../assets/images/logo.png">
    <meta property="og:title" content="Explore Tangier - Tangier Vibes">
    <meta property="og:description" content="Browse all published places in Tangier. Filter by category and discover beaches, restaurants, culture, and more.">
    <meta property="og:image" content="../assets/images/logo.png">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://tanger.lovestoblog.com/explore.php">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
   
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/cards.css">
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/explor.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <link rel="stylesheet" href="../assets/css/rtl.css">
</head>
<body>

        <?php require '../includes/header.php' ?>
        
        <main class="explore_page" id="main_content">

            <!-- page header -->
            <section class="explore_head">

                <span class="explore_label">
                    <i class="fa-solid fa-compass" aria-hidden="true"></i>
                    <?= __('explore_label') ?>
                </span>

                <h1><?= __('explore_title') ?></h1>

                <p>
                    <?= __('explore_desc') ?>
                </p>

            </section>

            <?php if ($has_search): ?>
                <div class="search_results_info">
                    <?php if ($total_records > 0): ?>
                        <p><?= __('explore_results_count', $total_records) ?> "<strong><?= htmlspecialchars($keyword); ?></strong>"</p>
                    <?php else: ?>
                        <p><?= __('explore_no_results_title') ?> "<strong><?= htmlspecialchars($keyword); ?></strong>"</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- filters -->
           <section class="explore_filters">
                <a href="explore.php<?= $has_search ? '?q=' . urlencode($keyword) : ''; ?>" class="<?= empty($category_id) ? 'active' : ''; ?>">
                    <?= __('explore_filter_all') ?>
                </a>

                <?php foreach ($categories as $category): ?>
                    <a 
                        href="explore.php?category=<?= $category['id_category']; ?><?= $has_search ? '&q=' . urlencode($keyword) : ''; ?>"
                        class="<?= $category_id == $category['id_category'] ? 'active' : ''; ?>"
                    >
                        <?= htmlspecialchars($category['cat_name']); ?>
                    </a>
                <?php endforeach; ?>
            </section>

            <!-- posts grid -->
            <section class="grid_place">

                <?php if (!empty($posts)): ?>
                    <?php foreach ($posts as $post): ?>

                        <!-- post card -->
                        <a href="detail.php?id=<?= $post['id_post']; ?>" class="card_place">
                            <img src="<?= htmlspecialchars($post['image']); ?>" alt="<?= htmlspecialchars($post['title']); ?>" loading="lazy">

                            <div class="card_content">

                                <span class="category">
                                    <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
                                    <?= htmlspecialchars($post['cat_name']); ?>
                                </span>

                                <h3 class="title">
                                    <?= htmlspecialchars($post['title']); ?>
                                </h3>

                                <p class="location">
                                    <i class="fa-solid fa-user" aria-hidden="true"></i>
                                    <?= __('latest_by') ?> <?= htmlspecialchars($post['user_name'] ?? 'Admin'); ?>
                                </p>

                                <p class="location">
                                    <i class="fa-solid fa-calendar-days" aria-hidden="true"></i>
                                    <?= date('M d, Y', strtotime($post['created_at'])); ?>
                                </p>

                                <span class="btn">
                                    <?= __('explore_read_more') ?> <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                                </span>
                            </div>
                        </a>

                    <?php endforeach; ?>
                <?php elseif ($has_search): ?>

                    <div class="empty_state">
                        <i class="fa-solid fa-search" aria-hidden="true"></i>
                        <h3><?= __('explore_no_results_title') ?></h3>
                        <p><?= __('explore_no_results_desc') ?></p>
                    </div>

                <?php else: ?>

                    <p class="description"><?= __('explore_no_posts') ?></p>

                <?php endif; ?>


            </section>

            <?= render_pagination($current_page, $total_pages, $query_params); ?>

        </main>
        
        
        
        <?php require '../includes/footer.php' ?>
        
    <script src="../assets/js/main.js"></script>

</body>
</html>
