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
    ");
    $stmt->execute([$userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // 🔥 CHUẨN HÓA DATA 1 OBJECT DUY NHẤT
    $user = [
        'id' => $row['id'],
        'name' => $row['name'],
        'email' => $row['email'],
        'phone' => $row['phone'],
        'avatar' => $row['avatar'],

        'full_name' => $row['full_name'],
        'gender' => $row['gender'],
        'birthday' => $row['birthday'],
        'address' => $row['customer_address'],
    ];

    // ADDRESS
    $stmt = $conn->prepare("
        SELECT * FROM shipping_addresses
        WHERE user_id = ?
        ORDER BY is_default DESC, id DESC
    ");
    $stmt->execute([$userId]);
    $addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // NOTIFICATION
    $stmt = $conn->prepare("
        SELECT * FROM notifications
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT 20
    ");
    $stmt->execute([$userId]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $success = $_SESSION['settings_success'] ?? null;
    $error   = $_SESSION['settings_error'] ?? null;
    unset($_SESSION['settings_success'], $_SESSION['settings_error']);

    return compact('user','addresses','notifications','success','error');
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
    $gender   = $_POST['gender'] ?? null;
    $birthday = $_POST['birthday'] ?? null;
    $address  = trim($_POST['address'] ?? '');

    $errors = [];

    if (empty($name)) $errors[] = 'Tên không được trống';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email không hợp lệ';

    if ($phone && !preg_match('/^[0-9]{10,11}$/', $phone)) {
        $errors[] = 'SĐT không hợp lệ';
    }

    if (!empty($errors)) {
        $_SESSION['settings_error'] = implode('<br>', $errors);
        header('Location: index.php?url=settings');
        exit;
    }

    // ================= AVATAR =================
    $avatarName = $_SESSION['user']['avatar'] ?? null;

    if (!empty($_FILES['avatar']['name'])) {

        $uploadDir = __DIR__ . '/../../../public/assets/img/avatars/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $avatarName = 'avatar_' . $userId . '_' . time() . '.' . $ext;

        move_uploaded_file(
            $_FILES['avatar']['tmp_name'],
            $uploadDir . $avatarName
        );
    }

    // ================= USERS TABLE =================
    $conn->prepare("
        UPDATE users
        SET name=?, email=?, phone=?, avatar=?
        WHERE id=?
    ")->execute([$name, $email, $phone, $avatarName, $userId]);

    // ================= CUSTOMERS TABLE =================
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

    // ================= FIX SESSION (QUAN TRỌNG) =================
    $_SESSION['user'] = array_merge($_SESSION['user'], [
        'name'     => $name,
        'email'    => $email,
        'phone'    => $phone,
        'avatar'   => $avatarName,
        'gender'   => $gender,
        'birthday' => $birthday,
        'address'  => $address,
        'full_name'=> $fullName
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
