<?php
// $promo = $promo ?? getActivePromotion();
// $products = $products ?? getPromotionProducts();

$banner = [
    'title' => $promo['name'] ?? 'Flash Sale',
    'desc' => 'Giảm giá cực sốc trong thời gian có hạn',
    'image' => $base . ($promo['image'] ?? 'assets/img/promo/default.jpg'),
    'end_time' => $promo['end_date'] ?? date('Y-m-d H:i:s', strtotime('+1 day'))
];
$products = $products ?? [];
?>

<style>
.promotion-page { padding: 40px 0; }

.page-header {
    text-align: center;
    margin-bottom: 40px;
}
.page-header h1 {
    font-size: 34px;
    font-weight: 800;
    color: #111827;
}
.page-header p { color: #6b7280; }

/* Banner đẹp hơn */
.promo-banner {
    position: relative;
    border-radius: 28px;
    overflow: hidden;
    margin-bottom: 50px;
}
.promo-banner img {
    width: 100%;
    height: 350px;
    object-fit: cover;
    filter: brightness(0.8);
}
.promo-text {
    position: absolute;
    top: 50%;
    left: 50px;
    transform: translateY(-50%);
    color: #fff;
}
.promo-text h2 {
    font-size: 34px;
    font-weight: 800;
}
.countdown {
    display: inline-block;
    margin: 15px 0;
    padding: 8px 14px;
    background: rgba(255,255,255,0.2);
    border-radius: 10px;
}

/* Grid */
.promo-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 25px;
}

.promo-card {
    background: #fff;
    border-radius: 20px;
    padding: 18px;
    text-align: center;
    transition: 0.3s;
    box-shadow: 0 12px 30px rgba(0,0,0,0.08);
}
.promo-card:hover {
    transform: translateY(-8px);
}

.promo-tag {
    position: absolute;
    top: 12px;
    left: 12px;
    background: red;
    color: #fff;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
}

.promo-card img {
    width: 150px;
    height: 150px;
    object-fit: contain;
}

.old-price {
    text-decoration: line-through;
    color: #aaa;
}

.new-price {
    font-size: 20px;
    font-weight: bold;
    color: #ef4444;
}

.btn-buy {
    margin-top: 10px;
    background: linear-gradient(135deg,#f97316,#ef4444);
    color: #fff;
    padding: 10px 15px;
    border-radius: 10px;
    border: none;
}
</style>

<main class="container promotion-page">

<div class="page-header">
    <h1>🔥 Khuyến mãi hot</h1>
    <p>Chỉ áp dụng trong thời gian giới hạn</p>
</div>

<!-- Banner -->
<div class="promo-banner">
    <img src="<?= $banner['image'] ?>">
    <div class="promo-text">
        <h2><?= $banner['title'] ?></h2>
        <p><?= $banner['desc'] ?></p>
        <div class="countdown" data-end="<?= $banner['end_time'] ?>"></div>
    </div>
</div>

<!-- Products -->
<div class="promo-grid">

<?php if ($products): ?>
<?php foreach ($products as $item): ?>

<?php
$price = $item['base_price'];
$discount = $item['discount_percent'] ?? 0;
$newPrice = $price - ($price * $discount / 100);
?>

<div class="promo-card">

<?php if ($discount > 0): ?>
<div class="promo-tag">-<?= $discount ?>%</div>
<?php endif; ?>

<img src="<?= $base.'assets/img/product/'.$item['image'] ?>">

<h3><?= $item['name'] ?></h3>

<p class="old-price"><?= number_format($price) ?>đ</p>
<p class="new-price"><?= number_format($newPrice) ?>đ</p>

<button class="btn-buy">Mua ngay</button>

</div>

<?php endforeach; ?>
<?php else: ?>
<p>Không có khuyến mãi</p>
<?php endif; ?>

</div>

</main>
