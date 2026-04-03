<?php global $base; ?>

<main class="auth">
    <!-- Auth intro -->
    <div class="auth__intro">
        <a href="<?= $base ?>" class="logo auth__intro-logo d-none d-md-flex">
            <img src="<?= $base ?>assets/icons/logo.svg" class="logo__img" />
            <h1 class="logo__title">TRQShop</h1>
        </a>

        <img src="<?= $base ?>assets/img/auth/intro.svg" class="auth__intro-img" />

        <p class="auth__intro-text">
            The best of luxury brand values, high quality products, and innovative services
        </p>

        <button class="auth__intro-next d-none d-md-flex js-toggle" toggle-target="#auth-content">
            <img src="<?= $base ?>assets/img/auth/intro-arrow.svg" />
        </button>
    </div>

    <!-- Auth content -->
    <div id="auth-content" class="auth__content hide">
        <div class="auth__content-inner">
            <a href="<?= $base ?>" class="logo">
                <img src="<?= $base ?>assets/icons/logo.svg" class="logo__img" />
                <h1 class="logo__title">TRQShop</h1>
            </a>

            <h1 class="auth__heading">Sign Up</h1>
            <p class="auth__desc">
                Let’s create your account and Shop like a pro and save money.
            </p>

            <!-- FORM REGISTER -->
            <form action="<?= $base ?>index.php?url=register" method="POST" class="form auth__form">

                <!-- Email -->
                <div class="form__group">
                    <div class="form__text-input">
                        <input type="email" name="email" placeholder="Email" class="form__input" required />
                        <img src="<?= $base ?>assets/icons/message.svg" class="form__input-icon" />
                    </div>
                </div>

                <!-- Password -->
                <div class="form__group">
                    <div class="form__text-input">
                        <input type="password" name="password" placeholder="Password"
                               class="form__input" required minlength="6" />
                        <img src="<?= $base ?>assets/icons/lock.svg" class="form__input-icon" />
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="form__group">
                    <div class="form__text-input">
                        <input type="password" name="password_confirmation"
                               placeholder="Confirm password"
                               class="form__input" required minlength="6" />
                        <img src="<?= $base ?>assets/icons/lock.svg" class="form__input-icon" />
                    </div>
                </div>

                <!-- Checkbox -->
                <div class="form__group form__group--inline">
                    <label class="form__checkbox">
                        <input type="checkbox" name="agree" class="form__checkbox-input d-none" required />
                        <span class="form__checkbox-label">Agree to terms</span>
                    </label>
                </div>

                <!-- Buttons -->
                <div class="form__group auth__btn-group">
                    <button type="submit" class="btn btn--primary auth__btn form__submit-btn">
                        Sign Up
                    </button>

                    <button type="button" class="btn btn--outline auth__btn btn--no-margin">
                        <img src="<?= $base ?>assets/icons/google.svg" class="btn__icon icon" />
                        Sign in with Google
                    </button>
                </div>
                <?php if (!empty($error)): ?>
                    <p class="form__error-pass" style="margin-top: 20px;"><?= $error ?></p>
                <?php endif; ?>
            </form>

            <p class="auth__text">
                You already have an account?
                <a href="<?= $base ?>index.php?url=login" class="auth__link auth__text-link">
                    Sign In
                </a>
            </p>
        </div>
    </div>
</main>
<script>
    window.dispatchEvent(new Event("template-loaded"));
</script>
