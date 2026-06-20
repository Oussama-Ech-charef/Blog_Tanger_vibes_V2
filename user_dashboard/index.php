<?php
$page_title = 'Overview';
require_once __DIR__ . '/init.php';

// Post counts
$counts = $conn->prepare("
    SELECT COUNT(*) as total,
           SUM(status='" . STATUS_DRAFT . "') as draft,
           SUM(status='" . STATUS_PENDING . "') as pending,
           SUM(status='" . STATUS_PUBLISHED . "') as published,
           SUM(status='" . STATUS_REJECTED . "') as rejected
    FROM posts WHERE id_user=:uid
");
$counts->execute([':uid' => $uid]);
$stats = $counts->fetch(PDO::FETCH_ASSOC);

// Recent activity from activity_log related to user's posts
$activities = $conn->prepare("
    SELECT al.* FROM activity_log al
    WHERE al.entity_type='post'
      AND al.entity_id IN (SELECT id_post FROM posts WHERE id_user=:uid)
      AND al.action_type IN ('post_submitted', 'post_approved', 'post_rejected', 'draft_saved')
    ORDER BY al.created_at DESC LIMIT 15
");
$activities->execute([':uid' => $uid]);
$recent = $activities->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/inc/header.php';
?>

<section class="stats_grid">
    <div class="stat_card">
        <div class="stat_card_header"><span class="stat_card_icon blue"><i class="fa-solid fa-file-lines" aria-hidden="true"></i></span></div>
        <p class="stat_card_label">Total Posts</p>
        <p class="stat_card_value"><?= (int)$stats['total'] ?></p>
    </div>
    <div class="stat_card">
        <div class="stat_card_header"><span class="stat_card_icon blue"><i class="fa-solid fa-pen" aria-hidden="true"></i></span></div>
        <p class="stat_card_label">Draft</p>
        <p class="stat_card_value"><?= (int)$stats['draft'] ?></p>
    </div>
    <div class="stat_card">
        <div class="stat_card_header"><span class="stat_card_icon yellow"><i class="fa-solid fa-clock" aria-hidden="true"></i></span></div>
        <p class="stat_card_label">Pending Review</p>
        <p class="stat_card_value"><?= (int)$stats['pending'] ?></p>
        <div class="stat_card_change <?= (int)$stats['pending'] > 0 ? 'negative' : 'positive' ?>">
            <?= (int)$stats['pending'] > 0 ? 'Awaiting moderation' : 'All clear' ?>
        </div>
    </div>
    <div class="stat_card">
        <div class="stat_card_header"><span class="stat_card_icon green"><i class="fa-solid fa-check-circle" aria-hidden="true"></i></span></div>
        <p class="stat_card_label">Published</p>
        <p class="stat_card_value"><?= (int)$stats['published'] ?></p>
    </div>
    <div class="stat_card">
        <div class="stat_card_header"><span class="stat_card_icon red"><i class="fa-solid fa-ban" aria-hidden="true"></i></span></div>
        <p class="stat_card_label">Rejected</p>
        <p class="stat_card_value"><?= (int)$stats['rejected'] ?></p>
    </div>
</section>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
    <div class="card">
        <div class="card_header">
            <h2><i class="fa-solid fa-bolt" style="color:var(--db-primary);margin-right:8px;" aria-hidden="true"></i>Quick Actions</h2>
        </div>
        <div class="card_body">
            <div class="quick_actions_grid" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <a href="myposts.php" class="quick_action_card" style="display:flex;flex-direction:column;align-items:center;gap:8px;padding:20px;background:#F8FAFC;border-radius:8px;text-decoration:none;color:var(--db-text-primary);transition:all 0.2s;border:1px solid transparent;">
                    <i class="fa-solid fa-file-lines" style="font-size:24px;color:var(--db-primary);" aria-hidden="true"></i>
                    <span style="font-size:13px;font-weight:600;">My Posts</span>
                </a>
                <a href="add_post.php" class="quick_action_card" style="display:flex;flex-direction:column;align-items:center;gap:8px;padding:20px;background:#F8FAFC;border-radius:8px;text-decoration:none;color:var(--db-text-primary);transition:all 0.2s;border:1px solid transparent;">
                    <i class="fa-solid fa-plus" style="font-size:24px;color:var(--db-primary);" aria-hidden="true"></i>
                    <span style="font-size:13px;font-weight:600;">New Post</span>
                </a>
                <a href="profile.php" class="quick_action_card" style="display:flex;flex-direction:column;align-items:center;gap:8px;padding:20px;background:#F8FAFC;border-radius:8px;text-decoration:none;color:var(--db-text-primary);transition:all 0.2s;border:1px solid transparent;">
                    <i class="fa-solid fa-user" style="font-size:24px;color:var(--db-primary);" aria-hidden="true"></i>
                    <span style="font-size:13px;font-weight:600;">Edit Profile</span>
                </a>
                <a href="../pages/index.php" class="quick_action_card" style="display:flex;flex-direction:column;align-items:center;gap:8px;padding:20px;background:#F8FAFC;border-radius:8px;text-decoration:none;color:var(--db-text-primary);transition:all 0.2s;border:1px solid transparent;">
                    <i class="fa-solid fa-globe" style="font-size:24px;color:var(--db-primary);" aria-hidden="true"></i>
                    <span style="font-size:13px;font-weight:600;">View Site</span>
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card_header">
            <h2><i class="fa-solid fa-clock-rotate-left" style="color:var(--db-primary);margin-right:8px;" aria-hidden="true"></i>Recent Activity</h2>
        </div>
        <div class="card_body">
            <?php if (!empty($recent)): ?>
            <div class="activity_feed">
                <?php $icon_map = [
                    'post_submitted' => 'fa-solid fa-paper-plane',
                    'post_approved' => 'fa-solid fa-check',
                    'post_rejected' => 'fa-solid fa-ban',
                    'draft_saved' => 'fa-solid fa-pen',
                ]; ?>
                <?php foreach ($recent as $act): ?>
                <div class="activity_item">
                    <div class="activity_icon"><i class="<?= $icon_map[$act['action_type']] ?? 'fa-solid fa-circle' ?>" style="color:var(--db-primary);" aria-hidden="true"></i></div>
                    <div class="activity_content">
                        <p class="activity_desc"><?= htmlspecialchars($act['description']) ?></p>
                        <p class="activity_time"><?= time_ago($act['created_at']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="empty_state" style="text-align:center;padding:32px 0;color:var(--db-text-secondary);">
                <i class="fa-solid fa-clock" style="font-size:32px;display:block;margin-bottom:12px;" aria-hidden="true"></i>
                <h3 style="font-size:16px;margin:0 0 4px;">No activity yet</h3>
                <p style="font-size:13px;margin:0;">Your recent activity will appear here.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
