<?php
$page_title = 'Posts Management';
require_once __DIR__ . '/init.php';

$message = '';
$message_type = '';

// Handle redirect messages from add_post/edit_post
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'created') { $message = 'Post created successfully.'; $message_type = 'success'; }
    elseif ($_GET['msg'] === 'updated') { $message = 'Post updated successfully.'; $message_type = 'success'; }
}

// Approve
if (isset($_POST['approve']) && is_numeric($_POST['approve'])) {
    if (validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $pid = (int)$_POST['approve'];
        try {
            $s = $conn->prepare("UPDATE posts SET status=:pub_status, id_approved_by=:admin, approved_at=NOW(), reviewed_at=NOW() WHERE id_post=:id AND status=:pend_status");
            $s->execute([':pub_status' => STATUS_PUBLISHED, ':pend_status' => STATUS_PENDING, ':admin' => $_SESSION['id_user'], ':id' => $pid]);
            if ($s->rowCount()) {
                $t = $conn->prepare("SELECT title, id_user FROM posts WHERE id_post=:id");
                $t->execute([':id' => $pid]);
                $prow = $t->fetch(PDO::FETCH_ASSOC);
                $pt = $prow['title'];
                $author_id = (int)$prow['id_user'];
                $conn->prepare("INSERT INTO activity_log (action_type,description,user_id,entity_type,entity_id) VALUES ('post_approved',:d,:u,'post',:e)")->execute([':d'=>"Approved post: $pt",':u'=>$_SESSION['id_user'],':e'=>$pid]);
                if ($author_id) {
                    $conn->prepare("INSERT INTO user_notifications (id_user,type,message,link) VALUES (:uid,'post_approved',:msg,:lnk)")
                         ->execute([':uid'=>$author_id, ':msg'=>"Your post \"$pt\" has been approved and published.", ':lnk'=>"../pages/detail.php?id=$pid"]);
                }
                $message = 'Post approved and published.';
                $message_type = 'success';
            }
        } catch (PDOException $e) { error_log($e->getMessage()); $message = 'An error occurred.'; $message_type = 'error'; }
    } else { $message = 'Invalid security token.'; $message_type = 'error'; }
}

// Reject
if (isset($_POST['reject_id'])) {
    $pid = (int)$_POST['reject_id'];
    $reason = trim($_POST['rejection_reason'] ?? '');
    if (validate_csrf_token($_POST['csrf_token'] ?? '') && !empty($reason)) {
        try {
            $s = $conn->prepare("UPDATE posts SET status=:rej_status, rejection_reason=:reason, reviewed_at=NOW() WHERE id_post=:id AND status=:pend_status");
            $s->execute([':rej_status' => STATUS_REJECTED, ':pend_status' => STATUS_PENDING, ':reason'=>$reason, ':id'=>$pid]);
            if ($s->rowCount()) {
                $t = $conn->prepare("SELECT title, id_user FROM posts WHERE id_post=:id");
                $t->execute([':id'=>$pid]);
                $prow = $t->fetch(PDO::FETCH_ASSOC);
                $pt = $prow['title'];
                $author_id = (int)$prow['id_user'];
                $conn->prepare("INSERT INTO activity_log (action_type,description,user_id,entity_type,entity_id) VALUES ('post_rejected',:d,:u,'post',:e)")->execute([':d'=>"Rejected post: $pt",':u'=>$_SESSION['id_user'],':e'=>$pid]);
                if ($author_id) {
                    $conn->prepare("INSERT INTO user_notifications (id_user,type,message,link) VALUES (:uid,'post_rejected',:msg,:lnk)")
                         ->execute([':uid'=>$author_id, ':msg'=>"Your post \"$pt\" has been rejected.", ':lnk'=>"../user_dashboard/edit_post.php?id=$pid"]);
                }
                $message = 'Post rejected.';
                $message_type = 'success';
            }
        } catch (PDOException $e) { error_log($e->getMessage()); $message = 'An error occurred.'; $message_type = 'error'; }
    } else { $message = 'Rejection reason is required.'; $message_type = 'error'; }
}

