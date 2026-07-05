<?php

require_once '../config/connection.php';
require_once '../includes/security.php';
require_once '../includes/lang.php';
require_once '../includes/helpers.php';
 
 send_security_headers();

// check post id
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$post_id = $_GET['id'];


// get post
$stmt = $conn->prepare("
    select posts.*, categories.cat_name, users.user_name
    from posts
    inner join categories on posts.id_category = categories.id_category
    inner join users on posts.id_user = users.id_user
    where posts.id_post = :id_post and posts.status = :pub_status
");

$stmt->execute([
    ':id_post' => $post_id,
    ':pub_status' => STATUS_PUBLISHED
    ]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    header('Location: index.php');
    exit;
}

$comment_error = '';
$comment_success = '';

if (isset($_SESSION['comment_added']) && $_SESSION['comment_added']) {
    $comment_success = __('detail_comment_success');
    unset($_SESSION['comment_added']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!validate_csrf_token($csrf_token)) {
        $comment_error = __('detail_comment_error_generic');
    }

    $author_name = trim($_POST['name'] ?? '');
    $comment_text = trim($_POST['message'] ?? '');

    if (empty($comment_error)) {
        if (empty($author_name) || empty($comment_text)) {
            $comment_error = __('detail_comment_error_required');
        } elseif (strlen($author_name) > 100) {
            $comment_error = __('detail_comment_error_name_length');
        } elseif (strlen($comment_text) > 1000) {
            $comment_error = __('detail_comment_error_text_length');
        }
    }

    if (empty($comment_error)) {
        try {
            $comment_user_id = isset($_SESSION['id_user']) ? (int)$_SESSION['id_user'] : null;
            $stmt = $conn->prepare("
                insert into comments (id_post, id_user, author_name, comment_text, status)
                values (:id_post, :id_user, :author_name, :comment_text, 'pending')
            ");
            $stmt->execute([
                ':id_post' => $post_id,
                ':id_user' => $comment_user_id,
                ':author_name' => $author_name,
                ':comment_text' => $comment_text
            ]);

            // log activity
            try {
                $log = $conn->prepare("insert into activity_log (action_type, description, user_id, entity_type, entity_id) values ('comment_added', :desc, null, 'comment', :eid)");
                $log->execute([':desc' => "$author_name commented on: " . $post['title'], ':eid' => $conn->lastInsertId()]);
            } catch (PDOException $e) {
                error_log("Activity log error: " . $e->getMessage());
            }

            $_SESSION['comment_added'] = true;
            header("Location: detail.php?id=" . $post_id . "#comments");
            exit();
        } catch (PDOException $e) {
            error_log("Comment insert error: " . $e->getMessage());
            $comment_error = __('detail_comment_error_generic');
        }
    }
}

// get approved comments for this post
$comment_stmt = $conn->prepare("
    select id_comment, author_name, comment_text, created_at
    from comments
    where id_post = :id_post and status = 'approved'
    order by created_at desc
");
$comment_stmt->execute([':id_post' => $post_id]);
$comments = $comment_stmt->fetchAll(PDO::FETCH_ASSOC);
$comment_count = count($comments);
?>

<!DOCTYPE html>
<html lang="<?= get_lang_code() ?>" dir="<?= get_lang_dir() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['title']) ?> - Tangier Vibes</title>
    <meta name="description" content="<?= htmlspecialchars(substr(strip_tags($post['content']), 0, 150)) ?>">
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link rel="apple-touch-icon" href="../assets/images/logo.png">
    <meta property="og:title" content="<?= htmlspecialchars($post['title']) ?> - Tangier Vibes">
    <meta property="og:description" content="<?= htmlspecialchars(substr(strip_tags($post['content']), 0, 150)) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($post['image'] ? '../' . $post['image'] : '../assets/images/logo.png') ?>">
    <meta property="og:type" content="article">
    <meta property="og:url" content="https://tanger.lovestoblog.com/detail.php?id=<?= $post['id_post'] ?>">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/detail.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <link rel="stylesheet" href="../assets/css/rtl.css">
</head>
<body>


<?php require '../includes/header.php' ?>

    <div class="detail_container" id="main_content">

        <!-- category -->
        <div class="detail_category">
            <i class="fa-solid fa-layer-group" aria-hidden="true"></i> <?= __('detail_tanger_label') ?> <span class="cat_name"><?= htmlspecialchars($post['cat_name']); ?></span>
        </div>

        <h1><?= htmlspecialchars($post['title']); ?></h1>

        <!-- post info -->
        <div class="icons">
            <span><i class="fa-solid fa-calendar-days" aria-hidden="true"></i><?= date(__('date_format_detail'), strtotime($post['created_at'])); ?></span>
            <span><i class="fa-solid fa-circle-user" aria-hidden="true"></i><?= __('detail_by') ?> <?= htmlspecialchars($post['user_name'] ?? __('admin_label')); ?></span>
            
        </div>

        <!-- image -->
        <?php if (!empty($post['image'])): ?>
            <img src="../<?= htmlspecialchars($post['image']); ?>" alt="<?= htmlspecialchars($post['title']); ?>" loading="lazy">
        <?php endif; ?>

        <!-- content -->
        <div class="content">
            <?= render_post_content($post['content']); ?>
        </div>


        <!-- share links -->
        <?php
        $share_url = urlencode('https://tanger.lovestoblog.com/detail.php?id=' . $post['id_post']);
        $share_title = urlencode(htmlspecialchars_decode($post['title']) . ' - Tangier Vibes');
        ?>
        <div class="social">
            <i class="fas fa-share-alt" aria-hidden="true"></i> <?= __('detail_share') ?>:
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $share_url ?>" target="_blank" rel="noopener noreferrer">Facebook</a> /
            <a href="https://twitter.com/intent/tweet?text=<?= $share_title ?>&url=<?= $share_url ?>" target="_blank" rel="noopener noreferrer">X (Twitter)</a> /
            <a href="https://wa.me/?text=<?= $share_title ?>%20<?= $share_url ?>" target="_blank" rel="noopener noreferrer">WhatsApp</a>
        </div>

        <!-- map design -->
        <div class="map_box">
            <iframe
                src="https://www.openstreetmap.org/export/embed.html?bbox=-5.85,35.75,-5.82,35.77&layer=mapnik&marker=35.7595,-5.8368"
                width="100%"
                height="400"
                class="border_0"
                allowfullscreen=""
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>


        <!-- comments -->
        <div id="comments" class="comments_posts">
            <div class="comment_title">
                <i class="fa-solid fa-comment-dots" aria-hidden="true"></i> <?= __('detail_comments_title') ?>
                <span><?= $comment_count; ?></span>
            </div>
        </div>

        <div class="comments_list">
            <?php if (!empty($comments)): ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="comment_item">
                        <div class="comment_header">
                            <span class="comment_name"><?= htmlspecialchars($comment['author_name']) ?></span>
                            <span><?= date(__('date_format_detail'), strtotime($comment['created_at'])) ?></span>
                        </div>
                        <div class="comment_text"><?= htmlspecialchars($comment['comment_text']) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="description"><?= __('detail_comments_empty') ?></p>
            <?php endif; ?>
        </div>

        <?php if (!empty($comment_error)): ?>
            <?php render_notification($comment_error, 'error'); ?>
        <?php endif; ?>

        <div class="comment_form">
            <h3 class="comment_title"><?= __('detail_comment_leave') ?></h3>

            <form action="" method="POST">
                <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
                <div class="form_name">
                    <label><?= __('detail_comment_name_label') ?> :</label>
                    <input type="text" name="name" placeholder="<?= __('detail_comment_name_placeholder') ?>" required>
                </div>
                <div class="form_desc">
                    <label><?= __('detail_comment_message_label') ?> :</label>
                    <textarea name="message" placeholder="<?= __('detail_comment_message_placeholder') ?>" required></textarea>
                </div>
                <button type="submit"><i class="fa-solid fa-paper-plane" aria-hidden="true"></i> <?= __('detail_comment_btn') ?></button>
            </form>
        </div>


    </div>




    <?php if (!empty($comment_success)): ?>
        <?php render_notification($comment_success, 'success'); ?>
    <?php endif; ?>

    <?php require '../includes/footer.php'; ?>
    <script src="../assets/js/main.js"></script>
</body>

</html>
