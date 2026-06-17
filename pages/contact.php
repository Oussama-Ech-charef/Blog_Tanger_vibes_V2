<?php
session_start();
require '../config/connection.php';
require_once '../includes/security.php';

send_security_headers();

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // validate CSRF
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!validate_csrf_token($csrf_token)) {
        $error = "Invalid request. Please try again.";
    }

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // validation
    if (empty($error)) {
        if (empty($name) || empty($email) || empty($subject) || empty($message)) {
            $error = "All fields are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format.";
        }
    }

    if (empty($error)) {
        try {
            // create table if not exists
            $conn->exec("
                create table if not exists contact_messages (
                    id_message int auto_increment primary key,
                    full_name varchar(150) not null,
                    email varchar(150) not null,
                    subject varchar(255) not null,
                    message text not null,
                    created_at timestamp default current_timestamp
                )
            ");

            // insert
            $stmt = $conn->prepare("
                insert into contact_messages (full_name, email, subject, message)
                values (:full_name, :email, :subject, :message)
            ");
            $stmt->execute([
                ':full_name' => $name,
                ':email' => $email,
                ':subject' => $subject,
                ':message' => $message
            ]);

            $success = "Thank you for reaching out! We will get back to you soon.";
        } catch (PDOException $e) {
            error_log("Contact form error: " . $e->getMessage());
            $error = "An unexpected error occurred. Please try again later.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Tangier Vibes</title>
    <meta name="description" content="Get in touch with the Tangier Vibes team. Send us a message, ask questions, or share your feedback.">
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link rel="apple-touch-icon" href="../assets/images/logo.png">
    <meta property="og:title" content="Contact Us - Tangier Vibes">
    <meta property="og:description" content="Get in touch with the Tangier Vibes team. Send us a message, ask questions, or share your feedback.">
    <meta property="og:image" content="../assets/images/logo.png">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://tanger.lovestoblog.com/contact.php">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/contact.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
</head>
<body>

<?php require '../includes/header.php'; ?>

<main class="contact_page">

    <!-- hero -->
    <section class="contact_head">
        <span class="contact_label">
            <i class="fa-solid fa-envelope"></i>
            Contact Us
        </span>
        <h1>We would love to hear from you.</h1>
        <p>
            Have a question, a suggestion, or just want to say hello? Send us a message and we will get back to you.
        </p>
    </section>

    <!-- info cards -->
    <section class="contact_section">
        <div class="contact_info_grid">
            <div class="info_card">
                <div class="info_icon"><i class="fa-solid fa-envelope"></i></div>
                <h3>Email</h3>
                <p><a href="mailto:contact@tangiervibes.com">contact@tangiervibes.com</a></p>
            </div>

            <div class="info_card">
                <div class="info_icon"><i class="fa-solid fa-phone"></i></div>
                <h3>Phone</h3>
                <p>+212 600 000 000</p>
            </div>

            <div class="info_card">
                <div class="info_icon"><i class="fa-solid fa-location-dot"></i></div>
                <h3>Address</h3>
                <p>Tangier, Morocco</p>
            </div>
        </div>
    </section>

    <!-- form + map layout -->
    <section class="contact_section">
        <h2 class="section_title">Send us a message</h2>
        <p class="section_desc">
            We reply within 24 hours.
        </p>

        <?php if (!empty($success)): ?>
            <p class="success_message"><?= htmlspecialchars($success); ?></p>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <p class="error_message"><?= htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <div class="contact_layout">
            <div class="contact_form_box">
                <form action="#" method="POST" class="contact_form">
                    <input type="hidden" name="csrf_token" value="<?= get_csrf_token(); ?>">

                    <div class="form_group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" placeholder="Your name" value="<?= htmlspecialchars($name ?? '') ?>" required>
                    </div>

                    <div class="form_group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="you@example.com" value="<?= htmlspecialchars($email ?? '') ?>" required>
                    </div>

                    <div class="form_group">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" placeholder="What is this about?" value="<?= htmlspecialchars($subject ?? '') ?>" required>
                    </div>

                    <div class="form_group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" placeholder="Write your message..." required><?= htmlspecialchars($message ?? ''); ?></textarea>
                    </div>

                    <button type="submit">
                        <i class="fa-solid fa-paper-plane"></i>
                        Send Message
                    </button>
                </form>
            </div>

            <div class="map_box">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d10754.139064625955!2d-5.8367744!3d35.7594653!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xd0b8165f4a90f3d%3A0x127b3b98cb1b5b62!2sTangier%2C%20Morocco!5e0!3m2!1sen!2sma!4v1710000000000!5m2!1sen!2sma"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </div>
    </section>

    <!-- faq -->
    <section class="contact_section">
        <h2 class="section_title">Frequently Asked Questions</h2>
        <p class="section_desc">
            Quick answers to common questions about Tangier Vibes.
        </p>

        <div class="faq_list">
            <div class="faq_item">
                <button class="faq_question">
                    What is Tangier Vibes?
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="faq_answer">
                    <p>Tangier Vibes is a community-driven tourism blog and city guide dedicated to Tangier, Morocco. It helps visitors and locals discover places, restaurants, beaches, cultural sites, and hidden gems through authentic user-generated content.</p>
                </div>
            </div>

            <div class="faq_item">
                <button class="faq_question">
                    How can I publish a post?
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="faq_answer">
                    <p>Simply create an account, log in, and go to your Dashboard. Click "Add Post" to write about a place, upload images, and submit. Your post will be reviewed by our team before being published to ensure quality.</p>
                </div>
            </div>

            <div class="faq_item">
                <button class="faq_question">
                    How can I contact the team?
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="faq_answer">
                    <p>You can reach us through the contact form on this page, or email us directly at contact@tangiervibes.com. We typically respond within 24 hours.</p>
                </div>
            </div>

            <div class="faq_item">
                <button class="faq_question">
                    Is registration free?
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="faq_answer">
                    <p>Yes, registration is completely free. Anyone can sign up, start exploring Tangier, and share their own experiences with the community.</p>
                </div>
            </div>
        </div>
    </section>

</main>

<?php require '../includes/footer.php'; ?>
<script src="../assets/js/main.js"></script>
<script src="../assets/js/contact.js"></script>
</body>
</html>
