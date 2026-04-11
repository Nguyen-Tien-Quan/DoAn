<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$conn = getDB();

$message = '';
$error = '';
$showOtpModal = false;

// ================= OTP TIME CONTROL =================
if (!isset($_SESSION['otp_expire'])) {
    $_SESSION['otp_expire'] = 0;
}

// ================= GỬI OTP =================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'send_otp') {

    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "❌ Email không hợp lệ";
    } else {

        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = "❌ Email không tồn tại";
        } else {

            $otp = rand(100000, 999999);

            // ⏱ 2 phút
            $_SESSION['otp_expire'] = time() + 120;

            $conn->prepare("DELETE FROM password_resets WHERE email=?")
                 ->execute([$email]);

            $stmt = $conn->prepare("
                INSERT INTO password_resets (email, otp, expire_at)
                VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 2 MINUTE))
            ");
            $stmt->execute([$email, $otp]);

            $_SESSION['reset_email'] = $email;

            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'nguyentienquan1st@gmail.com';
                $mail->Password = 'nrlaktvinfboqyfc';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('nguyentienquan1st@gmail.com', 'TRQShop');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Ma Xac Minh OTP';
                $mail->Body = "
                    <div style='font-family:Arial'>
                        <h2>Mã OTP của bạn</h2>
                        <h1 style='color:#4f46e5'>$otp</h1>
                        <p>Hiệu lực: 2 phút</p>
                    </div>
                ";

                $mail->send();

                $message = "✅ OTP đã được gửi!";
                $showOtpModal = true;

            } catch (Exception $e) {
                $error = "❌ Lỗi gửi mail: " . $mail->ErrorInfo;
            }
        }
    }
}

// ================= VERIFY OTP =================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'verify_otp') {

    $otp = $_POST['otp'] ?? '';
    $email = $_SESSION['reset_email'] ?? '';

    // check hết hạn PHP session
    if (time() > ($_SESSION['otp_expire'] ?? 0)) {
        $error = "❌ OTP đã hết hạn, vui lòng gửi lại";
        $showOtpModal = false;
    } else {

        $stmt = $conn->prepare("
            SELECT * FROM password_resets
            WHERE email=? AND otp=? AND expire_at > NOW()
        ");
        $stmt->execute([$email, $otp]);
        $check = $stmt->fetch();

        if ($check) {
            $_SESSION['otp_verified'] = true;
            header("Location: index.php?url=reset-password");
            exit;
        } else {
            $error = "❌ OTP sai hoặc hết hạn";
            $showOtpModal = true;
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

            <?php if ($message): ?>
                <div class="auth__message message message--success"><?= $message ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="auth__message message message--error"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" class="form auth__form">
                <input type="hidden" name="action" value="send_otp">

                <div class="form__group">
                    <div class="form__text-input">
                        <input type="email" name="email" placeholder="Email" class="form__input" required />
                    </div>
                </div>

                <div class="form__group auth__btn-group">
                    <button class="btn btn--primary auth__btn">
                        Gửi mã OTP
                    </button>
                </div>
            </form>

        </div>
    </div>
</main>

<!-- ================= OTP MODAL ================= -->
<div id="otpModal" class="otp-modal <?= $showOtpModal ? 'show' : '' ?>">

    <div class="otp-modal-content">

        <h3>Nhập mã OTP</h3>

        <p class="otp-timer">
            Hết hạn sau: <span id="timer">02:00</span>
        </p>

        <form method="POST">
            <input type="hidden" name="action" value="verify_otp">

            <input type="text"
                   name="otp"
                   maxlength="6"
                   placeholder="Nhập 6 số OTP"
                   class="otp-input"
                   required>

            <button class="btn btn--primary otp-btn">
                Xác nhận
            </button>
        </form>

        <form method="POST" style="margin-top:10px;">
            <input type="hidden" name="action" value="send_otp">
            <input type="hidden" name="email" value="<?= $_SESSION['reset_email'] ?? '' ?>">

            <button class="btn otp-resend">
                Gửi lại OTP
            </button>
        </form>

        <button class="btn otp-cancel" onclick="closeModal()">
            Đóng
        </button>

    </div>
</div>

<!-- ================= STYLE ================= -->
<style>
.otp-modal{
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.55);
    display:none;
    justify-content:center;
    align-items:center;
    z-index:9999;
}

.otp-modal.show{ display:flex; }

.otp-modal-content{
    width:360px;
    background:#fff;
    padding:25px;
    border-radius:16px;
    text-align:center;
    box-shadow:0 20px 50px rgba(0,0,0,.2);
}

.otp-timer{
    margin:10px 0;
    color:#ef4444;
    font-weight:600;
}

.otp-input{
    width:100%;
    padding:12px;
    border:1px solid #ddd;
    border-radius:10px;
    text-align:center;
    font-size:18px;
    letter-spacing:3px;
    margin:10px 0;
}

.otp-btn{
    width:100%;
}

.otp-resend{
    width:100%;
    background:#f3f4f6;
    margin-top:10px;
}

.otp-cancel{
    width:100%;
    margin-top:10px;
    background:#ef4444;
    color:#fff;
}
</style>

<!-- ================= SCRIPT ================= -->
<script>
let expireTime = <?= $_SESSION['otp_expire'] ?? 0 ?> * 1000;

function startTimer() {
    let timer = document.getElementById("timer");

    let x = setInterval(function () {

        let now = new Date().getTime();
        let distance = expireTime - now;

        if (distance <= 0) {
            clearInterval(x);
            timer.innerHTML = "Hết hạn";

            document.querySelector(".otp-input").disabled = true;
        } else {

            let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            let seconds = Math.floor((distance % (1000 * 60)) / 1000);

            timer.innerHTML =
                (minutes < 10 ? "0" + minutes : minutes) + ":" +
                (seconds < 10 ? "0" + seconds : seconds);
        }

    }, 1000);
}

function closeModal(){
    document.getElementById("otpModal").classList.remove("show");
}

window.onload = function(){
    if(document.getElementById("otpModal").classList.contains("show")){
        startTimer();
    }
};
</script>
