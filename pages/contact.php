<?php
session_start();
require '../config/connection.php';
require_once '../includes/security.php';
require_once '../includes/lang.php';
 
 send_security_headers();

$success = "";
$error = "";

if (isset($_SESSION['contact_submitted'])) {
    $success = $_SESSION['contact_submitted'];
    unset($_SESSION['contact_submitted']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // validate CSRF
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!validate_csrf_token($csrf_token)) {
        $error = __('contact_error_invalid');
    }

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // validation
    if (empty($error)) {
        if (empty($name) || empty($email) || empty($subject) || empty($message)) {
            $error = __('contact_error_required');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = __('contact_error_email');
        }
    }

    if (empty($error)) {
        try {
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

            $_SESSION['contact_submitted'] = __('contact_success');
            header('Location: contact.php');
            exit;
        } catch (PDOException $e) {
            error_log("Contact form error: " . $e->getMessage());
            $error = __('contact_error_generic');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= get_lang_code() ?>" dir="<?= get_lang_dir() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('contact_label') ?> - Tangier Vibes</title>
    <meta name="description" content="<?= __('contact_meta_desc') ?>">
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link rel="apple-touch-icon" href="../assets/images/logo.png">
    <meta property="og:title" content="<?= __('contact_label') ?> - Tangier Vibes">
    <meta property="og:description" content="<?= __('contact_meta_desc') ?>">
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
    <link rel="stylesheet" href="../assets/css/rtl.css">
</head>
<body>

<?php require '../includes/header.php'; ?>

<main class="contact_page">

    <!-- hero -->
    <section class="contact_head">
        <span class="contact_label">
            <i class="fa-solid fa-envelope"></i>
            <?= __('contact_label') ?>
        </span>
        <h1><?= __('contact_title') ?></h1>
        <p>
            <?= __('contact_desc') ?>
        </p>
    </section>

    <!-- info cards -->
    <section class="contact_section">
        <div class="contact_info_grid">
            <div class="info_card">
                <div class="info_icon"><i class="fa-solid fa-envelope"></i></div>
                <h3><?= __('contact_email_title') ?></h3>
                <p><a href="mailto:contact@tangiervibes.com">contact@tangiervibes.com</a></p>
            </div>

            <div class="info_card">
                <div class="info_icon"><i class="fa-solid fa-phone"></i></div>
                <h3><?= __('contact_phone_title') ?></h3>
                <p>+212 600 000 000</p>
            </div>

            <div class="info_card">
                <div class="info_icon"><i class="fa-solid fa-location-dot"></i></div>
                <h3><?= __('contact_address_title') ?></h3>
                <p>Tangier, Morocco</p>
            </div>
        </div>
    </section>

    <!-- form + map layout -->
    <section class="contact_section">
        <h2 class="section_title"><?= __('contact_form_title') ?></h2>
        <p class="section_desc">
            <?= __('contact_form_desc') ?>
        </p>

        <?php if (!empty($error)): ?>
            <p class="error_message"><?= htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <div class="contact_layout">
            <div class="contact_form_box">
                <form action="#" method="POST" class="contact_form">
                    <input type="hidden" name="csrf_token" value="<?= get_csrf_token(); ?>">

                    <div class="form_group">
                        <label for="name"><?= __('contact_form_name_label') ?></label>
                        <input type="text" id="name" name="name" placeholder="<?= __('contact_form_name_placeholder') ?>" value="<?= htmlspecialchars($name ?? '') ?>" required>
                    </div>

                    <div class="form_group">
                        <label for="email"><?= __('contact_form_email_label') ?></label>
                        <input type="email" id="email" name="email" placeholder="<?= __('contact_form_email_placeholder') ?>" value="<?= htmlspecialchars($email ?? '') ?>" required>
                    </div>

                    <div class="form_group">
                        <label for="subject"><?= __('contact_form_subject_label') ?></label>
                        <input type="text" id="subject" name="subject" placeholder="<?= __('contact_form_subject_placeholder') ?>" value="<?= htmlspecialchars($subject ?? '') ?>" required>
                    </div>

                    <div class="form_group">
                        <label for="message"><?= __('contact_form_message_label') ?></label>
                        <textarea id="message" name="message" placeholder="<?= __('contact_form_message_placeholder') ?>" required><?= htmlspecialchars($message ?? ''); ?></textarea>
                    </div>

                    <button type="submit">
                        <i class="fa-solid fa-paper-plane"></i>
                        <?= __('contact_form_btn') ?>
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
        <h2 class="section_title"><?= __('faq_title') ?></h2>
        <p class="section_desc">
            <?= __('faq_desc') ?>
        </p>

        <div class="faq_list">
            <div class="faq_item">
                <button class="faq_question">
                    <?= __('faq_q1') ?>
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="faq_answer">
                    <p><?= __('faq_a1') ?></p>
                </div>
            </div>

            <div class="faq_item">
                <button class="faq_question">
                    <?= __('faq_q2') ?>
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="faq_answer">
                    <p><?= __('faq_a2') ?></p>
                </div>
            </div>

            <div class="faq_item">
                <button class="faq_question">
                    <?= __('faq_q3') ?>
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="faq_answer">
                    <p><?= __('faq_a3') ?></p>
                </div>
            </div>

            <div class="faq_item">
                <button class="faq_question">
                    <?= __('faq_q4') ?>
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="faq_answer">
                    <p><?= __('faq_a4') ?></p>
                </div>
            </div>
        </div>
    </section>

</main>

<?php if (!empty($success)): ?>
    <div class="comment-success-popup"><?= htmlspecialchars($success); ?></div>
<?php endif; ?>

<?php require '../includes/footer.php'; ?>
<script src="../assets/js/main.js"></script>
<script src="../assets/js/contact.js"></script>
</body>
</html>
