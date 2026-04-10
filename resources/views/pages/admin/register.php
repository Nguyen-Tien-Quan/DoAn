<?php
session_start();
require_once 'includes/db.php';

// Tự động thêm các cột còn thiếu trong bảng users (chạy 1 lần)
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN phone VARCHAR(30) NULL");
} catch (PDOException $e) {}
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN address TEXT NULL");
} catch (PDOException $e) {}
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN avatar VARCHAR(255) NULL");
} catch (PDOException $e) {}
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN role_id INT DEFAULT 3");
} catch (PDOException $e) {}
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN status TINYINT(1) DEFAULT 1");
} catch (PDOException $e) {}

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Vui lòng điền đầy đủ họ tên, email và mật khẩu.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ.';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự.';
    } elseif ($password !== $confirm_password) {
        $error = 'Xác nhận mật khẩu không khớp.';
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $error = 'Email đã được sử dụng.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $role_customer = 3;
            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, password, phone, address, role_id, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, 1, NOW())
            ");
            if ($stmt->execute([$name, $email, $hashed, $phone, $address, $role_customer])) {
                $success = 'Đăng ký thành công! Bạn có thể <a href="login.php">đăng nhập</a> ngay.';
            } else {
                $error = 'Đăng ký thất bại, vui lòng thử lại sau.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký - FastFood Admin</title>
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-primary">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-6 col-lg-8 col-md-10">
                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <h1 class="h4 text-gray-900">Tạo tài khoản mới</h1>
                        </div>
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                        <?php endif; ?>
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        <form method="POST" action="">
                            <div class="form-group">
                                <input type="text" name="name" class="form-control" placeholder="Họ và tên" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <input type="email" name="email" class="form-control" placeholder="Email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <input type="password" name="password" class="form-control" placeholder="Mật khẩu (ít nhất 6 ký tự)" required>
                            </div>
                            <div class="form-group">
                                <input type="password" name="confirm_password" class="form-control" placeholder="Xác nhận mật khẩu" required>
                            </div>
                            <div class="form-group">
                                <input type="text" name="phone" class="form-control" placeholder="Số điện thoại" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <textarea name="address" class="form-control" rows="2" placeholder="Địa chỉ"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-user btn-block">Đăng ký</button>
                        </form>
                        <hr>
                        <div class="text-center">
                            <a class="small" href="login.php">Đã có tài khoản? Đăng nhập</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>