<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$cart = $_SESSION['cart'] ?? [];

$subtotal = 0;
$itemCount = count($cart);


foreach ($cart as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$shipping = 10000;
$total = $subtotal + $shipping;
?>

<main class="checkout-page">
    <div class="container">
        <!-- Search bar -->
        <div class="checkout-container">
            <div class="search-bar d-none d-md-flex">
                <input type="text" placeholder="Search for item" class="search-bar__input" />
                <button class="search-bar__submit">
                    <img src="<?= $base ?>assets/icons/search.svg" class="search-bar__icon icon" />
                </button>
            </div>
        </div>

        <!-- Breadcrumbs -->
        <div class="checkout-container">
            <ul class="breadcrumbs checkout-page__breadcrumbs">
                <li>
                    <a href="<?= $base ?>" class="breadcrumbs__link">
                        Home
                        <img src="<?= $base ?>assets/icons/arrow-right.svg" />
                    </a>
                </li>
                <li>
                    <a href="#!" class="breadcrumbs__link breadcrumbs__link--current">Checkout</a>
                </li>
            </ul>
        </div>

        <!-- Checkout content -->
        <div class="checkout-container">
            <div class="row gy-xl-3">

                <!-- LEFT -->
                <div class="col-8 col-xl-12">
                    <div class="cart-info">
                        <div class="cart-info__list">

                            <?php if (!empty($cart)): ?>
                                <?php foreach ($cart as $item): ?>
                                    <article class="cart-item" id="item-<?= $item['id'] ?>">
                                        <a href="<?= $base ?>index.php?url=product&id=<?= $item['id'] ?>">
                                            <img src="<?= $base ?>assets/img/product/<?= $item['image'] ?>"
                                                class="cart-item__thumb" />
                                        </a>

                                        <div class="cart-item__content">
                                            <div class="cart-item__content-left">
                                                <h3 class="cart-item__title">
                                                    <a href="<?= $base ?>index.php?url=product&id=<?= $item['id'] ?>">
                                                        <?= $item['name'] ?>
                                                    </a>
                                                </h3>

                                                <p class="cart-item__price-wrap">
                                                    <?= vnd($item['price']) ?> |
                                                    <span class="cart-item__status">In Stock</span>
                                                </p>

                                                <div class="cart-item__ctrl cart-item__ctrl--md-block">
                                                    <div class="cart-item__input">

                                                        <!-- MINUS -->
                                                        <button type="button" class="cart-item__input-btn minus" data-id="<?= $item['id'] ?>">
                                                            <img class="icon" src="<?= $base ?>assets/icons/minus.svg" />
                                                        </button>

                                                        <span id="qty-<?= $item['id'] ?>"><?= $item['quantity'] ?></span>

                                                        <!-- PLUS -->
                                                        <button type="button" class="cart-item__input-btn plus" data-id="<?= $item['id'] ?>">
                                                            <img class="icon" src="<?= $base ?>assets/icons/plus.svg" />
                                                        </button>

                                                    </div>
                                                </div>
                                            </div>

                                            <div class="cart-item__content-right">
                                                <p class="cart-item__total-price" id="total-<?= $item['id'] ?>">
                                                    <?= vnd($item['price'] * $item['quantity']) ?>
                                                </p>

                                                <div class="cart-item__ctrl">
                                                    <button class="cart-item__ctrl-btn">
                                                        <img src="<?= $base ?>assets/icons/heart-2.svg" />
                                                        Save
                                                    </button>

                                                   <button
                                                        type="button"
                                                        class="cart-item__ctrl-btn btn-delete js-toggle "
                                                        data-id="<?= $item['id'] ?>"
                                                        toggle-target="#delete-confirm"
                                                    >
                                                        <img src="<?= $base ?>assets/icons/trash.svg" />
                                                        Delete
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </article>
                                <?php endforeach; ?>

                            <?php else: ?>
                                <p class="text-center py-5 fs-4 ">
                                    Giỏ hàng của bạn đang trống.<br>
                                    <a href="<?= $base ?>" class="btn btn--primary mt-3">Tiếp tục mua sắm</a>
                                </p>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>

                <!-- RIGHT -->
                <div class="col-4 col-xl-12">
                    <div class="cart-info">
                        <div class="cart-info__row">
                            <span>Subtotal (items)</span>
                            <span><?= $itemCount ?></span>
                        </div>

                        <div class="cart-info__row">
                            <span>Price (Total)</span>
                            <span><?= vnd($subtotal) ?></span>
                        </div>

                        <div class="cart-info__row">
                            <span>Shipping</span>
                            <span><?= vnd($shipping) ?></span>
                        </div>

                        <div class="cart-info__separate"></div>

                        <div class="cart-info__row">
                            <span>Estimated Total</span>
                            <span><?= vnd($total) ?></span>
                        </div>

                        <a href="#!" class="cart-info__next-btn btn btn--primary btn--rounded">
                            Continue to checkout
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div id="delete-confirm" class="modal modal--small hide">
            <div class="modal__content">
                <p class="modal__text">Do you want to remove this item from shopping cart?</p>
                <div class="modal__bottom">
                    <button class="btn btn--small btn--outline modal__btn js-toggle" toggle-target="#delete-confirm">
                        Cancel
                    </button>
                   <button
                        id="confirm-delete-btn"
                        class="btn btn--small btn--danger btn--primary modal__btn btn--no-margin"
                    >
                        Delete
                    </button>
                </div>
            </div>
            <div class="modal__overlay js-toggle" toggle-target="#delete-confirm"></div>
    </div>
</main>
