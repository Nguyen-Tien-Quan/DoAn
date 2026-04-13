<?php
if (session_status() === PHP_SESSION_NONE) session_start();


if (!isset($_SESSION['user'])) {
    header("Location: index.php?url=login");
    exit;
}

$user = $_SESSION['user'];
$conn = getDB();

// ===== GET DATA =====

// customer
$stmt = $conn->prepare("SELECT * FROM customers WHERE user_id=?");
$stmt->execute([$user['id']]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

// address (default)
$stmt = $conn->prepare("
    SELECT * FROM shipping_addresses
    WHERE user_id=?
    ORDER BY is_default DESC, created_at DESC
    LIMIT 1
");
$stmt->execute([$user['id']]);
$address = $stmt->fetch(PDO::FETCH_ASSOC);

// favorites
$stmt = $conn->prepare("
    SELECT p.*
    FROM favorites f
    JOIN products p ON p.id = f.product_id
    WHERE f.user_id=?
    ORDER BY f.created_at DESC
    LIMIT 2
");
$stmt->execute([$user['id']]);
$favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>



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
Registered: <?= date('d M Y', strtotime($user['created_at'] ?? 'now')) ?>
</p>
</div>

<!-- MENU -->
<div class="profile-menu">
<h3 class="profile-menu__title">Manage Account</h3>
<ul class="profile-menu__list">
<li><a href="index.php?url=profile-edit" class="profile-menu__link">Personal info</a></li>
<li><a href="index.php?url=profile-address" class="profile-menu__link">Addresses</a></li>
<li><a href="index.php?url=change-password" class="profile-menu__link">Change password</a></li>
</ul>
</div>

<div class="profile-menu">
<h3 class="profile-menu__title">My items</h3>
<ul class="profile-menu__list">
<li><a href="index.php?url=orders" class="profile-menu__link">Orders</a></li>
<li><a href="index.php?url=favorites" class="profile-menu__link">Favorites</a></li>
</ul>
</div>

<div class="profile-menu">
<ul class="profile-menu__list">
<li><a href="index.php?url=logout" class="profile-menu__link text-danger">Logout</a></li>
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
<h2 class="cart-info__heading">Account info</h2>

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
<h3>Phone</h3>
<p><?= htmlspecialchars($customer['phone'] ?? 'Chưa có') ?></p>
</div>
</article>
</div>

<div class="col">
<article class="account-info">
<div class="account-info__icon">
<img src="<?= $base ?>assets/icons/location.svg" class="icon"/>
</div>
<div>
<h3>Address</h3>
<p><?= htmlspecialchars($address['address'] ?? 'Chưa có địa chỉ') ?></p>
</div>
</article>
</div>

</div>
</div>

<!-- FAVORITES -->
<div class="col-12">
<h2 class="cart-info__heading">Favorites</h2>

<?php if (empty($favorites)): ?>
<p>Chưa có sản phẩm yêu thích</p>
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
Add to cart
</button>
</form>

</div>
</div>

</article>

<div class="separate" style="--margin:20px"></div>
<?php endforeach; ?>

<?php endif; ?>

</div>

</div>

</div>
</div>

</div>
</div>
</div>
</main>
