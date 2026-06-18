<?php

session_start();

require '../config/connection.php';
require_once '../includes/security.php';
require_once '../includes/lang.php';

send_security_headers();

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // validate CSRF token
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!validate_csrf_token($csrf_token)) {
        $error = __('register_error_invalid');
    }

    // get form values
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // validation
    if (empty($error)) {
        if (empty($name) || empty($email) || empty($password)) {

            $error = __('register_error_required');

        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $error = __('register_error_email');

        } elseif (strlen($password) < 6) {

            $error = __('register_error_password');

        } else {

            // check email exists
            $stmt = $conn->prepare("select id_user from users where email = :email");
            $stmt->execute([
                ':email' => $email
            ]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $error = __('register_error_exists');
            } else {
                // hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // add user
                $stmt = $conn->prepare("
                    insert into users (user_name, email, password, role)
                    values (:user_name, :email, :password, 'user')
                ");

                $stmt->execute([
                    ':user_name' => $name,
                    ':email' => $email,
                    ':password' => $hashed_password
                ]);

                $_SESSION['success'] = "Account created successfully.";
                header("Location: login.php");
                exit();
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
    <title>Register - Tangier Vibes</title>
    <meta name="description" content="Create your Tangier Vibes account and start sharing places, experiences, and hidden gems of Tangier.">
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link rel="apple-touch-icon" href="../assets/images/logo.png">
    <meta property="og:title" content="Register - Tangier Vibes">
    <meta property="og:description" content="Create your Tangier Vibes account and start sharing places, experiences, and hidden gems of Tangier.">
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
        
        <!-- register card -->
        <section class="card">

            <a href="index.php" class="logo">
                <img src="../assets/images/logo.png" alt="Tangier Vibes Logo" class="logo_img" style="height:50px;width:auto;">
            </a>

            <h1><?= __('register_title') ?></h1>

            <p><?= __('register_subtitle') ?></p>

                    <!-- error message -->
                    <?php if(!empty($error)): ?>
                        <p class="error_message"><?= $error; ?></p>
                    <?php endif; ?>


            <!-- register form -->
            <form action="#" method="POST">
                <input type="hidden" name="csrf_token" value="<?= get_csrf_token(); ?>">
                <label for="name"><?= __('register_name_label') ?></label>
                <input type="text" id="name" name="name" placeholder="<?= __('register_name_placeholder') ?>" value="<?= htmlspecialchars($name ?? '') ?>" required>

                <label for="email"><?= __('register_email_label') ?></label>
                <input type="email" id="email" name="email" placeholder="<?= __('register_email_placeholder') ?>" value="<?= htmlspecialchars($email ?? '') ?>" required>

                <label for="password"><?= __('register_password_label') ?></label>
                <input type="password" id="password" name="password" placeholder="<?= __('register_password_placeholder') ?>" required>

                <button type="submit" class="btn"><?= __('register_btn') ?></button>
            </form>

            <p class="switch"><?= __('register_switch') ?> <a href="login.php"><?= __('register_switch_link') ?></a></p>

        </section>
    </main>

</body>
</html>
