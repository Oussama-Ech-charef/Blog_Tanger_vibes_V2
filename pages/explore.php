<?php
session_start();
require '../config/connection.php';
require_once '../includes/security.php';
require_once '../includes/pagination.php';

send_security_headers();

$per_page = 6;
$page = get_valid_page();
$category_id = $_GET['category'] ?? null;

// Count first to validate pages before fetching
if (!empty($category_id)) {
    $count_stmt = $conn->prepare("
        select count(*) from posts
        where status = 'published'
        and id_category = :category_id
    ");
    $count_stmt->execute([':category_id' => $category_id]);
    $total_records = (int)$count_stmt->fetchColumn();
    $query_params = ['category' => $category_id];
} else {
    $count_stmt = $conn->prepare("
        select count(*) from posts
        where status = 'published'
    ");
    $count_stmt->execute();
    $total_records = (int)$count_stmt->fetchColumn();
    $query_params = [];
}

$total_pages = get_total_pages($total_records, $per_page);
$current_page = min($page, $total_pages);

// Redirect if page was invalid
if ($current_page !== $page) {
    if (!empty($category_id)) {
        header('Location: explore.php?category=' . urlencode($category_id) . '&page=' . $current_page);
    } else {
        header('Location: explore.php?page=' . $current_page);
    }
    exit();
}

$offset = get_offset($current_page, $per_page);

$stmt = $conn->prepare("select * from categories order by cat_name asc");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!empty($category_id)) {
    $stmt = $conn->prepare("
        select posts.*, categories.cat_name, users.user_name
        from posts
        inner join categories on posts.id_category = categories.id_category
        inner join users on posts.id_user = users.id_user
        where posts.status = 'published'
        and posts.id_category = :category_id
        order by posts.created_at desc
        limit :lim offset :off
    ");
    $stmt->bindValue(':category_id', $category_id, PDO::PARAM_INT);
    $stmt->bindValue(':lim', $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
    $stmt->execute();
} else {
    $stmt = $conn->prepare("
        select posts.*, categories.cat_name, users.user_name
        from posts
        inner join categories on posts.id_category = categories.id_category
        inner join users on posts.id_user = users.id_user
        where posts.status = 'published'
        order by posts.created_at desc
        limit :lim offset :off
    ");
    $stmt->bindValue(':lim', $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
    $stmt->execute();
}

$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>








<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Tangier Vibes</title>
    
  
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
   
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/explor.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    
</head>
<body>

        <?php require '../includes/header.php' ?>
        
        <main class="explore_page">

            <!-- page header -->
            <section class="explore_head">

                <span class="explore_label">
                    <i class="fa-solid fa-compass"></i>
                    Explore Tangier
                </span>

                <h1>Discover all posts</h1>

                <p>
                    Explore the best places, beaches, food spots, culture, and hidden vibes around Tangier.
                </p>

            </section>

            <!-- filters -->
           <section class="explore_filters">
                <a href="explore.php" class="<?= empty($category_id) ? 'active' : ''; ?>">
                    All
                </a>

                <?php foreach ($categories as $category): ?>
                    <a 
                        href="explore.php?category=<?= $category['id_category']; ?>"
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


            </section>

            <?= render_pagination($current_page, $total_pages, $query_params); ?>

        </main>
        
        
        
        <?php require '../includes/footer.php' ?>
        
    <script src="../assets/js/main.js"></script>

</body>
</html>
