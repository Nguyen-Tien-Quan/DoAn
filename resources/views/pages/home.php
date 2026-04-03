
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
        <div class="row row-cols-5 row-cols-lg-2 row-cols-sm-1 g-3">

            <?php foreach ($products as $product): ?>
                <div class="col">
                    <article class="product-card">
                        <div class="product-card__img-wrap">
                            <a href="index.php?url=product&id=<?= $product['id'] ?>">
                                <img src="<?= $base ?>assets/img/product/<?= $product['image'] ?>"
                                    class="product-card__thumb" />
                            </a>
                            <button class="like-btn product-card__like-btn <?= in_array($product['id'], $favIds) ? 'liked' : '' ?>"
                                data-id="<?= $product['id'] ?>">
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
</main>
<script>
    window.addEventListener("template-loaded", handleActiveMenu);
</script>
<script>
document.querySelectorAll('.like-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const productId = this.dataset.id;
        fetch('index.php?url=add-favorite', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ product_id: productId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                this.classList.add('liked'); // bạn có thể toggle class để show heart-red
                console.log('Đã thêm vào favorites');
            } else {
                alert(data.message || 'Đã có trong favorites');
            }
        });
    });
});
</script>
