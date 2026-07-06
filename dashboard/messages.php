<?php
require_once __DIR__ . '/init.php';
$page_title = __('messages_inbox_title');
$message = ''; $message_type = '';

$csrf = get_csrf_token();

// Mark as read
if (isset($_POST['mark_read']) && is_numeric($_POST['mark_read'])) {
    if (validate_csrf_token($_POST['csrf_token'] ?? '')) {
        try {
            $conn->prepare("UPDATE contact_messages SET is_read=1 WHERE id_message=:id")->execute([':id'=>(int)$_POST['mark_read']]);
        } catch (PDOException $e) { error_log($e->getMessage()); }
    }
}

// Delete
if (isset($_POST['delete']) && is_numeric($_POST['delete'])) {
    if (validate_csrf_token($_POST['csrf_token'] ?? '')) {
        try {
            $conn->prepare("DELETE FROM contact_messages WHERE id_message=:id")->execute([':id'=>(int)$_POST['delete']]);
            $message = __('messages_deleted'); $message_type = 'success';
        } catch (PDOException $e) { error_log($e->getMessage()); $message = __('messages_error_generic'); $message_type = 'error'; }
    } else { $message = __('posts_error_security'); $message_type = 'error'; }
}

// View single (must happen after read/delete to pick up changes)
$view_message = null;
if (isset($_GET['view']) && is_numeric($_GET['view'])) {
    $s = $conn->prepare("SELECT * FROM contact_messages WHERE id_message=:id");
    $s->execute([':id'=>(int)$_GET['view']]);
    $view_message = $s->fetch(PDO::FETCH_ASSOC);
    // Auto-mark as read when viewing
    if ($view_message && empty((int)$view_message['is_read'])) {
        try {
            $conn->prepare("UPDATE contact_messages SET is_read=1 WHERE id_message=:id")->execute([':id'=> (int)$_GET['view']]);
            $view_message['is_read'] = 1;
        } catch (PDOException $e) { error_log($e->getMessage()); }
    }
}

// Listing
$per_page = 20;
$page = get_valid_page();
$search = trim($_GET['q'] ?? '');
$where = "1=1"; $params = [];
if (!empty($search)) { $where .= " AND (full_name LIKE :s OR email LIKE :s2 OR subject LIKE :s3)"; $params[':s'] = '%'.$search.'%'; $params[':s2'] = '%'.$search.'%'; $params[':s3'] = '%'.$search.'%'; }

$cs = $conn->prepare("SELECT COUNT(*) FROM contact_messages WHERE $where");
$cs->execute($params);
$total_records = (int)$cs->fetchColumn();
$total_pages = get_total_pages($total_records, $per_page);
$current_page = min($page, $total_pages);
$offset = get_offset($current_page, $per_page);

$ds = $conn->prepare("SELECT * FROM contact_messages WHERE $where ORDER BY created_at DESC LIMIT :lim OFFSET :off");
foreach ($params as $k=>$v) $ds->bindValue($k, $v, PDO::PARAM_STR);
$ds->bindValue(':lim', $per_page, PDO::PARAM_INT);
$ds->bindValue(':off', $offset, PDO::PARAM_INT);
$ds->execute();
$messages = $ds->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/inc/header.php';
?>

<?php render_notification($message, $message_type); ?>

<?php if ($view_message): ?>
<div class="card">
    <div class="card_header">
        <h2><?= sprintf(__('messages_view_from'), htmlspecialchars($view_message['full_name'])) ?></h2>
        <a href="messages.php<?=!empty($search)?'?q='.urlencode($search):''?>" class="btn btn_secondary btn_sm"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> <?= __('messages_back') ?></a>
    </div>
    <div class="card_body">
        <div style="display:grid;gap:20px;">
            <div><div class="detail_label"><?= __('messages_label_name') ?></div><div class="detail_value"><?=htmlspecialchars($view_message['full_name'])?></div></div>
            <div><div class="detail_label"><?= __('messages_label_email') ?></div><div class="detail_value"><a href="mailto:<?=htmlspecialchars($view_message['email'])?>" class="view_link"><?=htmlspecialchars($view_message['email'])?></a></div></div>
            <div><div class="detail_label"><?= __('messages_label_subject') ?></div><div class="detail_value"><?=htmlspecialchars($view_message['subject'])?></div></div>
            <div><div class="detail_label"><?= __('messages_label_date') ?></div><div class="detail_value"><?=date('F j, Y g:i A',strtotime($view_message['created_at']))?></div></div>
            <div><div class="detail_label"><?= __('messages_label_message') ?></div><div class="detail_value" style="background:#F8FAFC;padding:16px;border-radius:8px;border:1px solid var(--db-card-border);line-height:1.7;white-space:pre-wrap;"><?=htmlspecialchars($view_message['message'])?></div></div>
            <div style="display:flex;gap:12px;padding-top:12px;border-top:1px solid var(--db-card-border);">
                <a href="mailto:<?=htmlspecialchars($view_message['email'])?>?subject=Re: <?=htmlspecialchars($view_message['subject'])?>" class="btn btn_primary" target="_blank" rel="noopener"><i class="fa-solid fa-reply"></i> <?= __('messages_btn_reply') ?></a>
                <form method="POST" action="messages.php" class="inline_form">
                    <input type="hidden" name="csrf_token" value="<?=$csrf?>">
                    <input type="hidden" name="delete" value="<?=$view_message['id_message']?>">
                    <?php if (!empty($search)): ?><input type="hidden" name="q" value="<?=htmlspecialchars($search)?>"><?php endif; ?>
                    <button type="submit" class="btn btn_danger" onclick="return confirm(__('messages_confirm_delete'))"><i class="fa-solid fa-trash"></i> <?= __('messages_btn_delete') ?></button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php else: ?>

