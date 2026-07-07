<?php
// Categories management
require_once __DIR__ . '/init.php';
require_admin();
$page_title = __('categories_management_title');
$message = ''; $message_type = '';

// Add category
if (isset($_POST['add_category'])) {
    $name = trim($_POST['cat_name'] ?? '');
    if (validate_csrf_token($_POST['csrf_token']??'') && !empty($name) && strlen($name)<=100) {
        try {
            $conn->prepare("INSERT INTO categories (cat_name) VALUES (:n)")->execute([':n'=>$name]);
            $message = __('categories_added'); $message_type = 'success';
        } catch (PDOException $e) {
            $message = $e->getCode()==23000 ? __('categories_duplicate') : __('categories_error_generic');
            $message_type = 'error';
        }
    } else { $message = __('categories_invalid'); $message_type = 'error'; }
}

// Edit category
if (isset($_POST['edit_category'])) {
    $id = (int)($_POST['cat_id']??0); $name = trim($_POST['cat_name']??'');
    if (validate_csrf_token($_POST['csrf_token']??'') && $id>0 && !empty($name) && strlen($name)<=100) {
        try {
            $conn->prepare("UPDATE categories SET cat_name=:n WHERE id_category=:id")->execute([':n'=>$name,':id'=>$id]);
            $message = __('categories_updated'); $message_type = 'success';
        } catch (PDOException $e) {
            $message = $e->getCode()==23000 ? __('categories_name_duplicate') : __('categories_error_generic');
            $message_type = 'error';
        }
    } else { $message = __('categories_invalid'); $message_type = 'error'; }
}

// Delete category
if (isset($_POST['delete_category'])) {
    $cid = (int)($_POST['cat_id'] ?? 0);
    if (validate_csrf_token($_POST['csrf_token'] ?? '') && $cid > 0) {
        $check = $conn->prepare("SELECT COUNT(*) FROM posts WHERE id_category=:id");
        $check->execute([':id'=>$cid]);
        if ((int)$check->fetchColumn() > 0) {
            $message = __('categories_has_posts'); $message_type = 'error';
        } else {
            try {
                $conn->prepare("DELETE FROM categories WHERE id_category=:id")->execute([':id'=>$cid]);
                $message = __('categories_deleted'); $message_type = 'success';
            } catch (PDOException $e) { $message = __('categories_error_generic'); $message_type = 'error'; }
        }
    } else { $message = __('categories_invalid'); $message_type = 'error'; }
}

$categories = $conn->query("SELECT c.*, COUNT(p.id_post) AS post_count FROM categories c LEFT JOIN posts p ON p.id_category = c.id_category GROUP BY c.id_category ORDER BY c.cat_name ASC")->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/inc/header.php';
?>

<?php render_notification($message, $message_type); ?>

<div class="grid_2col">
    <div class="card">
        <div class="card_header"><h2><i class="fa-solid fa-plus icon_primary" aria-hidden="true"></i><?= __('categories_add_heading') ?></h2></div>
        <div class="card_body">
            <form method="POST" action="categories.php">
                <input type="hidden" name="csrf_token" value="<?=$csrf_token?>">
                <input type="hidden" name="add_category" value="1">
                <div class="form_group"><label for="cat_name"><?= __('categories_name_label') ?></label><input type="text" id="cat_name" name="cat_name" placeholder="<?= __('categories_name_placeholder') ?>" required maxlength="100"></div>
                <button type="submit" class="btn btn_primary"><i class="fa-solid fa-plus" aria-hidden="true"></i> <?= __('categories_add_btn') ?></button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card_header"><h2><i class="fa-solid fa-list icon_primary" aria-hidden="true"></i><?= sprintf(__('categories_list_heading'), count($categories)) ?></h2></div>
        <div class="card_body_no_padding">
            <div class="table_wrapper">
                <table class="data_table">
                    <thead><tr><th><?= __('categories_th_name') ?></th><th><?= __('categories_th_posts') ?></th><th><?= __('categories_th_created') ?></th><th><?= __('categories_th_actions') ?></th></tr></thead>
                    <tbody>
                        <?php if (!empty($categories)): ?>
                            <?php foreach ($categories as $c): ?>
                            <tr>
                                <td><strong><?=htmlspecialchars($c['cat_name'])?></strong></td>
                                <td><span class="fw_600"><?=(int)$c['post_count']?></span></td>
                                <td class="date_cell"><?=date('M j, Y',strtotime($c['created_at']))?></td>
                                <td><div class="cell_actions">
                                    <button class="btn_small btn_secondary" onclick='editCat(<?=$c['id_category']?>,<?=json_encode($c['cat_name'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)?>)'><i class="fa-solid fa-pen" aria-hidden="true"></i> <?= __('categories_edit_btn') ?></button>
                                    <form method="POST" action="categories.php" class="inline_form delete-cat-form" data-cat-name="<?=htmlspecialchars($c['cat_name'], ENT_QUOTES)?>">
                                        <input type="hidden" name="csrf_token" value="<?=$csrf_token?>">
                                        <input type="hidden" name="delete_category" value="1">
                                        <input type="hidden" name="cat_id" value="<?=$c['id_category']?>">
                                        <button type="submit" class="btn_small btn_danger" aria-label="<?= __('dashboard_aria_delete_category') ?>"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4"><div class="empty_state"><i class="fa-solid fa-tags"></i><h3><?= __('categories_empty_title') ?></h3><p><?= __('categories_empty_desc') ?></p></div></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal_overlay" id="editModal">
    <div class="modal_box">
        <h2><?= __('categories_edit_modal_title') ?></h2>
        <form method="POST" action="categories.php">
            <input type="hidden" name="csrf_token" value="<?=$csrf_token?>">
            <input type="hidden" name="edit_category" value="1">
            <input type="hidden" name="cat_id" id="editCatId">
            <div class="form_group"><label for="editCatName"><?= __('categories_edit_name_label') ?></label><input type="text" id="editCatName" name="cat_name" required maxlength="100"></div>
            <div class="modal_actions"><button type="button" class="btn btn_secondary" onclick="closeModal('editModal')"><?= __('categories_edit_cancel') ?></button><button type="submit" class="btn btn_primary"><?= __('categories_edit_save') ?></button></div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
