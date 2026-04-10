<?php

function renderOrder($order){
    ob_start();

    // Map trạng thái tiếng Việt
    $statusText = match($order['status']){
        'pending' => '🟡 Đang xử lý',
        'confirmed' => '🔵 Đã xác nhận',
        'preparing' => '🟠 Đang chuẩn bị',
        'delivering' => '🚚 Đang giao',
        'completed' => '✅ Hoàn thành',
        'cancelled' => '❌ Đã hủy',
        default => $order['status']
    };

    // Timeline
    $steps = ['pending','confirmed','preparing','delivering','completed'];
    $currentIndex = array_search($order['status'], $steps);
?>

<div class="order-card">

    <!-- TOP -->
    <div class="order-top">
        <div class="order-code">#<?= $order['order_code'] ?></div>
        <div class="order-date"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></div>
    </div>

    <!-- BODY -->
    <div class="order-body">
        <div class="order-price">
            <?= number_format($order['final_amount']) ?>đ
        </div>

        <div class="order-status <?= $order['status'] ?>">
            <?= $statusText ?>
        </div>

        <div class="order-actions">
            <a href="index.php?url=order-detail&id=<?= $order['id'] ?>" class="btn-view">
                Chi tiết
            </a>

            <?php if (in_array($order['status'], ['pending','confirmed'])): ?>
                <button class="btn-cancel"
                    onclick="openCancelModal(<?= $order['id'] ?>)">
                    Hủy
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- PROGRESS -->
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
