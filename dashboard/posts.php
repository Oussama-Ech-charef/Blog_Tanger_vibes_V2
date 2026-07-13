<?php
// Posts management
require_once __DIR__ . '/init.php';
$page_title = $is_admin ? __('posts_management_title') : __('posts_my_posts_title');

$uid = current_user_id();
$message = '';
$message_type = '';

// Handle redirect messages from add_post/edit_post
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'created') { $message = __('posts_created'); $message_type = 'success'; }
    elseif ($_GET['msg'] === 'updated') { $message = __('posts_updated'); $message_type = 'success'; }
}

// Read flash message from POST redirect
if (isset($_SESSION['flash_msg'])) {
    $message = $_SESSION['flash_msg'];
    $message_type = $_SESSION['flash_type'] ?? 'success';
    unset($_SESSION['flash_msg'], $_SESSION['flash_type']);
}

// Build redirect URL keeping filter params
function posts_redirect_url() {
    $params = [];
    foreach (['q', 'page'] as $k) {
        $v = $_POST[$k] ?? $_GET[$k] ?? '';
        if ($v !== '') $params[$k] = $v;
    }
    return 'posts.php' . (!empty($params) ? '?' . http_build_query($params) : '');
}

// Approve — admin only
if (isset($_POST['approve']) && is_numeric($_POST['approve'])) {
    require_admin();
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
                $conn->prepare("INSERT INTO activity_log (action_type,description,user_id,entity_type,entity_id) VALUES ('post_approved',:d,:u,'post',:e)")->execute([':d'=>"Approved post: $pt",':u'=>$_SESSION['id_user'],':e'=>$pid]);
                $_SESSION['flash_msg'] = __('posts_approved');
                $_SESSION['flash_type'] = 'success';
                header('Location: ' . posts_redirect_url());
                exit;
            }
        } catch (PDOException $e) { error_log($e->getMessage()); $_SESSION['flash_msg'] = __('posts_error_generic'); $_SESSION['flash_type'] = 'error'; header('Location: ' . posts_redirect_url()); exit; }
    } else { $_SESSION['flash_msg'] = __('posts_error_security'); $_SESSION['flash_type'] = 'error'; header('Location: ' . posts_redirect_url()); exit; }
}

// Reject — admin only
if (isset($_POST['reject_id'])) {
    require_admin();
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
                $conn->prepare("INSERT INTO activity_log (action_type,description,user_id,entity_type,entity_id) VALUES ('post_rejected',:d,:u,'post',:e)")->execute([':d'=>"Rejected post: $pt",':u'=>$_SESSION['id_user'],':e'=>$pid]);
                $_SESSION['flash_msg'] = __('posts_rejected');
                $_SESSION['flash_type'] = 'success';
                header('Location: ' . posts_redirect_url());
                exit;
            }
        } catch (PDOException $e) { error_log($e->getMessage()); $_SESSION['flash_msg'] = __('posts_error_generic'); $_SESSION['flash_type'] = 'error'; header('Location: ' . posts_redirect_url()); exit; }
    } else { $_SESSION['flash_msg'] = __('posts_error_reason_required'); $_SESSION['flash_type'] = 'error'; header('Location: ' . posts_redirect_url()); exit; }
}

// Delete — admin can delete any post, user can delete own
if (isset($_POST['delete']) && is_numeric($_POST['delete'])) {
    if (validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $pid = (int)$_POST['delete'];
        try {
            $ownership_sql = $is_admin ? "" : " AND id_user = :uid";
            $t = $conn->prepare("SELECT title, image FROM posts WHERE id_post=:id$ownership_sql");
            $params_bind = [':id' => $pid];
            if (!$is_admin) $params_bind[':uid'] = $uid;
            $t->execute($params_bind);
            $del_post = $t->fetch(PDO::FETCH_ASSOC);
            if (!$del_post) { $_SESSION['flash_msg'] = __('posts_error_generic'); $_SESSION['flash_type'] = 'error'; header('Location: ' . posts_redirect_url()); exit; }
            $title = $del_post['title'];

            $conn->prepare("DELETE FROM comments WHERE id_post=:id")->execute([':id'=>$pid]);
            $del_where = $is_admin ? "id_post=:id" : "id_post=:id AND id_user=:uid";
            $del_params = [':id'=>$pid];
            if (!$is_admin) $del_params[':uid'] = $uid;
            $s = $conn->prepare("DELETE FROM posts WHERE $del_where");
            $s->execute($del_params);

            if ($s->rowCount()) {
                safe_delete_uploaded_image($del_post['image'] ?? null);
                $conn->prepare("INSERT INTO activity_log (action_type,description,user_id,entity_type,entity_id) VALUES ('post_deleted',:d,:u,'post',:e)")
                    ->execute([':d'=>"Deleted post: $title",':u'=>$_SESSION['id_user'],':e'=>$pid]);
                $_SESSION['flash_msg'] = __('posts_deleted');
                $_SESSION['flash_type'] = 'success';
                header('Location: ' . posts_redirect_url());
                exit;
            }
        } catch (PDOException $e) { error_log($e->getMessage()); $_SESSION['flash_msg'] = __('posts_error_generic'); $_SESSION['flash_type'] = 'error'; header('Location: ' . posts_redirect_url()); exit; }
    } else { $_SESSION['flash_msg'] = __('posts_error_security'); $_SESSION['flash_type'] = 'error'; header('Location: ' . posts_redirect_url()); exit; }
}

