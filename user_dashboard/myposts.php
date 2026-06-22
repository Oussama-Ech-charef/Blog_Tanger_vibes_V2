<?php
$page_title = 'My Posts';
require_once __DIR__ . '/init.php';

$message = '';
$message_type = '';

// Redirect to add_post
if (isset($_GET['action']) && $_GET['action'] === 'new') {
    header('Location: add_post.php');
    exit();
}

// Handle redirect messages from submit_post / edit_my_post
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'submitted') { $message = 'Post submitted for review.'; $message_type = 'success'; }
    elseif ($_GET['msg'] === 'saved') { $message = 'Post saved as draft.'; $message_type = 'success'; }
    elseif ($_GET['msg'] === 'updated') { $message = 'Post updated.'; $message_type = 'success'; }
    elseif ($_GET['msg'] === 'resubmitted') { $message = 'Post resubmitted for review.'; $message_type = 'success'; }
    elseif ($_GET['msg'] === 'deleted') { $message = 'Post deleted.'; $message_type = 'success'; }
}

// Delete own post (any status)
if (isset($_POST['delete']) && is_numeric($_POST['delete'])) {
    $pid = (int)$_POST['delete'];
    if (validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $s = $conn->prepare("SELECT id_user FROM posts WHERE id_post=:id");
        $s->execute([':id' => $pid]);
        $p = $s->fetch(PDO::FETCH_ASSOC);
        if ($p && (int)$p['id_user'] === $uid) {
            $conn->prepare("DELETE FROM comments WHERE id_post=:id")->execute([':id' => $pid]);
            $conn->prepare("DELETE FROM posts WHERE id_post=:id AND id_user=:uid")->execute([':id' => $pid, ':uid' => $uid]);
            $message = 'Post deleted.';
            $message_type = 'success';
        } else {
            $message = 'You can only delete your own posts.';
            $message_type = 'error';
        }
    } else { $message = 'Invalid security token.'; $message_type = 'error'; }
}

// Pagination
$per_page = 15;
$page = get_valid_page();

// Count total user posts
$cs = $conn->prepare("SELECT COUNT(*) FROM posts WHERE id_user=:uid");
$cs->execute([':uid' => $uid]);
$total_records = (int)$cs->fetchColumn();
$total_pages = get_total_pages($total_records, $per_page);
$current_page = min($page, $total_pages);
$offset = get_offset($current_page, $per_page);

// Fetch user's posts with pagination
$ds = $conn->prepare("SELECT posts.*, categories.cat_name FROM posts INNER JOIN categories ON posts.id_category=categories.id_category WHERE posts.id_user=:uid ORDER BY posts.created_at DESC LIMIT :lim OFFSET :off");
$ds->bindValue(':uid', $uid, PDO::PARAM_INT);
$ds->bindValue(':lim', $per_page, PDO::PARAM_INT);
$ds->bindValue(':off', $offset, PDO::PARAM_INT);
$ds->execute();
$my_posts = $ds->fetchAll(PDO::FETCH_ASSOC);

// Group by status
$grouped = ['draft' => [], 'pending' => [], 'published' => [], 'rejected' => []];
foreach ($my_posts as $p) {
    $grouped[$p['status']][] = $p;
}

require_once __DIR__ . '/inc/header.php';
?>

<?php render_notification($message, $message_type); ?>

<div class="filters_bar" style="display:flex;gap:12px;align-items:center;margin-bottom:24px;flex-wrap:wrap;">
    <div style="flex:1;min-width:200px;">
        <h2 style="margin:0;font-size:20px;font-weight:700;">My Posts</h2>
    </div>
    <a href="add_post.php" class="btn btn_primary btn_sm" style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;background:var(--db-primary);color:#fff;border:none;cursor:pointer;">
        <i class="fa-solid fa-plus" aria-hidden="true"></i> New Post
    </a>
</div>

