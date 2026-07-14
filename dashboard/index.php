<?php
// Dashboard overview
require_once __DIR__ . '/init.php';
$page_title = __('dashboard_overview_title');

$uid = current_user_id();

if ($is_admin):
// Load global statistics for admin
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
$pending_count = (int)$post_stats['pending'];

// Load chart data for last 12 months
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

// Fill 12 months with zeros so charts always show full year
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

else:
// Load personal statistics for non-admin user
$status_stmt = $conn->prepare("
    SELECT COUNT(*) as total,
           SUM(status = :pub) as published,
           SUM(status = :pend) as pending,
           SUM(status = :rej) as rejected,
           SUM(status = :draft) as draft FROM posts WHERE id_user = :uid
");
$status_stmt->execute([
    ':pub' => STATUS_PUBLISHED,
    ':pend' => STATUS_PENDING,
    ':rej' => STATUS_REJECTED,
    ':draft' => STATUS_DRAFT,
    ':uid' => $uid
]);
$post_stats = $status_stmt->fetch(PDO::FETCH_ASSOC);

$recent_posts = $conn->prepare("SELECT id_post, title, status, created_at FROM posts WHERE id_user = :uid ORDER BY created_at DESC LIMIT 5");
$recent_posts->execute([':uid' => $uid]);
$recent_posts = $recent_posts->fetchAll(PDO::FETCH_ASSOC);
endif;

require_once __DIR__ . '/inc/header.php';
?>

<?php if ($is_admin): ?>
<?php if ($pending_count > 0): render_notification(sprintf(__('notif_pending_review'), $pending_count), 'warning'); endif; ?>
<section class="stats_grid">
    <div class="stat_card">
        <div class="stat_card_header"><span class="stat_card_icon blue"><i class="fa-solid fa-file-lines" aria-hidden="true"></i></span></div>
        <p class="stat_card_label"><?= __('stat_total_posts') ?></p>
        <p class="stat_card_value"><?= (int)$post_stats['total'] ?></p>
        <div class="stat_card_change positive"><?= sprintf(__('stat_published_count'), (int)$post_stats['published'], (int)$post_stats['pending']) ?></div>
    </div>
    <div class="stat_card">
        <div class="stat_card_header"><span class="stat_card_icon green"><i class="fa-solid fa-check-circle" aria-hidden="true"></i></span></div>
        <p class="stat_card_label"><?= __('stat_published') ?></p>
        <p class="stat_card_value"><?= (int)$post_stats['published'] ?></p>
        <div class="stat_card_change positive"><i class="fa-solid fa-arrow-up" aria-hidden="true"></i> <?= sprintf(__('stat_live_count'), (int)$post_stats['published']) ?></div>
    </div>
    <div class="stat_card">
        <div class="stat_card_header"><span class="stat_card_icon yellow"><i class="fa-solid fa-clock" aria-hidden="true"></i></span></div>
        <p class="stat_card_label"><?= __('stat_pending_review') ?></p>
        <p class="stat_card_value"><?= (int)$post_stats['pending'] ?></p>
        <div class="stat_card_change <?= $pending_count > 0 ? 'negative' : 'positive' ?>"><?= $pending_count > 0 ? __('stat_needs_attention') : __('stat_all_clear') ?></div>
    </div>
    <div class="stat_card">
        <div class="stat_card_header"><span class="stat_card_icon red"><i class="fa-solid fa-ban" aria-hidden="true"></i></span></div>
        <p class="stat_card_label"><?= __('stat_rejected') ?></p>
        <p class="stat_card_value"><?= (int)$post_stats['rejected'] ?></p>
    </div>
    <div class="stat_card">
        <div class="stat_card_header"><span class="stat_card_icon purple"><i class="fa-solid fa-comments" aria-hidden="true"></i></span></div>
        <p class="stat_card_label"><?= __('stat_comments') ?></p>
        <p class="stat_card_value"><?= $comment_count ?></p>
    </div>
    <div class="stat_card">
        <div class="stat_card_header"><span class="stat_card_icon blue"><i class="fa-solid fa-users" aria-hidden="true"></i></span></div>
        <p class="stat_card_label"><?= __('stat_users') ?></p>
        <p class="stat_card_value"><?= $user_count ?></p>
        <div class="stat_card_change positive"><i class="fa-solid fa-arrow-up" aria-hidden="true"></i> <?= sprintf(__('stat_new_users_month'), $new_users) ?></div>
    </div>
    <div class="stat_card">
        <div class="stat_card_header"><span class="stat_card_icon yellow"><i class="fa-solid fa-envelope" aria-hidden="true"></i></span></div>
        <p class="stat_card_label"><?= __('stat_messages') ?></p>
        <p class="stat_card_value"><?= $message_count ?></p>
        <div class="stat_card_change <?= $unread_messages > 0 ? 'negative' : 'positive' ?>"><?= sprintf(__('stat_recent_messages'), $unread_messages) ?></div>
    </div>
</section>

<section class="chart_grid">
    <div class="chart_container"><h3><?= __('chart_posts_month') ?></h3><canvas id="postsChart"></canvas></div>
    <div class="chart_container"><h3><?= __('chart_comments_month') ?></h3><canvas id="commentsChart"></canvas></div>
    <div class="chart_container"><h3><?= __('chart_user_registrations') ?></h3><canvas id="usersChart"></canvas></div>
    <div class="chart_container"><h3><?= __('chart_content_category') ?></h3><canvas id="categoryChart"></canvas></div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    Chart.defaults.font.family = "'Inter', -apple-system, sans-serif";
    Chart.defaults.font.size = 12;
    Chart.defaults.color = '#64748B';

    new Chart(document.getElementById('postsChart'), {
        type: 'bar',
        data: { labels: <?= json_encode($pm_labels) ?>, datasets: [{ label: '<?= __('chart_label_posts') ?>', data: <?= json_encode($pm_data) ?>, backgroundColor: 'rgba(0,71,171,0.15)', borderColor: '#0047AB', borderWidth: 2, borderRadius: 6, borderSkipped: false }] },
        options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' } }, x: { grid: { display: false } } } }
    });

    new Chart(document.getElementById('commentsChart'), {
        type: 'bar',
        data: { labels: <?= json_encode($cm_labels) ?>, datasets: [{ label: '<?= __('chart_label_comments') ?>', data: <?= json_encode($cm_data) ?>, backgroundColor: 'rgba(16,185,129,0.15)', borderColor: '#10B981', borderWidth: 2, borderRadius: 6, borderSkipped: false }] },
        options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' } }, x: { grid: { display: false } } } }
    });

    new Chart(document.getElementById('usersChart'), {
        type: 'line',
        data: { labels: <?= json_encode($um_labels) ?>, datasets: [{ label: '<?= __('chart_label_users') ?>', data: <?= json_encode($um_data) ?>, borderColor: '#7C3AED', backgroundColor: 'rgba(124,58,237,0.08)', fill: true, tension: 0.4, pointBackgroundColor: '#7C3AED', pointRadius: 4, borderWidth: 2 }] },
        options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' } }, x: { grid: { display: false } } } }
    });

    new Chart(document.getElementById('categoryChart'), {
        type: 'doughnut',
        data: { labels: <?= json_encode($cat_labels) ?>, datasets: [{ data: <?= json_encode($cat_data) ?>, backgroundColor: <?= json_encode(array_slice($cat_colors, 0, count($cat_labels))) ?>, borderWidth: 0 }] },
        options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { position: 'bottom', labels: { padding: 16, usePointStyle: true } } }, cutout: '65%' }
    });
});
</script>

