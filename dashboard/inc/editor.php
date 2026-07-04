<?php
// Reusable Rich Text Editor Component for Add and Edit Post forms
// Expected variable: $editor_content (string)
$editor_content = $editor_content ?? '';
$editor_html = render_post_content($editor_content);
?>
<div class="add_post_card add_post_card_editor">
    <div class="editor_toolbar" id="editorToolbar">
        <button type="button" class="editor_toolbar_btn" data-cmd="bold" title="Bold (Ctrl+B)" aria-label="Bold"><i class="fa-solid fa-bold"></i></button>
        <button type="button" class="editor_toolbar_btn" data-cmd="italic" title="Italic (Ctrl+I)" aria-label="Italic"><i class="fa-solid fa-italic"></i></button>
        <button type="button" class="editor_toolbar_btn" data-cmd="underline" title="Underline (Ctrl+U)" aria-label="Underline"><i class="fa-solid fa-underline"></i></button>
        <span class="editor_divider" aria-hidden="true"></span>
        <button type="button" class="editor_toolbar_btn" data-cmd="insertUnorderedList" title="Bullet list" aria-label="Bullet list"><i class="fa-solid fa-list-ul"></i></button>
        <button type="button" class="editor_toolbar_btn" data-cmd="insertOrderedList" title="Numbered list" aria-label="Numbered list"><i class="fa-solid fa-list-ol"></i></button>
        <span class="editor_divider" aria-hidden="true"></span>
        <button type="button" class="editor_toolbar_btn" data-cmd="formatBlock" data-val="h2" title="Heading" aria-label="Heading"><i class="fa-solid fa-heading"></i></button>
        <button type="button" class="editor_toolbar_btn" data-cmd="formatBlock" data-val="p" title="Paragraph" aria-label="Paragraph"><i class="fa-solid fa-paragraph"></i></button>
        <span class="editor_divider" aria-hidden="true"></span>
        <button type="button" class="editor_toolbar_btn" data-cmd="createLink" title="Insert link" aria-label="Insert link"><i class="fa-solid fa-link"></i></button>
        <button type="button" class="editor_toolbar_btn" data-cmd="insertImage" title="Insert image" aria-label="Insert image"><i class="fa-solid fa-image"></i></button>
    </div>
    <div class="add_post_card_body">
        <div id="editorSurface" class="editor_surface" contenteditable="true" role="textbox" aria-multiline="true" data-placeholder="Write your story..."><?= $editor_html ?></div>
        <textarea id="content" name="content" class="editor_input"><?= htmlspecialchars($editor_content) ?></textarea>
    </div>
</div>
