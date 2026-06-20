<?php
// ============================================================
// User Dashboard Initialization
// Include at the top of every user_dashboard page
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

// Must be logged in
if (!isset($_SESSION['id_user'])) {
    header('Location: ../pages/index.php');
    exit();
}

// Check account is still active and get role
$s = $conn->prepare("SELECT is_active, role FROM users WHERE id_user=:id");
$s->execute([':id' => $_SESSION['id_user']]);
$user_data = $s->fetch(PDO::FETCH_ASSOC);
if (!$user_data || !$user_data['is_active']) {
    session_unset();
    session_destroy();
    header('Location: ../pages/index.php');
    exit();
}

// Admins use the admin dashboard, not the user dashboard
if ($user_data['role'] === 'admin') {
    header('Location: ../dashboard/index.php');
    exit();
}

$csrf_token = get_csrf_token();
$uid = (int)$_SESSION['id_user'];

function get_unread_notification_count($conn, $user_id) {
    try {
        $s = $conn->prepare("SELECT COUNT(*) FROM user_notifications WHERE id_user=:id AND is_read=0");
        $s->execute([':id' => $user_id]);
        return (int)$s->fetchColumn();
    } catch (PDOException $e) {
        return 0;
    }
}
