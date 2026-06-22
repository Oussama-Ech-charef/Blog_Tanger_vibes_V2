<link rel="stylesheet" href="../assets/css/auth_modal.css">

<div class="auth_overlay" id="authOverlay" role="dialog" aria-modal="true" aria-labelledby="authModalTitle" data-unexpected-error="<?= __('auth_error_unexpected') ?>">
    <div class="auth_modal">

        <button class="auth_close" id="authClose" aria-label="<?= __('auth_modal_close_aria') ?>">&times;</button>

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
            <button class="auth_social_btn" disabled title="<?= __('auth_modal_coming_soon') ?>">
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
                <input type="email" id="authEmail" name="email" placeholder="<?= __('auth_modal_email_placeholder') ?>" required autocomplete="email">
            </div>

            <div class="form_group">
                <label for="authPassword"><?= __('auth_modal_password') ?></label>
                <input type="password" id="authPassword" name="password" placeholder="<?= __('auth_modal_password_placeholder') ?>" required autocomplete="current-password">
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
                <input type="text" id="authName" name="name" placeholder="<?= __('auth_modal_name_placeholder') ?>" required autocomplete="name">
            </div>

            <div class="form_group">
                <label for="authRegEmail"><?= __('auth_modal_email') ?></label>
                <input type="email" id="authRegEmail" name="email" placeholder="<?= __('auth_modal_email_placeholder') ?>" required autocomplete="email">
            </div>

            <div class="form_group">
                <label for="authRegPassword"><?= __('auth_modal_password') ?></label>
                <input type="password" id="authRegPassword" name="password" placeholder="<?= __('auth_modal_password_placeholder') ?>" required autocomplete="new-password">
            </div>

            <div class="form_group">
                <label for="authConfirmPassword"><?= __('auth_modal_confirm_password') ?></label>
                <input type="password" id="authConfirmPassword" name="confirm_password" placeholder="<?= __('auth_modal_confirm_placeholder') ?>" required autocomplete="new-password">
            </div>

            <div class="auth_error" id="authRegisterError"></div>

            <button type="submit" class="auth_btn" id="authRegisterBtn">
                <span class="auth_btn_text"><?= __('auth_modal_register_btn') ?></span>
                <span class="auth_btn_spinner"></span>
            </button>
        </form>

    </div>
</div>

<script src="../assets/js/auth_modal.js"></script>
