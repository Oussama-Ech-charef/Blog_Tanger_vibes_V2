<?php
$page_title = 'Edit Post';
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/../includes/post_helpers.php';

$uid = (int)$_SESSION['id_user'];
$errors = [];
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($post_id <= 0) { header('Location: posts.php'); exit(); }

$s = $conn->prepare("SELECT * FROM posts WHERE id_post=:id");
$s->execute([':id' => $post_id]);
$post = $s->fetch(PDO::FETCH_ASSOC);
if (!$post) { header('Location: posts.php'); exit(); }

$categories = $conn->query("SELECT * FROM categories ORDER BY cat_name ASC")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_post'])) {
    $title = trim($_POST['title'] ?? '');
    $cat_id = (int)($_POST['category'] ?? 0);
    $content = trim($_POST['content'] ?? '');

    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) $errors[] = 'Invalid request.';
    $errors = array_merge($errors, validate_post_input($title, $cat_id, $content));

    $image_path = $post['image'];

    $img = process_post_image($post['image']);
    $errors = array_merge($errors, $img['errors']);
    if ($img['path'] !== null) {
        $image_path = $img['path'];
    }

    $image_path = handle_image_removal($image_path, isset($_POST['remove_image']) && $_POST['remove_image'] === '1');

    if (empty($errors)) {
        try {
            $new_status = in_array($_POST['status'] ?? '', [STATUS_DRAFT, STATUS_PUBLISHED]) ? $_POST['status'] : $post['status'];

            update_post($conn, $post_id, $cat_id, $title, $image_path, $content, $new_status, false, $uid, true);
            log_post_activity($conn, 'post_updated', "Updated post: $title", $uid, $post_id);

            header('Location: posts.php?msg=updated');
            exit;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $errors[] = 'Database error.';
        }
    }
}

require_once __DIR__ . '/inc/header.php';
?>

<?php if (!empty($errors)): render_notification(implode(' | ', array_map('htmlspecialchars', $errors)), 'error'); endif; ?>

<div class="card">
    <div class="card_header">
        <h2><i class="fa-solid fa-pen" style="color:var(--db-primary);margin-right:8px;" aria-hidden="true"></i>Edit Post</h2>
        <div style="display:flex;gap:8px;align-items:center;">
            <span class="status_badge <?= $post['status'] ?>"><?= ucfirst(htmlspecialchars($post['status'])) ?></span>
            <a href="../pages/detail.php?id=<?= $post_id ?>" class="btn btn_secondary btn_sm" target="_blank" rel="noopener"><i class="fa-solid fa-eye"></i> Preview</a>
            <a href="posts.php" class="btn btn_secondary btn_sm"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back</a>
        </div>
    </div>
    <div class="card_body">

        <form method="POST" action="edit_post.php?id=<?= $post_id ?>" enctype="multipart/form-data" style="max-width:800px;">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="edit_post" value="1">

            <div class="form_group"><label for="title">Title</label><input type="text" id="title" name="title" value="<?= htmlspecialchars($post['title']) ?>" required maxlength="255"></div>

            <div class="form_row">
                <div class="form_group">
                    <label for="category">Category</label>
                    <select id="category" name="category" required>
                        <option value="">Select...</option>
                        <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['id_category'] ?>" <?= (int)$post['id_category'] === (int)$c['id_category'] ? 'selected' : '' ?>><?= htmlspecialchars($c['cat_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form_group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="draft" <?= $post['status'] === STATUS_DRAFT ? 'selected' : '' ?>>Draft</option>
                        <option value="published" <?= $post['status'] === STATUS_PUBLISHED ? 'selected' : '' ?>>Published</option>
                    </select>
                </div>
            </div>

            <div class="form_group">
                <label>Featured Image</label>
                <?php if (!empty($post['image'])): ?>
                <div style="display:flex;align-items:center;gap:16px;margin-bottom:12px;padding:12px;background:#F8FAFC;border-radius:8px;border:1px solid var(--db-card-border);">
                    <img src="../<?= htmlspecialchars($post['image']) ?>" alt="" style="height:60px;border-radius:4px;object-fit:cover;">
                    <span style="font-size:13px;color:var(--db-text-secondary);"><?= htmlspecialchars(basename($post['image'])) ?></span>
                    <label style="margin-left:auto;font-size:13px;cursor:pointer;"><input type="checkbox" name="remove_image" value="1"> Remove</label>
                </div>
                <?php endif; ?>
                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp">
                <span class="form_hint">Leave empty to keep current.</span>
            </div>

            <div class="form_group"><label for="content">Content</label><textarea id="content" name="content" required style="min-height:350px;"><?= htmlspecialchars($post['content']) ?></textarea></div>

            <div class="form_actions">
                <button type="submit" class="btn btn_primary"><i class="fa-solid fa-save" aria-hidden="true"></i> Save Changes</button>
                <a href="posts.php" class="btn btn_secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
