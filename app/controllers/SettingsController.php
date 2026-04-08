<?php
require_once __DIR__ . '/../../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkLogin() {
    if (!isset($_SESSION['user'])) {
        header('Location: index.php?url=login');
        exit;
    }
}

// Lấy dữ liệu cho trang settings
function getSettingsData() {
    checkLogin();
    $userId = $_SESSION['user']['id'];
    $conn = getDB();

    // Thông tin user
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Lấy danh sách địa chỉ từ bảng shipping_addresses
    $stmt = $conn->prepare("SELECT * FROM shipping_addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC");
    $stmt->execute([$userId]);
    $addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Thông báo
    $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
    $stmt->execute([$userId]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $success = $_SESSION['settings_success'] ?? null;
    $error   = $_SESSION['settings_error'] ?? null;
    unset($_SESSION['settings_success'], $_SESSION['settings_error']);

    return [
        'user' => $user,
        'addresses' => $addresses,
        'notifications' => $notifications,
        'success' => $success,
        'error' => $error
    ];
}

// Cập nhật thông tin cá nhân (giữ nguyên)
function updateProfile() {
    checkLogin();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: index.php?url=settings');
        exit;
    }

    $userId = $_SESSION['user']['id'];
    $conn   = getDB();

    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    $errors = [];
    if (empty($name)) $errors[] = 'Họ tên không được để trống';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email không hợp lệ';
    if ($phone && !preg_match('/^[0-9]{10,11}$/', $phone)) $errors[] = 'Số điện thoại phải 10-11 số';

    // Xử lý avatar
    $avatar = null;
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $newName = 'avatar_' . $userId . '_' . time() . '.' . $ext;
            $uploadPath = __DIR__ . '/../../public/assets/img/avatars/';
            if (!is_dir($uploadPath)) mkdir($uploadPath, 0777, true);
            move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadPath . $newName);
            $avatar = $newName;
        } else {
            $errors[] = 'Ảnh đại diện không đúng định dạng (jpg, png, gif)';
        }
    }

    if (empty($errors)) {
        $sql = "UPDATE users SET name = ?, email = ?, phone = ?";
        $params = [$name, $email, $phone];
        if ($avatar) {
            $sql .= ", avatar = ?";
            $params[] = $avatar;
        }
        $sql .= " WHERE id = ?";
        $params[] = $userId;
        $stmt = $conn->prepare($sql);
        if ($stmt->execute($params)) {
            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['email'] = $email;
            $_SESSION['user']['phone'] = $phone;
            if ($avatar) $_SESSION['user']['avatar'] = $avatar;
            $_SESSION['settings_success'] = 'Cập nhật thông tin thành công';
        } else {
            $_SESSION['settings_error'] = 'Có lỗi xảy ra, vui lòng thử lại';
        }
    } else {
        $_SESSION['settings_error'] = implode('<br>', $errors);
    }
    header('Location: index.php?url=settings');
    exit;
}

// Đổi mật khẩu (giữ nguyên)
function changePassword() {
    checkLogin();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: index.php?url=settings');
        exit;
    }

    $userId = $_SESSION['user']['id'];
    $conn = getDB();

    $oldPass = $_POST['old_password'] ?? '';
    $newPass = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!password_verify($oldPass, $user['password'])) {
        $_SESSION['settings_error'] = 'Mật khẩu cũ không đúng';
        header('Location: index.php?url=settings');
        exit;
    }
    if (strlen($newPass) < 6) {
        $_SESSION['settings_error'] = 'Mật khẩu mới phải có ít nhất 6 ký tự';
        header('Location: index.php?url=settings');
        exit;
    }
    if ($newPass !== $confirm) {
        $_SESSION['settings_error'] = 'Xác nhận mật khẩu không khớp';
        header('Location: index.php?url=settings');
        exit;
    }

    $hashed = password_hash($newPass, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    if ($stmt->execute([$hashed, $userId])) {
        $_SESSION['settings_success'] = 'Đổi mật khẩu thành công';
    } else {
        $_SESSION['settings_error'] = 'Đổi mật khẩu thất bại';
    }
    header('Location: index.php?url=settings');
    exit;
}

