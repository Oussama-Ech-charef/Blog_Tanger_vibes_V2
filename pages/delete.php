<?php

session_start();
require '../config/connection.php';
require_once '../includes/security.php';

// check login
if (!isset($_SESSION['id_user'])) {
    header("Location: index.php");
    exit();
}

// POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dashboard.php");
    exit();
}

// validate CSRF token
$csrf_token = $_POST['csrf_token'] ?? '';
if (!validate_csrf_token($csrf_token)) {
    header("Location: dashboard.php?error=invalid_request");
    exit();
}

// check post id
$post_id = $_POST['id'] ?? '';
if (empty($post_id)) {
    header("Location: dashboard.php");
    exit();
}

$id_user = $_SESSION['id_user'];
$role = $_SESSION['role'];

// get image path before deleting
$img_stmt = $conn->prepare("select image from posts where id_post = :id_post");
$img_stmt->execute([':id_post' => $post_id]);
$post_data = $img_stmt->fetch(PDO::FETCH_ASSOC);

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

// delete image file if it exists
if ($post_data && !empty($post_data['image'])) {
    $image_path = __DIR__ . '/../' . $post_data['image'];
    if (file_exists($image_path)) {
        unlink($image_path);
    }
}

header("Location: dashboard.php");
exit();
