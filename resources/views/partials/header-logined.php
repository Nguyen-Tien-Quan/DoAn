<?php
// Đảm bảo có session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user = $_SESSION['user'] ?? null;

// Helper format tiền
if (!function_exists('vnd')) {
    function vnd($amount) {
        return number_format($amount) . 'đ';
    }
}

// Hàm lấy favorites
if (!function_exists('getFavorites')) {
    function getFavorites() {
        return []; // TODO: truy vấn DB
    }
}

// Kết nối DB
require_once __DIR__ . '/../../../config/database.php';
$conn = getDB();

// Kiểm tra có parent_id không
$hasParentCol = false;
try {
    $stmt = $conn->query("SHOW COLUMNS FROM categories LIKE 'parent_id'");
    $hasParentCol = $stmt->rowCount() > 0;
} catch (PDOException $e) {
    $hasParentCol = false;
}

$tree = [];

if ($hasParentCol) {

    // Lấy tất cả category
    $sql = "SELECT id, name, slug, parent_id, image, description
            FROM categories
            ORDER BY parent_id, id";
    $result = $conn->query($sql);

    $categories = [];

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $row['children'] = []; // init luôn
        $categories[$row['id']] = $row;
    }

    // BUILD TREE CHUẨN
    foreach ($categories as $id => &$cat) {
        if ($cat['parent_id'] == 0) {
            $tree[$id] = &$cat; // root
        } else {
            $parentId = $cat['parent_id'];

            if (isset($categories[$parentId])) {
                $categories[$parentId]['children'][$id] = &$cat;
            }
        }
    }
    unset($cat);

} else {
    // fallback nếu không có parent_id
    $sql = "SELECT id, name, slug, image, description FROM categories ORDER BY id";
    $result = $conn->query($sql);

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $row['children'] = [];
        $tree[$row['id']] = $row;
    }
}

// ================= CART =================
$cart = $_SESSION['cart'] ?? [];
$cartCount = count($cart);

$total = 0;
foreach ($cart as $item) {
    $total += ($item['price'] ?? 0) * ($item['quantity'] ?? 0);
}

$shipping = 10000;
$miniSubtotal = $total;
$miniTotal = $miniSubtotal + $shipping;

// ================= FAVORITE =================
$favorites = $user ? getFavorites() : [];
$totalFav = count($favorites);
?>

<style>

    @media (min-width: 992.98px) and (max-width: 1199.98px) {
    .navbar {
        margin-left: 20px;
    }

    .navbar__link {
        gap: 0;
        padding: 0 8px;
    }
}

/* ================= SEARCH FULL ================= */
.search-full {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: none;
}

.search-full.active {
    display: block;
}

/* overlay */
.search-full__overlay {
    position: absolute;
    height: 100vh;
    inset: 0;
    background: rgba(0,0,0,0.6);
    backdrop-filter: blur(8px);
}

/* content */
.search-full__content {
    position: relative;
    max-width: 800px;
    margin: 100px auto;
    padding: 20px;
}

/* ===== INPUT ===== */
.search-full__top {
    display: flex;
    gap: 10px;
}

.search-full__top input {
    flex: 1;
    padding: 18px 20px;
    font-size: 20px;
    border-radius: 999px;
    border: 1px solid var(--separate-color);
    outline: none;

    background: var(--search-bar-bg);
    color: var(--text-color);
}

.search-full__top input::placeholder {
    color: var(--form-placeholder-color);
}

.search-full__top button {
    background: #ffb700;
    border: none;
    color: #fff;
    width: 50px;
    height: 50px;
    border-radius: 999px;
    cursor: pointer;
}

/* ===== HISTORY ===== */
.search-full__history {
    margin-top: 20px;
    background: var(--dropdown-bg-color);
    border-radius: 16px;
    padding: 15px;
    box-shadow: 0 10px 30px var(--dropdown-shadow-color);
}

.search-full__history-head {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    color: var(--text-color);
}

