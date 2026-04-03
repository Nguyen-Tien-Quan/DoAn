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

    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']); // checkbox remember me

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if (password_verify($password, $user['password'])) {
            // Lưu session
            $_SESSION['user'] = $user;

            // Remember Me
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                $update = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                $update->execute([$token, $user['id']]);
                setcookie('remember_me', $token, time() + 30*24*60*60, '/', '', false, true);
            } else {
                setcookie('remember_me', '', time() - 3600, '/', '', false, true);
                $update = $conn->prepare("UPDATE users SET remember_token = NULL WHERE id = ?");
                $update->execute([$user['id']]);
            }

            header("Location: index.php");
            exit;
        } else {
            return "❌ Sai mật khẩu";
        }
    } else {
        return "❌ Không tồn tại tài khoản";
    }
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
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $password_confirmation = $_POST['password_confirmation'] ?? '';
        if ($password !== $password_confirmation)
            { return "❌ Mật khẩu xác nhận không khớp"; }
        // Kiểm tra email tồn tại
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]); if ($stmt->fetch()) { return "❌ Email đã tồn tại"; }
        // Hash mật khẩu
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        // INSERT với đầy đủ các cột quan trọng + status = 1 (active)
        $stmt = $conn->prepare(" INSERT INTO users (name, email, password, role_id, status, created_at, updated_at) VALUES (?, ?, ?, ?, 1, NOW(), NOW()) ");
        $roleId = 3; // ← đổi thành 1, 2 hoặc 3 tùy theo role bạn đã insert vào bảng roles
        if ($stmt->execute([$name, $email, $hashedPassword, $roleId]))
            { return "✅ Đăng ký thành công! Bạn có thể đăng nhập ngay."; }
        else {
            // HIỂN THỊ LỖI CHI TIẾT để biết chính xác nguyên nhân
            $error = $stmt->errorInfo(); return "❌ Đăng ký thất bại: " . ($error[2] ?? 'Không xác định');
            }
    }
/**
 * Gọi autoLogin ngay khi load file
 */
autoLogin();
