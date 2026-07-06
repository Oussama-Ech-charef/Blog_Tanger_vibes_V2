<?php

require_once '../config/connection.php';
require_once '../includes/security.php';
require_once '../includes/pagination.php';
require_once '../includes/lang.php';
require_once '../includes/helpers.php';

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
$where = "posts.status = :pub_status";
$params = [':pub_status' => STATUS_PUBLISHED];

if (!empty($category_id)) {
    $where .= " AND posts.id_category = :category_id";
    $params[':category_id'] = (int)$category_id;
}

if (!empty($keyword)) {
    $like_kw = '%' . $keyword . '%';
    $where .= " AND (posts.title LIKE :kw_title OR categories.cat_name LIKE :kw_cat OR posts.content LIKE :kw_content)";
    $params[':kw_title'] = $like_kw;
    $params[':kw_cat'] = $like_kw;
    $params[':kw_content'] = $like_kw;
}

// Count
$count_sql = "
    SELECT COUNT(*) FROM posts
    INNER JOIN categories ON posts.id_category = categories.id_category
    WHERE $where
";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->execute($params);
$total_records = (int)$count_stmt->fetchColumn();

$total_pages = get_total_pages($total_records, $per_page);
$current_page = min($page, $total_pages);

// Redirect if page was invalid
if ($current_page !== $page) {
    $redirect_params['page'] = $current_page;
    header('Location: explore.php?' . http_build_query($redirect_params));
    exit();
}

$offset = get_offset($current_page, $per_page);

$cat_stmt = $conn->prepare("SELECT * FROM categories ORDER BY cat_name ASC");
$cat_stmt->execute();
$categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);

// Data query
$data_sql = "
    SELECT posts.*, categories.cat_name, users.user_name
    FROM posts
    INNER JOIN categories ON posts.id_category = categories.id_category
    INNER JOIN users ON posts.id_user = users.id_user
    WHERE $where
    ORDER BY posts.created_at DESC
    LIMIT :lim OFFSET :off
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
    <title><?= __('explore_page_title') ?></title>
    <meta name="description" content="<?= __('explore_meta_desc') ?>">
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link rel="apple-touch-icon" href="../assets/images/logo.png">
    <meta property="og:title" content="<?= __('explore_og_title') ?>">
    <meta property="og:description" content="<?= __('explore_og_desc') ?>">
    <meta property="og:image" content="../assets/images/logo.png">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://tanger.lovestoblog.com/explore.php">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/cards.css">
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/explore.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <link rel="stylesheet" href="../assets/css/rtl.css">
</head>
<body>

    <?php require '../includes/header.php'; ?>

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
                    href="explore.php?category=<?= (int)$category['id_category']; ?><?= $has_search ? '&q=' . urlencode($keyword) : ''; ?>"
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
                    <?= render_post_card($post, 'explore_read_more') ?>
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

    <?php require '../includes/footer.php'; ?>

    <script src="../assets/js/main.js"></script>

</body>
</html>