#historyList {
    list-style: none;
    padding: 0;
    margin: 0;
}

#historyList li {
    padding: 10px;
    border-radius: 10px;
    cursor: pointer;
    color: var(--text-color);
}

#historyList li:hover {
    background: var(--form-option-hover-bg);
}

/* ===== SUGGEST ===== */
.search-suggest-products {
    background: var(--dropdown-bg-color);
    margin-top: 10px;
    border-radius: 12px;
    padding: 10px;
    box-shadow: 0 10px 30px var(--dropdown-shadow-color);
}

.suggest-item {
    display: flex;
    gap: 10px;
    padding: 10px;
    cursor: pointer;
    align-items: center;
    color: var(--text-color);
}

.suggest-item:hover {
    background: var(--form-option-hover-bg);
}

.suggest-item span:last-child {
    margin-left: auto;
    font-weight: 500;
}

</style>

<header id="header" class="header">
    <div class="container">
        <div class="top-bar">
            <!-- More -->
            <button class="top-bar__more d-none d-lg-block js-toggle" toggle-target="#navbar">
                <img src="./assets/icons/more.svg" alt="" class="icon top-bar__more-icon" />
            </button>

            <!-- Logo -->
            <a href="<?= $base ?>" class="logo top-bar__logo">
                <img src="./assets/icons/logo.svg" alt="TRQshop" class="logo__img top-bar__logo-img" />
                <h1 class="logo__title top-bar__logo-title">TRQshop</h1>
            </a>

            <!-- Navbar -->
            <nav id="navbar" class="navbar">
                <button class="navbar__close-btn js-toggle" toggle-target="#navbar">
                    <img class="icon" src="./assets/icons/arrow-left.svg" alt="" />
                </button>

                <a href="<?= $base ?>index.php?url=checkout" class="nav-btn d-none d-md-flex">
                    <img src="<?= $base ?>assets/icons/buy.svg" class="nav-btn__icon icon" />
                    <span class="nav-btn__title">Cart</span>
                    <span class="nav-btn__qnt"><?= count($_SESSION['cart'] ?? []) ?></span>
                </a>

                <a href="<?= $base ?>index.php?url=favorite" class="nav-btn d-none d-md-flex">
                    <img src="<?= $base ?>assets/icons/heart.svg" class="nav-btn__icon icon" />
                    <span class="nav-btn__title">Favorite</span>
                    <span class="nav-btn__qnt fav-count-badge"><?= $totalFav ?></span>
                </a>

                <ul class="navbar__list js-dropdown-list">
                    <!-- ========== DANH MỤC + SẢN PHẨM (MEGA MENU) ========== -->
                    <li class="navbar__item">
                        <a href="<?= $base ?>index.php?url=home" class="navbar__link">
                            Danh mục
                            <img src="./assets/icons/arrow-down.svg" alt="" class="icon navbar__arrow" />
                        </a>

                        <div class="dropdown js-dropdown">
                            <div class="dropdown__inner">
                                <div class="top-menu">
                                    <div class="top-menu__main">
                                        <div class="menu-column">
                                            <div class="menu-column__icon d-lg-none">
                                                <img
                                                    src="./assets/img/category/cate-1.1.svg"
                                                    alt=""
                                                    class="menu-column__icon-1"
                                                />
                                                <img
                                                    src="./assets/img/category/cate-1.2.svg"
                                                    alt=""
                                                    class="menu-column__icon-2"
                                                />
                                            </div>
                                            <div class="menu-column__content">
                                                <h2 class="menu-column__heading d-lg-none">Tất cả danh mục</h2>
                                                <ul class="menu-column__list js-menu-list">

                                                    <?php foreach ($tree as $rootId => $rootCat): ?>
                                                        <li class="menu-column__item">
                                                            <a href="<?= $base ?>index.php?url=category&id=<?= $rootCat['id'] ?>" class="menu-column__link">
                                                                <?= htmlspecialchars($rootCat['name']) ?>
                                                            </a>

                                                            <?php if (!empty($rootCat['children'])): ?>
                                                                <div class="sub-menu">
                                                                    <?php
                                                                    $children = $rootCat['children'];
                                                                    $columns = array_chunk($children, 2, true);
                                                                    ?>

                                                                    <?php foreach ($columns as $column): ?>
                                                                        <div class="sub-menu__column">
                                                                            <?php foreach ($column as $childCat): ?>
                                                                                <div class="menu-column">
                                                                                    <?php if (!empty($childCat['image'])): ?>
                                                                                        <div class="menu-column__icon">
                                                                                            <img src="<?= $base ?>assets/img/category-item/<?= htmlspecialchars($childCat['image']) ?>"
                                                                                                class="menu-column__icon-1" alt="">
                                                                                        </div>
                                                                                    <?php endif; ?>

                                                                                    <div class="menu-column__content">
                                                                                        <h2 class="menu-column__heading">
                                                                                            <a href="<?= $base ?>index.php?url=category&id=<?= $childCat['id'] ?>">
                                                                                                <?= htmlspecialchars($childCat['name']) ?>
                                                                                            </a>
                                                                                        </h2>

                                                                                        <?php
                                                                                        $subProducts = getProductsByCategoryId($childCat['id'], 10);
                                                                                        if (!empty($subProducts)):
                                                                                        ?>
                                                                                            <ul class="menu-column__list">
                                                                                                <?php foreach ($subProducts as $prod): ?>
                                                                                                    <li class="menu-column__item">
                                                                                                        <a href="<?= $base ?>index.php?url=product&id=<?= $prod['id'] ?>" class="menu-column__link">
                                                                                                            <?= htmlspecialchars($prod['name']) ?>

                                                                                                        </a>
                                                                                                    </li>
                                                                                                <?php endforeach; ?>
                                                                                            </ul>
                                                                                        <?php endif; ?>

                                                                                    </div>
                                                                                </div>
                                                                            <?php endforeach; ?>
                                                                        </div>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                            <?php endif; ?>
                                                        </li>
                                                    <?php endforeach; ?>

                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>

                    <!-- ========== KHUYẾN MÃI ========== -->
                    <li class="navbar__item">
                        <a href="<?= $base ?>index.php?url=promotion" class="navbar__link">
                            Khuyến mãi
                        </a>
                    </li>

                    <!-- ========== HỖ TRỢ ========== -->
                    <li class="navbar__item">
                        <a href="<?= $base ?>index.php?url=support" class="navbar__link">
                            Hỗ trợ
                        </a>
                    </li>

                    <!-- ========== GIỚI THIỆU ========== -->
                    <li class="navbar__item">
                        <a href="<?= $base ?>index.php?url=about" class="navbar__link">
                            Giới thiệu
                        </a>
                    </li>

                    <li class="navbar__item">
                        <a href="<?= $base ?>index.php?url=contact" class="navbar__link">
                            Liên hệ
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="navbar__overlay js-toggle" toggle-target="#navbar"></div>

            <div class="top-act">

                <button class="top-act__group d-md-none top-act__group--single search-box" id="openSearch">
                    <img src="<?= $base ?>assets/icons/search.svg" class="icon top-act__icon" />
                </button>

                <!-- SEARCH FULL SCREEN -->
                <div class="search-full" id="searchFull">
                    <div class="search-full__overlay"></div>

                    <div class="search-full__content">
                        <!-- Input -->
                        <div class="search-full__top">
                            <input type="text" id="searchInputFull"
                                placeholder="Bạn tìm gì hôm nay..."
                                autocomplete="off" />

                            <button id="closeSearch">✕</button>
                        </div>

                        <!-- History -->
                        <div class="search-full__history">
                            <div class="search-full__history-head">
                                <span>Lịch sử tìm kiếm</span>
                                <button id="clearHistory">Xóa tất cả</button>
                            </div>

                            <ul id="historyList"></ul>
                        </div>

                        <div id="suggestBox" class="search-suggest-products" style="display:none;"></div>
                    </div>
                </div>


                <div class="search-overlay"></div>

                <div class="top-act__group d-xl-none d-lg-none d-md-none d-sm-none top-act__group--bell">
                    <div class="top-act__btn-wrap">
                        <button class="top-act__btn js-toggle" toggle-target="#noti-dropdown">
                            <img src="<?= $base ?>assets/icons/bell.svg" class="icon top-act__icon" />
                            <span class="top-act__title" id="noti-count">0</span>
                        </button>
                        <div id="noti-dropdown" class="act-dropdown hide">
                            <div class="act-dropdown__inner">
                                <img src="./assets/icons/arrow-up.png" alt="" class="act-dropdown__arrow" />
                                <div class="act-dropdown__top">
                                    <h2 class="act-dropdown__title">Thông báo</h2>
                                    <a href="#" class="act-dropdown__view-all" id="mark-all-read">Đánh dấu đã đọc</a>
                                </div>
                                <div class="noti-list" id="noti-list"><div class="noti-item">Đang tải...</div></div>
                                <div class="act-dropdown__separate"></div>
                                <div class="act-dropdown__checkout">
                                    <a href="<?= $base ?>index.php?url=notifications" class="btn btn--primary btn--rounded act-dropdown__checkout-btn">Xem tất cả</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="top-act__group d-md-none">
                    <!-- Yêu thích -->
                    <div class="top-act__btn-wrap">
                        <a class="top-act__btn" href="<?= $base ?>index.php?url=favorite">
                            <img src="<?= $base ?>assets/icons/heart.svg" alt="" class="icon top-act__icon" />
                            <span class="top-act__title fav-count-badge"><?= $totalFav ?></span>
                        </a>
                        <div class="act-dropdown">
                            <div class="act-dropdown__inner">
                                <img src="./assets/icons/arrow-up.png" alt="" class="act-dropdown__arrow" />
                                <div class="act-dropdown__top">
                                    <h2 class="act-dropdown__title">Bạn có <?= $totalFav ?> mục yêu thích</h2>
                                    <a href="<?= $base ?>index.php?url=favorite" class="act-dropdown__view-all">Xem tất cả</a>
                                </div>
                                <div class="row row-cols-3 gx-2 act-dropdown__list">
                                    <?php if ($totalFav > 0): ?>
                                        <?php foreach ($favorites as $item): ?>
                                            <div class="col">
                                                <article class="cart-preview-item">
                                                    <div class="cart-preview-item__img-wrap">
                                                        <img src="<?= $base ?>assets/img/product/<?= htmlspecialchars($item['image'] ?? 'placeholder.png') ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="cart-preview-item__thumb" />
                                                    </div>
                                                    <h3 class="cart-preview-item__title"><?= htmlspecialchars($item['name']) ?></h3>
                                                    <p class="cart-preview-item__price"><?= vnd($item['base_price'] ?? 0) ?></p>
                                                </article>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p style="padding:10px; color:#777;">Chưa có sản phẩm yêu thích.</p>
                                    <?php endif; ?>
                                </div>
                                <div class="act-dropdown__separate"></div>
                                <div class="act-dropdown__checkout">
                                    <a href="<?= $base ?>index.php?url=favorite" class="btn btn--primary btn--rounded act-dropdown__checkout-btn">Xem tất cả</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="top-act__separate"></div>

                    <!-- Giỏ hàng -->
                    <div class="top-act__btn-wrap">
                        <a href="<?= $base ?>index.php?url=checkout" class="top-act__btn">
                            <img src="<?= $base ?>assets/icons/buy.svg" class="icon" />
                            <span class="top-act__title"><?= number_format($total) ?>đ</span>
                        </a>
                        <div class="act-dropdown">
                            <div class="act-dropdown__inner">
                                <div class="act-dropdown__top">
                                    <h2 class="act-dropdown__title">Bạn có <?= $cartCount ?> sản phẩm</h2>
                                    <a href="<?= $base ?>index.php?url=checkout" class="act-dropdown__view-all">Xem tất cả</a>
                                </div>
                                <div class="row row-cols-3 gx-2 act-dropdown__list" id="cart-list">
                                    <?php foreach ($cart as $item): ?>
                                        <div class="col">
                                            <article class="cart-preview-item">
                                                <div class="cart-preview-item__img-wrap">
                                                    <img src="<?= $base ?>assets/img/product/<?= $item['image'] ?>" class="cart-preview-item__thumb" />
                                                </div>
                                                <h3 class="cart-preview-item__title"><?= $item['name'] ?></h3>
                                                <p class="cart-preview-item__price"><?= number_format($item['price']) ?>đ</p>
                                            </article>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="act-dropdown__bottom">
                                    <div class="act-dropdown__row"><span>Tạm tính</span><span id="mini-subtotal"><?= vnd($miniSubtotal) ?></span></div>
                                    <div class="act-dropdown__row"><span>Phí vận chuyển</span><span><?= vnd($shipping) ?></span></div>
                                    <div class="act-dropdown__row act-dropdown__row--bold"><span>Tổng cộng</span><span id="mini-total"><?= vnd($miniTotal) ?></span></div>
                                </div>
                                <div class="act-dropdown__checkout">
                                    <a href="<?= $base ?>index.php?url=checkout" class="btn btn--primary btn--rounded act-dropdown__checkout-btn">Thanh toán</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User menu -->
                <div class="top-act__user">
                    <?php if ($user): ?>
                        <img src="<?= $base ?>assets/img/avatars/<?= htmlspecialchars($_SESSION['user']['avatar'] ?? 'avatar-default.png') ?>" alt="Avatar" class="top-act__avatar" />
                        <div class="act-dropdown top-act__dropdown">
                            <div class="act-dropdown__inner user-menu">
                                <img src="<?= $base ?>assets/icons/arrow-up.png" alt="" class="act-dropdown__arrow top-act__dropdown-arrow" />
                                <div class="user-menu__top">
                                    <img src="<?= $base ?>assets/img/avatars/<?= htmlspecialchars($_SESSION['user']['avatar'] ?? 'avatar-default.png') ?>" alt="Avatar" class="user-menu__avatar" />
                                    <div>
                                        <p class="user-menu__name"><?= $user['name'] ?? 'User' ?></p>
                                        <p>@<?= explode('@', $user['email'])[0] ?? '' ?></p>
                                    </div>
                                </div>
                                <ul class="user-menu__list">
                                    <li><a href="<?= $base ?>index.php?url=profile" class="user-menu__link">Hồ sơ</a></li>
                                    <li><a href="<?= $base ?>index.php?url=favorite" class="user-menu__link">Yêu thích</a></li>
                                    <li class="user-menu__separate">
                                        <a href="#!" class="user-menu__link" id="switch-theme-btn">
                                            <span>Chế độ tối</span>
                                            <img src="<?= $base ?>assets/icons/sun.svg" alt="" class="icon user-menu__icon" />
                                        </a>
                                    </li>
                                    <li><a href="<?= $base ?>index.php?url=settings" class="user-menu__link">Cài đặt</a></li>
                                    <li class="user-menu__separate"><a href="<?= $base ?>index.php?url=logout" class="user-menu__link">Đăng xuất</a></li>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