// Delete — admin can delete any post
if (isset($_POST['delete']) && is_numeric($_POST['delete'])) {
    if (validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $pid = (int)$_POST['delete'];
        try {
            // Fetch post info before deleting
            $t = $conn->prepare("SELECT title, image FROM posts WHERE id_post=:id");
            $t->execute([':id' => $pid]);
            $del_post = $t->fetch(PDO::FETCH_ASSOC);
            $title = $del_post ? $del_post['title'] : "#$pid";

            // Delete associated comments
            $conn->prepare("DELETE FROM comments WHERE id_post=:id")->execute([':id'=>$pid]);
            // Delete the post
            $s = $conn->prepare("DELETE FROM posts WHERE id_post=:id");
            $s->execute([':id'=>$pid]);

            if ($s->rowCount()) {
                // Delete associated image file
                if (!empty($del_post['image'])) {
                    $img_path = __DIR__ . '/../' . $del_post['image'];
                    if (file_exists($img_path)) { unlink($img_path); }
                }
                // Log the deletion
                $conn->prepare("INSERT INTO activity_log (action_type,description,user_id,entity_type,entity_id) VALUES ('post_deleted',:d,:u,'post',:e)")
                    ->execute([':d'=>"Deleted post: $title",':u'=>$_SESSION['id_user'],':e'=>$pid]);
                $message = 'Post deleted.';
                $message_type = 'success';
            }
        } catch (PDOException $e) { error_log($e->getMessage()); $message = 'An error occurred.'; $message_type = 'error'; }
    } else { $message = 'Invalid security token.'; $message_type = 'error'; }
}

