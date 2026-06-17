<?php
session_start();
require '../config/connection.php';
require_once '../includes/security.php';

send_security_headers();

// check login
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit();
}

$id_user = $_SESSION['id_user'];
$role = $_SESSION['role'];

$error = "";

// get categories
$cat_stmt = $conn->prepare("select * from categories order by cat_name asc");
$cat_stmt->execute();
$categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // validate CSRF token
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!validate_csrf_token($csrf_token)) {
        $error = "Invalid request. Please try again.";
    }

    // get form values
    $title = trim($_POST['title'] ?? '');
    $category_id = $_POST['category_id'] ?? '';
    $content = trim($_POST['content'] ?? '');
    $publish_option = $_POST['publish_option'] ?? 'publish';

    // validation
    if (empty($error) && (empty($title) || empty($category_id) || empty($content))) {
        $error = "Title, category and content are required.";
    }

    $image = null;

    // upload image
    if (empty($error) && !empty($_FILES['image']['name'])) {
        $upload_errors = validate_uploaded_image($_FILES['image']);

        if (!empty($upload_errors)) {
            $error = $upload_errors[0];
        } else {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $secure_name = generate_secure_filename($ext);
            $image = "../assets/uploads/" . $secure_name;
            move_uploaded_file($_FILES['image']['tmp_name'], $image);
        }
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

        // insert post
        $stmt = $conn->prepare("
            insert into posts (
                id_category,
                id_user,
                id_approved_by,
                title,
                image,
                content,
                status,
                approved_at
            ) values (
                :id_category,
                :id_user,
                :id_approved_by,
                :title,
                :image,
                :content,
                :status,
                :approved_at
            )
        ");

        $stmt->execute([
            ':id_category' => $category_id,
            ':id_user' => $id_user,
            ':id_approved_by' => $approved_by,
            ':title' => $title,
            ':image' => $image,
            ':content' => $content,
            ':status' => $status,
            ':approved_at' => $approved_at,
        ]);

        header("Location: dashboard.php");
        exit();
    }
}




?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Post - Tangier Vibes</title>
    <meta name="description" content="Create a new post about a place, restaurant, beach, or experience in Tangier.">
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link rel="apple-touch-icon" href="../assets/images/logo.png">
    <meta property="og:title" content="Add Post - Tangier Vibes">
    <meta property="og:description" content="Create a new post about a place, restaurant, beach, or experience in Tangier.">
    <meta property="og:image" content="../assets/images/logo.png">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/add_post.css">
    <link rel="stylesheet" href="../assets/css/components.css">
</head>
<body>

<?php require '../includes/header.php'; ?>

<main class="dashboard_page">

    <!-- header -->
    <section class="dashboard_head">
        <div>
            <span class="dashboard_label">
                <i class="fa-solid fa-plus"></i>
                Add Post
            </span>

            <h1>Create new post</h1>

            <p>
                Add a new place to Tangier Vibes.
            </p>
        </div>

        <a href="dashboard.php" class="add_post_btn">
            <i class="fa-solid fa-arrow-left"></i>
            Back
        </a>
    </section>

    <!-- form -->
    <section class="form_box">

                <?php if (!empty($error)): ?>
                    <p class="error_message"><?= $error; ?></p>
                <?php endif; ?>

                <form action="add_post.php" method="POST" class="post_form" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= get_csrf_token(); ?>">

                    <!-- title -->
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" placeholder="Post title" required>

                    <!-- category and status -->
                    <div class="form_row">
                        <div class="form_group">
                            <label for="category_id">Category</label>
                            <select id="category_id" name="category_id" required>
                                <option value="">Choose category</option>

                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id_category']; ?>">
                                        <?= htmlspecialchars($category['cat_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form_group">
                            <label for="publish_option">Publish option</label>
                            <select id="publish_option" name="publish_option">
                                <option value="publish">Publish now</option>
                                <option value="draft">Save as draft</option>
                            </select>
                        </div>
                    </div>

                    <!-- image -->
                    <div class="form_row">
                        <div class="form_group">
                            <label for="image">Image</label>
                            <input type="file" id="image" name="image" accept="image/*">
                            <div class="image_preview" id="image_preview"></div>
                        </div>
                    </div>

                    <!-- content -->
                    <label for="content">Content</label>
                    <textarea id="content" name="content" placeholder="Write post content..." required></textarea>

                    <!-- button -->
                    <button type="submit" class="add_post_btn">
                        <i class="fa-solid fa-paper-plane"></i>
                        Publish
                    </button>

                </form>
    </section>

</main>

<script src="../assets/js/main.js"></script>
<script src="../assets/js/add_post.js"></script>
</body>
</html>
