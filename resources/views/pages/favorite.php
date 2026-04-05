<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// BASE URL
$base = '/DoAn/DoAnTotNghiep/public/';

$conn = getDB();

// Kiểm tra user đã login chưa
$user = $_SESSION['user'] ?? null;

// Lấy danh sách yêu thích
$favorites = [];

if ($user) {
    $stmt = $conn->prepare("
        SELECT
            p.id,
            p.name,
            p.base_price,
            p.image,
            pv.stock_quantity,
            f.id AS fav_id
        FROM favorites f
        JOIN products p ON f.product_id = p.id
        LEFT JOIN (
            SELECT product_id, MIN(stock_quantity) as stock_quantity
            FROM product_variants
            GROUP BY product_id
        ) pv ON pv.product_id = p.id
        WHERE f.user_id = ?
    ");

    $stmt->execute([$user['id']]);
    $favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!-- MAIN -->
<main class="checkout-page">
    <div class="container">

        <!-- Breadcrumbs -->
        <div class="checkout-container">
            <ul class="breadcrumbs checkout-page__breadcrumbs">
                <li>
                    <a href="<?= $base ?>" class="breadcrumbs__link">
                        Home
                        <img src="<?= $base ?>assets/icons/arrow-right.svg" alt="" />
                    </a>
                </li>
                <li>
                    <a href="#!" class="breadcrumbs__link breadcrumbs__link--current">Favorite</a>
                </li>
            </ul>
        </div>

        <!-- Favorites content -->
        <div class="checkout-container">
            <div class="row gy-xl-3">
                <div class="col-12">
                    <div class="cart-info">
                        <h1 class="cart-info__heading">Favorite List</h1>

                        <?php if(count($favorites) > 0): ?>
                            <p class="cart-info__desc"><?= count($favorites) ?> items</p>

                            <div class="cart-info__check-all d-flex ">
                                <label class="cart-info__checkbox d-flex">
                                    <input type="checkbox" id="check-all" class="cart-info__checkbox-input" />
                                    <span class="cart-item__title">Check all</span>
                                </label>

                                <!-- 👉 NÚT DELETE ALL -->
                                <button
                                    id="delete-all-btn"
                                    class="btn btn--danger btn--small d-none"
                                >
                                    Delete All
                                </button>
                            </div>

                            <div class="cart-info__list">
                                <?php foreach ($favorites as $item): ?>
                                <article class="cart-item" data-id="<?= $item['id'] ?>">
                                    <label class="cart-info__checkbox">
                                        <input type="checkbox" class="cart-info__checkbox-input"  />
                                    </label>
                                    <a href="<?= $base ?>index.php?url=product&id=<?= $item['id'] ?>">
                                        <img src="<?= $base ?>assets/img/product/<?= htmlspecialchars($item['image'] ?? 'placeholder.png') ?>" alt="" class="cart-item__thumb" />
                                    </a>
                                    <div class="cart-item__content">
                                        <div class="cart-item__content-left">
                                            <h3 class="cart-item__title">
                                                <a href="<?= $base ?>index.php?url=product&id=<?= $item['id'] ?>">
                                                    <?= htmlspecialchars($item['name']) ?>
                                                </a>
                                            </h3>
                                            <p class="cart-item__price-wrap">
                                                $<?= vnd($item['base_price']) ?> |
                                                <span class="cart-item__status"><?= ($item['stock_quantity'] ?? 0) > 0 ? 'In Stock' : 'Out of Stock' ?></span>
                                            </p>
                                            <div class="cart-item__ctrl-wrap">
                                                <div class="cart-item__ctrl cart-item__ctrl--md-block">
                                                    <div class="cart-item__input">
                                                        <?= htmlspecialchars($item['brand'] ?? 'Brand') ?>
                                                        <img class="icon" src="<?= $base ?>assets/icons/arrow-down-2.svg" alt="" />
                                                    </div>
                                                    <!-- <div class="cart-item__input">
                                                        <button class="cart-item__input-btn"><img class="icon" src="<?= $base ?>assets/icons/minus.svg" alt="" /></button>
                                                        <span>1</span>
                                                        <button class="cart-item__input-btn"><img class="icon" src="<?= $base ?>assets/icons/plus.svg" alt="" /></button>
                                                    </div> -->
                                                </div>
                                                <div class="cart-item__ctrl">
                                                    <!-- <button class="cart-item__ctrl-btn">
                                                        <img src="<?= $base ?>assets/icons/heart-2.svg" alt="" /> Save
                                                    </button> -->
                                                    <button class="cart-item__ctrl-btn btn-delete-fav js-toggle" toggle-target="#delete-fav-confirm">
                                                        <img src="./assets/icons/trash.svg" alt="" />
                                                        Delete
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="cart-item__content-right">
                                            <p class="cart-item__total-price">$<?=  vnd($item['base_price']) ?></p>
                                            <button class="cart-item__checkout-btn btn btn--primary btn--rounded">
                                                Check Out
                                            </button>
                                        </div>
                                    </div>
                                </article>
                                <?php endforeach; ?>
                            </div>

                            <div class="cart-info__bottom">
                                <div class="cart-info__row cart-info__row-md--block">
                                    <div class="cart-info__continue">
                                        <a href="<?= $base ?>" class="cart-info__continue-link">
                                            <img class="cart-info__continue-icon icon" src="<?= $base ?>assets/icons/arrow-down-2.svg" alt="" />
                                            Continue Shopping
                                        </a>
                                    </div>
                                    <a href="<?= $base ?>index.php?url=checkout" class="cart-info__checkout-all btn btn--primary btn--rounded">
                                        All Check Out
                                    </a>
                                </div>
                            </div>

                        <?php else: ?>
                            <!-- Khi không có item nào -->
                            <div class="favorites-empty text-center" style="padding: 50px 0;">
                                <img src="<?= $base ?>assets/img/empty-favorites.png" alt="No Favorites" style="max-width: 200px; margin-bottom: 20px;">
                                <p style="font-size: 18px; color: #555; margin-bottom: 20px;">Bạn ko có bất kỳ sản phẩm nào trong danh sách yêu thích.</p>
                                <a href="<?= $base ?>" class="btn btn--primary btn--rounded mt-3">Explore Products</a>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>

        <div id="delete-fav-confirm" class="modal modal--small hide">
            <div class="modal__content">
                <p class="modal__text">Bạn có chắc muốn xóa sản phẩm khỏi yêu thích?</p>
                <div class="modal__bottom">
                    <button class="btn btn--small btn--outline modal__btn js-toggle" toggle-target="#delete-confirm">
                        Cancel
                    </button>
                    <button class="btn btn--small btn--danger modal__btn btn--no-margin" id="confirm-delete-fav">
                        Delete
                    </button>
                </div>
            </div>
            <div class="modal__overlay js-toggle" toggle-target="#delete-confirm"></div>
        </div>
</main>
