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

// check admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// validate CSRF token from GET link
$csrf_token = $_GET['csrf_token'] ?? '';
if (!validate_csrf_token($csrf_token)) {
    header("Location: dashboard.php?error=invalid_request");
    exit();
}

// check post id
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$post_id = $_GET['id'];
$id_user = $_SESSION['id_user'];
$error = "";

// get post
$stmt = $conn->prepare("
    select posts.*, categories.cat_name, users.user_name
    from posts
    inner join categories on posts.id_category = categories.id_category
    inner join users on posts.id_user = users.id_user
    where posts.id_post = :id_post
");
$stmt->execute([
    ':id_post' => $post_id
]);

$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // validate CSRF token from form
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!validate_csrf_token($csrf_token)) {
        $error = "Invalid request. Please try again.";
    }

    // get reason
    $rejection_reason = trim($_POST['rejection_reason'] ?? '');

    // validation
    if (empty($error) && empty($rejection_reason)) {
        $error = "Rejection reason is required.";
    }

    if (empty($error)) {
        // reject post
        $stmt = $conn->prepare("
            update posts
            set status = 'rejected',
                id_approved_by = :id_approved_by,
                approved_at = now(),
                rejection_reason = :rejection_reason
            where id_post = :id_post and status = 'pending'
        ");

        $stmt->execute([
            ':id_approved_by' => $id_user,
            ':rejection_reason' => $rejection_reason,
            ':id_post' => $post_id
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
    <title>Reject Post - Tangier Vibes</title>
    <meta name="description" content="Provide a rejection reason for a pending post on Tangier Vibes.">
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link rel="apple-touch-icon" href="../assets/images/logo.png">
    <meta property="og:title" content="Reject Post - Tangier Vibes">
    <meta property="og:description" content="Provide a rejection reason for a pending post on Tangier Vibes.">
    <meta property="og:image" content="../assets/images/logo.png">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/reject.css">
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
                <i class="fa-solid fa-xmark"></i>
                Reject Post
            </span>

            <h1><?= htmlspecialchars($post['title']); ?></h1>
            <p>Write the reason so the author knows what to fix.</p>
        </div>

        <a href="dashboard.php" class="add_post_btn">
            <i class="fa-solid fa-arrow-left"></i>
            Back
        </a>
    </section>

    <!-- reject form -->
    <section class="reject_box">
        <?php if (!empty($error)): ?>
            <p class="error_message"><?= htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form action="#" method="POST" class="reject_form">
            <input type="hidden" name="csrf_token" value="<?= get_csrf_token(); ?>">
            <label for="rejection_reason">Rejection reason</label>
            <textarea id="rejection_reason" name="rejection_reason" placeholder="Explain what needs to be changed..." required><?= htmlspecialchars($post['rejection_reason'] ?? ''); ?></textarea>

            <button type="submit" class="add_post_btn">
                <i class="fa-solid fa-ban"></i>
                Reject Post
            </button>
        </form>
    </section>
</main>

<script src="../assets/js/main.js"></script>
</body>
</html>