// Quick view from notification link
$quick_view_post = null;
if (isset($_GET['view']) && is_numeric($_GET['view'])) {
    $qv_id = (int)$_GET['view'];
    $ownership_sql = $is_admin ? "" : " AND posts.id_user = :uid";
    $qv_s = $conn->prepare("SELECT posts.*, categories.cat_name, users.user_name FROM posts INNER JOIN categories ON posts.id_category=categories.id_category INNER JOIN users ON posts.id_user=users.id_user WHERE posts.id_post=:id$ownership_sql");
    $qv_params = [':id' => $qv_id];
    if (!$is_admin) $qv_params[':uid'] = $uid;
    $qv_s->execute($qv_params);
    $quick_view_post = $qv_s->fetch(PDO::FETCH_ASSOC);
}

// Build paginated query
$per_page = isset($_GET['per_page']) ? max(4, min(50, (int)$_GET['per_page'])) : 8;
$p_page = get_valid_page('page');
$p_offset = get_offset($p_page, $per_page);

$search = trim($_GET['q'] ?? '');

$where = $is_admin ? "(users.role = 'admin' OR posts.status != :draft_status)" : "posts.id_user = :uid";
$params = $is_admin ? [':draft_status' => STATUS_DRAFT] : [':uid' => $uid];

if (!empty($search)) {
    $where .= " AND (posts.title LIKE :s OR posts.content LIKE :s2)";
    $params[':s'] = '%'.$search.'%';
    $params[':s2'] = '%'.$search.'%';
}

// Count total matching posts
$count_s = $conn->prepare("SELECT COUNT(*) FROM posts INNER JOIN categories ON posts.id_category=categories.id_category INNER JOIN users ON posts.id_user=users.id_user WHERE $where");
$count_s->execute($params);
$total_records = (int)$count_s->fetchColumn();

$total_pages = get_total_pages($total_records, $per_page);
$p_page = min($p_page, $total_pages);
$p_offset = get_offset($p_page, $per_page);

// Fetch posts for current page
$data_s = $conn->prepare("SELECT posts.*, categories.cat_name, users.user_name, users.role AS author_role FROM posts INNER JOIN categories ON posts.id_category=categories.id_category INNER JOIN users ON posts.id_user=users.id_user WHERE $where ORDER BY posts.created_at DESC LIMIT :lim OFFSET :off");
foreach ($params as $key => $val) {
    $data_s->bindValue($key, $val);
}
$data_s->bindValue(':lim', $per_page, PDO::PARAM_INT);
$data_s->bindValue(':off', $p_offset, PDO::PARAM_INT);
$data_s->execute();
$posts = $data_s->fetchAll(PDO::FETCH_ASSOC);

$query_params = ['per_page' => $per_page];
if (!empty($search)) $query_params['q'] = $search;

require_once __DIR__ . '/inc/header.php';
?>

<?php if ($quick_view_post): ?>
<?php
$qv = $quick_view_post;
$qv_close_url = posts_redirect_url();
?>
<div class="quickview_overlay" style="display:flex;">
    <div class="quickview_box">
        <div class="quickview_header">
            <h2><?= htmlspecialchars($qv['title']) ?></h2>
            <a href="<?= htmlspecialchars($qv_close_url) ?>" class="quickview_close">&times;</a>
        </div>
        <div class="quickview_body">
            <?php if (!empty($qv['image'])): ?>
            <div><img src="../<?= htmlspecialchars($qv['image']) ?>" alt="" style="max-width:100%;border-radius:8px;margin-bottom:16px;"></div>
            <?php endif; ?>
            <div class="quickview_meta">
                <span class="quickview_meta_item"><i class="fa-solid fa-tag"></i> <?= htmlspecialchars($qv['cat_name']) ?></span>
                <span class="quickview_meta_item"><i class="fa-solid fa-circle"></i> <?= ucfirst(htmlspecialchars($qv['status'])) ?></span>
                <span class="quickview_meta_item"><i class="fa-solid fa-user"></i> <?= htmlspecialchars($qv['user_name']) ?></span>
                <span class="quickview_meta_item"><i class="fa-solid fa-calendar"></i> <?= date('M j, Y', strtotime($qv['created_at'])) ?></span>
            </div>
            <?php if (!empty($qv['rejection_reason'])): ?>
            <div style="background:#FEF2F2;border:1px solid #FECACA;border-radius:8px;padding:12px 16px;font-size:13px;color:#991B1B;margin-bottom:16px;">
                <strong style="color:#EF4444;"><?= __('posts_quickview_rejection_reason') ?></strong><br><?= htmlspecialchars($qv['rejection_reason']) ?>
            </div>
            <?php endif; ?>
            <div class="quickview_content"><?= render_post_content($qv['content']) ?></div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php render_notification($message, $message_type); ?>

