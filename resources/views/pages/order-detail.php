<?php
$order = $order ?? [];
?>

<style>
:root{
    --primary:#ee4d2d;
    --bg:#f5f5f5;
    --card:#fff;
    --text:#222;
    --muted:#888;
    --border:#eee;
    --shadow:0 8px 24px rgba(0,0,0,0.06);
}

.order-detail{
    background:var(--bg);
    padding:28px 0;
    font-family:system-ui;
}

/* LAYOUT */
.order-layout{
    display:grid;
    grid-template-columns:1.7fr 1fr;
    gap:20px;
    align-items:start;
}

/* CARD */
.od-card{
    background:var(--card);
    border-radius:14px;
    padding:16px;
    box-shadow:var(--shadow);
    border:1px solid var(--border);
    margin-bottom:16px;
}

/* HEADER */
.od-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:16px;
}

.od-header h2{
    font-size:18px;
    margin:0;
}

.od-date{
    font-size:12px;
    color:var(--muted);
    margin-top:4px;
}

.btn-back{
    color:var(--primary);
    text-decoration:none;
    font-weight:600;
    font-size:13px;
}

/* ITEMS */
.od-item{
    display:flex;
    gap:12px;
    padding:12px;
    border-radius:12px;
    background:#fafafa;
    margin-bottom:10px;
    transition:.2s;
}

.od-item:hover{
    transform:translateY(-2px);
    background:#f3f3f3;
}

.od-item img{
    width:60px;
    height:60px;
    border-radius:10px;
    object-fit:cover;
}

.od-item .info{flex:1;}

.od-item .name{
    font-weight:600;
    font-size:14px;
}

.od-item .qty{
    font-size:12px;
    color:var(--muted);
    margin-top:4px;
}

.od-item .price{
    color:var(--primary);
    font-weight:700;
}

/* TOTAL */
.od-total{
    border-top:1px dashed var(--border);
    margin-top:10px;
    padding-top:10px;
}

.od-total div{
    display:flex;
    justify-content:space-between;
    font-size:14px;
    margin:6px 0;
}

.od-total .final{
    font-size:18px;
    font-weight:800;
    color:var(--primary);
}

/* STATUS */
.od-status{
    display:inline-flex;
    padding:6px 12px;
    border-radius:999px;
    font-size:12px;
    font-weight:600;
    margin-bottom:10px;
}

