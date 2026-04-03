<?php
require_once __DIR__ . '/../../config/database.php';

$token = $_GET['token'] ?? '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    if ($password !== $confirm) {
        $message = "❌ Mật khẩu không khớp";
    } else {

        $conn = getDB();

        $stmt = $conn->prepare("
            SELECT * FROM password_resets
            WHERE token = ? AND expire_at > NOW()
        ");
        $stmt->execute([$token]);
        $reset = $stmt->fetch();

        if (!$reset) {
            $message = "❌ Token không hợp lệ hoặc hết hạn";
        } else {

            $hash = password_hash($password, PASSWORD_DEFAULT);

            // update password
            $conn->prepare("UPDATE users SET password = ? WHERE email = ?")
                 ->execute([$hash, $reset['email']]);

            // xóa token
            $conn->prepare("DELETE FROM password_resets WHERE email = ?")
                 ->execute([$reset['email']]);

            $message = "✅ Đổi mật khẩu thành công!";
        }
    }
}
?>
<main class="auth">
    <div class="auth__content">
        <div class="auth__content-inner">

            <h1 class="auth__heading">Set new password</h1>

            <?php if ($message): ?>
                <div class="auth__message message message--success">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="form">
                <input type="hidden" name="token" value="<?= $token ?>">

                <input type="password" name="password" placeholder="New password" required class="form__input"><br><br>
                <input type="password" name="confirm" placeholder="Confirm password" required class="form__input"><br><br>

                <button class="btn btn--primary">Update Password</button>
            </form>

        </div>
    </div>
</main>
