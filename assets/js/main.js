function autoDismissPopup(selector, delay) {
    const el = document.querySelector(selector);
    if (!el) return;
    setTimeout(function() {
        el.classList.add('fade-out');
        setTimeout(function() { el.remove(); }, 500);
    }, delay || 3000);
}

document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const menuBtn = document.getElementById('menu_btn');
    const closeMenuBtn = document.getElementById('close_menu');
    const mobileNav = document.querySelector('.header_nav_mobile');

    if (menuBtn && closeMenuBtn && mobileNav) {
        menuBtn.addEventListener('click', function() {
            mobileNav.classList.add('open');
        });

        closeMenuBtn.addEventListener('click', function() {
            mobileNav.classList.remove('open');
        });
    }

    // Language dropdown toggle
    const triggers = document.querySelectorAll('[data-lang-dropdown]');

    triggers.forEach(function(trigger) {
        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            const dropdown = trigger.closest('.lang_dropdown');
            const isOpen = dropdown.classList.contains('open');

            // close all other dropdowns
            document.querySelectorAll('.lang_dropdown.open').forEach(function(d) {
                d.classList.remove('open');
            });

            if (!isOpen) {
                dropdown.classList.add('open');
            }
        });
    });

    // User dropdown toggle
    const userTriggers = document.querySelectorAll('[data-user-dropdown]');

    userTriggers.forEach(function(trigger) {
        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            const dropdown = trigger.closest('.user_dropdown');
            const menu = dropdown.querySelector('.user_dropdown_menu');
            const isOpen = dropdown.classList.contains('open');

            document.querySelectorAll('.user_dropdown.open').forEach(function(d) {
                d.classList.remove('open');
                d.querySelector('[data-user-dropdown]').setAttribute('aria-expanded', 'false');
            });

            if (!isOpen) {
                dropdown.classList.add('open');
                trigger.setAttribute('aria-expanded', 'true');
                var firstItem = menu.querySelector('[role="menuitem"]');
                if (firstItem) firstItem.focus();
            }
        });
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        document.querySelectorAll('.lang_dropdown.open').forEach(function(d) {
            d.classList.remove('open');
        });
        document.querySelectorAll('.user_dropdown.open').forEach(function(d) {
            d.classList.remove('open');
            var btn = d.querySelector('[data-user-dropdown]');
            if (btn) btn.setAttribute('aria-expanded', 'false');
        });
    });

    // Keyboard navigation for user dropdowns
    document.addEventListener('keydown', function(e) {
        // Escape closes open dropdowns
        if (e.key === 'Escape') {
            document.querySelectorAll('.user_dropdown.open').forEach(function(d) {
                d.classList.remove('open');
                var btn = d.querySelector('[data-user-dropdown]');
                if (btn) {
                    btn.setAttribute('aria-expanded', 'false');
                    btn.focus();
                }
            });
        }

        // Arrow down/up within user dropdown menu
        var openMenu = document.querySelector('.user_dropdown.open .user_dropdown_menu');
        if (!openMenu) return;

        var items = Array.from(openMenu.querySelectorAll('[role="menuitem"]'));
        if (items.length === 0) return;

        var currentIndex = items.indexOf(document.activeElement);

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            var nextIndex = (currentIndex + 1) % items.length;
            items[nextIndex].focus();
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            var prevIndex = (currentIndex - 1 + items.length) % items.length;
            items[prevIndex].focus();
        }
    });

    // auto-dismiss notification popups
    autoDismissPopup('.notification');
});
