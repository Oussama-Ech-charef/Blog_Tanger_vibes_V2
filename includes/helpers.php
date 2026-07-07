<?php

// Show relative time (e.g. "3 min ago")
function time_ago($datetime) {
    $now = new DateTime();
    $then = new DateTime($datetime);
    $diff = $now->getTimestamp() - $then->getTimestamp();

    if ($diff < 60) return __('time_just_now');
    if ($diff < 3600) return sprintf(__('time_minutes_ago'), floor($diff / 60));
    if ($diff < 86400) return sprintf(__('time_hours_ago'), floor($diff / 3600));
    if ($diff < 604800) return sprintf(__('time_days_ago'), floor($diff / 86400));
    return date('M j, Y', strtotime($datetime));
}

// Truncate text to a given length
function truncate_text($text, $length = 80) {
    if (strlen($text) <= $length) return htmlspecialchars($text);
    return htmlspecialchars(substr($text, 0, $length)) . '...';
}

// Render post content with allowed HTML tags
function render_post_content($html) {
    $html = trim((string)$html);
    if ($html === '') return '';

    if ($html === strip_tags($html)) {
        return nl2br(htmlspecialchars($html, ENT_QUOTES, 'UTF-8'));
    }

    if (!class_exists('DOMDocument')) {
        return nl2br(htmlspecialchars(strip_tags($html), ENT_QUOTES, 'UTF-8'));
    }

    $allowed_tags = ['a', 'b', 'br', 'div', 'em', 'h2', 'i', 'img', 'li', 'ol', 'p', 'strong', 'u', 'ul'];
    $allowed_attrs = [
        'a' => ['href', 'title', 'target', 'rel'],
        'img' => ['src', 'alt', 'title'],
    ];

    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="UTF-8"><div id="content-root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    $clean_node = function ($node) use (&$clean_node, $allowed_tags, $allowed_attrs) {
        if ($node->nodeType === XML_ELEMENT_NODE) {
            $tag = strtolower($node->nodeName);

            if (!in_array($tag, $allowed_tags, true)) {
                $parent = $node->parentNode;
                if ($parent) {
                    while ($node->firstChild) {
                        $parent->insertBefore($node->firstChild, $node);
                    }
                    $parent->removeChild($node);
                }
                return;
            }

            if ($node->hasAttributes()) {
                $remove_attrs = [];
                foreach ($node->attributes as $attr) {
                    $name = strtolower($attr->name);
                    $allowed = in_array($name, $allowed_attrs[$tag] ?? [], true);
                    if (!$allowed || str_starts_with($name, 'on')) {
                        $remove_attrs[] = $attr->name;
                    }
                }
                foreach ($remove_attrs as $attr_name) {
                    $node->removeAttribute($attr_name);
                }
            }

            if ($tag === 'a') {
                $href = trim($node->getAttribute('href'));
                if ($href === '' || preg_match('/^\s*javascript:/i', $href)) {
                    $node->removeAttribute('href');
                } else {
                    $node->setAttribute('rel', 'noopener noreferrer');
                    if (!$node->hasAttribute('target')) {
                        $node->setAttribute('target', '_blank');
                    }
                }
            }

            if ($tag === 'img') {
                $src = trim($node->getAttribute('src'));
                if ($src === '' || preg_match('/^\s*(javascript|data):/i', $src)) {
                    $node->parentNode?->removeChild($node);
                    return;
                }
            }
        }

        for ($i = $node->childNodes->length - 1; $i >= 0; $i--) {
            $clean_node($node->childNodes->item($i));
        }
    };

    $root = $dom->getElementById('content-root');
    if (!$root) return '';
    $clean_node($root);

    $output = '';
    foreach ($root->childNodes as $child) {
        $output .= $dom->saveHTML($child);
    }

    return trim($output);
}

// Choose avatar color based on name
function avatar_color($name) {
    $colors = ['blue', 'green', 'purple', 'orange'];
    $index = abs(crc32($name)) % count($colors);
    return $colors[$index];
}

// Get initials from a name
function avatar_initials($name) {
    $parts = explode(' ', trim($name));
    if (count($parts) >= 2) {
        return strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
    }
    return strtoupper(substr($name, 0, 2));
}

