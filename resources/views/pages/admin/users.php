<?php
require_once 'includes/auth.php';
requireAdmin(); // Chỉ admin mới được xem
require_once 'includes/db.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Xử lý thêm, sửa, xóa, khôi phục user
$error = $success = '';

// Thêm user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $role_id = (int)$_POST['role_id'];
    $status = (int)$_POST['status'];

    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Họ tên, email và mật khẩu không được để trống.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ.';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự.';
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $error = 'Email đã tồn tại.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (role_id, name, email, password, phone, status) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$role_id, $name, $email, $hash, $phone, $status])) {
                $success = 'Thêm người dùng thành công.';
            } else {
                $error = 'Thêm thất bại.';
            }
        }
    }
}

// Sửa user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit') {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $role_id = (int)$_POST['role_id'];
    $status = (int)$_POST['status'];
    $password = $_POST['password'] ?? '';

    if (empty($name) || empty($email)) {
        $error = 'Họ tên và email không được trống.';
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check->execute([$email, $id]);
        if ($check->fetch()) {
            $error = 'Email đã tồn tại.';
        } else {
            if (!empty($password)) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET role_id=?, name=?, email=?, password=?, phone=?, status=? WHERE id=?");
                $stmt->execute([$role_id, $name, $email, $hash, $phone, $status, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET role_id=?, name=?, email=?, phone=?, status=? WHERE id=?");
                $stmt->execute([$role_id, $name, $email, $phone, $status, $id]);
            }
            $success = 'Cập nhật thành công.';
        }
    }
}

// Xóa mềm (status = 0)
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("UPDATE users SET status = 0 WHERE id = ?")->execute([$id]);
    $success = 'Đã vô hiệu hóa người dùng.';
}
// Khôi phục
if (isset($_GET['restore'])) {
    $id = (int)$_GET['restore'];
    $pdo->prepare("UPDATE users SET status = 1 WHERE id = ?")->execute([$id]);
    $success = 'Đã khôi phục người dùng.';
}

// Phân trang & lọc
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_role = isset($_GET['role_id']) ? (int)$_GET['role_id'] : 0;
$filter_status = isset($_GET['status']) ? (int)$_GET['status'] : -1;

