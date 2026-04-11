<?php
// $banner và $products nên được truyền từ controller

// fallback nếu chưa có data
$banner = $banner ?? [
    'title' => 'Flash Sale',
    'desc' => 'Ưu đãi cực sốc hôm nay',
    'image' => $base . 'assets/img/promo/default.jpg',
    'end_time' => date('Y-m-d H:i:s', strtotime('+1 day'))
];

$products = $products ?? [];
?>

<style>
.promotion-page { padding: 40px 0; }

.page-header {
    text-align: center;
    margin-bottom: 35px;
}
.page-header h1 {
    font-size: 30px;
    font-weight: 700;
}
.page-header p { color: #6b7280; }

/* Banner */
.promo-banner {
    position: relative;
    border-radius: 24px;
    overflow: hidden;
    margin-bottom: 40px;
}
.promo-banner img {
    width: 100%;
    height: 320px;
    object-fit: cover;
}
.promo-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, rgba(0,0,0,0.7), transparent);
}
.promo-text {
    position: absolute;
    top: 50%;
    left: 40px;
    transform: translateY(-50%);
    color: #fff;
}
.promo-text h2 { font-size: 28px; }
.countdown { margin: 10px 0; font-weight: 600; }

/* Grid */
.promo-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
    gap: 22px;
}

.promo-card {
    background: #fff;
    border-radius: 18px;
    padding: 15px;
    text-align: center;
    position: relative;
    transition: 0.25s;
    box-shadow: 0 10px 25px rgba(0,0,0,0.06);
}
.promo-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
}

.promo-tag {
    position: absolute;
    top: 12px;
    left: 12px;
    background: linear-gradient(135deg,#ef4444,#f97316);
    color: white;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.promo-card img {
    width: 140px;
    height: 140px;
    object-fit: contain;
    margin: 10px auto;
}

.old-price {
    text-decoration: line-through;
    color: #9ca3af;
    font-size: 13px;
}
.new-price {
    font-size: 18px;
    font-weight: 700;
    color: #f97316;
    margin: 5px 0 10px;
}

.btn {
    padding: 8px 16px;
    border-radius: 10px;
    font-size: 14px;
    text-decoration: none;
    cursor: pointer;
}
.btn--primary {
    background: #4f46e5;
    color: #fff;
}
.btn--outline {
    border: 1px solid #4f46e5;
    color: #4f46e5;
}
.btn--outline:hover {
    background: #4f46e5;
    color: #fff;
}
</style>

<main class="container promotion-page">

    <div class="page-header">
        <h1>🔥 Khuyến mãi hôm nay</h1>
        <p>Săn deal cực sốc – số lượng có hạn</p>
    </div>

    <!-- Banner -->
    <div class="promo-banner">
        <img src="<?= $banner['image'] ?>">
        <div class="promo-overlay"></div>

        <div class="promo-text">
            <h2><?= $banner['title'] ?></h2>
            <p><?= $banner['desc'] ?></p>
            <span class="countdown" data-end="<?= $banner['end_time'] ?>"></span>
            <br>
            <a href="<?= $base ?>index.php?url=products" class="btn btn--primary">Mua ngay</a>
        </div>
    </div>

    <!-- Products -->
    <div class="promo-grid">

        <?php if (!empty($products)): ?>
            <?php foreach ($products as $item): ?>

                <?php
                    // FIX LỖI Ở ĐÂY
                    $price = $item['base_price'] ?? $item['price'] ?? 0;
                    $discount = $item['discount'] ?? 0;
                    $newPrice = $price - ($price * $discount / 100);
                ?>

                <div class="promo-card">

                    <?php if ($discount > 0): ?>
                        <div class="promo-tag">-<?= $discount ?>%</div>
                    <?php endif; ?>

                    <img src="<?= $base.'assets/img/product/'.$item['image'] ?>">

                    <h3><?= $item['name'] ?></h3>

                    <?php if ($discount > 0): ?>
                        <p class="old-price"><?= number_format($price) ?>đ</p>
                    <?php endif; ?>

                    <p class="new-price"><?= number_format($newPrice) ?>đ</p>

                    <button class="btn btn--outline">Thêm vào giỏ</button>
                </div>

            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align:center;color:#999;">Không có khuyến mãi</p>
        <?php endif; ?>

    </div>

</main>

<script>
document.querySelectorAll('.countdown').forEach(el => {
    const end = new Date(el.dataset.end).getTime();

    const update = () => {
        if (!end) return;

        const now = new Date().getTime();
        const diff = end - now;

        if (diff <= 0) {
            el.innerText = "Đã kết thúc";
            return;
        }

        const h = Math.floor(diff / 3600000);
        const m = Math.floor((diff % 3600000) / 60000);
        const s = Math.floor((diff % 60000) / 1000);

        el.innerText = `${h}h ${m}m ${s}s`;
    };

    update();
    setInterval(update, 1000);
});
</script>
