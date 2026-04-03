<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';
$conn = getDB(); // kết nối PDO

// --- Auto login nếu có cookie remember_me ---
if (!isset($_SESSION['user']) && isset($_COOKIE['remember_me'])) {
    $token = $_COOKIE['remember_me'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE remember_token = ? AND status = 1");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $_SESSION['user'] = $user;
        // refresh cookie 30 ngày
        setcookie('remember_me', $token, time() + 30*24*60*60, '/', '', false, true);
        header("Location: index.php");
        exit;
    } else {
        // xóa cookie nếu token không hợp lệ
        setcookie('remember_me', '', time() - 3600, '/', '', false, true);
    }
}

// --- Xử lý form login ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);

    // kiểm tra email tồn tại
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND status = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;

        if ($remember) {
            // tạo token ngẫu nhiên và lưu vào DB + cookie 30 ngày
            $token = bin2hex(random_bytes(32));
            $update = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
            $update->execute([$token, $user['id']]);
            setcookie('remember_me', $token, time() + 30*24*60*60, '/', '', false, true);
        } else {
            // xóa cookie và token nếu không tick remember
            setcookie('remember_me', '', time() - 3600, '/', '', false, true);
            $update = $conn->prepare("UPDATE users SET remember_token = NULL WHERE id = ?");
            $update->execute([$user['id']]);
        }

        header("Location: index.php"); // redirect sau login
        exit;
    } else {
        $error = '❌ Email hoặc mật khẩu không đúng';
    }
}

// nếu muốn, email input tự điền từ cookie (token) hoặc session
$rememberedEmail = '';
if (isset($_SESSION['user'])) {
    $rememberedEmail = $_SESSION['user']['email'];
} elseif (isset($_COOKIE['remember_me'])) {
    // optional: nếu muốn lấy email từ DB theo token
    $token = $_COOKIE['remember_me'];
    $stmt = $conn->prepare("SELECT email FROM users WHERE remember_token = ? AND status = 1");
    $stmt->execute([$token]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) $rememberedEmail = $row['email'];
}
?>

<main class="auth">
    <div class="auth__intro d-md-none">
        <img src="<?= $base ?>assets/img/auth/intro.svg" alt="" class="auth__intro-img" />
        <p class="auth__intro-text">
            The best of luxury brand values, high quality products, and innovative services
        </p>
    </div>

    <div class="auth__content">
        <div class="auth__content-inner">
            <a href="<?= $base ?>" class="logo">
                <img src="<?= $base ?>assets/icons/logo.svg" alt="grocerymart" class="logo__img" />
                <h2 class="logo__title">grocerymart</h2>
            </a>

            <h1 class="auth__heading">Hello Again!</h1>
            <p class="auth__desc">
                Welcome back to sign in. As a returning customer, you have access to your previously saved all information.
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
                    <p class="form__error">Email is not in correct format</p>
                </div>

                <div class="form__group">
                    <div class="form__text-input">
                        <input
                            type="password"
                            name="password"
                            placeholder="Password"
                            class="form__input"
                            required
                            minlength="6"
                        />
                        <img src="<?= $base ?>assets/icons/lock.svg" class="form__input-icon" />
                    </div>
                    <p class="form__error">Password must be at least 6 characters</p>
                </div>

                <div class="form__group form__group--inline">
                    <label class="form__checkbox">
                        <input type="checkbox" name="remember" class="form__checkbox-input d-none"
                        <?= isset($_COOKIE['remember_me']) ? 'checked' : '' ?> />
                        <span class="form__checkbox-label">Remember me</span>
                    </label>

                    <a href="<?= $base ?>index.php?url=forgot-password" class="auth__link form__pull-right">Forgot password?</a>
                </div>

                <div class="form__group auth__btn-group">
                    <button type="submit" class="btn btn--primary auth__btn form__submit-btn">
                        Sign In
                    </button>
                </div>

                <?php if (!empty($error)): ?>
                    <p class="form__error-pass" style="margin-top: 20px; color: red;"><?= $error ?></p>
                <?php endif; ?>
            </form>

            <p class="auth__text">
                Don’t have an account yet?
                <a href="<?= $base ?>index.php?url=register" class="auth__link auth__text-link">
                    Sign Up
                </a>
            </p>
        </div>
    </div>
</main>

<script>
    window.dispatchEvent(new Event("template-loaded"));
</script>
