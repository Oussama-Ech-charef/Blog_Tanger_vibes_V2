<?php

session_start();
require '../config/connection.php';
require_once '../includes/security.php';
require_once '../includes/lang.php';
 
 send_security_headers();

// check login
if (!isset($_SESSION['id_user'])) {
    header("Location: index.php");
    exit();
}

// check post id
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$post_id = $_GET['id'];
$id_user = $_SESSION['id_user'];
$role = $_SESSION['role'];
$error = "";

// get categories
$cat_stmt = $conn->prepare("
    select * from categories
     order by cat_name asc");
$cat_stmt->execute();
$categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);

// get post
$stmt = $conn->prepare("
    select * from posts
    where id_post = :id_post and id_user = :id_user
");

$stmt->execute([
    ':id_post' => $post_id,
    ':id_user' => $id_user
]);

$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // validate CSRF token
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!validate_csrf_token($csrf_token)) {
        $error = __('add_post_error_invalid');
    }

    // get form values
    $title = trim($_POST['title'] ?? '');
    $category_id = $_POST['category_id'] ?? '';
    $content = trim($_POST['content'] ?? '');
    $publish_option = $_POST['publish_option'] ?? 'publish';
    $image = $post['image'];

    // validate category exists
    if (empty($error) && !empty($category_id)) {
        $cat_check = $conn->prepare("select id_category from categories where id_category = :id");
        $cat_check->execute([':id' => $category_id]);
        if (!$cat_check->fetch()) {
            $error = __('add_post_error_category');
        }
    }

    // upload image
    if (empty($error) && !empty($_FILES['image']['name'])) {
        $upload_errors = validate_uploaded_image($_FILES['image']);

        if (!empty($upload_errors)) {
            $error = $upload_errors[0];
        } else {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $secure_name = generate_secure_filename($ext);
            $new_image = "../assets/uploads/" . $secure_name;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $new_image)) {
                // delete old image if exists
                if (!empty($post['image'])) {
                    $old_path = __DIR__ . '/../' . $post['image'];
                    if (file_exists($old_path)) {
                        unlink($old_path);
                    }
                }
                $image = $new_image;
            }
        }
    }

    // validation
    if (empty($error) && (empty($title) || empty($category_id) || empty($content))) {
        $error = __('add_post_error_required');
    }

    if (empty($error)) {

        // status
        if ($publish_option === 'draft') {
            $status = 'draft';
            $approved_by = null;
            $approved_at = null;
        } else {
            if ($role === 'admin') {
                $status = 'published';
                $approved_by = $id_user;
                $approved_at = date('Y-m-d H:i:s');
            } else {
                $status = 'pending';
                $approved_by = null;
                $approved_at = null;
            }
        }

        // update post
        $stmt = $conn->prepare("
            update posts
            set id_category = :id_category,
                title = :title,
                image = :image,
                content = :content,
                status = :status,
                id_approved_by = :id_approved_by,
                approved_at = :approved_at,
                rejection_reason = null
            where id_post = :id_post and id_user = :id_user
        ");

        $stmt->execute([
            ':id_category' => $category_id,
            ':title' => $title,
            ':image' => $image,
            ':content' => $content,
            ':status' => $status,
            ':id_approved_by' => $approved_by,
            ':approved_at' => $approved_at,
            ':id_post' => $post_id,
            ':id_user' => $id_user
        ]);

        header("Location: dashboard.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="<?= get_lang_code() ?>" dir="<?= get_lang_dir() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('edit_post_label') ?> - Tangier Vibes</title>
    <meta name="description" content="<?= __('edit_post_desc') ?>">
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link rel="apple-touch-icon" href="../assets/images/logo.png">
    <meta property="og:title" content="<?= __('edit_post_label') ?> - Tangier Vibes">
    <meta property="og:description" content="<?= __('edit_post_desc') ?>">
    <meta property="og:image" content="../assets/images/logo.png">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/add_post.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/rtl.css">
</head>
<body>

<?php require '../includes/header.php'; ?>

<main class="dashboard_page">
    <!-- header -->
    <section class="dashboard_head">
        <div>
            <span class="dashboard_label">
                <i class="fa-solid fa-pen"></i>
                <?= __('edit_post_label') ?>
            </span>

            <h1><?= __('edit_post_title') ?></h1>
            <p><?= __('edit_post_desc') ?></p>
        </div>

        <a href="dashboard.php" class="add_post_btn">
            <i class="fa-solid fa-arrow-left"></i>
            <?= __('edit_post_back') ?>
        </a>
    </section>

    <!-- form -->
    <section class="form_box">

            <?php if (!empty($error)): ?>
                <p class="error_message"><?= $error; ?></p>
            <?php endif; ?>

            <form action="#" method="POST" class="post_form" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= get_csrf_token(); ?>">

                <!-- title -->
                <label for="title"><?= __('add_post_title_label') ?></label>
                <input type="text" id="title" name="title" placeholder="<?= __('add_post_title_placeholder') ?>" value="<?= htmlspecialchars($post['title']); ?>"  required>

                <!-- category and status -->
                <div class="form_row">
                    <div class="form_group">
                        <label for="category_id"><?= __('add_post_category_label') ?></label>
                        <select id="category_id" name="category_id" required>
                            <option value=""><?= __('add_post_category_placeholder') ?></option>

                            <?php foreach ($categories as $category): ?>
                                <option 
                                    value="<?= $category['id_category']; ?>"
                                    <?= $category['id_category'] == $post['id_category'] ? 'selected' : ''; ?>
                                >
                                    <?= htmlspecialchars($category['cat_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form_group">
                        <label for="publish_option"><?= __('add_post_publish_label') ?></label>
                        <select id="publish_option" name="publish_option">
                            <option value="publish"><?= __('add_post_publish_option') ?></option>
                            <option value="draft" <?= $post['status'] === 'draft' ? 'selected' : ''; ?>>
                                <?= __('add_post_draft_option') ?>
                            </option>
                        </select>
                    </div>
                </div>

                <!-- image -->
                <div class="form_row">
                    <div class="form_group">
                        <label for="image"><?= __('add_post_image_label') ?></label>
                        <input type="file" id="image" name="image" accept="image/*">
                        <div class="image_preview" id="image_preview">
                            <?php if (!empty($post['image'])): ?>
                                <img src="<?= htmlspecialchars($post['image']); ?>" alt="<?= __('edit_post_label') ?>" loading="lazy">
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($post['image'])): ?>
                            <p class="current_file">
                                <?= sprintf(__('edit_post_current_image'), htmlspecialchars($post['image'])); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- content -->
                <label for="content"><?= __('add_post_content_label') ?></label>
                <textarea id="content" name="content" placeholder="<?= __('add_post_content_placeholder') ?>" required><?= htmlspecialchars($post['content']); ?></textarea>

                <!-- button -->
                <button type="submit" class="add_post_btn">
                    <i class="fa-solid fa-paper-plane"></i>
                    <?= __('add_post_submit') ?>
                </button>

            </form>
    </section>
</main>

<script src="../assets/js/main.js"></script>
<script src="../assets/js/add_post.js"></script>
</body>
</html>
