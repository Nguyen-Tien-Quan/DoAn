<style>
    .order-detail{
    background:#f6f7fb;
    padding:30px 0;
}

.od-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:25px;
}

.od-date{
    color:#888;
    font-size:13px;
}

.btn-back{
    text-decoration:none;
    color:#555;
}

.od-card{
    background:#fff;
    border-radius:16px;
    padding:20px;
    margin-bottom:20px;
    box-shadow:0 8px 25px rgba(0,0,0,0.05);
}

/* ITEM */
.od-item{
    display:flex;
    align-items:center;
    gap:15px;
    padding:12px 0;
    border-bottom:1px solid #eee;
}

.od-item img{
    width:60px;
    height:60px;
    border-radius:10px;
    object-fit:cover;
}

.od-item .info{
    flex:1;
}

.od-item .name{
    font-weight:600;
}

.od-item .qty{
    font-size:13px;
    color:#888;
}

.od-item .price{
    font-weight:600;
    color:#ff4d4f;
}

/* TOTAL */
.od-total{
    margin-top:15px;
}

.od-total div{
    display:flex;
    justify-content:space-between;
    margin:6px 0;
}

.od-total .final{
    font-size:18px;
    font-weight:700;
    color:#ff4d4f;
}

/* STATUS */
.od-status{
    padding:10px;
    border-radius:10px;
    background:#e6f7ff;
    text-align:center;
    font-weight:600;
}

/* TIMELINE */
.timeline{
    position:relative;
    padding-left:20px;
}

.timeline::before{
    content:"";
    position:absolute;
    left:6px;
    top:0;
    bottom:0;
    width:2px;
    background:#ddd;
}

.timeline-item{
    display:flex;
    gap:10px;
    margin-bottom:15px;
}

.timeline-item .dot{
    width:12px;
    height:12px;
    background:#1890ff;
    border-radius:50%;
    margin-top:4px;
}

.timeline-item .title{
    font-weight:600;
}

.timeline-item .time{
    font-size:12px;
    color:#888;
}

.timeline-item .note{
    font-size:13px;
    color:#555;
}
</style>
<main class="order-detail">
    <div class="container">

        <!-- HEADER -->
        <div class="od-header">
            <div>
                <h2>🧾 Đơn hàng #<?= $order['order_code'] ?? $order['id'] ?></h2>
                <span class="od-date">
                    <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                </span>
            </div>
            <a href="index.php?url=orders" class="btn-back">← Quay lại</a>
        </div>

        <div class="row">

            <!-- LEFT -->
            <div class="col-lg-8">

                <!-- ITEMS -->
                <div class="od-card">
                    <h3>Sản phẩm</h3>

                    <?php foreach ($items as $item): ?>
                        <div class="od-item">
                            <img src="<?= $base ?>assets/img/product/<?= $item['image'] ?>">

                            <div class="info">
                                <div class="name"><?= $item['product_name'] ?></div>
                                <div class="qty">x<?= $item['quantity'] ?></div>
                            </div>

                            <div class="price">
                                <?= number_format($item['subtotal']) ?>đ
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="od-total">
                        <div>
                            <span>Tạm tính</span>
                            <span><?= number_format($order['total_amount']) ?>đ</span>
                        </div>
                        <div>
                            <span>Giảm giá</span>
                            <span>-<?= number_format($order['discount_amount'] ?? 0) ?>đ</span>
                        </div>
                        <div>
                            <span>Ship</span>
                            <span><?= number_format($order['shipping_fee'] ?? 0) ?>đ</span>
                        </div>

                        <div class="final">
                            <span>Tổng</span>
                            <span><?= number_format($order['final_amount'] ?? $order['total_amount']) ?>đ</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- RIGHT -->
            <div class="col-lg-4">

                <!-- STATUS -->
                <div class="od-card">
                    <h3>Trạng thái</h3>

                    <?php
                    $map = [
                        'pending'=>'Chờ xác nhận',
                        'confirmed'=>'Đã xác nhận',
                        'preparing'=>'Đang chuẩn bị',
                        'delivering'=>'Đang giao',
                        'completed'=>'Hoàn thành',
                        'cancelled'=>'Đã hủy'
                    ];
                    ?>

                    <div class="od-status">
                        <?= $map[$order['status']] ?? $order['status'] ?>
                    </div>
                </div>

                <!-- ADDRESS -->
                <div class="od-card">
                    <h3>Giao hàng</h3>
                    <p><b><?= $order['receiver_name'] ?></b></p>
                    <p><?= $order['receiver_phone'] ?></p>
                    <p><?= $order['delivery_address'] ?></p>
                </div>

                <!-- PAYMENT -->
                <div class="od-card">
                    <h3>Thanh toán</h3>
                    <p><?= strtoupper($order['payment_method'] ?? 'COD') ?></p>
                </div>

                <!-- TIMELINE -->
                <?php if (!empty($statusHistory)): ?>
                    <div class="od-card">
                        <h3>Lịch sử</h3>

                        <div class="timeline">
                            <?php foreach ($statusHistory as $h): ?>
                                <div class="timeline-item">
                                    <div class="dot"></div>
                                    <div class="content">
                                        <div class="title">
                                            <?= $map[$h['status']] ?? $h['status'] ?>
                                        </div>
                                        <div class="time">
                                            <?= date('d/m/Y H:i', strtotime($h['created_at'])) ?>
                                        </div>
                                        <?php if ($h['note']): ?>
                                            <div class="note"><?= $h['note'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                    </div>
                <?php endif; ?>

            </div>

        </div>
    </div>
</main>
