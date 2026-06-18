<?php

session_start();

require '../config/connection.php';
require_once '../includes/security.php';
require_once '../includes/lang.php';

send_security_headers();

$error = "";

// success message from register
$success = $_SESSION['success'] ?? "";
$success = $success === "Account created successfully." ? __('login_success') : $success;

unset($_SESSION['success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // validate CSRF token
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!validate_csrf_token($csrf_token)) {
        $error = __('login_error_invalid');
    }

    // get form values
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // validation
    if (empty($error)) {
        if (empty($email) || empty($password)) {

            $error = __('login_error_required');

        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $error = __('login_error_email');

        } else {

            // get user
            $stmt = $conn->prepare("select * from users where email = :email");
            $stmt->execute([
                ':email' => $email
            ]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // check password
            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);

                // create session
                $_SESSION['id_user'] = $user['id_user'];
                $_SESSION['user_name'] = $user['user_name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['last_activity'] = time();

                header("Location: dashboard.php");
                exit();
            } else {
                $error = __('login_error_credentials');
            }
        }
    }
}



?>









<!DOCTYPE html>
<html lang="<?= get_lang_code() ?>" dir="<?= get_lang_dir() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Tangier Vibes</title>
    <meta name="description" content="Log in to your Tangier Vibes account to manage posts and explore Tangier.">
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link rel="apple-touch-icon" href="../assets/images/logo.png">
    <meta property="og:title" content="Login - Tangier Vibes">
    <meta property="og:description" content="Log in to your Tangier Vibes account to manage posts and explore Tangier.">
    <meta property="og:image" content="../assets/images/logo.png">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/rtl.css">
</head>
<body>

    <main class="login_and_register">

        <!-- login card -->
        <section class="card">

            <a href="index.php" class="logo">
                <img src="../assets/images/logo.png" alt="Tangier Vibes Logo" class="logo_img" style="height:50px;width:auto;">
            </a>

            <h1><?= __('login_title') ?></h1>

            <p><?= __('login_subtitle') ?></p>

                    <!-- messages -->
                    <?php if (!empty($success)): ?>
                        <p class="success_message"><?= $success; ?></p>
                    <?php endif; ?>


                    <?php if (!empty($error)): ?>
                        <p class="error_message"><?= $error; ?></p>
                    <?php endif; ?>


            <!-- login form -->
            <form action="#" method="POST">
                <input type="hidden" name="csrf_token" value="<?= get_csrf_token(); ?>">
                <label for="email"><?= __('login_email_label') ?></label>
                <input type="email" id="email" name="email" placeholder="<?= __('login_email_placeholder') ?>" value="<?= htmlspecialchars($email ?? '') ?>" required>

                <label for="password"><?= __('login_password_label') ?></label>
                <input type="password" id="password" name="password" placeholder="<?= __('login_password_placeholder') ?>" required>

                <div class="options">
                    <label>
                        <input type="checkbox" name="remember">
                        <?= __('login_remember') ?>
                    </label>
                    <a href="#"><?= __('login_forgot') ?></a>
                </div>

                <button type="submit" class="btn"><?= __('login_btn') ?></button>
            </form>


            <p class="switch"><?= __('login_switch') ?> <a href="register.php"><?= __('login_switch_link') ?></a></p>

        </section>

    </main>

</body>
</html>
