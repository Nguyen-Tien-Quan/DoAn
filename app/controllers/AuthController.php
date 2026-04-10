<?php
require_once __DIR__ . '/../../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';

/**
 * Xử lý login
 */
function handleLogin() {
    $conn = getDB();

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    // Validate
    if (empty($email) || empty($password)) {
        return "❌ Vui lòng nhập email và mật khẩu";
    }

    // Tìm user
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        return "❌ Không tồn tại tài khoản";
    }

    // Check password
    if (!password_verify($password, $user['password'])) {
        return "❌ Sai mật khẩu";
    }

    // Check status (nếu có khóa tài khoản)
    if (isset($user['status']) && $user['status'] == 0) {
        return "❌ Tài khoản đã bị khóa";
    }

    // ✅ Lưu session (xóa password cho an toàn)
    unset($user['password']);
    $_SESSION['user'] = $user;

    // ✅ Remember Me
    if ($remember) {
        $token = bin2hex(random_bytes(32));

        $update = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
        $update->execute([$token, $user['id']]);

        setcookie('remember_me', $token, [
            'expires' => time() + 30*24*60*60,
            'path' => '/',
            'httponly' => true,
            'secure' => false // đổi true nếu dùng HTTPS
        ]);
    } else {
        setcookie('remember_me', '', time() - 3600, '/');

        $update = $conn->prepare("UPDATE users SET remember_token = NULL WHERE id = ?");
        $update->execute([$user['id']]);
    }

    // ✅ PHÂN QUYỀN REDIRECT
    switch ($user['role_id']) {
        case 1:
            header("Location: admin.php"); // admin
            break;

        case 2:
            header("Location: staff.php"); // nhân viên
            break;

        case 3:
        default:
            header("Location: index.php"); // user
            break;
    }

    exit;
}

/**
 * Xử lý tự động login nếu có cookie remember_me
 */
function autoLogin() {
    if (!isset($_SESSION['user']) && isset($_COOKIE['remember_me'])) {
        $token = $_COOKIE['remember_me'];
        $conn = getDB();
        $stmt = $conn->prepare("SELECT * FROM users WHERE remember_token = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $_SESSION['user'] = $user;
            // refresh cookie 30 ngày
            setcookie('remember_me', $token, time() + 30*24*60*60, '/', '', false, true);
            header("Location: index.php");
            exit;
        } else {
            setcookie('remember_me', '', time() - 3600, '/', '', false, true);
        }
    }
}

/**
 * Xử lý logout
 */
function handleLogout() {
    $conn = getDB();
    if (isset($_SESSION['user'])) {
        $update = $conn->prepare("UPDATE users SET remember_token = NULL WHERE id = ?");
        $update->execute([$_SESSION['user']['id']]);
    }

    session_destroy();

    if (isset($_COOKIE['remember_me'])) {
        setcookie('remember_me', '', time() - 3600, '/', '', false, true);
    }

    header("Location: index.php?url=login");
    exit();
}

    function handleRegister() {
    $conn = getDB();

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirmation = $_POST['password_confirmation'] ?? '';

    if (empty($email) || empty($password)) {
        return "❌ Vui lòng nhập đủ thông tin";
    }

    if ($password !== $password_confirmation) {
        return "❌ Mật khẩu xác nhận không khớp";
    }

    // Check email
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return "❌ Email đã tồn tại";
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // ✅ tạo username random
    $randomNumber = rand(1000, 9999);
    $name = "user_" . $randomNumber;

    // ❗ tránh trùng (xịn hơn)
    do {
        $stmt = $conn->prepare("SELECT id FROM users WHERE name = ?");
        $stmt->execute([$name]);
        $exists = $stmt->fetch();

        if ($exists) {
            $randomNumber = rand(1000, 9999);
            $name = "user_" . $randomNumber;
        }
    } while ($exists);

    // Insert
    $stmt = $conn->prepare("
        INSERT INTO users (name, email, password, role_id, status, created_at, updated_at)
        VALUES (?, ?, ?, ?, 1, NOW(), NOW())
    ");

    $roleId = 3;

    if ($stmt->execute([$name, $email, $hashedPassword, $roleId])) {
        return "✅ Đăng ký thành công!";
    } else {
        $error = $stmt->errorInfo();
        return "❌ Đăng ký thất bại: " . ($error[2] ?? 'Không xác định');
    }
}
/**
 * Gọi autoLogin ngay khi load file
 */
autoLogin();
