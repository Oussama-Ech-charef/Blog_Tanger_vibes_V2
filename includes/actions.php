<?php

session_start();
require '../config/connection.php';
require_once __DIR__ . '/security.php';

// check login
if (!isset($_SESSION['id_user'])) {
    header("Location: ../pages/index.php");
    exit();
}

// check admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../pages/dashboard.php");
    exit();
}

// validate CSRF token
$csrf_token = $_GET['csrf_token'] ?? '';
if (!validate_csrf_token($csrf_token)) {
    header("Location: ../pages/dashboard.php?error=invalid_request");
    exit();
}

// check action and post id
if (!isset($_GET['action']) || !isset($_GET['id'])) {
    header("Location: ../pages/dashboard.php");
    exit();
}

$action = $_GET['action'];
$post_id = $_GET['id'];
$id_user = $_SESSION['id_user'];

// approve post
if ($action === 'approve') {
    $stmt = $conn->prepare("
        update posts
        set status = 'published',
            id_approved_by = :id_approved_by,
            approved_at = now(),
            rejection_reason = null
        where id_post = :id_post and status = 'pending'
    ");
    $stmt->execute([
        ':id_approved_by' => $id_user,
        ':id_post' => $post_id
    ]);
}

header("Location: ../pages/dashboard.php");
exit();
