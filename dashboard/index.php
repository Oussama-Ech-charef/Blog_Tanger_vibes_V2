<?php
$page_title = 'Overview';
require_once __DIR__ . '/init.php';

// Total posts by status
$status_stmt = $conn->prepare("
    SELECT COUNT(*) as total,
           SUM(status = :pub) as published,
           SUM(status = :pend) as pending,
           SUM(status = :rej) as rejected,
           SUM(status = :draft) as draft FROM posts
");
$status_stmt->execute([
    ':pub' => STATUS_PUBLISHED,
    ':pend' => STATUS_PENDING,
    ':rej' => STATUS_REJECTED,
    ':draft' => STATUS_DRAFT
]);
$post_stats = $status_stmt->fetch(PDO::FETCH_ASSOC);

$comment_count = (int)$conn->query("SELECT COUNT(*) FROM comments")->fetchColumn();
$user_count = (int)$conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
$new_users = (int)$conn->query("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();
$message_count = (int)$conn->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();
$unread_messages = (int)$conn->query("SELECT COUNT(*) FROM contact_messages WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
$pc_stmt = $conn->prepare("SELECT COUNT(*) FROM posts WHERE status = :st");
$pc_stmt->execute([':st' => STATUS_PENDING]);
$pending_count = (int)$pc_stmt->fetchColumn();

// Chart data
$posts_chart = $conn->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count FROM posts WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH) GROUP BY month ORDER BY month ASC")->fetchAll(PDO::FETCH_ASSOC);
$comments_chart = $conn->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count FROM comments WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH) GROUP BY month ORDER BY month ASC")->fetchAll(PDO::FETCH_ASSOC);
$users_chart = $conn->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH) GROUP BY month ORDER BY month ASC")->fetchAll(PDO::FETCH_ASSOC);

