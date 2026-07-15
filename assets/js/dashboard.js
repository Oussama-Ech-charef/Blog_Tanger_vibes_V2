// Admin dashboard JavaScript
window.openModal = function (id) {
    var el = document.getElementById(id);
    if (el) el.classList.add('open');
};

window.closeModal = function (id) {
    var el = document.getElementById(id);
    if (el) el.classList.remove('open');
};

window.editCat = function (id, name) {
    document.getElementById('editCatId').value = id;
    document.getElementById('editCatName').value = name;
    window.openModal('editModal');
};

window.closeEditModal = function () {
    window.closeModal('editModal');
};

window.toggleBulk = function () {
    var c = document.querySelectorAll('.cb:checked').length;
    var b = document.getElementById('bulkDeleteBtn');
    if (b) b.style.display = c > 0 ? 'inline-flex' : 'none';
};

window.toggleDropdown = function (btn) {
    var dropdown = btn.closest('.action_dropdown');
    var isOpen = dropdown.classList.contains('open');
    window.closeAllDropdowns();
    if (!isOpen) {
        dropdown.classList.add('open');
        window.positionDropdownMenu(btn, dropdown);
    }
};

window.closeAllDropdowns = function () {
    document.querySelectorAll('.action_dropdown.open').forEach(function (d) {
        d.classList.remove('open');
        var menu = d.querySelector('.action_dropdown_menu');
        if (menu) {
            menu.style.position = '';
            menu.style.top = '';
            menu.style.left = '';
            menu.style.right = '';
            menu.style.maxHeight = '';
            menu.style.overflowY = '';
        }
    });
};

// Position dropdown using fixed coords, constrained to the card's visible area
// so it never appears outside the card and never gets clipped by overflow.
window.positionDropdownMenu = function (btn, dropdown) {
    var menu = dropdown.querySelector('.action_dropdown_menu');
    if (!menu) return;

    var card = dropdown.closest('.card_posts_table, .card_table');
    if (!card) return;

    var wrapper = card.querySelector('.table_wrapper');
    if (!wrapper) return;

    // Reset any max-height/overflow from a previous drop-up position
    menu.style.maxHeight = '';
    menu.style.overflowY = '';

    // Use the table wrapper as the boundary so the menu never overlaps
    // pagination and stays inside the card.
    var wrapperRect = wrapper.getBoundingClientRect();
    var btnRect = btn.getBoundingClientRect();
    var menuWidth = 190;
    var gap = 4;
    var threshold = 180;

    // Available space inside the table area
    var spaceBelow = wrapperRect.bottom - btnRect.bottom;
    var spaceAbove = btnRect.top - wrapperRect.top;

    // Decide direction based on space within the table area
    var top;
    if (spaceBelow >= threshold) {
        top = btnRect.bottom + gap;
    } else if (spaceAbove >= threshold) {
        top = btnRect.top - gap;
        menu.style.maxHeight = Math.min(spaceAbove - gap, 260) + 'px';
        menu.style.overflowY = 'auto';
    } else {
        if (spaceBelow >= spaceAbove) {
            top = btnRect.bottom + gap;
            menu.style.maxHeight = Math.max(spaceBelow - gap, 100) + 'px';
            menu.style.overflowY = 'auto';
        } else {
            top = btnRect.top - gap;
            menu.style.maxHeight = Math.max(spaceAbove - gap, 100) + 'px';
            menu.style.overflowY = 'auto';
        }
    }

    // Clamp top so the menu stays inside the table wrapper vertically
    var minTop = wrapperRect.top + gap;
    var maxTop = wrapperRect.bottom - 40;
    if (top < minTop) top = minTop;
    if (top > maxTop) top = maxTop;

    // Horizontal: always right-align to the button's right edge, then
    // clamp only enough to stay inside the wrapper.
    // For drop-up menus, a tiny right-nudge compensates for the scrollbar
    // gutter that `overflowY: auto` may reserve on some platforms.
    var nudge = (spaceBelow < threshold) ? 3 : 0;
    var menuRight = btnRect.right + nudge;
    var menuLeft = menuRight - menuWidth;

    if (menuLeft < wrapperRect.left + gap) {
        menuLeft = wrapperRect.left + gap;
        menuRight = menuLeft + menuWidth;
    }

    if (menuRight > wrapperRect.right - gap) {
        menuRight = wrapperRect.right - gap;
        menuLeft = menuRight - menuWidth;
    }

    menu.style.right = (window.innerWidth - menuRight) + 'px';
    menu.style.left = 'auto';
    menu.style.position = 'fixed';
    menu.style.top = top + 'px';
};

// Calculate how many table rows fit in the available space, then
// redirect with the right per_page if it differs from the current value.
// When force is true, always recalculates (used on resize);
// when force is false (initial load), skips if per_page is already in the URL
// to prevent reload loops.
window.calculatePerPage = function (force) {
    var urlParams = new URLSearchParams(window.location.search);
    if (!force && urlParams.has('per_page')) return;

    var wrapper = document.querySelector('.card_posts_table .table_wrapper');
    if (!wrapper) return;

    var table = wrapper.querySelector('.data_table');
    if (!table) return;

    // The wrapper height is determined by the flex layout (available space).
    // We subtract the thead height to get the room for body rows.
    var wrapperHeight = wrapper.clientHeight;
    if (wrapperHeight < 100) return;

    var thead = table.querySelector('thead');
    var theadHeight = thead ? thead.getBoundingClientRect().height : 40;
    var availableHeight = wrapperHeight - theadHeight;

    // Measure the first row to estimate row height.
    var firstRow = table.querySelector('tbody tr');
    var rowHeight = firstRow ? firstRow.getBoundingClientRect().height : 52;

    var capacity = Math.floor(availableHeight / rowHeight);
    capacity = Math.max(8, Math.min(50, capacity));

    var currentPerPage = parseInt(urlParams.get('per_page')) || 8;
    if (capacity !== currentPerPage) {
        var newParams = new URLSearchParams(urlParams);
        newParams.set('per_page', capacity);
        newParams.delete('page');
        window.location.search = newParams.toString();
    }
};

