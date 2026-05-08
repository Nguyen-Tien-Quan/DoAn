<?php

$conn = getDB();

$base = $base ?? '/';

// =========================
// DEFAULT DATA
// =========================
$promo = $promo ?? [];
$products = $products ?? [];

$banner = $banner ?? [
    'title'     => $promo['name'] ?? 'Flash Sale',
    'desc'      => $promo['description'] ?? 'Ưu đãi cực sốc chỉ trong thời gian giới hạn',
    'image'     => !empty($promo['image'])
        ? $base . $promo['image']
        : $base . 'assets/img/promo/default.jpg',
    'end_time'  => $promo['end_date']
        ?? date('Y-m-d H:i:s', strtotime('+1 day'))
];

?>

<style>
.promotion-page{
    padding:40px 0 80px;
    background:#f5f7fb;
    transition:.3s;
}

/* DARK MODE */
html.dark .promotion-page{
    background:#0f172a;
}

/* =========================
   HEADER
========================= */
.page-header{
    text-align:center;
    margin-bottom:35px;
}

.page-header h1{
    font-size:42px;
    font-weight:800;
    color:#111827;
    margin-bottom:10px;
}

html.dark .page-header h1{
    color:#f8fafc;
}

.page-header p{
    color:#6b7280;
    font-size:16px;
}

html.dark .page-header p{
    color:#94a3b8;
}

/* =========================
   BANNER
========================= */
.promo-banner{
    position:relative;
    overflow:hidden;
    border-radius:30px;
    margin-bottom:50px;
    height:420px;
    box-shadow:0 20px 50px rgba(0,0,0,.15);
}

html.dark .promo-banner{
    box-shadow:0 20px 50px rgba(0,0,0,.45);
}

.promo-banner img{
    width:100%;
    height:100%;
    object-fit:cover;
    filter:brightness(.55);
    transition:.4s;
}

.promo-banner:hover img{
    transform:scale(1.05);
}

.promo-overlay{
    position:absolute;
    inset:0;
    background:linear-gradient(
        to right,
        rgba(0,0,0,.75),
        rgba(0,0,0,.25)
    );
}

.promo-content{
    position:absolute;
    top:50%;
    left:60px;
    transform:translateY(-50%);
    z-index:2;
    max-width:550px;
    color:#fff;
}

.promo-badge{
    display:inline-flex;
    align-items:center;
    gap:8px;
    padding:8px 16px;
    border-radius:999px;
    background:rgba(255,255,255,.18);
    backdrop-filter:blur(10px);
    margin-bottom:18px;
    font-size:14px;
    font-weight:600;
}

.promo-content h2{
    font-size:48px;
    font-weight:800;
    line-height:1.2;
    margin-bottom:15px;
}

.promo-content p{
    font-size:17px;
    line-height:1.7;
    opacity:.95;
    margin-bottom:25px;
}

/* =========================
   COUNTDOWN
========================= */
.countdown-wrap{
    display:flex;
    gap:15px;
    flex-wrap:wrap;
}

.time-box{
    width:90px;
    padding:14px 10px;
    border-radius:18px;
    text-align:center;
    background:rgba(255,255,255,.18);
    backdrop-filter:blur(10px);
}

.time-box strong{
    display:block;
    font-size:28px;
    font-weight:800;
    color:#fff;
}

.time-box span{
    font-size:13px;
    opacity:.9;
}

/* =========================
   GRID
========================= */
.promo-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(280px,1fr));
    gap:28px;
}

/* =========================
   CARD
========================= */
.promo-card{
    position:relative;
    background:#fff;
    border-radius:26px;
    overflow:hidden;
    transition:.35s;
    box-shadow:0 10px 35px rgba(0,0,0,.08);
}

html.dark .promo-card{
    background:#1e293b;
    box-shadow:0 10px 35px rgba(0,0,0,.35);
}

.promo-card:hover{
    transform:translateY(-10px);
    box-shadow:0 18px 40px rgba(0,0,0,.12);
}

html.dark .promo-card:hover{
    box-shadow:0 18px 45px rgba(0,0,0,.45);
}

