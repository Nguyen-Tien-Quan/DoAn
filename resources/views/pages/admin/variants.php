<?php
require_once __DIR__ . '/includes/auth.php';
requireStaffOrAdmin();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

// Tự động thêm cột nếu thiếu (đảm bảo tương thích)
try { $pdo->exec("ALTER TABLE product_variants ADD COLUMN stock_quantity INT DEFAULT 0"); } catch(PDOException $e) {}
try { $pdo->exec("ALTER TABLE product_variants ADD COLUMN status TINYINT DEFAULT 1"); } catch(PDOException $e) {}

// Xử lý thêm mới size
$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $product_id = (int)($_POST['product_id'] ?? 0);
    $variant_name = trim($_POST['variant_name'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock_quantity'] ?? 0);
    if ($product_id > 0 && !empty($variant_name) && $price > 0) {
        $stmt = $pdo->prepare("INSERT INTO product_variants (product_id, variant_name, price, stock_quantity) VALUES (?,?,?,?)");
        $stmt->execute([$product_id, $variant_name, $price, $stock]);
        $success = "Thêm size thành công";
    } else {
        $error = "Vui lòng nhập đầy đủ thông tin (sản phẩm, tên size, giá)";
    }
}

// Xử lý sửa size
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit') {
    $id = (int)($_POST['id'] ?? 0);
    $product_id = (int)($_POST['product_id'] ?? 0);
    $variant_name = trim($_POST['variant_name'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock_quantity'] ?? 0);
    if ($id > 0 && $product_id > 0 && !empty($variant_name) && $price > 0) {
        $stmt = $pdo->prepare("UPDATE product_variants SET product_id=?, variant_name=?, price=?, stock_quantity=? WHERE id=?");
        $stmt->execute([$product_id, $variant_name, $price, $stock, $id]);
        $success = "Cập nhật size thành công";
    } else {
        $error = "Dữ liệu không hợp lệ";
    }
}

// Xóa size (xóa cứng, vì size không quan trọng lưu lịch sử)
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM product_variants WHERE id = ?")->execute([$id]);
    $success = "Đã xóa size";
}

// Lấy danh sách sản phẩm để hiển thị trong dropdown
$products = $pdo->query("SELECT id, name FROM products WHERE status = 1 ORDER BY name")->fetchAll();
// Lấy danh sách size kèm tên sản phẩm
$variants = $pdo->query("
    SELECT v.*, p.name as product_name 
    FROM product_variants v 
    LEFT JOIN products p ON v.product_id = p.id 
    ORDER BY v.id DESC
")->fetchAll();
?>

<div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
        <?php require_once __DIR__ . '/includes/topbar.php'; ?>
        <div class="container-fluid">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Quản lý size món ăn</h1>
                <button class="btn btn-primary" data-toggle="modal" data-target="#addVariantModal"><i class="fas fa-plus"></i> Thêm size</button>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert"><?= htmlspecialchars($success) ?><button type="button" class="close" data-dismiss="alert">&times;</button></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert"><?= htmlspecialchars($error) ?><button type="button" class="close" data-dismiss="alert">&times;</button></div>
            <?php endif; ?>

            <!-- Modal thêm size -->
            <div class="modal fade" id="addVariantModal" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form method="POST">
                            <div class="modal-header">
                                <h5 class="modal-title">Thêm size mới</h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="action" value="add">
                                <div class="form-group">
                                    <label>Chọn món <span class="text-danger">*</span></label>
                                    <select name="product_id" class="form-control" required>
                                        <option value="">-- Chọn --</option>
                                        <?php foreach ($products as $p): ?>
                                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Tên size <span class="text-danger">*</span></label>
                                    <input type="text" name="variant_name" class="form-control" placeholder="Ví dụ: S, M, L, Large..." required>
                                </div>
                                <div class="form-group">
                                    <label>Giá <span class="text-danger">*</span></label>
                                    <input type="number" step="1000" name="price" class="form-control" placeholder="Giá bán (VNĐ)" required>
                                </div>
                                <div class="form-group">
                                    <label>Tồn kho</label>
                                    <input type="number" name="stock_quantity" class="form-control" value="0">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Lưu</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Danh sách size -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Danh sách size</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr><th>ID</th><th>Sản phẩm</th><th>Tên size</th><th>Giá</th><th>Tồn kho</th><th>Hành động</th>
                            </thead>
                            <tbody>
                                <?php if (count($variants) == 0): ?>
                                    <tr><td colspan="6" class="text-center">Chưa có size nào</td></tr>
                                <?php else: ?>
                                    <?php foreach ($variants as $v): ?>
                                    <tr>
                                        <td><?= $v['id'] ?></td>
                                        <td><?= htmlspecialchars($v['product_name']) ?></td>
                                        <td><?= htmlspecialchars($v['variant_name']) ?></td>
                                        <td><?= number_format($v['price']) ?>₫</td>
                                        <td><?= (int)$v['stock_quantity'] ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editModal<?= $v['id'] ?>"><i class="fas fa-edit"></i></button>
                                            <a href="variants.php?delete=<?= $v['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Xóa size này?')"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>

                                    <!-- Modal sửa size -->
                                    <div class="modal fade" id="editModal<?= $v['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Sửa size</h5>
                                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="hidden" name="action" value="edit">
                                                        <input type="hidden" name="id" value="<?= $v['id'] ?>">
                                                        <div class="form-group">
                                                            <label>Chọn món <span class="text-danger">*</span></label>
                                                            <select name="product_id" class="form-control" required>
                                                                <?php foreach ($products as $p): ?>
                                                                    <option value="<?= $p['id'] ?>" <?= $p['id'] == $v['product_id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['name']) ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Tên size <span class="text-danger">*</span></label>
                                                            <input type="text" name="variant_name" class="form-control" value="<?= htmlspecialchars($v['variant_name']) ?>" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Giá <span class="text-danger">*</span></label>
                                                            <input type="number" step="1000" name="price" class="form-control" value="<?= $v['price'] ?>" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Tồn kho</label>
                                                            <input type="number" name="stock_quantity" class="form-control" value="<?= (int)$v['stock_quantity'] ?>">
                                                        </div>
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
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; require_once __DIR__ . '/includes/scripts.php'; ?>