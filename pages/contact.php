<?php

require_once '../config/connection.php';
require_once '../includes/security.php';
require_once '../includes/lang.php';
require_once '../includes/helpers.php';

send_security_headers();

$success = "";
$error = "";

if (isset($_SESSION['contact_submitted'])) {
    $success = $_SESSION['contact_submitted'];
    unset($_SESSION['contact_submitted']);
}

// Preserve form values for repopulation
$form_name    = '';
$form_email   = '';
$form_subject = '';
$form_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validate CSRF token
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!validate_csrf_token($csrf_token)) {
        $error = __('contact_error_invalid');
    }

    $form_name    = trim($_POST['name'] ?? '');
    $form_email   = trim($_POST['email'] ?? '');
    $form_subject = trim($_POST['subject'] ?? '');
    $form_message = trim($_POST['message'] ?? '');

    // Validate form fields
    if (empty($error)) {
        if (empty($form_name) || empty($form_email) || empty($form_subject) || empty($form_message)) {
            $error = __('contact_error_required');
        } elseif (!filter_var($form_email, FILTER_VALIDATE_EMAIL)) {
            $error = __('contact_error_email');
        }
    }

    if (empty($error)) {
        try {
            // Insert message into database
            $stmt = $conn->prepare("
                INSERT INTO contact_messages (full_name, email, subject, message)
                VALUES (:full_name, :email, :subject, :message)
            ");
            $stmt->execute([
                ':full_name' => $form_name,
                ':email'     => $form_email,
                ':subject'   => $form_subject,
                ':message'   => $form_message
            ]);

            // Log activity for admins
            try {
                $new_id = $conn->lastInsertId();
                $log = $conn->prepare("INSERT INTO activity_log (action_type, description, user_id, entity_type, entity_id) VALUES ('message_received', :desc, null, 'message', :eid)");
                $log->execute([':desc' => "New message from {$form_name}: {$form_subject}", ':eid' => $new_id]);
            } catch (PDOException $e) {
                error_log("Activity log error: " . $e->getMessage());
            }

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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap">
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

<main class="contact_page" id="main_content">

    <!-- success notification (inside body, before content) -->
    <?php if (!empty($success)): ?>
        <?php render_notification($success, 'success'); ?>
    <?php endif; ?>

    <!-- hero -->
    <section class="contact_head motion-reveal">
        <span class="contact_label">
            <i class="fa-solid fa-envelope" aria-hidden="true"></i>
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
            <div class="info_card motion-reveal">
                <div class="info_icon"><i class="fa-solid fa-envelope" aria-hidden="true"></i></div>
                <h3><?= __('contact_email_title') ?></h3>
                <p><a href="mailto:contact@tangiervibes.com">contact@tangiervibes.com</a></p>
            </div>

            <div class="info_card motion-reveal">
                <div class="info_icon"><i class="fa-solid fa-phone" aria-hidden="true"></i></div>
                <h3><?= __('contact_phone_title') ?></h3>
                <p>+212 600 000 000</p>
            </div>

            <div class="info_card motion-reveal">
                <div class="info_icon"><i class="fa-solid fa-location-dot" aria-hidden="true"></i></div>
                <h3><?= __('contact_address_title') ?></h3>
                <p>Tangier, Morocco</p>
            </div>
        </div>
    </section>

    <!-- form + map layout -->
    <section class="contact_section">
        <h2 class="section_title motion-reveal"><?= __('contact_form_title') ?></h2>
        <p class="section_desc motion-reveal">
            <?= __('contact_form_desc') ?>
        </p>

        <?php if (!empty($error)): ?>
            <?php render_notification($error, 'error'); ?>
        <?php endif; ?>

        <div class="contact_layout">
            <div class="contact_form_box motion-reveal">
                <form action="contact.php" method="POST" class="contact_form">
                    <input type="hidden" name="csrf_token" value="<?= get_csrf_token(); ?>">

                    <div class="form_group">
                        <label for="contact_name"><?= __('contact_form_name_label') ?></label>
                        <input type="text" id="contact_name" name="name" placeholder="<?= __('contact_form_name_placeholder') ?>" value="<?= htmlspecialchars($form_name) ?>" required>
                    </div>

                    <div class="form_group">
                        <label for="contact_email"><?= __('contact_form_email_label') ?></label>
                        <input type="email" id="contact_email" name="email" placeholder="<?= __('contact_form_email_placeholder') ?>" value="<?= htmlspecialchars($form_email) ?>" required>
                    </div>

                    <div class="form_group">
                        <label for="contact_subject"><?= __('contact_form_subject_label') ?></label>
                        <input type="text" id="contact_subject" name="subject" placeholder="<?= __('contact_form_subject_placeholder') ?>" value="<?= htmlspecialchars($form_subject) ?>" required>
                    </div>

                    <div class="form_group">
                        <label for="contact_message"><?= __('contact_form_message_label') ?></label>
                        <textarea id="contact_message" name="message" placeholder="<?= __('contact_form_message_placeholder') ?>" required><?= htmlspecialchars($form_message); ?></textarea>
                    </div>

                    <button type="submit">
                        <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                        <?= __('contact_form_btn') ?>
                    </button>
                </form>
            </div>

            <!-- Map: using OpenStreetMap to comply with CSP (frame-src allows openstreetmap.org) -->
            <div class="map_box">
                <iframe
                    src="https://www.openstreetmap.org/export/embed.html?bbox=-5.87,35.74,-5.80,35.78&amp;layer=mapnik&amp;marker=35.7595,-5.8340"
                    width="100%"
                    height="400"
                    class="border_0"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    title="Tangier location map">
                </iframe>
            </div>
        </div>
    </section>

    <!-- faq -->
    <section class="contact_section">
        <h2 class="section_title motion-reveal"><?= __('faq_title') ?></h2>
        <p class="section_desc motion-reveal">
            <?= __('faq_desc') ?>
        </p>

        <div class="faq_list">
            <div class="faq_item motion-reveal">
                <button class="faq_question">
                    <?= __('faq_q1') ?>
                    <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                </button>
                <div class="faq_answer">
                    <p><?= __('faq_a1') ?></p>
                </div>
            </div>

            <div class="faq_item motion-reveal">
                <button class="faq_question">
                    <?= __('faq_q2') ?>
                    <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                </button>
                <div class="faq_answer">
                    <p><?= __('faq_a2') ?></p>
                </div>
            </div>

            <div class="faq_item motion-reveal">
                <button class="faq_question">
                    <?= __('faq_q3') ?>
                    <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                </button>
                <div class="faq_answer">
                    <p><?= __('faq_a3') ?></p>
                </div>
            </div>

            <div class="faq_item motion-reveal">
                <button class="faq_question">
                    <?= __('faq_q4') ?>
                    <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                </button>
                <div class="faq_answer">
                    <p><?= __('faq_a4') ?></p>
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
