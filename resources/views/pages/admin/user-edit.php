<?php
require_once 'includes/db.php';
requireStaffOrAdmin();
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

try { $pdo->exec("ALTER TABLE users ADD COLUMN avatar VARCHAR(255) NULL"); } catch (PDOException $e) {}
if (!is_dir('uploads/avatars')) mkdir('uploads/avatars', 0777, true);

$error = $success = '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) header('Location: users.php');

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) header('Location: users.php');

$roles = $pdo->query("SELECT id, name FROM roles ORDER BY id")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $role_id = (int)($_POST['role_id'] ?? 0);
    $status = (int)($_POST['status'] ?? 1);
    $avatar_path = $user['avatar'] ?? '';

    // Xử lý ảnh mới
    if (isset($_FILES['avatar_file']) && $_FILES['avatar_file']['error'] == 0) {
        $allowed = ['jpg','jpeg','png','gif','webp'];
        $ext = strtolower(pathinfo($_FILES['avatar_file']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $new_name = uniqid() . '.' . $ext;
            $dest = 'uploads/avatars/' . $new_name;
            if (move_uploaded_file($_FILES['avatar_file']['tmp_name'], $dest)) {
                // Xóa ảnh cũ nếu là file upload
                if (!empty($avatar_path) && file_exists($avatar_path) && strpos($avatar_path, 'uploads/avatars/') === 0) {
                    unlink($avatar_path);
                }
                $avatar_path = $dest;
            } else $error = "Upload ảnh thất bại.";
        } else $error = "Định dạng ảnh không hợp lệ.";
    } elseif (isset($_POST['avatar_url']) && !empty($_POST['avatar_url'])) {
        if (!empty($avatar_path) && file_exists($avatar_path) && strpos($avatar_path, 'uploads/avatars/') === 0) {
            unlink($avatar_path);
        }
        $avatar_path = trim($_POST['avatar_url']);
    }

    if (empty($error)) {
        if (empty($name)) $error = "Họ tên không được trống";
        elseif (empty($email)) $error = "Email không được trống";
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error = "Email không hợp lệ";
        elseif ($role_id <= 0) $error = "Chọn vai trò";
        else {
            $check = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $check->execute([$email, $id]);
            if ($check->fetch()) $error = "Email đã được sử dụng";
            else {
                if (!empty($password)) {
                    if (strlen($password) < 6) $error = "Mật khẩu ít nhất 6 ký tự";
                    else {
                        $hashed = password_hash($password, PASSWORD_DEFAULT);
                        $sql = "UPDATE users SET role_id=?, name=?, email=?, password=?, phone=?, avatar=?, status=? WHERE id=?";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$role_id, $name, $email, $hashed, $phone, $avatar_path, $status, $id]);
                        $success = "Cập nhật thành công!";
                        $user = $pdo->prepare("SELECT * FROM users WHERE id=?")->execute([$id]) ? $user : null; // refresh
                    }
                } else {
                    $sql = "UPDATE users SET role_id=?, name=?, email=?, phone=?, avatar=?, status=? WHERE id=?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$role_id, $name, $email, $phone, $avatar_path, $status, $id]);
                    $success = "Cập nhật thành công!";
                }
                // Refresh user data
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$id]);
                $user = $stmt->fetch();
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
                <h1 class="h3 mb-0 text-gray-800">Sửa người dùng</h1>
                <a href="users.php" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Quay lại</a>
            </div>
            <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
            <div class="card shadow mb-4">
                <div class="card-header"><h6 class="m-0 font-weight-bold text-primary">Chỉnh sửa thông tin</h6></div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group"><label>Họ tên <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required></div>
                        <div class="form-group"><label>Email <span class="text-danger">*</span></label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required></div>
                        <div class="form-group"><label>Số điện thoại</label><input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>"></div>
                        <div class="form-group"><label>Mật khẩu mới</label><input type="password" name="password" class="form-control" placeholder="Để trống nếu không đổi"><small class="text-muted">Ít nhất 6 ký tự</small></div>
                        <div class="form-group"><label>Vai trò <span class="text-danger">*</span></label><select name="role_id" class="form-control" required><?php foreach ($roles as $role): ?><option value="<?= $role['id'] ?>" <?= ($role['id'] == $user['role_id']) ? 'selected' : '' ?>><?= ucfirst($role['name']) ?></option><?php endforeach; ?></select></div>
                        <div class="form-group"><label>Trạng thái</label><select name="status" class="form-control"><option value="1" <?= $user['status'] == 1 ? 'selected' : '' ?>>Hoạt động</option><option value="0" <?= $user['status'] == 0 ? 'selected' : '' ?>>Khóa</option></select></div>
                        <div class="form-group">
                            <label>Ảnh hiện tại</label><br>
                            <?php if (!empty($user['avatar'])): ?>
                                <img src="<?= htmlspecialchars($user['avatar']) ?>" width="80" height="80" class="rounded-circle mb-2"><br>
                            <?php else: ?>
                                <span class="text-muted">Chưa có ảnh</span><br>
                            <?php endif; ?>
                            <label>Đổi ảnh (tải lên)</label>
                            <input type="file" name="avatar_file" class="form-control-file" accept="image/*">
                            <small class="text-muted">Hoặc nhập URL mới</small>
                        </div>
                        <div class="form-group"><label>Hoặc URL ảnh mới</label><input type="text" name="avatar_url" class="form-control" placeholder="https://..."></div>
                        <button type="submit" class="btn btn-primary">Cập nhật</button>
                        <a href="users.php" class="btn btn-secondary">Hủy</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; require_once 'includes/scripts.php'; ?>