<?php


// ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// generate or retrieve CSRF token
function get_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}


// validate CSRF token
function validate_csrf_token($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}


// check session timeout (30 min)
function check_session_timeout() {
    $timeout = 1800;

    if (isset($_SESSION['id_user']) && isset($_SESSION['last_activity'])) {
        if (time() - $_SESSION['last_activity'] > $timeout) {
            session_unset();
            session_destroy();
            header("Location: login.php?timeout=1");
            exit();
        }
    }

    if (isset($_SESSION['id_user'])) {
        $_SESSION['last_activity'] = time();
    }
}


// send security headers (call before any HTML output)
function send_security_headers() {
    header("X-Frame-Options: DENY");
    header("X-Content-Type-Options: nosniff");
    header("Referrer-Policy: strict-origin-when-cross-origin");
    header("Permissions-Policy: geolocation=(), camera=(), microphone=()");
}


// validate uploaded image file
function validate_uploaded_image($file) {
    $errors = [];

    // check upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Image upload failed.";
        return $errors;
    }

    // check file size (max 5 MB)
    $max_size = 5 * 1024 * 1024;
    if ($file['size'] > $max_size) {
        $errors[] = "Image must be less than 5 MB.";
        return $errors;
    }

    // check MIME type using getimagesize (works without fileinfo extension)
    $image_data = @getimagesize($file['tmp_name']);

    if ($image_data === false) {
        $errors[] = "File is not a valid image.";
        return $errors;
    }

    $mime = $image_data['mime'];
    $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];

    if (!in_array($mime, $allowed_mimes)) {
        $errors[] = "Only JPG, PNG, and WebP images are allowed.";
        return $errors;
    }

    // check extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($ext, $allowed_exts)) {
        $errors[] = "Invalid file extension.";
        return $errors;
    }

    return $errors;
}


// generate secure filename
function generate_secure_filename($extension) {
    return "post_" . date('Ymd') . "_" . bin2hex(random_bytes(6)) . "." . $extension;
}