window.dispatchEvent(new Event("template-loaded"));

// ================= FAVORITE =================
window.updateFavoriteBadge = function(newCount = null) {
    if (newCount !== null) {
        document.querySelectorAll('.fav-count-badge').forEach(el => {
            el.textContent = newCount;
        });
    } else {
        fetch('index.php?url=get-favorites-count', {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.count !== undefined) {
                document.querySelectorAll('.fav-count-badge').forEach(el => {
                    el.textContent = data.count;
                });
            }
        })
        .catch(err => console.error('Lỗi lấy số lượng yêu thích:', err));
    }
};

// ================= SEARCH =================
const openBtn = document.getElementById('openSearch');
const searchFull = document.getElementById('searchFull');
const closeBtn = document.getElementById('closeSearch');
const input = document.getElementById('searchInputFull');
const historyList = document.getElementById('historyList');
const clearBtn = document.getElementById('clearHistory');
const overlay = document.querySelector('.search-full__overlay');
const suggestBox = document.getElementById('suggestBox');

const KEY = 'search_history';

// ===== OPEN =====
openBtn.onclick = () => {
    searchFull.classList.add('active');
    input.focus();
    renderHistory();
};

// ===== CLOSE =====
function closeSearchBox() {
    searchFull.classList.remove('active');
    suggestBox.style.display = 'none';
    input.value = '';
}

