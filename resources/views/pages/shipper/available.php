<?php
// Biến $orders từ controller (getAvailableOrders)
?>
<div class="page-header">
    <h2>Đơn hàng đang chờ shipper</h2>
    <div class="order-tabs">
        <a href="?action=available" class="tab-btn active">Tất cả</a>
    </div>
</div>

<?php if (empty($orders)): ?>
    <div class="empty-state">
        <i class="fas fa-box-open"></i>
        <p>Không có đơn hàng nào cần giao</p>
    </div>
<?php else: ?>
    <div class="order-list">
        <?php foreach ($orders as $order): ?>
            <div class="order-card">
                <div class="card-header">
                    <span class="order-code">#<?= htmlspecialchars($order['order_code']) ?></span>
                    <span class="order-status status-pending">Chờ nhận</span>
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
                    <button class="btn btn-primary" onclick="acceptOrder(<?= $order['id'] ?>)">Nhận giao</button>
                    <a href="?action=order-detail&id=<?= $order['id'] ?>" class="btn btn-outline">Chi tiết</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
