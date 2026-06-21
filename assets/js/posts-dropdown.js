/* ============================================================
   Tangier Vibes — Posts Page Dropdown & Reject Modal
   ============================================================ */

document.addEventListener('DOMContentLoaded', function () {

    /* ── Dropdown toggle (event delegation) ──────────────── */
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.action_dropdown_btn');
        if (btn) {
            var dropdown = btn.closest('.action_dropdown');
            var isOpen = dropdown.classList.contains('open');
            document.querySelectorAll('.action_dropdown.open').forEach(function (d) {
                d.classList.remove('open');
            });
            if (!isOpen) dropdown.classList.add('open');
            return;
        }
        if (!e.target.closest('.action_dropdown')) {
            document.querySelectorAll('.action_dropdown.open').forEach(function (d) {
                d.classList.remove('open');
            });
        }
    });

    /* ── Escape closes reject modal + dropdowns ──────────── */
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.getElementById('rejectModal').classList.remove('open');
            document.querySelectorAll('.action_dropdown.open').forEach(function (d) {
                d.classList.remove('open');
            });
        }
    });

    /* ── Open reject modal ───────────────────────────────── */
    document.querySelectorAll('.dropdown_reject').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('rejectPostId').value = btn.getAttribute('data-post-id');
            document.getElementById('rejectPostTitle').textContent = 'Reject: "' + btn.getAttribute('data-post-title') + '"';
            document.getElementById('rejectModal').classList.add('open');
        });
    });

    /* ── Close reject modal (Cancel button + backdrop) ───── */
    document.querySelectorAll('.modal_cancel').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('rejectModal').classList.remove('open');
        });
    });

    document.getElementById('rejectModal').addEventListener('click', function (e) {
        if (e.target === this) {
            this.classList.remove('open');
        }
    });

    /* ── Confirm dialogs (Approve / Delete) ────────────── */
    document.querySelectorAll('.dropdown_approve').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            if (!confirm('Approve and publish?')) {
                e.preventDefault();
            }
        });
    });

    document.querySelectorAll('.dropdown_delete').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            if (!confirm('Delete this post permanently?')) {
                e.preventDefault();
            }
        });
    });

});
