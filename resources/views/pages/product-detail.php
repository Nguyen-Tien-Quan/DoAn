<?php
// $product là array: ['id', 'name', 'image', 'base_price', 'description']
?>

<main class="product-page">
    <div class="container">

        <!-- Search bar -->
        <div class="product-container">
            <div class="search-bar d-none d-md-flex">
                <input type="text" placeholder="Search for item" class="search-bar__input" />
                <button class="search-bar__submit">
                    <img src="<?= $base ?>assets/icons/search.svg" class="search-bar__icon icon" />
                </button>
            </div>
        </div>

        <!-- Breadcrumbs -->
        <div class="product-container">
            <ul class="breadcrumbs">
                <li>
                    <a href="#!" class="breadcrumbs__link">
                        Departments
                        <img src="<?= $base ?>assets/icons/arrow-right.svg" />
                    </a>
                </li>
                <li>
                    <a href="#!" class="breadcrumbs__link">
                        Coffee
                        <img src="<?= $base ?>assets/icons/arrow-right.svg" />
                    </a>
                </li>
                <li>
                    <a href="#!" class="breadcrumbs__link">
                        Coffee Beans
                        <img src="<?= $base ?>assets/icons/arrow-right.svg" />
                    </a>
                </li>
                <li>
                    <a href="#!" class="breadcrumbs__link breadcrumbs__link--current">
                        <?= $product['name'] ?>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Product info -->
        <div class="product-container prod-info-content">
            <div class="row">

                <!-- LEFT: IMAGE -->
                <div class="col-5 col-xl-6 col-lg-12">
                    <div class="prod-preview">
                        <div class="prod-preview__list">
                            <div class="prod-preview__item">
                                <img src="<?= $base ?>assets/img/product/<?= $product['image'] ?>"
                                    class="prod-preview__img" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT -->
                <div class="col-7 col-xl-6 col-lg-12">
                    <section class="prod-info">

                        <!-- NAME -->
                        <h1 class="prod-info__heading">
                            <?= $product['name'] ?>
                        </h1>

                        <!-- PRICE -->
                        <div class="prod-info__card">
                            <div class="prod-info__row">
                                <span class="prod-info__price">
                                    <?= number_format($product['base_price']) ?>đ
                                </span>
                                <span class="prod-info__tax">10%</span>
                            </div>

                            <!-- TOTAL -->
                            <p class="prod-info__total-price">
                                <?= number_format($product['base_price'] * 1.1) ?>đ
                            </p>

                            <!-- ADD TO CART -->
                            <div class="prod-info__row">
                                <form class="add-cart-form d-flex align-items-center gap-3"
                                    action="<?= $base ?>index.php?url=add-cart&id=<?= $product['id'] ?>"
                                    method="POST">

                                    <!-- Quantity
                                    <div class="quantity-input">
                                        <button type="button" class="qty-btn minus">-</button>

                                        <input type="number" name="quantity" value="1" min="1"
                                            class="qty-input">

                                        <button type="button" class="qty-btn plus">+</button>
                                    </div> -->

                                    <button type="submit" onclick="addCart()"
                                        class="btn btn--primary prod-info__add-to-cart">
                                        Add to cart
                                    </button>

                                </form>
                            </div>
                        </div>

                        <!-- DESCRIPTION -->
                        <div class="text-content">
                            <h3>Description</h3>
                            <p>
                                <?= $product['description'] ?? 'No description' ?>
                            </p>
                        </div>

                    </section>
                </div>
            </div>
        </div>

    </div>
</main>
<script>
function addCart() {
    const isLogin = <?= isset($_SESSION['user']) ? 'true' : 'false' ?>;

    if (!isLogin) {
        alert("Chưa đăng nhập!");
        window.location.href = "index.php?url=login";
        return;
    }

    document.querySelector(".add-cart-form").submit();
}
</script>
