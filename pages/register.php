<?php

session_start();

require '../config/connection.php';
require_once '../includes/security.php';

send_security_headers();

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // validate CSRF token
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!validate_csrf_token($csrf_token)) {
        $error = "Invalid request. Please try again.";
    }

    // get form values
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // validation
    if (empty($error)) {
        if (empty($name) || empty($email) || empty($password)) {

            $error = "All fields are required.";

        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $error = "Invalid email format.";

        } elseif (strlen($password) < 6) {

            $error = "Password must be at least 6 characters.";

        } else {

            // check email exists
            $stmt = $conn->prepare("select id_user from users where email = :email");
            $stmt->execute([
                ':email' => $email
            ]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $error = "Email already exists.";
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Tangier Vibes</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>

    <main class="login_and_register">
        
        <!-- register card -->
        <section class="card">

            <a href="index.php" class="logo">
                <i class="fa-solid fa-compass"></i>
                Tangier <span>Vibes</span>
            </a>

            <h1>Register</h1>

            <p>Create your Tangier Vibes account.</p>

                    <!-- error message -->
                    <?php if(!empty($error)): ?>
                        <p class="error_message"><?= $error; ?></p>
                    <?php endif; ?>


            <!-- register form -->
            <form action="#" method="POST">
                <input type="hidden" name="csrf_token" value="<?= get_csrf_token(); ?>">
                <label for="name">Full name</label>
                <input type="text" id="name" name="name" placeholder="Your name" value="<?= htmlspecialchars($name ?? '') ?>" required>

                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="you@example.com" value="<?= htmlspecialchars($email ?? '') ?>" required>

                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Create password" required>

                <button type="submit" class="btn">Create account</button>
            </form>

            <p class="switch">Already have an account? <a href="login.php">Login</a></p>

        </section>
    </main>

</body>
</html>