<?php else: ?>
<section class="stats_grid">
    <div class="stat_card">
        <div class="stat_card_header"><span class="stat_card_icon blue"><i class="fa-solid fa-file-lines" aria-hidden="true"></i></span></div>
        <p class="stat_card_label"><?= __('stat_total_posts') ?></p>
        <p class="stat_card_value"><?= (int)$post_stats['total'] ?></p>
    </div>
    <div class="stat_card">
        <div class="stat_card_header"><span class="stat_card_icon green"><i class="fa-solid fa-check-circle" aria-hidden="true"></i></span></div>
        <p class="stat_card_label"><?= __('stat_published') ?></p>
        <p class="stat_card_value"><?= (int)$post_stats['published'] ?></p>
    </div>
    <div class="stat_card">
        <div class="stat_card_header"><span class="stat_card_icon yellow"><i class="fa-solid fa-clock" aria-hidden="true"></i></span></div>
        <p class="stat_card_label"><?= __('stat_pending_review') ?></p>
        <p class="stat_card_value"><?= (int)$post_stats['pending'] ?></p>
    </div>
    <div class="stat_card">
        <div class="stat_card_header"><span class="stat_card_icon purple"><i class="fa-solid fa-pen" aria-hidden="true"></i></span></div>
        <p class="stat_card_label"><?= __('stat_draft') ?></p>
        <p class="stat_card_value"><?= (int)$post_stats['draft'] ?></p>
    </div>
    <div class="stat_card">
        <div class="stat_card_header"><span class="stat_card_icon red"><i class="fa-solid fa-ban" aria-hidden="true"></i></span></div>
        <p class="stat_card_label"><?= __('stat_rejected') ?></p>
        <p class="stat_card_value"><?= (int)$post_stats['rejected'] ?></p>
    </div>
</section>

<?php if (!empty($recent_posts)): ?>
<section class="card" style="margin-top:24px;">
    <div class="card_header"><h2><?= __('recent_posts_title') ?></h2></div>
    <div class="card_body_no_padding">
        <div class="table_wrapper">
            <table class="data_table">
                <thead><tr><th><?= __('posts_th_title') ?></th><th><?= __('posts_th_status') ?></th><th><?= __('posts_th_date') ?></th><th><?= __('posts_th_actions') ?></th></tr></thead>
                <tbody>
                    <?php foreach ($recent_posts as $rp): ?>
                    <tr>
                        <td><a href="edit_post.php?id=<?= $rp['id_post'] ?>" style="color:var(--db-primary);font-weight:500;"><?= htmlspecialchars($rp['title']) ?></a></td>
                        <td><span class="status_badge <?= $rp['status'] ?>"><?= ucfirst(htmlspecialchars($rp['status'])) ?></span></td>
                        <td class="date_cell"><?= date('M j, Y', strtotime($rp['created_at'])) ?></td>
                        <td><a href="preview.php?id=<?= $rp['id_post'] ?>" class="btn_small btn_secondary"><i class="fa-solid fa-eye" aria-hidden="true"></i> <?= __('posts_view_post') ?></a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php endif; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