// nút X
closeBtn.onclick = closeSearchBox;

// click overlay
overlay.onclick = closeSearchBox;

// ESC để đóng
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeSearchBox();
});

// ===== ENTER SEARCH =====
input.addEventListener('keydown', e => {
    if (e.key === 'Enter') {
        const val = input.value.trim();
        if (!val) return;

        saveHistory(val);
        window.location.href = `index.php?url=search&keyword=${encodeURIComponent(val)}`;
    }
});

// ===== CLICK BUTTON SEARCH =====
const searchBtn = document.querySelector('.search-full__top button:not(#closeSearch)');

if (searchBtn) {
    searchBtn.onclick = () => {
        const val = input.value.trim();
        if (!val) return;

        saveHistory(val);
        window.location.href = `index.php?url=search&keyword=${encodeURIComponent(val)}`;
    };
}

// ===== SAVE HISTORY =====
function saveHistory(val) {
    let arr = JSON.parse(localStorage.getItem(KEY)) || [];
    arr = arr.filter(i => i !== val);
    arr.unshift(val);
    if (arr.length > 10) arr.pop();

    localStorage.setItem(KEY, JSON.stringify(arr));
}

// ===== RENDER HISTORY =====
function renderHistory() {
    let arr = JSON.parse(localStorage.getItem(KEY)) || [];

    if (arr.length === 0) {
        historyList.innerHTML = "<li>Chưa có lịch sử</li>";
        return;
    }

    historyList.innerHTML = arr.map(item =>
        `<li onclick="selectHistory('${item}')">${item}</li>`
    ).join('');
}

