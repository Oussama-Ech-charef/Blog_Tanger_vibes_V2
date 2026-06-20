<?php
// ============================================================
// Dashboard Initialization
// Include at the top of every dashboard page
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/lang.php';
require_once __DIR__ . '/../includes/pagination.php';
require_once __DIR__ . '/../includes/helpers.php';

send_security_headers();

// Admin check
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../pages/index.php');
    exit();
}

// Check admin account is still active
$s = $conn->prepare("SELECT is_active FROM users WHERE id_user=:id");
$s->execute([':id' => $_SESSION['id_user']]);
$active = (int)$s->fetchColumn();
if (!$active) {
    session_unset();
    session_destroy();
    header('Location: ../pages/index.php');
    exit();
}

$csrf_token = get_csrf_token();
