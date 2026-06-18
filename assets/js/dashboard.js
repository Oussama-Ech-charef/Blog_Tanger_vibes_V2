document.addEventListener('DOMContentLoaded', function() {
    var modals = document.querySelectorAll('.view_modal, .reason_modal');

    function closeAllModals() {
        modals.forEach(function(m) { m.classList.remove('open'); });
        document.body.style.overflow = '';
    }

    // open modal — intercept links with hash targeting a modal
    document.querySelectorAll('a[href^="#view_post_"], a[href^="#reject_reason_"]').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            var targetId = this.getAttribute('href').substring(1);
            var target = document.getElementById(targetId);
            if (target) {
                closeAllModals();
                target.classList.add('open');
                document.body.style.overflow = 'hidden';
            }
        });
    });

    // close via [data-modal-close] buttons
    document.querySelectorAll('[data-modal-close]').forEach(function(el) {
        el.addEventListener('click', function(e) {
            e.preventDefault();
            closeAllModals();
        });
    });

    // close on overlay click
    modals.forEach(function(modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeAllModals();
            }
        });
    });

    // close on Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            var anyOpen = false;
            modals.forEach(function(m) {
                if (m.classList.contains('open')) anyOpen = true;
            });
            if (anyOpen) closeAllModals();
        }
    });
});