$where = " WHERE 1=1 ";
$params = [];
if ($search != '') {
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

// Đếm tổng
$countSql = "SELECT COUNT(*) FROM users u $where";
$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$totalRecords = $stmt->fetchColumn();
$totalPages = ceil($totalRecords / $limit);

// Lấy danh sách
$sql = "SELECT u.*, r.name as role_name
        FROM users u
        LEFT JOIN roles r ON u.role_id = r.id
        $where
        ORDER BY u.id DESC
        LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Lấy danh sách roles cho dropdown
$roles = $pdo->query("SELECT id, name FROM roles ORDER BY id")->fetchAll();
?>

<div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
        <?php require_once 'includes/topbar.php'; ?>
        <div class="container-fluid">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Quản lý người dùng</h1>
                <button class="btn btn-primary" data-toggle="modal" data-target="#addUserModal"><i class="fas fa-plus"></i> Thêm người dùng</button>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <!-- Bộ lọc -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <form method="GET" class="form-inline">
                        <input type="text" name="search" class="form-control mr-2" placeholder="Tìm kiếm..." value="<?= htmlspecialchars($search) ?>">
                        <select name="role_id" class="form-control mr-2">
                            <option value="0">-- Tất cả vai trò --</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role['id'] ?>" <?= $filter_role == $role['id'] ? 'selected' : '' ?>><?= ucfirst($role['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="status" class="form-control mr-2">
                            <option value="-1">-- Tất cả trạng thái --</option>
                            <option value="1" <?= $filter_status == 1 ? 'selected' : '' ?>>Hoạt động</option>
                            <option value="0" <?= $filter_status == 0 ? 'selected' : '' ?>>Đã khóa</option>
                        </select>
                        <button type="submit" class="btn btn-primary">Lọc</button>
                        <a href="users.php" class="btn btn-secondary ml-2">Reset</a>
                    </form>
                </div>
            </div>

            <!-- Danh sách -->
            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Danh sách người dùng</h6></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr><th>ID</th><th>Avatar</th><th>Họ tên</th><th>Email</th><th>Điện thoại</th><th>Vai trò</th><th>Trạng thái</th><th>Hành động</th>
                            </thead>
                            <tbody>
                                <?php if (count($users) == 0): ?>
                                    <tr><td colspan="8" class="text-center">Không có dữ liệu</td></tr>
                                <?php endif; ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?= $user['id'] ?></td>
                                        <td>
                                            <?php if (!empty($user['avatar'])): ?>
                                                <img src="<?= htmlspecialchars($user['avatar']) ?>" width="40" height="40" class="rounded-circle">
                                            <?php else: ?>
                                                <img src="img/undraw_profile.svg" width="40" height="40" class="rounded-circle">
                                            <?php endif; ?>
                                         </td>
                                        <td><?= htmlspecialchars($user['name']) ?></td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td><?= htmlspecialchars($user['phone'] ?? '') ?></td>
                                        <td>
                                            <?php
                                            $badge = 'secondary';
                                            if ($user['role_name'] == 'admin') $badge = 'danger';
                                            elseif ($user['role_name'] == 'staff') $badge = 'warning';
                                            ?>
                                            <span class="badge badge-<?= $badge ?>"><?= ucfirst($user['role_name']) ?></span>
                                         </td>
                                        <td>
                                            <?php if ($user['status'] == 1): ?>
                                                <span class="badge badge-success">Hoạt động</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Đã khóa</span>
                                            <?php endif; ?>
                                         </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editModal<?= $user['id'] ?>"><i class="fas fa-edit"></i></button>
                                            <?php if ($user['status'] == 1): ?>
                                                <a href="users.php?delete=<?= $user['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Vô hiệu hóa người dùng này?')"><i class="fas fa-trash"></i></a>
                                            <?php else: ?>
                                                <a href="users.php?restore=<?= $user['id'] ?>" class="btn btn-sm btn-success" onclick="return confirm('Khôi phục người dùng này?')"><i class="fas fa-undo-alt"></i></a>
                                            <?php endif; ?>
                                         </td>
                                    </tr>

                                    <!-- Modal sửa user -->
                                    <div class="modal fade" id="editModal<?= $user['id'] ?>">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Sửa người dùng</h5>
                                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="hidden" name="action" value="edit">
                                                        <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                                        <div class="form-group"><label>Họ tên</label><input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required></div>
                                                        <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required></div>
                                                        <div class="form-group"><label>Điện thoại</label><input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>"></div>
                                                        <div class="form-group"><label>Mật khẩu mới (để trống nếu không đổi)</label><input type="password" name="password" class="form-control" placeholder="Nhập nếu muốn đổi"></div>
                                                        <div class="form-group"><label>Vai trò</label><select name="role_id" class="form-control">
                                                            <?php foreach ($roles as $role): ?>
                                                                <option value="<?= $role['id'] ?>" <?= $user['role_id'] == $role['id'] ? 'selected' : '' ?>><?= ucfirst($role['name']) ?></option>
                                                            <?php endforeach; ?>
                                                        </select></div>
                                                        <div class="form-group"><label>Trạng thái</label><select name="status" class="form-control">
                                                            <option value="1" <?= $user['status']==1?'selected':'' ?>>Hoạt động</option>
                                                            <option value="0" <?= $user['status']==0?'selected':'' ?>>Khóa</option>
                                                        </select></div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" class="btn btn-primary">Lưu</button>
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if ($totalPages > 1): ?>
                    <nav><ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&role_id=<?= $filter_role ?>&status=<?= $filter_status ?>"><?= $i ?></a></li>
                        <?php endfor; ?>
                    </ul></nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal thêm user -->
<div class="modal fade" id="addUserModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm người dùng mới</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group"><label>Họ tên</label><input type="text" name="name" class="form-control" required></div>
                    <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" required></div>
                    <div class="form-group"><label>Điện thoại</label><input type="text" name="phone" class="form-control"></div>
                    <div class="form-group"><label>Mật khẩu</label><input type="password" name="password" class="form-control" required></div>
                    <div class="form-group"><label>Vai trò</label><select name="role_id" class="form-control">
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= $role['id'] ?>"><?= ucfirst($role['name']) ?></option>
                        <?php endforeach; ?>
                    </select></div>
                    <div class="form-group"><label>Trạng thái</label><select name="status" class="form-control">
                        <option value="1">Hoạt động</option>
                        <option value="0">Khóa</option>
                    </select></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Thêm</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; require_once 'includes/scripts.php'; ?>
