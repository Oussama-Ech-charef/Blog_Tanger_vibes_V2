<?php

session_start();
require_once '../includes/security.php';

send_security_headers();

// clear session
session_unset();

session_destroy();

// clear session cookie
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
