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

    // close dropdown when clicking outside
    document.addEventListener('click', function() {
        document.querySelectorAll('.lang_dropdown.open').forEach(function(d) {
            d.classList.remove('open');
        });
    });
});