<form method="GET" id="filterForm" class="filters_bar">
    <div class="search_input">
        <i class="fa-solid fa-search" aria-hidden="true"></i>
        <input type="hidden" name="per_page" value="<?= $per_page ?>">
        <input type="text" name="q" placeholder="<?= __('posts_search_title_placeholder') ?>" value="<?= htmlspecialchars($search) ?>" onchange="this.form.submit()">
    </div>
    <a href="add_post.php" class="btn btn_primary btn_sm ml_auto"><i class="fa-solid fa-plus" aria-hidden="true"></i> <?= __('posts_new_post') ?></a>
</form>

<div class="card card_posts_table">
    <div class="card_header"><h2><?= $is_admin ? __('posts_all_posts') : __('posts_my_posts_title') ?></h2></div>
    <div class="card_body_no_padding">
        <div class="table_wrapper">
            <table class="data_table">
                <thead><tr><th><?= __('posts_th_title') ?></th><th><?= __('posts_th_category') ?></th><?php if ($is_admin): ?><th><?= __('posts_th_author') ?></th><th><?= __('posts_th_role') ?></th><?php endif; ?><th><?= __('posts_th_status') ?></th><th><?= __('posts_th_date') ?></th><th><?= __('posts_th_actions') ?></th></tr></thead>
                <tbody>
                    <?php if (!empty($posts)): ?>
                        <?php foreach ($posts as $p): ?>
                        <tr>
                            <td><strong><?=htmlspecialchars($p['title'])?></strong><?php if(!empty($p['rejection_reason'])):?><br><small class="rejection_reason"><?= sprintf(__('posts_rejection_reason'), htmlspecialchars($p['rejection_reason'])) ?></small><?php endif;?></td>
                            <td><?=htmlspecialchars($p['cat_name'])?></td>
                            <?php if ($is_admin): ?>
                            <td><?=htmlspecialchars($p['user_name'])?></td>
                            <td><span class="role_badge role_<?=htmlspecialchars($p['author_role'])?>"><?=ucfirst(htmlspecialchars($p['author_role']))?></span></td>
                            <?php endif; ?>
                            <td><span class="status_badge <?=htmlspecialchars($p['status'])?>"><?=ucfirst(htmlspecialchars($p['status']))?></span></td>
                            <td class="date_cell"><?=date('M j, Y',strtotime($p['created_at']))?></td>
                            <td>
                                <div class="cell_actions">
                                    <div class="action_dropdown">
                                        <button type="button" class="action_dropdown_btn" onclick="toggleDropdown(this)" aria-label="<?= __('dashboard_aria_actions') ?>"><i class="fa-solid fa-ellipsis-vertical" aria-hidden="true"></i></button>
                                        <div class="action_dropdown_menu">
                                            <a href="preview.php?id=<?= $p['id_post'] ?>" class="dropdown_item"><i class="fa-solid fa-eye" aria-hidden="true"></i> <?= __('posts_view_post') ?></a>
                                            <button type="button" class="dropdown_item" data-post-quickview='<?= json_encode(['title'=>$p['title'],'cat_name'=>$p['cat_name'],'user_name'=>$p['user_name'],'status'=>$p['status'],'content'=>render_post_content($p['content']),'image'=>$p['image'],'created_at'=>$p['created_at'],'rejection_reason'=>$p['rejection_reason']], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>'><i class="fa-solid fa-window-maximize" aria-hidden="true"></i> <?= __('posts_quick_view') ?></button>
                                            <?php if ($is_admin && $p['status'] === STATUS_PENDING): ?>
                                            <div class="dropdown_divider"></div>
                                            <form method="POST" action="posts.php" class="dropdown_form">
                                                <input type="hidden" name="csrf_token" value="<?=$csrf_token?>">
                                                <input type="hidden" name="approve" value="<?=$p['id_post']?>">
                                                <?php foreach ($query_params as $qk=>$qv): ?><input type="hidden" name="<?=htmlspecialchars($qk)?>" value="<?=htmlspecialchars($qv)?>"><?php endforeach; ?>
                                                <button type="submit" class="dropdown_item dropdown_approve"><i class="fa-solid fa-check" aria-hidden="true"></i> <?= __('posts_approve') ?></button>
                                            </form>
                                            <button type="button" class="dropdown_item dropdown_reject" data-post-id="<?=$p['id_post']?>" data-post-title="<?=htmlspecialchars($p['title'], ENT_QUOTES)?>"><i class="fa-solid fa-ban" aria-hidden="true"></i> <?= __('posts_reject') ?></button>
                                            <?php endif; ?>
                                            <?php if ((int)$p['id_user'] === $uid): ?>
                                            <div class="dropdown_divider"></div>
                                            <a href="edit_post.php?id=<?=$p['id_post']?>" class="dropdown_item"><i class="fa-solid fa-pen" aria-hidden="true"></i> <?= __('posts_edit_post') ?></a>
                                            <?php endif; ?>
                                            <?php if ($is_admin || (int)$p['id_user'] === $uid): ?>
                                            <div class="dropdown_divider"></div>
                                            <form method="POST" action="posts.php" class="dropdown_form">
                                                <input type="hidden" name="csrf_token" value="<?=$csrf_token?>">
                                                <input type="hidden" name="delete" value="<?=$p['id_post']?>">
                                                <?php foreach ($query_params as $qk=>$qv): ?><input type="hidden" name="<?=htmlspecialchars($qk)?>" value="<?=htmlspecialchars($qv)?>"><?php endforeach; ?>
                                                <button type="submit" class="dropdown_item dropdown_danger dropdown_delete"><i class="fa-solid fa-trash" aria-hidden="true"></i> <?= __('posts_delete_post') ?></button>
                                            </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="<?= $is_admin ? 7 : 5 ?>"><div class="empty_state"><i class="fa-solid fa-file-lines"></i><h3><?= __('posts_empty_title') ?></h3><p><?= __('posts_empty_desc') ?></p></div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php render_dashboard_pagination('posts.php', $p_page, $total_pages, $query_params, $per_page, $total_records); ?>
