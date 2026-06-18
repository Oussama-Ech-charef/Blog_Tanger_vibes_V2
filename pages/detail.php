<?php

session_start();

require '../config/connection.php';
require_once '../includes/security.php';
require_once '../includes/lang.php';
 
 send_security_headers();

// check post id
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$post_id = $_GET['id'];


// get post
$stmt = $conn->prepare("
    select posts.*, categories.cat_name, users.user_name
    from posts
    inner join categories on posts.id_category = categories.id_category
    inner join users on posts.id_user = users.id_user
    where posts.id_post = :id_post and posts.status = 'published'
");

$stmt->execute([
    ':id_post' => $post_id
    ]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    header('Location: index.php');
    exit;
}



?>

<!DOCTYPE html>
<html lang="<?= get_lang_code() ?>" dir="<?= get_lang_dir() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['title']) ?> - Tangier Vibes</title>
    <meta name="description" content="<?= htmlspecialchars(substr(strip_tags($post['content']), 0, 150)) ?>">
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link rel="apple-touch-icon" href="../assets/images/logo.png">
    <meta property="og:title" content="<?= htmlspecialchars($post['title']) ?> - Tangier Vibes">
    <meta property="og:description" content="<?= htmlspecialchars(substr(strip_tags($post['content']), 0, 150)) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($post['image'] ?? '../assets/images/logo.png') ?>">
    <meta property="og:type" content="article">
    <meta property="og:url" content="https://tanger.lovestoblog.com/detail.php?id=<?= $post['id_post'] ?>">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
   
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/detail.css">
    <link rel="stylesheet" href="../assets/css/rtl.css">
</head>
<body>


<?php require '../includes/header.php' ?>

    <div class="detail_container">

        <!-- category -->
        <div class="detail_category">
            <i class="fa-solid fa-layer-group"></i> TANGER / <span class="cat_name"><?= htmlspecialchars($post['cat_name']); ?></span>
        </div>

        <h1><?= htmlspecialchars($post['title']); ?></h1>

        <!-- post info -->
        <div class="icons">
            <span><i class="fa-solid fa-calendar-days"></i><?= date('M d, Y', strtotime($post['created_at'])); ?></span>
            <span><i class="fa-solid fa-circle-user"></i><?= __('detail_by') ?> <?= htmlspecialchars($post['user_name'] ?? 'Admin'); ?></span>
            
        </div>

        <!-- image -->
        <img src="<?= htmlspecialchars($post['image']); ?>" alt="<?= htmlspecialchars($post['title']); ?>" loading="lazy">

        <!-- content -->
        <div class="content">
            <?= nl2br(htmlspecialchars($post['content'])); ?>
        </div>


        <!-- share links -->
        <div class="social">
            <i class="fas fa-share-alt"></i> <?= __('detail_share') ?>: <a href="#">Facebook</a> /<a href="#">Twitter</a> /<a href="#">WhatsApp</a>
        </div>

        <!-- map design -->
        <div class="map_box">
            <iframe
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d10754.139064625955!2d-5.8367744!3d35.7594653!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xd0b8165f4a90f3d%3A0x127b3b98cb1b5b62!2sTangier%2C%20Morocco!5e0!3m2!1sen!2sma!4v1710000000000!5m2!1sen!2sma"
                width="100%"
                height="400"
                style="border:0;"
                allowfullscreen=""
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>


        <!-- comments -->
        <div class="comments_posts">
            <div class="comment_title">
                <i class="fa-solid fa-comment-dots"></i> <?= __('detail_comments_title') ?>
                <span >1</span>
            </div>
        </div>

        <div class="comments_list">
            
            <div class="comment_item">
                <div class="comment_header">
                    <span class="comment_name"><i class="fa-solid fa-circle-user"></i>Oussama</span>
                    <span class="comment_date"><i class="fa-solid fa-calendar-days"></i>May 16, 2026</span>
                </div>
                <div class="comment_text">
                    Lorem ipsum dolor sit amet consectetur adipisicing elit. Dolore, saepe.
                </div>
            </div>

        </div>

        <div class="comment_form">
            <h3 class="comment_title"><?= __('detail_comment_leave') ?></h3>

            <form action="#" method="POST">
                <div class="form_name">
                    <label><?= __('detail_comment_name_label') ?> :</label>
                    <input type="text" name="name" placeholder="<?= __('detail_comment_name_placeholder') ?>">
                </div>
                <div class="form_desc">
                    <label><?= __('detail_comment_message_label') ?> :</label>
                    <textarea name="message" placeholder="<?= __('detail_comment_message_placeholder') ?>"></textarea>
                </div>
                <button type="submit"><i class="fa-solid fa-paper-plane"></i> <?= __('detail_comment_btn') ?></button>
            </form>
        </div>


    </div>




    <script src="../assets/js/main.js"></script>
</body>

</html>
