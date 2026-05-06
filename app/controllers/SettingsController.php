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

// ================== GET DATA ==================
function getSettingsData() {

    checkLogin();
    $userId = $_SESSION['user']['id'];
    $conn = getDB();

    $stmt = $conn->prepare("
        SELECT
            u.id,
            u.name,
            u.email,
            u.phone,
            u.avatar,

            c.full_name,
            c.gender,
            c.birthday,
            c.address AS customer_address

        FROM users u
        LEFT JOIN customers c ON c.user_id = u.id
        WHERE u.id = ?
        LIMIT 1
    ");

    $stmt->execute([$userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) return null;

    $user = [
        'id'        => $row['id'],
        'name'      => $row['name'] ?? '',
        'email'     => $row['email'] ?? '',
        'phone'     => $row['phone'] ?? '',
        'avatar'    => $row['avatar'] ?? '',

        'full_name' => $row['full_name'] ?? '',
        'gender'    => $row['gender'] ?? '',
        'birthday'  => $row['birthday'] ?? '',
        'address'   => $row['customer_address'] ?? '',
    ];

    // ================= ADDRESS (FIX KHÔNG DÙNG tap) =================
    $stmt = $conn->prepare("
        SELECT * FROM shipping_addresses
        WHERE user_id = ?
        ORDER BY is_default DESC, id DESC
    ");
    $stmt->execute([$userId]);
    $addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ================= NOTIFICATION =================
    $stmt = $conn->prepare("
        SELECT * FROM notifications
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT 20
    ");
    $stmt->execute([$userId]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'user' => $user,
        'addresses' => $addresses,
        'notifications' => $notifications,
        'success' => $_SESSION['settings_success'] ?? null,
        'error' => $_SESSION['settings_error'] ?? null,
    ];
}

// ================== UPDATE PROFILE ==================
function updateProfile() {
    checkLogin();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: index.php?url=settings');
        exit;
    }

    $userId = $_SESSION['user']['id'];
    $conn   = getDB();

    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');

    $fullName = trim($_POST['full_name'] ?? '');
    $gender   = $_POST['gender'] ?? '';
    $birthday = $_POST['birthday'] ?? '';
    $address  = trim($_POST['address'] ?? '');

    if (empty($name) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['settings_error'] = 'Dữ liệu không hợp lệ';
        header('Location: index.php?url=settings');
        exit;
    }

    // ================= AVATAR FIX CHUẨN =================
    $avatarName = $_SESSION['user']['avatar'] ?? '';

    if (!empty($_FILES['avatar']['name']) && $_FILES['avatar']['error'] === 0) {

        // 👉 FIX PATH CHUẨN XAMPP (QUAN TRỌNG NHẤT)
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/DoAn/DoAnTotNghiep/public/assets/img/avatars/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));

        $allow = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($ext, $allow)) {
            $_SESSION['settings_error'] = 'File không hợp lệ';
            header('Location: index.php?url=settings');
            exit;
        }

        // 👉 tạo tên file mới
        $avatarName = 'avatar_' . $userId . '_' . time() . '.' . $ext;

        $targetPath = $uploadDir . $avatarName;

        // 👉 debug nếu cần
        // var_dump($targetPath); exit;

        if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $targetPath)) {
            $_SESSION['settings_error'] = 'Upload ảnh thất bại';
            header('Location: index.php?url=settings');
            exit;
        }
    }

    // ================= UPDATE USERS =================
    $conn->prepare("
        UPDATE users
        SET name=?, email=?, phone=?, avatar=?
        WHERE id=?
    ")->execute([$name, $email, $phone, $avatarName, $userId]);

    // ================= UPSERT CUSTOMERS =================
    $stmt = $conn->prepare("SELECT id FROM customers WHERE user_id=?");
    $stmt->execute([$userId]);

    if ($stmt->fetch()) {
        $conn->prepare("
            UPDATE customers
            SET full_name=?, phone=?, gender=?, birthday=?, address=?, updated_at=NOW()
            WHERE user_id=?
        ")->execute([$fullName, $phone, $gender, $birthday, $address, $userId]);
    } else {
        $conn->prepare("
            INSERT INTO customers (user_id, full_name, phone, gender, birthday, address, created_at)
            VALUES (?,?,?,?,?,?,NOW())
        ")->execute([$userId, $fullName, $phone, $gender, $birthday, $address]);
    }

    // ================= SYNC SESSION =================
    $_SESSION['user'] = array_merge($_SESSION['user'], [
        'name'      => $name,
        'email'     => $email,
        'phone'     => $phone,
        'avatar'    => $avatarName,
        'gender'    => $gender,
        'birthday'  => $birthday,
        'address'   => $address,
        'full_name' => $fullName
    ]);

    $_SESSION['settings_success'] = 'Cập nhật thành công';
    header('Location: index.php?url=settings');
    exit;
}
// ================= PASSWORD =================
function changePassword() {
    checkLogin();

    $userId = $_SESSION['user']['id'];
    $conn = getDB();

    $old = $_POST['old_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $cf  = $_POST['confirm_password'] ?? '';

    $stmt = $conn->prepare("SELECT password FROM users WHERE id=?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!password_verify($old,$user['password'])) {
        $_SESSION['settings_error'] = 'Sai mật khẩu cũ';
        header('Location: index.php?url=settings');
        exit;
    }

    if (strlen($new) < 6) {
        $_SESSION['settings_error'] = 'Mật khẩu phải >= 6 ký tự';
        header('Location: index.php?url=settings');
        exit;
    }

    if ($new !== $cf) {
        $_SESSION['settings_error'] = 'Xác nhận không khớp';
        header('Location: index.php?url=settings');
        exit;
    }

    $hash = password_hash($new,PASSWORD_DEFAULT);
    $conn->prepare("UPDATE users SET password=? WHERE id=?")
         ->execute([$hash,$userId]);

    $_SESSION['settings_success'] = 'Đổi mật khẩu thành công';
    header('Location: index.php?url=settings');
    exit;
}

// ================= ADDRESS =================
function addAddress() {
    checkLogin();
    $conn = getDB();
    $userId = $_SESSION['user']['id'];

    $full = $_POST['full_name'];
    $phone= $_POST['phone'];
    $addr = $_POST['address'];
    $city = $_POST['city'];
    $default = isset($_POST['is_default']) ? 1 : 0;

    if ($default) {
        $conn->prepare("UPDATE shipping_addresses SET is_default=0 WHERE user_id=?")
             ->execute([$userId]);
    }

    $conn->prepare("
        INSERT INTO shipping_addresses(user_id,full_name,phone,address,city,is_default,created_at)
        VALUES (?,?,?,?,?,?,NOW())
    ")->execute([$userId,$full,$phone,$addr,$city,$default]);

    $_SESSION['settings_success']='Thêm địa chỉ thành công';
    header('Location: index.php?url=settings');
    exit;
}

// ================= DELETE =================
function deleteAddress() {
    checkLogin();
    $conn = getDB();
    $userId = $_SESSION['user']['id'];

    $id = $_POST['address_id'];

    $conn->prepare("DELETE FROM shipping_addresses WHERE id=? AND user_id=?")
         ->execute([$id,$userId]);

    $_SESSION['settings_success']='Đã xóa';
    header('Location: index.php?url=settings');
    exit;
}

// ================= NOTIFICATION =================
function markNotificationRead() {
    checkLogin();
    $conn = getDB();

    $id = $_POST['notification_id'];
    $userId = $_SESSION['user']['id'];

    $conn->prepare("
        UPDATE notifications SET is_read=1
        WHERE id=? AND user_id=?
    ")->execute([$id,$userId]);

    header('Content-Type: application/json');
    echo json_encode(['success'=>true]);
    exit;
}

function updateAddress() {
    checkLogin();
    $conn = getDB();
    $userId = $_SESSION['user']['id'];

    $id   = $_POST['address_id'];
    $full = $_POST['full_name'];
    $phone= $_POST['phone'];
    $addr = $_POST['address'];
    $city = $_POST['city'];
    $default = isset($_POST['is_default']) ? 1 : 0;

    if ($default) {
        $conn->prepare("UPDATE shipping_addresses SET is_default=0 WHERE user_id=?")
             ->execute([$userId]);
    }

    $conn->prepare("
        UPDATE shipping_addresses
        SET full_name=?, phone=?, address=?, city=?, is_default=?, updated_at=NOW()
        WHERE id=? AND user_id=?
    ")->execute([$full,$phone,$addr,$city,$default,$id,$userId]);

    $_SESSION['settings_success'] = 'Cập nhật địa chỉ thành công';
    header('Location: index.php?url=settings');
    exit;
}

function markAllRead() {
    checkLogin();
    $conn = getDB();
    $userId = $_SESSION['user']['id'];

    $conn->prepare("
        UPDATE notifications
        SET is_read=1
        WHERE user_id=?
    ")->execute([$userId]);

    header('Content-Type: application/json');
    echo json_encode(['success'=>true]);
    exit;
}
