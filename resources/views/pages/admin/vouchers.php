<?php
require_once __DIR__ . '/includes/auth.php';
requireStaffOrAdmin();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

// Tạo bảng nếu chưa có
$pdo->exec("CREATE TABLE IF NOT EXISTS vouchers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    discount_type ENUM('percent','fixed') NOT NULL,
    discount_value DECIMAL(12,2) NOT NULL,
    min_order_amount DECIMAL(12,2) DEFAULT 0,
    max_discount_amount DECIMAL(12,2) DEFAULT 0,
    start_date DATETIME,
    end_date DATETIME,
    usage_limit INT DEFAULT 0,
    used_count INT DEFAULT 0,
    status TINYINT DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
)");

// Thêm mới
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $code = strtoupper(trim($_POST['code']));
    $name = trim($_POST['name']);
    $discount_type = $_POST['discount_type'];
    $discount_value = (float)$_POST['discount_value'];
    $min_order_amount = (float)($_POST['min_order_amount'] ?? 0);
    $max_discount_amount = (float)($_POST['max_discount_amount'] ?? 0);
    $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
    $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
    $usage_limit = (int)($_POST['usage_limit'] ?? 0);
    $status = (int)($_POST['status'] ?? 1);
    if (empty($code) || empty($name) || $discount_value <= 0) $error = "Vui lòng nhập đầy đủ.";
    else {
        $check = $pdo->prepare("SELECT id FROM vouchers WHERE code = ?");
        $check->execute([$code]);
        if ($check->fetch()) $error = "Mã đã tồn tại.";
        else {
            $stmt = $pdo->prepare("INSERT INTO vouchers (code, name, discount_type, discount_value, min_order_amount, max_discount_amount, start_date, end_date, usage_limit, status, created_at) VALUES (?,?,?,?,?,?,?,?,?,?, NOW())");
            $stmt->execute([$code, $name, $discount_type, $discount_value, $min_order_amount, $max_discount_amount, $start_date, $end_date, $usage_limit, $status]);
            $success = "Thêm khuyến mãi thành công!";
        }
    }
}
// Sửa
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit') {
    $id = (int)$_POST['id'];
    $code = strtoupper(trim($_POST['code']));
    $name = trim($_POST['name']);
    $discount_type = $_POST['discount_type'];
    $discount_value = (float)$_POST['discount_value'];
    $min_order_amount = (float)($_POST['min_order_amount'] ?? 0);
    $max_discount_amount = (float)($_POST['max_discount_amount'] ?? 0);
    $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
    $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
    $usage_limit = (int)($_POST['usage_limit'] ?? 0);
    $status = (int)($_POST['status'] ?? 1);
    if (empty($code) || empty($name) || $discount_value <= 0) $error = "Vui lòng nhập đầy đủ.";
    else {
        $check = $pdo->prepare("SELECT id FROM vouchers WHERE code = ? AND id != ?");
        $check->execute([$code, $id]);
        if ($check->fetch()) $error = "Mã đã tồn tại.";
        else {
            $stmt = $pdo->prepare("UPDATE vouchers SET code=?, name=?, discount_type=?, discount_value=?, min_order_amount=?, max_discount_amount=?, start_date=?, end_date=?, usage_limit=?, status=?, updated_at=NOW() WHERE id=?");
            $stmt->execute([$code, $name, $discount_type, $discount_value, $min_order_amount, $max_discount_amount, $start_date, $end_date, $usage_limit, $status, $id]);
            $success = "Cập nhật thành công!";
        }
    }
}
// Xóa mềm
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("UPDATE vouchers SET status = 0 WHERE id = ?")->execute([$id]);
    $success = "Đã vô hiệu hóa khuyến mãi.";
}
if (isset($_GET['restore'])) {
    $id = (int)$_GET['restore'];
    $pdo->prepare("UPDATE vouchers SET status = 1 WHERE id = ?")->execute([$id]);
    $success = "Đã khôi phục khuyến mãi.";
}

// Phân trang, lọc
$limit = 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? (int)$_GET['status'] : -1;
$type_filter = isset($_GET['discount_type']) ? $_GET['discount_type'] : '';

