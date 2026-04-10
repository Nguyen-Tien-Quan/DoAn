<?php
require_once 'includes/db.php';
requireStaffOrAdmin();
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Tự động thêm cột avatar nếu chưa có
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN avatar VARCHAR(255) NULL");
} catch (PDOException $e) {}

if (!is_dir('uploads/avatars')) mkdir('uploads/avatars', 0777, true);

$error = $success = '';
$roles = $pdo->query("SELECT id, name FROM roles ORDER BY id")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $role_id = (int)($_POST['role_id'] ?? 0);
    $status = (int)($_POST['status'] ?? 1);
    $avatar_path = '';

    // Xử lý upload ảnh
    if (isset($_FILES['avatar_file']) && $_FILES['avatar_file']['error'] == 0) {
        $allowed = ['jpg','jpeg','png','gif','webp'];
        $ext = strtolower(pathinfo($_FILES['avatar_file']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $new_name = uniqid() . '.' . $ext;
            $dest = 'uploads/avatars/' . $new_name;
            if (move_uploaded_file($_FILES['avatar_file']['tmp_name'], $dest)) {
                $avatar_path = $dest;
            } else $error = "Upload ảnh thất bại.";
        } else $error = "Định dạng ảnh không hợp lệ.";
    } elseif (!empty($_POST['avatar_url'])) {
        $avatar_path = trim($_POST['avatar_url']);
    }

    if (empty($error)) {
        if (empty($name)) $error = "Họ tên không được trống";
        elseif (empty($email)) $error = "Email không được trống";
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error = "Email không hợp lệ";
        elseif (empty($password)) $error = "Mật khẩu không được trống";
        elseif (strlen($password) < 6) $error = "Mật khẩu ít nhất 6 ký tự";
        elseif ($role_id <= 0) $error = "Chọn vai trò";
        else {
            $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $check->execute([$email]);
            if ($check->fetch()) $error = "Email đã tồn tại";
            else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO users (role_id, name, email, password, phone, avatar, status) VALUES (?,?,?,?,?,?,?)";
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute([$role_id, $name, $email, $hashed, $phone, $avatar_path, $status])) {
                    $success = "Thêm người dùng thành công!";
                    $_POST = [];
                } else $error = "Lỗi khi thêm dữ liệu.";
            }
        }
    }
}
?>

<div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
        <?php require_once 'includes/topbar.php'; ?>
        <div class="container-fluid">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Thêm người dùng</h1>
                <a href="users.php" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Quay lại</a>
            </div>
            <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
            <div class="card shadow mb-4">
                <div class="card-header"><h6 class="m-0 font-weight-bold text-primary">Thông tin tài khoản</h6></div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group"><label>Họ tên <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required></div>
                        <div class="form-group"><label>Email <span class="text-danger">*</span></label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required></div>
                        <div class="form-group"><label>Số điện thoại</label><input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"></div>
                        <div class="form-group"><label>Mật khẩu <span class="text-danger">*</span></label><input type="password" name="password" class="form-control" required><small class="text-muted">Ít nhất 6 ký tự</small></div>
                        <div class="form-group"><label>Vai trò <span class="text-danger">*</span></label><select name="role_id" class="form-control" required><option value="">-- Chọn --</option><?php foreach ($roles as $role): ?><option value="<?= $role['id'] ?>" <?= (isset($_POST['role_id']) && $_POST['role_id'] == $role['id']) ? 'selected' : '' ?>><?= ucfirst($role['name']) ?></option><?php endforeach; ?></select></div>
                        <div class="form-group"><label>Trạng thái</label><select name="status" class="form-control"><option value="1" selected>Hoạt động</option><option value="0">Khóa</option></select></div>
                        <div class="form-group"><label>Ảnh đại diện (tải lên)</label><input type="file" name="avatar_file" class="form-control-file" accept="image/*"><small class="text-muted">Hoặc nhập URL</small></div>
                        <div class="form-group"><label>Hoặc URL ảnh</label><input type="text" name="avatar_url" class="form-control" placeholder="https://..." value="<?= htmlspecialchars($_POST['avatar_url'] ?? '') ?>"></div>
                        <button type="submit" class="btn btn-primary">Lưu</button>
                        <a href="users.php" class="btn btn-secondary">Hủy</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; require_once 'includes/scripts.php'; ?>