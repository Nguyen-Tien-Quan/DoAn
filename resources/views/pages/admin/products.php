<?php
require_once __DIR__ . '/includes/auth.php';
requireStaffOrAdmin();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

// Tự động tạo bảng nếu thiếu (giữ nguyên code cũ)
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100),
        slug VARCHAR(150) UNIQUE,
        description TEXT,
        image VARCHAR(255),
        status TINYINT DEFAULT 1,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        category_id BIGINT UNSIGNED,
        name VARCHAR(150),
        slug VARCHAR(180) UNIQUE,
        description TEXT,
        base_price DECIMAL(12,2),
        image VARCHAR(255),
        is_featured TINYINT DEFAULT 0,
        status TINYINT DEFAULT 1,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL,
        FOREIGN KEY (category_id) REFERENCES categories(id)
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS toppings (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100),
        price DECIMAL(12,2),
        status TINYINT DEFAULT 1,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS product_variants (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        product_id BIGINT UNSIGNED,
        variant_name VARCHAR(100),
        price DECIMAL(12,2),
        stock_quantity INT DEFAULT 0,
        status TINYINT DEFAULT 1,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS product_toppings (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        product_id BIGINT UNSIGNED,
        topping_id BIGINT UNSIGNED,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        FOREIGN KEY (topping_id) REFERENCES toppings(id) ON DELETE CASCADE
    )");
} catch (PDOException $e) {}

// Thêm cột nếu thiếu
try { $pdo->exec("ALTER TABLE products ADD COLUMN image VARCHAR(255) NULL"); } catch(PDOException $e){}
try { $pdo->exec("ALTER TABLE products ADD COLUMN is_featured TINYINT DEFAULT 0"); } catch(PDOException $e){}
try { $pdo->exec("ALTER TABLE products ADD COLUMN status TINYINT DEFAULT 1"); } catch(PDOException $e){}
try { $pdo->exec("ALTER TABLE products ADD COLUMN slug VARCHAR(180) UNIQUE"); } catch(PDOException $e){}
try { $pdo->exec("ALTER TABLE products ADD COLUMN description TEXT NULL"); } catch(PDOException $e){}
try { $pdo->exec("ALTER TABLE categories ADD COLUMN status TINYINT DEFAULT 1"); } catch(PDOException $e){}

// Xóa mềm
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $pdo->prepare("UPDATE products SET status = 0 WHERE id = ?")->execute([$id]);
    $success = "Đã vô hiệu hóa món ăn.";
}
if (isset($_GET['action']) && $_GET['action'] == 'restore' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $pdo->prepare("UPDATE products SET status = 1 WHERE id = ?")->execute([$id]);
    $success = "Đã khôi phục món ăn.";
}

// Phân trang & lọc
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$status_filter = isset($_GET['status']) ? (int)$_GET['status'] : -1;

$where = " WHERE 1=1 ";
$params = [];
if ($search != '') {
    $where .= " AND p.name LIKE ? ";
    $params[] = "%$search%";
}
if ($category_id > 0) {
    $where .= " AND p.category_id = ? ";
    $params[] = $category_id;
}
if ($status_filter != -1) {
    $where .= " AND p.status = ? ";
    $params[] = $status_filter;
}

$countSql = "SELECT COUNT(*) FROM products p $where";
$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$totalRecords = $stmt->fetchColumn();
$totalPages = ceil($totalRecords / $limit);

$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        $where 
        ORDER BY p.id DESC 
        LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = [];
try {
    $categories = $pdo->query("SELECT id, name FROM categories WHERE status = 1 ORDER BY name")->fetchAll();
} catch (PDOException $e) {
    $categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();
}
?>

<div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
        <?php require_once __DIR__ . '/includes/topbar.php'; ?>
        <div class="container-fluid">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Danh sách món ăn</h1>
                <a href="product-add.php" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Thêm món</a>
            </div>
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <form method="GET" class="form-inline">
                        <input type="text" name="search" class="form-control mr-2" placeholder="Tìm theo tên" value="<?= htmlspecialchars($search) ?>">
                        <select name="category_id" class="form-control mr-2">
                            <option value="0">-- Tất cả danh mục --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $category_id == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="status" class="form-control mr-2">
                            <option value="-1">-- Tất cả trạng thái --</option>
                            <option value="1" <?= $status_filter == 1 ? 'selected' : '' ?>>Đang bán</option>
                            <option value="0" <?= $status_filter == 0 ? 'selected' : '' ?>>Ngừng bán</option>
                        </select>
                        <button type="submit" class="btn btn-primary">Lọc</button>
                        <a href="products.php" class="btn btn-secondary ml-2">Reset</a>
                    </form>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr><th>ID</th><th>Hình ảnh</th><th>Tên món</th><th>Danh mục</th><th>Giá cơ bản</th><th>Nổi bật</th><th>Trạng thái</th><th>Hành động</th>
                            </thead>
                            <tbody>
                                <?php if (count($products) == 0): ?>
                                    <tr><td colspan="8" class="text-center">Không có dữ liệu</td></tr>
                                <?php endif; ?>
                                <?php foreach ($products as $item): ?>
                                <tr>
                                    <td><?= $item['id'] ?></td>
                                    <td>
                                        <?php if (!empty($item['image'])): ?>
                                            <img src="<?= htmlspecialchars($item['image']) ?>" width="50" height="50" class="rounded">
                                        <?php else: ?>
                                            —
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($item['name']) ?></td>
                                    <td><?= htmlspecialchars($item['category_name'] ?? 'Chưa phân loại') ?></td>
                                    <td><?= number_format($item['base_price'] ?? 0, 0, ',', '.') ?>đ</td>
                                    <td><?= (isset($item['is_featured']) && $item['is_featured'] == 1) ? '<span class="badge badge-warning">Nổi bật</span>' : '<span class="badge badge-secondary">Thường</span>' ?></td>
                                    <td><?= (isset($item['status']) && $item['status'] == 1) ? '<span class="badge badge-success">Đang bán</span>' : '<span class="badge badge-danger">Ngừng bán</span>' ?></td>
                                    <td>
                                        <a href="product-edit.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                                        <?php if (isset($item['status']) && $item['status'] == 1): ?>
                                            <a href="javascript:void(0)" onclick="confirmDelete(<?= $item['id'] ?>)" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></a>
                                        <?php else: ?>
                                            <a href="products.php?action=restore&id=<?= $item['id'] ?>" class="btn btn-sm btn-success" onclick="return confirm('Khôi phục món này?')"><i class="fas fa-undo-alt"></i></a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if ($totalPages > 1): ?>
                    <nav><ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category_id=<?= $category_id ?>&status=<?= $status_filter ?>"><?= $i ?></a></li>
                        <?php endfor; ?>
                    </ul></nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(id) {
    if (confirm('Vô hiệu hóa món ăn này?')) {
        window.location.href = 'products.php?action=delete&id=' + id;
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; require_once __DIR__ . '/includes/scripts.php'; ?>