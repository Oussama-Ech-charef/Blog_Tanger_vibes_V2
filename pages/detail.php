<?php

require_once '../config/connection.php';
require_once '../includes/security.php';
require_once '../includes/lang.php';
require_once '../includes/helpers.php';
 
 send_security_headers();

// Page cache for anonymous users
if (page_cache_try()) exit;

// Check if post ID is provided and numeric
if (!isset($_GET['id']) || !ctype_digit((string)$_GET['id'])) {
    header('Location: index.php');
    exit;
}

$post_id = (int)$_GET['id'];


// Load post from database
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

if (isset($_SESSION['flash_error'])) {
    $comment_error = $_SESSION['flash_error'];
    unset($_SESSION['flash_error']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Guests must not be able to insert — reject early
    if (!isset($_SESSION['id_user'])) {
        $_SESSION['flash_error'] = __('detail_comment_error_login_required');
        header('Location: detail.php?id=' . $post_id . '#comments');
        exit;
    }

    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!validate_csrf_token($csrf_token)) {
        $_SESSION['flash_error'] = __('detail_comment_error_generic');
        header('Location: detail.php?id=' . $post_id . '#comments');
        exit;
    }

    // Rate limiting: max 5 comments per user per hour
    if (isset($_SESSION['id_user'])) {
        $cmt_check = $conn->prepare("SELECT COUNT(*) FROM comments WHERE id_user=:uid AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)");
        $cmt_check->execute([':uid' => $_SESSION['id_user']]);
        if ((int)$cmt_check->fetchColumn() >= 5) {
            $_SESSION['flash_error'] = __('detail_comment_error_rate_limit');
            header('Location: detail.php?id=' . $post_id . '#comments');
            exit;
        }
    }

    $author_name = $_SESSION['user_name'];
    $comment_text = trim($_POST['message'] ?? '');

    if (empty($comment_text)) {
        $_SESSION['flash_error'] = __('detail_comment_error_required');
        header('Location: detail.php?id=' . $post_id . '#comments');
        exit;
    }

    if (strlen($comment_text) > 1000) {
        $_SESSION['flash_error'] = __('detail_comment_error_text_length');
        header('Location: detail.php?id=' . $post_id . '#comments');
        exit;
    }

    if (empty($comment_error)) {
        try {
            $comment_user_id = (int)$_SESSION['id_user'];
            $comment_status = 'pending';
            if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                $comment_status = 'approved';
            }
            $stmt = $conn->prepare("
                insert into comments (id_post, id_user, author_name, comment_text, status)
                values (:id_post, :id_user, :author_name, :comment_text, :status)
            ");
            $stmt->execute([
                ':id_post' => $post_id,
                ':id_user' => $comment_user_id,
                ':author_name' => $author_name,
                ':comment_text' => $comment_text,
                ':status' => $comment_status
            ]);

            // log activity
            try {
                $log = $conn->prepare("insert into activity_log (action_type, description, user_id, entity_type, entity_id) values ('comment_added', :desc, null, 'comment', :eid)");
                $log->execute([':desc' => "$author_name commented on: " . $post['title'], ':eid' => $conn->lastInsertId()]);
            } catch (PDOException $e) {
                error_log("Activity log error: " . $e->getMessage());
            }

            if (class_exists('PageCache')) PageCache::flush();
            $_SESSION['comment_added'] = true;
            header("Location: detail.php?id=" . $post_id . "#comments");
            exit();
        } catch (PDOException $e) {
            error_log("Comment insert error: " . $e->getMessage());
            $comment_error = __('detail_comment_error_generic');
        }
    }
}

