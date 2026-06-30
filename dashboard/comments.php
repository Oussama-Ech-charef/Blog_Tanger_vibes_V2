<?php
$page_title = 'Comments Moderation';
require_once __DIR__ . '/init.php';
$message = ''; $message_type = '';

// Approve
if (isset($_POST['approve']) && is_numeric($_POST['approve'])) {
    if (validate_csrf_token($_POST['csrf_token'] ?? '')) {
        try {
            $conn->prepare("UPDATE comments SET status='approved' WHERE id_comment=:id")->execute([':id'=>(int)$_POST['approve']]);
            $message = 'Comment approved.'; $message_type = 'success';
        } catch (PDOException $e) { error_log($e->getMessage()); $message = 'Error.'; $message_type = 'error'; }
    } else { $message = 'Invalid security token.'; $message_type = 'error'; }
}

// Reject
if (isset($_POST['reject']) && is_numeric($_POST['reject'])) {
    if (validate_csrf_token($_POST['csrf_token'] ?? '')) {
        try {
            $conn->prepare("UPDATE comments SET status=:rej_status WHERE id_comment=:id")->execute([':rej_status' => STATUS_REJECTED, ':id'=>(int)$_POST['reject']]);
            $message = 'Comment rejected.'; $message_type = 'success';
        } catch (PDOException $e) { error_log($e->getMessage()); $message = 'Error.'; $message_type = 'error'; }
    } else { $message = 'Invalid security token.'; $message_type = 'error'; }
}

// Delete
if (isset($_POST['delete']) && is_numeric($_POST['delete'])) {
    if (validate_csrf_token($_POST['csrf_token'] ?? '')) {
        try {
            $conn->prepare("DELETE FROM comments WHERE id_comment=:id")->execute([':id'=>(int)$_POST['delete']]);
            $message = 'Comment deleted.'; $message_type = 'success';
        } catch (PDOException $e) { error_log($e->getMessage()); $message = 'Error.'; $message_type = 'error'; }
    } else { $message = 'Invalid security token.'; $message_type = 'error'; }
}

// Bulk delete
if (isset($_POST['bulk_delete']) && !empty($_POST['comment_ids'])) {
    if (validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $ids = array_map('intval', explode(',', $_POST['comment_ids']));
        $phs = implode(',', array_fill(0, count($ids), '?'));
        try {
            $conn->prepare("DELETE FROM comments WHERE id_comment IN ($phs)")->execute($ids);
            $message = count($ids).' comment(s) deleted.'; $message_type = 'success';
        } catch (PDOException $e) { error_log($e->getMessage()); $message = 'Error.'; $message_type = 'error'; }
    } else { $message = 'Invalid security token.'; $message_type = 'error'; }
}

$csrf = get_csrf_token();

