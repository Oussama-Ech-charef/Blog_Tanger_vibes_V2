<?php

require_once '../includes/security.php';

send_security_headers();

// Require POST method with CSRF validation
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

$csrf_token = $_POST['csrf_token'] ?? '';
if (!validate_csrf_token($csrf_token)) {
    header("Location: index.php?error=invalid_request");
    exit();
}

// Clear session
session_unset();

session_destroy();

// Clear session cookie from browser
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// go home
header("Location: index.php");
exit();
