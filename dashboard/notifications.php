<?php
$page_title = 'Notifications';
require_once __DIR__ . '/init.php';

// Mark single as read
if (isset($_POST['read']) && is_numeric($_POST['read'])) {
    if (validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $conn->prepare("UPDATE activity_log SET is_read=1 WHERE id_activity=:id")->execute([':id'=>(int)$_POST['read']]);
    }
    $q = $_GET;
    unset($q['read']);
    header('Location: notifications.php?' . http_build_query($q));
    exit();
}

// Mark all as read
if (isset($_POST['mark_all_read']) && validate_csrf_token($_POST['csrf_token'] ?? '')) {
    $conn->prepare("UPDATE activity_log SET is_read=1 WHERE action_type NOT IN ('draft_saved')")->execute();
    $q = $_GET;
    unset($q['mark_all_read']);
    header('Location: notifications.php?' . http_build_query($q));
    exit();
}

//  Category mapping
$category_options = [
    ''                => 'All Notifications',
    'posts'           => 'Posts',
    'comments'        => 'Comments',
    'users'           => 'Users',
    'categories'      => 'Categories',
    'contact_messages'=> 'Contact Messages',
    'approvals'       => 'Approvals',
    'rejections'      => 'Rejections',
    'system'          => 'System Events',
    'security'        => 'Security Events',
];

$category_action_map = [
    'posts'           => ['post_created', 'post_submitted'],
    'comments'        => ['comment_added'],
    'users'           => ['user_registered'],
    'categories'      => [],
    'contact_messages'=> ['message_received'],
    'approvals'       => ['post_approved'],
    'rejections'      => ['post_rejected'],
    'system'          => ['post_updated', 'post_deleted'],
    'security'        => [],
];

//  Action type info for display 
$type_info = [
    'post_created'     => ['icon' => 'fa-solid fa-plus',          'color' => '#10B981', 'bg' => '#D1FAE5', 'label' => 'Post Created'],
    'post_submitted'   => ['icon' => 'fa-solid fa-paper-plane',   'color' => '#3B82F6', 'bg' => '#DBEAFE', 'label' => 'Submitted'],
    'post_approved'    => ['icon' => 'fa-solid fa-check-circle',  'color' => '#059669', 'bg' => '#D1FAE5', 'label' => 'Approved'],
    'post_rejected'    => ['icon' => 'fa-solid fa-ban',           'color' => '#DC2626', 'bg' => '#FEE2E2', 'label' => 'Rejected'],
    'post_updated'     => ['icon' => 'fa-solid fa-pen',           'color' => '#7C3AED', 'bg' => '#EDE9FE', 'label' => 'Updated'],
    'post_deleted'     => ['icon' => 'fa-solid fa-trash',         'color' => '#EF4444', 'bg' => '#FEE2E2', 'label' => 'Deleted'],
    'comment_added'    => ['icon' => 'fa-solid fa-comment',       'color' => '#0047AB', 'bg' => '#E8F0FE', 'label' => 'Comment'],
    'user_registered'  => ['icon' => 'fa-solid fa-user-plus',     'color' => '#7C3AED', 'bg' => '#EDE9FE', 'label' => 'New User'],
    'message_received' => ['icon' => 'fa-solid fa-envelope',      'color' => '#D97706', 'bg' => '#FEF3C7', 'label' => 'Message'],
];

//  Link builder 
function notification_link($action_type, $entity_type, $entity_id) {
    if ($action_type === 'message_received') {
        return $entity_id ? "messages.php?view=$entity_id" : 'messages.php';
    }
    if ($entity_type === 'post' && $entity_id) {
        $action_base = '';
        if (in_array($action_type, ['post_approved', 'post_rejected', 'post_created', 'post_submitted', 'post_updated', 'post_deleted'], true)) {
            $action_base = "posts.php?view=$entity_id";
        }
        return $action_base ?: "posts.php?id=$entity_id";
    }
    if ($entity_type === 'comment' && $entity_id) return 'comments.php';
    if ($entity_type === 'user' && $entity_id) return 'users.php';
    return null;
}

