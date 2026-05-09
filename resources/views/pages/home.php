<?php
$products = $products ?? [];
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
$favIds = $favIds ?? [];
$categories = $categories ?? [];
$variants = $variants ?? [];
$allCategories = $allCategories ?? [];


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
        z-index: 2;
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        background: var(--filter-bg, #ffffff);
        backdrop-filter: blur(10px);
        padding: 18px;
        border-radius: 16px;
        margin-bottom: 20px;
        box-shadow: var(--filter-shadow, 0 8px 30px rgba(0,0,0,0.08));
        width: 100%;
        box-sizing: border-box;
    }

    .filter-search {
        flex: 1 1 40px;
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

    .filter-select {
        padding: 8px 12px;
        border-radius: 10px;
        border: 1px solid #ddd;
        background: var(--filter-input-bg, #f1f5f9);
        color: var(--filter-input-text, #111);
        font-size: 14px;
        outline: none;
        cursor: pointer;
        min-width: 160px;
        max-width: 200px;
        appearance: auto;
        -webkit-appearance: auto;
    }

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

    .filter-group {
        display: flex;
        align-items: center;
        gap: 6px;
        flex: 0 0 auto;
    }

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

        .filter-search {
            flex: 0 0 65%;
            min-width: 200px;
        }

        .filter-search input {
            padding: 8px 10px;
            font-size: 13px;
        }

        .label {
            display: none;
        }

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

        .clear-filter {
            flex: 0 0 auto;
            padding: 6px 10px;
            font-size: 12px;
        }
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


    @media (min-width: 769px) and (max-width: 1198.98px) {
        .filter-pro {
            flex-wrap: nowrap;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            padding: 12px;
            gap: 10px;
        }

        .filter-pro::-webkit-scrollbar {
            display: none;
        }

        .filter-search {
            flex: 0 0 auto;
            width: 200px;
            min-width: 160px;
        }

        .filter-scroll {
            flex: 1 1 auto;
            display: flex;
            gap: 10px;
            overflow-x: auto;
            white-space: nowrap;
        }

        .filter-scroll::-webkit-scrollbar {
            display: none;
        }

        .filter-group {
            flex: 0 0 auto;
            white-space: nowrap;
        }

        .clear-filter {
            flex-shrink: 0;
        }

        .label {
            display: none;
        }
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* ================= BROWSE CATEGORIES SLIDER ================= */
    .cate-slider-wrapper {
        position: relative;
    }

    .cate-slider {
        display: flex;
        flex-wrap: nowrap;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        -webkit-overflow-scrolling: touch;
        gap: 12px;
        padding-bottom: 10px;
        margin-left: -4px;
        margin-right: -4px;
    }

    .cate-slider::-webkit-scrollbar {
        display: none;
    }

    .cate-slider .col {
        flex: 0 0 auto;
        width: calc(25% - 12px);  /* 4 items trên desktop */
        scroll-snap-align: start;
    }

    @media (max-width: 768px) {
        .cate-slider .col {
            width: 100%; /* mobile hiển thị 1.5 items */
        }
    }

    /* Nút điều hướng */
    .cate-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        z-index: 2;
        background: rgba(255,255,255,0.9);
        border: 1px solid #ccc;
        border-radius: 50%;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        transition: all 0.2s;
        font-size: 18px;
        color: #333;
    }
    .cate-nav:hover {
        background: #fff;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }
    .cate-nav.prev { left: -10px; }
    .cate-nav.next { right: -10px; }

    /* Ẩn nút khi không cần (sẽ xử lý bằng JS) */
    .cate-nav.disabled {
        display: none;
    }

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

    .product-card__img-wrap {
    position: relative;
}

.product-discount-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    background: linear-gradient(45deg, #ef4444, #f97316);
    color: #fff;
    font-weight: bold;
    font-size: 12px;
    padding: 4px 8px;
    border-radius: 999px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    z-index: 2;
}

.product-sale-price {
    color: #ef4444;
    font-weight: 700;
    font-size: 14px;
}

.product-price-wrap {
    display: flex;
    flex-direction: column;
}

.product-card__price.old {
    text-decoration: line-through;
    font-size: 12px;
    color: #999;
}

.product-card__price.sale {
    color: #ef4444;
    font-weight: 700;
}

.product-discount-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    background: #ef4444;
    color: #fff;
    font-size: 12px;
    padding: 3px 8px;
    border-radius: 999px;
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
        <div class="cate-slider-wrapper">
            <button class="cate-nav prev" id="catePrev" style="display: none;">❮</button>
            <button class="cate-nav next" id="cateNext" style="display: none;">❯</button>
            <div class="cate-slider" id="cateSlider">
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
        </div>
    </section>

    <!-- Browse Products -->
    <section class="home__container">
        <div class="home__row">
            <div class="filter-pro">
                <div class="filter-search">
                    <input type="text" id="keyword" placeholder="🔍 Tìm sản phẩm...">
                </div>
                <div class="filter-scroll">
                    <div class="filter-group">
                        <span class="label">Giá</span>
                        <button class="chip" data-type="price" data-value="">Tất cả</button>
                        <button class="chip" data-type="price" data-value="0-50000">Dưới 50k</button>
                        <button class="chip" data-type="price" data-value="50000-100000">50k - 100k</button>
                        <button class="chip" data-type="price" data-value="100000-99999999">100k+</button>
                    </div>
                    <div class="filter-group">
                        <span class="label">Size</span>
                        <button class="chip" data-type="size" data-value="">All</button>
                        <button class="chip" data-type="size" data-value="S">S</button>
                        <button class="chip" data-type="size" data-value="M">M</button>
                        <button class="chip" data-type="size" data-value="L">L</button>
                    </div>
                    <div class="filter-group">
                        <span class="label">Danh mục</span>
                        <select id="category-select" class="filter-select">
                            <option value="">Tất cả</option>
                            <?php foreach ($allCategories as $cat): ?>
                                <option value="<?= $cat['id'] ?>">
                                    <?= str_repeat('—', $cat['depth'] ?? 0) . ' ' . htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <span class="label">Sắp xếp</span>
                        <button class="chip" data-type="sort" data-value="">Mặc định</button>
                        <button class="chip" data-type="sort" data-value="price_asc">Giá ↑</button>
                        <button class="chip" data-type="sort" data-value="price_desc">Giá ↓</button>
                    </div>
                </div>
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
                            <?php if (!empty($product['is_on_sale']) && $product['discount_percent'] > 0): ?>
                                <div class="product-discount-badge">
                                    -<?= (int)$product['discount_percent'] ?>%
                                </div>
                            <?php endif; ?>
                            <button class="like-btn product-card__like-btn <?= in_array($product['id'], $favIds) ? 'like-btn--liked' : '' ?>" data-id="<?= $product['id'] ?>">
                                <img src="<?= $base ?>assets/icons/heart.svg" class="like-btn__icon icon" />
                                <img src="<?= $base ?>assets/icons/heart-red.svg" class="like-btn__icon--liked" />
                            </button>
                        </div>
                        <h3 class="product-card__title">
                            <a href="index.php?url=product&id=<?= $product['id'] ?>"><?= htmlspecialchars($product['name']) ?></a>
                        </h3>
                        <?php if (!empty($product['brand'])): ?>
                            <p class="product-card__brand"><?= htmlspecialchars($product['brand']) ?></p>
                        <?php endif; ?>
                        <div class="product-card__row">

                            <?php if (!empty($product['is_on_sale']) && !empty($product['discount_percent'])): ?>
                                <div class="product-price-wrap">
                                    <span class="product-card__price old">
                                        <?= number_format($product['base_price'] ?? 0) ?>đ
                                    </span>

                                    <span class="product-card__price sale">
                                        <?= number_format($product['final_price'] ?? $product['base_price']) ?>đ
                                    </span>
                                </div>

                                <div class="product-discount-badge">
                                    -<?= (int)$product['discount_percent'] ?>%
                                </div>
                            <?php else: ?>
                                <span class="product-card__price">
                                    <?= number_format($product['base_price'] ?? 0) ?>đ
                                </span>
                            <?php endif; ?>

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
    // ========== TOAST & CLIPBOARD ==========
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

// ========== CATEGORY SLIDER ==========
function initCategorySlider() {
    const slider = document.getElementById('cateSlider');
    const prevBtn = document.getElementById('catePrev');
    const nextBtn = document.getElementById('cateNext');
    if (!slider || !prevBtn || !nextBtn) return;

    function updateButtons() {
        const maxScroll = slider.scrollWidth - slider.clientWidth;
        if (maxScroll <= 0) {
            prevBtn.style.display = 'none';
            nextBtn.style.display = 'none';
        } else {
            prevBtn.style.display = 'block';
            nextBtn.style.display = 'block';
        }
    }

    prevBtn.addEventListener('click', () => {
        slider.scrollBy({ left: -250, behavior: 'smooth' });
    });

    nextBtn.addEventListener('click', () => {
        slider.scrollBy({ left: 250, behavior: 'smooth' });
    });

    // ================= AUTO SCROLL =================
    let autoScroll = setInterval(() => {
        const maxScroll = slider.scrollWidth - slider.clientWidth;

        if (slider.scrollLeft >= maxScroll) {
            slider.scrollTo({ left: 0, behavior: 'smooth' });
        } else {
            slider.scrollBy({ left: 250, behavior: 'smooth' });
        }
    }, 3000); // 3 giây chạy 1 lần

    // Pause khi hover
    slider.addEventListener('mouseenter', () => {
        clearInterval(autoScroll);
    });

    slider.addEventListener('mouseleave', () => {
        autoScroll = setInterval(() => {
            const maxScroll = slider.scrollWidth - slider.clientWidth;

            if (slider.scrollLeft >= maxScroll) {
                slider.scrollTo({ left: 0, behavior: 'smooth' });
            } else {
                slider.scrollBy({ left: 250, behavior: 'smooth' });
            }
        }, 3000);
    });

    slider.addEventListener('scroll', updateButtons);
    window.addEventListener('resize', updateButtons);

    updateButtons();
}

// ========== LIKE BUTTONS ==========
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

// ========== FILTER STATE ==========
const filterState = {
    price: '',
    size: '',
    category: '',
    sort: '',
    keyword: ''
};

// 🔥 LẤY PARAM TỪ URL -> ĐỔ VÀO FILTER
const urlParams = new URLSearchParams(window.location.search);

filterState.keyword = urlParams.get('keyword') || '';
filterState.category = urlParams.get('category') || '';
filterState.sort = urlParams.get('sort') || '';
filterState.size = urlParams.get('size') || '';

// price dạng 0-50000
const price = urlParams.get('price');
if (price) filterState.price = price;

// 👉 set lại UI
if (filterState.keyword) {
    document.getElementById('keyword').value = filterState.keyword;
}
if (filterState.category) {
    document.getElementById('category-select').value = filterState.category;
}

// active chip
document.querySelectorAll('.chip').forEach(chip => {
    if (chip.dataset.value === filterState[chip.dataset.type]) {
        chip.classList.add('active');
    }
});

function applyFilter(page = 1) {
    let params = new URLSearchParams();

    Object.entries(filterState).forEach(([key, value]) => {
        if (!value) return;

        // 🔥 FIX PRICE
        if (key === 'price') {
            const [min, max] = value.split('-');
            if (min) params.append('min_price', min);
            if (max) params.append('max_price', max);
        } else {
            params.append(key, value);
        }
    });

    if (page > 1) params.append('page', page);

    const url = `index.php?url=home&ajax=1&${params.toString()}`;
    loadProductsAjax(url);

    const cleanUrl = `index.php?url=home&${params.toString()}`;
    window.history.replaceState({}, '', cleanUrl);
}

// ========== CHIPS ==========
document.querySelectorAll('.chip').forEach(btn => {
    btn.addEventListener('click', function () {
        const type = this.dataset.type;
        const value = this.dataset.value;

        if (type === 'category') return; // bỏ qua chip category vì đã dùng select

        if (filterState[type] === value) {
            filterState[type] = '';
            this.classList.remove('active');
        } else {
            filterState[type] = value;
            document.querySelectorAll(`.chip[data-type="${type}"]`).forEach(c => c.classList.remove('active'));
            this.classList.add('active');
        }
        applyFilter();
    });
});

// ========== CATEGORY SELECT ==========
const categorySelect = document.getElementById('category-select');
if (categorySelect) {
    categorySelect.addEventListener('change', function () {
        filterState.category = this.value;
        applyFilter();
    });
}

// ========== SEARCH ==========
const keywordInput = document.getElementById('keyword');
let debounceTimer;
keywordInput.addEventListener('input', function () {
    filterState.keyword = this.value;
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(applyFilter, 400);
});

// ========== CLEAR ==========
document.querySelector('.clear-filter').addEventListener('click', () => {
    Object.keys(filterState).forEach(k => filterState[k] = '');
    document.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
    keywordInput.value = '';
    if (categorySelect) categorySelect.value = '';
    applyFilter();
});

// Category link ở Browse Categories
document.querySelectorAll('.category-link').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const catId = this.getAttribute('data-cat-id');

        Object.keys(filterState).forEach(k => filterState[k] = '');
        document.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
        keywordInput.value = '';

        filterState.category = catId;
        if (categorySelect) categorySelect.value = catId;

        applyFilter();
        document.querySelector('.home__container').scrollIntoView({ behavior: 'smooth' });
    });
});

