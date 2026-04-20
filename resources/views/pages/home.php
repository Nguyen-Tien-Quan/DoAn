<?php
$products = $products ?? [];
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
$favIds = $favIds ?? [];
$categories = $categories ?? [];
$variants = $variants ?? [];
?>
<style>
/* ================= DARK THEME ================= */
html.dark {
    --filter-bg: rgba(17, 24, 39, 0.95);
    --filter-shadow: 0 8px 30px rgba(0,0,0,0.4);

    --filter-input-bg: #1f2937;
    --filter-input-text: #fff;

    --filter-label-color: #aaa;

    --filter-chip-bg: #1f2937;
    --filter-chip-text: #ddd;
    --filter-chip-hover: #374151;

    --filter-chip-active: linear-gradient(45deg, #6366f1, #ec4899);
    --filter-chip-active-text: #fff;
    --filter-chip-active-shadow: 0 0 10px rgba(236,72,153,0.5);

    --filter-clear-bg: #ef4444;
    --filter-clear-text: #fff;
}

/* ================= FILTER ================= */
.filter-pro {
    position: sticky;
    top: 80px;
    z-index: 50;

    display: flex;
    flex-wrap: wrap;
    gap: 12px;

    /* LIGHT fallback */
    background: var(--filter-bg, #ffffff);
    backdrop-filter: blur(10px);

    padding: 14px;
    border-radius: 16px;
    margin-bottom: 20px;

    box-shadow: var(--filter-shadow, 0 8px 30px rgba(0,0,0,0.08));
}

/* Search */
.filter-search {
    flex: 1 1 250px;
}

.filter-search input {
    width: 100%;
    padding: 10px 14px;
    border-radius: 12px;
    border: none;

    background: var(--filter-input-bg, #f1f5f9);
    color: var(--filter-input-text, #111);

    outline: none;
}

/* Chips */
.filter-chips {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.label {
    color: var(--filter-label-color, #666);
    font-size: 13px;
}

/* Chip */
.chip {
    background: var(--filter-chip-bg, #f1f5f9);
    border: 1px solid transparent;

    padding: 6px 12px;
    border-radius: 999px;

    color: var(--filter-chip-text, #333);
    font-size: 13px;

    cursor: pointer;
    transition: all 0.25s ease;
}

.chip:hover {
    background: var(--filter-chip-hover, #e2e8f0);
    transform: translateY(-1px);
}

.chip.active {
    background: var(--filter-chip-active, linear-gradient(45deg, #6366f1, #ec4899));
    color: var(--filter-chip-active-text, #fff);
    border: none;

    box-shadow: var(--filter-chip-active-shadow, 0 0 10px rgba(99,102,241,0.3));
}

/* Clear */
.clear-filter {
    margin-left: auto;

    background: var(--filter-clear-bg, #ef4444);
    border: none;

    padding: 8px 14px;
    border-radius: 10px;

    color: var(--filter-clear-text, #fff);
    cursor: pointer;
}

.clear-filter:hover {
    opacity: 0.9;
}


/* Tablet */
@media (max-width: 1024px) {
    .filter-pro {
        gap: 10px;
        padding: 12px;
    }

    .filter-search {
        flex: 1 1 200px;
    }

    .chip {
        font-size: 12px;
        padding: 5px 10px;
    }
}

.filter-scroll {
    display: flex;
    gap: 12px;
    overflow-x: auto;
    padding-top: 6px;
}

/* group */
.filter-group {
    display: flex;
    align-items: center;
    gap: 6px;
    flex: 0 0 auto;
}

/* mobile fix */
@media (max-width: 768px) {
    .filter-pro {
        flex-direction: column;
    }

    .filter-search {
        width: 100%;
    }

    .filter-scroll {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .filter-group {
        flex: 0 0 auto;
        white-space: nowrap;
    }
}

/* Mobile */
@media (max-width: 768px) {
    .filter-pro {
        position: sticky;
        top: 70px;

        display: flex;
        flex-wrap: nowrap;
        overflow-x: auto;

        padding: 8px;
        gap: 8px;

        border-radius: 12px;

        scroll-snap-type: x mandatory;
    }

    .filter-pro::-webkit-scrollbar {
        display: none;
    }

    /* Search nhỏ lại */
    .filter-search {
        flex: 0 0 65%;
        min-width: 200px;
    }

    .filter-search input {
        padding: 8px 10px;
        font-size: 13px;
    }

    /* Ẩn label cho gọn */
    .label {
        display: none;
    }

    /* Group filter = dạng button */
    .filter-chips {
        flex: 0 0 auto;
        display: flex;
        gap: 6px;
    }

    .chip {
        flex: 0 0 auto;
        white-space: nowrap;

        font-size: 12px;
        padding: 6px 10px;

        border-radius: 999px;
    }

    /* Clear */
    .clear-filter {
        flex: 0 0 auto;
        padding: 6px 10px;
        font-size: 12px;
    }
}

/* Animation */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
<main class="container home">

    <!-- Slideshow -->
    <div class="home__container">
        <div class="slideshow">
            <div class="slideshow__inner" id="slideshowInner">

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

            <div class="filter-pro">

                <!-- Search -->
                <div class="filter-search">
                    <input type="text" id="keyword" placeholder="🔍 Tìm sản phẩm...">
                </div>

                <!-- Wrapper scroll ngang -->
                <div class="filter-scroll">

                    <!-- Price -->
                    <div class="filter-group">
                        <span class="label">Giá</span>
                        <button class="chip" data-type="price" data-value="">Tất cả</button>
                        <button class="chip" data-type="price" data-value="0-50000">Dưới 50k</button>
                        <button class="chip" data-type="price" data-value="50000-100000">50k - 100k</button>
                        <button class="chip" data-type="price" data-value="100000-99999999">100k+</button>
                    </div>

                    <!-- Size -->
                    <div class="filter-group">
                        <span class="label">Size</span>
                        <button class="chip" data-type="size" data-value="">All</button>
                        <button class="chip" data-type="size" data-value="S">S</button>
                        <button class="chip" data-type="size" data-value="M">M</button>
                        <button class="chip" data-type="size" data-value="L">L</button>
                    </div>

                    <!-- Category -->
                    <div class="filter-group">
                        <span class="label">Danh mục</span>
                        <button class="chip" data-type="category" data-value="">Tất cả</button>

                        <?php foreach ($categories as $cat): ?>
                            <button class="chip"
                                    data-type="category"
                                    data-value="<?= $cat['id'] ?>">
                                <?= $cat['name'] ?>
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <!-- Sort -->
                    <div class="filter-group">
                        <span class="label">Sắp xếp</span>
                        <button class="chip" data-type="sort" data-value="">Mặc định</button>
                        <button class="chip" data-type="sort" data-value="price_asc">Giá ↑</button>
                        <button class="chip" data-type="sort" data-value="price_desc">Giá ↓</button>
                    </div>

                </div>

                <!-- Clear -->
                <button class="clear-filter">✖</button>

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

    // ========== APPLY COUPON ==========
function applyCouponCode(code) {
    const subtotalEl = document.querySelector('#cart-subtotal'); // phải có id này
    if (!subtotalEl) return;

    let subtotal = parseInt(subtotalEl.dataset.value || 0);

    fetch('index.php?url=applyCoupon', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `code=${encodeURIComponent(code)}&subtotal=${subtotal}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            updateCartSummary(data);
        } else {
            showToast(data.message || 'Mã không hợp lệ');
        }
    })
    .catch(() => {
        showToast('Lỗi khi áp dụng mã');
    });
}

function updateCartSummary(data) {
    const discountEl = document.querySelector('#cart-discount');
    const totalEl = document.querySelector('#cart-total');

    if (discountEl) {
        discountEl.innerText = data.formatted_discount;
    }

    if (totalEl) {
        totalEl.innerText = data.formatted_new_total;
    }
}
const state = {
    price: '',
    size: '',
    category: '',
    sort: '',
    keyword: ''
};

// ================= CLICK CHIP =================
document.querySelectorAll('.chip').forEach(btn => {
    btn.addEventListener('click', function () {

        const type = this.dataset.type;
        const value = this.dataset.value;

        // toggle
        if (state[type] === value) {
            state[type] = '';
            this.classList.remove('active');
        } else {
            state[type] = value;

            // remove active cùng group
            document.querySelectorAll(`.chip[data-type="${type}"]`)
                .forEach(c => c.classList.remove('active'));

            this.classList.add('active');
        }

        applyFilter();
    });
});

// ================= SEARCH =================
const keywordInput = document.getElementById('keyword');

let debounceTimer;
keywordInput.addEventListener('input', function () {
    state.keyword = this.value;

    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(applyFilter, 400);
});

// ================= CLEAR =================
document.querySelector('.clear-filter').addEventListener('click', () => {

    Object.keys(state).forEach(k => state[k] = '');

    document.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));

    keywordInput.value = '';

    applyFilter();
});

// ================= AJAX LOAD =================
let controller;

function applyFilter() {

    let params = new URLSearchParams();

    Object.entries(state).forEach(([k, v]) => {
        if (v) params.append(k, v);
    });

    const url = `index.php?url=home&ajax=1&${params.toString()}`;

    loadProductsAjax(url);
}

// ================= LOAD PRODUCTS =================
function loadProductsAjax(url) {

    const productList = document.getElementById('product-list');

    // loading overlay
    productList.style.opacity = "0.5";

    if (controller) controller.abort();
    controller = new AbortController();

    fetch(url, { signal: controller.signal })
        .then(res => res.text())
        .then(html => {

            const parser = new DOMParser();
            const doc = parser.parseFromString(html, "text/html");

            const newList = doc.getElementById("product-list");

            if (newList) {
                productList.innerHTML = newList.innerHTML;
            }

            productList.style.opacity = "1";

            // update URL (không reload)
            window.history.replaceState({}, '', url.replace('&ajax=1', ''));

        })
        .catch(err => {
            if (err.name !== "AbortError") {
                console.error(err);
            }
        });
}
</script>
