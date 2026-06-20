<?php

session_start();

require '../config/connection.php';
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
    where posts.id_post = :id_post and posts.status = '" . STATUS_PUBLISHED . "'
");

$stmt->execute([
    ':id_post' => $post_id
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
            $stmt = $conn->prepare("
                insert into comments (id_post, author_name, comment_text, status)
                values (:id_post, :author_name, :comment_text, 'approved')
            ");
            $stmt->execute([
                ':id_post' => $post_id,
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

            // notify post author
            if (!empty($post['id_user']) && (!isset($_SESSION['id_user']) || (int)$_SESSION['id_user'] !== (int)$post['id_user'])) {
                try {
                    $n = $conn->prepare("INSERT INTO user_notifications (id_user,type,message,link) VALUES (:uid,'new_comment',:msg,:lnk)");
                    $n->execute([':uid'=>$post['id_user'], ':msg'=>"$author_name commented on your post: " . $post['title'], ':lnk'=>"detail.php?id=$post_id"]);
                } catch (PDOException $e) {
                    error_log("Notification error: " . $e->getMessage());
                }
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
    <meta property="og:image" content="<?= htmlspecialchars($post['image'] ?? '../assets/images/logo.png') ?>">
    <meta property="og:type" content="article">
    <meta property="og:url" content="https://tanger.lovestoblog.com/detail.php?id=<?= $post['id_post'] ?>">
    <meta name="twitter:card" content="summary_large_image">
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
        <img src="<?= htmlspecialchars($post['image']); ?>" alt="<?= htmlspecialchars($post['title']); ?>" loading="lazy">

        <!-- content -->
        <div class="content">
            <?= nl2br(htmlspecialchars($post['content'])); ?>
        </div>


        <!-- share links -->
        <div class="social">
            <i class="fas fa-share-alt" aria-hidden="true"></i> <?= __('detail_share') ?>: <a href="#">Facebook</a> /<a href="#">Twitter</a> /<a href="#">WhatsApp</a>
        </div>

        <!-- map design -->
        <div class="map_box">
            <iframe
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d10754.139064625955!2d-5.8367744!3d35.7594653!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xd0b8165f4a90f3d%3A0x127b3b98cb1b5b62!2sTangier%2C%20Morocco!5e0!3m2!1sen!2sma!4v1710000000000!5m2!1sen!2sma"
                width="100%"
                height="400"
                style="border:0;"
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
    <script src="../assets/js/detail.js"></script>
</body>

</html>
