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
            header("Location: index.php");
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


// convert PHP ini size value (e.g. "2M", "50M") to bytes
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

// format bytes to MB with 1 decimal place
function format_file_size($bytes) {
    return number_format($bytes / (1024 * 1024), 1) . ' MB';
}

// get effective max upload size (min of target 50MB and actual server limits)
function get_effective_max_upload() {
    $target = 50 * 1024 * 1024;
    $ini_upload = parse_ini_size(ini_get('upload_max_filesize'));
    $ini_post = parse_ini_size(ini_get('post_max_size'));
    return min($target, $ini_upload, $ini_post);
}

// validate uploaded image file
function validate_uploaded_image($file) {
    $errors = [];
    $max_size = get_effective_max_upload();
    $max_size_display = format_file_size($max_size);

    // check upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
            $errors[] = "Selected image size exceeds the maximum allowed limit of $max_size_display.";
        } else {
            $errors[] = "Image upload failed. Please try again.";
        }
        return $errors;
    }

    // check file size
    if ($file['size'] > $max_size) {
        $actual = format_file_size($file['size']);
        $errors[] = "Selected image size: $actual. Maximum allowed size: $max_size_display.";
        return $errors;
    }

    // check MIME type using getimagesize (works without fileinfo extension)
    $image_data = @getimagesize($file['tmp_name']);

    if ($image_data === false) {
        $errors[] = "Unsupported image format.";
        return $errors;
    }

    $mime = $image_data['mime'];
    $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];

    if (!in_array($mime, $allowed_mimes)) {
        $errors[] = "Unsupported image format.";
        return $errors;
    }

    // check extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($ext, $allowed_exts)) {
        $errors[] = "Unsupported image format.";
        return $errors;
    }

    return $errors;
}


// generate secure filename
function generate_secure_filename($extension) {
    return "post_" . date('Ymd') . "_" . bin2hex(random_bytes(6)) . "." . $extension;
}
