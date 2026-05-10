
<?php

session_start();



if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}


require_once '../config/db_connection.php';
require_once '../includes/users.php';



$database = new Database();
$db = $database->getConnection();

$user = new User($db);


$error = '';



if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email     = trim($_POST['email']);
    $password  = $_POST['password'];

   
    $result = $user->register($full_name, $email, $password);

    if ($result['status']) {
        $_SESSION['success_message'] = "Registration successful! Please login.";
        header("Location: login.php");
        exit();
    } else {
        $error = $result['message']; 
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
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <link rel="stylesheet" href="../assets/css/register_login.css">
</head>
<body>
    <?php require '../includes/header.php'; ?>

    <div class="auth_container">
        <div class="auth_card">
            <div class="auth_header">
                <h1 class="auth_title">Create Account</h1>
                <p class="auth_subtitle">Join us to explore Tangier.</p>
            </div>

            
                <?php if($error): ?>
                    <div class="auth_error">
                        <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
            

            <form action="register.php" method="POST" class="auth_form">
                <div class="auth_input_group">
                    <input type="text" name="full_name" class="auth_input" placeholder="Full Name" required>
                    <i class="fa-regular fa-user"></i>
                </div>
                
                <div class="auth_input_group">
                    <input type="email" name="email" class="auth_input" placeholder="Email Address" required>
                    <i class="fa-regular fa-envelope"></i>
                </div>

                <div class="auth_input_group">
                    <input type="password" name="password" class="auth_input" placeholder="Password" required>
                    <i class="fa-solid fa-lock"></i>
                </div>

                <button type="submit" class="auth_btn">Register Now</button>
            </form>

            <div class="auth_footer">
                <p>Already have an account? <a href="login.php">Login</a></p>
            </div>
        </div>
    </div>

    <?php require '../includes/footer.php'; ?>
</body>
</html>
