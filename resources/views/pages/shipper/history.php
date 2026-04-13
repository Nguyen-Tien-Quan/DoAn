<div class="page-header"><h2>Lịch sử giao hàng</h2></div>
<?php if (empty($history)): ?>
    <div class="empty-state"><i class="fas fa-history"></i><p>Chưa có đơn hàng nào hoàn thành.</p></div>
<?php else: ?>
    <div class="order-list">
        <?php foreach ($history as $order): ?>
            <div class="order-card">
                <div class="card-header">
                    <span class="order-code">#<?= $order['order_code'] ?></span>
                    <span class="order-status <?= getStatusClass($order['delivery_status']) ?>"><?= getDeliveryStatusText($order['delivery_status']) ?></span>
                </div>
                <div class="card-body">
                    <div class="customer-info"><i class="fas fa-user"></i><span><?= htmlspecialchars($order['customer_name'] ?? 'Khách lẻ') ?></span></div>
                    <div class="order-meta">
                        <div class="meta-item"><i class="fas fa-calendar"></i><span>Giao: <?= date('H:i d/m/Y', strtotime($order['completed_at'] ?? $order['updated_at'])) ?></span></div>
                        <div class="meta-item"><i class="fas fa-money-bill-wave"></i><span><?= formatMoney($order['final_amount']) ?></span></div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="?action=order-detail&id=<?= $order['id'] ?>" class="btn btn-outline">Xem chi tiết</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
