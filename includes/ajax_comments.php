<?php

require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/lang.php';
require_once __DIR__ . '/../includes/helpers.php';

send_security_headers();
header('Content-Type: application/json; charset=utf-8');

// Only allow GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Validate post id
$post_id = isset($_GET['id']) && ctype_digit((string)$_GET['id']) ? (int)$_GET['id'] : 0;
if ($post_id < 1) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid post id']);
    exit;
}

// Verify the post exists and is published
$check = $conn->prepare("select 1 from posts where id_post = :id and status = :st");
$check->execute([':id' => $post_id, ':st' => STATUS_PUBLISHED]);
if (!$check->fetchColumn()) {
    http_response_code(404);
    echo json_encode(['error' => 'Post not found']);
    exit;
}

// Pagination params
$per_page = 10;
$page = isset($_GET['p']) && ctype_digit((string)$_GET['p']) ? max(1, (int)$_GET['p']) : 1;
$offset = ($page - 1) * $per_page;

// Fetch the approved comments for this page
$stmt = $conn->prepare("
    select id_comment, author_name, comment_text, created_at
    from comments
    where id_post = :id_post and status = 'approved'
    order by created_at desc
    limit :lim offset :off
");
$stmt->bindValue(':id_post', $post_id, PDO::PARAM_INT);
$stmt->bindValue(':lim', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':off', $offset, PDO::PARAM_INT);
$stmt->execute();
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count total
$cnt = $conn->prepare("select count(*) from comments where id_post = :id and status = 'approved'");
$cnt->execute([':id' => $post_id]);
$total = (int)$cnt->fetchColumn();

$loaded_so_far = $offset + count($comments);
$has_more = $loaded_so_far < $total;

// Render the comment items as HTML
$html = '';
$date_fmt = __('date_format_detail');
foreach ($comments as $c) {
    $initials = htmlspecialchars(avatar_initials($c['author_name']));
    $name = htmlspecialchars($c['author_name']);
    $date = date($date_fmt, strtotime($c['created_at']));
    $text = htmlspecialchars($c['comment_text']);
    $html .= '<div class="post_comment_item">';
    $html .= '<div class="post_comment_header">';
    $html .= '<span class="post_comment_author">';
    $html .= '<span class="comment_avatar">' . $initials . '</span>';
    $html .= '<span class="comment_name">' . $name . '</span>';
    $html .= '</span>';
    $html .= '<span class="comment_date"><i class="fa-regular fa-clock" aria-hidden="true"></i> ' . $date . '</span>';
    $html .= '</div>';
    $html .= '<div class="post_comment_text">' . $text . '</div>';
    $html .= '</div>';
}

echo json_encode([
    'html' => $html,
    'has_more' => $has_more,
    'loaded' => $loaded_so_far,
    'total' => $total,
    'page' => $page
]);
