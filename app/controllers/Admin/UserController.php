<?php
// app/controllers/Admin/UserController.php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../../config/database.php';

// Lấy danh sách roles
function getAllRoles() {
    $pdo = getDB();
    return $pdo->query("SELECT id, name FROM roles ORDER BY id")->fetchAll();
}

// Lấy role của user hiện tại
function getCurrentUserRole($user_id) {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT role_id FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn();
}

// Lấy danh sách users có phân trang và lọc
function getUsers($page = 1, $limit = 10, $filters = []) {
    $pdo = getDB();
    $offset = ($page - 1) * $limit;
    $search = $filters['search'] ?? '';
    $filter_role = $filters['role_id'] ?? 0;
    $filter_status = $filters['status'] ?? -1;

    $where = " WHERE 1=1 ";
    $params = [];
    if (!empty($search)) {
        $where .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?) ";
        $params = ["%$search%", "%$search%", "%$search%"];
    }
    if ($filter_role > 0) {
        $where .= " AND u.role_id = ? ";
        $params[] = $filter_role;
    }
    if ($filter_status != -1) {
        $where .= " AND u.status = ? ";
        $params[] = $filter_status;
    }

    $countSql = "SELECT COUNT(*) FROM users u $where";
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $totalRecords = $stmt->fetchColumn();
    $totalPages = ceil($totalRecords / $limit);

    $sql = "SELECT u.*, r.name as role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id $where ORDER BY u.id ASC LIMIT $limit OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll();


    return [
        'data' => $users,
        'total' => $totalRecords,
        'totalPages' => $totalPages,
        'currentPage' => $page
    ];
}

// Thêm user
function handleAddUser() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $pdo = getDB();

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $role_id = (int)($_POST['role_id'] ?? 0);
    $status = (int)($_POST['status'] ?? 1);

    if (empty($name) || empty($email) || empty($password)) {
        $_SESSION['error'] = 'Họ tên, email và mật khẩu không được để trống.';
        header('Location: admin.php?url=users');
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Email không hợp lệ.';
        header('Location: admin.php?url=users');
        exit;
    }
    if (strlen($password) < 6) {
        $_SESSION['error'] = 'Mật khẩu phải có ít nhất 6 ký tự.';
        header('Location: admin.php?url=users');
        exit;
    }

    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetch()) {
        $_SESSION['error'] = 'Email đã tồn tại.';
        header('Location: admin.php?url=users');
        exit;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (role_id, name, email, password, phone, status) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$role_id, $name, $email, $hash, $phone, $status])) {
        $_SESSION['success'] = 'Thêm người dùng thành công.';
    } else {
        $_SESSION['error'] = 'Thêm thất bại.';
    }
    header('Location: admin.php?url=users');
    exit;
}

