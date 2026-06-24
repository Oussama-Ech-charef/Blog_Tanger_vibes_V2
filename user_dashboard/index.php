<?php
$page_title = 'Overview';
require_once __DIR__ . '/init.php';

// Post counts
$counts = $conn->prepare("
    SELECT COUNT(*) as total,
           SUM(status=:draft) as draft,
           SUM(status=:pending) as pending,
           SUM(status=:published) as published,
           SUM(status=:rejected) as rejected
    FROM posts WHERE id_user=:uid
");
$counts->execute([
    ':uid' => $uid,
    ':draft' => STATUS_DRAFT,
    ':pending' => STATUS_PENDING,
    ':published' => STATUS_PUBLISHED,
    ':rejected' => STATUS_REJECTED
]);
$stats = $counts->fetch(PDO::FETCH_ASSOC);
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

<?php require_once __DIR__ . '/inc/footer.php'; ?>
