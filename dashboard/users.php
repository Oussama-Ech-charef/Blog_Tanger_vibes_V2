<?php
$page_title = 'User Management';
require_once __DIR__ . '/init.php';
$message = ''; $message_type = '';

$csrf = get_csrf_token();

// Verify CSRF helper
$_csrf_token = $_POST['csrf_token'] ?? '';

// Change role
if (isset($_POST['role'],$_POST['uid']) && is_numeric($_POST['uid'])) {
    if (validate_csrf_token($_csrf_token)) {
        $uid = (int)$_POST['uid']; $nr = $_POST['role']==='admin'?'admin':'user';
        if ($uid !== (int)$_SESSION['id_user']) {
            try {
                $conn->prepare("UPDATE users SET role=:r WHERE id_user=:id")->execute([':r'=>$nr,':id'=>$uid]);
                $message = "Role updated to $nr."; $message_type = 'success';
            } catch (PDOException $e) { error_log($e->getMessage()); $message = 'Error.'; $message_type = 'error'; }
        } else { $message = 'Cannot change your own role.'; $message_type = 'error'; }
    } else { $message = 'Invalid security token.'; $message_type = 'error'; }
}

// Activate / Deactivate
if (isset($_POST['activate']) && is_numeric($_POST['activate'])) {
    if (validate_csrf_token($_csrf_token)) {
        $uid = (int)$_POST['activate'];
        if ($uid !== (int)$_SESSION['id_user']) {
            try {
                $conn->prepare("UPDATE users SET is_active=1 WHERE id_user=:id")->execute([':id'=>$uid]);
                $message = 'User activated.'; $message_type = 'success';
            } catch (PDOException $e) { error_log($e->getMessage()); $message = 'Error.'; $message_type = 'error'; }
        } else { $message = 'Cannot deactivate your own account.'; $message_type = 'error'; }
    } else { $message = 'Invalid security token.'; $message_type = 'error'; }
}
if (isset($_POST['deactivate']) && is_numeric($_POST['deactivate'])) {
    if (validate_csrf_token($_csrf_token)) {
        $uid = (int)$_POST['deactivate'];
        if ($uid !== (int)$_SESSION['id_user']) {
            try {
                $conn->prepare("UPDATE users SET is_active=0 WHERE id_user=:id")->execute([':id'=>$uid]);
                $message = 'User deactivated.'; $message_type = 'success';
            } catch (PDOException $e) { error_log($e->getMessage()); $message = 'Error.'; $message_type = 'error'; }
        } else { $message = 'Cannot deactivate your own account.'; $message_type = 'error'; }
    } else { $message = 'Invalid security token.'; $message_type = 'error'; }
}

// Delete
if (isset($_POST['delete']) && is_numeric($_POST['delete'])) {
    if (validate_csrf_token($_csrf_token)) {
        $uid = (int)$_POST['delete'];
        if ($uid !== (int)$_SESSION['id_user']) {
            try {
                $conn->prepare("DELETE FROM comments WHERE id_post IN (SELECT id_post FROM posts WHERE id_user=:u)")->execute([':u'=>$uid]);
                $conn->prepare("DELETE FROM posts WHERE id_user=:u")->execute([':u'=>$uid]);
                $conn->prepare("DELETE FROM users WHERE id_user=:id")->execute([':id'=>$uid]);
                $message = 'User deleted.'; $message_type = 'success';
            } catch (PDOException $e) { error_log($e->getMessage()); $message = 'Error.'; $message_type = 'error'; }
        } else { $message = 'Cannot delete your own account.'; $message_type = 'error'; }
    } else { $message = 'Invalid security token.'; $message_type = 'error'; }
}

// ── Filter vars ─────────────────────────────────────────────
$per_page = 20;
$page = get_valid_page();
$search = trim($_GET['q'] ?? '');
$role_filter = $_GET['role'] ?? '';
$status_filter = $_GET['status'] ?? '';

$where = "1=1";
$params = [];

if (!empty($search)) {
    $where .= " AND (user_name LIKE :s OR email LIKE :s2)";
    $params[':s'] = '%'.$search.'%';
    $params[':s2'] = '%'.$search.'%';
}
if (!empty($role_filter) && in_array($role_filter, ['admin', 'user'])) {
    $where .= " AND role=:rl";
    $params[':rl'] = $role_filter;
}
if ($status_filter === 'active') {
    $where .= " AND is_active=1";
} elseif ($status_filter === 'inactive') {
    $where .= " AND is_active=0";
}

