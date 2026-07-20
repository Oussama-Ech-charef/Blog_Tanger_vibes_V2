<?php
// Add new post
require_once __DIR__ . '/init.php';
$page_title = __('add_post_title');
require_once __DIR__ . '/../includes/post_helpers.php';

$uid = (int)$_SESSION['id_user'];
$errors = [];
$categories = $conn->query("SELECT * FROM categories ORDER BY cat_name ASC")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_post'])) {
    $title = trim($_POST['title'] ?? '');
    $cat_id = (int)($_POST['category'] ?? 0);
    $content = trim($_POST['content'] ?? '');

    if ($is_admin) {
        $status = in_array($_POST['status'] ?? '', [STATUS_DRAFT, STATUS_PUBLISHED]) ? $_POST['status'] : STATUS_DRAFT;
    } else {
        // Users can only set draft or pending (submit for review)
        $status = in_array($_POST['status'] ?? '', [STATUS_DRAFT, STATUS_PENDING]) ? $_POST['status'] : STATUS_DRAFT;
    }

    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) $errors[] = __('post_error_invalid');
    $errors = array_merge($errors, validate_post_input($title, $cat_id, $content));

    $img = process_post_image();
    $errors = array_merge($errors, $img['errors']);

    if (empty($errors)) {
        try {
            $nid = insert_post($conn, $cat_id, $uid, $title, $img['path'], $content, $status, $is_admin);
            header('Location: posts.php?msg=created');
            exit;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $errors[] = __('post_error_db');
        }
    }
}

require_once __DIR__ . '/inc/header.php';
?>

<?php if (!empty($errors)): render_notification(implode(' | ', array_map('htmlspecialchars', $errors)), 'error'); endif; ?>

<div class="add_post_page">
    <div class="add_post_layout">

        <form method="POST" action="add_post.php" enctype="multipart/form-data" id="addPostForm">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="add_post" value="1">

            <!-- Left Column — Writing Area -->
            <div class="add_post_main">

                <div class="add_post_header">
                    <div>
                        <h1><?= __('add_post_create') ?></h1>
                        <p><?= __('add_post_create_desc') ?></p>
                    </div>
                    <a href="posts.php" class="btn btn_secondary btn_sm"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> <?= __('add_post_back') ?></a>
                </div>

                <!-- Card 1 — Cover Image Upload -->
                <div class="add_post_card">
                    <div class="add_post_card_body">
                        <div class="upload_zone" id="uploadZone">
                            <div class="upload_placeholder" id="uploadPlaceholder">
                                <i class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i>
                                <span class="upload_text"><?= __('add_post_add_cover') ?></span>
                                <span class="upload_hint"><?= __('add_post_upload_hint') ?></span>
                            </div>
                            <div class="upload_preview" id="uploadPreview" style="display:none;">
                                <img id="previewImage" src="" alt="<?= __('cover_image_preview_alt') ?>">
                                <div class="upload_info">
                                    <span id="imageInfo"></span>
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
                        <input type="text" id="title" name="title" value="<?= htmlspecialchars($title ?? '') ?>" required maxlength="255" placeholder="<?= __('add_post_title_placeholder') ?>" class="title_input" autocomplete="off">
                    </div>
                </div>

                <!-- Card 3 — Content Editor -->
                <?php 
                $editor_content = $content ?? '';
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
                                <option value="published"><?= __('add_post_published') ?></option>
                                <option value="draft" selected><?= __('add_post_draft') ?></option>
                                <?php else: ?>
                                <option value="pending" selected><?= __('add_post_submit_review') ?></option>
                                <option value="draft"><?= __('add_post_draft') ?></option>
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
                                <i class="fa-solid fa-paper-plane" aria-hidden="true"></i> <?= __('add_post_publish_btn') ?>
                            </button>
                            <button type="submit" class="btn btn_secondary btn_full" data-set-status="draft">
                                <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> <?= __('add_post_save_draft_btn') ?>
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
                                <option value="<?= $c['id_category'] ?>" <?= ($cat_id ?? 0) == (int)$c['id_category'] ? 'selected' : '' ?>><?= htmlspecialchars($c['cat_name']) ?></option>
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
                            <img class="add_post_image_preview" id="sidebarPreview" src="" alt="<?= __('featured_image_preview_alt') ?>">
                            <div class="add_post_image_placeholder" id="sidebarPlaceholder">
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
                            <span class="add_post_info_label"><?= __('add_post_date') ?></span>
                            <span class="add_post_info_value"><?= date('M j, Y') ?></span>
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

<?php require_once __DIR__ . '/inc/footer.php'; ?>
