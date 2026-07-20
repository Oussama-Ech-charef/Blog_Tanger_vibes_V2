<?php

require_once __DIR__ . '/security.php';
require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/lang.php';

header('Content-Type: application/json');

function auth_redirect_url() {
    $default = '../pages/index.php';
    $redirect = trim($_POST['redirect_url'] ?? '');

    if ($redirect === '') return $default;
    if (preg_match('/[\r\n]/', $redirect)) return $default;
    if (strpos($redirect, '://') !== false || str_starts_with($redirect, '//')) return $default;
    if ($redirect[0] === '/' || str_starts_with($redirect, '../pages/') || str_starts_with($redirect, 'pages/')) {
        return $redirect;
    }

    return $default;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => __('auth_method_not_allowed')]);
    exit();
}

$action = $_POST['action'] ?? '';

//  login 
if ($action === 'login') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!validate_csrf_token($csrf_token)) {
        echo json_encode(['success' => false, 'error' => __('login_error_invalid')]);
        exit();
    }

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'error' => __('auth_modal_error_required')]);
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'error' => __('auth_modal_error_email')]);
        exit();
    }

    // rate limiting — DB-based, survives browser restarts and session clears
    $normalized_email = strtolower($email);
    $max_attempts = 5;
    $lockout_minutes = 10;
    $lockout_datetime = date('Y-m-d H:i:s', strtotime("+{$lockout_minutes} minutes"));
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $max_ip_attempts = 20;
    $ip_lockout_datetime = date('Y-m-d H:i:s', strtotime("+{$lockout_minutes} minutes"));

    // clean up expired locks (locked_until in the past) — resets counter so a
    // single bad attempt after lock expiry doesn't re-lock the account
    $conn->prepare("delete from login_attempts where email = :email and locked_until is not null and locked_until <= now()")
         ->execute([':email' => $normalized_email]);
    $conn->prepare("delete from login_attempts where email = :ip_prefix and locked_until is not null and locked_until <= now()")
         ->execute([':ip_prefix' => 'ip:' . $ip]);

    // check if this email is currently locked — using MySQL NOW() so that
    // the comparison uses MySQL's timezone, avoiding PHP/MySQL timezone mismatch
    $lock_stmt = $conn->prepare("select 1 from login_attempts where email = :email and locked_until > now()");
    $lock_stmt->execute([':email' => $normalized_email]);
    if ($lock_stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => __('login_error_rate_limit')]);
        exit();
    }
    // IP-based check: prevent brute-force across different accounts
    $ip_lock = $conn->prepare("select 1 from login_attempts where email = :ip and locked_until > now()");
    $ip_lock->execute([':ip' => 'ip:' . $ip]);
    if ($ip_lock->fetch()) {
        echo json_encode(['success' => false, 'error' => __('login_error_rate_limit')]);
        exit();
    }

    $stmt = $conn->prepare("select * from users where email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password'])) {
        // record failed attempt in the database (per-email)
        $conn->prepare("
            insert into login_attempts (email, failed_attempts, last_attempt, locked_until)
            values (:email, 1, now(), null)
            on duplicate key update
                failed_attempts = failed_attempts + 1,
                last_attempt = now(),
                locked_until = if(
                    failed_attempts + 1 >= :max,
                    :locked_until,
                    locked_until
                )
        ")->execute([
            ':email' => $normalized_email,
            ':max' => $max_attempts,
            ':locked_until' => $lockout_datetime,
        ]);
        // record failed attempt per-IP (across all accounts)
        $conn->prepare("
            insert into login_attempts (email, failed_attempts, last_attempt, locked_until)
            values (:ip, 1, now(), null)
            on duplicate key update
                failed_attempts = failed_attempts + 1,
                last_attempt = now(),
                locked_until = if(
                    failed_attempts + 1 >= :max,
                    :locked_until,
                    locked_until
                )
        ")->execute([
            ':ip' => 'ip:' . $ip,
            ':max' => $max_ip_attempts,
            ':locked_until' => $ip_lockout_datetime,
        ]);
        echo json_encode(['success' => false, 'error' => __('login_error_credentials')]);
        exit();
    }

    // check if account is active
    if (isset($user['is_active']) && empty($user['is_active'])) {
        echo json_encode(['success' => false, 'error' => __('login_error_deactivated')]);
        exit();
    }

    // clear rate limit on success
    $conn->prepare("delete from login_attempts where email = :email")
         ->execute([':email' => $normalized_email]);
    $conn->prepare("delete from login_attempts where email = :ip")
         ->execute([':ip' => 'ip:' . $ip]);

    session_regenerate_id(true);
    $_SESSION['id_user'] = $user['id_user'];
    $_SESSION['user_name'] = $user['user_name'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['last_activity'] = time();

    echo json_encode(['success' => true, 'redirect' => auth_redirect_url()]);
    exit();
}

//  register 
if ($action === 'register') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!validate_csrf_token($csrf_token)) {
        echo json_encode(['success' => false, 'error' => __('register_error_invalid')]);
        exit();
    }

    // Rate limiting: max 3 registrations per IP per hour
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $ip_hash = 'reg_ip:' . crc32($ip);
    $reg_check = $conn->prepare("SELECT COUNT(*) FROM activity_log WHERE action_type='user_registered' AND description LIKE :ip AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)");
    $reg_check->execute([':ip' => '%[' . $ip_hash . ']%']);
    if ((int)$reg_check->fetchColumn() >= 3) {
        echo json_encode(['success' => false, 'error' => __('register_error_rate_limit')]);
        exit();
    }

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'error' => __('auth_modal_error_required')]);
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'error' => __('auth_modal_error_email')]);
        exit();
    }

    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'error' => __('auth_modal_error_password')]);
        exit();
    }

    if ($password !== $confirm) {
        echo json_encode(['success' => false, 'error' => __('register_error_password')]);
        exit();
    }

    $stmt = $conn->prepare("select id_user from users where email = :email");
    $stmt->execute([':email' => $email]);

    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => __('register_error_exists')]);
        exit();
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("insert into users (user_name, email, password, role) values (:name, :email, :password, 'user')");
    $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':password' => $hashed,
    ]);

    // log activity
    $new_id = $conn->lastInsertId();
    try {
        $log = $conn->prepare("insert into activity_log (action_type, description, user_id, entity_type, entity_id) values ('user_registered', :desc, :uid, 'user', :eid)");
        $log->execute([':desc' => "New user registered: $name [$ip_hash]", ':uid' => $new_id, ':eid' => $new_id]);
    } catch (PDOException $e) {
        error_log("Activity log error: " . $e->getMessage());
    }

    session_regenerate_id(true);
    $_SESSION['id_user'] = $new_id;
    $_SESSION['user_name'] = $name;
    $_SESSION['role'] = 'user';
    $_SESSION['last_activity'] = time();

    echo json_encode(['success' => true, 'redirect' => auth_redirect_url()]);
    exit();
}

http_response_code(400);
echo json_encode(['success' => false, 'error' => __('auth_invalid_action')]);
