<?php

require_once __DIR__ . '/../../../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* =========================
   GET PROFILE
========================= */
function getProfile($id)
{
    $conn = getDB();

    $stmt = $conn->prepare("
        SELECT *
        FROM users
        WHERE id = ?
    ");

    $stmt->execute([$id]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/* =========================
   UPDATE PROFILE
========================= */
function updateProfile()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    $action = $_POST['action'] ?? '';

    switch ($action) {

        case 'update_profile':
            handleUpdateProfile();
            break;

        case 'change_password':
            handleChangePassword();
            break;

        case 'update_avatar':
            handleUpdateAvatar();
            break;
    }
}

/* =========================
   UPDATE INFO
========================= */
function handleUpdateProfile()
{
    $conn = getDB();

    $user = $_SESSION['user'];

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (empty($name) || empty($email)) {

        $_SESSION['error'] = "Họ tên và email không được để trống";
        return;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $_SESSION['error'] = "Email không hợp lệ";
        return;
    }

    $check = $conn->prepare("
        SELECT id
        FROM users
        WHERE email = ?
        AND id != ?
    ");

    $check->execute([$email, $user['id']]);

    if ($check->fetch()) {

        $_SESSION['error'] = "Email đã tồn tại";
        return;
    }

    $stmt = $conn->prepare("
        UPDATE users
        SET
            name = ?,
            email = ?,
            phone = ?
        WHERE id = ?
    ");

    $success = $stmt->execute([
        $name,
        $email,
        $phone,
        $user['id']
    ]);

    if ($success) {

        $_SESSION['user']['name'] = $name;
        $_SESSION['user']['email'] = $email;
        $_SESSION['user']['phone'] = $phone;

        $_SESSION['success'] = "Cập nhật hồ sơ thành công";
    } else {

        $_SESSION['error'] = "Cập nhật thất bại";
    }

    header("Location: admin.php?url=profile");
    exit;
}

/* =========================
   CHANGE PASSWORD
========================= */
function handleChangePassword()
{
    $conn = getDB();

    $user = $_SESSION['user'];

    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (
        empty($current) ||
        empty($new) ||
        empty($confirm)
    ) {

        $_SESSION['error'] = "Vui lòng nhập đầy đủ thông tin";
        return;
    }

    if (strlen($new) < 6) {

        $_SESSION['error'] = "Mật khẩu tối thiểu 6 ký tự";
        return;
    }

    if ($new !== $confirm) {

        $_SESSION['error'] = "Xác nhận mật khẩu không khớp";
        return;
    }

    $stmt = $conn->prepare("
        SELECT password
        FROM users
        WHERE id = ?
    ");

    $stmt->execute([$user['id']]);

    $hash = $stmt->fetchColumn();

    if (!password_verify($current, $hash)) {

        $_SESSION['error'] = "Mật khẩu hiện tại không đúng";
        return;
    }

    $newHash = password_hash($new, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
        UPDATE users
        SET password = ?
        WHERE id = ?
    ");

    $success = $stmt->execute([
        $newHash,
        $user['id']
    ]);

    if ($success) {

        $_SESSION['success'] = "Đổi mật khẩu thành công";
    } else {

        $_SESSION['error'] = "Đổi mật khẩu thất bại";
    }

    header("Location: admin.php?url=profile");
    exit;
}

/* =========================
   UPDATE AVATAR
========================= */
function handleUpdateAvatar()
{
    $conn = getDB();

    $user = $_SESSION['user'];

    if (
        !isset($_FILES['avatar']) ||
        $_FILES['avatar']['error'] != 0
    ) {

        $_SESSION['error'] = "Vui lòng chọn ảnh";
        return;
    }

    $ext = strtolower(pathinfo(
        $_FILES['avatar']['name'],
        PATHINFO_EXTENSION
    ));

    $allow = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array($ext, $allow)) {

        $_SESSION['error'] = "Định dạng ảnh không hợp lệ";
        return;
    }

    $uploadDir = __DIR__ . '/../../../public/uploads/avatars/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = 'avatar_' .
                $user['id'] .
                '_' .
                time() .
                '.' .
                $ext;

    $fullPath = $uploadDir . $fileName;

    $dbPath = 'uploads/avatars/' . $fileName;

    if (
        move_uploaded_file(
            $_FILES['avatar']['tmp_name'],
            $fullPath
        )
    ) {

        $stmt = $conn->prepare("
            UPDATE users
            SET avatar = ?
            WHERE id = ?
        ");

        $success = $stmt->execute([
            $dbPath,
            $user['id']
        ]);

        if ($success) {

            $_SESSION['user']['avatar'] = $dbPath;

            $_SESSION['success'] =
                "Cập nhật ảnh đại diện thành công";
        } else {

            $_SESSION['error'] = "Lưu ảnh thất bại";
        }

    } else {

        $_SESSION['error'] = "Upload ảnh thất bại";
    }

    header("Location: admin.php?url=profile");
    exit;
}
