(function () {
    var contentTextarea = document.getElementById('content');
    var wordCount = document.getElementById('wordCount');
    var charCount = document.getElementById('charCount');

    /* ── Word & Character Count ────────────────────────── */

    function updateCounts() {
        var text = contentTextarea ? contentTextarea.value : '';
        var chars = text.length;
        var words = text.trim() ? text.trim().split(/\s+/).length : 0;
        if (charCount) charCount.textContent = chars;
        if (wordCount) wordCount.textContent = words;
    }

    if (contentTextarea) {
        contentTextarea.addEventListener('input', updateCounts);
    }

    /* ── Editor Toolbar Commands ───────────────────────── */

    document.querySelectorAll('[data-cmd]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var cmd = this.getAttribute('data-cmd');
            var val = this.getAttribute('data-val') || null;

            if (cmd === 'createLink') {
                var url = prompt('Enter URL:');
                if (url) document.execCommand(cmd, false, url);
                return;
            }

            if (cmd === 'insertImage') {
                var imgUrl = prompt('Enter image URL:');
                if (imgUrl) document.execCommand(cmd, false, imgUrl);
                return;
            }

            document.execCommand(cmd, false, val);
        });
    });

    /* ── Initial count (if content pre-filled) ─────────── */
    updateCounts();
})();
