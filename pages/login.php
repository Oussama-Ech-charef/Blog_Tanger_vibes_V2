
<?php

session_start();

if(isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}




require '../config/db_connection.php';
require '../includes/users.php';


$database = new Database();
$db = $database->getConnection();

$user = new User($db);


$error = '';


if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $loggedInUser = $user->login($email, $password);

    if ($loggedInUser) {
        $_SESSION['user_id']   = $loggedInUser['id'];
        $_SESSION['full_name']   = $loggedInUser['full_name'];
        $_SESSION['role']   = $loggedInUser['role'];



        header("Location: ../index.php");
        exit();
    }else{
        $error = "Invalid email or password.";
    }



}

?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Tangier Vibes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/register_login.css">
</head>
<body>
    <?php require '../includes/header.php'; ?>

    <div class="auth_container">
        <div class="auth_card">
            <div class="auth_header">
                <h1 class="auth_title">Welcome Back</h1>
                <p class="auth_subtitle">Sign in to continue exploring Tangier.</p>
            </div>

         
           <?php if($error): ?>
                <div class="auth_error">
                    <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
                </div>
           <?php endif; ?>
          

            <form action="login.php" method="POST" class="auth_form">
                <div class="auth_input_group">
                    <input type="email" name="email" class="auth_input" placeholder="Email Address" required>
                    <i class="fa-regular fa-envelope"></i>
                </div>

                <div class="auth_input_group">
                    <input type="password" name="password" class="auth_input" placeholder="Password" required>
                    <i class="fa-solid fa-lock"></i>
                </div>

                <button type="submit" class="auth_btn">Sign In</button>
            </form>

            <div class="auth_footer">
                Don't have an account yet? <a href="register.php">Sign up</a>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>
