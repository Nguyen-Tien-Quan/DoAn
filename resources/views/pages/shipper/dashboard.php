<?php
// Biến từ controller: $stats, $orders
$currentShipperId = $_SESSION['user']['id'] ?? 0;
?>
<div class="page-header">
    <h2>Đơn hàng của tôi</h2>
    <div class="order-tabs">
        <a href="?action=dashboard" class="tab-btn <?= !isset($_GET['status']) ? 'active' : '' ?>">Tất cả</a>
        <a href="?action=dashboard&status=pending" class="tab-btn <?= ($_GET['status'] ?? '') == 'pending' ? 'active' : '' ?>">Chờ giao</a>
        <a href="?action=dashboard&status=shipping" class="tab-btn <?= ($_GET['status'] ?? '') == 'shipping' ? 'active' : '' ?>">Đang giao</a>
        <a href="?action=dashboard&status=delivered" class="tab-btn <?= ($_GET['status'] ?? '') == 'delivered' ? 'active' : '' ?>">Đã giao</a>
    </div>
</div>

<!-- Thống kê nhanh -->
<div class="stats-grid">
    <div class="stat-card">
        <i class="fas fa-clock"></i>
        <div class="stat-info">
            <span class="stat-value"><?= $stats['pending'] ?? 0 ?></span>
            <span class="stat-label">Chờ giao</span>
        </div>
    </div>
    <div class="stat-card">
        <i class="fas fa-truck"></i>
        <div class="stat-info">
            <span class="stat-value"><?= $stats['shipping'] ?? 0 ?></span>
            <span class="stat-label">Đang giao</span>
        </div>
    </div>
    <div class="stat-card">
        <i class="fas fa-check-circle"></i>
        <div class="stat-info">
            <span class="stat-value"><?= $stats['delivered_today'] ?? 0 ?></span>
            <span class="stat-label">Đã giao hôm nay</span>
        </div>
    </div>
    <div class="stat-card">
        <i class="fas fa-money-bill-wave"></i>
        <div class="stat-info">
            <span class="stat-value"><?= formatMoney($stats['total_cash_today'] ?? 0) ?></span>
            <span class="stat-label">COD hôm nay</span>
        </div>
    </div>
</div>

<!-- Danh sách đơn hàng -->
<div class="order-list">
    <?php if (empty($orders)): ?>
        <div class="empty-state">
            <i class="fas fa-box-open"></i>
            <p>Không có đơn hàng nào</p>
        </div>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <?php
                $isMyOrder = ($order['shipper_id'] == $currentShipperId);
                $status = $order['delivery_status'];
                // Text hiển thị trạng thái
                if ($status == 'pending') {
                    $displayStatus = $isMyOrder ? 'Đã nhận' : 'Chờ nhận';
                } else {
                    $displayStatus = getDeliveryStatusText($status);
                }
                $statusClass = getStatusClass($status);
            ?>
            <div class="order-card">
                <div class="card-header">
                    <span class="order-code">#<?= htmlspecialchars($order['order_code']) ?></span>
                    <span class="order-status <?= $statusClass ?>"><?= $displayStatus ?></span>
                </div>
                <div class="card-body">
                    <div class="customer-info">
                        <i class="fas fa-user"></i>
                        <span><?= htmlspecialchars($order['customer_name'] ?? 'Khách lẻ') ?></span>
                        <span class="phone"><?= htmlspecialchars($order['customer_phone'] ?? '') ?></span>
                    </div>
                    <div class="address-info">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?= htmlspecialchars($order['customer_address'] ?? $order['delivery_address'] ?? '') ?></span>
                    </div>
                    <div class="order-meta">
                        <div class="meta-item">
                            <i class="fas fa-clock"></i>
                            <span>Đặt: <?= date('H:i d/m/Y', strtotime($order['created_at'])) ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-money-bill-wave"></i>
                            <span>
                                <?php if ($order['payment_method'] == 'cash'): ?>
                                    COD: <?= formatMoney($order['final_amount']) ?>
                                <?php else: ?>
                                    Đã TT: <?= formatMoney($order['final_amount']) ?>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <?php if (!$isMyOrder && $status == 'pending'): ?>
                        <!-- Đơn chưa có shipper -->
                        <button class="btn btn-primary" onclick="acceptOrder(<?= $order['id'] ?>)">Nhận giao</button>
                    <?php elseif ($isMyOrder && $status == 'pending'): ?>
                        <!-- Đã nhận, chờ bắt đầu giao -->
                        <button class="btn btn-success" onclick="updateStatus(<?= $order['id'] ?>, 'shipping')">Bắt đầu giao</button>
                    <?php elseif ($isMyOrder && $status == 'shipping'): ?>
                        <!-- Đang giao -->
                        <button class="btn btn-success" onclick="updateStatus(<?= $order['id'] ?>, 'delivered')">Đã giao</button>
                        <button class="btn btn-danger" onclick="updateStatus(<?= $order['id'] ?>, 'failed')">Báo lỗi</button>
                    <?php endif; ?>
                    <a href="?action=order-detail&id=<?= $order['id'] ?>" class="btn btn-outline">Chi tiết</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<!-- Script không cần nữa vì hàm acceptOrder/updateStatus đã có trong layout -->
