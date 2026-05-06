<?php
global $orders, $page, $totalPages;
if (!function_exists('renderOrder')) {
    require_once __DIR__ . '/../../../app/helpers/order_helper.php';
}
?>
<style>
/* HEADER */
.order-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
}

.btn-back{
    text-decoration:none;
    color:#555;
    font-size:14px;
}

/* FILTER TAB */
.order-filter{
    display:flex;
    gap:10px;
    margin-bottom:20px;
}

.filter-btn{
    padding:8px 16px;
    border-radius:999px;
    border:1px solid #ddd;
    background:#fff;
    cursor:pointer;
    font-size:13px;
}

.filter-btn.active{
    background:#ff4d4f;
    color:#fff;
    border-color:#ff4d4f;
}

/* LIST */
.order-list{
    display:flex;
    flex-direction:column;
    gap:20px;
}

/* CARD */
.order-card{
    background:#fff;
    border-radius:16px;
    padding:20px;
    box-shadow:0 10px 30px rgba(0,0,0,0.05);
    transition:.3s;
    border:1px solid #f1f1f1;
}

.order-card:hover{
    transform:translateY(-5px);
}

/* TOP */
.order-top{
    display:flex;
    justify-content:space-between;
    font-size:14px;
    margin-bottom:15px;
}

