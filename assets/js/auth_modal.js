document.addEventListener('DOMContentLoaded', function() {
    const overlay = document.getElementById('authOverlay');
    const closeBtn = document.getElementById('authClose');
    const tabs = document.querySelectorAll('.auth_tab');
    const loginForm = document.getElementById('authLoginForm');
    const registerForm = document.getElementById('authRegisterForm');
    const loginError = document.getElementById('authLoginError');
    const registerError = document.getElementById('authRegisterError');

    // open modal — triggered by any element with data-auth-toggle
    document.querySelectorAll('[data-auth-toggle]').forEach(function(el) {
        el.addEventListener('click', function(e) {
            e.preventDefault();
            overlay.classList.add('open');
            document.body.style.overflow = 'hidden';
        });
    });

    // close modal
    function closeModal() {
        overlay.classList.remove('open');
        document.body.style.overflow = '';
        loginError.classList.remove('show');
        registerError.classList.remove('show');
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

            var target = tab.getAttribute('data-tab');
            loginForm.classList.toggle('active', target === 'login');
            registerForm.classList.toggle('active', target === 'register');
            loginError.classList.remove('show');
            registerError.classList.remove('show');
        });
    });

    // form submission
    function submitForm(form, errorEl, btn) {
        var formData = new FormData(form);

        btn.classList.add('loading');
        btn.disabled = true;
        errorEl.classList.remove('show');

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
                    // registration success — switch to login tab
                    document.querySelector('[data-tab="login"]').click();
                    errorEl.textContent = '';
                    registerForm.reset();
                }
            } else {
                errorEl.textContent = data.error;
                errorEl.classList.add('show');
            }
        })
        .catch(function() {
            btn.classList.remove('loading');
            btn.disabled = false;
            errorEl.textContent = typeof AUTH_ERROR_UNEXPECTED !== 'undefined' ? AUTH_ERROR_UNEXPECTED : 'An unexpected error occurred. Please try again.';
            errorEl.classList.add('show');
        });
    }

    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        submitForm(loginForm, loginError, document.getElementById('authLoginBtn'));
    });

    registerForm.addEventListener('submit', function(e) {
        e.preventDefault();
        submitForm(registerForm, registerError, document.getElementById('authRegisterBtn'));
    });
});
