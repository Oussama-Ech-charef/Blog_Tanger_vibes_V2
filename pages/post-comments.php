<?php

require_once '../config/connection.php';
require_once '../config/constants.php';
require_once '../includes/security.php';
require_once '../includes/lang.php';
require_once '../includes/helpers.php';

send_security_headers();

if (page_cache_try()) exit;

// Validate post id
if (!isset($_GET['id']) || !ctype_digit((string)$_GET['id'])) {
    header('Location: index.php');
    exit;
}

$post_id = (int)$_GET['id'];

// Load the published post
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

// Count total approved comments
$count_stmt = $conn->prepare("
    select count(*) from comments where id_post = :id_post and status = 'approved'
");
$count_stmt->execute([':id_post' => $post_id]);
$total_comments = (int)$count_stmt->fetchColumn();

// Fetch the first 10 approved comments
$per_page = 10;
$cmt_stmt = $conn->prepare("
    select id_comment, author_name, comment_text, created_at
    from comments
    where id_post = :id_post and status = 'approved'
    order by created_at desc
    limit :lim
");
$cmt_stmt->bindValue(':id_post', $post_id, PDO::PARAM_INT);
$cmt_stmt->bindValue(':lim', $per_page, PDO::PARAM_INT);
$cmt_stmt->execute();
$comments = $cmt_stmt->fetchAll(PDO::FETCH_ASSOC);

$initial_count = count($comments);
$has_more = $initial_count < $total_comments;

?>

<!DOCTYPE html>
<html lang="<?= get_lang_code() ?>" dir="<?= get_lang_dir() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sprintf(__('comment_page_title'), htmlspecialchars($post['title'])) ?> - Tangier Vibes</title>
    <meta name="description" content="<?= htmlspecialchars(substr(strip_tags($post['content']), 0, 150)) ?>">
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link rel="apple-touch-icon" href="../assets/images/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../<?= asset_version('assets/css/public.min.css') ?>">
</head>
<body>

<?php require '../includes/header.php' ?>

<div class="post_comments_page" id="main_content">

    <!-- back link -->
    <a href="detail.php?id=<?= (int)$post['id_post'] ?>" class="back_link motion-reveal-left">
        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
        <?= __('comment_page_back') ?>
    </a>

    <!-- post overview -->
    <div class="post_overview motion-reveal-left">

        <div class="post_overview_category">
            <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
            <?= htmlspecialchars($post['cat_name']) ?>
        </div>

        <h1 class="post_overview_title">
            <a href="detail.php?id=<?= (int)$post['id_post'] ?>"><?= htmlspecialchars($post['title']) ?></a>
        </h1>

        <div class="post_overview_meta">
            <span>
                <i class="fa-regular fa-circle-user" aria-hidden="true"></i>
                <?= __('comment_page_meta_author') ?>
                <?= htmlspecialchars($post['user_name'] ?? __('admin_label')) ?>
            </span>
            <span>
                <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                <?= __('comment_page_meta_date') ?>
                <?= date(__('date_format_detail'), strtotime($post['created_at'])) ?>
            </span>
            <span>
                <i class="fa-regular fa-comment-dots" aria-hidden="true"></i>
                <?= $total_comments ?> <?= __('comment_page_meta_count') ?>
            </span>
        </div>

    </div>

    <!-- comments section -->
    <div class="post_comments_section motion-reveal">

        <div class="post_comments_header">
            <h2 class="post_comments_heading">
                <i class="fa-regular fa-comments" aria-hidden="true"></i>
                <?= sprintf(__('comment_page_heading'), '') ?>
                <span><?= htmlspecialchars($post['title']) ?></span>
            </h2>
            <?php if ($total_comments > 0): ?>
                <span class="post_comments_count"><?= $total_comments ?></span>
            <?php endif; ?>
        </div>

        <div class="post_comments_divider"></div>

        <?php if (!empty($comments)): ?>

            <div class="post_comments_list" id="post-comments-list">
                <?php foreach ($comments as $comment): ?>
                    <div class="post_comment_item">
                        <div class="post_comment_header">
                            <span class="post_comment_author">
                                <span class="comment_avatar"><?= htmlspecialchars(avatar_initials($comment['author_name'])) ?></span>
                                <span class="comment_name"><?= htmlspecialchars($comment['author_name']) ?></span>
                            </span>
                            <span class="comment_date">
                                <i class="fa-regular fa-clock" aria-hidden="true"></i>
                                <?= date(__('date_format_detail'), strtotime($comment['created_at'])) ?>
                            </span>
                        </div>
                        <div class="post_comment_text"><?= htmlspecialchars($comment['comment_text']) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($has_more): ?>
                <div class="load_more_wrapper" id="load-more-wrapper">
                    <button type="button" class="load_more_btn" id="load-more-btn"
                            data-post-id="<?= (int)$post_id ?>"
                            data-page="1"
                            data-total="<?= (int)$total_comments ?>"
                            data-loaded="<?= (int)$initial_count ?>">
                        <i class="fa-regular fa-comment-dots" aria-hidden="true"></i>
                        <span class="load_more_text"><?= __('comment_load_more') ?></span>
                        <span class="load_more_loading" style="display:none">
                            <i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i>
                            <?= __('comment_load_more_loading') ?>
                        </span>
                    </button>
                    <p class="load_more_status" id="load-more-status" style="display:none"></p>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="post_comments_empty">
                <i class="fa-regular fa-comments" aria-hidden="true"></i>
                <p><?= __('comment_page_empty') ?></p>
                <a href="detail.php?id=<?= (int)$post['id_post'] ?>" class="btn_primary post_comments_empty_btn">
                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                    <?= __('comment_page_back') ?>
                </a>
            </div>
        <?php endif; ?>

    </div>

</div>

<?php require '../includes/footer.php'; ?>
<script src="../<?= asset_version('assets/js/public.min.js') ?>"></script>
<script>
(function() {
    var btn = document.getElementById('load-more-btn');
    if (!btn) return;

    var list = document.getElementById('post-comments-list');
    var wrapper = document.getElementById('load-more-wrapper');
    var status = document.getElementById('load-more-status');
    var textEl = btn.querySelector('.load_more_text');
    var loadingEl = btn.querySelector('.load_more_loading');

    var postId = parseInt(btn.getAttribute('data-post-id'), 10);
    var currentPage = parseInt(btn.getAttribute('data-page'), 10);
    var totalComments = parseInt(btn.getAttribute('data-total'), 10);
    var loadedCount = parseInt(btn.getAttribute('data-loaded'), 10);

    btn.addEventListener('click', function() {
        var nextPage = currentPage + 1;

        // Show loading state
        textEl.style.display = 'none';
        loadingEl.style.display = 'inline';
        btn.disabled = true;
        status.style.display = 'none';

        var xhr = new XMLHttpRequest();
        xhr.open('GET', '../includes/ajax_comments.php?id=' + postId + '&p=' + nextPage, true);

        xhr.onload = function() {
            // Reset loading state
            textEl.style.display = 'inline';
            loadingEl.style.display = 'none';
            btn.disabled = false;

            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    var data = JSON.parse(xhr.responseText);

                    if (data.error) {
                        status.textContent = '<?= addslashes(__('comment_load_more_error')) ?>';
                        status.style.display = 'block';
                        return;
                    }

                    // Append the new comments
                    if (data.html) {
                        list.insertAdjacentHTML('beforeend', data.html);
                    }

                    currentPage = data.page;
                    loadedCount = data.loaded;

                    // Update button text with remaining count
                    var remaining = totalComments - loadedCount;
                    if (data.has_more) {
                        textEl.textContent = '<?= addslashes(__('comment_load_more')) ?>';
                    } else {
                        // No more comments
                        btn.style.display = 'none';
                        status.textContent = '<?= addslashes(__('comment_load_more_none')) ?>';
                        status.style.display = 'block';
                    }
                } catch (e) {
                    status.textContent = '<?= addslashes(__('comment_load_more_error')) ?>';
                    status.style.display = 'block';
                }
            } else {
                status.textContent = '<?= addslashes(__('comment_load_more_error')) ?>';
                status.style.display = 'block';
            }
        };

        xhr.onerror = function() {
            textEl.style.display = 'inline';
            loadingEl.style.display = 'none';
            btn.disabled = false;
            status.textContent = '<?= addslashes(__('comment_load_more_error')) ?>';
            status.style.display = 'block';
        };

        xhr.send();
    });
})();
</script>
</body>
</html>
