<?php
$products = $products ?? [];
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
$favIds = $favIds ?? [];
$categories = $categories ?? []; // danh mục từ DB
$categories = $categories ?? [];
$variants = $variants ?? [];
?>

<style>
#product-list {
    position: relative;
    transition: opacity 0.25s ease, transform 0.25s ease;
}
#product-list.loading {
    filter: blur(2px);
}
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
.ajax-overlay::after {
    content: "";
    width: 36px;
    height: 36px;
    border: 4px solid #ddd;
    border-top: 4px solid #ffb700;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Vuốt ngang đẹp hơn */
.cate-slider {
    display: flex;
    flex-wrap: nowrap;
    overflow-x: auto;
    gap: 20px;
    padding-bottom: 10px;

    scroll-behavior: smooth;
    scroll-snap-type: x mandatory; /* snap ngang */
}

/* Ẩn scrollbar trên PC */
.cate-slider::-webkit-scrollbar {
    display: none;
}
.cate-slider {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

/* Snap cho từng item */
.cate-slider > .col {
    flex: 0 0 calc(33.333% - 20px);
    max-width: calc(33.333% - 20px);
    scroll-snap-align: start;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}


@media (max-width: 992px) {
    .cate-slider > .col {
        flex: 0 0 50%;
        max-width: 50%;
    }
}

@media (max-width: 576px) {
    .cate-slider > .col {
        flex: 0 0 100%;
        max-width: 100%;
    }
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
                            <source media="(max-width: 767.98px)" srcset="<?= $base ?>assets/img/slideshow/item-1-md.png" />
                            <img src="<?= $base ?>assets/img/slideshow/item-1.png" alt="" class="slideshow__img" />
                        </picture>
                    </a>
                </div>
                <div class="slideshow__item">
                    <a href="#!" class="slideshow__link">
                        <img src="<?= $base ?>assets/img/slideshow/item-2.png" alt="anh 2" class="slideshow__img" />
                    </a>
                </div>
                <div class="slideshow__item">
                    <a href="#!" class="slideshow__link">
                        <img src="<?= $base ?>assets/img/slideshow/item-3.png" alt="" class="slideshow__img" />
                    </a>
                </div>
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
        <div class="home__cate row row-cols-4 row-cols-md-1 cate-slider">
            <?php foreach ($categories as $cat): ?>
                <div class="col">
                    <a href="index.php?url=home&category=<?= $cat['id'] ?>">
                        <article class="cate-item">
                            <img src="<?= $base ?>assets/img/category-item/<?= $cat['image'] ?>" class="cate-item__thumb" />
                            <div class="cate-item__info">
                                <h3 class="cate-item__title"><?= htmlspecialchars($cat['name']) ?></h3>
                                <p class="cate-item__desc"><?= htmlspecialchars($cat['description'] ?? '') ?></p>
                            </div>
                        </article>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Browse Products -->
    <section class="home__container">
        <div class="home__row">
            <h2 class="home__heading">Total Products</h2>
            <div class="filter-wrap">
                <button class="filter-btn js-toggle" toggle-target="#home-filter">
                    Filter
                    <img src="<?= $base ?>assets/icons/filter.svg" alt="" class="filter-btn__icon icon" />
                </button>

                <div id="home-filter" class="filter hide">
                    <!-- <img src="<?= $base ?>assets/icons/arrow-up.png" alt="" class="filter__arrow" /> -->
                        <h3 class="filter__heading">
                            Filter
                            <img
                                src="<?= $base ?>assets/icons/close.svg"
                                alt=""
                                class="d-none d-sm-block filter__btn-icon icon js-toggle"
                                toggle-target="#home-filter"
                                />
                        </h3>

                        <form action="" class="filter__form form">
                            <div class="filter__row filter__content">

                                <!-- PRICE -->
                                <div class="filter__col">
                                    <label class="form__label">Price</label>

                                    <div class="filter__form-group filter__form-group--inline">
                                        <div>
                                            <label class="form__label form__label--small">Minimum</label>
                                            <div class="filter__form-text-input filter__form-text-input--small">
                                                <input type="number" name="min_price" class="filter__form-input" placeholder="0">
                                            </div>
                                        </div>

                                        <div>
                                            <label class="form__label form__label--small">Maximum</label>
                                            <div class="filter__form-text-input filter__form-text-input--small">
                                                <input type="number" name="max_price" class="filter__form-input" placeholder="100000">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="filter__separate"></div>

                                <!-- SIZE -->
                                <div class="filter__col">
                                    <label class="form__label">Size</label>

                                    <div class="filter__form-group">
                                        <div class="form__tags">
                                            <?php foreach ($variants as $v): ?>
                                                <button
                                                    type="button"
                                                    class="form__tag size-option"
                                                    data-size="<?= $v ?>"
                                                >
                                                    <?= htmlspecialchars($v) ?>
                                                </button>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>

                                    <input type="hidden" name="size" id="size-input" />
                                </div>

                                <div class="filter__separate"></div>

                                <!-- SEARCH -->
                                <div class="filter__col">
                                    <label class="form__label">Search</label>

                                    <div class="filter__form-group">
                                        <div class="filter__form-text-input">
                                            <input
                                                type="text"
                                                name="keyword"
                                                placeholder="Search product..."
                                                class="filter__form-input"
                                            />
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="filter__row filter__footer">
                                <button type="button" class="btn btn--text filter__cancel js-toggle" toggle-target="#home-filter">
                                    Cancel
                                </button>
                                <button type="submit" class="btn btn--primary filter__submit">
                                    Apply
                                </button>
                            </div>
                        </form>
                    </div>
            </div>
        </div>

        <!-- Products -->
        <div class="row row-cols-5 row-cols-lg-2 row-cols-sm-1 g-3" id="product-list">
            <?php foreach ($products as $product): ?>
                <div class="col">
                    <article class="product-card">
                        <div class="product-card__img-wrap">
                            <a href="index.php?url=product&id=<?= $product['id'] ?>">
                                <img src="<?= $base ?>assets/img/product/<?= $product['image'] ?>" class="product-card__thumb" />
                            </a>
                            <button class="like-btn product-card__like-btn <?= in_array($product['id'], $favIds) ? 'like-btn--liked' : '' ?>" data-id="<?= $product['id'] ?>">
                                <img src="<?= $base ?>assets/icons/heart.svg" class="like-btn__icon icon" />
                                <img src="<?= $base ?>assets/icons/heart-red.svg" class="like-btn__icon--liked" />
                            </button>
                        </div>

                        <h3 class="product-card__title">
                            <a href="index.php?url=product&id=<?= $product['id'] ?>">
                                <?= htmlspecialchars($product['name']) ?>
                            </a>
                        </h3>

                        <p class="product-card__brand"><?= htmlspecialchars($product['brand'] ?? 'Brand') ?></p>

                        <div class="product-card__row">
                            <span class="product-card__price"><?= number_format($product['base_price'] ?? 0) ?>đ</span>
                            <img src="<?= $base ?>assets/icons/star.svg" class="product-card__star" />
                            <span><?= $product['rating'] ?? '4.5' ?></span>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="pagination" id="pagination">
            <?php if ($page > 1): ?>
                <a href="index.php?url=home&page=1">«</a>
                <a href="index.php?url=home&page=<?= $page - 1 ?>">‹</a>
            <?php endif; ?>

            <?php
            $start = max(1, $page - 2);
            $end = min($totalPages, $page + 2);
            if ($start > 1) echo '<a href="index.php?url=home&page=1">1</a>' . ($start > 2 ? '<span>...</span>' : '');
            for ($i = $start; $i <= $end; $i++): ?>
                <a href="index.php?url=home&page=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor;
            if ($end < $totalPages) echo ($end < $totalPages - 1 ? '<span>...</span>' : '') . '<a href="index.php?url=home&page=' . $totalPages . '">' . $totalPages . '</a>';
            if ($page < $totalPages): ?>
                <a href="index.php?url=home&page=<?= $page + 1 ?>">›</a>
                <a href="index.php?url=home&page=<?= $totalPages ?>">»</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</main>

<script>
    window.addEventListener("template-loaded", handleActiveMenu);

    // AJAX Pagination
    document.addEventListener("DOMContentLoaded", function () {
    const productList = document.getElementById("product-list");
    const pagination = document.getElementById("pagination");
    if (!pagination) return;

    pagination.addEventListener("click", function (e) {
        const link = e.target.closest("a");
        if (!link) return;
        e.preventDefault();

        const url = link.href;

        let overlay = document.createElement("div");
        overlay.className = "ajax-overlay";
        productList.appendChild(overlay);
        productList.classList.add("loading");

        fetch(url)
            .then(res => res.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, "text/html");
                const newList = doc.querySelector("#product-list");
                const newPagination = doc.querySelector("#pagination");

                productList.style.opacity = "0";
                setTimeout(() => {
                    if (newList) productList.innerHTML = newList.innerHTML;
                    if (newPagination) pagination.innerHTML = newPagination.innerHTML;

                    productList.style.opacity = "1";
                    productList.classList.remove("loading");
                    overlay.remove();
                    window.scrollTo({ top: productList.offsetTop - 100, behavior: "smooth" });
                }, 250);
            });
        });
    });

    // ====================== FILTER ======================

    // chọn size
    document.querySelectorAll(".size-option").forEach(btn => {
        btn.addEventListener("click", function () {
            document.querySelectorAll(".size-option").forEach(b => b.classList.remove("active"));
            this.classList.add("active");

            document.getElementById("size-input").value = this.dataset.size;
        });
    });

    // submit filter (AJAX)
    document.querySelector(".filter__form").addEventListener("submit", function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    const params = new URLSearchParams(formData).toString();

    const url = `index.php?url=home&ajax=1&${params}`;

    const productList = document.getElementById("product-list");

    let overlay = document.createElement("div");
    overlay.className = "ajax-overlay";
    productList.appendChild(overlay);
    productList.classList.add("loading");

    fetch(url)
        .then(res => res.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, "text/html");

            const newList = doc.querySelector("#product-list");

            if (newList) {
                productList.innerHTML = newList.innerHTML;
            }

            productList.classList.remove("loading");
            overlay.remove();
        });
});
</script>