// ── Count & Fetch ───────────────────────────────────────────
$cs = $conn->prepare("SELECT COUNT(*) FROM users WHERE $where");
$cs->execute($params);
$total_records = (int)$cs->fetchColumn();
$total_pages = get_total_pages($total_records, $per_page);
$current_page = min($page, $total_pages);
$offset = get_offset($current_page, $per_page);

$ds = $conn->prepare("SELECT *, (SELECT COUNT(*) FROM posts WHERE posts.id_user=users.id_user) as post_count FROM users WHERE $where ORDER BY created_at DESC LIMIT :lim OFFSET :off");
$int_params = [];
foreach ($params as $k=>$v) $ds->bindValue($k, $v, in_array($k, $int_params) ? PDO::PARAM_INT : PDO::PARAM_STR);
$ds->bindValue(':lim', $per_page, PDO::PARAM_INT);
$ds->bindValue(':off', $offset, PDO::PARAM_INT);
$ds->execute();
$users = $ds->fetchAll(PDO::FETCH_ASSOC);

// ── Query params for pagination ─────────────────────────────
$query_params = [];
if (!empty($search)) $query_params['q'] = $search;
if (!empty($role_filter)) $query_params['role'] = $role_filter;
if (!empty($status_filter)) $query_params['status'] = $status_filter;

require_once __DIR__ . '/inc/header.php';
?>

<?php render_notification($message, $message_type); ?>

<form method="GET" id="filterForm" class="filters_bar">
    <div class="search_input">
        <i class="fa-solid fa-search" aria-hidden="true"></i>
        <input type="text" name="q" placeholder="Search by name or email..." value="<?=htmlspecialchars($search)?>" onchange="this.form.submit()">
    </div>
    <select name="role" class="filter_select" onchange="this.form.submit()">
        <option value="">All Roles</option>
        <option value="admin" <?=$role_filter==='admin'?'selected':''?>>Administrators</option>
        <option value="user" <?=$role_filter==='user'?'selected':''?>>Normal Users</option>
    </select>
    <select name="status" class="filter_select" onchange="this.form.submit()">
        <option value="">All Status</option>
        <option value="active" <?=$status_filter==='active'?'selected':''?>>Active</option>
        <option value="inactive" <?=$status_filter==='inactive'?'selected':''?>>Inactive</option>
    </select>
    <span style="font-size:14px;color:var(--db-text-secondary);font-weight:500;margin-left:auto;"><?=$total_records?> user(s)</span>
    <?php if (!empty($search) || !empty($role_filter) || !empty($status_filter)): ?>
    <a href="users.php" class="btn_small btn_secondary" style="text-decoration:none;display:inline-flex;align-items:center;gap:4px;white-space:nowrap;"><i class="fa-solid fa-times" aria-hidden="true"></i> Clear</a>
    <?php endif; ?>
</form>

