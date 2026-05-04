<?php

if (!isset($order) && isset($data['order'])) {
    $order = $data['order'];
}

$orderCode = $order['order_code'] ?? '';
$customerName = htmlspecialchars($order['customer_name'] ?? 'Khách lẻ');
$customerPhone = $order['customer_phone'] ?? '';
$customerAddress = htmlspecialchars($order['customer_address'] ?? $order['delivery_address'] ?? '');
$paymentMethod = $order['payment_method'] ?? '';
$finalAmount = $order['final_amount'] ?? 0;
$status = $order['delivery_status'] ?? '';
$createdAt = !empty($order['created_at'])
    ? date('H:i d/m/Y', strtotime($order['created_at']))
    : '';

?>
<div class="page-header">
    <h2>Chi tiết đơn hàng #<?= $orderCode ?></h2>
    <a href="javascript:history.back()" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Quay lại</a>
</div>

<div class="row" style="display: flex; gap: 2rem; flex-wrap: wrap;">
    <div class="col" style="flex: 2; min-width: 300px;">
        <div class="cart-info cart-info--shadow" style="background: white; border-radius: 1.2rem; padding: 2rem; margin-bottom: 2rem;">
            <h3 style="margin-bottom: 1.5rem;">Thông tin khách hàng</h3>
            <p><i class="fas fa-user"></i> <?= $customerName ?></p>
            <p><i class="fas fa-phone"></i> <a href="tel:<?= $customerPhone ?>"><?= $customerPhone ?></a></p>
            <p><i class="fas fa-map-marker-alt"></i> <?= $customerAddress ?></p>
            <div class="map-container" style="height:200px; background:#e2e8f0; border-radius:0.8rem; margin:1.5rem 0;">
                <!-- Có thể nhúng Google Maps iframe với địa chỉ -->
                <iframe width="100%" height="100%" frameborder="0" style="border:0" src="https://maps.google.com/maps?q=<?= urlencode($customerAddress) ?>&output=embed" allowfullscreen></iframe>
            </div>
            <div style="display: flex; gap: 1rem;">
                <a href="tel:<?= $customerPhone ?>" class="btn btn-primary"><i class="fas fa-phone"></i> Gọi khách</a>
                <a href="https://maps.google.com/?q=<?= urlencode($customerAddress) ?>" target="_blank" class="btn btn-outline"><i class="fas fa-map"></i> Chỉ đường</a>
            </div>
        </div>

        <div class="cart-info cart-info--shadow" style="background: white; border-radius: 1.2rem; padding: 2rem;">
            <h3 style="margin-bottom: 1.5rem;">Sản phẩm</h3>
            <?php foreach ($order['items'] as $item): ?>

                <div style="display: flex; justify-content: space-between; padding: 0.8rem 0; border-bottom: 1px solid #e2e8f0;">
                    <div>
                        <strong><?= $item['quantity'] ?>x</strong> <?= htmlspecialchars($item['product_name']) ?>
                        <?php if (!empty($item['variant_name'])): ?><small>(<?= $item['variant_name'] ?>)</small><?php endif; ?>
                        <?php if (!empty($item['toppings'])): ?>
                            <br><small style="color:#64748b;">Topping: <?= implode(', ', array_column($item['toppings'], 'topping_name')) ?></small>
                        <?php endif; ?>
                    </div>
                    <span><?= formatMoney($item['unit_price'] * $item['quantity'] + ($item['topping_price'] ?? 0)) ?></span>
                </div>
            <?php endforeach; ?>
            <div style="margin-top: 1.5rem; text-align: right;">
                <p>Tạm tính: <?= formatMoney($order['total_amount'] ?? 0) ?></p>
                <p>Giảm giá: -<?= formatMoney($order['discount_amount'] ?? 0) ?></p>
                <p>Phí ship: <?= formatMoney($order['shipping_fee'] ?? 0) ?></p>
                <h3>Tổng: <?= formatMoney($order['final_amount'] ?? 0) ?></h3>
            </div>
        </div>
    </div>

    <div class="col" style="flex: 1; min-width: 250px;">
        <div class="cart-info cart-info--shadow" style="background: white; border-radius: 1.2rem; padding: 2rem;">
            <h3>Thanh toán</h3>
            <p>Phương thức: <?= $paymentMethod == 'cash' ? 'COD (Tiền mặt)' : strtoupper($paymentMethod) ?></p>
            <p>Trạng thái: <?= $order['payment']['payment_status'] ?? 'pending' ?></p>
            <hr style="margin: 1.5rem 0;">
            <h3>Cập nhật trạng thái</h3>
            <?php $currentShipperId = $_SESSION['user']['id'] ?? 0; ?>
            <?php if ($order['shipper_id'] == $currentShipperId): ?>
                <?php if ($status == 'pending'): ?>
                    <button class="btn btn-success" style="width:100%; margin-bottom:1rem;" onclick="updateStatus(<?= $order['id'] ?>, 'shipping')">Bắt đầu giao</button>
                <?php elseif ($status == 'shipping'): ?>
                    <button class="btn btn-success" style="width:100%; margin-bottom:1rem;" onclick="updateStatus(<?= $order['id'] ?>, 'delivered')">Đã giao hàng</button>
                    <button class="btn btn-danger" style="width:100%;" onclick="updateStatus(<?= $order['id'] ?>, 'failed')">Báo lỗi</button>
                <?php endif; ?>
            <?php elseif (
    !$order['shipper_id']
    && $status == 'pending'
    && $order['status'] == 'ready_for_delivery'
): ?>
                <button class="btn btn-primary" style="width:100%;" onclick="acceptOrder(<?= $order['id'] ?>)">Nhận giao</button>
            <?php else: ?>
                <p>Đơn hàng đã được shipper khác nhận.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="/DoAn/DoAnTotNghiep/public/assets/js/shipper.js"></script>
