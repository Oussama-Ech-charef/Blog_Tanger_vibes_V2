<?php

// Load required dependencies (security.php handles session start with secure params)
require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/lang.php';
require_once __DIR__ . '/../includes/pagination.php';
require_once __DIR__ . '/../includes/helpers.php';

check_session_timeout();
send_security_headers();

// Require authenticated user
if (!isset($_SESSION['id_user'])) {
    header('Location: ../pages/index.php?login=1');
    exit();
}

// Check if account is still active
$s = $conn->prepare("SELECT is_active FROM users WHERE id_user=:id");
$s->execute([':id' => $_SESSION['id_user']]);
$active = (int)$s->fetchColumn();
if (!$active) {
    session_unset();
    session_destroy();
    header('Location: ../pages/index.php');
    exit();
}

// Role helpers
$is_admin = ($_SESSION['role'] ?? '') === 'admin';

function current_user_id(): int {
    return (int)($_SESSION['id_user'] ?? 0);
}

function require_admin(): void {
    if (($_SESSION['role'] ?? '') !== 'admin') {
        http_response_code(403);
        $GLOBALS['page_title'] = __('forbidden_title');
        require __DIR__ . '/inc/header.php';
        echo '<div style="text-align:center;padding:80px 20px;"><h1>' . __('forbidden_title') . '</h1><p>' . __('dashboard_forbidden') . '</p><a href="index.php" class="btn btn_primary" style="display:inline-block;margin-top:16px;">' . __('sidebar_overview') . '</a></div>';
        require __DIR__ . '/inc/footer.php';
        exit();
    }
}

function require_login(): void {
    if (!isset($_SESSION['id_user'])) {
        header('Location: ../pages/index.php?login=1');
        exit();
    }
}

$csrf_token = get_csrf_token();
