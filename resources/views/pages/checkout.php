<?php

if (session_status() === PHP_SESSION_NONE) session_start();

$base = '/DoAn/DoAnTotNghiep/public/';

$cart = $_SESSION['cart'] ?? [];

$subtotal = 0;
$itemCount = 0;
foreach ($cart as $item) {
    $itemCount += $item['quantity'];
}

foreach ($cart as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$shipping = 10000;
$total = $subtotal + $shipping;
?>

<main class="checkout-page">
    <div class="container">
        <div class="checkout-container">
            <div class="search-bar d-none d-md-flex">
                <input type="text" placeholder="Search for item" class="search-bar__input" />
                <button class="search-bar__submit">
                    <img src="<?= $base ?>assets/icons/search.svg" class="search-bar__icon icon" />
                </button>
            </div>
        </div>

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

        <div class="checkout-container">
            <div class="row gy-xl-3">

                <!-- LEFT -->
                <div class="col-8 col-xl-12">
                    <div class="cart-info">
                        <div class="cart-info__list">

                            <?php if (!empty($cart)): ?>
                                <?php foreach ($cart as $key => $item): ?>
                                    <article class="cart-item" id="item-<?= $key ?>">
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

                                                <!-- ✅ VARIANT -->
                                                <?php if (!empty($item['variant']) && !empty($item['variant']['name'])): ?>
                                                    <p>Size: <?= $item['variant']['name'] ?></p>
                                                <?php endif; ?>

                                                <!-- ✅ TOPPING -->
                                                <?php if (!empty($item['toppings'])): ?>
                                                    <p>
                                                        Topping:
                                                        <?= implode(', ', array_column($item['toppings'], 'name')) ?>
                                                    </p>
                                                <?php endif; ?>

                                                <p class="cart-item__price-wrap">
                                                    <?= vnd($item['price']) ?> |
                                                    <span class="cart-item__status">In Stock</span>
                                                </p>

                                                <div class="cart-item__ctrl cart-item__ctrl--md-block">
                                                    <div class="cart-item__input">

                                                        <button type="button" class="cart-item__input-btn minus" data-id="<?= $key ?>">
                                                            <img class="icon" src="<?= $base ?>assets/icons/minus.svg" />
                                                        </button>

                                                        <span id="qty-<?= $key ?>"><?= $item['quantity'] ?></span>

                                                        <button type="button" class="cart-item__input-btn plus" data-id="<?= $key ?>">
                                                            <img class="icon" src="<?= $base ?>assets/icons/plus.svg" />
                                                        </button>

                                                    </div>
                                                </div>
                                            </div>

                                            <div class="cart-item__content-right">
                                                <p class="cart-item__total-price" id="total-<?= $key ?>">
                                                    <?= vnd($item['price'] * $item['quantity']) ?>
                                                </p>

                                                <div class="cart-item__ctrl">
                                                    <button class="cart-item__ctrl-btn btn-save" data-id="<?= $key ?>">
                                                        <img src="<?= $base ?>assets/icons/heart-2.svg" />
                                                        Save
                                                    </button>

                                                    <button
                                                        type="button"
                                                        class="cart-item__ctrl-btn btn-delete js-toggle"
                                                        data-id="<?= $key ?>"
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
                                <p class="text-center py-5 fs-4 cart-info__list--empty">
                                    <img src="<?= $base ?>assets/img/empty-cart.png" alt="Empty cart" class="mb-4" />
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
                            <span id="cart-count"><?= $itemCount ?></span>
                        </div>

                        <div class="cart-info__row">
                            <span>Price (Total)</span>
                            <span id="cart-subtotal"><?= vnd($subtotal) ?></span>
                        </div>

                        <div class="cart-info__row">
                            <span>Shipping</span>
                            <span id="cart-shipping"><?= vnd($shipping) ?></span>
                        </div>

                        <div class="cart-info__separate"></div>

                        <div class="cart-info__row">
                            <span>Estimated Total</span>
                            <span id="cart-total"><?= vnd($total) ?></span>
                        </div>

                        <a href="<?= $base ?>index.php?url=shipping" class="cart-info__next-btn btn btn--primary btn--rounded">
                            Continue to checkout
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- MODAL -->
    <div id="delete-confirm" class="modal modal--small hide">
        <div class="modal__content">
            <p class="modal__text">Bạn có muốn xóa sản phẩm này khỏi giỏ hàng không?</p>
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
