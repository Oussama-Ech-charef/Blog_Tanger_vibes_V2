<?php

// Start session with secure cookie settings
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}

// Generate or retrieve CSRF token
function get_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}


// Validate CSRF token
function validate_csrf_token($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}


// Check session timeout (30 min)
function check_session_timeout() {
    $timeout = 1800;

    if (isset($_SESSION['id_user']) && isset($_SESSION['last_activity'])) {
        if (time() - $_SESSION['last_activity'] > $timeout) {
            session_unset();
            session_destroy();
            $base = dirname($_SERVER['SCRIPT_NAME']);
            $redirect = $base ? "$base/index.php" : "index.php";
            header("Location: $redirect");
            exit();
        }
    }

    if (isset($_SESSION['id_user'])) {
        $_SESSION['last_activity'] = time();
    }
}


// Send security headers before any output
function send_security_headers() {
    header("X-Frame-Options: DENY");
    header("X-Content-Type-Options: nosniff");
    header("Referrer-Policy: strict-origin-when-cross-origin");
    header("Permissions-Policy: geolocation=(), camera=(), microphone=()");
    header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://fonts.googleapis.com; font-src 'self' https://cdnjs.cloudflare.com https://fonts.gstatic.com; script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net; img-src 'self' data:; connect-src 'self'; frame-src https://www.openstreetmap.org;");
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
    }
}


// Convert PHP ini size value (e.g. "2M") to bytes
function parse_ini_size($value) {
    $value = trim($value);
    $unit = strtoupper(substr($value, -1));
    $num = (int)$value;
    switch ($unit) {
        case 'G': return $num * 1024 * 1024 * 1024;
        case 'M': return $num * 1024 * 1024;
        case 'K': return $num * 1024;
        default:  return (int)$value;
    }
}

// Format bytes to MB with 1 decimal place
function format_file_size($bytes) {
    return number_format($bytes / (1024 * 1024), 1) . ' MB';
}

// Get effective max upload size (min of target and server limits)
function get_effective_max_upload() {
    $target = 50 * 1024 * 1024;
    $ini_upload = parse_ini_size(ini_get('upload_max_filesize'));
    $ini_post = parse_ini_size(ini_get('post_max_size'));
    return min($target, $ini_upload, $ini_post);
}

// Validate uploaded image file
function validate_uploaded_image($file) {
    $errors = [];
    $max_size = get_effective_max_upload();
    $max_size_display = format_file_size($max_size);

    // Check upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
            $errors[] = sprintf(__('upload_error_size'), $max_size_display);
        } else {
            $errors[] = __('upload_error_failed');
        }
        return $errors;
    }

    // Check file size
    if ($file['size'] > $max_size) {
        $actual = format_file_size($file['size']);
        $errors[] = sprintf(__('upload_error_size'), $max_size_display);
        return $errors;
    }

    // Check MIME type
    $mime = '';
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
    } elseif (function_exists('mime_content_type')) {
        $mime = mime_content_type($file['tmp_name']);
    } elseif (function_exists('getimagesize')) {
        $info = getimagesize($file['tmp_name']);
        $mime = $info['mime'] ?? '';
    }

    $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];

    if (!in_array($mime, $allowed_mimes)) {
        $errors[] = __('upload_error_format');
        return $errors;
    }

    // Check file extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($ext, $allowed_exts)) {
        $errors[] = __('upload_error_extension');
        return $errors;
    }

    return $errors;
}


// Generate secure filename
function generate_secure_filename($extension) {
    return "post_" . date('Ymd') . "_" . bin2hex(random_bytes(6)) . "." . $extension;
}