.sale-badge{
    position:absolute;
    top:18px;
    left:18px;
    z-index:2;
    background:linear-gradient(135deg,#ff512f,#dd2476);
    color:#fff;
    padding:8px 14px;
    border-radius:999px;
    font-size:13px;
    font-weight:700;
    box-shadow:0 8px 20px rgba(221,36,118,.35);
}

.product-thumb{
    height:230px;
    background:linear-gradient(to bottom,#fff,#f3f4f6);
    display:flex;
    align-items:center;
    justify-content:center;
    padding:20px;
}

html.dark .product-thumb{
    background:linear-gradient(to bottom,#1e293b,#0f172a);
}

.product-thumb a{
    width:100%;
    height:100%;
    display:flex;
    align-items:center;
    justify-content:center;
}

.product-thumb img{
    max-width:100%;
    max-height:100%;
    object-fit:contain;
    transition:.35s;
}

.promo-card:hover .product-thumb img{
    transform:scale(1.08);
}

.product-body{
    padding:22px;
}

.product-category{
    font-size:13px;
    color:#f97316;
    font-weight:700;
    margin-bottom:10px;
    text-transform:uppercase;
}

.product-title{
    font-size:22px;
    font-weight:800;
    color:#111827;
    margin-bottom:12px;
    line-height:1.4;
    min-height:62px;
}

html.dark .product-title{
    color:#f8fafc;
}

.product-title a{
    text-decoration:none;
    color:inherit;
}

.product-title a:hover{
    color:#f97316;
}

.price-wrap{
    display:flex;
    align-items:center;
    gap:12px;
    margin-bottom:14px;
}

.old-price{
    text-decoration:line-through;
    color:#9ca3af;
    font-size:16px;
}

html.dark .old-price{
    color:#94a3b8;
}

.new-price{
    font-size:28px;
    font-weight:800;
    color:#ef4444;
}

.product-meta{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:18px;
    font-size:14px;
    color:#6b7280;
}

html.dark .product-meta{
    color:#cbd5e1;
}

.product-end{
    background:#fff7ed;
    color:#ea580c;
    padding:8px 12px;
    border-radius:12px;
    font-size:13px;
    font-weight:600;
    margin-bottom:18px;
}

html.dark .product-end{
    background:#3b1d12;
    color:#fdba74;
}

/* =========================
   ACTIONS
========================= */
.promo-actions{
    display:flex;
    gap:12px;
    margin-top:15px;
}

.btn-detail{
    flex:1;
    display:flex;
    align-items:center;
    justify-content:center;
    text-decoration:none;
    border-radius:16px;
    border:2px solid #f97316;
    color:#f97316;
    font-weight:700;
    transition:.3s;
    background:#fff;
    min-height:54px;
}

html.dark .btn-detail{
    background:#0f172a;
    color:#fb923c;
    border-color:#fb923c;
}

.btn-detail:hover{
    background:#fff7ed;
}

html.dark .btn-detail:hover{
    background:#1e293b;
}

.add-cart-form{
    flex:1;
}

.btn-buy{
    width:100%;
    min-height:54px;
    border:none;
    border-radius:16px;
    padding:15px;
    background:linear-gradient(135deg,#f97316,#ef4444);
    color:#fff;
    font-size:16px;
    font-weight:700;
    cursor:pointer;
    transition:.3s;
}

.btn-buy:hover{
    transform:translateY(-2px);
    opacity:.92;
}

.empty-box{
    text-align:center;
    padding:80px 20px;
    border-radius:24px;
    background:#fff;
    box-shadow:0 10px 30px rgba(0,0,0,.05);
}

html.dark .empty-box{
    background:#1e293b;
    box-shadow:0 10px 30px rgba(0,0,0,.35);
}

.empty-box h3{
    font-size:28px;
    margin-bottom:10px;
}

html.dark .empty-box h3{
    color:#f8fafc;
}

.empty-box p{
    color:#6b7280;
}

html.dark .empty-box p{
    color:#94a3b8;
}

/* =========================
   MOBILE
========================= */
@media(max-width:768px){

    .promo-banner{
        height:520px;
    }

    .promo-content{
        left:25px;
        right:25px;
    }

    .promo-content h2{
        font-size:34px;
    }

    .page-header h1{
        font-size:32px;
    }

    .countdown-wrap{
        gap:10px;
    }

    .time-box{
        width:72px;
    }

    .time-box strong{
        font-size:22px;
    }

    .promo-actions{
        flex-direction:column;
    }
}
</style>

<main class="container promotion-page">

    <!-- HEADER -->
    <div class="page-header">
        <h1>🔥 Khuyến mãi hot hôm nay</h1>
        <p>Săn deal cực mạnh - số lượng có hạn - ưu đãi cập nhật liên tục</p>
    </div>

    <!-- BANNER -->
    <div class="promo-banner">

        <img
            src="<?= htmlspecialchars($banner['image']) ?>"
            alt="promotion"
        >

        <div class="promo-overlay"></div>

        <div class="promo-content">

            <div class="promo-badge">
                ⚡ Flash Sale đang diễn ra
            </div>

            <h2>
                <?= htmlspecialchars($banner['title']) ?>
            </h2>

            <p>
                <?= htmlspecialchars($banner['desc']) ?>
            </p>

            <div
                class="countdown-wrap"
                id="countdown"
                data-end="<?= htmlspecialchars($banner['end_time']) ?>"
            >

                <div class="time-box">
                    <strong id="days">00</strong>
                    <span>Ngày</span>
                </div>

                <div class="time-box">
                    <strong id="hours">00</strong>
                    <span>Giờ</span>
                </div>

                <div class="time-box">
                    <strong id="minutes">00</strong>
                    <span>Phút</span>
                </div>

                <div class="time-box">
                    <strong id="seconds">00</strong>
                    <span>Giây</span>
                </div>

            </div>

        </div>

    </div>

    <!-- PRODUCT GRID -->
    <div class="promo-grid">

        <?php if (!empty($products)): ?>

            <?php foreach ($products as $item): ?>

                <?php

                    $item = $item ?? [];

                    $id = (int)($item['id'] ?? 0);

                    $name = $item['name'] ?? 'Sản phẩm';

                    $image = !empty($item['image'])
                        ? $base . 'assets/img/product/' . $item['image']
                        : $base . 'assets/img/default-product.png';

                    $category = $item['category_name'] ?? 'Danh mục';

                    $sold = (int)($item['sold_count'] ?? 0);

                    $price = (float)($item['base_price'] ?? 0);

                    $final = !empty($item['final_price'])
                        ? (float)$item['final_price']
                        : $price;

                    $discount = (int)($item['discount_percent'] ?? 0);

                    $endDate = !empty($item['end_date'])
                        ? date('d/m/Y H:i', strtotime($item['end_date']))
                        : '--';

                ?>

                <div class="promo-card">

                    <?php if ($discount > 0): ?>
                        <div class="sale-badge">
                            -<?= $discount ?>%
                        </div>
                    <?php endif; ?>

                    <!-- IMAGE CLICK -->
                    <div class="product-thumb">

                        <a href="<?= $base ?>index.php?url=product&id=<?= $id ?>">

                            <img
                                src="<?= htmlspecialchars($image) ?>"
                                alt="<?= htmlspecialchars($name) ?>"
                            >

                        </a>

                    </div>

                    <div class="product-body">

                        <div class="product-category">
                            <?= htmlspecialchars($category) ?>
                        </div>

                        <!-- TITLE CLICK -->
                        <h3 class="product-title">

                            <a href="<?= $base ?>index.php?url=product&id=<?= $id ?>">

                                <?= htmlspecialchars($name) ?>

                            </a>

                        </h3>

                        <div class="price-wrap">

                            <span class="new-price">
                                <?= number_format($final) ?>đ
                            </span>

                            <?php if ($final < $price): ?>

                                <span class="old-price">
                                    <?= number_format($price) ?>đ
                                </span>

                            <?php endif; ?>

                        </div>

                        <div class="product-meta">

                            <span>
                                🔥 Đã bán <?= number_format($sold) ?>
                            </span>

                            <span>
                                ⭐ Hot deal
                            </span>

                        </div>

                        <div class="product-end">

                            ⏰ Kết thúc:
                            <?= $endDate ?>

                        </div>

                        <!-- ACTIONS -->
                        <div class="promo-actions">

                            <!-- DETAIL -->
                            <a
                                href="<?= $base ?>index.php?url=product&id=<?= $id ?>"
                                class="btn-detail"
                            >
                                Chi tiết
                            </a>

                            <!-- ADD CART -->
                            <form
                                action="<?= $base ?>index.php?url=add-cart&id=<?= $id ?>"
                                method="POST"
                                class="add-cart-form"
                            >

                                <input
                                    type="hidden"
                                    name="quantity"
                                    value="1"
                                >

                                <button type="submit" class="btn-buy">
                                    🛒 Mua ngay
                                </button>

                            </form>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <div class="empty-box">

                <h3>😢 Hiện chưa có khuyến mãi</h3>

                <p>
                    Vui lòng quay lại sau để săn deal mới.
                </p>

            </div>

        <?php endif; ?>

    </div>

</main>

<script>
const countdown = document.getElementById('countdown');

if (countdown) {

    const endTime = new Date(
        countdown.dataset.end
    ).getTime();

    function updateCountdown() {

        const now = new Date().getTime();

        const distance = endTime - now;

        if (distance <= 0) {

            document.getElementById('days').innerText = '00';
            document.getElementById('hours').innerText = '00';
            document.getElementById('minutes').innerText = '00';
            document.getElementById('seconds').innerText = '00';

            return;
        }

        const days = Math.floor(
            distance / (1000 * 60 * 60 * 24)
        );

        const hours = Math.floor(
            (distance % (1000 * 60 * 60 * 24))
            / (1000 * 60 * 60)
        );

        const minutes = Math.floor(
            (distance % (1000 * 60 * 60))
            / (1000 * 60)
        );

        const seconds = Math.floor(
            (distance % (1000 * 60))
            / 1000
        );

        document.getElementById('days').innerText =
            String(days).padStart(2, '0');

        document.getElementById('hours').innerText =
            String(hours).padStart(2, '0');

        document.getElementById('minutes').innerText =
            String(minutes).padStart(2, '0');

        document.getElementById('seconds').innerText =
            String(seconds).padStart(2, '0');
    }

    updateCountdown();

    setInterval(updateCountdown, 1000);
}
</script>
