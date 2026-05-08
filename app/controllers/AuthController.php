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
    $password = trim($_POST['password'] ?? '');
    $remember = isset($_POST['remember']);

    // ======================
    // VALIDATE
    // ======================
    if ($email === '' || $password === '') {
        return "❌ Vui lòng nhập email và mật khẩu";
    }

    // ======================
    // TÌM USER THEO EMAIL
    // ======================
    $stmt = $conn->prepare("
        SELECT *
        FROM users
        WHERE email = ?
        LIMIT 1
    ");

    $stmt->execute([$email]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // ======================
    // KHÔNG TỒN TẠI
    // ======================
    if (!$user) {
        return "❌ Tài khoản không tồn tại";
    }

    // ======================
    // CHECK STATUS
    // ======================
    if ((int)$user['status'] !== 1) {
        return "❌ Tài khoản đã bị khóa";
    }

    // ======================
    // CHECK PASSWORD
    // ======================
    if (!password_verify($password, $user['password'])) {
        return "❌ Sai mật khẩu";
    }

    // ======================
    // LOGIN SUCCESS
    // ======================
    unset($user['password']);

    $_SESSION['user'] = $user;

    // ======================
    // REMEMBER ME
    // ======================
    if ($remember) {

        $token = bin2hex(random_bytes(32));

        $update = $conn->prepare("
            UPDATE users
            SET remember_token = ?
            WHERE id = ?
        ");

        $update->execute([$token, $user['id']]);

        setcookie('remember_me', $token, [
            'expires'  => time() + (30 * 24 * 60 * 60),
            'path'     => '/',
            'httponly' => true,
            'secure'   => false,
            'samesite' => 'Lax'
        ]);

    } else {

        setcookie('remember_me', '', time() - 3600, '/');

        $update = $conn->prepare("
            UPDATE users
            SET remember_token = NULL
            WHERE id = ?
        ");

        $update->execute([$user['id']]);
    }

    // ======================
    // REDIRECT ROLE
    // ======================
    switch ((int)$user['role_id']) {

        case 1:
            header("Location: admin.php");
            break;

        case 2:
            header("Location: staff.php");
            break;

        case 4:
            header("Location: shipper.php");
            break;

        default:
            header("Location: index.php");
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
            // Phân quyền redirect
            switch ($user['role_id']) {
                case 1: header("Location: admin.php"); break;
                case 2: header("Location: staff.php"); break;
                case 4: header("Location: shipper.php"); break;
                default: header("Location: index.php");
            }
            exit;
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

    // Check email tồn tại
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        return "❌ Email đã tồn tại";
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Tạo username random
    do {
        $randomNumber = rand(1000, 9999);
        $name = "user_" . $randomNumber;

        $check = $conn->prepare("SELECT id FROM users WHERE name = ?");
        $check->execute([$name]);

    } while ($check->fetch());

    try {

        $conn->beginTransaction();

        // ======================
        // INSERT USERS
        // ======================
        $stmt = $conn->prepare("
            INSERT INTO users (
                role_id,
                name,
                email,
                password,
                phone,
                avatar,
                status,
                created_at,
                updated_at
            )
            VALUES (
                ?, ?, ?, ?, ?, ?, 1, NOW(), NOW()
            )
        ");

        $roleId = 3;

        $defaultPhone = '';
        $defaultAvatar = 'default.png';

        $stmt->execute([
            $roleId,
            $name,
            $email,
            $hashedPassword,
            $defaultPhone,
            $defaultAvatar
        ]);

        // Lấy user_id mới
        $userId = $conn->lastInsertId();

        // ======================
        // INSERT CUSTOMER MẶC ĐỊNH
        // ======================
        $customerStmt = $conn->prepare("
            INSERT INTO customers (
                user_id,
                full_name,
                phone,
                email,
                address,
                gender,
                birthday,
                created_at,
                updated_at
            )
            VALUES (
                ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()
            )
        ");

        $customerStmt->execute([
            $userId,
            $name,
            '',
            $email,
            '',
            'other',
            null
        ]);

        // ======================
        // TẠO CART MẶC ĐỊNH
        // ======================
        $cartStmt = $conn->prepare("
            INSERT INTO carts (
                user_id,
                created_at,
                updated_at
            )
            VALUES (
                ?, NOW(), NOW()
            )
        ");

        $cartStmt->execute([$userId]);

        $conn->commit();

        return "✅ Đăng ký thành công!";

    } catch (Exception $e) {

        $conn->rollBack();

        return "❌ Lỗi đăng ký: " . $e->getMessage();
    }
}
/**
 * Gọi autoLogin ngay khi load file
 */
autoLogin();
