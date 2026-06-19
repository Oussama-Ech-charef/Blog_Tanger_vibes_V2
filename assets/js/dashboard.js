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

        // Close sidebar on Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && sidebar.classList.contains('open')) {
                toggleSidebar();
            }
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

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && qvOverlay && qvOverlay.classList.contains('open')) {
            closeQuickView();
        }
    });

});
