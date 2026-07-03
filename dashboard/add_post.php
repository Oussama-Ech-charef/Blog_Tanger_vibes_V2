<?php
$page_title = 'Add New Post';
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/../includes/post_helpers.php';

$uid = (int)$_SESSION['id_user'];
$errors = [];
$categories = $conn->query("SELECT * FROM categories ORDER BY cat_name ASC")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_post'])) {
    $title = trim($_POST['title'] ?? '');
    $cat_id = (int)($_POST['category'] ?? 0);
    $content = trim($_POST['content'] ?? '');
    $status = in_array($_POST['status'] ?? '', [STATUS_DRAFT, STATUS_PUBLISHED]) ? $_POST['status'] : STATUS_PUBLISHED;

    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) $errors[] = 'Invalid request.';
    $errors = array_merge($errors, validate_post_input($title, $cat_id, $content));

    $img = process_post_image();
    $errors = array_merge($errors, $img['errors']);

    if (empty($errors)) {
        try {
            $nid = insert_post($conn, $cat_id, $uid, $title, $img['path'], $content, $status, true);
            header('Location: posts.php?msg=created');
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

<div class="add_post_page">
    <div class="add_post_layout">

        <form method="POST" action="add_post.php" enctype="multipart/form-data" id="addPostForm">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="add_post" value="1">

            <!-- Left Column — Writing Area -->
            <div class="add_post_main">

                <div class="add_post_header">
                    <div>
                        <h1>Create New Post</h1>
                        <p>Share your story with the world</p>
                    </div>
                    <a href="posts.php" class="btn btn_secondary btn_sm"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back</a>
                </div>

                <!-- Card 1 — Cover Image Upload -->
                <div class="add_post_card">
                    <div class="add_post_card_body">
                        <div class="upload_zone" id="uploadZone">
                            <div class="upload_placeholder" id="uploadPlaceholder">
                                <i class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i>
                                <span class="upload_text">Add a cover image</span>
                                <span class="upload_hint">Click to browse or drag &amp; drop — JPEG, PNG, WebP</span>
                            </div>
                            <div class="upload_preview" id="uploadPreview" style="display:none;">
                                <img id="previewImage" src="" alt="Cover image preview">
                                <div class="upload_info">
                                    <span id="imageInfo"></span>
                                    <button type="button" class="upload_remove" id="uploadRemove">
                                        <i class="fa-solid fa-xmark"></i> Remove
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
                        <label class="add_post_input_label" for="title">Post Title</label>
                        <input type="text" id="title" name="title" value="<?= htmlspecialchars($title ?? '') ?>" required maxlength="255" placeholder="Enter your post title..." class="title_input" autocomplete="off">
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
                        <span>Publish</span>
                    </div>
                    <div class="add_post_card_body">
                        <div class="add_post_form_group">
                            <label class="add_post_label" for="status">Status</label>
                            <select id="status" name="status" class="add_post_select">
                                <option value="published">Published</option>
                                <option value="draft" selected>Draft</option>
                            </select>
                        </div>
                        <div class="add_post_form_group">
                            <label class="add_post_label">Visibility</label>
                            <div class="add_post_visibility">
                                <i class="fa-solid fa-globe" aria-hidden="true"></i>
                                <span>Public</span>
                            </div>
                        </div>
                        <div class="add_post_sidebar_actions">
                            <button type="submit" class="btn btn_primary btn_full" data-set-status="published">
                                <i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Publish
                            </button>
                            <button type="submit" class="btn btn_secondary btn_full" data-set-status="draft">
                                <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Save Draft
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Card 2 — Category -->
                <div class="add_post_card">
                    <div class="add_post_card_header">
                        <i class="fa-solid fa-tag" aria-hidden="true"></i>
                        <span>Category</span>
                    </div>
                    <div class="add_post_card_body">
                        <div class="add_post_form_group">
                            <label class="add_post_label" for="category">Choose category</label>
                            <select id="category" name="category" required class="add_post_select">
                                <option value="">Select a category...</option>
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
                        <span>Featured Image</span>
                    </div>
                    <div class="add_post_card_body">
                        <div class="add_post_image_area">
                            <img class="add_post_image_preview" id="sidebarPreview" src="" alt="Featured image preview">
                            <div class="add_post_image_placeholder" id="sidebarPlaceholder">
                                <i class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i>
                                <span>Upload image</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 4 — Post Information -->
                <div class="add_post_card">
                    <div class="add_post_card_header">
                        <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                        <span>Post Information</span>
                    </div>
                    <div class="add_post_card_body">
                        <div class="add_post_info_row">
                            <span class="add_post_info_label">Author</span>
                            <span class="add_post_info_value"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                        </div>
                        <div class="add_post_info_row">
                            <span class="add_post_info_label">Date</span>
                            <span class="add_post_info_value"><?= date('M j, Y') ?></span>
                        </div>
                        <div class="add_post_info_row">
                            <span class="add_post_info_label">Words</span>
                            <span class="add_post_info_value" id="wordCount">0</span>
                        </div>
                        <div class="add_post_info_row">
                            <span class="add_post_info_label">Characters</span>
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
<?php require_once __DIR__ . '/inc/footer.php'; ?>