// ========== AJAX LOAD PRODUCTS ==========
let ajaxController;
function loadProductsAjax(url) {
    const productList = document.getElementById('product-list');
    const paginationDiv = document.getElementById('pagination');
    if (!productList) return;

    productList.style.opacity = '0.5';

    if (ajaxController) ajaxController.abort();
    ajaxController = new AbortController();

    fetch(url, { signal: ajaxController.signal })
        .then(res => res.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            const newList = doc.getElementById('product-list');
            const newPagination = doc.getElementById('pagination');

            if (newList) productList.innerHTML = newList.innerHTML;
            if (newPagination && paginationDiv) paginationDiv.innerHTML = newPagination.innerHTML;

            productList.style.opacity = '1';
            attachLikeButtons();
            bindPaginationEvents();
        })
        .catch(err => {
            if (err.name !== 'AbortError') console.error(err);
            productList.style.opacity = '1';
        });
}

// ========== PAGINATION CLICK ==========
function bindPaginationEvents() {
    const pagination = document.getElementById('pagination');
    if (!pagination) return;

    pagination.addEventListener('click', function(e) {
        const link = e.target.closest('a');
        if (!link) return;

        e.preventDefault();
        const href = link.getAttribute('href');
        const urlParams = new URLSearchParams(href.split('?')[1] || '');
        const page = urlParams.get('page') || 1;

        applyFilter(page);
        window.scrollTo({ top: document.querySelector('.home__container').offsetTop - 80, behavior: 'smooth' });
    });
}

// ========== APPLY COUPON ==========
function applyCouponCode(code) {
    const subtotalEl = document.querySelector('#cart-subtotal');
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
    if (discountEl) discountEl.innerText = data.formatted_discount;
    if (totalEl) totalEl.innerText = data.formatted_new_total;
}

// ========== INIT ==========
document.addEventListener('DOMContentLoaded', () => {
    attachLikeButtons();
    bindPaginationEvents();
    loadHomeVouchers();
    initCategorySlider();
});
</script>
