<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$base = '/DoAn/DoAnTotNghiep/public/';
$conn = getDB();

$user = $_SESSION['user'] ?? null;
$favorites = [];

if ($user) {
    $stmt = $conn->prepare("
        SELECT
            p.id,
            p.name,
            p.base_price,
            p.image,
            COALESCE(pv.stock_quantity, 0) AS stock_quantity,
            f.id AS fav_id
        FROM favorites f
        JOIN products p ON f.product_id = p.id
        LEFT JOIN (
            SELECT product_id, MIN(stock_quantity) as stock_quantity
            FROM product_variants
            GROUP BY product_id
        ) pv ON pv.product_id = p.id
        WHERE f.user_id = ?
        ORDER BY f.id DESC
    ");
    $stmt->execute([$user['id']]);
    $favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<main class="checkout-page">
    <div class="container">

        <!-- Breadcrumb -->
        <div class="checkout-container">
            <ul class="breadcrumbs checkout-page__breadcrumbs">
                <li>
                    <a href="<?= $base ?>" class="breadcrumbs__link">
                        Trang chủ
                        <img src="<?= $base ?>assets/icons/arrow-right.svg" alt="" />
                    </a>
                </li>
                <li>
                    <a href="#!" class="breadcrumbs__link breadcrumbs__link--current">Yêu thích</a>
                </li>
            </ul>
        </div>

        <!-- Content -->
        <div class="checkout-container">
            <div class="row gy-xl-3">
                <div class="col-12">
                    <div class="cart-info">

                        <h1 class="cart-info__heading">Danh sách yêu thích</h1>

                        <?php if(count($favorites) > 0): ?>

                            <p class="cart-info__desc">
                                Bạn có <?= count($favorites) ?> sản phẩm yêu thích
                            </p>

                            <!-- Check all -->
                            <div class="cart-info__check-all d-flex">
                                <label class="cart-info__checkbox d-flex">
                                    <input type="checkbox" id="check-all" class="cart-info__checkbox-input" />
                                    <span class="cart-item__title">Chọn tất cả</span>
                                </label>

                                <button id="delete-all-btn"
                                    class="btn btn--danger btn--small d-none">
                                    Xóa đã chọn
                                </button>
                            </div>

                            <!-- List -->
                            <div class="cart-info__list">
                                <?php foreach ($favorites as $item): ?>
                                <article class="cart-item" data-id="<?= $item['id'] ?>">

                                    <label class="cart-info__checkbox">
                                        <input type="checkbox" class="cart-info__checkbox-input item-checkbox"/>
                                    </label>

                                    <!-- FIX: dùng product_id -->
                                    <a href="<?= $base ?>index.php?url=product&id=<?= $item['id'] ?>">
                                        <img src="<?= $base ?>assets/img/product/<?= htmlspecialchars($item['image'] ?? 'placeholder.png') ?>"
                                             class="cart-item__thumb" />
                                    </a>

                                    <div class="cart-item__content">

                                        <div class="cart-item__content-left">
                                            <h3 class="cart-item__title">
                                                <a href="<?= $base ?>index.php?url=product&id=<?= $item['id'] ?>">
                                                    <?= htmlspecialchars($item['name']) ?>
                                                </a>
                                            </h3>

                                            <p class="cart-item__price-wrap">
                                                <?= vnd($item['base_price']) ?> |
                                                <span class="cart-item__status">
                                                    <?= $item['stock_quantity'] > 0 ? 'Còn hàng' : 'Hết hàng' ?>
                                                </span>
                                            </p>

                                            <div class="cart-item__ctrl-wrap">
                                                <div class="cart-item__ctrl">
                                                    <button
                                                        class="cart-item__ctrl-btn btn-delete-fav js-toggle"
                                                        toggle-target="#delete-fav-confirm"
                                                        data-id="<?= $item['fav_id'] ?>"
                                                    >
                                                        <img src="<?= $base ?>assets/icons/trash.svg" />
                                                        Xóa
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="cart-item__content-right">
                                            <p class="cart-item__total-price">
                                                <?= vnd($item['base_price']) ?>
                                            </p>

                                            <a href="<?= $base ?>index.php?url=checkout&product=<?= $item['id'] ?>"
                                               class="cart-item__checkout-btn btn btn--primary btn--rounded">
                                                Mua ngay
                                            </a>
                                        </div>
                                    </div>
                                </article>
                                <?php endforeach; ?>
                            </div>

                            <!-- Bottom -->
                            <div class="cart-info__bottom">
                                <div class="cart-info__row cart-info__row-md--block">

                                    <div class="cart-info__continue">
                                        <a href="<?= $base ?>" class="cart-info__continue-link">
                                            <img class="cart-info__continue-icon icon"
                                                 src="<?= $base ?>assets/icons/arrow-down-2.svg" />
                                            Tiếp tục mua hàng
                                        </a>
                                    </div>

                                    <a href="<?= $base ?>index.php?url=checkout"
                                       class="cart-info__checkout-all btn btn--primary btn--rounded">
                                        Thanh toán tất cả
                                    </a>
                                </div>
                            </div>

                        <?php else: ?>

                            <!-- Empty -->
                            <div class="favorites-empty text-center" style="padding: 50px 0;">
                                <img src="<?= $base ?>assets/img/empty-favorites.png"
                                     style="max-width: 200px; margin-bottom: 20px;">

                                <p style="font-size: 18px; color: #555;">
                                    Bạn chưa có sản phẩm yêu thích nào
                                </p>

                                <a href="<?= $base ?>" class="btn btn--primary btn--rounded mt-3">
                                    Khám phá sản phẩm
                                </a>
                            </div>

                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div id="delete-fav-confirm" class="modal modal--small hide">
            <div class="modal__content">
                <p class="modal__text">
                    Bạn có chắc muốn xóa sản phẩm này khỏi danh sách yêu thích?
                </p>
                <div class="modal__bottom">
                    <button class="btn btn--small btn--outline modal__btn js-toggle"
                        toggle-target="#delete-fav-confirm">
                        Hủy
                    </button>
                    <button class="btn btn--small btn--danger modal__btn"
                        id="confirm-delete-fav">
                        Xóa
                    </button>
                </div>
            </div>
            <div class="modal__overlay js-toggle"
                 toggle-target="#delete-fav-confirm"></div>
        </div>

</main>
