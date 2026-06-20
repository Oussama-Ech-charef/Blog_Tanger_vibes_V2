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
