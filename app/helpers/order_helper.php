<?php

function renderOrder($order){
    ob_start();

    // Lấy giá trị an toàn
    $status = $order['status'] ?? 'pending';
    $shipper_id = $order['shipper_id'] ?? null;
    $delivery_status = $order['delivery_status'] ?? 'pending';
    $order_code = $order['order_code'] ?? '';
    $created_at = $order['created_at'] ?? '';
    $final_amount = $order['final_amount'] ?? 0;
    $id = $order['id'] ?? 0;

    // Xác định text và class
    $displayStatus = '';
    $statusClass = '';

    if ($status == 'cancelled') {
        $displayStatus = '❌ Đã hủy';
        $statusClass = 'cancelled';
    } elseif ($status == 'completed') {
        $displayStatus = '✅ Hoàn thành';
        $statusClass = 'completed';
    } elseif ($delivery_status == 'shipping') {
        $displayStatus = '🚚 Đang giao';
        $statusClass = 'delivering';
    } elseif ($status == 'confirmed' && !empty($shipper_id)) {
        $displayStatus = '🛵 Đã có tài xế nhận';
        $statusClass = 'accepted';
    } elseif ($status == 'confirmed') {
        $displayStatus = '🔵 Đã xác nhận';
        $statusClass = 'confirmed';
    } elseif ($status == 'pending') {
        $displayStatus = '🟡 Đang xử lý';
        $statusClass = 'pending';
    } elseif ($status == 'preparing') {
        $displayStatus = '🟠 Đang chuẩn bị';
        $statusClass = 'preparing';
    } elseif ($status == 'delivering') {
        $displayStatus = '🚚 Đang giao';
        $statusClass = 'delivering';
    } else {
        $displayStatus = $status;
        $statusClass = $status;
    }

    // Timeline
    $steps = ['pending','confirmed','preparing','delivering','completed'];
    $currentIndex = array_search($status, $steps);
    if ($currentIndex === false) $currentIndex = 0;
?>
<div class="order-card">
    <div class="order-top">
        <div class="order-code">#<?= htmlspecialchars($order_code) ?></div>
        <div class="order-date"><?= date('d/m/Y H:i', strtotime($created_at)) ?></div>
    </div>
    <div class="order-body">
        <div class="order-price"><?= number_format($final_amount, 0, ',', '.') ?>đ</div>
        <div class="order-status <?= $statusClass ?>"><?= $displayStatus ?></div>
        <div class="order-actions">
            <a href="index.php?url=order-detail&id=<?= $id ?>" class="btn-view">Chi tiết</a>
            <?php if (in_array($status, ['pending','confirmed'])): ?>
                <button class="btn-cancel" onclick="openCancelModal(<?= $id ?>)">Hủy</button>
            <?php endif; ?>
        </div>
    </div>
    <div class="order-progress">
        <?php foreach ($steps as $index => $step): ?>
            <div class="step <?= $index <= $currentIndex ? 'active' : '' ?>">
                <div class="step-dot"></div>
                <span><?= $step ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php
    return ob_get_clean();
}