<div class="card">
    <div class="card_header"><h2>All Users</h2></div>
    <div class="card_body_no_padding">
        <div class="table_wrapper">
            <table class="data_table">
                <thead><tr><th>User</th><th>Email</th><th>Role</th><th>Status</th><th>Posts</th><th>Registered</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $u): ?>
                        <tr>
                            <td><div style="display:flex;align-items:center;gap:10px;"><span class="user_avatar <?=avatar_color($u['user_name'])?>"><?=avatar_initials($u['user_name'])?></span><div><strong><?=htmlspecialchars($u['user_name'])?></strong><?php if((int)$u['id_user']===(int)$_SESSION['id_user']):?><br><span style="font-size:11px;color:var(--db-primary);font-weight:600;">(You)</span><?php endif;?></div></div></td>
                            <td><span style="color:var(--db-text-secondary);"><?=htmlspecialchars($u['email'])?></span></td>
                            <td><span class="role_badge <?=$u['role']?>"><?=ucfirst(htmlspecialchars($u['role']))?></span></td>
                            <td><span class="status_badge status_<?=!empty($u['is_active'])?'approved':'rejected'?>"><?=!empty($u['is_active'])?'Active':'Inactive'?></span></td>
                            <td><span style="font-weight:600;"><?=(int)$u['post_count']?></span></td>
                            <td style="white-space:nowrap;color:var(--db-text-secondary);font-size:13px;"><?=date('M j, Y',strtotime($u['created_at']))?></td>
                            <td><div class="cell_actions">
                                <?php if ((int)$u['id_user'] !== (int)$_SESSION['id_user']): ?>
                                    <?php if ($u['role']==='admin'): ?>
                                        <form method="POST" action="users.php" style="display:inline">
                                            <input type="hidden" name="csrf_token" value="<?=$csrf?>">
                                            <input type="hidden" name="role" value="user">
                                            <input type="hidden" name="uid" value="<?=$u['id_user']?>">
                                            <?php foreach ($query_params as $qk=>$qv): ?><input type="hidden" name="<?=htmlspecialchars($qk)?>" value="<?=htmlspecialchars($qv)?>"><?php endforeach; ?>
                                            <button type="submit" class="btn_small btn_secondary" onclick="return confirm('Demote to user?')"><i class="fa-solid fa-user" aria-hidden="true"></i> Demote</button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" action="users.php" style="display:inline">
                                            <input type="hidden" name="csrf_token" value="<?=$csrf?>">
                                            <input type="hidden" name="role" value="admin">
                                            <input type="hidden" name="uid" value="<?=$u['id_user']?>">
                                            <?php foreach ($query_params as $qk=>$qv): ?><input type="hidden" name="<?=htmlspecialchars($qk)?>" value="<?=htmlspecialchars($qv)?>"><?php endforeach; ?>
                                            <button type="submit" class="btn_small btn_success" onclick="return confirm('Promote to admin?')"><i class="fa-solid fa-shield" aria-hidden="true"></i> Make Admin</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if (!empty($u['is_active'])): ?>
                                        <form method="POST" action="users.php" style="display:inline">
                                            <input type="hidden" name="csrf_token" value="<?=$csrf?>">
                                            <input type="hidden" name="deactivate" value="<?=$u['id_user']?>">
                                            <?php foreach ($query_params as $qk=>$qv): ?><input type="hidden" name="<?=htmlspecialchars($qk)?>" value="<?=htmlspecialchars($qv)?>"><?php endforeach; ?>
                                            <button type="submit" class="btn_small btn_warning" onclick="return confirm('Deactivate user?')" aria-label="Deactivate user"><i class="fa-solid fa-pause"></i></button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" action="users.php" style="display:inline">
                                            <input type="hidden" name="csrf_token" value="<?=$csrf?>">
                                            <input type="hidden" name="activate" value="<?=$u['id_user']?>">
                                            <?php foreach ($query_params as $qk=>$qv): ?><input type="hidden" name="<?=htmlspecialchars($qk)?>" value="<?=htmlspecialchars($qv)?>"><?php endforeach; ?>
                                            <button type="submit" class="btn_small btn_success" onclick="return confirm('Activate user?')" aria-label="Activate user"><i class="fa-solid fa-play"></i></button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="POST" action="users.php" style="display:inline">
                                        <input type="hidden" name="csrf_token" value="<?=$csrf?>">
                                        <input type="hidden" name="delete" value="<?=$u['id_user']?>">
                                        <?php foreach ($query_params as $qk=>$qv): ?><input type="hidden" name="<?=htmlspecialchars($qk)?>" value="<?=htmlspecialchars($qv)?>"><?php endforeach; ?>
                                        <button type="submit" class="btn_small btn_danger" onclick="return confirm('Delete user and all posts?')" aria-label="Delete user"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                <?php else: ?>
                                    <span style="font-size:12px;color:var(--db-text-muted);">Current user</span>
                                <?php endif; ?>
                            </div></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7"><div class="empty_state"><i class="fa-solid fa-users"></i><h3>No users found</h3><p>Try adjusting your filters.</p></div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($total_pages > 1): ?>
    <div style="padding:16px 24px;border-top:1px solid var(--db-card-border);">
        <div class="dashboard_pagination">
            <?php $u = http_build_query($query_params); $pre = !empty($u) ? '?'.$u.'&page=' : '?page='; ?>
            <?php if ($current_page > 1): ?><a href="users.php<?=$pre.($current_page-1)?>" class="page_btn" aria-label="Previous page"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></a><?php endif; ?>
            <?php for ($i=1;$i<=$total_pages;$i++): ?><a href="users.php<?=$pre.$i?>" class="page_btn <?=$i===$current_page?'active':''?>"><?=$i?></a><?php endfor; ?>
            <?php if ($current_page < $total_pages): ?><a href="users.php<?=$pre.($current_page+1)?>" class="page_btn" aria-label="Next page"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></a><?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
