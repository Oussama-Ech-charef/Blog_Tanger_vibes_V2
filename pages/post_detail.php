<?php

session_start();
require_once '../config/db_connection.php';
require_once '../includes/Post.php';


$database = new Database();
$db = $database->getConnection();
$postObj = new Post($db);


$id = isset($_GET['id']) ? $_GET['id'] : null;


if (!$id) {
    header("Location: index.php");
    exit();
}


$post = $postObj->detailById($id);


if (!$post) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['title']); ?> - Tangier Vibes</title>
    
  
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
   
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <link rel="stylesheet" href="../assets/css/post_detail.css">
</head>

<body>
  
    <?php require '../includes/header.php'; ?>

    <main>  
        
        <section class="detail_hero" style="background-image: linear-gradient(to bottom, rgba(0,0,0,0.2), rgba(0,0,0,0.7)), url('<?= htmlspecialchars($post['image']) ?>');">
            <div class="detail_header_content">
                <a href="index.php" class="detail_back_btn">
                    <i class="fa-solid fa-arrow-left"></i> Home
                </a>
                <div class="hero_text_box">
                    <h1 class="detail_title"><?= htmlspecialchars($post['title']); ?></h1>
                    <div class="detail_meta">
                        <span class="meta_tag"><i class="fa-solid fa-location-dot"></i> Tangier, Morocco</span>
                       
                    </div>
                </div>
            </div>
        </section>

        <div class="detail_container">
           
            <div class="detail_main_card">
                <div class="content_section">
                    <h2 class="section_title">About this place</h2>
                    <div class="detail_text_content">
                        <p class="description_lead"><?= nl2br(htmlspecialchars($post['description'])); ?></p>
                        <div class="full_content">
                            <?= nl2br(htmlspecialchars($post['content'])); ?>
                        </div>
                    </div>
                </div>

                
                <div class="comments_wrapper">
                    <h3 class="comments_heading">Comments</h3>
                    
                    <div class="comment_list">
                        <div class="comment_box">
                            <div class="comment_user_img">
                                <i class="fa-solid fa-user-circle"></i>
                            </div>
                            <div class="comment_body">
                                <div class="comment_top">
                                    <strong>Visitor</strong>
                                    <span>Just now</span>
                                </div>
                                <p>This is an amazing place in Tangier! Totally worth visiting.</p>
                            </div>
                        </div>
                    </div>

                    <div class="comment_form_container">
                        <h4 class="form_title">Leave a Comment</h4>
                        <form class="comment_form" action="#" method="POST">
                            <div class="input_row">
                                <input type="text" placeholder="Your Name" required>
                            </div>
                            <div class="input_row">
                                <textarea placeholder="Share your experience about this place..." required></textarea>
                            </div>
                            <button type="submit" class="post_comment_btn">
                                <span>Post Comment</span>
                                <i class="fa-solid fa-paper-plane"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <aside class="detail_sidebar">
                <div class="sidebar_card">
                    <h3 class="sidebar_heading">Place Info</h3>
                    <ul class="info_list">
                        <li>
                            <i class="fa-solid fa-tag"></i>
                            <div>
                                <span>Category</span>
                                <strong>Tangier Place</strong>
                            </div>
                        </li>
                        <li>
                            <i class="fa-solid fa-eye"></i>
                            <div>
                                <span>Views</span>
                                <strong><?= number_format($post['views']); ?></strong>
                            </div>
                        </li>
                        <li>
                            <i class="fa-solid fa-calendar-day"></i>
                            <div>
                                <span>Published</span>
                                <strong><?= date('M d, Y', strtotime($post['created_at'])); ?></strong>
                            </div>
                        </li>
                    </ul>

                    <button class="favorite_btn">
                        <i class="fa-regular fa-heart"></i> Add to Favorites
                    </button>
                </div>
            </aside>

        </div>
    </main>

    <script src="../assets/js/main.js"></script>
</body>
</html>