//  Filter vars
$per_page = 20;
$page = get_valid_page();
$search = trim($_GET['q'] ?? '');
$status_filter = $_GET['status'] ?? '';
$role_filter = $_GET['role'] ?? '';
$user_filter = $_GET['user'] ?? '';
$post_filter = $_GET['post'] ?? '';
$date_filter = $_GET['date'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build WHERE clause 
$where_parts = ["1=1"];
$params = [];

// Search — comment text, author name, post title
if (!empty($search)) {
    $where_parts[] = "(comments.author_name LIKE :s OR comments.comment_text LIKE :s2 OR posts.title LIKE :s3)";
    $params[':s'] = '%'.$search.'%';
    $params[':s2'] = '%'.$search.'%';
    $params[':s3'] = '%'.$search.'%';
}

// Status filter
if (!empty($status_filter) && in_array($status_filter, ['approved', STATUS_REJECTED, STATUS_PENDING, 'pending'])) {
    if ($status_filter === 'pending') $status_filter = STATUS_PENDING;
    $where_parts[] = "comments.status = :st";
    $params[':st'] = $status_filter;
}

// Role filter (via author_name match to users.user_name)
if (!empty($role_filter) && in_array($role_filter, ['admin', 'user'])) {
    $where_parts[] = "users.role = :rl";
    $params[':rl'] = $role_filter;
}

// User filter (match author_name to selected user's name)
if (!empty($user_filter) && is_numeric($user_filter)) {
    $u_stmt = $conn->prepare("SELECT user_name FROM users WHERE id_user = :id");
    $u_stmt->execute([':id' => (int)$user_filter]);
    $uname = $u_stmt->fetchColumn();
    if ($uname) {
        $where_parts[] = "comments.author_name = :un";
        $params[':un'] = $uname;
    }
}

// Post filter
if (!empty($post_filter) && is_numeric($post_filter)) {
    $where_parts[] = "comments.id_post = :pid";
    $params[':pid'] = (int)$post_filter;
}

// Date filter
if ($date_filter === 'today') {
    $where_parts[] = "DATE(comments.created_at) = CURDATE()";
} elseif ($date_filter === 'yesterday') {
    $where_parts[] = "DATE(comments.created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
} elseif ($date_filter === '7days') {
    $where_parts[] = "comments.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
} elseif ($date_filter === '30days') {
    $where_parts[] = "comments.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
} elseif ($date_filter === 'month') {
    $where_parts[] = "YEAR(comments.created_at) = YEAR(CURDATE()) AND MONTH(comments.created_at) = MONTH(CURDATE())";
} elseif ($date_filter === 'year') {
    $where_parts[] = "YEAR(comments.created_at) = YEAR(CURDATE())";
} elseif ($date_filter === 'custom') {
    if (!empty($date_from)) {
        $where_parts[] = "comments.created_at >= :date_from";
        $params[':date_from'] = $date_from . ' 00:00:00';
    }
    if (!empty($date_to)) {
        $where_parts[] = "comments.created_at <= :date_to";
        $params[':date_to'] = $date_to . ' 23:59:59';
    }
}

$where = implode(' AND ', $where_parts);

//  Load dropdown data 
$users_for_filter = $conn->query("SELECT id_user, user_name, role FROM users WHERE is_active=1 ORDER BY user_name ASC")->fetchAll(PDO::FETCH_ASSOC);
$posts_for_filter = $conn->query("SELECT DISTINCT p.id_post, p.title FROM posts p INNER JOIN comments c ON c.id_post = p.id_post ORDER BY p.title ASC")->fetchAll(PDO::FETCH_ASSOC);

//  Count 
$cs = $conn->prepare("SELECT COUNT(*) FROM comments LEFT JOIN posts ON comments.id_post=posts.id_post LEFT JOIN users ON comments.author_name = users.user_name WHERE $where");
$cs->execute($params);
$total_records = (int)$cs->fetchColumn();
$total_pages = get_total_pages($total_records, $per_page);
$current_page = min($page, $total_pages);
$offset = get_offset($current_page, $per_page);

//  Fetch 
$ds = $conn->prepare("SELECT comments.*, posts.title as post_title, posts.id_post, users.role as author_role FROM comments LEFT JOIN posts ON comments.id_post=posts.id_post LEFT JOIN users ON comments.author_name = users.user_name WHERE $where ORDER BY comments.created_at DESC LIMIT :lim OFFSET :off");
$int_params = [':pid'];
foreach ($params as $k => $v) {
    $ds->bindValue($k, $v, in_array($k, $int_params) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$ds->bindValue(':lim', $per_page, PDO::PARAM_INT);
$ds->bindValue(':off', $offset, PDO::PARAM_INT);
$ds->execute();
$comments = $ds->fetchAll(PDO::FETCH_ASSOC);

//  Query params for pagination 
$query_params = [];
if (!empty($search)) $query_params['q'] = $search;
if (!empty($status_filter)) $query_params['status'] = $status_filter;
if (!empty($role_filter)) $query_params['role'] = $role_filter;
if (!empty($user_filter)) $query_params['user'] = $user_filter;
if (!empty($post_filter)) $query_params['post'] = $post_filter;
if (!empty($date_filter)) $query_params['date'] = $date_filter;
if (!empty($date_from)) $query_params['date_from'] = $date_from;
if (!empty($date_to)) $query_params['date_to'] = $date_to;

require_once __DIR__ . '/inc/header.php';
?>

<?php render_notification($message, $message_type); ?>

<form method="GET" id="filterForm" class="filters_bar" style="flex-wrap:wrap;">
    <div class="search_input">
        <i class="fa-solid fa-search" aria-hidden="true"></i>
        <input type="text" name="q" placeholder="Search comments, author, post..." value="<?=htmlspecialchars($search)?>" onchange="this.form.submit()">
    </div>
    <select name="status" class="filter_select" onchange="this.form.submit()">
        <option value="">All Status</option>
        <option value="approved" <?=$status_filter==='approved'?'selected':''?>>Approved</option>
        <option value="pending" <?=$status_filter===STATUS_PENDING?'selected':''?>>Pending</option>
        <option value="rejected" <?=$status_filter===STATUS_REJECTED?'selected':''?>>Rejected</option>
    </select>
    <select name="role" class="filter_select" onchange="this.form.submit()">
        <option value="">All Roles</option>
        <option value="admin" <?=$role_filter==='admin'?'selected':''?>>Administrators</option>
        <option value="user" <?=$role_filter==='user'?'selected':''?>>Users</option>
    </select>
    <select name="user" class="filter_select" onchange="this.form.submit()">
        <option value="">All Users</option>
        <?php foreach ($users_for_filter as $u): ?>
            <option value="<?=$u['id_user']?>" <?=$user_filter==$u['id_user']?'selected':''?>><?=htmlspecialchars($u['user_name'])?></option>
        <?php endforeach; ?>
    </select>
    <select name="post" class="filter_select" onchange="this.form.submit()">
        <option value="">All Posts</option>
        <?php foreach ($posts_for_filter as $p): ?>
            <option value="<?=$p['id_post']?>" <?=$post_filter==$p['id_post']?'selected':''?>><?=htmlspecialchars(truncate_text($p['title'], 40))?></option>
        <?php endforeach; ?>
    </select>
    <select name="date" class="filter_select" onchange="if(this.value!=='custom'){this.form.submit();}else{document.getElementById('commentDateRange').style.display='flex';}">
        <option value="">All Dates</option>
        <option value="today" <?=$date_filter==='today'?'selected':''?>>Today</option>
        <option value="yesterday" <?=$date_filter==='yesterday'?'selected':''?>>Yesterday</option>
        <option value="7days" <?=$date_filter==='7days'?'selected':''?>>Last 7 Days</option>
        <option value="30days" <?=$date_filter==='30days'?'selected':''?>>Last 30 Days</option>
        <option value="month" <?=$date_filter==='month'?'selected':''?>>This Month</option>
        <option value="year" <?=$date_filter==='year'?'selected':''?>>This Year</option>
        <option value="custom" <?=$date_filter==='custom'?'selected':''?>>Custom Range</option>
    </select>
    <div class="notif_date_range" id="commentDateRange" style="display:<?=$date_filter==='custom'?'flex':'none'?>">
        <input type="date" name="date_from" value="<?= htmlspecialchars($date_from) ?>" onchange="this.form.submit()">
        <span class="date_cell" style="color:var(--db-text-muted);">to</span>
        <input type="date" name="date_to" value="<?= htmlspecialchars($date_to) ?>" onchange="this.form.submit()">
    </div>
    <span class="ml_auto" style="font-size:14px;color:var(--db-text-secondary);font-weight:500;"><?=$total_records?> comment(s)</span>
    <?php if (!empty($search) || !empty($status_filter) || !empty($role_filter) || !empty($user_filter) || !empty($post_filter) || !empty($date_filter)): ?>
    <a href="comments.php" class="btn_small btn_secondary clear_filter_btn"><i class="fa-solid fa-times" aria-hidden="true"></i> Clear</a>
    <?php endif; ?>
</form>

<div class="card">
    <div class="card_header">
        <h2>Comments</h2>
        <?php if (!empty($comments)): ?>
        <form method="POST" onsubmit="return confirm('Delete selected?')">
            <?php foreach ($query_params as $qk=>$qv): ?><input type="hidden" name="<?=htmlspecialchars($qk)?>" value="<?=htmlspecialchars($qv)?>"><?php endforeach; ?>
            <input type="hidden" name="csrf_token" value="<?=$csrf?>">
            <input type="hidden" name="bulk_delete" value="1">
            <input type="hidden" name="comment_ids" id="bulkIds">
            <button type="submit" class="btn btn_danger btn_sm" id="bulkDeleteBtn" style="display:none" onclick="document.getElementById('bulkIds').value=Array.from(document.querySelectorAll('.cb:checked')).map(c=>c.value).join(',')"><i class="fa-solid fa-trash" aria-hidden="true"></i> Delete Selected</button>
        </form>
        <?php endif; ?>
    </div>
    <div class="card_body_no_padding">
        <div class="table_wrapper">
            <table class="data_table">
                <thead><tr><th style="width:36px;"><input type="checkbox" id="selectAll" onchange="document.querySelectorAll('.cb').forEach(c=>c.checked=this.checked);toggleBulk()"></th><th>Author</th><th>Comment</th><th>Post</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php if (!empty($comments)): ?>
                        <?php foreach ($comments as $c): ?>
                        <tr>
                            <td><input type="checkbox" class="cb" value="<?=$c['id_comment']?>" onchange="toggleBulk()"></td>
                            <td>
                                <div class="flex_center">
                                    <span class="user_avatar <?=avatar_color($c['author_name'])?>"><?=avatar_initials($c['author_name'])?></span>
                                    <div>
                                        <strong><?=htmlspecialchars($c['author_name'])?></strong>
                                        <?php if (!empty($c['author_role'])): ?>
                                        <span class="role_badge <?=$c['author_role']?>"><?=ucfirst($c['author_role'])?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td style="max-width:300px;"><span class="text_clamp_2"><?=htmlspecialchars($c['comment_text'])?></span></td>
                            <td><?php if($c['post_title']):?><a href="../pages/detail.php?id=<?=$c['id_post']?>" target="_blank" rel="noopener" class="view_link"><?=htmlspecialchars(truncate_text($c['post_title'],40))?></a><?php else:?><span class="text_muted">[deleted]</span><?php endif;?></td>
                            <td><span class="status_badge status_<?=$c['status']?>"><?=ucfirst(htmlspecialchars($c['status'] ?? STATUS_PENDING))?></span></td>
                            <td class="date_cell"><?=time_ago($c['created_at'])?></td>
                            <td>
                                <div class="cell_actions cell_actions_right">
                                    <div class="action_dropdown">
                                        <button type="button" class="action_dropdown_btn" onclick="toggleDropdown(this)" aria-label="Actions"><i class="fa-solid fa-ellipsis-vertical" aria-hidden="true"></i></button>
                                        <div class="action_dropdown_menu">
                                            <?php if (!empty($c['id_post'])): ?>
                                            <a href="../pages/detail.php?id=<?=$c['id_post']?>" class="dropdown_item" target="_blank" rel="noopener"><i class="fa-solid fa-eye" aria-hidden="true"></i> View Comment</a>
                                            <?php endif; ?>
                                            <?php if ($c['status'] !== 'approved'): ?>
                                            <form method="POST" action="comments.php" class="dropdown_form">
                                                <input type="hidden" name="csrf_token" value="<?=$csrf?>">
                                                <input type="hidden" name="approve" value="<?=$c['id_comment']?>">
                                                <?php foreach ($query_params as $qk=>$qv): ?><input type="hidden" name="<?=htmlspecialchars($qk)?>" value="<?=htmlspecialchars($qv)?>"><?php endforeach; ?>
                                                <button type="submit" class="dropdown_item"><i class="fa-solid fa-check" aria-hidden="true"></i> Approve Comment</button>
                                            </form>
                                            <?php endif; ?>
                                            <?php if ($c['status'] !== STATUS_REJECTED): ?>
                                            <form method="POST" action="comments.php" class="dropdown_form">
                                                <input type="hidden" name="csrf_token" value="<?=$csrf?>">
                                                <input type="hidden" name="reject" value="<?=$c['id_comment']?>">
                                                <?php foreach ($query_params as $qk=>$qv): ?><input type="hidden" name="<?=htmlspecialchars($qk)?>" value="<?=htmlspecialchars($qv)?>"><?php endforeach; ?>
                                                <button type="submit" class="dropdown_item" onclick="return confirm('Reject comment?')"><i class="fa-solid fa-ban" aria-hidden="true"></i> Reject Comment</button>
                                            </form>
                                            <?php endif; ?>
                                            <div class="dropdown_divider"></div>
                                            <form method="POST" action="comments.php" class="dropdown_form">
                                                <input type="hidden" name="csrf_token" value="<?=$csrf?>">
                                                <input type="hidden" name="delete" value="<?=$c['id_comment']?>">
                                                <?php foreach ($query_params as $qk=>$qv): ?><input type="hidden" name="<?=htmlspecialchars($qk)?>" value="<?=htmlspecialchars($qv)?>"><?php endforeach; ?>
                                                <button type="submit" class="dropdown_item dropdown_danger" onclick="return confirm('Delete this comment permanently?')"><i class="fa-solid fa-trash" aria-hidden="true"></i> Delete Comment</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7"><div class="empty_state"><i class="fa-solid fa-comments"></i><h3>No comments</h3><p>Try adjusting your filters.</p></div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php render_dashboard_pagination('comments.php', $current_page, $total_pages, $query_params); ?>
</div>


<?php require_once __DIR__ . '/inc/footer.php'; ?>
