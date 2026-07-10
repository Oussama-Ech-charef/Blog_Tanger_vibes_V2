<?php
// Edit existing post
require_once __DIR__ . '/init.php';
$page_title = __('edit_post_title');
require_once __DIR__ . '/../includes/post_helpers.php';

$uid = (int)$_SESSION['id_user'];
$errors = [];
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($post_id <= 0) { header('Location: posts.php'); exit(); }

// Everyone can only edit their own posts
$s = $conn->prepare("SELECT * FROM posts WHERE id_post=:id AND id_user=:uid");
$s->bindValue(':id', $post_id, PDO::PARAM_INT);
$s->bindValue(':uid', $uid, PDO::PARAM_INT);
$s->execute();
$post = $s->fetch(PDO::FETCH_ASSOC);
if (!$post) { header('Location: posts.php'); exit(); }

$categories = $conn->query("SELECT * FROM categories ORDER BY cat_name ASC")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_post'])) {
    $title = trim($_POST['title'] ?? '');
    $cat_id = (int)($_POST['category'] ?? 0);
    $content = trim($_POST['content'] ?? '');

    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) $errors[] = __('post_error_invalid');
    $errors = array_merge($errors, validate_post_input($title, $cat_id, $content));

    $old_image = $post['image'];
    $image_path = $old_image;
    $new_upload_path = null;

    $img = process_post_image();
    $errors = array_merge($errors, $img['errors']);
    if ($img['path'] !== null) {
        $new_upload_path = $img['path'];
        $image_path = $img['path'];
    }

    $remove_requested = isset($_POST['remove_image']) && $_POST['remove_image'] === '1';
    $image_path = handle_image_removal($image_path, $remove_requested);

    if (empty($errors)) {
        try {
            if ($is_admin) {
                $new_status = in_array($_POST['status'] ?? '', [STATUS_DRAFT, STATUS_PUBLISHED]) ? $_POST['status'] : $post['status'];
            } else {
                // Users cannot set published; draft stays draft, anything else becomes pending
                $submitted_status = $_POST['status'] ?? '';
                if ($submitted_status === STATUS_DRAFT) {
                    $new_status = STATUS_DRAFT;
                } else {
                    $new_status = STATUS_PENDING;
                }
                // Clear rejection reason when user resubmits
                $clear_reason = true;
            }

            update_post($conn, $post_id, $cat_id, $title, $image_path, $content, $new_status, $clear_reason ?? false, $uid, $is_admin);
            log_post_activity($conn, 'post_updated', "Updated post: $title", $uid, $post_id);

                // Clean up old image if replaced or removed
            if ($old_image && ($new_upload_path !== null || $remove_requested)) {
                safe_delete_uploaded_image($old_image);
            }

            header('Location: posts.php?msg=updated');
            exit;
        } catch (PDOException $e) {
            if ($new_upload_path !== null) {
                safe_delete_uploaded_image($new_upload_path);
            }
            error_log($e->getMessage());
            $errors[] = __('post_error_db');
        }
    }
}

require_once __DIR__ . '/inc/header.php';
?>

<?php if (!empty($errors)): render_notification(implode(' | ', array_map('htmlspecialchars', $errors)), 'error'); endif; ?>

<?php $has_image = !empty($post['image']); ?>

