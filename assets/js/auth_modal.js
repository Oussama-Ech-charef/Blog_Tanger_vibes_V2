function showNotification(message, type) {
    const icons = {success:'fa-check-circle', error:'fa-exclamation-circle', warning:'fa-triangle-exclamation', info:'fa-info-circle'};
    const el = document.createElement('div');
    el.className = 'notification ' + type;
    el.innerHTML = '<i class="fa-solid ' + (icons[type] || icons.info) + '"></i> ' + message;
    document.body.appendChild(el);
    autoDismissPopup('.notification');
}

document.addEventListener('DOMContentLoaded', function() {
    const overlay = document.getElementById('authOverlay');
    const closeBtn = document.getElementById('authClose');
    const tabs = document.querySelectorAll('.auth_tab');
    const loginForm = document.getElementById('authLoginForm');
    const registerForm = document.getElementById('authRegisterForm');

    var lastFocusedElement = null;

    // open modal — triggered by any element with data-auth-toggle
    document.querySelectorAll('[data-auth-toggle]').forEach(function(el) {
        el.addEventListener('click', function(e) {
            e.preventDefault();
            lastFocusedElement = document.activeElement;
            overlay.classList.add('open');
            document.body.style.overflow = 'hidden';
            closeBtn.focus();
        });
    });

    // close modal
    function closeModal() {
        overlay.classList.remove('open');
        document.body.style.overflow = '';
        if (lastFocusedElement) {
            lastFocusedElement.focus();
            lastFocusedElement = null;
        }
    }

    closeBtn.addEventListener('click', closeModal);

    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) closeModal();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && overlay.classList.contains('open')) closeModal();
    });

    // tab switching
    tabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            tabs.forEach(function(t) {
                t.classList.remove('active');
                t.setAttribute('aria-selected', 'false');
            });
            tab.classList.add('active');
            tab.setAttribute('aria-selected', 'true');

            const target = tab.getAttribute('data-tab');
            loginForm.classList.toggle('active', target === 'login');
            registerForm.classList.toggle('active', target === 'register');
        });
    });

    // form submission
    function submitForm(form, btn) {
        const formData = new FormData(form);

        btn.classList.add('loading');
        btn.disabled = true;

        fetch('../includes/ajax_auth.php', {
            method: 'POST',
            body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            btn.classList.remove('loading');
            btn.disabled = false;

            if (data.success) {
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    showNotification(data.message || 'Account created. Please sign in.', 'success');
                    document.querySelector('[data-tab="login"]').click();
                    registerForm.reset();
                }
            } else {
                showNotification(data.error || 'An error occurred.', 'error');
            }
        })
        .catch(function() {
            btn.classList.remove('loading');
            btn.disabled = false;
            showNotification(typeof AUTH_ERROR_UNEXPECTED !== 'undefined' ? AUTH_ERROR_UNEXPECTED : 'An unexpected error occurred. Please try again.', 'error');
        });
    }

    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        submitForm(loginForm, document.getElementById('authLoginBtn'));
    });

    registerForm.addEventListener('submit', function(e) {
        e.preventDefault();
        submitForm(registerForm, document.getElementById('authRegisterBtn'));
    });
});