// Sửa user
function handleEditUser() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $pdo = getDB();

    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $role_id = (int)($_POST['role_id'] ?? 0);
    $status = (int)($_POST['status'] ?? 1);
    $password = $_POST['password'] ?? '';

    if ($id <= 0 || empty($name) || empty($email)) {
        $_SESSION['error'] = 'Dữ liệu không hợp lệ.';
        header('Location: admin.php?url=users');
        exit;
    }

    $current_user_id = $_SESSION['user']['id'] ?? 0;
    $is_super_admin = ($current_user_id == 1);
    $current_role = getCurrentUserRole($current_user_id);

    $stmt = $pdo->prepare("SELECT role_id FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $target_role = $stmt->fetchColumn();

    if ($target_role == 1 && !$is_super_admin && $id != $current_user_id) {
        $_SESSION['error'] = 'Bạn không có quyền chỉnh sửa tài khoản admin khác.';
        header('Location: admin.php?url=users');
        exit;
    }
    if ($id == $current_user_id && !$is_super_admin && $role_id != 1 && $current_role == 1) {
        $_SESSION['error'] = 'Bạn không thể tự hạ cấp tài khoản admin của chính mình.';
        header('Location: admin.php?url=users');
        exit;
    }

    $check = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $check->execute([$email, $id]);
    if ($check->fetch()) {
        $_SESSION['error'] = 'Email đã tồn tại.';
        header('Location: admin.php?url=users');
        exit;
    }

    if (!empty($password)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET role_id=?, name=?, email=?, password=?, phone=?, status=? WHERE id=?");
        $stmt->execute([$role_id, $name, $email, $hash, $phone, $status, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET role_id=?, name=?, email=?, phone=?, status=? WHERE id=?");
        $stmt->execute([$role_id, $name, $email, $phone, $status, $id]);
    }
    $_SESSION['success'] = 'Cập nhật thành công.';
    header('Location: admin.php?url=users');
    exit;
}

// Xóa mềm (vô hiệu hóa)
function handleDeleteUser() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $pdo = getDB();
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $current_user_id = $_SESSION['user']['id'] ?? 0;
    $is_super_admin = ($current_user_id == 1);

    if ($id == $current_user_id) {
        $_SESSION['error'] = 'Bạn không thể vô hiệu hóa chính mình.';
        header('Location: admin.php?url=users');
        exit;
    }

    $stmt = $pdo->prepare("SELECT role_id FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $target_role = $stmt->fetchColumn();

    if ($target_role == 1 && $id == 1 && !$is_super_admin) {
        $_SESSION['error'] = 'Bạn không thể vô hiệu hóa super admin.';
        header('Location: admin.php?url=users');
        exit;
    }

    $pdo->prepare("UPDATE users SET status = 0 WHERE id = ?")->execute([$id]);
    $_SESSION['success'] = 'Đã vô hiệu hóa người dùng.';
    header('Location: admin.php?url=users');
    exit;
}

// Khôi phục user
function handleRestoreUser() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $pdo = getDB();
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $pdo->prepare("UPDATE users SET status = 1 WHERE id = ?")->execute([$id]);
    $_SESSION['success'] = 'Đã khôi phục người dùng.';
    header('Location: admin.php?url=users');
    exit;
}

// Xóa vĩnh viễn
function handleHardDeleteUser() {

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $pdo = getDB();

    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    $current_user_id = $_SESSION['user']['id'] ?? 0;
    $is_super_admin = ($current_user_id == 1);

    // ================= VALIDATE =================

    if ($id <= 0) {
        $_SESSION['error'] = 'ID không hợp lệ.';
        header('Location: admin.php?url=users');
        exit;
    }

    // Không cho tự xóa
    if ($id == $current_user_id) {
        $_SESSION['error'] = 'Bạn không thể xóa chính mình.';
        header('Location: admin.php?url=users');
        exit;
    }

    // Lấy thông tin user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION['error'] = 'Người dùng không tồn tại.';
        header('Location: admin.php?url=users');
        exit;
    }

    // Chặn xóa super admin
    if ($user['role_id'] == 1 && $id == 1 && !$is_super_admin) {
        $_SESSION['error'] = 'Bạn không thể xóa super admin.';
        header('Location: admin.php?url=users');
        exit;
    }

    // ================= CHECK ORDERS =================

    $checkOrder = $pdo->prepare("
        SELECT COUNT(*)
        FROM orders
        WHERE user_id = ?
    ");

    $checkOrder->execute([$id]);

    if ($checkOrder->fetchColumn() > 0) {

        $_SESSION['error'] = 'Không thể xóa vì người dùng đã có đơn hàng.';

        header('Location: admin.php?url=users');
        exit;
    }

    try {

        $pdo->beginTransaction();

        // =================================================
        // XÓA CART ITEMS
        // =================================================

        try {

            $cartStmt = $pdo->prepare("
                SELECT id
                FROM carts
                WHERE user_id = ?
            ");

            $cartStmt->execute([$id]);

            $cartIds = $cartStmt->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($cartIds)) {

                $placeholders = implode(',', array_fill(0, count($cartIds), '?'));

                $deleteCartItems = $pdo->prepare("
                    DELETE FROM cart_items
                    WHERE cart_id IN ($placeholders)
                ");

                $deleteCartItems->execute($cartIds);
            }

        } catch (Exception $e) {}

        // =================================================
        // XÓA CARTS
        // =================================================

        try {

            $pdo->prepare("
                DELETE FROM carts
                WHERE user_id = ?
            ")->execute([$id]);

        } catch (Exception $e) {}

        // =================================================
        // XÓA FAVORITES
        // =================================================

        try {

            $pdo->prepare("
                DELETE FROM favorites
                WHERE user_id = ?
            ")->execute([$id]);

        } catch (Exception $e) {}

        // =================================================
        // XÓA REVIEWS
        // =================================================

        try {

            $pdo->prepare("
                DELETE FROM reviews
                WHERE user_id = ?
            ")->execute([$id]);

        } catch (Exception $e) {}

        // =================================================
        // XÓA NOTIFICATIONS
        // =================================================

        try {

            $pdo->prepare("
                DELETE FROM notifications
                WHERE user_id = ?
            ")->execute([$id]);

        } catch (Exception $e) {}

        // =================================================
        // XÓA CUSTOMERS
        // =================================================

        try {

            $pdo->prepare("
                DELETE FROM customers
                WHERE user_id = ?
            ")->execute([$id]);

        } catch (Exception $e) {}

        // =================================================
        // XÓA ADDRESSES
        // =================================================

        try {

            $pdo->prepare("
                DELETE FROM addresses
                WHERE user_id = ?
            ")->execute([$id]);

        } catch (Exception $e) {}

        // =================================================
        // XÓA USER TOKENS
        // =================================================

        try {

            $pdo->prepare("
                DELETE FROM user_tokens
                WHERE user_id = ?
            ")->execute([$id]);

        } catch (Exception $e) {}

        // =================================================
        // XÓA SESSION LOGIN
        // =================================================

        try {

            $pdo->prepare("
                DELETE FROM login_logs
                WHERE user_id = ?
            ")->execute([$id]);

        } catch (Exception $e) {}

        // =================================================
        // XÓA AVATAR
        // =================================================

        $avatar = $user['avatar'] ?? '';

        if (
            !empty($avatar) &&
            strpos($avatar, 'uploads/avatars/') !== false &&
            file_exists($avatar)
        ) {
            unlink($avatar);
        }

        // =================================================
        // XÓA USER
        // =================================================

        $deleteUser = $pdo->prepare("
            DELETE FROM users
            WHERE id = ?
        ");

        $deleteUser->execute([$id]);

        $pdo->commit();

        $_SESSION['success'] = 'Đã xóa vĩnh viễn người dùng.';

    } catch (Exception $e) {

        $pdo->rollBack();

        $_SESSION['error'] = 'Lỗi khi xóa: ' . $e->getMessage();
    }

    header('Location: admin.php?url=users');
    exit;
}
