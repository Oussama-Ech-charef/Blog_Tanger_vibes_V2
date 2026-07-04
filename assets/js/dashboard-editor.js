(function () {
    var contentTextarea = document.getElementById('content');
    var editorSurface = document.getElementById('editorSurface');
    var wordCount = document.getElementById('wordCount');
    var charCount = document.getElementById('charCount');
    var form = contentTextarea ? contentTextarea.closest('form') : null;

    function syncTextarea() {
        if (contentTextarea && editorSurface) {
            contentTextarea.value = editorSurface.innerHTML.trim();
        }
    }

    function updateCounts() {
        var text = editorSurface ? editorSurface.innerText : (contentTextarea ? contentTextarea.value : '');
        var chars = text.length;
        var words = text.trim() ? text.trim().split(/\s+/).length : 0;

        if (charCount) charCount.textContent = chars;
        if (wordCount) wordCount.textContent = words;
    }

    function handleEditorInput() {
        syncTextarea();
        updateCounts();
    }

    function focusEditor() {
        if (editorSurface) {
            editorSurface.focus();
        }
    }

    if (editorSurface) {
        editorSurface.addEventListener('input', handleEditorInput);
        editorSurface.addEventListener('blur', syncTextarea);
    }

    if (form) {
        form.addEventListener('submit', syncTextarea);
    }

    document.querySelectorAll('[data-cmd]').forEach(function (btn) {
        btn.addEventListener('mousedown', function (event) {
            event.preventDefault();
        });

        btn.addEventListener('click', function () {
            var cmd = this.getAttribute('data-cmd');
            var val = this.getAttribute('data-val') || null;

            if (cmd === 'createLink') {
                var url = prompt('Enter URL:');
                if (url) {
                    focusEditor();
                    document.execCommand(cmd, false, url);
                    handleEditorInput();
                }
                return;
            }

            if (cmd === 'insertImage') {
                var imgUrl = prompt('Enter image URL:');
                if (imgUrl) {
                    focusEditor();
                    document.execCommand(cmd, false, imgUrl);
                    handleEditorInput();
                }
                return;
            }

            focusEditor();
            document.execCommand(cmd, false, val);
            handleEditorInput();
        });
    });

    syncTextarea();
    updateCounts();
})();