$where = " WHERE 1=1 ";
$params = [];
if ($search != '') { $where .= " AND (code LIKE ? OR name LIKE ?) "; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($status_filter != -1) { $where .= " AND status = ? "; $params[] = $status_filter; }
if ($type_filter != '') { $where .= " AND discount_type = ? "; $params[] = $type_filter; }

$total = $pdo->prepare("SELECT COUNT(*) FROM vouchers $where");
$total->execute($params);
$totalRecords = $total->fetchColumn();
$totalPages = ceil($totalRecords / $limit);

$stmt = $pdo->prepare("SELECT * FROM vouchers $where ORDER BY id DESC LIMIT $limit OFFSET $offset");
$stmt->execute($params);
$vouchers = $stmt->fetchAll();
?>

<div id="content-wrapper" class="d-flex flex-column"><div id="content"><?php require_once __DIR__ . '/includes/topbar.php'; ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Quản lý khuyến mãi</h1>
        <button class="btn btn-primary" data-toggle="modal" data-target="#addModal"><i class="fas fa-plus"></i> Thêm mã</button>
    </div>
    <?php if (isset($success)): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if (isset($error)): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="card shadow mb-4"><div class="card-header py-3">
        <form method="GET" class="form-inline">
            <input type="text" name="search" class="form-control mr-2" placeholder="Mã, tên" value="<?= htmlspecialchars($search) ?>">
            <select name="discount_type" class="form-control mr-2"><option value="">-- Loại --</option><option value="percent" <?= $type_filter=='percent'?'selected':'' ?>>Phần trăm</option><option value="fixed" <?= $type_filter=='fixed'?'selected':'' ?>>Tiền mặt</option></select>
            <select name="status" class="form-control mr-2"><option value="-1">-- Trạng thái --</option><option value="1" <?= $status_filter==1?'selected':'' ?>>Hoạt động</option><option value="0" <?= $status_filter==0?'selected':'' ?>>Vô hiệu</option></select>
            <button type="submit" class="btn btn-primary">Lọc</button><a href="vouchers.php" class="btn btn-secondary ml-2">Reset</a>
        </form>
    </div></div>

    <div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Danh sách mã khuyến mãi</h6></div>
    <div class="card-body"><div class="table-responsive">
        <table class="table table-bordered"><thead><tr><th>ID</th><th>Mã</th><th>Tên</th><th>Loại</th><th>Giá trị</th><th>Đơn tối thiểu</th><th>Giảm tối đa</th><th>Ngày hiệu lực</th><th>Lượt dùng</th><th>Trạng thái</th><th>Hành động</th></tr></thead>
        <tbody><?php foreach ($vouchers as $v): ?>
            <tr><td><?= $v['id'] ?></td><td><strong><?= htmlspecialchars($v['code']) ?></strong></td><td><?= htmlspecialchars($v['name']) ?></td>
            <td><?= $v['discount_type']=='percent'?'Phần trăm':'Tiền mặt' ?></td><td><?= $v['discount_type']=='percent'?$v['discount_value'].'%':number_format($v['discount_value']).'đ' ?></td>
            <td><?= number_format($v['min_order_amount']) ?>đ</td><td><?= number_format($v['max_discount_amount']) ?>đ</td>
            <td><?= ($v['start_date']?date('d/m/Y',strtotime($v['start_date'])):'—').' → '.($v['end_date']?date('d/m/Y',strtotime($v['end_date'])):'—') ?></td>
            <td><?= ($v['used_count']??0).' / '.($v['usage_limit']?$v['usage_limit']:'∞') ?></td>
            <td><?= $v['status']==1?'<span class="badge badge-success">Hoạt động</span>':'<span class="badge badge-danger">Vô hiệu</span>' ?></td>
            <td><button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editModal<?= $v['id'] ?>"><i class="fas fa-edit"></i></button>
                <?php if($v['status']==1): ?><a href="vouchers.php?delete=<?= $v['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Vô hiệu hóa?')"><i class="fas fa-trash"></i></a>
                <?php else: ?><a href="vouchers.php?restore=<?= $v['id'] ?>" class="btn btn-sm btn-success"><i class="fas fa-undo-alt"></i></a><?php endif; ?>
            </td></tr>
            <!-- Modal sửa -->
            <div class="modal fade" id="editModal<?= $v['id'] ?>"><div class="modal-dialog modal-lg"><div class="modal-content">
                <form method="POST"><div class="modal-header"><h5>Sửa mã khuyến mãi</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
                <div class="modal-body"><input type="hidden" name="action" value="edit"><input type="hidden" name="id" value="<?= $v['id'] ?>">
                    <div class="form-row"><div class="col-md-6"><div class="form-group"><label>Mã</label><input type="text" name="code" class="form-control" value="<?= htmlspecialchars($v['code']) ?>" required></div></div>
                    <div class="col-md-6"><div class="form-group"><label>Tên</label><input type="text" name="name" class="form-control" value="<?= htmlspecialchars($v['name']) ?>" required></div></div></div>
                    <div class="form-row"><div class="col-md-4"><div class="form-group"><label>Loại</label><select name="discount_type" class="form-control"><option value="percent" <?= $v['discount_type']=='percent'?'selected':'' ?>>Phần trăm</option><option value="fixed" <?= $v['discount_type']=='fixed'?'selected':'' ?>>Tiền mặt</option></select></div></div>
                    <div class="col-md-4"><div class="form-group"><label>Giá trị</label><input type="number" step="1000" name="discount_value" class="form-control" value="<?= $v['discount_value'] ?>" required></div></div>
                    <div class="col-md-4"><div class="form-group"><label>Đơn tối thiểu</label><input type="number" step="1000" name="min_order_amount" class="form-control" value="<?= $v['min_order_amount'] ?>"></div></div></div>
                    <div class="form-row"><div class="col-md-4"><div class="form-group"><label>Giảm tối đa</label><input type="number" step="1000" name="max_discount_amount" class="form-control" value="<?= $v['max_discount_amount'] ?>"></div></div>
                    <div class="col-md-4"><div class="form-group"><label>Ngày bắt đầu</label><input type="datetime-local" name="start_date" class="form-control" value="<?= $v['start_date']?date('Y-m-d\TH:i',strtotime($v['start_date'])):'' ?>"></div></div>
                    <div class="col-md-4"><div class="form-group"><label>Ngày kết thúc</label><input type="datetime-local" name="end_date" class="form-control" value="<?= $v['end_date']?date('Y-m-d\TH:i',strtotime($v['end_date'])):'' ?>"></div></div></div>
                    <div class="form-row"><div class="col-md-4"><div class="form-group"><label>Giới hạn lượt</label><input type="number" name="usage_limit" class="form-control" value="<?= $v['usage_limit'] ?>"></div></div>
                    <div class="col-md-4"><div class="form-group"><label>Đã dùng</label><input type="text" class="form-control" value="<?= $v['used_count'] ?>" disabled></div></div>
                    <div class="col-md-4"><div class="form-group"><label>Trạng thái</label><select name="status" class="form-control"><option value="1" <?= $v['status']==1?'selected':'' ?>>Hoạt động</option><option value="0" <?= $v['status']==0?'selected':'' ?>>Vô hiệu</option></select></div></div></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Lưu</button><button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button></div>
                </form>
            </div></div></div>
        <?php endforeach; ?></tbody>
        </table>
        <?php if($totalPages>1): ?><nav><ul class="pagination justify-content-center"><?php for($i=1;$i<=$totalPages;$i++): ?><li class="page-item <?= $i==$page?'active':'' ?>"><a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&discount_type=<?= $type_filter ?>&status=<?= $status_filter ?>"><?= $i ?></a></li><?php endfor; ?></ul></nav><?php endif; ?>
    </div></div></div>
</div></div></div>

<!-- Modal thêm -->
<div class="modal fade" id="addModal"><div class="modal-dialog modal-lg"><div class="modal-content">
    <form method="POST"><div class="modal-header"><h5>Thêm mã khuyến mãi</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
    <div class="modal-body"><input type="hidden" name="action" value="add">
        <div class="form-row"><div class="col-md-6"><div class="form-group"><label>Mã</label><input type="text" name="code" class="form-control" required></div></div>
        <div class="col-md-6"><div class="form-group"><label>Tên</label><input type="text" name="name" class="form-control" required></div></div></div>
        <div class="form-row"><div class="col-md-4"><div class="form-group"><label>Loại</label><select name="discount_type" class="form-control"><option value="percent">Phần trăm</option><option value="fixed">Tiền mặt</option></select></div></div>
        <div class="col-md-4"><div class="form-group"><label>Giá trị</label><input type="number" step="1000" name="discount_value" class="form-control" required></div></div>
        <div class="col-md-4"><div class="form-group"><label>Đơn tối thiểu</label><input type="number" step="1000" name="min_order_amount" class="form-control" value="0"></div></div></div>
        <div class="form-row"><div class="col-md-4"><div class="form-group"><label>Giảm tối đa</label><input type="number" step="1000" name="max_discount_amount" class="form-control" value="0"></div></div>
        <div class="col-md-4"><div class="form-group"><label>Ngày bắt đầu</label><input type="datetime-local" name="start_date" class="form-control"></div></div>
        <div class="col-md-4"><div class="form-group"><label>Ngày kết thúc</label><input type="datetime-local" name="end_date" class="form-control"></div></div></div>
        <div class="form-row"><div class="col-md-4"><div class="form-group"><label>Giới hạn lượt</label><input type="number" name="usage_limit" class="form-control" value="0"></div></div>
        <div class="col-md-4"><div class="form-group"><label>Trạng thái</label><select name="status" class="form-control"><option value="1">Hoạt động</option><option value="0">Vô hiệu</option></select></div></div></div>
    </div>
    <div class="modal-footer"><button type="submit" class="btn btn-primary">Lưu</button><button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button></div>
    </form>
</div></div></div>

<?php require_once __DIR__ . '/includes/footer.php'; require_once __DIR__ . '/includes/scripts.php'; ?>