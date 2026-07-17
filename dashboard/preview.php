<?php
// Post preview page
require_once __DIR__ . '/init.php';
$page_title = __('post_preview_title');

$uid = current_user_id();
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($post_id <= 0) { header('Location: posts.php'); exit(); }

$ownership_sql = $is_admin ? "p.id_post=:id" : "p.id_post=:id AND p.id_user=:uid";
$s = $conn->prepare("SELECT p.*, c.cat_name, u.user_name FROM posts p INNER JOIN categories c ON p.id_category = c.id_category INNER JOIN users u ON p.id_user = u.id_user WHERE $ownership_sql");
$s->bindValue(':id', $post_id, PDO::PARAM_INT);
if (!$is_admin) $s->bindValue(':uid', $uid, PDO::PARAM_INT);
$s->execute();
$post = $s->fetch(PDO::FETCH_ASSOC);
if (!$post) { header('Location: posts.php'); exit(); }

require_once __DIR__ . '/inc/header.php';
?>

<div class="card">
    <div class="card_header">
        <h2><?= htmlspecialchars($post['title']) ?></h2>
        <div class="flex_row" style="gap:8px;">
            <a href="edit_post.php?id=<?= $post_id ?>" class="btn btn_secondary btn_sm"><i class="fa-solid fa-pen" aria-hidden="true"></i> <?= __('posts_edit_post') ?></a>
            <a href="posts.php" class="btn btn_secondary btn_sm"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> <?= __('add_post_back') ?></a>
        </div>
    </div>
    <div class="card_body">
        <?php if (!empty($post['image'])): ?>
        <div style="margin-bottom:24px;">
            <img src="../<?= htmlspecialchars($post['image']) ?>" alt="" style="max-width:100%;border-radius:8px;max-height:400px;object-fit:cover;width:100%;">
        </div>
        <?php endif; ?>
        <div class="quickview_meta" style="margin-bottom:20px;">
            <span class="quickview_meta_item"><i class="fa-solid fa-tag"></i> <?= htmlspecialchars($post['cat_name']) ?></span>
            <span class="quickview_meta_item"><i class="fa-solid fa-circle"></i> <span class="status_badge <?= $post['status'] ?>"><?= translate_status($post['status']) ?></span></span>
            <span class="quickview_meta_item"><i class="fa-solid fa-user"></i> <?= htmlspecialchars($post['user_name']) ?></span>
            <span class="quickview_meta_item"><i class="fa-solid fa-calendar"></i> <?= date('M j, Y', strtotime($post['created_at'])) ?></span>
        </div>
        <?php if (!empty($post['rejection_reason'])): ?>
        <div style="background:#FEF2F2;border:1px solid #FECACA;border-radius:8px;padding:12px 16px;font-size:13px;color:#991B1B;margin-bottom:20px;">
            <strong style="color:#EF4444;"><?= __('posts_quickview_rejection_reason') ?></strong><br><?= htmlspecialchars($post['rejection_reason']) ?>
        </div>
        <?php endif; ?>
        <div class="post_preview_content"><?= render_post_content($post['content']) ?></div>
    </div>
</div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