</div>

<div class="quickview_overlay" id="postQuickView">
    <div class="quickview_box">
        <div class="quickview_header">
            <h2 id="qvTitle"></h2>
            <button class="quickview_close" id="qvClose">&times;</button>
        </div>
        <div class="quickview_body">
            <div id="qvImage" style="display:none;">
                <img src="" alt="" class="quickview_image">
            </div>
            <div class="quickview_meta">
                <span class="quickview_meta_item"><i class="fa-solid fa-tag"></i> <span id="qvCategory"></span></span>
                <span class="quickview_meta_item"><i class="fa-solid fa-circle"></i> <span id="qvStatus"></span></span>
                <span class="quickview_meta_item"><i class="fa-solid fa-user"></i> <span id="qvAuthor"></span></span>
                <span class="quickview_meta_item"><i class="fa-solid fa-calendar"></i> <span id="qvDate"></span></span>
            </div>
            <div id="qvRejection" style="display:none;background:#FEF2F2;border:1px solid #FECACA;border-radius:8px;padding:12px 16px;font-size:13px;color:#991B1B;">
                <strong style="display:block;margin-bottom:4px;color:#EF4444;"><?= __('posts_quickview_rejection_reason') ?></strong>
                <span id="qvRejectionText"></span>
            </div>
            <div class="quickview_content" id="qvContent"></div>
        </div>
    </div>
</div>

<div class="modal_overlay" id="rejectModal">
    <div class="modal_box">
        <h2><?= __('posts_reject_title') ?></h2>
        <p id="rejectPostTitle"><?= __('posts_reject_desc') ?></p>
        <form method="POST" action="posts.php" class="reject_form">
            <input type="hidden" name="csrf_token" value="<?=$csrf_token?>">
            <input type="hidden" name="reject_id" id="rejectPostId" value="">
            <?php foreach ($query_params as $qk=>$qv): ?><input type="hidden" name="<?=htmlspecialchars($qk)?>" value="<?=htmlspecialchars($qv)?>"><?php endforeach; ?>
            <textarea name="rejection_reason" id="rejection_reason" placeholder="<?= __('posts_reject_placeholder') ?>" required></textarea>
            <div class="modal_actions"><button type="button" class="btn btn_secondary modal_cancel"><?= __('posts_reject_cancel') ?></button><button type="submit" class="btn btn_danger"><?= __('posts_reject_confirm') ?></button></div>
        </form>
    </div>
</div>
<?php $extra_scripts[] = '../assets/js/posts-dropdown.js'; ?>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