// ===== SEARCH SUGGEST =====
input.addEventListener('input', () => {
    const val = input.value.trim();

    if (!val) {
        suggestBox.style.display = 'none';
        renderHistory();
        return;
    }

    fetch(`index.php?url=search-suggest&keyword=${encodeURIComponent(val)}`)
        .then(res => res.json())
        .then(data => {

            if (data.length === 0) {
                suggestBox.innerHTML = "<p>Không có gợi ý</p>";
            } else {
                suggestBox.innerHTML = data.map(item => `
                    <div class="suggest-item" onclick="goProduct(${item.id})">
                        <img src="assets/img/product/${item.image}" width="40">
                        <span>${item.name}</span>
                        <span>${Number(item.base_price).toLocaleString()}đ</span>
                    </div>
                `).join('');
            }

            suggestBox.style.display = 'block';
        });
});

// ===== CLICK HISTORY =====
window.selectHistory = function(val) {
    window.location.href = `index.php?url=search&keyword=${encodeURIComponent(val)}`;
};

// ===== CLEAR HISTORY =====
clearBtn.onclick = () => {
    localStorage.removeItem(KEY);
    renderHistory();
};

// // ===== GO PRODUCT DETAIL =====
// function goProduct(id) {
//     window.location.href = `index.php?url=product-detail&id=${id}`;
// }

window.goProduct = function(id) {
    window.location.href = `index.php?url=product&id=${id}`;
};

</script>
