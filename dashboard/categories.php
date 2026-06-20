<?php
$page_title = 'Categories Management';
require_once __DIR__ . '/init.php';
$message = ''; $message_type = '';

// Add
if (isset($_POST['add_category'])) {
    $name = trim($_POST['cat_name'] ?? '');
    if (validate_csrf_token($_POST['csrf_token']??'') && !empty($name) && strlen($name)<=100) {
        try {
            $conn->prepare("INSERT INTO categories (cat_name) VALUES (:n)")->execute([':n'=>$name]);
            $message = 'Category created.'; $message_type = 'success';
        } catch (PDOException $e) {
            $message = $e->getCode()==23000 ? 'Category already exists.' : 'Error.';
            $message_type = 'error';
        }
    } else { $message = 'Invalid request.'; $message_type = 'error'; }
}

// Edit
if (isset($_POST['edit_category'])) {
    $id = (int)($_POST['cat_id']??0); $name = trim($_POST['cat_name']??'');
    if (validate_csrf_token($_POST['csrf_token']??'') && $id>0 && !empty($name) && strlen($name)<=100) {
        try {
            $conn->prepare("UPDATE categories SET cat_name=:n WHERE id_category=:id")->execute([':n'=>$name,':id'=>$id]);
            $message = 'Category updated.'; $message_type = 'success';
        } catch (PDOException $e) {
            $message = $e->getCode()==23000 ? 'Name already exists.' : 'Error.';
            $message_type = 'error';
        }
    } else { $message = 'Invalid request.'; $message_type = 'error'; }
}

// Delete
if (isset($_POST['delete_category'])) {
    $cid = (int)($_POST['cat_id'] ?? 0);
    if (validate_csrf_token($_POST['csrf_token'] ?? '') && $cid > 0) {
        $check = $conn->prepare("SELECT COUNT(*) FROM posts WHERE id_category=:id");
        $check->execute([':id'=>$cid]);
        if ((int)$check->fetchColumn() > 0) {
            $message = 'Cannot delete: category has posts.'; $message_type = 'error';
        } else {
            try {
                $conn->prepare("DELETE FROM categories WHERE id_category=:id")->execute([':id'=>$cid]);
                $message = 'Category deleted.'; $message_type = 'success';
            } catch (PDOException $e) { $message = 'Error.'; $message_type = 'error'; }
        }
    } else { $message = 'Invalid request.'; $message_type = 'error'; }
}

$categories = $conn->query("SELECT c.*, COUNT(p.id_post) AS post_count FROM categories c LEFT JOIN posts p ON p.id_category = c.id_category GROUP BY c.id_category ORDER BY c.cat_name ASC")->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/inc/header.php';
?>

<?php render_notification($message, $message_type); ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
    <div class="card">
        <div class="card_header"><h2><i class="fa-solid fa-plus" style="color:var(--db-primary);margin-right:8px;" aria-hidden="true"></i>Add Category</h2></div>
        <div class="card_body">
            <form method="POST" action="categories.php">
                <input type="hidden" name="csrf_token" value="<?=$csrf_token?>">
                <input type="hidden" name="add_category" value="1">
                <div class="form_group"><label for="cat_name">Category Name</label><input type="text" id="cat_name" name="cat_name" placeholder="e.g., Beaches" required maxlength="100"></div>
                <button type="submit" class="btn btn_primary"><i class="fa-solid fa-plus" aria-hidden="true"></i> Add Category</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card_header"><h2><i class="fa-solid fa-list" style="color:var(--db-primary);margin-right:8px;" aria-hidden="true"></i>Categories (<?=count($categories)?>)</h2></div>
        <div class="card_body_no_padding">
            <div class="table_wrapper">
                <table class="data_table">
                    <thead><tr><th>Name</th><th>Posts</th><th>Created</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php if (!empty($categories)): ?>
                            <?php foreach ($categories as $c): ?>
                            <tr>
                                <td><strong><?=htmlspecialchars($c['cat_name'])?></strong></td>
                                <td><span style="font-weight:600;"><?=(int)$c['post_count']?></span></td>
                                <td style="color:var(--db-text-secondary);font-size:13px;"><?=date('M j, Y',strtotime($c['created_at']))?></td>
                                <td><div class="cell_actions">
                                    <button class="btn_small btn_secondary" onclick="editCat(<?=$c['id_category']?>,<?=json_encode($c['cat_name'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)?>)"><i class="fa-solid fa-pen" aria-hidden="true"></i> Edit</button>
                                    <form method="POST" action="categories.php" style="display:inline" class="delete-cat-form" data-cat-name="<?=htmlspecialchars($c['cat_name'], ENT_QUOTES)?>">
                                        <input type="hidden" name="csrf_token" value="<?=$csrf_token?>">
                                        <input type="hidden" name="delete_category" value="1">
                                        <input type="hidden" name="cat_id" value="<?=$c['id_category']?>">
                                        <button type="submit" class="btn_small btn_danger" aria-label="Delete category"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4"><div class="empty_state"><i class="fa-solid fa-tags"></i><h3>No categories</h3><p>Create your first category above.</p></div></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal_overlay" id="editModal">
    <div class="modal_box">
        <h2>Edit Category</h2>
        <form method="POST" action="categories.php">
            <input type="hidden" name="csrf_token" value="<?=$csrf_token?>">
            <input type="hidden" name="edit_category" value="1">
            <input type="hidden" name="cat_id" id="editCatId">
            <div class="form_group"><label for="editCatName">Name</label><input type="text" id="editCatName" name="cat_name" required maxlength="100"></div>
            <div class="modal_actions"><button type="button" class="btn btn_secondary" onclick="closeEditModal()">Cancel</button><button type="submit" class="btn btn_primary">Save</button></div>
        </form>
    </div>
</div>
<script>
function editCat(id,name){document.getElementById('editCatId').value=id;document.getElementById('editCatName').value=name;document.getElementById('editModal').classList.add('open');}
function closeEditModal(){document.getElementById('editModal').classList.remove('open');}
document.getElementById('editModal').addEventListener('click',function(e){if(e.target===this)closeEditModal();});
document.addEventListener('keydown',function(e){if(e.key==='Escape')closeEditModal();});
document.querySelectorAll('.delete-cat-form').forEach(function(f){
    f.addEventListener('submit',function(e){
        if(!confirm('Delete "'+f.getAttribute('data-cat-name')+'"?'))e.preventDefault();
    });
});
</script>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