// ========== ĐỊA CHỈ GIAO HÀNG (dùng bảng shipping_addresses) ==========
function addAddress() {
    checkLogin();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: index.php?url=settings');
        exit;
    }

    $userId = $_SESSION['user']['id'];
    $conn = getDB();

    $fullName = trim($_POST['full_name'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $address  = trim($_POST['address'] ?? '');
    $city     = trim($_POST['city'] ?? '');
    $isDefault = isset($_POST['is_default']) ? 1 : 0;

    if (empty($fullName) || empty($phone) || empty($address) || empty($city)) {
        $_SESSION['settings_error'] = 'Vui lòng điền đầy đủ thông tin địa chỉ';
        header('Location: index.php?url=settings');
        exit;
    }

    // Nếu đặt làm mặc định, bỏ mặc định các địa chỉ khác
    if ($isDefault) {
        $stmt = $conn->prepare("UPDATE shipping_addresses SET is_default = 0 WHERE user_id = ?");
        $stmt->execute([$userId]);
    }

    $stmt = $conn->prepare("INSERT INTO shipping_addresses (user_id, full_name, phone, address, city, is_default, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    if ($stmt->execute([$userId, $fullName, $phone, $address, $city, $isDefault])) {
        $_SESSION['settings_success'] = 'Thêm địa chỉ thành công';
    } else {
        $_SESSION['settings_error'] = 'Thêm địa chỉ thất bại';
    }
    header('Location: index.php?url=settings');
    exit;
}

function updateAddress() {
    checkLogin();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: index.php?url=settings');
        exit;
    }

    $userId = $_SESSION['user']['id'];
    $conn = getDB();

    $id       = $_POST['address_id'] ?? 0;
    $fullName = trim($_POST['full_name'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $address  = trim($_POST['address'] ?? '');
    $city     = trim($_POST['city'] ?? '');
    $isDefault = isset($_POST['is_default']) ? 1 : 0;

    // Kiểm tra địa chỉ thuộc user
    $stmt = $conn->prepare("SELECT id FROM shipping_addresses WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $userId]);
    if (!$stmt->fetch()) {
        $_SESSION['settings_error'] = 'Địa chỉ không hợp lệ';
        header('Location: index.php?url=settings');
        exit;
    }

    if ($isDefault) {
        $stmt = $conn->prepare("UPDATE shipping_addresses SET is_default = 0 WHERE user_id = ?");
        $stmt->execute([$userId]);
    }

    $stmt = $conn->prepare("UPDATE shipping_addresses SET full_name = ?, phone = ?, address = ?, city = ?, is_default = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$fullName, $phone, $address, $city, $isDefault, $id, $userId])) {
        $_SESSION['settings_success'] = 'Cập nhật địa chỉ thành công';
    } else {
        $_SESSION['settings_error'] = 'Cập nhật thất bại';
    }
    header('Location: index.php?url=settings');
    exit;
}

function deleteAddress() {
    checkLogin();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: index.php?url=settings');
        exit;
    }

    $userId = $_SESSION['user']['id'];
    $conn = getDB();
    $id = $_POST['address_id'] ?? 0;

    $stmt = $conn->prepare("DELETE FROM shipping_addresses WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$id, $userId])) {
        $_SESSION['settings_success'] = 'Xóa địa chỉ thành công';
    } else {
        $_SESSION['settings_error'] = 'Xóa thất bại';
    }
    header('Location: index.php?url=settings');
    exit;
}

// ========== THÔNG BÁO ==========
function markNotificationRead() {
    checkLogin();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(400);
        exit;
    }

    $userId = $_SESSION['user']['id'];
    $conn = getDB();
    $notiId = $_POST['notification_id'] ?? 0;

    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $success = $stmt->execute([$notiId, $userId]);

    header('Content-Type: application/json');
    echo json_encode(['success' => $success]);
    exit;
}

function markAllRead() {
    checkLogin();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: index.php?url=settings');
        exit;
    }

    $userId = $_SESSION['user']['id'];
    $conn = getDB();

    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$userId]);

    $_SESSION['settings_success'] = 'Đã đánh dấu tất cả thông báo là đã đọc';
    header('Location: index.php?url=settings');
    exit;
}
