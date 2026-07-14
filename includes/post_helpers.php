<?php

require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/security.php';

// Validate post input fields
function validate_post_input($title, $cat_id, $content) {
    $errors = [];
    if (empty($title)) $errors[] = __('post_error_title_required');
    if ((int)$cat_id <= 0) $errors[] = __('post_error_category_required');
    if (trim(strip_tags((string)$content)) === '') $errors[] = __('post_error_content_required');
    return $errors;
}

// Process uploaded post image
function process_post_image($existing_path = null) {
    $result = ['path' => null, 'errors' => []];

    if (empty($_FILES['image']['name']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
        return $result;
    }

    $ups = validate_uploaded_image($_FILES['image']);
    if (!empty($ups)) {
        $result['errors'] = $ups;
        return $result;
    }

    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $fn = generate_secure_filename($ext);
    $dest = __DIR__ . '/../assets/uploads/' . $fn;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
        $result['path'] = 'assets/uploads/' . $fn;
    } else {
        $result['errors'][] = __('post_error_upload_failed');
    }

    return $result;
}

// Handle image removal request
function handle_image_removal($current_path, $remove_requested) {
    if ($remove_requested) {
        return null;
    }
    return $current_path;
}

// Insert a new post into database
function insert_post($conn, $cat_id, $user_id, $title, $image, $content, $status, $is_admin) {
    $s = $conn->prepare("INSERT INTO posts (id_category, id_user, title, image, content, status) VALUES (:c, :u, :t, :i, :co, :st)");
    $s->execute([':c' => (int)$cat_id, ':u' => (int)$user_id, ':t' => $title, ':i' => $image, ':co' => $content, ':st' => $status]);
    $nid = (int)$conn->lastInsertId();

    if ($is_admin) {
        log_post_activity($conn, 'post_created', "Created post: $title", $user_id, $nid);
    } else {
        $action_type = $status === STATUS_DRAFT ? 'draft_saved' : 'post_created';
        $action_desc = $status === STATUS_DRAFT ? 'Draft saved' : 'Post submitted';
        log_post_activity($conn, $action_type, "$action_desc: $title", $user_id, $nid);
    }

    // Flush page cache
    if (class_exists('PageCache')) PageCache::flush();

    return $nid;
}

// Update an existing post
function update_post($conn, $post_id, $cat_id, $title, $image, $content, $status, $clear_reason, $user_id, $is_admin) {
    $sql = "UPDATE posts SET id_category=:c, title=:t, image=:i, content=:co, status=:st";
    if ($clear_reason) {
        $sql .= ", rejection_reason=NULL";
    }
    $sql .= " WHERE id_post=:id";
    if (!$is_admin) {
        $sql .= " AND id_user=:uid";
    }

    $params = [':c' => (int)$cat_id, ':t' => $title, ':i' => $image, ':co' => $content, ':st' => $status, ':id' => (int)$post_id];
    if (!$is_admin) {
        $params[':uid'] = (int)$user_id;
    }

    $conn->prepare($sql)->execute($params);
}

// Log post-related activity
function log_post_activity($conn, $type, $description, $user_id, $post_id) {
    $s = $conn->prepare("INSERT INTO activity_log (action_type, description, user_id, entity_type, entity_id) VALUES (:at, :d, :u, 'post', :e)");
    $s->execute([':at' => $type, ':d' => $description, ':u' => (int)$user_id, ':e' => (int)$post_id]);
}

// Fetch posts with filtering criteria
function fetch_posts($conn, $criteria = []) {
    $defaults = [
        'where' => ['1=1'],
        'params' => [],
        'order' => 'posts.created_at DESC',
        'limit' => null,
        'offset' => null,
        'extra_select' => '',
        'joins' => ''
    ];
    $c = array_merge($defaults, $criteria);

    $select = "posts.*, categories.cat_name, users.user_name" . $c['extra_select'];
    $joins = $c['joins'] ?: "INNER JOIN categories ON posts.id_category = categories.id_category INNER JOIN users ON posts.id_user = users.id_user";
    $where = implode(' AND ', $c['where']);

    $sql = "SELECT $select FROM posts $joins WHERE $where ORDER BY {$c['order']}";
    if ($c['limit'] !== null) {
        $sql .= " LIMIT " . (int)$c['limit'];
    }
    if ($c['offset'] !== null) {
        $sql .= " OFFSET " . (int)$c['offset'];
    }

    $s = $conn->prepare($sql);
    $s->execute($c['params']);
    return $s->fetchAll(PDO::FETCH_ASSOC);
}

