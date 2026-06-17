<style>
/* ── Auth Modal ──────────────────────────────────────────────────────── */

.auth_overlay {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s ease, visibility 0.3s ease;
}

.auth_overlay.open {
    opacity: 1;
    visibility: visible;
}

.auth_modal {
    width: min(100%, 440px);
    max-height: 90vh;
    overflow-y: auto;
    padding: 40px 36px 32px;
    border-radius: 16px;
    background: var(--bg_color);
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.2);
    transform: scale(0.92) translateY(12px);
    transition: transform 0.35s cubic-bezier(0.22, 1, 0.36, 1);
    position: relative;
}

.auth_overlay.open .auth_modal {
    transform: scale(1) translateY(0);
}

.auth_close {
    position: absolute;
    top: 14px;
    inset-inline-end: 14px;
    width: 34px;
    height: 34px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    border-radius: 50%;
    background: var(--color_light);
    color: var(--color_gray);
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.2s;
}

.auth_close:hover {
    background: var(--color_border);
    color: var(--color_dark);
}

.auth_modal_header {
    text-align: center;
    margin-bottom: 28px;
}

.auth_modal_logo {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 16px;
    font-weight: 800;
    font-size: 1.3rem;
    color: var(--color_dark);
}

.auth_modal_logo img {
    height: 32px;
    width: auto;
}

.auth_modal_header h2 {
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--color_dark);
    margin-bottom: 8px;
    line-height: 1.2;
}

.auth_modal_header p {
    font-size: 0.9rem;
    color: var(--color_gray);
    line-height: 1.5;
    margin: 0;
}

/* tabs */
.auth_tabs {
    display: flex;
    border-radius: 10px;
    background: var(--color_light);
    padding: 4px;
    margin-bottom: 24px;
}

.auth_tab {
    flex: 1;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    border-radius: 8px;
    background: transparent;
    color: var(--color_gray);
    font-size: 0.85rem;
    font-weight: 600;
    font-family: var(--font_main);
    cursor: pointer;
    transition: all 0.2s;
}

.auth_tab.active {
    background: var(--bg_color);
    color: var(--color_primary);
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
}

.auth_tab:hover:not(.active) {
    color: var(--color_dark);
}

/* social */
.auth_social {
    margin-bottom: 20px;
}

.auth_social_btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    width: 100%;
    height: 46px;
    border: 1px solid var(--color_border);
    border-radius: 10px;
    background: var(--bg_color);
    color: var(--color_dark);
    font-size: 0.9rem;
    font-weight: 600;
    font-family: var(--font_main);
    cursor: pointer;
    transition: all 0.2s;
}

.auth_social_btn:hover {
    background: var(--color_light);
    border-color: var(--color_primary);
}

.auth_social_btn i {
    font-size: 1.1rem;
}

/* divider */
.auth_divider {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 20px;
    color: var(--color_border);
    font-size: 0.8rem;
    font-weight: 500;
}

.auth_divider::before,
.auth_divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--color_border);
}

/* forms */
.auth_form {
    display: none;
    flex-direction: column;
    gap: 14px;
}

.auth_form.active {
    display: flex;
}

.auth_form .form_group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.auth_form label {
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--color_dark);
}

