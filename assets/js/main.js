function autoDismissPopup(selector, delay) {
    const el = document.querySelector(selector);
    if (!el) return;
    setTimeout(function() {
        el.classList.add('fade-out');
        setTimeout(function() { el.remove(); }, 500);
    }, delay || 3000);
}

document.addEventListener('DOMContentLoaded', function() {
    // get menu elements
    const menuBtn = document.getElementById('menu_btn');
    const closeMenuBtn = document.getElementById('close_menu');
    const mobileNav = document.querySelector('.header_nav_mobile');

    if (menuBtn && closeMenuBtn && mobileNav) {
        // open menu
        menuBtn.addEventListener('click', function() {
            mobileNav.classList.add('open');
        });

        // close menu
        closeMenuBtn.addEventListener('click', function() {
            mobileNav.classList.remove('open');
        });
    }

    // language dropdown toggle
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

    // user dropdown toggle
    const userTriggers = document.querySelectorAll('[data-user-dropdown]');

    userTriggers.forEach(function(trigger) {
        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            const dropdown = trigger.closest('.user_dropdown');
            const isOpen = dropdown.classList.contains('open');

            // close all other user dropdowns
            document.querySelectorAll('.user_dropdown.open').forEach(function(d) {
                d.classList.remove('open');
            });

            if (!isOpen) {
                dropdown.classList.add('open');
            }
        });
    });

    // close all dropdowns when clicking outside
    document.addEventListener('click', function() {
        document.querySelectorAll('.lang_dropdown.open').forEach(function(d) {
            d.classList.remove('open');
        });
        document.querySelectorAll('.user_dropdown.open').forEach(function(d) {
            d.classList.remove('open');
        });
    });
});
