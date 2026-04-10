<?php
require_once 'includes/auth.php';  // QUAN TRỌNG: phải đặt đầu tiên
requireStaffOrAdmin();
require_once 'includes/db.php';

// ================== XỬ LÝ AJAX (chi tiết đơn hàng) ==================
if (isset($_GET['ajax']) && $_GET['ajax'] == 'detail') {
    $id = (int)$_GET['id'];
    if ($id <= 0) exit('ID không hợp lệ');

    $stmt = $pdo->prepare("SELECT o.*, c.full_name, c.phone, c.address, 
                                  p.payment_method, p.payment_status
                           FROM orders o
                           LEFT JOIN customers c ON o.customer_id = c.id
                           LEFT JOIN payments p ON o.id = p.order_id
                           WHERE o.id = ?");
    $stmt->execute([$id]);
    $order = $stmt->fetch();
    if (!$order) exit('Không tìm thấy đơn hàng');

    $items = $pdo->prepare("SELECT oi.*, pr.name as product_name, pv.variant_name
                            FROM order_items oi
                            LEFT JOIN products pr ON oi.product_id = pr.id
                            LEFT JOIN product_variants pv ON oi.variant_id = pv.id
                            WHERE oi.order_id = ?");
    $items->execute([$id]);
    $items = $items->fetchAll();

    $payment_labels = ['pending' => 'Chưa thanh toán', 'paid' => 'Đã thanh toán', 'failed' => 'Thất bại', 'refunded' => 'Hoàn tiền'];
?>
    <div>
        <p><strong>Mã đơn:</strong> <?= htmlspecialchars($order['order_code']) ?></p>
        <p><strong>Khách hàng:</strong> <?= htmlspecialchars($order['full_name'] ?? 'Khách lẻ') ?></p>
        <p><strong>SĐT:</strong> <?= htmlspecialchars($order['phone'] ?? '') ?></p>
        <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($order['delivery_address'] ?? $order['address'] ?? '') ?></p>
        <p><strong>Ghi chú:</strong> <?= nl2br(htmlspecialchars($order['note'] ?? '')) ?></p>
        <p><strong>PT thanh toán:</strong> <?= $order['payment_method'] ?? 'Chưa có' ?></p>
        <p><strong>Trạng thái thanh toán:</strong> <?= $payment_labels[$order['payment_status']] ?? $order['payment_status'] ?></p>
        <hr>
        <h6>Sản phẩm đã đặt</h6>
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead class="thead-light">
                    <tr><th>Sản phẩm</th><th>Size</th><th>Số lượng</th><th>Đơn giá</th><th>Topping</th><th>Thành tiền</th></tr>
                </thead>
                <tbody>
                    <?php if (count($items) == 0): ?>
                        <tr><td colspan="6" class="text-center">Chưa có sản phẩm</td></tr>
                    <?php else: ?>
                        <?php foreach ($items as $item):
                            $top = $pdo->prepare("SELECT t.name, oit.price FROM order_item_toppings oit JOIN toppings t ON oit.topping_id = t.id WHERE oit.order_item_id = ?");
                            $top->execute([$item['id']]);
                            $toppings = $top->fetchAll();
                            $topping_text = '';
                            foreach ($toppings as $t) {
                                $topping_text .= $t['name'] . ' (+' . number_format($t['price']) . 'đ) ';
                            }
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($item['product_name']) ?></td>
                            <td><?= htmlspecialchars($item['variant_name'] ?? '—') ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td><?= number_format($item['unit_price']) ?>đ</td>
                            <td><?= $topping_text ?: '—' ?></td>
                            <td><?= number_format($item['subtotal']) ?>đ</td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr><th colspan="5" class="text-right">Tổng tiền hàng:</th><td><?= number_format($order['total_amount'] ?? 0) ?>đ</td></tr>
                    <tr><th colspan="5" class="text-right">Giảm giá:</th><td><?= number_format($order['discount_amount'] ?? 0) ?>đ</td></tr>
                    <tr><th colspan="5" class="text-right">Phí ship:</th><td><?= number_format($order['shipping_fee'] ?? 0) ?>đ</td></tr>
                    <tr><th colspan="5" class="text-right">Thành tiền:</th><td><strong><?= number_format($order['final_amount'] ?? 0) ?>đ</strong></td></tr>
                </tfoot>
            </table>
        </div>
    </div>
<?php
    exit;
}

// ================== HIỂN THỊ DANH SÁCH ĐƠN HÀNG ==================
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

$where = "WHERE 1=1";
$params = [];
if ($search) {
    $where .= " AND order_code LIKE ?";
    $params[] = "%$search%";
}
if ($status_filter) {
    $where .= " AND status = ?";
    $params[] = $status_filter;
}

$total = $pdo->prepare("SELECT COUNT(*) FROM orders $where");
$total->execute($params);
$totalRecords = $total->fetchColumn();
$totalPages = ceil($totalRecords / $limit);

$sql = "SELECT o.*, c.full_name, c.phone, p.payment_status
        FROM orders o
        LEFT JOIN customers c ON o.customer_id = c.id
        LEFT JOIN payments p ON o.id = p.order_id
        $where
        ORDER BY o.id DESC
        LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

$statuses = [
    'pending' => 'Chờ xác nhận',
    'confirmed' => 'Đã xác nhận',
    'preparing' => 'Đang chuẩn bị',
    'delivering' => 'Đang giao',
    'completed' => 'Hoàn thành',
    'cancelled' => 'Đã hủy'
];
$payment_statuses = [
    'pending' => 'Chưa thanh toán',
    'paid' => 'Đã thanh toán',
    'failed' => 'Thất bại',
    'refunded' => 'Hoàn tiền'
];
?>

<div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
        <?php require_once 'includes/topbar.php'; ?>
        <div class="container-fluid">
            <h1 class="h3 mb-4 text-gray-800">Quản lý đơn hàng</h1>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <form method="GET" class="form-inline">
                        <input type="text" name="search" class="form-control mr-2" placeholder="Mã đơn" value="<?= htmlspecialchars($search) ?>">
                        <select name="status" class="form-control mr-2">
                            <option value="">-- Trạng thái --</option>
                            <?php foreach ($statuses as $val => $label): ?>
                                <option value="<?= $val ?>" <?= $status_filter == $val ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-primary">Lọc</button>
                        <a href="orders.php" class="btn btn-secondary ml-2">Reset</a>
                    </form>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Danh sách đơn hàng</h6></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr><th>ID</th><th>Mã đơn</th><th>Khách hàng</th><th>SĐT</th><th>Tổng tiền</th><th>Thanh toán</th><th>Trạng thái</th><th>Ngày tạo</th><th>Thao tác</th></tr>
                            </thead>
                            <tbody>
                                <?php if (count($orders) == 0): ?>
                                    <tr><td colspan="9" class="text-center">Không có đơn hàng nào</td></tr>
                                <?php else: ?>
                                    <?php foreach ($orders as $o): ?>
                                        <?php
                                        $payment_badge = match($o['payment_status'] ?? 'pending') {
                                            'paid' => 'success',
                                            'pending' => 'warning',
                                            'failed' => 'danger',
                                            'refunded' => 'secondary',
                                            default => 'secondary'
                                        };
                                        $status_badge = match($o['status'] ?? 'pending') {
                                            'pending' => 'warning',
                                            'confirmed' => 'info',
                                            'preparing' => 'primary',
                                            'delivering' => 'dark',
                                            'completed' => 'success',
                                            'cancelled' => 'danger',
                                            default => 'secondary'
                                        };
                                        ?>
                                        <tr>
                                            <td><?= $o['id'] ?></td>
                                            <td><?= htmlspecialchars($o['order_code']) ?></td>
                                            <td><?= htmlspecialchars($o['full_name'] ?? 'Khách lẻ') ?></td>
                                            <td><?= htmlspecialchars($o['phone'] ?? '') ?></td>
                                            <td><?= number_format($o['final_amount'] ?? 0, 0, ',', '.') ?>đ</td>
                                            <td><span class="badge badge-<?= $payment_badge ?>"><?= $payment_statuses[$o['payment_status'] ?? 'pending'] ?></span></td>
                                            <td><span class="badge badge-<?= $status_badge ?>"><?= $statuses[$o['status'] ?? 'pending'] ?></span></td>
                                            <td><?= date('d/m/Y H:i', strtotime($o['created_at'] ?? 'now')) ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-info btn-view-detail" data-id="<?= $o['id'] ?>"><i class="fas fa-eye"></i> Xem</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if ($totalPages > 1): ?>
                    <nav><ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= $status_filter ?>"><?= $i ?></a></li>
                        <?php endfor; ?>
                    </ul></nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="detailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chi tiết đơn hàng</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="detailContent"><p class="text-center">Đang tải...</p></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('.btn-view-detail').click(function() {
        var id = $(this).data('id');
        $('#detailModal').modal('show');
        $('#detailContent').html('<p class="text-center">Đang tải...</p>');
        $.get('orders.php', { ajax: 'detail', id: id }, function(data) {
            $('#detailContent').html(data);
        }).fail(function() {
            $('#detailContent').html('<p class="text-danger text-center">Lỗi tải dữ liệu.</p>');
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; require_once 'includes/scripts.php'; ?>