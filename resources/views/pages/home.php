<?php

    $products = $products ?? [];
    $page = $page ?? 1;
    $totalPages = $totalPages ?? 1;
    $favIds = $favIds ?? [];
    ?>

<style>
#product-list {
    position: relative;
    transition: opacity 0.25s ease, transform 0.25s ease;
}

/* blur nhẹ khi load */
#product-list.loading {
    filter: blur(2px);
}

/* overlay loading */
.ajax-overlay {
    position: absolute;
    inset: 0;
    background: rgba(255,255,255,0.6);
    backdrop-filter: blur(3px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
}

/* spinner đẹp hơn */
.ajax-overlay::after {
    content: "";
    width: 36px;
    height: 36px;
    border: 4px solid #ddd;
    border-top: 4px solid #ffb700;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}


</style>

<main class="container home">
    <!-- Slideshow -->
    <div class="home__container">
        <div class="slideshow">
            <div class="slideshow__inner" id="slideshowInner">
                <div class="slideshow__item">
                    <a href="#!" class="slideshow__link">
                        <picture>
                            <source media="(max-width: 767.98px)"
                                srcset="<?= $base ?>assets/img/slideshow/item-1-md.png" />
                            <img src="<?= $base ?>assets/img/slideshow/item-1.png" alt=""
                                class="slideshow__img" />
                        </picture>
                    </a>
                </div>
                <div class="slideshow__item">
                    <a href="#!" class="slideshow__link">
                        <img src="<?= $base ?>assets/img/slideshow/item-2.png" alt="anh 2"
                            class="slideshow__img" />
                    </a>
                </div>
                <div class="slideshow__item">
                    <a href="#!" class="slideshow__link">
                        <img src="<?= $base ?>assets/img/slideshow/item-3.png" alt=""
                            class="slideshow__img" />
                    </a>
                </div>
            </div>

            <div class="slideshow__control">
                <button class="slideshow__prev" id="prevBtn"></button>
                <button class="slideshow__next" id="nextBtn"></button>
            </div>

            <div class="slideshow__page">
                <span class="slideshow__number" id="current">1</span>
                <span class="slideshow__slider"></span>
                <span class="slideshow__number" id="total">5</span>
            </div>
        </div>
    </div>

    <!-- Browse Categories -->
    <section class="home__container">

        <div class="home__cate row row-cols-3 row-cols-md-1">
            <div class="col">
                <a href="#!">
                    <article class="cate-item">
                        <img src="<?= $base ?>assets/img/category-item/item-1.png" class="cate-item__thumb" />
                        <div class="cate-item__info">
                            <h3 class="cate-item__title">$24 - $150</h3>
                            <p class="cate-item__desc">New sumatra mandeling coffe blend</p>
                        </div>
                    </article>
                </a>
            </div>

            <div class="col">
                <a href="#!">
                    <article class="cate-item">
                        <img src="<?= $base ?>assets/img/category-item/item-2.png" class="cate-item__thumb" />
                        <div class="cate-item__info">
                            <h3 class="cate-item__title">$37 - $160</h3>
                            <p class="cate-item__desc">Espresso arabica and robusta beans</p>
                        </div>
                    </article>
                </a>
            </div>

            <div class="col">
                <a href="#!">
                    <article class="cate-item">
                        <img src="<?= $base ?>assets/img/category-item/item-3.png" class="cate-item__thumb" />
                        <div class="cate-item__info">
                            <h3 class="cate-item__title">$32 - $160</h3>
                            <p class="cate-item__desc">Lavazza top class whole bean coffee blend</p>
                        </div>
                    </article>
                </a>
            </div>
        </div>
    </section>

    <!-- Browse Products -->
    <section class="home__container">
        <div class="home__row">
            <h2 class="home__heading">Total LavAzza 1320</h2>
            <div class="filter-wrap">
                        <button class="filter-btn js-toggle" toggle-target="#home-filter">
                            Filter
                            <img src="./assets/icons/filter.svg" alt="" class="filter-btn__icon icon" />
                        </button>

                        <div id="home-filter" class="filter hide">
                            <img src="./assets/icons/arrow-up.png" alt="" class="filter__arrow" />
                            <h3 class="filter__heading">
                                Filter
                                <img
                                    src="./assets/icons/close.svg"
                                    alt=""
                                    class="d-none d-sm-block filter__btn-icon icon js-toggle"
                                    toggle-target="#home-filter"
                                />
                            </h3>
                            <form action="" class="filter__form form">
                                <div class="filter__row filter__content">
                                    <!-- Filter column 1 -->
                                    <div class="filter__col">
                                        <label for="" class="form__label">Price</label>
                                        <div class="filter__form-group">
                                            <div
                                                class="filter__form-slider"
                                                style="--min-value: 10%; --max-value: 60%"
                                            ></div>
                                        </div>
                                        <div class="filter__form-group filter__form-group--inline">
                                            <div>
                                                <label for="" class="form__label form__label--small"> Minimum </label>
                                                <div class="filter__form-text-input filter__form-text-input--small">
                                                    <input
                                                        type="text"
                                                        name=""
                                                        id=""
                                                        class="filter__form-input"
                                                        value="$30.00"
                                                    />
                                                </div>
                                            </div>
                                            <div>
                                                <label for="" class="form__label form__label--small"> Maximum </label>
                                                <div class="filter__form-text-input filter__form-text-input--small">
                                                    <input
                                                        type="text"
                                                        name=""
                                                        id=""
                                                        class="filter__form-input"
                                                        value="$100.00"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="filter__separate"></div>

                                    <!-- Filter column 2 -->
                                    <div class="filter__col">
                                        <label for="" class="form__label">Size/Weight</label>
                                        <div class="filter__form-group">
                                            <div class="form__select-wrap">
                                                <div class="form__select" style="--width: 158px">
                                                    500g
                                                    <img
                                                        src="./assets/icons/select-arrow.svg"
                                                        alt=""
                                                        class="form__select-arrow icon"
                                                    />
                                                </div>
                                                <div class="form__select">
                                                    Gram
                                                    <img
                                                        src="./assets/icons/select-arrow.svg"
                                                        alt=""
                                                        class="form__select-arrow icon"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="filter__form-group">
                                            <div class="form__tags">
                                                <button class="form__tag">Small</button>
                                                <button class="form__tag">Medium</button>
                                                <button class="form__tag">Large</button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="filter__separate"></div>

                                    <!-- Filter column 3 -->
                                    <div class="filter__col">
                                        <label for="" class="form__label">Brand</label>
                                        <div class="filter__form-group">
                                            <div class="filter__form-text-input">
                                                <input
                                                    type="text"
                                                    name=""
                                                    id=""
                                                    placeholder="Search brand name"
                                                    class="filter__form-input"
                                                />
                                                <img
                                                    src="./assets/icons/search.svg"
                                                    alt=""
                                                    class="filter__form-input-icon icon"
                                                />
                                            </div>
                                        </div>
                                        <div class="filter__form-group">
                                            <div class="form__tags">
                                                <button class="form__tag">Lavazza</button>
                                                <button class="form__tag">Nescafe</button>
                                                <button class="form__tag">Starbucks</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="filter__row filter__footer">
                                    <button class="btn btn--text filter__cancel js-toggle" toggle-target="#home-filter">
                                        Cancel
                                    </button>
                                    <button class="btn btn--primary filter__submit">Show Result</button>
                                </div>
                            </form>
                        </div>
            </div>
        </div>

        <!-- Products -->
        <div class="row row-cols-5 row-cols-lg-2 row-cols-sm-1 g-3" id="product-list">

            <?php $favIds = $favIds ?? [];
             foreach ($products as $product): ?>
                <div class="col">
                    <article class="product-card">
                        <div class="product-card__img-wrap">
                            <a href="index.php?url=product&id=<?= $product['id'] ?>">
                                <img src="<?= $base ?>assets/img/product/<?= $product['image'] ?>"
                                    class="product-card__thumb" />
                            </a>
                            <button class="like-btn product-card__like-btn <?= in_array($product['id'], $favIds) ? 'like-btn--liked' : '' ?>" data-id="<?= $product['id'] ?>">
                                <img src="<?= $base ?>assets/icons/heart.svg" class="like-btn__icon icon" />
                                <img src="<?= $base ?>assets/icons/heart-red.svg" class="like-btn__icon--liked" />
                            </button>
                        </div>

                        <h3 class="product-card__title">
                            <a href="index.php?url=product&id=<?= $product['id'] ?>">
                                <?= $product['name'] ?>
                            </a>
                        </h3>

                        <p class="product-card__brand">FastFood</p>

                        <div class="product-card__row">
                            <span class="product-card__price">
                                <?= number_format($product['base_price']) ?>đ
                            </span>
                            <img src="<?= $base ?>assets/icons/star.svg" class="product-card__star" />
                            <span>4.5</span>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>

        </div>
    </section>

    <!-- pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="pagination" id="pagination">

            <!-- PREV -->
            <?php if ($page > 1): ?>
                <a href="index.php?url=home&page=1">«</a>
                <a href="index.php?url=home&page=<?= $page - 1 ?>">‹</a>
            <?php endif; ?>

            <?php
                $start = max(1, $page - 2);
                $end = min($totalPages, $page + 2);
            ?>

            <!-- đầu -->
            <?php if ($start > 1): ?>
                <a href="index.php?url=home&page=1">1</a>
                <?php if ($start > 2): ?>
                    <span>...</span>
                <?php endif; ?>
            <?php endif; ?>

            <!-- loop -->
            <?php for ($i = $start; $i <= $end; $i++): ?>
                <a href="index.php?url=home&page=<?= $i ?>"
                class="<?= $i == $page ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <!-- cuối -->
            <?php if ($end < $totalPages): ?>
                <?php if ($end < $totalPages - 1): ?>
                    <span>...</span>
                <?php endif; ?>
                <a href="index.php?url=home&page=<?= $totalPages ?>">
                    <?= $totalPages ?>
                </a>
            <?php endif; ?>

            <!-- NEXT -->
            <?php if ($page < $totalPages): ?>
                <a href="index.php?url=home&page=<?= $page + 1 ?>">›</a>
                <a href="index.php?url=home&page=<?= $totalPages ?>">»</a>
            <?php endif; ?>

        </div>
    <?php endif; ?>
</main>
<script>
    window.addEventListener("template-loaded", handleActiveMenu);
</script>


<!-- ======================= JS thêm (AJAX pagination) ======================= -->
<script>
document.addEventListener("DOMContentLoaded", function () {

    const productList = document.getElementById("product-list");
    const pagination = document.getElementById("pagination");

    if (!pagination) return;

    pagination.addEventListener("click", function (e) {
        const link = e.target.closest("a");
        if (!link) return;

        e.preventDefault();

        const url = link.href;

        // 👉 tạo overlay loading (không xóa content)
        let overlay = document.createElement("div");
        overlay.className = "ajax-overlay";
        productList.appendChild(overlay);

        // 👉 hiệu ứng blur + tối nhẹ
        productList.classList.add("loading");

        fetch(url)
            .then(res => res.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, "text/html");

                const newList = doc.querySelector("#product-list");
                const newPagination = doc.querySelector("#pagination");

                // 👉 fade OUT
                productList.style.opacity = "0";

                setTimeout(() => {
                    if (newList) {
                        productList.innerHTML = newList.innerHTML;
                    }

                    if (newPagination) {
                        pagination.innerHTML = newPagination.innerHTML;
                    }

                    // 👉 fade IN
                    productList.style.opacity = "1";

                    productList.classList.remove("loading");

                    // remove overlay
                    overlay.remove();

                    // scroll mượt
                    window.scrollTo({
                        top: productList.offsetTop - 100,
                        behavior: "smooth"
                    });

                }, 250); // thời gian fade out
            });
    });

});
</script>