<div class="add_post_page">
    <div class="add_post_layout">

        <form method="POST" action="edit_post.php?id=<?= $post_id ?>" enctype="multipart/form-data" id="editPostForm">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="edit_post" value="1">
            <input type="hidden" name="remove_image" id="removeImageFlag" value="0">

            <!-- Left Column — Writing Area -->
            <div class="add_post_main">

                <div class="add_post_header">
                    <div>
                        <h1><?= __('edit_post_heading') ?></h1>
                        <p><?= __('edit_post_desc') ?></p>
                    </div>
                    <div class="flex_row" style="gap:8px;">
                        <span class="status_badge <?= $post['status'] ?>"><?= ucfirst(htmlspecialchars($post['status'])) ?></span>
                        <a href="preview.php?id=<?= $post_id ?>" class="btn btn_secondary btn_sm" target="_blank" rel="noopener"><i class="fa-solid fa-eye" aria-hidden="true"></i> <?= __('edit_post_preview') ?></a>
                        <a href="posts.php" class="btn btn_secondary btn_sm"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> <?= __('add_post_back') ?></a>
                    </div>
                </div>

                <!-- Card 1 — Cover Image Upload -->
                <div class="add_post_card">
                    <div class="add_post_card_body">
                        <div class="upload_zone<?= $has_image ? ' has_image' : '' ?>" id="uploadZone">
                            <div class="upload_placeholder" id="uploadPlaceholder"<?= $has_image ? ' style="display:none;"' : '' ?>>
                                <i class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i>
                                <span class="upload_text"><?= __('add_post_add_cover') ?></span>
                                <span class="upload_hint"><?= __('add_post_upload_hint') ?></span>
                            </div>
                            <div class="upload_preview" id="uploadPreview"<?= $has_image ? '' : ' style="display:none;"' ?>>
                                <img id="previewImage" src="<?= $has_image ? '../' . htmlspecialchars($post['image']) : '' ?>" alt="Cover image preview">
                                <div class="upload_info">
                                    <span id="imageInfo"><?= $has_image ? htmlspecialchars(basename($post['image'])) : '' ?></span>
                                    <button type="button" class="upload_remove" id="uploadRemove">
                                        <i class="fa-solid fa-xmark"></i> <?= __('add_post_remove') ?>
                                    </button>
                                </div>
                            </div>
                            <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp" class="upload_input">
                        </div>
                    </div>
                </div>

                <!-- Card 2 — Post Title -->
                <div class="add_post_card">
                    <div class="add_post_card_body">
                        <label class="add_post_input_label" for="title"><?= __('add_post_title_label') ?></label>
                        <input type="text" id="title" name="title" value="<?= htmlspecialchars($post['title']) ?>" required maxlength="255" placeholder="<?= __('add_post_title_placeholder') ?>" class="title_input" autocomplete="off">
                    </div>
                </div>

                <!-- Card 3 — Content Editor -->
                <?php 
                $editor_content = $post['content'];
                require __DIR__ . '/inc/editor.php'; 
                ?>

            </div>

            <!-- Right Column — Settings Panel -->
            <aside class="add_post_sidebar">

                <!-- Card 1 — Publish -->
                <div class="add_post_card">
                    <div class="add_post_card_header">
                        <i class="fa-solid fa-rocket" aria-hidden="true"></i>
                        <span><?= $is_admin ? __('add_post_publish_header') : __('add_post_submission_header') ?></span>
                    </div>
                    <div class="add_post_card_body">
                        <div class="add_post_form_group">
                            <label class="add_post_label" for="status"><?= __('add_post_status') ?></label>
                            <select id="status" name="status" class="add_post_select">
                                <?php if ($is_admin): ?>
                                <option value="published" <?= $post['status'] === STATUS_PUBLISHED ? 'selected' : '' ?>><?= __('add_post_published') ?></option>
                                <option value="draft" <?= $post['status'] === STATUS_DRAFT ? 'selected' : '' ?>><?= __('add_post_draft') ?></option>
                                <?php else: ?>
                                <option value="pending" <?= $post['status'] === STATUS_PENDING || $post['status'] === STATUS_REJECTED ? 'selected' : '' ?>><?= __('add_post_submit_review') ?></option>
                                <option value="draft" <?= $post['status'] === STATUS_DRAFT ? 'selected' : '' ?>><?= __('add_post_draft') ?></option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <?php if ($is_admin): ?>
                        <div class="add_post_form_group">
                            <label class="add_post_label"><?= __('add_post_visibility') ?></label>
                            <div class="add_post_visibility">
                                <i class="fa-solid fa-globe" aria-hidden="true"></i>
                                <span><?= __('add_post_public') ?></span>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="add_post_sidebar_actions">
                            <?php if ($is_admin): ?>
                            <button type="submit" class="btn btn_primary btn_full" data-set-status="published">
                                <i class="fa-solid fa-paper-plane" aria-hidden="true"></i> <?= __('edit_post_update_publish') ?>
                            </button>
                            <button type="submit" class="btn btn_secondary btn_full" data-set-status="draft">
                                <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> <?= __('edit_post_save_draft') ?>
                            </button>
                            <?php else: ?>
                            <button type="submit" class="btn btn_primary btn_full" data-set-status="pending">
                                <i class="fa-solid fa-paper-plane" aria-hidden="true"></i> <?= __('add_post_submit_review_btn') ?>
                            </button>
                            <button type="submit" class="btn btn_secondary btn_full" data-set-status="draft">
                                <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> <?= __('add_post_save_draft_btn') ?>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Card 2 — Category -->
                <div class="add_post_card">
                    <div class="add_post_card_header">
                        <i class="fa-solid fa-tag" aria-hidden="true"></i>
                        <span><?= __('add_post_category_header') ?></span>
                    </div>
                    <div class="add_post_card_body">
                        <div class="add_post_form_group">
                            <label class="add_post_label" for="category"><?= __('add_post_choose_category') ?></label>
                            <select id="category" name="category" required class="add_post_select">
                                <option value=""><?= __('add_post_select_category') ?></option>
                                <?php foreach ($categories as $c): ?>
                                <option value="<?= $c['id_category'] ?>" <?= (int)$post['id_category'] === (int)$c['id_category'] ? 'selected' : '' ?>><?= htmlspecialchars($c['cat_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Card 3 — Featured Image Preview -->
                <div class="add_post_card">
                    <div class="add_post_card_header">
                        <i class="fa-solid fa-image" aria-hidden="true"></i>
                        <span><?= __('add_post_featured_image') ?></span>
                    </div>
                    <div class="add_post_card_body">
                        <div class="add_post_image_area">
                            <img class="add_post_image_preview<?= $has_image ? ' show' : '' ?>" id="sidebarPreview" src="<?= $has_image ? '../' . htmlspecialchars($post['image']) : '' ?>" alt="Featured image preview">
                            <div class="add_post_image_placeholder" id="sidebarPlaceholder"<?= $has_image ? ' style="display:none;"' : '' ?>>
                                <i class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i>
                                <span><?= __('add_post_upload_image') ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 4 — Post Information -->
                <div class="add_post_card">
                    <div class="add_post_card_header">
                        <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                        <span><?= __('add_post_info_header') ?></span>
                    </div>
                    <div class="add_post_card_body">
                        <div class="add_post_info_row">
                            <span class="add_post_info_label"><?= __('add_post_author') ?></span>
                            <span class="add_post_info_value"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                        </div>
                        <div class="add_post_info_row">
                            <span class="add_post_info_label"><?= __('add_post_created') ?></span>
                            <span class="add_post_info_value"><?= date('M j, Y', strtotime($post['created_at'] ?? $post['updated_at'] ?? 'now')) ?></span>
                        </div>
                        <div class="add_post_info_row">
                            <span class="add_post_info_label"><?= __('add_post_words') ?></span>
                            <span class="add_post_info_value" id="wordCount">0</span>
                        </div>
                        <div class="add_post_info_row">
                            <span class="add_post_info_label"><?= __('add_post_characters') ?></span>
                            <span class="add_post_info_value" id="charCount">0</span>
                        </div>
                    </div>
                </div>

            </aside>
        </form>

    </div>
</div>

<script src="../assets/js/dashboard-post-form.js"></script>
<script src="../assets/js/dashboard-editor.js"></script>
<script>
(function () {
    // Toggle remove_image flag when Remove button is clicked
    var removeBtn = document.getElementById('uploadRemove');
    var removeFlag = document.getElementById('removeImageFlag');
    if (removeBtn && removeFlag) {
        removeBtn.addEventListener('click', function () {
            removeFlag.value = '1';
        });
    }

    // Keep existing image src after JS init
    var previewImg = document.getElementById('previewImage');
    var sidebarPreview = document.getElementById('sidebarPreview');
    if (previewImg && previewImg.getAttribute('src') && !previewImg.src) {
        previewImg.src = previewImg.getAttribute('src');
    }
    if (sidebarPreview && sidebarPreview.getAttribute('src') && !sidebarPreview.src) {
        sidebarPreview.src = sidebarPreview.getAttribute('src');
    }
})();
</script>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
