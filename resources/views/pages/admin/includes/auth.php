<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/db.php';

$stmt = $pdo->prepare("
    SELECT u.*, r.name as role_name 
    FROM users u 
    LEFT JOIN roles r ON u.role_id = r.id 
    WHERE u.id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$currentUser = $stmt->fetch();

if (!$currentUser) {
    session_destroy();
    header('Location: login.php');
    exit;
}

if ($currentUser['status'] != 1) {
    session_destroy();
    header('Location: login.php?error=account_locked');
    exit;
}

// Cập nhật session
$_SESSION['user_name'] = $currentUser['name'];
$_SESSION['role_name'] = $currentUser['role_name'];
$_SESSION['role_id'] = $currentUser['role_id'];

$GLOBALS['currentUser'] = $currentUser;

function requireAdmin() {
    global $currentUser;
    if ($currentUser['role_name'] !== 'admin') {
        header('HTTP/1.0 403 Forbidden');
        die('<div style="text-align:center;margin-top:50px;"><h1>403 - Truy cập bị từ chối</h1><p>Bạn không có quyền truy cập trang này.</p><a href="index.php">Dashboard</a> | <a href="logout.php">Đăng xuất</a></div>');
    }
}

function requireStaffOrAdmin() {
    global $currentUser;
    if (!in_array($currentUser['role_name'], ['admin', 'staff'])) {
        header('HTTP/1.0 403 Forbidden');
        die('<div style="text-align:center;margin-top:50px;"><h1>403 - Truy cập bị từ chối</h1><p>Vai trò hiện tại: <strong>' . htmlspecialchars($currentUser['role_name']) . '</strong><br>Yêu cầu: admin hoặc staff</p><a href="index.php">Dashboard</a> | <a href="logout.php">Đăng xuất</a></div>');
    }
}
?>