$cat_chart_stmt = $conn->prepare("
    SELECT c.cat_name, COUNT(p.id_post) as count FROM categories c
    LEFT JOIN posts p ON p.id_category = c.id_category AND p.status = :pub
    GROUP BY c.id_category, c.cat_name ORDER BY count DESC
");
$cat_chart_stmt->execute([':pub' => STATUS_PUBLISHED]);
$category_chart = $cat_chart_stmt->fetchAll(PDO::FETCH_ASSOC);

// Format chart data — pad all 12 months with zeros so charts always have 12 bars/points
$pm_labels = []; $pm_data = [];
$pm_map = [];
foreach ($posts_chart as $r) { $pm_map[$r['month']] = (int)$r['count']; }
for ($i = 11; $i >= 0; $i--) {
    $m = date('Y-m', strtotime("-$i months"));
    $pm_labels[] = date('M', strtotime($m . '-01'));
    $pm_data[] = $pm_map[$m] ?? 0;
}

$cm_labels = []; $cm_data = [];
$cm_map = [];
foreach ($comments_chart as $r) { $cm_map[$r['month']] = (int)$r['count']; }
for ($i = 11; $i >= 0; $i--) {
    $m = date('Y-m', strtotime("-$i months"));
    $cm_labels[] = date('M', strtotime($m . '-01'));
    $cm_data[] = $cm_map[$m] ?? 0;
}

$um_labels = []; $um_data = [];
$um_map = [];
foreach ($users_chart as $r) { $um_map[$r['month']] = (int)$r['count']; }
for ($i = 11; $i >= 0; $i--) {
    $m = date('Y-m', strtotime("-$i months"));
    $um_labels[] = date('M', strtotime($m . '-01'));
    $um_data[] = $um_map[$m] ?? 0;
}

$cat_labels = []; $cat_data = []; $cat_colors = ['#0047AB','#10B981','#F59E0B','#EF4444','#7C3AED','#EC4899'];
foreach ($category_chart as $i => $r) { $cat_labels[] = $r['cat_name']; $cat_data[] = (int)$r['count']; }

// Activity
$activities = $conn->query("SELECT * FROM activity_log WHERE action_type != 'draft_saved' ORDER BY created_at DESC LIMIT 15")->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/inc/header.php';
?>

<?php if ($pending_count > 0): render_notification($pending_count . ' post(s) pending review.', 'warning'); endif; ?>

<section class="stats_grid">
    <div class="stat_card">
        <div class="stat_card_header"><span class="stat_card_icon blue"><i class="fa-solid fa-file-lines" aria-hidden="true"></i></span></div>
        <p class="stat_card_label">Total Posts</p>
        <p class="stat_card_value"><?= (int)$post_stats['total'] ?></p>
        <div class="stat_card_change positive"><?= (int)$post_stats['published'] ?> published &middot; <?= (int)$post_stats['pending'] ?> pending</div>
    </div>
    <div class="stat_card">
        <div class="stat_card_header"><span class="stat_card_icon green"><i class="fa-solid fa-check-circle" aria-hidden="true"></i></span></div>
        <p class="stat_card_label">Published</p>
        <p class="stat_card_value"><?= (int)$post_stats['published'] ?></p>
        <div class="stat_card_change positive"><i class="fa-solid fa-arrow-up" aria-hidden="true"></i> <?= (int)$post_stats['published'] ?> live</div>
    </div>
    <div class="stat_card">
        <div class="stat_card_header"><span class="stat_card_icon yellow"><i class="fa-solid fa-clock" aria-hidden="true"></i></span></div>
        <p class="stat_card_label">Pending Review</p>
        <p class="stat_card_value"><?= (int)$post_stats['pending'] ?></p>
        <div class="stat_card_change <?= $pending_count > 0 ? 'negative' : 'positive' ?>"><?= $pending_count > 0 ? 'Needs attention' : 'All clear' ?></div>
    </div>
    <div class="stat_card">
        <div class="stat_card_header"><span class="stat_card_icon red"><i class="fa-solid fa-ban" aria-hidden="true"></i></span></div>
        <p class="stat_card_label">Rejected</p>
        <p class="stat_card_value"><?= (int)$post_stats['rejected'] ?></p>
    </div>
    <div class="stat_card">
        <div class="stat_card_header"><span class="stat_card_icon purple"><i class="fa-solid fa-comments" aria-hidden="true"></i></span></div>
        <p class="stat_card_label">Comments</p>
        <p class="stat_card_value"><?= $comment_count ?></p>
    </div>
    <div class="stat_card">
        <div class="stat_card_header"><span class="stat_card_icon blue"><i class="fa-solid fa-users" aria-hidden="true"></i></span></div>
        <p class="stat_card_label">Users</p>
        <p class="stat_card_value"><?= $user_count ?></p>
        <div class="stat_card_change positive"><i class="fa-solid fa-arrow-up" aria-hidden="true"></i> <?= $new_users ?> new this month</div>
    </div>
    <div class="stat_card">
        <div class="stat_card_header"><span class="stat_card_icon yellow"><i class="fa-solid fa-envelope" aria-hidden="true"></i></span></div>
        <p class="stat_card_label">Messages</p>
        <p class="stat_card_value"><?= $message_count ?></p>
        <div class="stat_card_change <?= $unread_messages > 0 ? 'negative' : 'positive' ?>"><?= $unread_messages ?> recent</div>
    </div>
</section>

<section class="chart_grid">
    <div class="chart_container"><h3>Posts per Month</h3><canvas id="postsChart"></canvas></div>
    <div class="chart_container"><h3>Comments per Month</h3><canvas id="commentsChart"></canvas></div>
    <div class="chart_container"><h3>User Registrations</h3><canvas id="usersChart"></canvas></div>
    <div class="chart_container"><h3>Content by Category</h3><canvas id="categoryChart"></canvas></div>
</section>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
    <div class="card">
        <div class="card_header"><h2><i class="fa-solid fa-bolt" style="color:var(--db-primary);margin-right:8px;" aria-hidden="true"></i>Quick Actions</h2></div>
        <div class="card_body">
            <div class="quick_actions_grid">
                <a href="add_post.php" class="quick_action_card"><i class="fa-solid fa-plus" aria-hidden="true"></i><span>Add New Post</span></a>
                <a href="posts.php?status=pending" class="quick_action_card"><i class="fa-solid fa-check" aria-hidden="true"></i><span>Review Pending</span></a>
                <a href="comments.php" class="quick_action_card"><i class="fa-solid fa-comment" aria-hidden="true"></i><span>Manage Comments</span></a>
                <a href="messages.php" class="quick_action_card"><i class="fa-solid fa-envelope" aria-hidden="true"></i><span>View Messages</span></a>
                <a href="users.php" class="quick_action_card"><i class="fa-solid fa-users-gear" aria-hidden="true"></i><span>Manage Users</span></a>
                <a href="categories.php" class="quick_action_card"><i class="fa-solid fa-tags" aria-hidden="true"></i><span>Categories</span></a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card_header"><h2><i class="fa-solid fa-clock-rotate-left" style="color:var(--db-primary);margin-right:8px;" aria-hidden="true"></i>Recent Activity</h2></div>
        <div class="card_body">
            <?php if (!empty($activities)): ?>
            <div class="activity_feed">
                <?php $icon_map = [
                    'post_created' => 'fa-solid fa-plus', 'post_approved' => 'fa-solid fa-check',
                    'post_rejected' => 'fa-solid fa-ban', 'comment_added' => 'fa-solid fa-comment',
                    'user_registered' => 'fa-solid fa-user-plus', 'message_received' => 'fa-solid fa-envelope',
                    'post_updated' => 'fa-solid fa-pen', 'post_deleted' => 'fa-solid fa-trash',
                ]; ?>
                <?php foreach ($activities as $act): ?>
                <div class="activity_item">
                    <div class="activity_icon <?= htmlspecialchars($act['action_type']) ?>"><i class="<?= $icon_map[$act['action_type']] ?? 'fa-solid fa-circle' ?>" aria-hidden="true"></i></div>
                    <div class="activity_content">
                        <p class="activity_desc"><?= htmlspecialchars($act['description']) ?></p>
                        <p class="activity_time"><?= time_ago($act['created_at']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="empty_state"><i class="fa-solid fa-clock" aria-hidden="true"></i><h3>No activity yet</h3><p>Activity will appear here as users interact with the site.</p></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    Chart.defaults.font.family = "'Inter', -apple-system, sans-serif";
    Chart.defaults.font.size = 12;
    Chart.defaults.color = '#64748B';

    new Chart(document.getElementById('postsChart'), {
        type: 'bar',
        data: { labels: <?= json_encode($pm_labels) ?>, datasets: [{ label: 'Posts', data: <?= json_encode($pm_data) ?>, backgroundColor: 'rgba(0,71,171,0.15)', borderColor: '#0047AB', borderWidth: 2, borderRadius: 6, borderSkipped: false }] },
        options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' } }, x: { grid: { display: false } } } }
    });

    new Chart(document.getElementById('commentsChart'), {
        type: 'bar',
        data: { labels: <?= json_encode($cm_labels) ?>, datasets: [{ label: 'Comments', data: <?= json_encode($cm_data) ?>, backgroundColor: 'rgba(16,185,129,0.15)', borderColor: '#10B981', borderWidth: 2, borderRadius: 6, borderSkipped: false }] },
        options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' } }, x: { grid: { display: false } } } }
    });

    new Chart(document.getElementById('usersChart'), {
        type: 'line',
        data: { labels: <?= json_encode($um_labels) ?>, datasets: [{ label: 'Users', data: <?= json_encode($um_data) ?>, borderColor: '#7C3AED', backgroundColor: 'rgba(124,58,237,0.08)', fill: true, tension: 0.4, pointBackgroundColor: '#7C3AED', pointRadius: 4, borderWidth: 2 }] },
        options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' } }, x: { grid: { display: false } } } }
    });

    new Chart(document.getElementById('categoryChart'), {
        type: 'doughnut',
        data: { labels: <?= json_encode($cat_labels) ?>, datasets: [{ data: <?= json_encode($cat_data) ?>, backgroundColor: <?= json_encode(array_slice($cat_colors, 0, count($cat_labels))) ?>, borderWidth: 0 }] },
        options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { position: 'bottom', labels: { padding: 16, usePointStyle: true } } }, cutout: '65%' }
    });
});
</script>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