// Render a notification bubble
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

// Render error notifications
function render_errors($errors) {
    if (empty($errors)) return;
    $msg = implode(' | ', array_map('htmlspecialchars', $errors));
    render_notification($msg, 'error');
}

// Render a post card for the public pages
function render_post_card($post, $btn_key = 'latest_read_more') {
    $html = '<a href="detail.php?id=' . (int)$post['id_post'] . '" class="card_place motion-reveal motion-reveal-scale">';
    $html .= '<img src="../' . htmlspecialchars((string)($post['image'] ?? '')) . '" alt="' . htmlspecialchars((string)($post['title'] ?? '')) . '" loading="lazy">';
    $html .= '<div class="card_content">';
    $html .= '<span class="category"><i class="fa-solid fa-layer-group" aria-hidden="true"></i> ' . htmlspecialchars((string)($post['cat_name'] ?? '')) . '</span>';
    $html .= '<h3 class="title">' . htmlspecialchars((string)($post['title'] ?? '')) . '</h3>';
    $user_name = htmlspecialchars((string)($post['user_name'] ?? 'Admin'));
    $html .= '<p class="location"><i class="fa-solid fa-user" aria-hidden="true"></i> ' . __('latest_by') . ' ' . $user_name . '</p>';
    $html .= '<p class="location"><i class="fa-solid fa-calendar-days" aria-hidden="true"></i> ' . date('M d, Y', strtotime((string)($post['created_at'] ?? 'now'))) . '</p>';
    $btn_text = __($btn_key);
    $html .= '<span class="btn">' . $btn_text . ' <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>';
    $html .= '</div></a>';
    return $html;
}

// Render a search input field
function render_search_input($name, $value, $placeholder = null) {
    if ($placeholder === null) {
        $placeholder = __('search_placeholder');
    }
    $html = '<div class="search_input">';
    $html .= '<i class="fa-solid fa-search" aria-hidden="true"></i>';
    $html .= '<input type="text" name="' . htmlspecialchars($name) . '" placeholder="' . htmlspecialchars($placeholder) . '" value="' . htmlspecialchars($value) . '">';
    $html .= '</div>';
    return $html;
}

// Safely delete an uploaded image (path traversal check)
function safe_delete_uploaded_image($path) {
    if (empty($path)) return false;
    $uploads_dir = realpath(__DIR__ . '/../assets/uploads');
    if ($uploads_dir === false) return false;
    $target = realpath(__DIR__ . '/../' . ltrim($path, '/'));
    if ($target === false) return false;
    $ds = DIRECTORY_SEPARATOR;
    if (strncmp($target . $ds, $uploads_dir . $ds, strlen($uploads_dir) + 1) !== 0) return false;
    $ext = strtolower(pathinfo($target, PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) return false;
    if (!is_file($target)) return false;
    return @unlink($target);
}

// Render a filter select dropdown
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

// Render a date range filter
function render_date_range_filter($id, $date_from = '', $date_to = '', $selected = '', $param_from = 'date_from', $param_to = 'date_to') {
    $display = $selected === 'custom' ? 'flex' : 'none';
    $html = '<div class="notif_date_range" id="' . htmlspecialchars($id) . '" style="display:' . $display . '">';
    $html .= '<input type="date" name="' . htmlspecialchars($param_from) . '" value="' . htmlspecialchars($date_from) . '">';
    $html .= '<span class="text_muted date_cell">' . __('filter_to') . '</span>';
    $html .= '<input type="date" name="' . htmlspecialchars($param_to) . '" value="' . htmlspecialchars($date_to) . '">';
    $html .= '</div>';
    return $html;
}

// Execute a DB action with CSRF validation
function execute_db_action($db_op, $success_msg = '', $error_msg = '') {
    if ($success_msg === '') $success_msg = __('db_op_success');
    if ($error_msg === '') $error_msg = __('db_op_error');
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        return [__('csrf_invalid'), 'error'];
    }
    try {
        $db_op();
        return [$success_msg, 'success'];
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return [$error_msg, 'error'];
    }
}
