<?php
session_start();
require '../config/connection.php';
require_once '../includes/security.php';
require_once '../includes/lang.php';
 
 send_security_headers();

// check login
if (!isset($_SESSION['id_user'])) {
    header("Location: index.php");
    exit();
}

$id_user = $_SESSION['id_user'];
$user_name = $_SESSION['user_name'];
$role = $_SESSION['role'];

// count stats
if ($role === 'admin') {
    $count_stmt = $conn->prepare("
        select
            count(*) as total,
            sum(case when status = 'pending' then 1 else 0 end) as pending,
            sum(case when status = 'published' then 1 else 0 end) as published,
            sum(case when status = 'rejected' then 1 else 0 end) as rejected,
            sum(case when status = 'draft' and id_user = :id_user then 1 else 0 end) as draft
        from posts
        where status != 'draft' or id_user = :id_user2
    ");
    $count_stmt->execute([
        ':id_user' => $id_user,
        ':id_user2' => $id_user
    ]);
} else {
    $count_stmt = $conn->prepare("
        select
            count(*) as total,
            sum(case when status = 'pending' then 1 else 0 end) as pending,
            sum(case when status = 'published' then 1 else 0 end) as published,
            sum(case when status = 'rejected' then 1 else 0 end) as rejected,
            sum(case when status = 'draft' then 1 else 0 end) as draft
        from posts
        where id_user = :id_user
    ");
    $count_stmt->execute([
        ':id_user' => $id_user
    ]);
}

$stats = $count_stmt->fetch(PDO::FETCH_ASSOC);
$total_posts = (int)$stats['total'];
$pending_posts = (int)$stats['pending'];
$published_posts = (int)$stats['published'];
$rejected_posts = (int)$stats['rejected'];
$draft_posts = (int)$stats['draft'];

// get posts
if ($role === 'admin') {
    $stmt = $conn->prepare("
        select posts.*, categories.cat_name, users.user_name
        from posts
        inner join categories on posts.id_category = categories.id_category
        inner join users on posts.id_user = users.id_user
        where posts.status != 'draft' or posts.id_user = :id_user
        order by posts.updated_at desc, posts.created_at desc
    ");
    $stmt->bindValue(':id_user', $id_user, PDO::PARAM_INT);
    $stmt->execute();
} else {
     $stmt = $conn->prepare("
        select posts.*, categories.cat_name
        from posts
        inner join categories on posts.id_category = categories.id_category
        where posts.id_user = :id_user
        order by posts.updated_at desc, posts.created_at desc
    ");
    $stmt->bindValue(':id_user', $id_user, PDO::PARAM_INT);
    $stmt->execute();
}

$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="<?= get_lang_code() ?>" dir="<?= get_lang_dir() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Tangier Vibes</title>
    <meta name="description" content="Manage your posts, track publishing status, and moderate content from your Tangier Vibes dashboard.">
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link rel="apple-touch-icon" href="../assets/images/logo.png">
    <meta property="og:title" content="Dashboard - Tangier Vibes">
    <meta property="og:description" content="Manage your posts, track publishing status, and moderate content from your Tangier Vibes dashboard.">
    <meta property="og:image" content="../assets/images/logo.png">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/rtl.css">
</head>
<body>

<?php require '../includes/header.php'; ?>

<main class="dashboard_page">
    <!-- header -->
    <section class="dashboard_head">
        <div>
            <span class="dashboard_label">
                <i class="fa-solid fa-gauge"></i>
                <?= __('dashboard_label') ?>
            </span>
            <h1><?= __('dashboard_welcome', htmlspecialchars($user_name)) ?></h1>
            <p><?= __('dashboard_desc') ?></p>
        </div>

        <a href="add_post.php" class="add_post_btn">
            <i class="fa-solid fa-plus"></i>
            <?= __('dashboard_add_post') ?>
        </a>
    </section>

    <!-- stats -->
    <section class="stats_grid">
        <div class="stat_card">
            <span><?= __('dashboard_total') ?></span>
            <strong><?= $total_posts; ?></strong>
        </div>

        <div class="stat_card">
            <span><?= __('dashboard_published') ?></span>
            <strong><?= $published_posts; ?></strong>
        </div>

        <div class="stat_card">
            <span><?= __('dashboard_pending') ?></span>
            <strong><?= $pending_posts; ?></strong>
        </div>

        <div class="stat_card">
            <span><?= __('dashboard_draft') ?></span>
            <strong><?= $draft_posts; ?></strong>
        </div>

        <div class="stat_card">
            <span><?= __('dashboard_rejected') ?></span>
            <strong><?= $rejected_posts; ?></strong>
        </div>
    </section>

    <!-- posts table -->
    <section class="posts_box">
        <div class="box_head">
            <h2><?= __('dashboard_posts_title') ?></h2>
        </div>

        <p class="scroll_table">
            <i class="fa-solid fa-arrow-left-long"></i>
            <?= __('dashboard_scroll') ?>
            <i class="fa-solid fa-arrow-right-long"></i>
        </p>


        <?php if (!empty($posts)): ?>
            <div class="table_wrap">
                <table>
                    <thead>
                        <tr>
                            <th><?= __('dashboard_th_title') ?></th>
                            <th><?= __('dashboard_th_category') ?></th>

                            <?php if ($role === 'admin'): ?>
                                <th><?= __('dashboard_th_author') ?></th>
                            <?php endif; ?>

                            <th><?= __('dashboard_th_status') ?></th>
                            <th><?= __('dashboard_th_date') ?> <span class="date_format">(d/m/y)</span></th>
                            <th><?= __('dashboard_th_action') ?></th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($posts as $post): ?>
                            <tr>
                                <td>
                                    <span class="title_cell">
                                        <?= htmlspecialchars($post['title']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?= htmlspecialchars($post['cat_name']); ?>
                                </td>

                                <?php if ($role === 'admin'): ?>
                                    <td>
                                        <?= htmlspecialchars($post['user_name']); ?>
                                    </td>
                                <?php endif; ?>

                                <td>
                                    <span class="status <?= htmlspecialchars($post['status']); ?>">
                                        <?= htmlspecialchars($post['status']); ?>
                                    </span>
                                </td>

                                <td class="date_cell">
                                    <?= date('d/m/y', strtotime($post['created_at'])); ?>
                                </td>

                                <td>
                                    <div class="table_actions">
                                        <!-- view -->
                                        <a href="#view_post_<?= $post['id_post']; ?>" class="action_btn view">
                                            <i class="fa-solid fa-eye"></i>
                                            <span><?= __('dashboard_view') ?></span>
                                        </a>

                                        <?php if ($post['id_user'] == $id_user): ?>
                                            <!-- edit -->
                                            <a href="edit.php?id=<?= $post['id_post']; ?>" class="action_btn edit">
                                                <i class="fa-solid fa-pen"></i>
                                                <span><?= __('dashboard_edit') ?></span>
                                            </a>
                                        <?php endif; ?>

                                        <?php if ($role === 'admin' && $post['id_user'] != $id_user): ?>
                                            <?php if ($post['status'] === 'pending'): ?>
                                                <!-- approve -->
                                                <a href="../includes/actions.php?action=approve&id=<?= $post['id_post']; ?>&csrf_token=<?= $_SESSION['csrf_token']; ?>" class="action_btn approve">
                                                    <i class="fa-solid fa-check"></i>
                                                    <span><?= __('dashboard_approve') ?></span>
                                                </a>
                                            <?php endif; ?>

                                            <?php if ($post['status'] === 'pending'): ?>
                                                <!-- reject -->
                                                <a href="reject.php?id=<?= $post['id_post']; ?>&csrf_token=<?= $_SESSION['csrf_token']; ?>" class="action_btn reject">
                                                    <i class="fa-solid fa-xmark"></i>
                                                    <span><?= __('dashboard_reject') ?></span>
                                                </a>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        <?php if ($role !== 'admin' && $post['status'] === 'rejected' && !empty($post['rejection_reason'])): ?>
                                            <!-- reason -->
                                            <a href="#reject_reason_<?= $post['id_post']; ?>" class="action_btn reason">
                                                <i class="fa-solid fa-circle-info"></i>
                                                <span><?= __('dashboard_reason') ?></span>
                                            </a>
                                        <?php endif; ?>

                                        <!-- delete -->
                                        <a href="delete.php?id=<?= $post['id_post']; ?>&csrf_token=<?= $_SESSION['csrf_token']; ?>" class="action_btn delete"  onclick="return confirm('<?= __('dashboard_delete_confirm') ?>');">
                                            <i class="fa-solid fa-trash"></i>
                                            <span><?= __('dashboard_delete') ?></span>
                                        </a>
                                    </div>


                                    <!-- view modal -->
                                    <div id="view_post_<?= $post['id_post']; ?>" class="view_modal">
                                        <div class="view_card">

                                            <div class="view_head">
                                                <h3><?= __('dashboard_post_details') ?></h3>

                                                <a href="#" class="view_close">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </a>
                                            </div>

                                            <?php if (!empty($post['image'])): ?>
                                                <img src="<?= htmlspecialchars($post['image']); ?>" alt="<?= htmlspecialchars($post['title']); ?>" loading="lazy">
                                            <?php endif; ?>

                                            <div class="view_info">
                                                <p>
                                                    <strong><?= __('dashboard_post_title') ?></strong>
                                                    <?= htmlspecialchars($post['title']); ?>
                                                </p>
                                            </div>

                                            <div class="view_content">
                                                <strong><?= __('dashboard_post_content') ?></strong>

                                                <p>
                                                    <?= nl2br(htmlspecialchars($post['content'])); ?>
                                                </p>
                                            </div>

                                        </div>
                                    </div>

                                    <?php if ($role !== 'admin' && $post['status'] === 'rejected' && !empty($post['rejection_reason'])): ?>
                                        <!-- reason modal -->
                                        <div id="reject_reason_<?= $post['id_post']; ?>" class="reason_modal">
                                            <div class="reason_card">
                                                <div class="reason_head">
                                                    <span>
                                                        <i class="fa-solid fa-ban"></i>
                                                        <?= __('dashboard_rejection_reason') ?>
                                                    </span>

                                                    <a href="#" class="reason_close" aria-label="Close">
                                                        <i class="fa-solid fa-xmark"></i>
                                                    </a>
                                                </div>

                                                <h3><?= htmlspecialchars($post['title']); ?></h3>

                                                <p><?= nl2br(htmlspecialchars($post['rejection_reason'])); ?></p>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="empty_text"><?= __('dashboard_empty') ?></p>
        <?php endif; ?>
    </section>
</main>

<script src="../assets/js/main.js"></script>
</body>
</html>
