<?php
require_once __DIR__ . '/includes/auth.php';
requireStaffOrAdmin();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

// Tự động tạo bảng reviews nếu chưa có (đảm bảo)
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS reviews (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        customer_id BIGINT UNSIGNED,
        product_id BIGINT UNSIGNED,
        rating INT,
        comment TEXT,
        images TEXT NULL,
        likes INT DEFAULT 0,
        status TINYINT DEFAULT 1,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL,
        FOREIGN KEY (customer_id) REFERENCES customers(id),
        FOREIGN KEY (product_id) REFERENCES products(id)
    )");
} catch (PDOException $e) {}

// Thêm cột nếu thiếu (đề phòng)
try { $pdo->exec("ALTER TABLE reviews ADD COLUMN status TINYINT DEFAULT 1"); } catch(PDOException $e){}
try { $pdo->exec("ALTER TABLE reviews ADD COLUMN images TEXT NULL"); } catch(PDOException $e){}
try { $pdo->exec("ALTER TABLE reviews ADD COLUMN likes INT DEFAULT 0"); } catch(PDOException $e){}

// Xử lý duyệt / ẩn đánh giá (toggle status)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'toggle_status') {
    $review_id = (int)$_POST['review_id'];
    $current_status = (int)$_POST['current_status'];
    $new_status = $current_status == 1 ? 0 : 1;
    $stmt = $pdo->prepare("UPDATE reviews SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $review_id]);
    $success = $new_status == 1 ? "Đã hiển thị đánh giá." : "Đã ẩn đánh giá.";
}

// Xóa mềm (set status = 0)
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("UPDATE reviews SET status = 0 WHERE id = ?")->execute([$id]);
    $success = "Đã xóa đánh giá (chuyển sang ẩn).";
}

// Khôi phục
if (isset($_GET['restore'])) {
    $id = (int)$_GET['restore'];
    $pdo->prepare("UPDATE reviews SET status = 1 WHERE id = ?")->execute([$id]);
    $success = "Đã khôi phục đánh giá.";
}

// Phân trang và lọc
$limit = 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$product_filter = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
$rating_filter = isset($_GET['rating']) ? (int)$_GET['rating'] : 0;
$status_filter = isset($_GET['status']) ? (int)$_GET['status'] : -1; // -1: all, 1: hiển thị, 0: ẩn

