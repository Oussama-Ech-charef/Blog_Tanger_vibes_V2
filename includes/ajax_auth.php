<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/lang.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
    exit();
}

$action = $_POST['action'] ?? '';

// --- login ---
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

    $stmt = $conn->prepare("select * from users where email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password'])) {
        echo json_encode(['success' => false, 'error' => __('login_error_credentials')]);
        exit();
    }

    session_regenerate_id(true);
    $_SESSION['id_user'] = $user['id_user'];
    $_SESSION['user_name'] = $user['user_name'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['last_activity'] = time();

    echo json_encode(['success' => true, 'redirect' => '../pages/dashboard.php']);
    exit();
}

// --- register ---
if ($action === 'register') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!validate_csrf_token($csrf_token)) {
        echo json_encode(['success' => false, 'error' => __('register_error_invalid')]);
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

    echo json_encode(['success' => true, 'message' => __('login_success')]);
    exit();
}

http_response_code(400);
echo json_encode(['success' => false, 'error' => 'Invalid action.']);
