<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "❌ Email không hợp lệ";
    } else {

        $conn = getDB();

        // check user tồn tại
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            $message = "❌ Email không tồn tại";
        } else {

            // XÓA token cũ
            $conn->prepare("DELETE FROM password_resets WHERE email = ?")
                 ->execute([$email]);

            // tạo token
            $token = bin2hex(random_bytes(32));
            $expire = date("Y-m-d H:i:s", strtotime("+15 minutes"));

            // lưu DB
            $stmt = $conn->prepare("
                INSERT INTO password_resets (email, token, expire_at)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$email, $token, $expire]);

            $link = "http://localhost/DoAn/public/index.php?url=reset-password&token=$token";

            // ================= GỬI MAIL =================
            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'no-reply@trqshop.com'; // 🔥 đổi
                $mail->Password = 'your_app_password';   // 🔥 đổi
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('no-reply@trqshop.com', 'TRQShop');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Reset Password';
                $mail->Body = "
                    <h3>Reset Password</h3>
                    <p>Click link bên dưới để đổi mật khẩu:</p>
                    <a href='$link'>$link</a>
                    <p>Link hết hạn sau 15 phút</p>
                ";

                $mail->send();

                $message = "✅ Đã gửi email! Kiểm tra Gmail.";
            } catch (Exception $e) {
                $message = "❌ Lỗi gửi mail: " . $mail->ErrorInfo;
            }
        }
    }
}
?>
 <main class="auth">
    <div class="auth__intro d-md-none">
        <img src="./assets/img/auth/forgot-password.png" class="auth__intro-img" />
    </div>

    <div class="auth__content">
        <div class="auth__content-inner">

            <a href="./" class="logo">
                <img src="./assets/icons/logo.svg" class="logo__img" />
                <h2 class="logo__title">TrQShop</h2>
            </a>

            <h1 class="auth__heading">Reset your password</h1>
            <p class="auth__desc">
                Enter your email and we'll send you a link to reset your password.
            </p>

            <!-- MESSAGE -->
            <?php if ($message): ?>
                <div class="auth__message message message--success">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <!-- FORM -->
            <form method="POST" class="form auth__form auth__form-forgot">
                <div class="form__group">
                    <div class="form__text-input">
                        <input
                            type="email"
                            name="email"
                            placeholder="Email"
                            class="form__input"
                            required
                        />
                        <img src="./assets/icons/message.svg" class="form__input-icon" />
                    </div>
                </div>

                <div class="form__group auth__btn-group">
                    <button class="btn btn--primary auth__btn">
                        Reset password
                    </button>
                </div>
            </form>

            <p class="auth__text">
                <a href="<?= $base ?>index.php?url=login" class="auth__link auth__text-link">
                    Back to Sign In
                </a>
            </p>

        </div>
    </div>
</main>
