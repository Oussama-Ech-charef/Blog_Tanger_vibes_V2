<?php
$page_title = 'New Post';
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/../includes/post_helpers.php';

$errors = [];
$categories = $conn->query("SELECT * FROM categories ORDER BY cat_name ASC")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_post'])) {
    $title = trim($_POST['title'] ?? '');
    $cat_id = (int)($_POST['category'] ?? 0);
    $content = trim($_POST['content'] ?? '');
    $action = in_array($_POST['action'] ?? '', [STATUS_DRAFT, STATUS_PENDING]) ? $_POST['action'] : STATUS_DRAFT;

    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) $errors[] = 'Invalid request.';
    $errors = array_merge($errors, validate_post_input($title, $cat_id, $content));

    $img = process_post_image();
    $errors = array_merge($errors, $img['errors']);

    if (empty($errors)) {
        try {
            $nid = insert_post($conn, $cat_id, $uid, $title, $img['path'], $content, $action, false);
            $msg = $action === STATUS_DRAFT ? 'saved' : 'submitted';
            header('Location: myposts.php?msg=' . $msg);
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
        <h2><i class="fa-solid fa-pen-to-square" style="color:var(--db-primary);margin-right:8px;" aria-hidden="true"></i>Create New Post</h2>
        <a href="myposts.php" class="btn btn_secondary btn_sm"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back</a>
    </div>
    <div class="card_body">
        <form method="POST" action="add_post.php" enctype="multipart/form-data" style="max-width:800px;">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="add_post" value="1">

            <div class="form_group">
                <label for="title">Post Title</label>
                <input type="text" id="title" name="title" value="<?= htmlspecialchars($title ?? '') ?>" required maxlength="255" placeholder="Enter post title">
            </div>

            <div class="form_row">
                <div class="form_group">
                    <label for="category">Category</label>
                    <select id="category" name="category" required>
                        <option value="">Select...</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= $c['id_category'] ?>" <?= ($cat_id ?? 0) == (int)$c['id_category'] ? 'selected' : '' ?>><?= htmlspecialchars($c['cat_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form_group">
                <label for="image">Featured Image</label>
                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp">
                <span class="form_hint">Optional. JPEG, PNG or WebP.</span>
            </div>

            <div class="form_group">
                <label for="content">Content</label>
                <textarea id="content" name="content" required placeholder="Write your post..." style="min-height:350px;"><?= htmlspecialchars($content ?? '') ?></textarea>
            </div>

            <div class="form_actions">
                <button type="submit" name="action" value="pending" class="btn btn_primary"><i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Submit for Review</button>
                <button type="submit" name="action" value="draft" class="btn btn_secondary"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Save as Draft</button>
                <a href="myposts.php" class="btn btn_secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
