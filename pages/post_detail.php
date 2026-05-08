<?php

require_once '../config/db_connection.php';
require_once '../includes/Post.php';




$database = new Database();
$db = $database->getConnection();

$postObj = new Post($db);


$id = isset($_GET['id']) ? $_GET['id'] : header("Location: ../index.php");

$post = $postObj->detailById($id);


if (!$post) {
    header("Location: ../index.php");
    exit();
}


?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['title']); ?> Tangier Vibes</title>
    
    
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/post_detail.css">
</head>

<body>
    <?php require '../includes/header.php'; ?>

    <main>  
        <!-- hero section -->

        <div class="detail_hero" style="background-image: url('<?= htmlspecialchars($post['image']) ?>');">
            <div class="detail_header_content">
                <a href="../index.php" class="detail_back_btn">
                    <i class="fa-solid fa-arrow-left"></i> Back
                </a>
                <div>
                    <h1 class="detail_title"><?php echo htmlspecialchars($post['title']); ?></h1>
                    <div class="detail_meta">
                        <span class="meta_location"><i class="fa-solid fa-location-dot"></i> Tangier, Morocco</span>
                        <span class="detail_badge"><i class="fa-solid fa-star"></i> Must Visit</span>
                    </div>
                </div>
            </div>
        </div>


        <div class="detail_container">
            <!-- Main Content Area -->
            <div class="detail_main_card">
                <div class="detail_info_section">
                    <h1>About this place</h1>
                    <div class="detail_description">
                        <h3><?= nl2br(htmlspecialchars($post['description'])); ?></h3>
                        <br>
                        <p><?= nl2br(htmlspecialchars($post['content'])); ?></p>
                    </div>
                </div>

                <!-- Location Map Section -->
                <div class="detail_info_section">
                    <h1>Location</h1>
                    <div class="map_container">
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d12950.342125557762!2d-5.9427438!3d35.7904838!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xd0b875cf026777f%3A0x86702e1b1b22e1a2!2sCap%20Spartel!5e0!3m2!1sen!2sma!4v1714954500000!5m2!1sen!2sma" 
                            width="100%" 
                            height="350" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                </div>

                <div class="comments_section">
                    <div class="sidebar_divider"></div>
                    <h3 class="comments_title"><i class="fa-solid fa-comments"></i> Comments (1)</h3>
                    
                    <div class="comment_list">
                        <div class="comment_item">
                            <div class="comment_avatar">
                                <img src="../assets/img/home.jpg" alt="avatar">
                            </div>
                            <div class="comment_content">
                                <div class="comment_header">
                                    <span class="user_name">Oussama</span>
                                    <span class="comment_date">Apr 17, 2026</span>
                                </div>
                                <p class="comment_text">Waaaaw! This lighthouse is one of the most beautiful places in Tangier. The sunset here is magical. Highly recommended!</p>
                            </div>
                        </div>
                    </div>

                    <!-- Comment Form -->
                    <div class="comment_form_box">
                        <h3>Leave a Comment</h3>
                        <form action="#" class="comment_form">
                            <div class="form_group">
                                <input type="text" name="name" placeholder="Your Name" required class="comment_input">
                            </div>
                            <div class="form_group">
                                <textarea name="comment" placeholder="What's on your mind? Share your thoughts..." required class="comment_textarea"></textarea>
                            </div>
                            <button type="submit" class="submit_comment_btn">
                                <span>Post Comment</span>
                                <i class="fa-solid fa-paper-plane"></i>
                            </button>
                        </form>
                    </div>
                </div>

            </div>

            <!-- sidebar  -->

            <aside class="detail_sidebar_card">

                    <div class="sidebar_header_info">
                        <h4 class="sidebar_title_premium">About This Place</h4>
                    </div>

                    <div class="sidebar_divider"></div>

                    <div class="premium_info_list">

                            <!-- category  -->
                            <div class="premium_info_item">
                                <div class="item_icon_box">
                                    <i class="fa-solid fa-tag"></i>
                                </div>
                                <div class="item_text">
                                    <span class="item_label">CATEGORY</span>
                                    <span class="item_value"><?= htmlspecialchars($post['cat_name']); ?></span>
                                </div>
                            </div>

                            <!-- views  -->

                            <div class="premium_info_item">
                                <div class="item_icon_box">
                                    <i class="fa-solid fa-eye"></i>
                                </div>
                                <div class="item_text">
                                    <span class="item_label">VIEWS</span>
                                    <span class="item_value"><?= number_format($post['views']); ?></span>
                                </div>
                            </div>

                            <!-- Comments -->
                            <div class="premium_info_item">
                                <div class="item_icon_box">
                                    <i class="fa-solid fa-comment-dots"></i>
                                </div>
                                <div class="item_text">
                                    <span class="item_label">COMMENTS</span>
                                    <span class="item_value">1</span>
                                </div>
                            </div>

                            <!-- Added Date -->
                            <div class="premium_info_item">
                                <div class="item_icon_box">
                                    <i class="fa-solid fa-calendar-day"></i>
                                </div>
                                <div class="item_text">
                                    <span class="item_label">ADDED</span>
                                    <span class="item_value"><?= date('M d, Y', strtotime($post['created_at'])); ?></span>
                                </div>
                            </div>

                    </div>

                    <button class="save_favorite_btn">
                        <i class="fa-regular fa-heart"></i> Save to Favorites
                    </button>

                    <div class="sidebar_divider"></div>

                    <div class="share_section">
                        <span class="share_label">SHARE</span>
                        <div class="share_btns">
                            <a href="#" class="share_icon fb"><i class="fa-brands fa-facebook-f"></i></a>
                            <a href="#" class="share_icon tw"><i class="fa-brands fa-twitter"></i></a>
                            <a href="#" class="share_icon wa"><i class="fa-brands fa-whatsapp"></i></a>
                        </div>
                    </div>




            </aside>
                
        </div>
        
    </main>



    <footer class="footer_post" >
        <p>&copy; 2026 Tangier Vibes. All rights reserved.</p>
    </footer>


    <!-- Mobile sticky action bar  -->
    <div class="mobile_action_bar">
        
        <div class="mobile_share_menu" id="mobileShareMenu">
            <a href="#" class="share_item fb"><i class="fa-brands fa-facebook-f"></i></a>
            <a href="#" class="share_item tw"><i class="fa-brands fa-twitter"></i></a>
            <a href="#" class="share_item wa"><i class="fa-brands fa-whatsapp"></i></a>
        </div>

        <div class="action_bar_container">
            <button class="mobile_save_btn">
                <i class="fa-regular fa-heart"></i> <span>Save to Favorites</span>
            </button>
            <button class="mobile_share_btn" onclick="toggleShare()">
                <i class="fa-solid fa-share-nodes"></i>
            </button>
        </div>
    </div>




        <script src="../assets/js/main.js"></script>
</body>
</html>
