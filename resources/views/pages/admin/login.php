<?php
session_start();
require_once 'includes/db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Vui lòng nhập email và mật khẩu.';
    } else {
        // Lấy user kèm tên role từ bảng roles
        $stmt = $pdo->prepare("
            SELECT u.*, r.name as role_name 
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.id 
            WHERE u.email = ?
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] != 1) {
                $error = 'Tài khoản đã bị khóa.';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['role_id'] = $user['role_id'];
                $_SESSION['role_name'] = $user['role_name']; // 'admin', 'staff', 'customer'
                header('Location: index.php');
                exit;
            }
        } else {
            $error = 'Email hoặc mật khẩu không đúng.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head><meta charset="UTF-8"><title>Đăng nhập</title><link href="https://fonts.googleapis.com/css?family=Nunito&display=swap" rel="stylesheet"><link href="css/sb-admin-2.min.css" rel="stylesheet"></head>
<body class="bg-gradient-primary">
<div class="container"><div class="row justify-content-center"><div class="col-xl-5 col-lg-6 col-md-8">
<div class="card o-hidden border-0 shadow-lg my-5"><div class="card-body p-4">
<div class="text-center mb-4"><h1 class="h4 text-gray-900">Đăng nhập hệ thống</h1></div>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<form method="POST"><div class="form-group"><input type="email" name="email" class="form-control form-control-user" placeholder="Email" required autofocus></div>
<div class="form-group"><input type="password" name="password" class="form-control form-control-user" placeholder="Mật khẩu" required></div>
<button type="submit" class="btn btn-primary btn-user btn-block">Đăng nhập</button></form>
<hr><div class="text-center"><a class="small" href="register.php">Đăng ký tài khoản</a></div>
</div></div></div></div></div>
<script src="vendor/jquery/jquery.min.js"></script><script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body></html>