.order-code{ font-weight:600; }
.order-date{ color:#888; }

/* BODY */
.order-body{
    display:flex;
    justify-content:space-between;
    align-items:center;
    flex-wrap:wrap;
    gap:10px;
    margin-bottom: 40px;
}

.order-price{
    font-size:22px;
    font-weight:700;
    color:#ff4d4f;
}

/* STATUS */
.order-status{
    padding:6px 14px;
    border-radius:999px;
    font-size:13px;
    font-weight:500;
}

.order-status.pending{ background:#fff3cd; color:#856404; }
.order-status.confirmed{ background:#d1ecf1; color:#0c5460; }
.order-status.preparing{ background:#cce5ff; color:#004085; }
.order-status.delivering{ background:#e2e3e5; color:#383d41; }
.order-status.completed{ background:#d4edda; color:#155724; }
.order-status.cancelled{ background:#f8d7da; color:#721c24; }
.order-status.ready_for_delivery {
    background: #fff0f6;
    color: #c2185b;
}

/* ACTION */
.order-actions{ display:flex; gap:10px; }

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

/* LOADING */
.loading{
    text-align:center;
    padding:30px;
    color:#888;
}

/* PAGINATION */
.pagination{
    display:flex;
    justify-content:center;
    gap:8px;
    margin-top:20px;
}

.page-btn{
    padding:6px 12px;
    border:1px solid #ddd;
    background:#fff;
    cursor:pointer;
    border-radius:6px;
}

.page-btn.active{
    background:#ff4d4f;
    color:#fff;
    border-color:#ff4d4f;
}

/* OVERLAY */
.modal-overlay{
    position:fixed;
    inset:0;
    background:rgba(0,0,0,0.45);
    backdrop-filter: blur(4px);
    display:none;
    justify-content:center;
    align-items:center;
    z-index:9999;
    animation:fadeIn .25s ease;
}

/* BOX */
.modal-box{
    background:#fff;
    padding:25px 30px;
    border-radius:16px;
    width:380px;
    max-width:90%;
    text-align:center;
    box-shadow:0 15px 40px rgba(0,0,0,0.15);
    animation:scaleUp .25s ease;
}

/* TITLE */
.modal-box h3{
    font-size:20px;
    margin-bottom:10px;
}

/* TEXT */
.modal-box p{
    color:#666;
    font-size:14px;
}

/* ACTIONS */
.modal-actions{
    margin-top:20px;
    display:flex;
    justify-content:center;
    gap:12px;
}

/* BUTTON */
.btn-close{
    padding:8px 18px;
    border:none;
    background:#eee;
    color:#333;
    border-radius:8px;
    cursor:pointer;
    transition:.2s;
}

.btn-close:hover{
    background:#ddd;
}

/* BUTTON CONFIRM */
.btn-confirm{
    padding:8px 18px;
    border:none;
    background:#ff4d4f;
    color:#fff;
    border-radius:8px;
    cursor:pointer;
    transition:.2s;
}

.btn-confirm:hover{
    background:#e60023;
}

/* ANIMATION */
@keyframes fadeIn{
    from{opacity:0}
    to{opacity:1}
}

@keyframes scaleUp{
    from{transform:scale(.9); opacity:0}
    to{transform:scale(1); opacity:1}
}

/* PROGRESS BAR */
.progress-bar{
    margin-top:20px;
}

.progress-line{
    height:4px;
    background:#eee;
    border-radius:10px;
    position:relative;
}

.progress-fill{
    position:absolute;
    top:0;
    left:0;
    height:100%;
    background:#ff4d4f;
    border-radius:10px;
    transition:.4s;
}

.progress-steps{
    display:flex;
    justify-content:space-between;
    position:relative;
    top:-10px;
}

.step{
    display:flex;
    justify-content:center;
    width:100%;
}

.step .dot{
    width:14px;
    height:14px;
    background:#ccc;
    border-radius:50%;
    z-index:2;
}

.step.active .dot{
    background:#ff4d4f;
    transform:scale(1.2);
}

.step{
    position:relative;
    display:flex;
    justify-content:center;
    width:100%;
}

.step-label{
    position:absolute;
    top:-28px;
    font-size:12px;
    background:#ff4d4f;
    color:#fff;
    padding:4px 8px;
    border-radius:999px;
    white-space:nowrap;
}

.dot{
    width:14px;
    height:14px;
    background:#ccc;
    border-radius:50%;
}

.dot.active{
    background:#ff4d4f;
    transform:scale(1.3);
}

</style>

<main class="order-page">
<div class="container">

    <div class="order-header">
        <h1>📦 Đơn hàng của tôi</h1>
        <a href="<?= $base ?>" class="btn-back">← Tiếp tục mua sắm</a>
    </div>

    <!-- FILTER -->
    <div class="order-filter">
        <button class="filter-btn active" data-status="">Tất cả</button>
        <button class="filter-btn" data-status="pending">Đang xử lý</button>
        <button class="filter-btn" data-status="completed">Hoàn thành</button>
        <button class="filter-btn" data-status="cancelled">Đã hủy</button>
    </div>

    <!-- LIST -->
    <div id="orderList" class="order-list">
        <?php foreach ($orders as $order): ?>
            <?= renderOrder($order) ?>
        <?php endforeach; ?>
    </div>

    <!-- PAGINATION -->
    <div id="pagination" class="pagination">
        <?php for ($i=1; $i <= $totalPages; $i++): ?>
            <button
                class="page-btn <?= $i==$page?'active':'' ?>"
                data-page="<?= $i ?>">
                <?= $i ?>
            </button>
        <?php endfor; ?>
    </div>

</div>
</main>

<!-- MODAL -->
<div id="cancelModal" class="modal-overlay">
    <div class="modal-box" onclick="event.stopPropagation()">
        <h3>⚠️ Xác nhận hủy đơn</h3>
        <p>Bạn có chắc muốn hủy đơn này không?</p>

        <div class="modal-actions">
            <button class="btn-close" onclick="closeModal()">Không</button>
            <button class="btn-confirm" onclick="confirmCancel()">Hủy đơn</button>
        </div>
    </div>
</div>

<script>
let currentOrderId = null;
let currentStatus = ''; // 🔥 lưu filter hiện tại

// FILTER
document.querySelectorAll('.filter-btn').forEach(btn=>{
    btn.onclick = function(){
        document.querySelectorAll('.filter-btn').forEach(b=>b.classList.remove('active'));
        this.classList.add('active');

        currentStatus = this.dataset.status; // lưu lại
        loadOrders(currentStatus, 1);
    }
});

// PAGINATION CLICK
document.querySelectorAll('.page-btn').forEach(btn=>{
    btn.onclick = function(){
        const page = this.dataset.page;
        loadOrders(currentStatus, page);
    }
});

function loadOrders(status='', page=1){
    const list = document.getElementById('orderList');

    list.innerHTML = '<div class="loading">Đang tải...</div>';

    fetch(`index.php?url=load-orders&status=${status}&page=${page}`)
    .then(res=>res.text())
    .then(html=>{
        list.innerHTML = html;

        // update active page
        document.querySelectorAll('.page-btn').forEach(btn=>{
            btn.classList.remove('active');
        });

        const btn = document.querySelector(`.page-btn[data-page="${page}"]`);
        if(btn) btn.classList.add('active');
    });
}

// MODAL
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
        closeModal();

        // 🔥 reload đúng filter hiện tại
        loadOrders(currentStatus, 1);
    });
}
</script>
