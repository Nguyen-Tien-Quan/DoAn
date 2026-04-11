<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ❌ CHẶN nếu chưa verify OTP
if (!isset($_SESSION['otp_verified']) || !$_SESSION['otp_verified']) {
    header("Location: index.php?url=forgot-password");
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    if ($password !== $confirm) {
        $message = "❌ Mật khẩu không khớp";
    } else {

        $conn = getDB();
        $email = $_SESSION['reset_email'];

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $conn->prepare("UPDATE users SET password = ? WHERE email = ?")
             ->execute([$hash, $email]);

        $conn->prepare("DELETE FROM password_resets WHERE email = ?")
             ->execute([$email]);

        unset($_SESSION['otp_verified']);
        unset($_SESSION['reset_email']);

        $message = "✅ Đổi mật khẩu thành công!";
    }
}
?>

<main class="auth">

    <!-- INTRO giống forgot password -->
    <div class="auth__intro d-md-none">
        <img src="./assets/img/auth/forgot-password.png" class="auth__intro-img" />
    </div>

    <div class="auth__content">
        <div class="auth__content-inner">

            <!-- LOGO -->
            <a href="./" class="logo">
                <img src="./assets/icons/logo.svg" class="logo__img" />
                <h2 class="logo__title">TrQShop</h2>
            </a>

            <h1 class="auth__heading">Thay đổi mật khẩu mới</h1>

            <?php if ($message): ?>
                <div class="auth__message message message--success">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="form auth__form">

                <div class="form__group">
                    <div class="form__text-input">
                        <input type="password"
                               name="password"
                               placeholder="Mật khẩu mới"
                               class="form__input"
                               required>
                    </div>
                </div>

                <div class="form__group">
                    <div class="form__text-input">
                        <input type="password"
                               name="confirm"
                               placeholder="Nhập lại mật khẩu "
                               class="form__input"
                               required>
                    </div>
                </div>

                <div class="form__group auth__btn-group">
                    <button class="btn btn--primary auth__btn">
                        Đổi mật khẩu
                    </button>
                </div>

            </form>

        </div>
    </div>
</main>
