// Posts page dropdown and reject modal
(function () {
    function init() {
        // Escape closes reject modal and dropdowns
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                var modal = document.getElementById('rejectModal');
                if (modal) modal.classList.remove('open');
                if (typeof window.closeAllDropdowns === 'function') {
                    window.closeAllDropdowns();
                }
            }
        });

        // Open reject modal
        document.querySelectorAll('.dropdown_reject').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var rejectPostId = document.getElementById('rejectPostId');
                var rejectPostTitle = document.getElementById('rejectPostTitle');
                var rejectModal = document.getElementById('rejectModal');

                if (rejectPostId) rejectPostId.value = btn.getAttribute('data-post-id');
                if (rejectPostTitle) rejectPostTitle.textContent = Lang.rejectModalTitle.replace('%s', btn.getAttribute('data-post-title'));
                if (rejectModal) rejectModal.classList.add('open');
            });
        });

        // Close reject modal
        document.querySelectorAll('.modal_cancel').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var modal = document.getElementById('rejectModal');
                if (modal) modal.classList.remove('open');
            });
        });

        var rejectModal = document.getElementById('rejectModal');
        if (rejectModal) {
            rejectModal.addEventListener('click', function (e) {
                if (e.target === this) {
                    this.classList.remove('open');
                }
            });
        }

        // Confirm approve or delete
        document.querySelectorAll('.dropdown_approve').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                if (!confirm(Lang.confirmApprove)) {
                    e.preventDefault();
                }
            });
        });

        document.querySelectorAll('.dropdown_delete').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                if (!confirm(Lang.confirmDeletePost)) {
                    e.preventDefault();
                }
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
