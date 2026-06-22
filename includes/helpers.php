<?php

/**
 * @param string $datetime
 * @return string
 */
function time_ago($datetime) {
    $now = new DateTime();
    $then = new DateTime($datetime);
    $diff = $now->getTimestamp() - $then->getTimestamp();

    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return date('M j, Y', strtotime($datetime));
}

/**
 * @param string $text
 * @param int $length
 * @return string
 */
function truncate_text($text, $length = 80) {
    if (strlen($text) <= $length) return htmlspecialchars($text);
    return htmlspecialchars(substr($text, 0, $length)) . '...';
}

/**
 * @param string $name
 * @return string
 */
function avatar_color($name) {
    $colors = ['blue', 'green', 'purple', 'orange'];
    $index = abs(crc32($name)) % count($colors);
    return $colors[$index];
}

/**
 * @param string $name
 * @return string
 */
function avatar_initials($name) {
    $parts = explode(' ', trim($name));
    if (count($parts) >= 2) {
        return strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
    }
    return strtoupper(substr($name, 0, 2));
}

/**
 * @param string $message
 * @param string $type
 * @return void
 */
function render_notification($message, $type = 'success') {
    if (empty($message)) return;
    $icon = match ($type) {
        'success' => 'fa-check-circle',
        'error' => 'fa-exclamation-circle',
        'warning' => 'fa-triangle-exclamation',
        'info' => 'fa-info-circle',
        default => 'fa-check-circle'
    };
    echo '<div class="notification ' . $type . '"><i class="fa-solid ' . $icon . '" aria-hidden="true"></i> ' . htmlspecialchars($message) . '</div>';
}

/**
 * @param array $errors
 * @return void
 */
function render_errors($errors) {
    if (empty($errors)) return;
    $msg = implode(' | ', array_map('htmlspecialchars', $errors));
    render_notification($msg, 'error');
}

/**
 * @param PDO $conn
 * @param string $type
 * @param string $description
 * @param int|null $user_id
 * @param string $entity_type
 * @param int|null $entity_id
 * @return void
 */
function insert_activity_log($conn, $type, $description, $user_id = null, $entity_type = 'post', $entity_id = null) {
    $s = $conn->prepare("INSERT INTO activity_log (action_type, description, user_id, entity_type, entity_id) VALUES (:at, :d, :u, :et, :ei)");
    $s->execute([':at' => $type, ':d' => $description, ':u' => $user_id, ':et' => $entity_type, ':ei' => $entity_id]);
}

/**
 * @param PDO $conn
 * @param int $user_id
 * @param string $type
 * @param string $message
 * @param string $link
 * @return void
 */
function insert_notification($conn, $user_id, $type, $message, $link = '') {
    $s = $conn->prepare("INSERT INTO user_notifications (id_user, type, message, link) VALUES (:uid, :t, :m, :l)");
    $s->execute([':uid' => $user_id, ':t' => $type, ':m' => $message, ':l' => $link]);
}

/**
 * @param string $icon
 * @param string $title
 * @param string $description
 * @return string
 */
function render_empty_state($icon, $title, $description = '') {
    $html = '<div class="empty_state">';
    $html .= '<i class="fa-solid fa-' . $icon . '" aria-hidden="true"></i>';
    $html .= '<h3>' . htmlspecialchars($title) . '</h3>';
    if (!empty($description)) {
        $html .= '<p>' . htmlspecialchars($description) . '</p>';
    }
    $html .= '</div>';
    return $html;
}

/**
 * @param array $post
 * @param string $btn_key
 * @return string
 */
function render_post_card($post, $btn_key = 'latest_read_more') {
    $html = '<a href="detail.php?id=' . (int)$post['id_post'] . '" class="card_place">';
    $html .= '<img src="../' . htmlspecialchars($post['image']) . '" alt="' . htmlspecialchars($post['title']) . '" loading="lazy">';
    $html .= '<div class="card_content">';
    $html .= '<span class="category"><i class="fa-solid fa-layer-group" aria-hidden="true"></i> ' . htmlspecialchars($post['cat_name']) . '</span>';
    $html .= '<h3 class="title">' . htmlspecialchars($post['title']) . '</h3>';
    $user_name = htmlspecialchars($post['user_name'] ?? 'Admin');
    $html .= '<p class="location"><i class="fa-solid fa-user" aria-hidden="true"></i> ' . __('latest_by') . ' ' . $user_name . '</p>';
    $html .= '<p class="location"><i class="fa-solid fa-calendar-days" aria-hidden="true"></i> ' . date('M d, Y', strtotime($post['created_at'])) . '</p>';
    $btn_text = __($btn_key);
    $html .= '<span class="btn">' . $btn_text . ' <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>';
    $html .= '</div></a>';
    return $html;
}

/**
 * @param string $name
 * @param string $value
 * @param string $placeholder
 * @return string
 */
function render_search_input($name, $value, $placeholder = 'Search...') {
    $html = '<div class="search_input">';
    $html .= '<i class="fa-solid fa-search" aria-hidden="true"></i>';
    $html .= '<input type="text" name="' . htmlspecialchars($name) . '" placeholder="' . htmlspecialchars($placeholder) . '" value="' . htmlspecialchars($value) . '">';
    $html .= '</div>';
    return $html;
}

/**
 * @param string $name
 * @param array $options
 * @param string $selected
 * @param string $label
 * @return string
 */
function render_filter_select($name, $options, $selected = '', $label = '') {
    $html = '<select name="' . htmlspecialchars($name) . '" class="filter_select">';
    if (!empty($label)) {
        $html .= '<option value="">' . htmlspecialchars($label) . '</option>';
    }
    foreach ($options as $value => $text) {
        $sel = ((string)$value === (string)$selected || (string)$text === (string)$selected) ? ' selected' : '';
        $html .= '<option value="' . htmlspecialchars($value) . '"' . $sel . '>' . htmlspecialchars($text) . '</option>';
    }
    $html .= '</select>';
    return $html;
}

/**
 * @param string $id
 * @param string $date_from
 * @param string $date_to
 * @param string $selected
 * @param string $param_from
 * @param string $param_to
 * @return string
 */
function render_date_range_filter($id, $date_from = '', $date_to = '', $selected = '', $param_from = 'date_from', $param_to = 'date_to') {
    $display = $selected === 'custom' ? 'flex' : 'none';
    $html = '<div class="notif_date_range" id="' . htmlspecialchars($id) . '" style="display:' . $display . '">';
    $html .= '<input type="date" name="' . htmlspecialchars($param_from) . '" value="' . htmlspecialchars($date_from) . '">';
    $html .= '<span style="color:var(--db-text-muted);font-size:13px;">to</span>';
    $html .= '<input type="date" name="' . htmlspecialchars($param_to) . '" value="' . htmlspecialchars($date_to) . '">';
    $html .= '</div>';
    return $html;
}

/**
 * @param callable $db_op
 * @param string $success_msg
 * @param string $error_msg
 * @return array{string,string}
 */
function execute_db_action($db_op, $success_msg = 'Operation completed.', $error_msg = 'An error occurred.') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        return ['Invalid security token.', 'error'];
    }
    try {
        $db_op();
        return [$success_msg, 'success'];
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return [$error_msg, 'error'];
    }
}
