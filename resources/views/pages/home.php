<?php
$products = $products ?? [];
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
$favIds = $favIds ?? [];
$categories = $categories ?? [];
$variants = $variants ?? [];
?>
<style>
    .filter-selected {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin: 10px 0;
}

.filter-chip {
    background: #f5f5f5;
    border-radius: 20px;
    padding: 4px 10px;
    font-size: 13px;
    cursor: pointer;
}

.filter-chip:hover {
    background: #ff4d4f;
    color: #fff;
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

    <!-- VOUCHER SECTION -->
    <div class="home__container voucher-home-section">
        <div class="voucher-header">
            <h2 class="home__heading">🎉 Mã giảm giá đang có</h2>
            <p class="voucher-sub">Nhấp vào mã để sao chép và sử dụng khi thanh toán</p>
        </div>
        <div id="voucher-list-container" class="voucher-list">
            <div class="loading-spinner">Đang tải mã khuyến mãi...</div>
        </div>
    </div>

    <!-- Browse Categories -->
    <section class="home__container">
        <div class="home__cate row row-cols-4 row-cols-md-1 cate-slider">
            <?php foreach ($categories as $cat): ?>
                <div class="col">
                    <a href="javascript:void(0)" class="category-link" data-cat-id="<?= $cat['id'] ?>">
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
                        <img src="./assets/icons/close.svg" alt="" class="d-none d-sm-block filter__btn-icon icon js-toggle" toggle-target="#home-filter" />
                    </h3>
                    <form action="" class="filter__form form" id="filter-form">
                        <div class="filter__row filter__content">
                            <!-- Price -->
                            <div class="filter__col">
                                <label class="form__label">Price</label>
                                <div class="filter__form-group">
                                    <div class="filter__form-slider" style="--min-value: 0%; --max-value: 70%"></div>
                                </div>
                                <div class="filter__form-group filter__form-group--inline">
                                    <div>
                                        <label class="form__label form__label--small">Minimum</label>
                                        <div class="filter__form-text-input filter__form-text-input--small">
                                            <input type="text" id="min_price" class="filter__form-input" value="0" />
                                        </div>
                                    </div>
                                    <div>
                                        <label class="form__label form__label--small">Maximum</label>
                                        <div class="filter__form-text-input filter__form-text-input--small">
                                            <input type="text" id="max_price" class="filter__form-input" value="200000" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="filter__separate"></div>

                            <!-- Size -->
                            <div class="filter__col">
                                <label class="form__label">Size</label>
                                <div class="filter__form-group">
                                    <div class="form__tags">
                                        <button type="button" class="form__tag size-option" data-size="S">Small</button>
                                        <button type="button" class="form__tag size-option" data-size="M">Medium</button>
                                        <button type="button" class="form__tag size-option" data-size="L">Large</button>
                                        <button type="button" class="form__tag size-option" data-size="XL">XL</button>
                                    </div>
                                </div>
                                <input type="hidden" id="size-input" name="size" value="">
                            </div>

                            <div class="filter__separate"></div>

                            <!-- Category -->
                            <div class="filter__col">
                                <label class="form__label">Danh mục</label>
                                <div class="filter__form-group">
                                    <div class="form__tags">
                                        <?php foreach ($categories as $cat): ?>
                                            <button type="button" class="form__tag category-option" data-id="<?= $cat['id'] ?>">
                                                <?= htmlspecialchars($cat['name']) ?>
                                            </button>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <input type="hidden" id="category-input" name="category" value="">
                            </div>

                            <div class="filter__separate"></div>

                            <!-- Sort -->
                            <div class="filter__col">
                                <label class="form__label">Sắp xếp</label>
                                <div class="filter__form-group">
                                    <select id="sort-select" class="form__select" name="sort">
                                        <option value="">Mặc định</option>
                                        <option value="price_asc">Giá thấp → cao</option>
                                        <option value="price_desc">Giá cao → thấp</option>
                                        <option value="name_asc">Tên A → Z</option>
                                        <option value="name_desc">Tên Z → A</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="filter__row filter__footer">
                            <button type="button" class="btn btn--text filter__cancel js-toggle" toggle-target="#home-filter">Cancel</button>
                            <button type="submit" class="btn btn--primary filter__submit">Show Result</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
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
                            <a href="index.php?url=product&id=<?= $product['id'] ?>"><?= htmlspecialchars($product['name']) ?></a>
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

<style>
    /* Voucher styles (giữ nguyên) */
    .voucher-home-section {
        margin: 30px 0;
        background: linear-gradient(135deg, #fff9e6 0%, #fff0d4 100%);
        border-radius: 24px;
        padding: 20px 24px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .voucher-header { text-align: center; margin-bottom: 20px; }
    .voucher-header .home__heading { font-size: 1.8rem; margin-bottom: 6px; color: #d32f2f; }
    .voucher-sub { font-size: 0.9rem; color: #666; }
    .voucher-list { display: flex; flex-wrap: wrap; justify-content: center; gap: 16px; }
    .voucher-card {
        background: white; border-radius: 60px; padding: 8px 20px 8px 24px;
        display: inline-flex; align-items: center; gap: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: transform 0.2s, box-shadow 0.2s; cursor: pointer; border: 1px solid #ffcd94;
    }
    .voucher-card:hover { transform: translateY(-3px); box-shadow: 0 6px 16px rgba(0,0,0,0.15); background: #fff5e8; }
    .voucher-code { font-weight: bold; font-size: 1.2rem; background: #ff6b6b; color: white; padding: 6px 12px; border-radius: 40px; letter-spacing: 1px; }
    .voucher-info { font-size: 0.9rem; color: #333; }
    .voucher-info strong { color: #d32f2f; }
    .copy-toast {
        position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%);
        background: #333; color: white; padding: 8px 16px; border-radius: 40px;
        font-size: 0.85rem; z-index: 1000; animation: fadeOut 2s forwards;
    }
    @keyframes fadeOut {
        0% { opacity: 1; }
        70% { opacity: 1; }
        100% { opacity: 0; visibility: hidden; }
    }
    .loading-spinner { text-align: center; color: #ff6b6b; font-style: italic; padding: 10px; }
    @keyframes spin { to { transform: rotate(360deg); } }
    .ajax-overlay {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(255,255,255,0.8); z-index: 100;
        display: flex; align-items: center; justify-content: center; border-radius: 12px;
    }
</style>

<script>
    // ========== VOUCHER ==========
    function loadHomeVouchers() {
        const container = document.getElementById('voucher-list-container');
        if (!container) return;
        fetch('index.php?url=getActiveCoupons')
            .then(res => res.json())
            .then(data => {
                if (data.success && data.coupons.length > 0) {
                    let html = '';
                    data.coupons.forEach(coupon => {
                        let discountText = coupon.type === 'percent'
                            ? `Giảm ${coupon.value}%`
                            : `Giảm ${Number(coupon.value).toLocaleString()}đ`;
                        if (coupon.max_discount && coupon.max_discount > 0) {
                            discountText += ` (tối đa ${Number(coupon.max_discount).toLocaleString()}đ)`;
                        }
                        if (coupon.min_order && coupon.min_order > 0) {
                            discountText += ` · Đơn tối thiểu ${Number(coupon.min_order).toLocaleString()}đ`;
                        }
                        html += `
                            <div class="voucher-card" data-code="${coupon.code}">
                                <span class="voucher-code">${coupon.code}</span>
                                <div class="voucher-info"><strong>${discountText}</strong></div>
                            </div>
                        `;
                    });
                    container.innerHTML = html;
                    document.querySelectorAll('.voucher-card').forEach(card => {
                        card.addEventListener('click', function() {
                            const code = this.getAttribute('data-code');
                            copyToClipboard(code);
                            showToast(`Đã sao chép mã: ${code}`);
                        });
                    });
                } else {
                    container.innerHTML = '<div class="loading-spinner">✨ Hiện chưa có mã khuyến mãi, hãy quay lại sau!</div>';
                }
            })
            .catch(() => {
                container.innerHTML = '<div class="loading-spinner">Không thể tải mã khuyến mãi, vui lòng thử lại sau.</div>';
            });
    }
    function copyToClipboard(text) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
    }
    function showToast(message) {
        let toast = document.querySelector('.copy-toast');
        if (toast) toast.remove();
        toast = document.createElement('div');
        toast.className = 'copy-toast';
        toast.innerText = message;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 2000);
    }

    // ========== FILTER & CATEGORY ==========
    function getCurrentFilterParams() {
        return {
            min_price: document.getElementById('min_price')?.value.trim() || '',
            max_price: document.getElementById('max_price')?.value.trim() || '',
            size: document.getElementById('size-input')?.value || '',
            category: document.getElementById('category-input')?.value || '',
            sort: document.getElementById('sort-select')?.value || '',
            keyword: document.querySelector('input[name="keyword"]')?.value.trim() || ''
        };
    }

    function buildFilterQueryString(extraParams = {}) {
        let params = { ...getCurrentFilterParams(), ...extraParams };
        Object.keys(params).forEach(key => {
            if (params[key] === '') delete params[key];
        });
        return new URLSearchParams(params).toString();
    }

    // Like buttons
    function attachLikeButtons() {
        document.querySelectorAll('.like-btn').forEach(btn => {
            const newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);
            newBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const productId = this.dataset.id;
                fetch(`index.php?url=like&id=${productId}`, { method: 'POST' })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'liked') this.classList.add('like-btn--liked');
                        else this.classList.remove('like-btn--liked');
                    })
                    .catch(console.error);
            });
        });
    }

    // AJAX load products
    let currentRequest = null;
    function loadProducts(url, updateHistory = true) {
        const productList = document.getElementById("product-list");
        const paginationDiv = document.getElementById("pagination");
        if (!productList) return;

        let overlay = document.createElement("div");
        overlay.className = "ajax-overlay";
        overlay.innerHTML = '<div style="width:40px;height:40px;border:4px solid #ccc; border-top-color:#333; border-radius:50%; animation:spin 0.6s linear infinite;"></div>';
        const container = productList.parentElement;
        container.style.position = "relative";
        container.appendChild(overlay);
        productList.style.opacity = "0.6";

        if (currentRequest) currentRequest.abort();
        currentRequest = new AbortController();

        fetch(url, { signal: currentRequest.signal })
            .then(res => res.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, "text/html");
                const newList = doc.getElementById("product-list");
                const newPagination = doc.getElementById("pagination");
                if (newList) productList.innerHTML = newList.innerHTML;
                if (newPagination && paginationDiv) paginationDiv.innerHTML = newPagination.innerHTML;
                productList.style.opacity = "1";
                attachLikeButtons();
                if (updateHistory) {
                    const newUrl = url.replace(/&?ajax=1/, '');
                    window.history.pushState({}, '', newUrl);
                }
            })
            .catch(err => {
                if (err.name === 'AbortError') return;
                console.error(err);
                alert("Không thể tải dữ liệu, vui lòng thử lại.");
            })
            .finally(() => {
                productList.style.opacity = "";
                overlay.remove();
                container.style.position = "";
                currentRequest = null;
            });
    }

    function bindPaginationEvents() {
        const pagination = document.getElementById("pagination");
        if (!pagination) return;
        pagination.addEventListener("click", function(e) {
            const link = e.target.closest("a");
            if (!link) return;
            e.preventDefault();
            const href = link.getAttribute("href");
            const urlParams = new URLSearchParams(href.split('?')[1] || '');
            let page = urlParams.get('page') || 1;
            const filterParams = getCurrentFilterParams();
            filterParams.page = page;
            const queryString = new URLSearchParams(filterParams).toString();
            const newUrl = `index.php?url=home&${queryString}`;
            loadProducts(newUrl, true);
            window.scrollTo({ top: document.querySelector('.home__container').offsetTop - 80, behavior: 'smooth' });
        });
    }

    // ========== FILTER LOGIC ==========
    (function() {
        const form = document.getElementById('filter-form');
        if (!form) return;

        const minPriceInput = document.getElementById('min_price');
        const maxPriceInput = document.getElementById('max_price');
        const pricePresetBtns = document.querySelectorAll('.price-preset');
        const sizeOptions = document.querySelectorAll('.size-option');
        const sizeHidden = document.getElementById('size-input');
        const cancelBtn = document.getElementById('filter-cancel-btn');
        const keywordInput = document.querySelector('input[name="keyword"]');
        const sortSelect = document.getElementById('sort-select');
        const categoryInput = document.getElementById('category-input');
        const categoryOptions = document.querySelectorAll('.category-option');

        function removePriceActiveClass() {
            pricePresetBtns.forEach(btn => btn.classList.remove('active'));
        }
        function syncPriceActiveFromInputs() {
            let minVal = minPriceInput.value === '' ? null : parseInt(minPriceInput.value, 10);
            let maxVal = maxPriceInput.value === '' ? null : parseInt(maxPriceInput.value, 10);
            if ((minVal === null || isNaN(minVal)) && (maxVal === null || isNaN(maxVal))) {
                removePriceActiveClass();
                return;
            }
            minVal = (minVal !== null && !isNaN(minVal)) ? minVal : 0;
            maxVal = (maxVal !== null && !isNaN(maxVal)) ? maxVal : Infinity;
            let matched = false;
            pricePresetBtns.forEach(btn => {
                const minPreset = parseInt(btn.getAttribute('data-min'), 10);
                const maxPreset = parseInt(btn.getAttribute('data-max'), 10);
                if (minVal === minPreset && maxVal === maxPreset) {
                    btn.classList.add('active');
                    matched = true;
                } else {
                    btn.classList.remove('active');
                }
            });
            if (!matched) removePriceActiveClass();
        }
        function syncSizeActiveFromInput() {
            const currentSize = sizeHidden.value;
            sizeOptions.forEach(btn => {
                const sizeValue = btn.getAttribute('data-size');
                if (currentSize && sizeValue === currentSize) btn.classList.add('active');
                else btn.classList.remove('active');
            });
        }
        function syncCategoryActiveFromInput() {
            const currentCat = categoryInput.value;
            categoryOptions.forEach(btn => {
                if (currentCat && btn.getAttribute('data-id') === currentCat) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });
        }

        // Price presets
        pricePresetBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                minPriceInput.value = this.getAttribute('data-min');
                maxPriceInput.value = this.getAttribute('data-max');
                removePriceActiveClass();
                this.classList.add('active');
                form.dispatchEvent(new Event('submit'));
            });
        });
        // Size options
        sizeOptions.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const selectedSize = this.getAttribute('data-size');
                if (sizeHidden.value === selectedSize) {
                    sizeHidden.value = '';
                    this.classList.remove('active');
                } else {
                    sizeHidden.value = selectedSize;
                    sizeOptions.forEach(opt => opt.classList.remove('active'));
                    this.classList.add('active');
                }
                syncSizeActiveFromInput();
                form.dispatchEvent(new Event('submit'));
            });
        });
        // Category options (trong filter)
        categoryOptions.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const catId = this.getAttribute('data-id');
                if (categoryInput.value === catId) {
                    categoryInput.value = '';
                    this.classList.remove('active');
                } else {
                    categoryOptions.forEach(opt => opt.classList.remove('active'));
                    categoryInput.value = catId;
                    this.classList.add('active');
                }
                form.dispatchEvent(new Event('submit'));
            });
        });
        // Category links from browse categories - GỌI TRỰC TIẾP LOADPRODUCTS
        document.querySelectorAll('.category-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const catId = this.getAttribute('data-cat-id');

                // Reset tất cả các bộ lọc khác về mặc định
                minPriceInput.value = '';
                maxPriceInput.value = '';
                sizeHidden.value = '';
                sortSelect.value = '';
                if (keywordInput) keywordInput.value = '';

                // Xóa active class của size và category cũ
                sizeOptions.forEach(opt => opt.classList.remove('active'));
                categoryOptions.forEach(opt => opt.classList.remove('active'));

                // Đặt category mới
                categoryInput.value = catId;
                // Active cho category vừa chọn
                categoryOptions.forEach(opt => {
                    if (opt.getAttribute('data-id') === catId) {
                        opt.classList.add('active');
                    }
                });

                // Đồng bộ giao diện giá, size
                syncPriceActiveFromInputs();
                syncSizeActiveFromInput();

                // Gọi trực tiếp loadProducts, không qua form submit (nhanh hơn)
                const queryString = buildFilterQueryString();
                const url = `index.php?url=home&ajax=1&${queryString}`;
                loadProducts(url, true);

                // Đóng filter nếu đang mở
                const filterDiv = document.getElementById('home-filter');
                if (filterDiv && filterDiv.classList.contains('show')) {
                    filterDiv.classList.remove('show');
                }

                // Cuộn đến sản phẩm
                document.querySelector('.home__container').scrollIntoView({ behavior: 'smooth' });
            });
        });

        // Price input debounce
        let priceTimeout;
        [minPriceInput, maxPriceInput].forEach(inp => {
            inp.addEventListener('input', () => {
                clearTimeout(priceTimeout);
                priceTimeout = setTimeout(() => {
                    syncPriceActiveFromInputs();
                    form.dispatchEvent(new Event('submit'));
                }, 500);
            });
        });
        // Keyword debounce
        let keywordTimeout;
        if (keywordInput) {
            keywordInput.addEventListener('input', () => {
                clearTimeout(keywordTimeout);
                keywordTimeout = setTimeout(() => {
                    form.dispatchEvent(new Event('submit'));
                }, 400);
            });
        }
        // Sort change
        if (sortSelect) {
            sortSelect.addEventListener('change', () => form.dispatchEvent(new Event('submit')));
        }
        // Reset filters (Cancel)
        function resetFiltersAndSubmit() {
            minPriceInput.value = '';
            maxPriceInput.value = '';
            sizeHidden.value = '';
            categoryInput.value = '';
            if (sortSelect) sortSelect.value = '';
            if (keywordInput) keywordInput.value = '';
            removePriceActiveClass();
            sizeOptions.forEach(btn => btn.classList.remove('active'));
            categoryOptions.forEach(btn => btn.classList.remove('active'));
            syncPriceActiveFromInputs();
            syncSizeActiveFromInput();
            form.dispatchEvent(new Event('submit'));
        }
        if (cancelBtn) {
            cancelBtn.addEventListener('click', (e) => {
                e.preventDefault();
                resetFiltersAndSubmit();
            });
        }
        // Submit filter
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            let minVal = minPriceInput.value.trim() === '' ? null : parseInt(minPriceInput.value, 10);
            let maxVal = maxPriceInput.value.trim() === '' ? null : parseInt(maxPriceInput.value, 10);
            if (minVal !== null && maxVal !== null && !isNaN(minVal) && !isNaN(maxVal) && minVal > maxVal) {
                alert('⚠️ Giá tối thiểu không thể lớn hơn giá tối đa.');
                return;
            }
            const queryString = buildFilterQueryString();
            const url = `index.php?url=home&ajax=1&${queryString}`;
            loadProducts(url, true);
            const filterDiv = document.getElementById('home-filter');
            if (filterDiv && filterDiv.classList.contains('show')) {
                filterDiv.classList.remove('show');
            }
        });

        // Khởi tạo active từ URL
        const urlParams = new URLSearchParams(window.location.search);
        const urlCat = urlParams.get('category');
        if (urlCat && categoryInput) {
            categoryInput.value = urlCat;
            syncCategoryActiveFromInput();
        }
        syncSizeActiveFromInput();
        syncPriceActiveFromInputs();
    })();

    function init() {
        attachLikeButtons();
        bindPaginationEvents();
        loadHomeVouchers();
        const observer = new MutationObserver(() => bindPaginationEvents());
        const paginationDiv = document.getElementById('pagination');
        if (paginationDiv) observer.observe(paginationDiv, { childList: true, subtree: true });
    }
    document.addEventListener("DOMContentLoaded", init);
</script>
