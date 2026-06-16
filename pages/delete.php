<?php

session_start();
require '../config/connection.php';
require_once '../includes/security.php';

// check login
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit();
}

// validate CSRF token
$csrf_token = $_GET['csrf_token'] ?? '';
if (!validate_csrf_token($csrf_token)) {
    header("Location: dashboard.php?error=invalid_request");
    exit();
}

// check post id
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$post_id = $_GET['id'];
$id_user = $_SESSION['id_user'];
$role = $_SESSION['role'];

// delete post
if ($role === 'admin') {
    $stmt = $conn->prepare("delete from posts where id_post = :id_post");
    $stmt->execute([
        ':id_post' => $post_id
    ]);
} else {
    $stmt = $conn->prepare("delete from posts where id_post = :id_post and id_user = :id_user");
    $stmt->execute([
        ':id_post' => $post_id,
        ':id_user' => $id_user
    ]);
}

header("Location: dashboard.php");
exit();
