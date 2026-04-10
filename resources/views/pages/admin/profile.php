<?php
require_once __DIR__ . '/includes/auth.php';
requireStaffOrAdmin();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

global $currentUser;
$user = $currentUser;
$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action == 'update_profile') {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        if (empty($name) || empty($email)) $error = 'Họ tên và email không được trống.';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error = 'Email không hợp lệ.';
        else {
            $check = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $check->execute([$email, $user['id']]);
            if ($check->fetch()) $error = 'Email đã được sử dụng.';
            else {
                $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, phone=?, address=? WHERE id=?");
                if ($stmt->execute([$name, $email, $phone, $address, $user['id']])) {
                    $success = 'Cập nhật thành công!';
                    $_SESSION['user_name'] = $name;
                    $user['name'] = $name; $user['email'] = $email; $user['phone'] = $phone; $user['address'] = $address;
                    $GLOBALS['currentUser'] = $user;
                } else $error = 'Cập nhật thất bại.';
            }
        }
    } elseif ($action == 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if (empty($current) || empty($new) || empty($confirm)) $error = 'Vui lòng điền đầy đủ.';
        elseif (strlen($new) < 6) $error = 'Mật khẩu mới tối thiểu 6 ký tự.';
        elseif ($new !== $confirm) $error = 'Xác nhận mật khẩu không khớp.';
        else {
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id=?");
            $stmt->execute([$user['id']]);
            if (!password_verify($current, $stmt->fetchColumn())) $error = 'Mật khẩu hiện tại sai.';
            else {
                $stmt = $pdo->prepare("UPDATE users SET password=? WHERE id=?");
                if ($stmt->execute([password_hash($new, PASSWORD_DEFAULT), $user['id']])) $success = 'Đổi mật khẩu thành công!';
                else $error = 'Đổi mật khẩu thất bại.';
            }
        }
    } elseif ($action == 'update_avatar') {
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
            $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
                if (!is_dir('uploads/avatars')) mkdir('uploads/avatars', 0777, true);
                $filename = 'avatar_' . $user['id'] . '_' . uniqid() . '.' . $ext;
                $dest = 'uploads/avatars/' . $filename;
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $dest)) {
                    $old = $user['avatar'] ?? '';
                    if (!empty($old) && file_exists($old) && strpos($old, 'uploads/avatars/') === 0) unlink($old);
                    $stmt = $pdo->prepare("UPDATE users SET avatar=? WHERE id=?");
                    if ($stmt->execute([$dest, $user['id']])) {
                        $user['avatar'] = $dest;
                        $GLOBALS['currentUser'] = $user;
                        $success = 'Cập nhật ảnh đại diện thành công!';
                    } else $error = 'Lưu ảnh thất bại.';
                } else $error = 'Upload ảnh thất bại.';
            } else $error = 'Định dạng ảnh không hợp lệ.';
        } else $error = 'Vui lòng chọn ảnh.';
    }
}
// Refresh user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$user['id']]);
$user = $stmt->fetch();
$GLOBALS['currentUser'] = $user;
?>
<div id="content-wrapper"><div id="content"><?php require_once __DIR__ . '/includes/topbar.php'; ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4"><h1 class="h3 mb-0 text-gray-800">Hồ sơ của tôi</h1></div>
    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
    <div class="row">
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Ảnh đại diện</h6></div>
            <div class="card-body text-center">
                <?php $avatar = !empty($user['avatar']) ? $user['avatar'] : 'img/undraw_profile.svg'; ?>
                <img src="<?= htmlspecialchars($avatar) ?>" class="rounded-circle mb-3" width="150" height="150" style="object-fit:cover;">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="update_avatar">
                    <div class="custom-file mb-2"><input type="file" class="custom-file-input" name="avatar" accept="image/*" required><label class="custom-file-label">Chọn ảnh mới</label></div>
                    <button type="submit" class="btn btn-primary btn-sm">Cập nhật</button>
                </form>
            </div></div>
        </div>
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Thông tin cá nhân</h6></div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="update_profile">
                    <div class="form-group"><label>Họ tên *</label><input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required></div>
                    <div class="form-group"><label>Email *</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required></div>
                    <div class="form-group"><label>Số điện thoại</label><input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>"></div>
                    <div class="form-group"><label>Địa chỉ</label><textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($user['address'] ?? '') ?></textarea></div>
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                </form>
            </div></div>
            <div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Đổi mật khẩu</h6></div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="change_password">
                    <div class="form-group"><label>Mật khẩu hiện tại *</label><input type="password" name="current_password" class="form-control" required></div>
                    <div class="form-group"><label>Mật khẩu mới *</label><input type="password" name="new_password" class="form-control" required><small class="text-muted">Ít nhất 6 ký tự</small></div>
                    <div class="form-group"><label>Xác nhận mật khẩu mới *</label><input type="password" name="confirm_password" class="form-control" required></div>
                    <button type="submit" class="btn btn-warning">Đổi mật khẩu</button>
                </form>
            </div></div>
        </div>
    </div>
</div></div></div>
<?php require_once __DIR__ . '/includes/footer.php'; require_once __DIR__ . '/includes/scripts.php'; ?>