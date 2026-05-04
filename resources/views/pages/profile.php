<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user'])) {
    header("Location: index.php?url=login");
    exit;
}

$user = $_SESSION['user'];
$conn = getDB();

// ===== CUSTOMER =====
$stmt = $conn->prepare("SELECT * FROM customers WHERE user_id=?");
$stmt->execute([$user['id']]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

// ===== DEFAULT ADDRESS =====
$stmt = $conn->prepare("
    SELECT * FROM shipping_addresses
    WHERE user_id=?
    ORDER BY is_default DESC, created_at DESC
    LIMIT 1
");
$stmt->execute([$user['id']]);
$address = $stmt->fetch(PDO::FETCH_ASSOC);

// ===== FAVORITES =====
$stmt = $conn->prepare("
    SELECT p.*
    FROM favorites f
    JOIN products p ON p.id = f.product_id
    WHERE f.user_id=?
    ORDER BY f.created_at DESC
    LIMIT 3
");
$stmt->execute([$user['id']]);
$favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ===== ORDER COUNT =====
$stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE user_id=?");
$stmt->execute([$user['id']]);
$orderCount = $stmt->fetchColumn();

// ===== RECENT ORDERS =====
$stmt = $conn->prepare("
    SELECT * FROM orders
    WHERE user_id=?
    ORDER BY created_at DESC
    LIMIT 2
");
$stmt->execute([$user['id']]);
$recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$base = '/DoAn/DoAnTotNghiep/public/';
?>

<style>
.profile-stats {
    display: flex;
    justify-content: space-around;
    margin-top: 10px;
}
.stat-item {
    text-align: center;
}
.stat-item strong {
    font-size: 18px;
    color: #ee4d2d;
}

.order-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.order-status {
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
}
.status-pending { background: #fff3cd; color: #856404; }
.status-shipping { background: #d1ecf1; color: #0c5460; }
.status-completed { background: #d4edda; color: #155724; }
.status-cancelled { background: #f8d7da; color: #721c24; }

.empty-state {
    text-align: center;
    padding: 20px;
}
.quick-link {
    margin-top: 10px;
    display: flex;
    gap: 10px;
}
.quick-link a {
    font-size: 14px;
    color: #ee4d2d;
    text-decoration: none;
}
</style>

<main class="profile">
<div class="container">

<div class="profile-container">
<div class="row gy-md-3">

<!-- SIDEBAR -->
<div class="col-3 col-xl-4 col-lg-5 col-md-12">
<aside class="profile__sidebar">

<div class="profile-user">
<img src="<?= $base ?>assets/img/avatars/<?= htmlspecialchars($user['avatar'] ?? 'default.png') ?>"
     onerror="this.src='<?= $base ?>assets/img/avatars/default.png'"
     class="profile-user__avatar"/>

<h1 class="profile-user__name"><?= htmlspecialchars($user['name']) ?></h1>

<p class="profile-user__desc">
Tham gia: <?= date('d/m/Y', strtotime($user['created_at'] ?? 'now')) ?>
</p>

<!-- QUICK LINKS -->
<div class="quick-link">
<a href="index.php?url=settings">⚙️ Cài đặt</a>
<a href="index.php?url=orders">📦 Đơn hàng</a>
</div>

</div>

<!-- STATS -->
<div class="profile-stats">
    <div class="stat-item">
        <strong><?= $orderCount ?></strong>
        <span>Đơn hàng</span>
    </div>
    <div class="stat-item">
        <strong><?= count($favorites) ?></strong>
        <span>Yêu thích</span>
    </div>
</div>

<!-- MENU -->
<div class="profile-menu">
<h3 class="profile-menu__title">Tài khoản của tôi</h3>
<ul class="profile-menu__list">
<li><a href="index.php?url=settings" class="profile-menu__link">Thông tin & bảo mật</a></li>
<li><a href="index.php?url=settings#address" class="profile-menu__link">Địa chỉ giao hàng</a></li>
</ul>
</div>

<div class="profile-menu">
<h3 class="profile-menu__title">Đơn mua</h3>
<ul class="profile-menu__list">
<li><a href="index.php?url=orders" class="profile-menu__link">Đơn hàng của tôi</a></li>
<li><a href="index.php?url=favorites" class="profile-menu__link">Sản phẩm yêu thích</a></li>
</ul>
</div>

<div class="profile-menu">
<ul class="profile-menu__list">
<li><a href="index.php?url=logout" class="profile-menu__link text-danger">Đăng xuất</a></li>
</ul>
</div>

</aside>
</div>

<!-- CONTENT -->
<div class="col-9 col-xl-8 col-lg-7 col-md-12">
<div class="cart-info">

<div class="row gy-3">

<!-- ACCOUNT INFO -->
<div class="col-12">
<h2 class="cart-info__heading">Thông tin tài khoản</h2>

<div class="row row-cols-2 row-cols-lg-1">

<div class="col">
<article class="account-info">
<div class="account-info__icon">
<img src="<?= $base ?>assets/icons/message.svg" class="icon"/>
</div>
<div>
<h3>Email</h3>
<p><?= htmlspecialchars($user['email']) ?></p>
</div>
</article>
</div>

<div class="col">
<article class="account-info">
<div class="account-info__icon">
<img src="<?= $base ?>assets/icons/calling.svg" class="icon"/>
</div>
<div>
<h3>Số điện thoại</h3>
<p><?= htmlspecialchars($customer['phone'] ?? 'Chưa cập nhật') ?></p>
</div>
</article>
</div>

<div class="col">
<article class="account-info">
<div class="account-info__icon">
<img src="<?= $base ?>assets/icons/location.svg" class="icon"/>
</div>
<div>
<h3>Địa chỉ mặc định</h3>
<p><?= htmlspecialchars($address['address'] ?? 'Chưa có địa chỉ') ?></p>
</div>
</article>
</div>

</div>
</div>

<!-- RECENT ORDERS -->
<div class="col-12">
<h2 class="cart-info__heading">Đơn hàng gần đây</h2>

<?php if (empty($recentOrders)): ?>
<p>Chưa có đơn hàng nào</p>
<a href="index.php" class="btn btn--primary">Mua sắm ngay</a>
<?php else: ?>

<?php foreach ($recentOrders as $o): ?>
<div class="order-item">
<div>
<strong>#<?= $o['id'] ?></strong><br>
<?= date('d/m/Y', strtotime($o['created_at'])) ?>
</div>

<div class="order-status status-<?= $o['status'] ?>">
<?= $o['status'] ?>
</div>

<a href="index.php?url=order-detail&id=<?= $o['id'] ?>" class="btn btn--outline">
Xem
</a>
</div>

<div class="separate" style="--margin:15px"></div>
<?php endforeach; ?>

<a href="index.php?url=orders" class="btn btn--primary">Xem tất cả</a>

<?php endif; ?>
</div>

<!-- FAVORITES -->
<div class="col-12">
<h2 class="cart-info__heading">Sản phẩm yêu thích</h2>

<?php if (empty($favorites)): ?>
<div class="empty-state">
<p>Bạn chưa có sản phẩm yêu thích nào</p>
<a href="index.php" class="btn btn--primary">Mua sắm ngay</a>
</div>
<?php else: ?>

<?php foreach ($favorites as $item): ?>
<article class="favourite-item">

<img src="<?= $base ?>assets/img/product/<?= $item['image'] ?>"
     class="favourite-item__thumb"/>

<div>
<h3 class="favourite-item__title">
<?= htmlspecialchars($item['name']) ?>
</h3>

<div class="favourite-item__content">
<span class="favourite-item__price">
<?= number_format($item['base_price']) ?>đ
</span>

<form method="POST" action="index.php?url=add-cart&id=<?= $item['id'] ?>">
<button class="btn btn--primary btn--rounded">
Thêm vào giỏ
</button>
</form>

</div>
</div>

</article>

<div class="separate" style="--margin:20px"></div>
<?php endforeach; ?>

<a href="index.php?url=favorites" class="btn btn--outline">Xem tất cả</a>

<?php endif; ?>

</div>

</div>

</div>
</div>

</div>
</div>
</div>
</main>
