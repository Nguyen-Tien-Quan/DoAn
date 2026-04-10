<?php
require_once __DIR__ . '/includes/auth.php';
requireStaffOrAdmin();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

// Thêm cột nếu thiếu
try { $pdo->exec("ALTER TABLE categories ADD COLUMN status TINYINT DEFAULT 1"); } catch(PDOException $e){}
try { $pdo->exec("ALTER TABLE categories ADD COLUMN slug VARCHAR(150) UNIQUE"); } catch(PDOException $e){}
try { $pdo->exec("ALTER TABLE categories ADD COLUMN image VARCHAR(255) NULL"); } catch(PDOException $e){}
try { $pdo->exec("ALTER TABLE categories ADD COLUMN description TEXT NULL"); } catch(PDOException $e){}

// Thêm mới
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $name = trim($_POST['name']);
    $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));
    $description = trim($_POST['description'] ?? '');
    $status = (int)($_POST['status'] ?? 1);
    if (!empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description, status) VALUES (?,?,?,?)");
        $stmt->execute([$name, $slug, $description, $status]);
        $success = "Thêm danh mục thành công";
    } else $error = "Tên danh mục không được trống";
}

// Sửa
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit') {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));
    $description = trim($_POST['description'] ?? '');
    $status = (int)($_POST['status'] ?? 1);
    if ($id > 0 && !empty($name)) {
        $stmt = $pdo->prepare("UPDATE categories SET name=?, slug=?, description=?, status=? WHERE id=?");
        $stmt->execute([$name, $slug, $description, $status, $id]);
        $success = "Cập nhật thành công";
    } else $error = "Dữ liệu không hợp lệ";
}

// Xóa mềm (status=0)
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("UPDATE categories SET status = 0 WHERE id = ?")->execute([$id]);
    $success = "Đã vô hiệu hóa danh mục";
}
if (isset($_GET['restore'])) {
    $id = (int)$_GET['restore'];
    $pdo->prepare("UPDATE categories SET status = 1 WHERE id = ?")->execute([$id]);
    $success = "Đã khôi phục danh mục";
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY id DESC")->fetchAll();
?>

<div id="content-wrapper" class="d-flex flex-column"><div id="content"><?php require_once __DIR__ . '/includes/topbar.php'; ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Quản lý danh mục</h1>
        <button class="btn btn-primary" data-toggle="modal" data-target="#addModal"><i class="fas fa-plus"></i> Thêm danh mục</button>
    </div>
    <?php if (isset($success)): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if (isset($error)): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <!-- Modal thêm -->
    <div class="modal fade" id="addModal"><div class="modal-dialog"><div class="modal-content">
        <form method="POST"><div class="modal-header"><h5>Thêm danh mục</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body"><input type="hidden" name="action" value="add">
            <div class="form-group"><label>Tên danh mục</label><input type="text" name="name" class="form-control" required></div>
            <div class="form-group"><label>Mô tả</label><textarea name="description" class="form-control" rows="3"></textarea></div>
            <div class="form-group"><label>Trạng thái</label><select name="status" class="form-control"><option value="1">Hiển thị</option><option value="0">Ẩn</option></select></div>
        </div>
        <div class="modal-footer"><button type="submit" class="btn btn-primary">Lưu</button><button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button></div>
        </form>
    </div></div></div>

    <div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Danh sách danh mục</h6></div>
    <div class="card-body"><div class="table-responsive">
        <table class="table table-bordered"><thead><tr><th>ID</th><th>Tên</th><th>Slug</th><th>Mô tả</th><th>Trạng thái</th><th>Hành động</th></tr></thead>
        <tbody><?php foreach ($categories as $cat): ?>
            <tr><td><?= $cat['id'] ?></td><td><?= htmlspecialchars($cat['name']) ?></td><td><?= htmlspecialchars($cat['slug'] ?? '') ?></td><td><?= htmlspecialchars($cat['description'] ?? '') ?></td>
            <td><?= $cat['status']==1?'<span class="badge badge-success">Hiển thị</span>':'<span class="badge badge-secondary">Ẩn</span>' ?></td>
            <td><button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editModal<?= $cat['id'] ?>"><i class="fas fa-edit"></i></button>
                <?php if($cat['status']==1): ?><a href="categories.php?delete=<?= $cat['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Vô hiệu hóa?')"><i class="fas fa-trash"></i></a>
                <?php else: ?><a href="categories.php?restore=<?= $cat['id'] ?>" class="btn btn-sm btn-success"><i class="fas fa-undo-alt"></i></a><?php endif; ?>
            </td></tr>
            <!-- Modal sửa -->
            <div class="modal fade" id="editModal<?= $cat['id'] ?>"><div class="modal-dialog"><div class="modal-content">
                <form method="POST"><div class="modal-header"><h5>Sửa danh mục</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
                <div class="modal-body"><input type="hidden" name="action" value="edit"><input type="hidden" name="id" value="<?= $cat['id'] ?>">
                    <div class="form-group"><label>Tên danh mục</label><input type="text" name="name" class="form-control" value="<?= htmlspecialchars($cat['name']) ?>" required></div>
                    <div class="form-group"><label>Mô tả</label><textarea name="description" class="form-control"><?= htmlspecialchars($cat['description']??'') ?></textarea></div>
                    <div class="form-group"><label>Trạng thái</label><select name="status" class="form-control"><option value="1" <?= $cat['status']==1?'selected':'' ?>>Hiển thị</option><option value="0" <?= $cat['status']==0?'selected':'' ?>>Ẩn</option></select></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Lưu</button><button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button></div>
                </form>
            </div></div></div>
        <?php endforeach; ?></tbody>
        </table>
    </div></div></div>
</div></div></div>
<?php require_once __DIR__ . '/includes/footer.php'; require_once __DIR__ . '/includes/scripts.php'; ?>