// Register DOM event handlers
(function () {
    function init() {
        // Dynamic per_page — calculate once after layout is ready
        window.calculatePerPage();
        // Mobile sidebar toggle
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

            // Close sidebar, modals, dropdowns on Escape key
            document.addEventListener('keydown', function (e) {
                if (e.key !== 'Escape') return;
                if (sidebar.classList.contains('open')) { toggleSidebar(); return; }
                window.closeAllDropdowns();
                document.querySelectorAll('.modal_overlay.open').forEach(function (m) { m.classList.remove('open'); });
            });
        }

        // Notification auto-dismiss
        (function () {
            var el = document.querySelector('.notification');
            if (!el) return;
            setTimeout(function () {
                el.classList.add('fade-out');
                setTimeout(function () { if (el.parentNode) el.parentNode.removeChild(el); }, 500);
            }, 3000);
        })();

        // Delete confirmation modals
        document.querySelectorAll('[data-confirm]').forEach(function (el) {
            el.addEventListener('click', function (e) {
                const message = el.getAttribute('data-confirm') || 'Are you sure?';
                if (!confirm(message)) {
                    e.preventDefault();
                }
            });
        });

        // Toast auto-dismiss
        document.querySelectorAll('.toast').forEach(function (toast) {
            setTimeout(function () {
                toast.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(20px)';
                setTimeout(function () { toast.remove(); }, 300);
            }, 4000);
        });

        // Table row link click
        document.querySelectorAll('.data_table tbody tr[data-href]').forEach(function (row) {
            row.addEventListener('click', function () {
                window.location = row.getAttribute('data-href');
            });
            row.style.cursor = 'pointer';
        });

        // Post quick view modal
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

            document.getElementById('qvContent').innerHTML = post.content || '';

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
            if (qvOverlay) qvOverlay.classList.remove('open');
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

        // Close modal on overlay click
        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('modal_overlay')) {
                e.target.classList.remove('open');
            }
        });

        // Delete category confirm
        document.querySelectorAll('.delete-cat-form').forEach(function (f) {
            f.addEventListener('submit', function (e) {
                if (!confirm(Lang.confirmDeleteCat.replace('%s', f.getAttribute('data-cat-name')))) e.preventDefault();
            });
        });

        // Notification bell toggle
        var notifBellBtn = document.getElementById('notifBellBtn');
        var notifDropdown = document.getElementById('notifDropdown');

        if (notifBellBtn && notifDropdown) {
            notifBellBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                var isOpen = notifDropdown.classList.contains('open');
                // Close any other open dropdowns
                window.closeAllDropdowns();
                if (!isOpen) {
                    notifBellBtn.classList.add('open');
                    notifDropdown.classList.add('open');
                    // Mark today's notifications as read via AJAX
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', window.location.pathname, true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onload = function () {
                        if (xhr.status === 200) {
                            // Remove unread styles and badge
                            var badge = notifBellBtn.querySelector('.notif_badge');
                            if (badge) badge.parentNode.removeChild(badge);
                            notifDropdown.querySelectorAll('.notif_dropdown_item.unread').forEach(function (item) {
                                item.classList.remove('unread');
                                var dot = item.querySelector('.notif_dd_dot');
                                if (dot) dot.parentNode.removeChild(dot);
                            });
                        }
                    };
                    xhr.send('ajax_notif_read=1&csrf_token=' + encodeURIComponent(Lang.csrfToken));
                }
            });
        }

        // Language dropdown toggle
        document.querySelectorAll('[data-lang-dropdown]').forEach(function (trigger) {
            trigger.addEventListener('click', function (e) {
                e.stopPropagation();
                var dropdown = trigger.closest('.lang_dropdown');
                var isOpen = dropdown.classList.contains('open');
                document.querySelectorAll('.lang_dropdown.open').forEach(function (d) {
                    d.classList.remove('open');
                });
                if (!isOpen) {
                    dropdown.classList.add('open');
                }
            });
        });

        // Close notification dropdown on outside click
        document.addEventListener('click', function (e) {
            var wrap = document.querySelector('.notif_bell_wrap');
            if (wrap && !e.target.closest('.notif_bell_wrap')) {
                var dd = document.getElementById('notifDropdown');
                var btn = document.getElementById('notifBellBtn');
                if (dd) dd.classList.remove('open');
                if (btn) btn.classList.remove('open');
            }
        });

        // Close dropdowns on outside click
        document.addEventListener('click', function (e) {
            if (!e.target.closest('.action_dropdown')) {
                window.closeAllDropdowns();
            }
            if (!e.target.closest('.lang_dropdown')) {
                document.querySelectorAll('.lang_dropdown.open').forEach(function (d) {
                    d.classList.remove('open');
                });
            }
        });

        // Close dropdowns on scroll
        var scrollableTables = document.querySelectorAll('.table_wrapper');
        scrollableTables.forEach(function (wrapper) {
            wrapper.addEventListener('scroll', window.closeAllDropdowns);
        });
        window.addEventListener('scroll', window.closeAllDropdowns, true);

        // On resize: recalculate per_page (debounced) to keep rows fitting without overflow
        var resizeTimer;
        window.addEventListener('resize', function () {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function () {
                window.calculatePerPage(true);
            }, 500);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
