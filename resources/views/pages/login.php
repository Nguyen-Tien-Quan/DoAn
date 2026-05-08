<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $error = handleLogin();
}

// email autofill
$rememberedEmail = '';

if (isset($_SESSION['user'])) {

    $rememberedEmail = $_SESSION['user']['email'];

} elseif (isset($_COOKIE['remember_me'])) {

    $conn = getDB();

    $token = $_COOKIE['remember_me'];

    $stmt = $conn->prepare("
        SELECT email
        FROM users
        WHERE remember_token = ?
        LIMIT 1
    ");

    $stmt->execute([$token]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $rememberedEmail = $row['email'];
    }
}
?>
<main class="auth">
    <div class="auth__intro d-md-none">
        <img src="<?= $base ?>assets/img/auth/intro.svg" alt="" class="auth__intro-img" />
        <p class="auth__intro-text">
            Giá trị thương hiệu cao cấp, sản phẩm chất lượng và dịch vụ đổi mới
        </p>
    </div>

    <div class="auth__content">
        <div class="auth__content-inner">
            <a href="<?= $base ?>" class="logo">
                <img src="<?= $base ?>assets/icons/logo.svg" alt="TRQShop" class="logo__img" />
                <h2 class="logo__title">TRQShop</h2>
            </a>

            <h1 class="auth__heading">Xin chào trở lại!</h1>
            <p class="auth__desc">
                Chào mừng bạn quay lại đăng nhập. Là khách hàng thân thiết, bạn có thể truy cập tất cả thông tin đã lưu trước đó.
            </p>

            <form action="" method="POST" class="form auth__form">

                <div class="form__group">
                    <div class="form__text-input">
                        <input
                            type="email"
                            name="email"
                            placeholder="Email"
                            value="<?= htmlspecialchars($rememberedEmail) ?>"
                            class="form__input"
                            required
                        />
                        <img src="<?= $base ?>assets/icons/message.svg" class="form__input-icon" />
                    </div>
                    <p class="form__error">Email không đúng định dạng</p>
                </div>

                <div class="form__group">
                    <div class="form__text-input">
                        <input
                            type="password"
                            name="password"
                            placeholder="Mật khẩu"
                            class="form__input"
                            required
                            minlength="6"
                        />
                        <img src="<?= $base ?>assets/icons/lock.svg" class="form__input-icon" />
                    </div>
                    <p class="form__error">Mật khẩu phải ít nhất 6 ký tự</p>
                </div>

                <div class="form__group form__group--inline">
                    <label class="form__checkbox">
                        <input type="checkbox" name="remember" class="form__checkbox-input d-none"
                        <?= isset($_COOKIE['remember_me']) ? 'checked' : '' ?> />
                        <span class="form__checkbox-label">Ghi nhớ đăng nhập</span>
                    </label>

                    <a href="<?= $base ?>index.php?url=forgot-password" class="auth__link form__pull-right">Quên mật khẩu?</a>
                </div>

                <div class="form__group auth__btn-group">
                    <button type="submit" class="btn btn--primary auth__btn form__submit-btn">
                        Đăng nhập
                    </button>
                </div>

                <?php if (!empty($error)): ?>
                    <p class="form__error-pass" style="margin-top: 20px; color: red;"><?= $error ?></p>
                <?php endif; ?>
            </form>

            <p class="auth__text">
                Chưa có tài khoản?
                <a href="<?= $base ?>index.php?url=register" class="auth__link auth__text-link">
                    Đăng ký
                </a>
            </p>
        </div>
    </div>
</main>

<script>
    window.dispatchEvent(new Event("template-loaded"));
</script>
