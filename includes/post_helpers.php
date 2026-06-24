<?php

require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/security.php';

/**
 * @param string $title
 * @param int|string $cat_id
 * @param string $content
 * @return array
 */
function validate_post_input($title, $cat_id, $content) {
    $errors = [];
    if (empty($title)) $errors[] = 'Title is required.';
    if ((int)$cat_id <= 0) $errors[] = 'Category is required.';
    if (empty($content)) $errors[] = 'Content is required.';
    return $errors;
}

/**
 * @param string|null $existing_path
 * @return array
 */
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

    if ($existing_path) {
        $old_file = __DIR__ . '/../' . ltrim($existing_path, '/');
        if (file_exists($old_file)) @unlink($old_file);
    }

    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $fn = generate_secure_filename($ext);
    $dest = __DIR__ . '/../assets/uploads/' . $fn;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
        $result['path'] = 'assets/uploads/' . $fn;
    } else {
        $result['errors'][] = 'Failed to upload image.';
    }

    return $result;
}

/**
 * @param string|null $current_path
 * @param bool $remove_requested
 * @return string|null
 */
function handle_image_removal($current_path, $remove_requested) {
    if ($remove_requested && $current_path) {
        $file = __DIR__ . '/../' . ltrim($current_path, '/');
        if (file_exists($file)) @unlink($file);
        return null;
    }
    return $current_path;
}

/**
 * @param PDO $conn
 * @param int $cat_id
 * @param int $user_id
 * @param string $title
 * @param string|null $image
 * @param string $content
 * @param string $status
 * @param bool $is_admin
 * @return int
 */
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

    return $nid;
}

/**
 * @param PDO $conn
 * @param int $post_id
 * @param int $cat_id
 * @param string $title
 * @param string|null $image
 * @param string $content
 * @param string $status
 * @param bool $clear_reason
 * @param int $user_id
 * @param bool $is_admin
 * @return void
 */
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

/**
 * @param PDO $conn
 * @param string $type
 * @param string $description
 * @param int $user_id
 * @param int $post_id
 * @return void
 */
function log_post_activity($conn, $type, $description, $user_id, $post_id) {
    $s = $conn->prepare("INSERT INTO activity_log (action_type, description, user_id, entity_type, entity_id) VALUES (:at, :d, :u, 'post', :e)");
    $s->execute([':at' => $type, ':d' => $description, ':u' => (int)$user_id, ':e' => (int)$post_id]);
}

/**
 * @param PDO $conn
 * @param array $criteria
 * @return array
 */
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


