<?php
// Reusable Rich Text Editor Component for Add and Edit Post forms
// Expected variable: $editor_content (string)
$editor_content = $editor_content ?? '';
$editor_html = render_post_content($editor_content);
?>
<div class="add_post_card add_post_card_editor">
    <div class="editor_toolbar" id="editorToolbar">
        <button type="button" class="editor_toolbar_btn" data-cmd="bold" title="<?= __('editor_bold') ?>" aria-label="<?= __('editor_aria_bold') ?>"><i class="fa-solid fa-bold"></i></button>
        <button type="button" class="editor_toolbar_btn" data-cmd="italic" title="<?= __('editor_italic') ?>" aria-label="<?= __('editor_aria_italic') ?>"><i class="fa-solid fa-italic"></i></button>
        <button type="button" class="editor_toolbar_btn" data-cmd="underline" title="<?= __('editor_underline') ?>" aria-label="<?= __('editor_aria_underline') ?>"><i class="fa-solid fa-underline"></i></button>
        <span class="editor_divider" aria-hidden="true"></span>
        <button type="button" class="editor_toolbar_btn" data-cmd="insertUnorderedList" title="<?= __('editor_bullet_list') ?>" aria-label="<?= __('editor_aria_bullet_list') ?>"><i class="fa-solid fa-list-ul"></i></button>
        <button type="button" class="editor_toolbar_btn" data-cmd="insertOrderedList" title="<?= __('editor_numbered_list') ?>" aria-label="<?= __('editor_aria_numbered_list') ?>"><i class="fa-solid fa-list-ol"></i></button>
        <span class="editor_divider" aria-hidden="true"></span>
        <button type="button" class="editor_toolbar_btn" data-cmd="formatBlock" data-val="h2" title="<?= __('editor_heading') ?>" aria-label="<?= __('editor_aria_heading') ?>"><i class="fa-solid fa-heading"></i></button>
        <button type="button" class="editor_toolbar_btn" data-cmd="formatBlock" data-val="p" title="<?= __('editor_paragraph') ?>" aria-label="<?= __('editor_aria_paragraph') ?>"><i class="fa-solid fa-paragraph"></i></button>
        <span class="editor_divider" aria-hidden="true"></span>
        <button type="button" class="editor_toolbar_btn" data-cmd="createLink" title="<?= __('editor_insert_link') ?>" aria-label="<?= __('editor_aria_insert_link') ?>"><i class="fa-solid fa-link"></i></button>
        <button type="button" class="editor_toolbar_btn" data-cmd="insertImage" title="<?= __('editor_insert_image') ?>" aria-label="<?= __('editor_aria_insert_image') ?>"><i class="fa-solid fa-image"></i></button>
    </div>
    <div class="add_post_card_body">
        <div id="editorSurface" class="editor_surface" contenteditable="true" role="textbox" aria-multiline="true" data-placeholder="<?= __('editor_placeholder') ?>"><?= $editor_html ?></div>
        <textarea id="content" name="content" class="editor_input"><?= htmlspecialchars($editor_content) ?></textarea>
    </div>
</div>
