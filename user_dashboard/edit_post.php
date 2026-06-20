<?php
$page_title = 'Edit Post';
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/../includes/post_helpers.php';

$errors = [];
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($post_id <= 0) { header('Location: myposts.php'); exit(); }

$s = $conn->prepare("SELECT * FROM posts WHERE id_post=:id AND id_user=:uid");
$s->execute([':id' => $post_id, ':uid' => $uid]);
$post = $s->fetch(PDO::FETCH_ASSOC);
if (!$post) { header('Location: myposts.php'); exit(); }

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
            $resubmit = isset($_POST['resubmit']);
            $clear_reason = false;
            $activity_type = 'post_updated';
            $action_desc = 'Post updated';
            $redirect_msg = 'updated';
            $new_status = $post['status'];

            if ($post['status'] === STATUS_PUBLISHED) {
                $new_status = STATUS_PENDING;
                $action_desc = 'Post updated and sent for re-review';
                $activity_type = 'post_submitted';
                $redirect_msg = 'resubmitted';
            } elseif ($resubmit) {
                $new_status = STATUS_PENDING;
                $action_desc = $post['status'] === STATUS_REJECTED ? 'Post resubmitted for review' : 'Post submitted for review';
                $activity_type = 'post_submitted';
                $redirect_msg = 'resubmitted';
                if ($post['status'] === STATUS_REJECTED) {
                    $clear_reason = true;
                }
            } elseif ($post['status'] === STATUS_DRAFT) {
                $activity_type = 'draft_saved';
                $action_desc = 'Draft saved';
            }

            update_post($conn, $post_id, $cat_id, $title, $image_path, $content, $new_status, $clear_reason, $uid, false);
            log_post_activity($conn, $activity_type, "$action_desc: $title", $uid, $post_id);

            header('Location: myposts.php?msg=' . $redirect_msg);
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
            <a href="myposts.php" class="btn btn_secondary btn_sm"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back</a>
        </div>
    </div>
    <div class="card_body">

        <?php if ($post['status'] === STATUS_REJECTED && !empty($post['rejection_reason'])): ?>
        <div style="background:#FEF2F2;border:1px solid #FECACA;border-radius:8px;padding:14px 18px;margin-bottom:24px;color:#991B1B;font-size:14px;">
            <strong style="display:block;font-size:13px;color:#EF4444;margin-bottom:4px;"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i> Rejection Reason</strong>
            <?= htmlspecialchars($post['rejection_reason']) ?>
            <p style="margin:8px 0 0;font-size:13px;color:#92400E;">Fix the issues above and check "Resubmit for Review" below to send it back for approval.</p>
        </div>
        <?php elseif ($post['status'] === STATUS_PUBLISHED): ?>
        <div style="background:#FEF3C7;border:1px solid #FDE68A;border-radius:8px;padding:14px 18px;margin-bottom:24px;color:#92400E;font-size:14px;">
            <strong style="display:block;font-size:13px;color:#D97706;margin-bottom:4px;"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i> Editing Published Post</strong>
            Saving changes will return this post to <strong>Pending</strong> status for admin re-review. It will not be publicly visible until approved again.
        </div>
        <?php elseif ($post['status'] === STATUS_PENDING): ?>
        <div style="background:#DBEAFE;border:1px solid #BFDBFE;border-radius:8px;padding:14px 18px;margin-bottom:24px;color:#1E40AF;font-size:14px;">
            <strong style="display:block;font-size:13px;color:#2563EB;margin-bottom:4px;"><i class="fa-solid fa-clock" aria-hidden="true"></i> Pending Review</strong>
            This post is awaiting admin review. Saving changes will keep it in the review queue.
        </div>
        <?php endif; ?>

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

            <?php if (in_array($post['status'], [STATUS_DRAFT, STATUS_REJECTED])): ?>
            <div style="margin-top:24px;padding-top:24px;border-top:1px solid var(--db-card-border);">
                <label style="display:flex;align-items:center;gap:10px;font-weight:600;font-size:14px;color:var(--db-text-primary);cursor:pointer;">
                    <input type="checkbox" name="resubmit" value="1" style="width:18px;height:18px;">
                    <i class="fa-solid fa-paper-plane" style="color:var(--db-primary);" aria-hidden="true"></i> <?= $post['status'] === STATUS_REJECTED ? 'Resubmit for Review' : 'Submit for Review' ?>
                    <span style="font-weight:400;font-size:13px;color:var(--db-text-muted);"><?= $post['status'] === STATUS_REJECTED ? '(clears rejection reason, sends back to pending)' : '(submits to admin for approval)' ?></span>
                </label>
            </div>
            <?php endif; ?>

            <div class="form_actions">
                <button type="submit" class="btn btn_primary"><i class="fa-solid fa-save" aria-hidden="true"></i> Save Changes</button>
                <?php if (in_array($post['status'], [STATUS_DRAFT, STATUS_REJECTED])): ?>
                <button type="submit" name="resubmit" value="1" class="btn btn_secondary" style="background:#D1FAE5;color:#065F46;"><i class="fa-solid fa-paper-plane" aria-hidden="true"></i> <?= $post['status'] === STATUS_REJECTED ? 'Save &amp; Resubmit' : 'Save &amp; Submit' ?></button>
                <?php endif; ?>
                <a href="myposts.php" class="btn btn_secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
