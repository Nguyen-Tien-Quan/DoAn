<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user = $_SESSION['user'] ?? null;
$favorites = $_SESSION['favorites'] ?? [];
$cards = $_SESSION['cards'] ?? [];
?>

<main class="profile">
    <div class="container">

        <!-- SEARCH -->
        <div class="profile-container">
            <div class="search-bar d-none d-md-flex">
                <input type="text" placeholder="Search for item" class="search-bar__input" />
                <button class="search-bar__submit">
                    <img src="<?= $base ?>assets/icons/search.svg" class="search-bar__icon icon" />
                </button>
            </div>
        </div>

        <div class="profile-container">
            <div class="row gy-md-3">

                <!-- SIDEBAR -->
                <div class="col-3 col-xl-4 col-lg-5 col-md-12">
                    <aside class="profile__sidebar">

                        <!-- USER -->
                        <div class="profile-user">
                            <img src="<?= $base ?>assets/img/avatar.jpg" class="profile-user__avatar" />

                            <h1 class="profile-user__name">
                                <?= $user['name'] ?? 'Guest' ?>
                            </h1>

                            <p class="profile-user__desc">
                                Registered:
                                <?= isset($user['created_at']) ? date('d M Y', strtotime($user['created_at'])) : '' ?>
                            </p>
                        </div>

                        <!-- MENU -->
                        <div class="profile-menu">
                            <h3 class="profile-menu__title">Manage Account</h3>
                            <ul class="profile-menu__list">
                                <li>
                                    <a href="<?= $base ?>index.php?url=profile" class="profile-menu__link">
                                        <span class="profile-menu__icon">
                                            <img src="<?= $base ?>assets/icons/profile.svg" class="icon" />
                                        </span>
                                        Personal info
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="profile-menu__link">
                                        <img src="<?= $base ?>assets/icons/location.svg" class="icon" />
                                        Addresses
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div class="profile-menu">
                            <h3 class="profile-menu__title">My items</h3>
                            <ul class="profile-menu__list">
                                <li>
                                    <a href="#" class="profile-menu__link">
                                        <img src="<?= $base ?>assets/icons/download.svg" class="icon" />
                                        Orders
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="profile-menu__link">
                                        <img src="<?= $base ?>assets/icons/heart.svg" class="icon" />
                                        Lists
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div class="profile-menu">
                            <h3 class="profile-menu__title">Subscriptions & plans</h3>
                            <ul class="profile-menu__list">
                                <li>
                                    <a href="#" class="profile-menu__link">
                                        <img src="<?= $base ?>assets/icons/shield.svg" class="icon" />
                                        Protection plans
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div class="profile-menu">
                            <h3 class="profile-menu__title">Customer Service</h3>
                            <ul class="profile-menu__list">
                                <li>
                                    <a href="#" class="profile-menu__link">
                                        <img src="<?= $base ?>assets/icons/info.svg" class="icon" />
                                        Help
                                    </a>
                                </li>
                            </ul>
                        </div>

                    </aside>
                </div>

                <!-- CONTENT -->
                <div class="col-9 col-xl-8 col-lg-7 col-md-12">
                    <div class="cart-info">
                        <div class="row gy-3">

                            <!-- WALLET -->
                            <div class="col-12">
                                <h2 class="cart-info__heading">My Wallet</h2>

                                <div class="row gy-md-2 row-cols-3 row-cols-xl-2 row-cols-lg-1">

                                    <?php if (!empty($cards)): ?>
                                        <?php foreach ($cards as $card): ?>
                                            <div class="col">
                                                <article class="payment-card" style="--bg-color: #1e2e69">
                                                    <div class="payment-card__top">
                                                        <span class="payment-card__type">
                                                            <?= $card['type'] ?>
                                                        </span>
                                                    </div>

                                                    <div class="payment-card__number">
                                                        **** **** **** <?= substr($card['number'], -4) ?>
                                                    </div>

                                                    <div class="payment-card__bottom">
                                                        <p class="payment-card__value">
                                                            <?= $user['name'] ?? '' ?>
                                                        </p>
                                                    </div>
                                                </article>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>

                                    <div class="col">
                                        <a class="new-card" href="#">
                                            <img src="<?= $base ?>assets/icons/plus.svg" class="icon" />
                                            <p>Add New Card</p>
                                        </a>
                                    </div>

                                </div>
                            </div>

                            <!-- ACCOUNT INFO -->
                            <div class="col-12">
                                <h2 class="cart-info__heading">Account info</h2>

                                <div class="row row-cols-2 row-cols-lg-1">

                                    <div class="col">
                                        <article class="account-info">
                                            <img src="<?= $base ?>assets/icons/message.svg" class="icon" />
                                            <div>
                                                <h3>Email Address</h3>
                                                <p><?= $user['email'] ?? '' ?></p>
                                            </div>
                                        </article>
                                    </div>

                                    <div class="col">
                                        <article class="account-info">
                                            <img src="<?= $base ?>assets/icons/calling.svg" class="icon" />
                                            <div>
                                                <h3>Phone number</h3>
                                                <p><?= $user['phone'] ?? 'Chưa có' ?></p>
                                            </div>
                                        </article>
                                    </div>

                                </div>
                            </div>

                            <!-- FAVORITE -->
                            <div class="col-12">
                                <h2 class="cart-info__heading">Lists</h2>

                                <?php if (!empty($favorites)): ?>
                                    <?php foreach ($favorites as $item): ?>
                                        <article class="favourite-item">
                                            <img src="<?= $item['image'] ?>" class="favourite-item__thumb" />

                                            <div>
                                                <h3 class="favourite-item__title">
                                                    <?= $item['name'] ?>
                                                </h3>

                                                <div class="favourite-item__content">
                                                    <span class="favourite-item__price">
                                                        <?= number_format($item['price']) ?>đ
                                                    </span>

                                                    <form action="<?= $base ?>index.php?url=add-cart&id=<?= $item['id'] ?>" method="POST">
                                                        <button class="btn btn--primary btn--rounded">
                                                            Add to cart
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </article>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p>Chưa có sản phẩm yêu thích</p>
                                <?php endif; ?>

                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</main>
