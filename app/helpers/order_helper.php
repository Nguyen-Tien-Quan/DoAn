<?php
function renderOrder($order){
    ob_start();

    $status = $order['status'] ?? 'pending';
    $order_code = $order['order_code'] ?? '';
    $created_at = $order['created_at'] ?? '';
    $final_amount = $order['final_amount'] ?? 0;
    $id = $order['id'] ?? 0;

    // STATUS TEXT
    $map = [
        'pending' => 'Đang xử lý',
        'confirmed' => 'Đã xác nhận',
        'preparing' => 'Đang chuẩn bị',
        'ready_for_delivery' => 'Chờ giao',
        'delivering' => 'Đang giao',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Đã hủy',
    ];

    $displayStatus = $map[$status] ?? $status;

    // STEP ORDER
    $steps = ['pending','confirmed','preparing','ready_for_delivery','delivering','completed'];
    $stepLabels = [
    'pending' => 'Đang xử lý',
    'confirmed' => 'Đã xác nhận',
    'preparing' => 'Chuẩn bị',
    'ready_for_delivery' => 'Chờ giao',
    'delivering' => 'Đang giao',
    'completed' => 'Hoàn thành'
];

    $currentIndex = array_search($status, $steps);

    // Nếu không nằm trong steps (ví dụ cancelled)
    $isCancelled = ($status === 'cancelled');

    if ($currentIndex === false) {
        $currentIndex = 0;
    }

    // % progress
    $percent = ($currentIndex/(count($steps)-1))*100;
?>

<div class="order-card">

    <div class="order-top">
        <div class="order-code">#<?= htmlspecialchars($order_code) ?></div>
        <div class="order-date"><?= date('d/m/Y H:i', strtotime($created_at)) ?></div>
    </div>

    <div class="order-body">
        <div class="order-price"><?= number_format($final_amount, 0, ',', '.') ?>đ</div>
        <!-- <div class="order-status <?= $status ?>"><?= $displayStatus ?></div> -->

        <div class="order-actions">
             <a href="index.php?url=order-detail&id=<?= $id ?>" class="btn-view">Chi tiết</a>
            <?php if (in_array($status, ['pending','confirmed'])): ?>
                <button class="btn-cancel" onclick="openCancelModal(<?= $id ?>)">Hủy</button>
            <?php endif; ?>
        </div>
    </div>

    <!-- PROGRESS BAR -->
    <?php if (!$isCancelled): ?>
    <div class="progress-bar">
        <div class="progress-line">
            <div class="progress-fill" style="width: <?= $percent ?>%"></div>
        </div>

        <div class="progress-steps">
        <?php foreach ($steps as $index => $step): ?>
            <div class="step <?= $index <= $currentIndex ? 'active' : '' ?>">

                <!-- LABEL -->
                <?php if ($index == $currentIndex): ?>
                    <div class="step-label">
                        <?= $stepLabels[$step] ?>
                    </div>
                <?php endif; ?>

                <!-- DOT -->
                <div class="dot"></div>

            </div>
        <?php endforeach; ?>
    </div>


    </div>
    <?php else: ?>
        <!-- Nếu đã hủy -->
        <div style="margin-top:15px; color:#ff4d4f; font-size:13px;">
            Đơn hàng đã bị hủy
        </div>
    <?php endif; ?>

</div>

<?php
    return ob_get_clean();
}
