<?php
session_start();
require '../config/connection.php';
require_once '../includes/security.php';
require_once '../includes/pagination.php';

send_security_headers();

// check login
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit();
}

$id_user = $_SESSION['id_user'];
$user_name = $_SESSION['user_name'];
$role = $_SESSION['role'];

$per_page = 10;
$page = get_valid_page();

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

// paginate
$total_pages = get_total_pages($total_posts, $per_page);
$current_page = min($page, $total_pages);

if ($current_page !== $page) {
    header('Location: dashboard.php?page=' . $current_page);
    exit();
}

$offset = get_offset($current_page, $per_page);

// get paginated posts
if ($role === 'admin') {
    $stmt = $conn->prepare("
        select posts.*, categories.cat_name, users.user_name
        from posts
        inner join categories on posts.id_category = categories.id_category
        inner join users on posts.id_user = users.id_user
        where posts.status != 'draft' or posts.id_user = :id_user
        order by posts.created_at desc
        limit :lim offset :off
    ");
    $stmt->bindValue(':id_user', $id_user, PDO::PARAM_INT);
    $stmt->bindValue(':lim', $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
    $stmt->execute();
} else {
     $stmt = $conn->prepare("
        select posts.*, categories.cat_name
        from posts
        inner join categories on posts.id_category = categories.id_category
        where posts.id_user = :id_user
        order by posts.created_at desc
        limit :lim offset :off
    ");
    $stmt->bindValue(':id_user', $id_user, PDO::PARAM_INT);
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
    <title>Dashboard - Tangier Vibes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>

<?php require '../includes/header.php'; ?>

<main class="dashboard_page">
    <!-- header -->
    <section class="dashboard_head">
        <div>
            <span class="dashboard_label">
                <i class="fa-solid fa-gauge"></i>
                Dashboard
            </span>
            <h1>Welcome, <?= htmlspecialchars($user_name); ?></h1>
            <p>Manage your posts and track their publishing status.</p>
        </div>

        <a href="add_post.php" class="add_post_btn">
            <i class="fa-solid fa-plus"></i>
            Add Post
        </a>
    </section>

    <!-- stats -->
    <section class="stats_grid">
        <div class="stat_card">
            <span>Total Posts</span>
            <strong><?= $total_posts; ?></strong>
        </div>

        <div class="stat_card">
            <span>Published</span>
            <strong><?= $published_posts; ?></strong>
        </div>

        <div class="stat_card">
            <span>Pending</span>
            <strong><?= $pending_posts; ?></strong>
        </div>

        <div class="stat_card">
            <span>Draft</span>
            <strong><?= $draft_posts; ?></strong>
        </div>

        <div class="stat_card">
            <span>Rejected</span>
            <strong><?= $rejected_posts; ?></strong>
        </div>
    </section>

    <!-- posts table -->
    <section class="posts_box">
        <div class="box_head">
            <h2>Posts</h2>
        </div>

        <p class="scroll_table">
            <i class="fa-solid fa-arrow-left-long"></i>
            Scroll table
            <i class="fa-solid fa-arrow-right-long"></i>
        </p>


        <?php if (!empty($posts)): ?>
            <div class="table_wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>

                            <?php if ($role === 'admin'): ?>
                                <th>Author</th>
                            <?php endif; ?>

                            <th>Status</th>
                            <th>Date <span class="date_format">(d/m/y)</span></th>
                            <th>Action</th>
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
                                            <span>View</span>
                                        </a>

                                        <?php if ($post['id_user'] == $id_user): ?>
                                            <!-- edit -->
                                            <a href="edit.php?id=<?= $post['id_post']; ?>" class="action_btn edit">
                                                <i class="fa-solid fa-pen"></i>
                                                <span>Edit</span>
                                            </a>
                                        <?php endif; ?>

                                        <?php if ($role === 'admin' && $post['id_user'] != $id_user): ?>
                                            <?php if ($post['status'] === 'pending'): ?>
                                                <!-- approve -->
                                                <a href="../includes/actions.php?action=approve&id=<?= $post['id_post']; ?>&csrf_token=<?= $_SESSION['csrf_token']; ?>" class="action_btn approve">
                                                    <i class="fa-solid fa-check"></i>
                                                    <span>Approve</span>
                                                </a>
                                            <?php endif; ?>

                                            <?php if ($post['status'] === 'pending'): ?>
                                                <!-- reject -->
                                                <a href="reject.php?id=<?= $post['id_post']; ?>&csrf_token=<?= $_SESSION['csrf_token']; ?>" class="action_btn reject">
                                                    <i class="fa-solid fa-xmark"></i>
                                                    <span>Reject</span>
                                                </a>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        <?php if ($role !== 'admin' && $post['status'] === 'rejected' && !empty($post['rejection_reason'])): ?>
                                            <!-- reason -->
                                            <a href="#reject_reason_<?= $post['id_post']; ?>" class="action_btn reason">
                                                <i class="fa-solid fa-circle-info"></i>
                                                <span>Reason</span>
                                            </a>
                                        <?php endif; ?>

                                        <!-- delete -->
                                        <a href="delete.php?id=<?= $post['id_post']; ?>&csrf_token=<?= $_SESSION['csrf_token']; ?>" class="action_btn delete"  onclick="return confirm('Are you sure you want to delete this post?');">
                                            <i class="fa-solid fa-trash"></i>
                                            <span>Delete</span>
                                        </a>
                                    </div>


                                    <!-- view modal -->
                                    <div id="view_post_<?= $post['id_post']; ?>" class="view_modal">
                                        <div class="view_card">

                                            <div class="view_head">
                                                <h3>Post details</h3>

                                                <a href="#" class="view_close">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </a>
                                            </div>

                                            <?php if (!empty($post['image'])): ?>
                                                <img src="<?= htmlspecialchars($post['image']); ?>" alt="<?= htmlspecialchars($post['title']); ?>">
                                            <?php endif; ?>

                                            <div class="view_info">
                                                <p>
                                                    <strong>Title</strong>
                                                    <?= htmlspecialchars($post['title']); ?>
                                                </p>
                                            </div>

                                            <div class="view_content">
                                                <strong>Content</strong>

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
                                                        Rejection Reason
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

            <?= render_pagination($current_page, $total_pages, []); ?>
        <?php else: ?>
            <p class="empty_text">No posts yet.</p>
        <?php endif; ?>
    </section>
</main>

<script src="../assets/js/main.js"></script>
</body>
</html>