$where = " WHERE 1=1 ";
$params = [];
if ($search != '') {
    $where .= " AND (c.full_name LIKE ? OR c.email LIKE ? OR p.name LIKE ? OR r.comment LIKE ?) ";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($product_filter > 0) {
    $where .= " AND r.product_id = ? ";
    $params[] = $product_filter;
}
if ($rating_filter > 0) {
    $where .= " AND r.rating = ? ";
    $params[] = $rating_filter;
}
if ($status_filter != -1) {
    $where .= " AND r.status = ? ";
    $params[] = $status_filter;
}

// Đếm tổng
$countSql = "SELECT COUNT(*) FROM reviews r 
             LEFT JOIN customers c ON r.customer_id = c.id 
             LEFT JOIN products p ON r.product_id = p.id 
             $where";
$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$totalRecords = $stmt->fetchColumn();
$totalPages = ceil($totalRecords / $limit);

// Lấy danh sách đánh giá
$sql = "SELECT r.*, c.full_name as customer_name, c.email, c.phone, p.name as product_name, p.image as product_image
        FROM reviews r 
        LEFT JOIN customers c ON r.customer_id = c.id 
        LEFT JOIN products p ON r.product_id = p.id 
        $where 
        ORDER BY r.id DESC 
        LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reviews = $stmt->fetchAll();

// Lấy danh sách sản phẩm cho dropdown lọc
$products = $pdo->query("SELECT id, name FROM products WHERE status = 1 ORDER BY name")->fetchAll();
?>

<div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
        <?php require_once __DIR__ . '/includes/topbar.php'; ?>
        <div class="container-fluid">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Quản lý đánh giá</h1>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert"><?= htmlspecialchars($success) ?><button type="button" class="close" data-dismiss="alert">&times;</button></div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert"><?= htmlspecialchars($error) ?><button type="button" class="close" data-dismiss="alert">&times;</button></div>
            <?php endif; ?>

            <!-- Bộ lọc -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <form method="GET" class="form-inline">
                        <input type="text" name="search" class="form-control mr-2" placeholder="Tìm theo tên, email, sản phẩm, nội dung" value="<?= htmlspecialchars($search) ?>">
                        <select name="product_id" class="form-control mr-2">
                            <option value="0">-- Tất cả sản phẩm --</option>
                            <?php foreach ($products as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= $product_filter == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="rating" class="form-control mr-2">
                            <option value="0">-- Tất cả số sao --</option>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <option value="<?= $i ?>" <?= $rating_filter == $i ? 'selected' : '' ?>><?= $i ?> sao</option>
                            <?php endfor; ?>
                        </select>
                        <select name="status" class="form-control mr-2">
                            <option value="-1">-- Tất cả trạng thái --</option>
                            <option value="1" <?= $status_filter == 1 ? 'selected' : '' ?>>Hiển thị</option>
                            <option value="0" <?= $status_filter == 0 ? 'selected' : '' ?>>Ẩn</option>
                        </select>
                        <button type="submit" class="btn btn-primary">Lọc</button>
                        <a href="reviews.php" class="btn btn-secondary ml-2">Reset</a>
                    </form>
                </div>
            </div>

            <!-- Danh sách đánh giá -->
            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Danh sách đánh giá</h6></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr><th>ID</th><th>Sản phẩm</th><th>Khách hàng</th><th>Đánh giá</th><th>Nội dung</th><th>Trạng thái</th><th>Ngày tạo</th><th>Thao tác</th>
                            </thead>
                            <tbody>
                                <?php if (count($reviews) == 0): ?>
                                    <tr><td colspan="8" class="text-center">Không có đánh giá nào</td></tr>
                                <?php else: ?>
                                    <?php foreach ($reviews as $rv): ?>
                                    <tr>
                                        <td><?= $rv['id'] ?></td>
                                        <td><?= htmlspecialchars($rv['product_name'] ?? 'Sản phẩm đã xóa') ?></td>
                                        <td><?= htmlspecialchars($rv['customer_name'] ?? 'Khách ẩn danh') ?><br><small><?= htmlspecialchars($rv['email'] ?? '') ?></small></td>
                                        <td>
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <?php if ($i <= $rv['rating']): ?>
                                                    <i class="fas fa-star text-warning"></i>
                                                <?php else: ?>
                                                    <i class="far fa-star text-muted"></i>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                         </td>
                                        <td><?= nl2br(htmlspecialchars(substr($rv['comment'] ?? '', 0, 100))) ?><?= strlen($rv['comment'] ?? '') > 100 ? '...' : '' ?> </td>
                                        <td>
                                            <?php if ($rv['status'] == 1): ?>
                                                <span class="badge badge-success">Hiển thị</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Ẩn</span>
                                            <?php endif; ?>
                                         </td>
                                        <td><?= date('d/m/Y H:i', strtotime($rv['created_at'] ?? 'now')) ?> </td>
                                        <td>
                                            <form method="POST" style="display:inline-block">
                                                <input type="hidden" name="action" value="toggle_status">
                                                <input type="hidden" name="review_id" value="<?= $rv['id'] ?>">
                                                <input type="hidden" name="current_status" value="<?= $rv['status'] ?>">
                                                <button type="submit" class="btn btn-sm btn-primary" title="Đổi trạng thái">
                                                    <?= $rv['status'] == 1 ? '<i class="fas fa-eye-slash"></i>' : '<i class="fas fa-eye"></i>' ?>
                                                </button>
                                            </form>
                                            <?php if ($rv['status'] == 1): ?>
                                                <a href="reviews.php?delete=<?= $rv['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Ẩn đánh giá này?')"><i class="fas fa-trash"></i></a>
                                            <?php else: ?>
                                                <a href="reviews.php?restore=<?= $rv['id'] ?>" class="btn btn-sm btn-success" onclick="return confirm('Khôi phục đánh giá này?')"><i class="fas fa-undo-alt"></i></a>
                                            <?php endif; ?>
                                            <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#detailModal<?= $rv['id'] ?>"><i class="fas fa-eye"></i></button>
                                         </td>
                                    </tr>

                                    <!-- Modal xem chi tiết đánh giá -->
                                    <div class="modal fade" id="detailModal<?= $rv['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5>Chi tiết đánh giá #<?= $rv['id'] ?></h5>
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                </div>
                                                <div class="modal-body">
                                                    <p><strong>Sản phẩm:</strong> <?= htmlspecialchars($rv['product_name'] ?? 'Đã xóa') ?></p>
                                                    <p><strong>Khách hàng:</strong> <?= htmlspecialchars($rv['customer_name'] ?? 'Ẩn danh') ?></p>
                                                    <p><strong>Email:</strong> <?= htmlspecialchars($rv['email'] ?? '') ?></p>
                                                    <p><strong>Số điện thoại:</strong> <?= htmlspecialchars($rv['phone'] ?? '') ?></p>
                                                    <p><strong>Đánh giá:</strong> 
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                            <?= $i <= $rv['rating'] ? '⭐' : '☆' ?>
                                                        <?php endfor; ?>
                                                    </p>
                                                    <p><strong>Nội dung:</strong></p>
                                                    <div class="border rounded p-2 bg-light"><?= nl2br(htmlspecialchars($rv['comment'] ?? '')) ?></div>
                                                    <?php if (!empty($rv['images'])): ?>
                                                        <p><strong>Hình ảnh:</strong> <a href="<?= htmlspecialchars($rv['images']) ?>" target="_blank">Xem</a></p>
                                                    <?php endif; ?>
                                                    <p><strong>Trạng thái:</strong> <?= $rv['status'] == 1 ? 'Hiển thị' : 'Ẩn' ?></p>
                                                    <p><strong>Ngày tạo:</strong> <?= date('d/m/Y H:i:s', strtotime($rv['created_at'] ?? 'now')) ?></p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if ($totalPages > 1): ?>
                    <nav><ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&product_id=<?= $product_filter ?>&rating=<?= $rating_filter ?>&status=<?= $status_filter ?>"><?= $i ?></a></li>
                        <?php endfor; ?>
                    </ul></nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; require_once __DIR__ . '/includes/scripts.php'; ?>