// Get approved comments for this post
$comment_stmt = $conn->prepare("
    select id_comment, author_name, comment_text, created_at
    from comments
    where id_post = :id_post and status = 'approved'
    order by created_at desc
");
$comment_stmt->execute([':id_post' => $post_id]);
$comments = $comment_stmt->fetchAll(PDO::FETCH_ASSOC);
$comment_count = count($comments);
$recent_comments = array_slice($comments, 0, 3);
$has_more_comments = $comment_count > 3;
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
    <link rel="stylesheet" href="../<?= asset_version('assets/css/public.min.css') ?>">
</head>
<body>

<?php require '../includes/header.php' ?>

<div class="detail_container" id="main_content">

    <!-- category -->
    <div class="detail_category motion-reveal-left">
        <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
        <?= __('detail_tanger_label') ?>
        <span class="cat_name"><?= htmlspecialchars($post['cat_name']); ?></span>
    </div>

    <!-- title -->
    <h1 class="motion-reveal"><?= htmlspecialchars($post['title']); ?></h1>

    <!-- metadata -->
    <div class="detail_meta motion-reveal">
        <span>
            <i class="fa-regular fa-calendar" aria-hidden="true"></i>
            <?= date(__('date_format_detail'), strtotime($post['created_at'])); ?>
        </span>
        <span>
            <i class="fa-regular fa-circle-user" aria-hidden="true"></i>
            <span class="detail_author_label"><?= __('detail_by') ?></span>
            <span class="detail_author"><?= htmlspecialchars($post['user_name'] ?? __('admin_label')); ?></span>
        </span>
    </div>

    <!-- hero image -->
    <?php if (!empty($post['image'])): ?>
        <div class="detail_hero motion-reveal">
            <?= optimized_image('../' . htmlspecialchars($post['image']), htmlspecialchars($post['title']), '', ['width' => 1200, 'height' => 675]) ?>
        </div>
    <?php endif; ?>

    <!-- content -->
    <div class="content motion-reveal">
        <?= render_post_content($post['content']); ?>
    </div>

    <!-- share -->
    <?php
    $share_url = urlencode('https://tanger.lovestoblog.com/detail.php?id=' . $post['id_post']);
    $share_title = urlencode(htmlspecialchars_decode($post['title']) . ' - Tangier Vibes');
    ?>
    <div class="detail_share motion-reveal">
        <span class="detail_share_label">
            <i class="fa-solid fa-share-nodes" aria-hidden="true"></i>
            <?= __('detail_share') ?>
        </span>
        <div class="detail_share_actions">
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $share_url ?>"
               target="_blank" rel="noopener noreferrer"
               class="share_btn share_btn--facebook"
               aria-label="<?= __('share_facebook') ?>">
                <i class="fa-brands fa-facebook-f" aria-hidden="true"></i>
                <?= __('share_facebook') ?>
            </a>
            <a href="https://twitter.com/intent/tweet?text=<?= $share_title ?>&url=<?= $share_url ?>"
               target="_blank" rel="noopener noreferrer"
               class="share_btn share_btn--twitter"
               aria-label="<?= __('share_twitter') ?>">
                <i class="fa-brands fa-x-twitter" aria-hidden="true"></i>
                <?= __('share_twitter') ?>
            </a>
            <a href="https://wa.me/?text=<?= $share_title ?>%20<?= $share_url ?>"
               target="_blank" rel="noopener noreferrer"
               class="share_btn share_btn--whatsapp"
               aria-label="<?= __('share_whatsapp') ?>">
                <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
                <?= __('share_whatsapp') ?>
            </a>
        </div>
    </div>

    <!-- map -->
    <div class="detail_map motion-reveal">
        <div class="detail_map_title">
            <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
            <?= __('detail_tanger_label') ?> <span><?= __('detail_share') ?></span>
        </div>
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
    </div>

    <!-- comments -->
    <div id="comments" class="motion-reveal">

        <!-- header -->
        <div class="comments_header">
            <i class="fa-regular fa-comment-dots" aria-hidden="true"></i>
            <h2 class="comments_title"><?= __('detail_comments_title') ?></h2>
            <span class="comments_count" data-counter><?= $comment_count; ?></span>
        </div>
        <div class="comments_divider"></div>

        <!-- success inline -->
        <?php if (!empty($comment_success)): ?>
            <div class="comment_message success">
                <i class="fa-solid fa-check-circle" aria-hidden="true"></i>
                <?= htmlspecialchars($comment_success) ?>
            </div>
        <?php endif; ?>

        <!-- error inline -->
        <?php if (!empty($comment_error)): ?>
            <div class="comment_message error">
                <i class="fa-solid fa-exclamation-circle" aria-hidden="true"></i>
                <?= htmlspecialchars($comment_error) ?>
            </div>
        <?php endif; ?>

        <!-- comments list (latest 3 only) -->
        <div class="comments_list" data-comments-list>
            <?php if (!empty($recent_comments)): ?>
                <?php foreach ($recent_comments as $comment): ?>
                    <div class="comment_item">
                        <div class="comment_header">
                            <span class="comment_author">
                                <span class="comment_avatar"><?= htmlspecialchars(avatar_initials($comment['author_name'])) ?></span>
                                <span class="comment_name"><?= htmlspecialchars($comment['author_name']) ?></span>
                            </span>
                            <span class="comment_date">
                                <i class="fa-regular fa-clock" aria-hidden="true"></i>
                                <?= date(__('date_format_detail'), strtotime($comment['created_at'])) ?>
                            </span>
                        </div>
                        <div class="comment_text"><?= htmlspecialchars($comment['comment_text']) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="comments_empty">
                    <i class="fa-regular fa-comments" aria-hidden="true"></i>
                    <p><?= __('detail_comments_empty') ?></p>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($has_more_comments): ?>
            <a href="post-comments.php?id=<?= (int)$post_id ?>" class="comments_view_all_link">
                <i class="fa-regular fa-comment-dots" aria-hidden="true"></i>
                <?= sprintf(__('detail_comments_view_all_count'), $comment_count) ?>
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </a>
        <?php elseif ($comment_count > 0): ?>
            <a href="post-comments.php?id=<?= (int)$post_id ?>" class="comments_view_all_link">
                <i class="fa-regular fa-comment-dots" aria-hidden="true"></i>
                <?= __('detail_comments_view_all') ?>
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </a>
        <?php endif; ?>

        <!-- comment form -->
        <div class="comment_form_wrapper">
            <h3 class="comment_form_title">
                <i class="fa-regular fa-pen-to-square" aria-hidden="true"></i>
                <?= __('detail_comment_leave') ?>
            </h3>
            <div class="comment_form">
                <form action="" method="POST" data-comment-form<?= !isset($_SESSION['id_user']) ? ' data-guest-comment' : '' ?>>
                    <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
                    <textarea name="message" placeholder="<?= __('detail_comment_message_placeholder') ?>" rows="3" data-comment-message></textarea>
                    <div class="comment_form_footer">
                        <button type="submit" class="btn_primary">
                            <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                            <?= __('detail_comment_btn') ?>
                        </button>
                        <?php if (isset($_SESSION['id_user'])): ?>
                            <span class="comment_user_note">
                                <i class="fa-regular fa-circle-user" aria-hidden="true"></i>
                                <?= sprintf(__('detail_comment_signed_as'), htmlspecialchars($_SESSION['user_name'])) ?>
                            </span>
                        <?php else: ?>
                            <span class="comment_user_note">
                                <i class="fa-regular fa-lock" aria-hidden="true"></i>
                                <?= __('detail_comment_login_note') ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

    </div>

</div>

<?php require '../includes/footer.php'; ?>
<script src="../<?= asset_version('assets/js/public.min.js') ?>"></script>
<script>
(function() {
    var storageKey = 'pending_comment_<?= (int)$post_id ?>';
    var textarea = document.querySelector('[data-comment-message]');
    var form = document.querySelector('[data-comment-form]');
    var commentSaved = <?= !empty($comment_success) ? 'true' : 'false' ?>;

    if (commentSaved) {
        sessionStorage.removeItem(storageKey);
    } else if (textarea && !textarea.value) {
        textarea.value = sessionStorage.getItem(storageKey) || '';
    }

    if (!form || !textarea) return;

    form.addEventListener('submit', function(e) {
        if (!form.hasAttribute('data-guest-comment')) {
            sessionStorage.setItem(storageKey, textarea.value);
            return;
        }

        e.preventDefault();
        sessionStorage.setItem(storageKey, textarea.value);
        var toggle = document.querySelector('[data-auth-toggle]');
        if (toggle) toggle.click();
    });

})();
</script>
</body>

</html>