//  Date grouping 
function get_date_group($date_str) {
    $ts = strtotime($date_str);
    $today = strtotime('today');
    $yesterday = strtotime('yesterday');

    if ($ts >= $today) return 'Today';
    if ($ts >= $yesterday) return 'Yesterday';
    if ($ts >= strtotime('monday this week')) return 'This Week';
    if ($ts >= strtotime('monday last week') && $ts < strtotime('monday this week')) return 'Last Week';
    if (date('m', $ts) === date('m') && date('Y', $ts) === date('Y')) return 'This Month';
    return date('F Y', $ts);
}

//  Read filters
$category_filter = $_GET['category'] ?? '';
$status_filter = $_GET['status'] ?? '';
$date_filter = $_GET['date'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$search = trim($_GET['q'] ?? '');
$user_filter = $_GET['user'] ?? '';
$per_page = 25;
$page = get_valid_page();

// Load users for filte
$all_users = $conn->query("SELECT id_user, user_name, role FROM users WHERE is_active=1 ORDER BY user_name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Build WHERE clause 
$where_parts = ["al.action_type != ?"];
$params = ['draft_saved'];

// Category filter
if (!empty($category_filter) && isset($category_action_map[$category_filter])) {
    $types = $category_action_map[$category_filter];
    if (!empty($types)) {
        $placeholders = implode(',', array_fill(0, count($types), '?'));
        $where_parts[] = "al.action_type IN ($placeholders)";
        $params = array_merge($params, $types);
    } else {
        $where_parts[] = "1=0";
    }
}

// Status filter
if ($status_filter === 'read') {
    $where_parts[] = "al.is_read = 1";
} elseif ($status_filter === 'unread') {
    $where_parts[] = "al.is_read = 0";
}

// Date filter
if ($date_filter === 'today') {
    $where_parts[] = "DATE(al.created_at) = CURDATE()";
} elseif ($date_filter === '7days') {
    $where_parts[] = "al.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
} elseif ($date_filter === '30days') {
    $where_parts[] = "al.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
} elseif ($date_filter === 'month') {
    $where_parts[] = "YEAR(al.created_at) = YEAR(CURDATE()) AND MONTH(al.created_at) = MONTH(CURDATE())";
} elseif ($date_filter === 'custom') {
    if (!empty($date_from)) {
        $where_parts[] = "al.created_at >= ?";
        $params[] = $date_from . ' 00:00:00';
    }
    if (!empty($date_to)) {
        $where_parts[] = "al.created_at <= ?";
        $params[] = $date_to . ' 23:59:59';
    }
}

// User filter
if (!empty($user_filter) && is_numeric($user_filter)) {
    $where_parts[] = "al.user_id = ?";
    $params[] = (int)$user_filter;
}

// Search
if (!empty($search)) {
    $where_parts[] = "(al.description LIKE ? OR u.user_name LIKE ? OR al.action_type LIKE ?)";
    $s = '%' . $search . '%';
    $params[] = $s;
    $params[] = $s;
    $params[] = $s;
}

$where = implode(' AND ', $where_parts);

// Count 
$cs = $conn->prepare("SELECT COUNT(*) FROM activity_log al LEFT JOIN users u ON al.user_id = u.id_user WHERE $where");
$cs->execute($params);
$total_records = (int)$cs->fetchColumn();
$total_pages = get_total_pages($total_records, $per_page);
$current_page = min($page, $total_pages);
$offset = get_offset($current_page, $per_page);

//  Fetch 
$ds = $conn->prepare("SELECT al.*, u.user_name FROM activity_log al LEFT JOIN users u ON al.user_id = u.id_user WHERE $where ORDER BY al.created_at DESC LIMIT ? OFFSET ?");

// Bind all WHERE params
foreach ($params as $i => $val) {
    $ds->bindValue($i + 1, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$ds->bindValue(count($params) + 1, $per_page, PDO::PARAM_INT);
$ds->bindValue(count($params) + 2, $offset, PDO::PARAM_INT);
$ds->execute();
$notifications = $ds->fetchAll(PDO::FETCH_ASSOC);

//  Count unread for header 
$unread_count = (int)$conn->query("SELECT COUNT(*) FROM activity_log WHERE is_read=0 AND action_type NOT IN ('draft_saved')")->fetchColumn();

//  Build query params for pagination
$query_params = [];
if (!empty($category_filter)) $query_params['category'] = $category_filter;
if (!empty($status_filter)) $query_params['status'] = $status_filter;
if (!empty($date_filter)) $query_params['date'] = $date_filter;
if (!empty($date_from)) $query_params['date_from'] = $date_from;
if (!empty($date_to)) $query_params['date_to'] = $date_to;
if (!empty($search)) $query_params['q'] = $search;
if (!empty($user_filter)) $query_params['user'] = $user_filter;

require_once __DIR__ . '/inc/header.php';
?>

<div class="notif_page_header">
    <div>
        <h1 class="notif_page_title">
            <i class="fa-solid fa-bell icon_primary" aria-hidden="true"></i> Notifications
        </h1>
        <p class="notif_page_subtitle">
            <?= $unread_count ?> unread notification<?= $unread_count !== 1 ? 's' : '' ?>
        </p>
    </div>
    <?php if ($unread_count > 0): ?>
    <form method="POST" action="notifications.php" class="inline_form">
        <?php foreach ($query_params as $qk=>$qv): ?><input type="hidden" name="<?=htmlspecialchars($qk)?>" value="<?=htmlspecialchars($qv)?>"><?php endforeach; ?>
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <button type="submit" name="mark_all_read" class="btn btn_secondary btn_sm" style="display:inline-flex;align-items:center;gap:6px;">
            <i class="fa-solid fa-check-double" aria-hidden="true"></i> Mark All as Read
        </button>
    </form>
    <?php endif; ?>
</div>

<form method="GET" id="notifFilterForm" class="notif_filters_bar">
    <div class="search_input">
        <i class="fa-solid fa-search" aria-hidden="true"></i>
        <input type="text" name="q" placeholder="Search notifications..." value="<?= htmlspecialchars($search) ?>" onchange="this.form.submit()">
    </div>
    <select name="category" class="filter_select" onchange="this.form.submit()">
        <?php foreach ($category_options as $val => $label): ?>
            <option value="<?=$val?>" <?=$category_filter===$val?'selected':''?>><?=htmlspecialchars($label)?></option>
        <?php endforeach; ?>
    </select>
    <select name="status" class="filter_select" onchange="this.form.submit()">
        <option value="">All</option>
        <option value="unread" <?=$status_filter==='unread'?'selected':''?>>Unread</option>
        <option value="read" <?=$status_filter==='read'?'selected':''?>>Read</option>
    </select>
    <select name="date" class="filter_select" onchange="if(this.value!=='custom'){this.form.submit();}else{document.getElementById('notifDateRange').style.display='flex';}">
        <option value="">All Dates</option>
        <option value="today" <?=$date_filter==='today'?'selected':''?>>Today</option>
        <option value="7days" <?=$date_filter==='7days'?'selected':''?>>Last 7 Days</option>
        <option value="30days" <?=$date_filter==='30days'?'selected':''?>>Last 30 Days</option>
        <option value="month" <?=$date_filter==='month'?'selected':''?>>This Month</option>
        <option value="custom" <?=$date_filter==='custom'?'selected':''?>>Custom Range</option>
    </select>
    <div class="notif_date_range" id="notifDateRange" style="display:<?=$date_filter==='custom'?'flex':'none'?>">
        <input type="date" name="date_from" value="<?= htmlspecialchars($date_from) ?>" onchange="this.form.submit()">
        <span class="text_muted date_cell">to</span>
        <input type="date" name="date_to" value="<?= htmlspecialchars($date_to) ?>" onchange="this.form.submit()">
    </div>
    <select name="user" class="filter_select" onchange="this.form.submit()">
        <option value="">All Users</option>
        <?php foreach ($all_users as $u): ?>
            <option value="<?=$u['id_user']?>" <?=$user_filter==$u['id_user']?'selected':''?>><?=htmlspecialchars($u['user_name'])?> (<?=ucfirst($u['role'])?>)</option>
        <?php endforeach; ?>
    </select>
    <?php if (!empty($search) || !empty($category_filter) || !empty($status_filter) || !empty($date_filter) || !empty($user_filter)): ?>
    <a href="notifications.php" class="btn_small btn_secondary clear_filter_btn"><i class="fa-solid fa-times" aria-hidden="true"></i> Clear</a>
    <?php endif; ?>
</form>

<div class="card">
    <div class="card_body_no_padding">
        <?php if (!empty($notifications)): ?>
            <?php
            $current_group = null;
            foreach ($notifications as $n):
                $group = get_date_group($n['created_at']);
                if ($group !== $current_group):
                    if ($current_group !== null):
            ?>
                    </div>
                    <?php endif; ?>
                    <div class="notif_group_header"><?= htmlspecialchars($group) ?></div>
                    <div>
                    <?php $current_group = $group;
                endif;
                $info = $type_info[$n['action_type']] ?? ['icon' => 'fa-solid fa-circle', 'color' => '#64748B', 'bg' => '#F1F5F9', 'label' => ucfirst(str_replace('_', ' ', $n['action_type']))];
                $link = notification_link($n['action_type'], $n['entity_type'], $n['entity_id']);
            ?>
            <div class="notif_item<?= !$n['is_read'] ? ' unread' : '' ?>">
                <div class="notif_icon_wrap" style="background:<?=$info['bg']?>;color:<?=$info['color']?>;">
                    <i class="<?=$info['icon']?>" aria-hidden="true"></i>
                </div>
                <div class="notif_body">
                    <p>
                        <span class="notif_desc"><?= htmlspecialchars($n['description']) ?></span>
                        <span class="notif_badge" style="background:<?=$info['bg']?>;color:<?=$info['color']?>;"><?= htmlspecialchars($info['label']) ?></span>
                    </p>
                    <p class="notif_meta">
                        <?php if (!empty($n['user_name'])): ?>
                            <i class="fa-solid fa-user" aria-hidden="true"></i> <?= htmlspecialchars($n['user_name']) ?> &middot;
                        <?php endif; ?>
                        <?= time_ago($n['created_at']) ?>
                    </p>
                </div>
                <div class="notif_actions">
                    <?php if ($link): ?>
                    <a href="<?= $link ?>" class="btn_small btn_secondary clear_filter_btn">
                        <i class="fa-solid fa-eye" aria-hidden="true"></i> View
                    </a>
                    <?php endif; ?>
                    <?php if (!$n['is_read']): ?>
                    <form method="POST" action="notifications.php" class="inline_form">
                        <?php foreach ($query_params as $qk=>$qv): ?><input type="hidden" name="<?=htmlspecialchars($qk)?>" value="<?=htmlspecialchars($qv)?>"><?php endforeach; ?>
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="read" value="<?= $n['id_activity'] ?>">
                        <button type="submit" class="btn_small btn_secondary" style="border:none;cursor:pointer" aria-label="Mark as read">
                            <i class="fa-solid fa-check"></i>
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if ($current_group !== null): ?>
            </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="notif_empty_state">
                <i class="fa-solid fa-bell notif_empty_icon" aria-hidden="true"></i>
                <h3 class="notif_empty_title">No notifications</h3>
                <p class="notif_empty_desc">Try adjusting your filters.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php render_dashboard_pagination('notifications.php', $current_page, $total_pages, $query_params); ?>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
