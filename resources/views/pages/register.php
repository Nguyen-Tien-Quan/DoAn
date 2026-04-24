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
            Giá trị thương hiệu cao cấp, sản phẩm chất lượng và dịch vụ đổi mới
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

            <h1 class="auth__heading">Đăng ký</h1>
            <p class="auth__desc">
                Hãy tạo tài khoản của bạn, mua sắm thông minh và tiết kiệm hơn.
            </p>

            <!-- FORM ĐĂNG KÝ -->
            <form action="<?= $base ?>index.php?url=register" method="POST" class="form auth__form">

                <!-- Email -->
                <div class="form__group">
                    <div class="form__text-input">
                        <input type="email" name="email" placeholder="Email" class="form__input" required />
                        <img src="<?= $base ?>assets/icons/message.svg" class="form__input-icon" />
                    </div>
                </div>

                <!-- Mật khẩu -->
                <div class="form__group">
                    <div class="form__text-input">
                        <input type="password" name="password" placeholder="Mật khẩu"
                               class="form__input" required minlength="6" />
                        <img src="<?= $base ?>assets/icons/lock.svg" class="form__input-icon" />
                    </div>
                </div>

                <!-- Xác nhận mật khẩu -->
                <div class="form__group">
                    <div class="form__text-input">
                        <input type="password" name="password_confirmation"
                               placeholder="Xác nhận mật khẩu"
                               class="form__input" required minlength="6" />
                        <img src="<?= $base ?>assets/icons/lock.svg" class="form__input-icon" />
                    </div>
                </div>

                <!-- Đồng ý điều khoản -->
                <div class="form__group form__group--inline">
                    <label class="form__checkbox">
                        <input type="checkbox" name="agree" class="form__checkbox-input d-none" required />
                        <span class="form__checkbox-label">Tôi đồng ý với điều khoản</span>
                    </label>
                </div>

                <!-- Nút bấm -->
                <div class="form__group auth__btn-group">
                    <button type="submit" class="btn btn--primary auth__btn form__submit-btn">
                        Đăng ký
                    </button>

                    <button type="button" class="btn btn--outline auth__btn btn--no-margin">
                        <img src="<?= $base ?>assets/icons/google.svg" class="btn__icon icon" />
                        Đăng nhập với Google
                    </button>
                </div>
                <?php if (!empty($error)): ?>
                    <p class="form__error-pass" style="margin-top: 20px;"><?= $error ?></p>
                <?php endif; ?>
            </form>

            <p class="auth__text">
                Bạn đã có tài khoản?
                <a href="<?= $base ?>index.php?url=login" class="auth__link auth__text-link">
                    Đăng nhập
                </a>
            </p>
        </div>
    </div>
</main>
<script>
    window.dispatchEvent(new Event("template-loaded"));
</script>