<div class="stats_grid" style="margin-bottom:24px;">
    <div class="stat_card" style="padding:16px;">
        <p class="stat_card_value" style="font-size:24px;"><?= count($grouped['draft']) ?></p>
        <p class="stat_card_label" style="margin:0;"><i class="fa-solid fa-pen"></i> Draft</p>
    </div>
    <div class="stat_card" style="padding:16px;">
        <p class="stat_card_value" style="font-size:24px;"><?= count($grouped['pending']) ?></p>
        <p class="stat_card_label" style="margin:0;"><i class="fa-solid fa-clock" style="color:#F59E0B;"></i> Pending</p>
    </div>
    <div class="stat_card" style="padding:16px;">
        <p class="stat_card_value" style="font-size:24px;"><?= count($grouped['published']) ?></p>
        <p class="stat_card_label" style="margin:0;"><i class="fa-solid fa-check-circle" style="color:#10B981;"></i> Published</p>
    </div>
    <div class="stat_card" style="padding:16px;">
        <p class="stat_card_value" style="font-size:24px;"><?= count($grouped['rejected']) ?></p>
        <p class="stat_card_label" style="margin:0;"><i class="fa-solid fa-ban" style="color:#EF4444;"></i> Rejected</p>
    </div>
</div>

<?php
$sections = [
    'draft' => ['label' => 'Draft', 'icon' => 'fa-solid fa-pen'],
    'pending' => ['label' => 'Pending Review', 'icon' => 'fa-solid fa-clock'],
    'published' => ['label' => 'Published', 'icon' => 'fa-solid fa-check-circle'],
    'rejected' => ['label' => 'Rejected', 'icon' => 'fa-solid fa-ban'],
];
foreach ($sections as $status => $sec):
    $posts_list = $grouped[$status];
?>
<div class="card">
    <div class="card_header">
        <h2><i class="<?= $sec['icon'] ?>" style="color:var(--db-primary);margin-right:8px;" aria-hidden="true"></i> <?= $sec['label'] ?> (<?= count($posts_list) ?>)</h2>
    </div>
    <div class="card_body_no_padding">
        <?php if (!empty($posts_list)): ?>
        <div class="table_wrapper">
            <table class="data_table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts_list as $p): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($p['title']) ?></strong>
                            <?php if (!empty($p['rejection_reason'])): ?>
                            <br><small style="color:var(--db-danger-text);font-size:11px;">Reason: <?= htmlspecialchars($p['rejection_reason']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($p['cat_name']) ?></td>
                        <td><span class="status_badge <?= $p['status'] ?>"><?= ucfirst(htmlspecialchars($p['status'])) ?></span></td>
                        <td style="white-space:nowrap;color:var(--db-text-secondary);font-size:13px;"><?= date('M j, Y', strtotime($p['created_at'])) ?></td>
                        <td>
                            <div class="cell_actions">
                                <button type="button" class="btn_small btn_secondary" data-post-quickview='<?= json_encode(['title'=>$p['title'],'cat_name'=>$p['cat_name'],'user_name'=>$_SESSION['user_name'],'status'=>$p['status'],'content'=>$p['content'],'image'=>$p['image'],'created_at'=>$p['created_at'],'rejection_reason'=>$p['rejection_reason']], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>'><i class="fa-solid fa-eye" aria-hidden="true"></i> View</button>
                                <a href="edit_post.php?id=<?= $p['id_post'] ?>" class="btn_small btn_secondary"><i class="fa-solid fa-pen" aria-hidden="true"></i> Edit</a>
                                <form method="POST" action="myposts.php" style="display:inline">
                                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                    <input type="hidden" name="delete" value="<?= $p['id_post'] ?>">
                                    <button type="submit" class="btn_small btn_danger" onclick="return confirm('Delete this post permanently?')" aria-label="Delete post"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty_state" style="text-align:center;padding:40px 20px;color:var(--db-text-secondary);">
            <p style="margin:0;">No <?= strtolower($sec['label']) ?> posts.</p>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>

<?php render_dashboard_pagination('myposts.php', $current_page, $total_pages, []); ?>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
