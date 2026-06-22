/* ============================================================
   Tangier Vibes — Admin Dashboard JavaScript
   ============================================================ */

document.addEventListener('DOMContentLoaded', function () {

    /* ── Mobile Sidebar Toggle ────────────────────────────── */
    const menuBtn = document.getElementById('mobileMenuBtn');
    const sidebar = document.querySelector('.sidebar');
    const sidebarOverlay = document.querySelector('.sidebar_overlay');

    if (menuBtn && sidebar) {
        function toggleSidebar() {
            sidebar.classList.toggle('open');
            if (sidebarOverlay) {
                sidebarOverlay.classList.toggle('open');
            }
        }

        menuBtn.addEventListener('click', toggleSidebar);

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', toggleSidebar);
        }

        // Consolidated Escape key handler (sidebar, modals, dropdowns)
        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape') return;
            if (sidebar.classList.contains('open')) { toggleSidebar(); return; }
            closeAllDropdowns();
            document.querySelectorAll('.modal_overlay.open').forEach(function (m) { m.classList.remove('open'); });
        });
    }

    /* ── Notification auto-dismiss ────────────────────────── */
    function autoDismissPopup(selector, delay) {
        const el = document.querySelector(selector);
        if (!el) return;
        setTimeout(function () {
            el.classList.add('fade-out');
            setTimeout(function () { el.remove(); }, 500);
        }, delay || 3000);
    }
    autoDismissPopup('.notification');

    /* ── Delete confirmation modals ───────────────────────── */
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            const message = el.getAttribute('data-confirm') || 'Are you sure?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    /* ── Toast auto-dismiss ───────────────────────────────── */
    document.querySelectorAll('.toast').forEach(function (toast) {
        setTimeout(function () {
            toast.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(20px)';
            setTimeout(function () { toast.remove(); }, 300);
        }, 4000);
    });

    /* ── Table row link click ─────────────────────────────── */
    document.querySelectorAll('.data_table tbody tr[data-href]').forEach(function (row) {
        row.addEventListener('click', function () {
            window.location = row.getAttribute('data-href');
        });
        row.style.cursor = 'pointer';
    });

    /* ── Post Quick View Modal ────────────────────────────── */
    const qvOverlay = document.getElementById('postQuickView');
    const qvClose = document.getElementById('qvClose');
    var qvLastFocused = null;

    function openQuickView(post) {
        qvLastFocused = document.activeElement;
        document.getElementById('qvTitle').textContent = post.title || '';
        document.getElementById('qvCategory').textContent = post.cat_name || '—';
        document.getElementById('qvAuthor').textContent = post.user_name || '—';
        document.getElementById('qvDate').textContent = post.created_at || '—';

        const statusEl = document.getElementById('qvStatus');
        if (post.status) {
            statusEl.innerHTML = '<span class="status_badge ' + post.status + '">' + post.status.charAt(0).toUpperCase() + post.status.slice(1) + '</span>';
        } else {
            statusEl.innerHTML = '—';
        }

        const imgWrap = document.getElementById('qvImage');
        if (post.image) {
            imgWrap.style.display = 'block';
            imgWrap.querySelector('img').src = '../' + post.image;
        } else {
            imgWrap.style.display = 'none';
        }

        const rejectionWrap = document.getElementById('qvRejection');
        if (post.rejection_reason) {
            rejectionWrap.style.display = 'block';
            document.getElementById('qvRejectionText').textContent = post.rejection_reason;
        } else {
            rejectionWrap.style.display = 'none';
        }

        document.getElementById('qvContent').textContent = post.content || '';

        qvOverlay.classList.add('open');
    }

    document.querySelectorAll('[data-post-quickview]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            try {
                const post = JSON.parse(btn.getAttribute('data-post-quickview'));
                openQuickView(post);
            } catch (err) {
                console.error('Invalid post data:', err);
            }
        });
    });

    function closeQuickView() {
        qvOverlay.classList.remove('open');
        if (qvLastFocused) {
            qvLastFocused.focus();
            qvLastFocused = null;
        }
    }

    if (qvClose) {
        qvClose.addEventListener('click', closeQuickView);
    }

    if (qvOverlay) {
        qvOverlay.addEventListener('click', function (e) {
            if (e.target === qvOverlay) closeQuickView();
        });
    }

    /* ── Generic Modal System ────────────────────────────── */
    window.openModal = function (id) {
        var el = document.getElementById(id);
        if (el) el.classList.add('open');
    };
    window.closeModal = function (id) {
        var el = document.getElementById(id);
        if (el) el.classList.remove('open');
    };
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('modal_overlay')) {
            e.target.classList.remove('open');
        }
    });

    /* ── Categories: editCat / closeEditModal ───────────── */
    window.editCat = function (id, name) {
        document.getElementById('editCatId').value = id;
        document.getElementById('editCatName').value = name;
        openModal('editModal');
    };
    window.closeEditModal = function () {
        closeModal('editModal');
    };
    document.querySelectorAll('.delete-cat-form').forEach(function (f) {
        f.addEventListener('submit', function (e) {
            if (!confirm('Delete "' + f.getAttribute('data-cat-name') + '"?')) e.preventDefault();
        });
    });

    /* ── Comments: bulk / dropdown ──────────────────────── */
    window.toggleBulk = function () {
        var c = document.querySelectorAll('.cb:checked').length;
        var b = document.getElementById('bulkDeleteBtn');
        if (b) b.style.display = c > 0 ? 'inline-flex' : 'none';
    };
    window.toggleDropdown = function (btn) {
        var dropdown = btn.closest('.action_dropdown');
        var isOpen = dropdown.classList.contains('open');
        closeAllDropdowns();
        if (!isOpen) dropdown.classList.add('open');
    };
    window.closeAllDropdowns = function () {
        document.querySelectorAll('.action_dropdown.open').forEach(function (d) {
            d.classList.remove('open');
        });
    };
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.action_dropdown')) closeAllDropdowns();
    });

});