<div class="filters_bar">
    <div class="search_input">
        <i class="fa-solid fa-search" aria-hidden="true"></i>
        <form method="GET" id="sf"><input type="text" name="q" placeholder="<?= __('messages_search_placeholder') ?>" value="<?=htmlspecialchars($search)?>" onchange="document.getElementById('sf').submit()"></form>
    </div>
    <span style="font-size:14px;color:var(--db-text-secondary);font-weight:500;"><?= sprintf(__('messages_count'), $total_records) ?></span>
</div>

<div class="card">
    <div class="card_header"><h2><?= __('messages_table_title') ?></h2></div>
    <div class="card_body_no_padding">
        <div class="table_wrapper">
            <table class="data_table">
                <thead><tr><th><?= __('messages_th_from') ?></th><th><?= __('messages_th_email') ?></th><th><?= __('messages_th_subject') ?></th><th><?= __('messages_th_status') ?></th><th><?= __('messages_th_date') ?></th><th><?= __('messages_th_actions') ?></th></tr></thead>
                <tbody>
                    <?php if (!empty($messages)): ?>
                        <?php foreach ($messages as $m): ?>
                        <tr class="<?=empty($m['is_read'])?'unread':''?>">
                            <td><div class="flex_center"><span class="user_avatar <?=avatar_color($m['full_name'])?>"><?=avatar_initials($m['full_name'])?></span><strong><?=htmlspecialchars($m['full_name'])?></strong></div></td>
                            <td><a href="mailto:<?=htmlspecialchars($m['email'])?>" class="view_link"><?=htmlspecialchars($m['email'])?></a></td>
                            <td><a href="messages.php?view=<?=$m['id_message']?><?=!empty($search)?'&q='.urlencode($search):''?>" style="color:var(--db-text-primary);font-weight:500;"><?=htmlspecialchars(truncate_text($m['subject'],60))?><?php if(empty($m['is_read'])):?> <span class="unread_dot"></span><?php endif;?></a></td>
                            <td><span class="status_badge status_<?=empty($m['is_read'])?'pending':'approved'?>"><?=empty($m['is_read'])?__('messages_status_unread'):__('messages_status_read')?></span></td>
                            <td class="date_cell"><?=date('M j, Y',strtotime($m['created_at']))?></td>
                            <td><div class="cell_actions"><a href="messages.php?view=<?=$m['id_message']?><?=!empty($search)?'&q='.urlencode($search):''?>" class="btn_small btn_secondary"><i class="fa-solid fa-eye" aria-hidden="true"></i> <?= __('messages_btn_read') ?></a><form method="POST" action="messages.php" class="inline_form">
    <input type="hidden" name="csrf_token" value="<?=$csrf?>">
    <input type="hidden" name="delete" value="<?=$m['id_message']?>">
    <?php if (!empty($search)): ?><input type="hidden" name="q" value="<?=htmlspecialchars($search)?>"><?php endif; ?>
    <button type="submit" class="btn_small btn_danger" onclick="return confirm(__('messages_confirm_delete'))" aria-label="<?= __('dashboard_aria_delete_message') ?>"><i class="fa-solid fa-trash"></i></button>
</form></div></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6"><div class="empty_state"><i class="fa-solid fa-envelope-open-text"></i><h3><?= __('messages_empty_title') ?></h3><p><?= !empty($search) ? __('messages_empty_no_matches') : __('messages_empty_none') ?></p></div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php $query_params = !empty($search) ? ['q' => $search] : []; ?>
    <?php render_dashboard_pagination('messages.php', $current_page, $total_pages, $query_params); ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