.od-status.pending{background:#fff1f0;color:#a8071a;}
.od-status.confirmed{background:#e6f4ff;color:#0958d9;}
.od-status.preparing{background:#f9f0ff;color:#531dab;}
.od-status.delivering{background:#e6fffb;color:#08979c;}
.od-status.completed{background:#f6ffed;color:#237804;}
.od-status.cancelled{background:#fff2f0;color:#cf1322;}

/* PROGRESS SHOPEE STYLE */
.progress{
    position:relative;
    display:flex;
    justify-content:space-between;
    margin-top:14px;
}

.progress::before{
    content:"";
    position:absolute;
    top:8px;
    left:0;
    right:0;
    height:4px;
    background:#eee;
    border-radius:999px;
}

.progress-bar{
    position:absolute;
    top:8px;
    left:0;
    height:4px;
    background:var(--primary);
    border-radius:999px;
    transition:0.4s ease;
}

.step{
    text-align:center;
    flex:1;
    position:relative;
    z-index:2;
}

.step .dot{
    width:16px;
    height:16px;
    border-radius:50%;
    background:#ddd;
    margin:auto;
    transition:.3s;
}

.step.active .dot{
    background:var(--primary);
    box-shadow:0 0 0 4px rgba(238,77,45,0.15);
}

.step span{
    font-size:10px;
    color:#777;
    display:block;
    margin-top:6px;
}

/* RIGHT SIDEBAR STICKY */
.order-right{
    position:sticky;
    top:20px;
}

/* TIMELINE */
.timeline{
    padding-left:18px;
    position:relative;
}

.timeline::before{
    content:"";
    position:absolute;
    left:6px;
    top:0;
    bottom:0;
    width:2px;
    background:#eee;
}

.timeline-item{
    display:flex;
    gap:10px;
    margin-bottom:12px;
}

.timeline-item .dot{
    width:9px;
    height:9px;
    border-radius:50%;
    background:var(--primary);
    margin-top:5px;
}

.timeline-item .title{
    font-size:13px;
    font-weight:600;
}

.timeline-item .time{
    font-size:11px;
    color:var(--muted);
}

/* BUTTON */
.btn-reorder{
    display:block;
    text-align:center;
    padding:11px;
    background:var(--primary);
    color:#fff;
    border-radius:10px;
    font-weight:600;
    text-decoration:none;
    margin-top:12px;
    transition:.2s;
}

.btn-reorder:hover{
    opacity:.9;
}

/* RESPONSIVE */
@media(max-width:900px){
    .order-layout{
        grid-template-columns:1fr;
    }
    .order-right{
        position:static;
    }
}
</style>

<main class="order-detail">
<div class="container">

    <!-- HEADER -->
    <div class="od-header">
        <div>
            <h2>Đơn hàng #<?= $order['order_code'] ?? $order['id'] ?></h2>
            <div class="od-date">
                <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
            </div>
        </div>
        <a class="btn-back" href="index.php?url=orders">← Quay lại</a>
    </div>

    <div class="order-layout">

        <!-- LEFT -->
        <div>

            <div class="od-card">
                <h3>Sản phẩm</h3>

                <?php foreach ($items ?? [] as $item): ?>
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
                    <div><span>Tạm tính</span><span><?= number_format($order['total_amount']) ?>đ</span></div>
                    <div><span>Giảm giá</span><span>-<?= number_format($order['discount_amount'] ?? 0) ?>đ</span></div>
                    <div><span>Ship</span><span><?= number_format($order['shipping_fee'] ?? 0) ?>đ</span></div>

                    <div class="final">
                        <span>Tổng</span>
                        <span><?= number_format($order['final_amount'] ?? $order['total_amount']) ?>đ</span>
                    </div>
                </div>

                <a class="btn-reorder" href="index.php?url=reorder&id=<?= $order['id'] ?>">
                    🔁 Mua lại
                </a>
            </div>

        </div>

        <!-- RIGHT -->
        <div class="order-right">

            <div class="od-card">
                <h3>Trạng thái</h3>

                <?php
                $map = [
                    'pending'=>'Chờ xác nhận',
                    'confirmed'=>'Đã xác nhận',
                    'preparing'=>'Chuẩn bị',
                    'ready_for_delivery'=>'Sẵn sàng giao',
                    'delivering'=>'Đang giao',
                    'completed'=>'Hoàn thành',
                    'cancelled'=>'Đã hủy'
                ];

                $steps = ['pending','confirmed','preparing','ready_for_delivery','delivering','completed'];
                $currentIndex = array_search($order['status'], $steps);
                $percent = ($currentIndex/(count($steps)-1))*100;
                ?>

                <div class="od-status <?= $order['status'] ?>">
                    <?= $map[$order['status']] ?>
                </div>

                <div class="progress">
                    <div class="progress-bar" style="width:<?= $percent ?>%"></div>

                    <?php foreach ($steps as $i => $step): ?>
                        <div class="step <?= $i <= $currentIndex ? 'active' : '' ?>">
                            <div class="dot"></div>
                            <span><?= $map[$step] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="od-card">
                <h3>Giao hàng</h3>
                <p><b><?= $order['receiver_name'] ?></b></p>
                <p><?= $order['receiver_phone'] ?></p>
                <p><?= $order['delivery_address'] ?></p>
            </div>

            <div class="od-card">
                <h3>Thanh toán</h3>
                <p><?= strtoupper($order['payment_method'] ?? 'COD') ?></p>
            </div>

            <?php if (!empty($statusHistory)): ?>
            <div class="od-card">
                <h3>Lịch sử</h3>

                <div class="timeline">
                    <?php foreach ($statusHistory as $h): ?>
                        <div class="timeline-item">
                            <div class="dot"></div>
                            <div>
                                <div class="title"><?= $map[$h['status']] ?? $h['status'] ?></div>
                                <div class="time"><?= date('d/m/Y H:i', strtotime($h['created_at'])) ?></div>
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