// ── Build filter query ──────────────────────────────────────
$per_page = 8;
$page = get_valid_page();
$search = trim($_GET['q'] ?? '');
$status_filter = $_GET['status'] ?? '';
$category_filter = $_GET['category'] ?? '';
$role_filter = $_GET['role'] ?? '';
$author_filter = $_GET['author'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$sort_order = $_GET['sort'] ?? 'desc';

$where = "(users.role = 'admin' OR posts.status != :draft_status)";
$params = [':draft_status' => STATUS_DRAFT];

if (!empty($search)) {
    $where .= " AND (posts.title LIKE :s OR posts.content LIKE :s2)";
    $params[':s'] = '%'.$search.'%';
    $params[':s2'] = '%'.$search.'%';
}
if (!empty($status_filter) && in_array($status_filter, [STATUS_PUBLISHED, STATUS_PENDING, STATUS_DRAFT, STATUS_REJECTED])) {
    $where .= " AND posts.status=:st";
    $params[':st'] = $status_filter;
}
if (!empty($category_filter) && is_numeric($category_filter)) {
    $where .= " AND posts.id_category=:cat";
    $params[':cat'] = (int)$category_filter;
}
if (!empty($role_filter) && in_array($role_filter, ['admin', 'user'])) {
    $where .= " AND users.role=:rl";
    $params[':rl'] = $role_filter;
}
if (!empty($author_filter) && is_numeric($author_filter)) {
    $where .= " AND posts.id_user=:auth";
    $params[':auth'] = (int)$author_filter;
}
if (!empty($date_from)) {
    $where .= " AND posts.created_at >= :date_from";
    $params[':date_from'] = $date_from . ' 00:00:00';
}
if (!empty($date_to)) {
    $where .= " AND posts.created_at <= :date_to";
    $params[':date_to'] = $date_to . ' 23:59:59';
}

$order_dir = in_array($sort_order, ['asc', 'desc']) ? strtoupper($sort_order) : 'DESC';

$count_s = $conn->prepare("SELECT COUNT(*) FROM posts INNER JOIN users ON posts.id_user=users.id_user WHERE $where");
$count_s->execute($params);
$total_records = (int)$count_s->fetchColumn();
$total_pages = get_total_pages($total_records, $per_page);
$current_page = min($page, $total_pages);
$offset = get_offset($current_page, $per_page);

$data_s = $conn->prepare("SELECT posts.*, categories.cat_name, users.user_name, users.role AS author_role FROM posts INNER JOIN categories ON posts.id_category=categories.id_category INNER JOIN users ON posts.id_user=users.id_user WHERE $where ORDER BY posts.created_at $order_dir LIMIT :lim OFFSET :off");
$int_params = [':cat', ':auth'];
foreach ($params as $k => $v) {
    $data_s->bindValue($k, $v, in_array($k, $int_params) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$data_s->bindValue(':lim', $per_page, PDO::PARAM_INT);
$data_s->bindValue(':off', $offset, PDO::PARAM_INT);
$data_s->execute();
$posts = $data_s->fetchAll(PDO::FETCH_ASSOC);
$categories = $conn->query("SELECT * FROM categories ORDER BY cat_name ASC")->fetchAll(PDO::FETCH_ASSOC);
$authors = $conn->query("SELECT DISTINCT u.id_user, u.user_name, u.role FROM users u INNER JOIN posts p ON p.id_user = u.id_user ORDER BY u.user_name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Build query params for pagination links
$query_params = [];
if (!empty($search)) $query_params['q'] = $search;
if (!empty($status_filter)) $query_params['status'] = $status_filter;
if (!empty($category_filter)) $query_params['category'] = $category_filter;
if (!empty($role_filter)) $query_params['role'] = $role_filter;
if (!empty($author_filter)) $query_params['author'] = $author_filter;
if (!empty($date_from)) $query_params['date_from'] = $date_from;
if (!empty($date_to)) $query_params['date_to'] = $date_to;
if (!empty($sort_order) && $sort_order !== 'desc') $query_params['sort'] = $sort_order;

require_once __DIR__ . '/inc/header.php';
?>

<?php render_notification($message, $message_type); ?>

<form method="GET" id="filterForm" class="filters_bar">
    <div class="search_input">
        <i class="fa-solid fa-search" aria-hidden="true"></i>
        <input type="text" name="q" placeholder="Search title or content..." value="<?= htmlspecialchars($search) ?>" onchange="this.form.submit()">
    </div>
    <select name="status" class="filter_select" onchange="this.form.submit()">
        <option value="">All Status</option>
        <?php foreach ([STATUS_PUBLISHED, STATUS_PENDING, STATUS_DRAFT, STATUS_REJECTED] as $s): ?>
            <option value="<?=$s?>" <?=$status_filter===$s?'selected':''?>><?=ucfirst($s)?></option>
        <?php endforeach; ?>
    </select>
    <select name="category" class="filter_select" onchange="this.form.submit()">
        <option value="">All Categories</option>
        <?php foreach ($categories as $c): ?>
            <option value="<?=$c['id_category']?>" <?=$category_filter==$c['id_category']?'selected':''?>><?=htmlspecialchars($c['cat_name'])?></option>
        <?php endforeach; ?>
    </select>
    <select name="role" class="filter_select" onchange="this.form.submit()">
        <option value="">All Roles</option>
        <option value="admin" <?=$role_filter==='admin'?'selected':''?>>Admin</option>
        <option value="user" <?=$role_filter==='user'?'selected':''?>>User</option>
    </select>
    <select name="author" class="filter_select" onchange="this.form.submit()">
        <option value="">All Authors</option>
        <?php foreach ($authors as $a): ?>
            <option value="<?=$a['id_user']?>" <?=$author_filter==$a['id_user']?'selected':''?>><?=htmlspecialchars($a['user_name'])?> (<?=ucfirst($a['role'])?>)</option>
        <?php endforeach; ?>
    </select>
    <input type="date" name="date_from" class="filter_select" value="<?= htmlspecialchars($date_from) ?>" onchange="this.form.submit()" style="width:155px;flex-shrink:0;" placeholder="From date">
    <input type="date" name="date_to" class="filter_select" value="<?= htmlspecialchars($date_to) ?>" onchange="this.form.submit()" style="width:155px;flex-shrink:0;" placeholder="To date">
    <select name="sort" class="filter_select" onchange="this.form.submit()">
        <option value="desc" <?=$sort_order==='desc'?'selected':''?>>Newest First</option>
        <option value="asc" <?=$sort_order==='asc'?'selected':''?>>Oldest First</option>
    </select>
    <a href="add_post.php" class="btn btn_primary btn_sm" style="margin-left:auto;"><i class="fa-solid fa-plus" aria-hidden="true"></i> New Post</a>
</form>

<div class="card">
    <div class="card_header"><h2>All Posts (<?= $total_records ?>)</h2></div>
    <div class="card_body_no_padding">
        <div class="table_wrapper">
            <table class="data_table">
                <thead><tr><th>Title</th><th>Category</th><th>Author</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php if (!empty($posts)): ?>
                        <?php foreach ($posts as $p): ?>
                        <tr>
                            <td><strong><?=htmlspecialchars($p['title'])?></strong><?php if(!empty($p['rejection_reason'])):?><br><small style="color:var(--db-danger-text);font-size:11px;">Reason: <?=htmlspecialchars($p['rejection_reason'])?></small><?php endif;?></td>
                            <td><?=htmlspecialchars($p['cat_name'])?></td>
                            <td>
                                <?=htmlspecialchars($p['user_name'])?>
                                <span class="role_badge role_<?=$p['author_role']?>"><?=ucfirst($p['author_role'])?></span>
                            </td>
                            <td><span class="status_badge <?=$p['status']?>"><?=ucfirst(htmlspecialchars($p['status']))?></span></td>
                            <td style="white-space:nowrap;color:var(--db-text-secondary);font-size:13px;"><?=date('M j, Y',strtotime($p['created_at']))?></td>
                            <td>
                                <div class="cell_actions">
                                    <div class="action_dropdown">
                                        <button type="button" class="action_dropdown_btn" aria-label="Actions"><i class="fa-solid fa-ellipsis-vertical" aria-hidden="true"></i></button>
                                        <div class="action_dropdown_menu">
                                            <button type="button" class="dropdown_item" data-post-quickview='<?= json_encode(['title'=>$p['title'],'cat_name'=>$p['cat_name'],'user_name'=>$p['user_name'],'status'=>$p['status'],'content'=>$p['content'],'image'=>$p['image'],'created_at'=>$p['created_at'],'rejection_reason'=>$p['rejection_reason']], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>'><i class="fa-solid fa-eye" aria-hidden="true"></i> View Post</button>
                                            <?php if ($p['status'] === STATUS_PENDING): ?>
                                            <div class="dropdown_divider"></div>
                                            <form method="POST" action="posts.php" class="dropdown_form">
                                                <input type="hidden" name="csrf_token" value="<?=$csrf_token?>">
                                                <input type="hidden" name="approve" value="<?=$p['id_post']?>">
                                                <?php foreach ($query_params as $qk=>$qv): ?><input type="hidden" name="<?=htmlspecialchars($qk)?>" value="<?=htmlspecialchars($qv)?>"><?php endforeach; ?>
                                                <button type="submit" class="dropdown_item dropdown_approve"><i class="fa-solid fa-check" aria-hidden="true"></i> Approve</button>
                                            </form>
                                            <button type="button" class="dropdown_item dropdown_reject" data-post-id="<?=$p['id_post']?>" data-post-title="<?=htmlspecialchars($p['title'], ENT_QUOTES)?>"><i class="fa-solid fa-ban" aria-hidden="true"></i> Reject</button>
                                            <?php endif; ?>
                                            <?php if ($p['author_role'] === 'admin'): ?>
                                            <div class="dropdown_divider"></div>
                                            <a href="edit_post.php?id=<?=$p['id_post']?>" class="dropdown_item"><i class="fa-solid fa-pen" aria-hidden="true"></i> Edit Post</a>
                                            <?php endif; ?>
                                            <div class="dropdown_divider"></div>
                                            <form method="POST" action="posts.php" class="dropdown_form">
                                                <input type="hidden" name="csrf_token" value="<?=$csrf_token?>">
                                                <input type="hidden" name="delete" value="<?=$p['id_post']?>">
                                                <?php foreach ($query_params as $qk=>$qv): ?><input type="hidden" name="<?=htmlspecialchars($qk)?>" value="<?=htmlspecialchars($qv)?>"><?php endforeach; ?>
                                                <button type="submit" class="dropdown_item dropdown_danger dropdown_delete"><i class="fa-solid fa-trash" aria-hidden="true"></i> Delete Post</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6"><div class="empty_state"><i class="fa-solid fa-file-lines"></i><h3>No posts found</h3><p>Try adjusting your filters.</p></div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($total_pages > 1): ?>
    <div style="padding:16px 24px;border-top:1px solid var(--db-card-border);">
        <div class="dashboard_pagination">
            <?php $u = http_build_query($query_params); $pre = !empty($u) ? '?'.$u.'&page=' : '?page='; ?>
            <?php if ($current_page > 1): ?><a href="posts.php<?=$pre.($current_page-1)?>" class="page_btn" aria-label="Previous page"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></a><?php endif; ?>
            <?php for ($i=1;$i<=$total_pages;$i++): ?><a href="posts.php<?=$pre.$i?>" class="page_btn <?=$i===$current_page?'active':''?>"><?=$i?></a><?php endfor; ?>
            <?php if ($current_page < $total_pages): ?><a href="posts.php<?=$pre.($current_page+1)?>" class="page_btn" aria-label="Next page"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></a><?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="modal_overlay" id="rejectModal">
    <div class="modal_box">
        <h2>Reject Post</h2>
        <p id="rejectPostTitle">Provide a reason.</p>
        <form method="POST" action="posts.php" class="reject_form">
            <input type="hidden" name="csrf_token" value="<?=$csrf_token?>">
            <input type="hidden" name="reject_id" id="rejectPostId" value="">
            <?php foreach ($query_params as $qk=>$qv): ?><input type="hidden" name="<?=htmlspecialchars($qk)?>" value="<?=htmlspecialchars($qv)?>"><?php endforeach; ?>
            <textarea name="rejection_reason" id="rejection_reason" placeholder="Explain what needs to be changed..." required></textarea>
            <div class="modal_actions"><button type="button" class="btn btn_secondary modal_cancel">Cancel</button><button type="submit" class="btn btn_danger">Reject Post</button></div>
        </form>
    </div>
</div>
<script src="../assets/js/posts-dropdown.js"></script>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
