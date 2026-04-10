<?php
// Kiểm tra nếu không phải admin thì chặn
function requireAdmin() {
    if ($_SESSION['role_name'] !== 'admin') {
        header('HTTP/1.0 403 Forbidden');
        die('Bạn không có quyền truy cập trang này.');
    }
}

// Kiểm tra nếu là staff hoặc admin
function requireStaffOrAdmin() {
    if (!in_array($_SESSION['role_name'], ['admin', 'staff'])) {
        header('HTTP/1.0 403 Forbidden');
        die('Bạn không có quyền truy cập trang này.');
    }
}
?>