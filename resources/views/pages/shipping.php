<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../app/controllers/CartController.php';

// LẤY DATA CART (QUAN TRỌNG)
$cartData = getCart(false);

$cartItems = $cartData['items'] ?? [];
$subtotal = $cartData['subtotal'] ?? 0;
$total = $cartData['total'] ?? 0;
$totalQty = 0;

foreach ($cartItems as $item) {
    $totalQty += $item['quantity'];
}
?>

<!-- MAIN -->
<main class="checkout-page">
    <div class="container">

        <!-- Search bar -->
         <div class="checkout-container">
                <div class="search-bar d-none d-md-flex">
                    <input type="text" name="" id="" placeholder="Search for item" class="search-bar__input" />
                    <button class="search-bar__submit">
                        <img src="./assets/icons/search.svg" alt="" class="search-bar__icon icon" />
                    </button>
                </div>
        </div>

        <!-- Breadcrumbs -->
       <div class="checkout-container">
                    <ul class="breadcrumbs checkout-page__breadcrumbs">
                        <li>
                            <a href="./" class="breadcrumbs__link">
                                Home
                                <img src="./assets/icons/arrow-right.svg" alt="" />
                            </a>
                        </li>
                        <li>
                            <a href="./checkout.html" class="breadcrumbs__link">
                                Checkout
                                <img src="./assets/icons/arrow-right.svg" alt="" />
                            </a>
                        </li>
                        <li>
                            <a href="#!" class="breadcrumbs__link breadcrumbs__link--current">Shipping</a>
                        </li>
                    </ul>
                </div>

        <!-- Checkout content -->
        <div class="checkout-container">
            <div class="row gy-xl-3">

                <!-- LEFT -->
                <div class="col-8 col-xl-12">
                    <div class="cart-info">
                        <h1 class="cart-info__heading">1. Shipping, arrives between Mon, May 16—Tue, May 24</h1>
                                <div class="cart-info__separate"></div>

                                <!-- Checkout address -->
                                <div class="user-address">
                                    <div class="user-address__top">
                                        <div>
                                            <h2 class="user-address__title">Shipping address</h2>
                                            <p class="user-address__desc">Where should we deliver your order?</p>
                                        </div>
                                        <button
                                            class="user-address__btn btn btn--primary btn--rounded btn--small js-toggle"
                                            toggle-target="#add-new-address"
                                        >
                                            <img src="./assets/icons/plus.svg" alt="" />
                                            Add a new address
                                        </button>
                                    </div>
                                    <div class="user-address__list">
                                        <!-- Empty message -->
                                        <!-- <p class="user-address__message">
                                            Not address yet.
                                            <a class="user-address__link js-toggle" href="#!" toggle-target="#add-new-address">Add a new address</a>
                                        </p> -->

                                        <!-- Address card 1 -->
                                        <article class="address-card">
                                            <div class="address-card__left">
                                                <div class="address-card__choose">
                                                    <label class="cart-info__checkbox">
                                                        <input
                                                            type="radio"
                                                            name="shipping-adress"
                                                            checked
                                                            class="cart-info__checkbox-input"
                                                        />
                                                    </label>
                                                </div>
                                                <div class="address-card__info">
                                                    <h3 class="address-card__title">Imran Khan</h3>
                                                    <p class="address-card__desc">
                                                        Museum of Rajas, Sylhet Sadar, Sylhet 3100.
                                                    </p>
                                                    <ul class="address-card__list">
                                                        <li class="address-card__list-item">Shipping</li>
                                                        <li class="address-card__list-item">Delivery from store</li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="address-card__right">
                                                <div class="address-card__ctrl">
                                                    <button
                                                        class="cart-info__edit-btn js-toggle"
                                                        toggle-target="#add-new-address"
                                                    >
                                                        <img class="icon" src="./assets/icons/edit.svg" alt="" />
                                                        Edit
                                                    </button>
                                                </div>
                                            </div>
                                        </article>

                                        <!-- Address card 2 -->
                                        <article class="address-card">
                                            <div class="address-card__left">
                                                <div class="address-card__choose">
                                                    <label class="cart-info__checkbox">
                                                        <input
                                                            type="radio"
                                                            name="shipping-adress"
                                                            class="cart-info__checkbox-input"
                                                        />
                                                    </label>
                                                </div>
                                                <div class="address-card__info">
                                                    <h3 class="address-card__title">Imran Khan</h3>
                                                    <p class="address-card__desc">
                                                        Al Hamra City (10th Floor), Hazrat Shahjalal Road, Sylhet,
                                                        Sylhet, Bangladesh
                                                    </p>
                                                    <ul class="address-card__list">
                                                        <li class="address-card__list-item">Shipping</li>
                                                        <li class="address-card__list-item">Delivery from store</li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="address-card__right">
                                                <div class="address-card__ctrl">
                                                    <button
                                                        class="cart-info__edit-btn js-toggle"
                                                        toggle-target="#add-new-address"
                                                    >
                                                        <img class="icon" src="./assets/icons/edit.svg" alt="" />
                                                        Edit
                                                    </button>
                                                </div>
                                            </div>
                                        </article>
                                    </div>
                                </div>

                                <div class="cart-info__separate"></div>

                        <div class="cart-info__separate"></div>

                        <h2 class="cart-info__sub-heading">Items details</h2>
                        <div class="cart-info__list">

                            <?php if(count($cartItems) > 0): ?>

                                <?php foreach ($cartItems as $item): ?>
                                    <article class="cart-item">
                                        <a href="#">
                                            <img src="./assets/img/product/<?= $item['image'] ?>"
                                                 class="cart-item__thumb" />
                                        </a>

                                        <div class="cart-item__content">
                                            <div class="cart-item__content-left">
                                                <h3 class="cart-item__title">
                                                    <?= htmlspecialchars($item['name']) ?>
                                                </h3>

                                                <p class="cart-item__price-wrap">
                                                    $<?= number_format($item['price'], 2) ?>
                                                </p>

                                                <div class="cart-item__ctrl cart-item__ctrl--md-block">
                                                    <div class="cart-item__input">
                                                        Quantity: <?= $item['quantity'] ?>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="cart-item__content-right">
                                                <p class="cart-item__total-price">
                                                    $<?= number_format($item['price'] * $item['quantity'], 2) ?>
                                                </p>
                                            </div>
                                        </div>
                                    </article>
                                <?php endforeach; ?>

                            <?php else: ?>

                                <!-- EMPTY -->
                                <div style="text-align:center; padding:50px;">
                                    <img src="./assets/img/empty-cart.png" style="max-width:200px;">
                                    <p>Giỏ hàng trống</p>
                                    <a href="index.php" class="btn btn--primary">Mua ngay</a>
                                </div>

                            <?php endif; ?>

                        </div>
                    </div>
                </div>

                <!-- RIGHT -->
                <div class="col-4 col-xl-12">
                    <div class="cart-info">

                        <div class="cart-info__row">
                            <span>Subtotal <span class="cart-info__sub-label">(items)</span></span>
                            <span><?= $totalQty ?></span>
                        </div>

                        <div class="cart-info__row">
                            <span>Price <span class="cart-info__sub-label">(Total)</span></span>
                            <span>$<?= number_format($subtotal, 2) ?></span>
                        </div>

                        <div class="cart-info__row">
                            <span>Shipping</span>
                            <span>$10.00</span>
                        </div>

                        <div class="cart-info__separate"></div>

                        <div class="cart-info__row">
                            <span>Estimated Total</span>
                            <span>$<?= number_format($total, 2) ?></span>
                        </div>

                        <a href="#" class="cart-info__next-btn btn btn--primary btn--rounded">
                            Continue to checkout
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</main>
