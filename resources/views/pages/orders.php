<style>
    .order-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:30px;
}

.btn-back{
    text-decoration:none;
    color:#555;
}

.order-list{
    display:flex;
    flex-direction:column;
    gap:20px;
}

.order-card{
    background:#fff;
    border-radius:16px;
    padding:20px;
    box-shadow:0 8px 25px rgba(0,0,0,0.06);
    transition:.25s;
}

.order-card:hover{
    transform:translateY(-4px);
}

.order-top{
    display:flex;
    justify-content:space-between;
    margin-bottom:10px;
    font-size:14px;
    color:#888;
}

.order-body{
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.order-price{
    font-size:20px;
    font-weight:700;
    color:#ff4d4f;
}

.order-status{
    padding:6px 14px;
    border-radius:999px;
    font-size:13px;
}

.order-status.warning{ background:#fff3cd; color:#856404; }
.order-status.success{ background:#d4edda; color:#155724; }
.order-status.danger{ background:#f8d7da; color:#721c24; }
.order-status.primary{ background:#cce5ff; color:#004085; }

.order-actions{
    display:flex;
    gap:10px;
}

.btn-view{
    padding:6px 14px;
    border:1px solid #1890ff;
    border-radius:999px;
    color:#1890ff;
    text-decoration:none;
}

.btn-cancel{
    padding:6px 14px;
    border:none;
    background:#ff4d4f;
    color:#fff;
    border-radius:999px;
    cursor:pointer;
}
</style>
<main class="order-page">
    <div class="container">

        <!-- HEADER -->
        <div class="order-header">
            <h1>📦 Đơn hàng của tôi</h1>
            <a href="<?= $base ?>" class="btn-back">← Tiếp tục mua sắm</a>
        </div>

        <?php if (empty($orders)): ?>
            <div class="order-empty">
                <img src="<?= $base ?>assets/img/empty-order.png">
                <h3>Chưa có đơn hàng nào</h3>
                <p>Hãy mua sắm ngay 🚀</p>
                <a href="<?= $base ?>" class="btn btn--primary">Mua ngay</a>
            </div>
        <?php else: ?>

            <div class="order-list">
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">

                        <!-- TOP -->
                        <div class="order-top">
                            <div>
                                <span class="label">Mã đơn</span>
                                <strong><?= $order['order_code'] ?></strong>
                            </div>

                            <div class="date">
                                <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                            </div>
                        </div>

                        <!-- BODY -->
                        <div class="order-body">
                            <div class="order-price">
                                <?= number_format($order['final_amount']) ?>đ
                            </div>

                            <?php
                                $statusMap = [
                                    'pending' => 'warning',
                                    'confirmed' => 'info',
                                    'preparing' => 'primary',
                                    'delivering' => 'secondary',
                                    'completed' => 'success',
                                    'cancelled' => 'danger'
                                ];
                                $cls = $statusMap[$order['status']] ?? 'secondary';
                            ?>

                            <div class="order-status <?= $cls ?>">
                                <?= $order['status'] ?>
                            </div>

                            <div class="order-actions">
                                <a href="index.php?url=order-detail&id=<?= $order['id'] ?>" class="btn-view">
                                    Chi tiết
                                </a>

                                <?php if (in_array($order['status'], ['pending','confirmed'])): ?>
                                    <button class="btn-cancel"
                                        onclick="openCancelModal(<?= $order['id'] ?>)">
                                        Hủy đơn
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>
    </div>
</main>
<script>
    let currentOrderId = null;

function openCancelModal(id){
    currentOrderId = id;
    document.getElementById('cancelModal').style.display = 'flex';
}

function closeModal(){
    document.getElementById('cancelModal').style.display = 'none';
}

function confirmCancel(){
    fetch('index.php?url=cancel-order', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'order_id=' + currentOrderId
    })
    .then(res=>res.json())
    .then(data=>{
        alert(data.message);
        location.reload();
    });
}
</script>