.auth_form input {
    height: 46px;
    padding: 0 14px;
    border: 1px solid var(--color_border);
    border-radius: 10px;
    color: var(--color_dark);
    font-size: 0.9rem;
    font-family: var(--font_main);
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.auth_form input:focus {
    border-color: var(--color_primary);
    box-shadow: 0 0 0 3px rgba(15, 111, 152, 0.12);
}

.auth_form .auth_error {
    padding: 10px 14px;
    border-radius: 8px;
    background: #fef2f2;
    color: #dc2626;
    font-size: 0.82rem;
    font-weight: 600;
    display: none;
}

.auth_form .auth_error.show {
    display: block;
}

.auth_form .auth_btn {
    height: 48px;
    border: none;
    border-radius: 10px;
    background: var(--color_primary);
    color: white;
    font-size: 0.95rem;
    font-weight: 700;
    font-family: var(--font_main);
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.auth_form .auth_btn:hover {
    background: var(--color_primary_dark);
}

.auth_form .auth_btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.auth_btn_spinner {
    display: none;
    width: 18px;
    height: 18px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: auth_spin 0.6s linear infinite;
}

.auth_btn.loading .auth_btn_text {
    visibility: hidden;
}

.auth_btn.loading .auth_btn_spinner {
    display: inline-block;
}

@keyframes auth_spin {
    to { transform: rotate(360deg); }
}

/* footer link */
.auth_switch {
    text-align: center;
    margin-top: 2px;
    font-size: 0.82rem;
    color: var(--color_gray);
}

.auth_switch button {
    border: none;
    background: none;
    color: var(--color_primary);
    font-weight: 700;
    font-size: inherit;
    font-family: var(--font_main);
    cursor: pointer;
    padding: 0;
    text-decoration: none;
}

.auth_switch button:hover {
    text-decoration: underline;
}

/* mobile */
@media (max-width: 500px) {
    .auth_modal {
        padding: 28px 20px 24px;
    }

    .auth_modal_header h2 {
        font-size: 1.25rem;
    }
}
</style>

<div class="auth_overlay" id="authOverlay" role="dialog" aria-modal="true" aria-labelledby="authModalTitle">
    <div class="auth_modal">

        <button class="auth_close" id="authClose" aria-label="Close">&times;</button>

        <!-- header -->
        <div class="auth_modal_header">
            <div class="auth_modal_logo">
                <img src="../assets/images/logo.png" alt="Tangier Vibes">
            </div>
            <h2 id="authModalTitle"><?= __('auth_modal_title') ?></h2>
            <p><?= __('auth_modal_subtitle') ?></p>
        </div>

        <!-- tabs -->
        <div class="auth_tabs" role="tablist">
            <button class="auth_tab active" data-tab="login" role="tab" aria-selected="true"><?= __('auth_modal_signin_tab') ?></button>
            <button class="auth_tab" data-tab="register" role="tab" aria-selected="false"><?= __('auth_modal_register_tab') ?></button>
        </div>

        <!-- social -->
        <div class="auth_social">
            <button class="auth_social_btn" disabled title="Coming soon">
                <i class="fa-brands fa-google"></i>
                <?= __('auth_modal_google') ?>
            </button>
        </div>

        <!-- divider -->
        <div class="auth_divider"><?= __('auth_modal_or') ?></div>

        <!-- login form -->
        <form class="auth_form active" id="authLoginForm" autocomplete="off">
            <input type="hidden" name="action" value="login">
            <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">

            <div class="form_group">
                <label for="authEmail"><?= __('auth_modal_email') ?></label>
                <input type="email" id="authEmail" name="email" placeholder="you@example.com" required autocomplete="email">
            </div>

            <div class="form_group">
                <label for="authPassword"><?= __('auth_modal_password') ?></label>
                <input type="password" id="authPassword" name="password" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" required autocomplete="current-password">
            </div>

            <div class="auth_error" id="authLoginError"></div>

            <button type="submit" class="auth_btn" id="authLoginBtn">
                <span class="auth_btn_text"><?= __('auth_modal_signin_btn') ?></span>
                <span class="auth_btn_spinner"></span>
            </button>
        </form>

        <!-- register form -->
        <form class="auth_form" id="authRegisterForm" autocomplete="off">
            <input type="hidden" name="action" value="register">
            <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">

            <div class="form_group">
                <label for="authName"><?= __('auth_modal_name') ?></label>
                <input type="text" id="authName" name="name" placeholder="Your name" required autocomplete="name">
            </div>

            <div class="form_group">
                <label for="authRegEmail"><?= __('auth_modal_email') ?></label>
                <input type="email" id="authRegEmail" name="email" placeholder="you@example.com" required autocomplete="email">
            </div>

            <div class="form_group">
                <label for="authRegPassword"><?= __('auth_modal_password') ?></label>
                <input type="password" id="authRegPassword" name="password" placeholder="Create a password" required autocomplete="new-password">
            </div>

            <div class="form_group">
                <label for="authConfirmPassword"><?= __('auth_modal_confirm_password') ?></label>
                <input type="password" id="authConfirmPassword" name="confirm_password" placeholder="Confirm password" required autocomplete="new-password">
            </div>

            <div class="auth_error" id="authRegisterError"></div>

            <button type="submit" class="auth_btn" id="authRegisterBtn">
                <span class="auth_btn_text"><?= __('auth_modal_register_btn') ?></span>
                <span class="auth_btn_spinner"></span>
            </button>
        </form>

    </div>
</div>

<script>
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
            errorEl.textContent = 'An unexpected error occurred. Please try again.';
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
</script>
