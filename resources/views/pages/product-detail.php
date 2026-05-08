<?php
// Xử lý ảnh sản phẩm
$product = $product ?? [];

$product['images'] = isset($product['images'])
    ? explode(',', $product['images'])
    : [($product['image'] ?? 'default.png')];

// Lấy các variant và topping (nếu có)
$variants = $product['variants'] ?? [];
$toppings = $product['toppings'] ?? [];

// Lấy review và tương tự
$reviews = $product['reviews'] ?? [];

$sort = $_GET['sort'] ?? 'new';
$filterStar = $_GET['star'] ?? 'all';
$pageReview = $_GET['rpage'] ?? 1;
$limit = 4;

/* ===== SORT TRƯỚC ===== */
if($sort == 'like'){
    usort($reviews, fn($a,$b) => ($b['likes'] ?? 0) <=> ($a['likes'] ?? 0));
}else{
    usort($reviews, fn($a,$b) => strtotime($b['created_at']) <=> strtotime($a['created_at']));
}

/* ===== FILTER ===== */
$filtered = array_filter($reviews, function($r) use ($filterStar){
    return $filterStar == 'all' || $r['rating'] == $filterStar;
});

/* ===== PAGINATION ===== */
$total = count($filtered);
$totalPage = ceil($total / $limit);
$start = ($pageReview - 1) * $limit;
$reviewsShow = array_slice($filtered, $start, $limit);

/* ===== SUMMARY ===== */
$totalReview = count($reviews);
$avg = $totalReview ? round(array_sum(array_column($reviews,'rating')) / $totalReview,1) : 0;

$starCount = [1=>0,2=>0,3=>0,4=>0,5=>0];
foreach($reviews as $r){
    $starCount[$r['rating']]++;
}

// ========== TÍNH TỒN KHO ==========
$totalStock = 0;
if (!empty($variants)) {
    foreach ($variants as $v) {
        $totalStock += (int)($v['stock_quantity'] ?? 0);
    }
} else {
    $totalStock = isset($product['stock_quantity']) ? (int)$product['stock_quantity'] : 1;
}
$hasStock = $totalStock > 0;

// ========== LẤY DANH SÁCH YÊU THÍCH (từ controller truyền vào) ==========
$favIds = $favIds ?? [];
$isFavorited = in_array($product['id'], $favIds);

$basePrice = (float)$product['base_price'];
$discount = (float)($product['discount_percent'] ?? 0);

$finalPrice = $basePrice;

if ($discount > 0) {
    $finalPrice = round($basePrice - ($basePrice * $discount / 100), 2);
}

$discountedBasePrice = $finalPrice;
?>


<style>
/* =========================
   VARIANT (SIZE)
========================= */
.variant-list {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}
.variant-item input {
    display: none;
}
.variant-box {
    border: 2px solid var(--separate-color);
    background: var(--product-detail-tag-bg);
    padding: 12px 18px;
    border-radius: 14px;
    cursor: pointer;
    transition: all 0.25s ease;
    position: relative;
}
.variant-item:hover .variant-box {
    transform: translateY(-3px);
}
.variant-item input:checked + .variant-box {
    border-color: #ff4d4f;
    background: linear-gradient(135deg, #fff0f0, #ffe5e5);
    transform: scale(1.05);
}
.variant-name {
    font-weight: 600;
    color: var(--text-color);
}
.variant-price {
    font-size: 13px;
    color: var(--filter-btn-color);
}
.variant-item.disabled {
    opacity: 0.4;
    pointer-events: none;
}
.sold-out {
    position: absolute;
    top: -6px;
    right: -6px;
    background: #ff4d4f;
    color: #fff;
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 6px;
}

/* =========================
   TOPPING
========================= */
.topping-list {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}
.topping-item input {
    display: none;
}
.topping-box {
    border: 1px solid var(--separate-color);
    background: var(--product-detail-tag-bg);
    padding: 10px 14px;
    border-radius: 999px;
    cursor: pointer;
    transition: all 0.25s;
    display: flex;
    gap: 6px;
    align-items: center;
    color: var(--text-color);
}
.topping-box .price {
    font-size: 12px;
    color: var(--filter-btn-color);
}
.topping-item:hover .topping-box {
    transform: translateY(-2px);
}
.topping-item input:checked + .topping-box {
    background: var(--primary-color, #1890ff);
    color: #fff;
    box-shadow: 0 4px 12px rgba(24, 144, 255, 0.25);
}

/* =========================
   QUANTITY (APP STYLE)
========================= */
.qty {
    display: inline-flex;
    align-items: center;
    background: var(--product-detail-tag-bg);
    border: 1px solid var(--separate-color);
    border-radius: 12px;
    overflow: hidden;
    margin-top: 12px;
    margin-bottom: 20px;
}
.qty-btn {
    width: 42px;
    height: 42px;
    border: none;
    background: transparent;
    color: var(--text-color);
    font-size: 20px;
    cursor: pointer;
    transition: 0.2s;
}
.qty-btn:hover {
    background: var(--form-option-hover-bg);
}
.qty-btn:active {
    transform: scale(0.9);
}
.qty-input {
    width: 50px;
    height: 42px;
    border: none;
    text-align: center;
    font-weight: 600;
    background: transparent;
    color: var(--text-color);
    pointer-events: none;
}

/* =========================
   FLOAT PRICE
========================= */
.float-price {
    position: fixed;
    bottom: 100px;
    right: 20px;
    background: var(--primary-color, #ff4d4f);
    color: #fff;
    padding: 10px 16px;
    border-radius: 20px;
    animation: floatUp 0.5s ease;
    z-index: 999;
}
.prod-info__add-to-cart {
    border: none;
    font-weight: bold;
    transition: 0.3s;
}
.prod-info__add-to-cart:hover {
    transform: translateY(-2px);
}
@keyframes floatUp {
    from {
        opacity: 0;
        transform: translateY(20px) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* =========================
   NO OPTION MESSAGE & STOCK INFO
========================= */
.no-option-message {
    background: #f8f9fa;
    padding: 12px 16px;
    border-radius: 12px;
    color: #6c757d;
    font-size: 1.4rem;
    margin: 8px 0;
    display: flex;
    align-items: center;
    gap: 8px;
}
.no-option-message i {
    font-size: 1.6rem;
    color: #adb5bd;
}
.stock-info {
    margin-top: 8px;
    margin-bottom: 12px;
    font-size: 1.3rem;
    display: flex;
    align-items: center;
    gap: 6px;
}
.stock-info .in-stock {
    color: #28a745;
}
.stock-info .out-of-stock {
    color: #dc3545;
}

/* ========== REVIEW SECTION ========== */
.review-filter {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 16px;
}
.review-filter a {
    padding: 4px 12px;
    border-radius: 20px;
    background: #f0f0f0;
    text-decoration: none;
    color: #333;
    font-size: 13px;
}
.review-filter a.active {
    background: #ff4d4f;
    color: white;
}
.review-summary {
    display: flex;
    gap: 20px;
    background: #f9f9f9;
    padding: 16px;
    border-radius: 16px;
    margin: 16px 0;
}
.review-summary__left {
    text-align: center;
    min-width: 100px;
}
.avg {
    font-size: 32px;
    font-weight: bold;
}
.stars {
    color: #ffc107;
}
.bar {
    background: #e0e0e0;
    border-radius: 10px;
    height: 8px;
    width: 150px;
    overflow: hidden;
}
.bar div {
    background: #ffc107;
    height: 100%;
}
.review-form {
    border: 1px solid #ddd;
    padding: 16px;
    border-radius: 16px;
    margin: 16px 0;
}
.review-form__group {
    margin-bottom: 12px;
}
.star-input {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
    gap: 4px;
}
.star-input input {
    display: none;
}
.star-input label {
    font-size: 24px;
    color: #ccc;
    cursor: pointer;
}
.star-input input:checked ~ label,
.star-input label:hover,
.star-input label:hover ~ label {
    color: #ffc107;
}
.review-card {
    border-bottom: 1px solid #eee;
    padding: 16px 0;
    position: relative;
}
.review-like {
    position: absolute;
    right: 0;
    top: 16px;
    background: #f0f0f0;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    cursor: pointer;
}
.review-card__top {
    display: flex;
    gap: 12px;
    align-items: center;
}
.review-card__avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
}
.review-card__stars .star.active {
    color: #ffc107;
}
.review-images {
    display: flex;
    gap: 8px;
    margin-top: 12px;
}
.review-images img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
}
.review-empty {
    text-align: center;
    padding: 20px;
    color: #999;
}

.custom-toast {
    font-family: system-ui, -apple-system, 'Segoe UI', sans-serif;
    font-weight: 500;
    letter-spacing: 0.3px;
    border-left: 4px solid #fff;
    border-radius: 8px !important;
}


</style>

<main class="product-page">
    <div class="container">

        <!-- Breadcrumbs -->
        <div class="product-container">
            <ul class="breadcrumbs">
                <li><a class="breadcrumbs__link" href="<?= $base ?>index.php">Home <img src="<?= $base ?>assets/icons/arrow-right.svg" /></a></li>
                <li>
                    <a class="breadcrumbs__link" href="<?= $base ?>index.php?url=category&id=<?= $product['category_id'] ?? 0 ?>">
                        <?= $product['category_name'] ?? 'Category' ?>
                        <img src="<?= $base ?>assets/icons/arrow-right.svg" />
                    </a>
                </li>
                <li><span class="breadcrumbs__link--current"><?= $product['name'] ?></span></li>
            </ul>
        </div>

        <!-- Product Info -->
        <div class="product-container prod-info-content">
            <div class="row">

                <!-- LEFT: IMAGE -->
                <div class="col-5 col-xl-6 col-lg-12">
                    <div class="prod-preview">
                        <div class="prod-preview__list">
                            <?php foreach ($product['images'] as $i => $img): ?>
                                <div class="prod-preview__item <?= $i === 0 ? 'active' : '' ?>">
                                    <img src="<?= $base ?>assets/img/product/<?= $img ?>" class="prod-preview__img" />
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="prod-preview__thumbs">
                            <?php foreach ($product['images'] as $i => $img): ?>
                                <img src="<?= $base ?>assets/img/product/<?= $img ?>"
                                     class="prod-preview__thumb-img <?= $i === 0 ? 'prod-preview__thumb-img--current' : '' ?>" />
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- RIGHT: INFO -->
                <div class="col-7 col-xl-6 col-lg-12">
                    <form class="form add-cart-form" action="<?= $base ?>index.php?url=add-cart&id=<?= $product['id'] ?>" method="POST">
                        <section class="prod-info">
                            <div class="row">
                                <!-- NAME -->
                                <div class="col-5 col-xxl-6 col-xl-12">
                                    <h1 class="prod-info__heading"><?= $product['name'] ?></h1>
                                    <div class="prod-prop">
                                        <img src="<?= $base ?>assets/icons/star.svg" class="prod-prop__icon"/>
                                        <span class="prod-prop__title">(<?= $product['avg_rating'] ?? 0 ?>) <?= count($reviews) ?> reviews</span>
                                    </div>

                                    <!-- STOCK INFO -->
                                    <div class="stock-info">
                                        <?php if ($hasStock): ?>
                                            <i class="fas fa-check-circle in-stock"></i>
                                            <span class="in-stock">Còn hàng (<?= number_format($totalStock) ?> sản phẩm)</span>
                                        <?php else: ?>
                                            <i class="fas fa-times-circle out-of-stock"></i>
                                            <span class="out-of-stock">Hết hàng</span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- VARIANTS (SIZE) -->
                                    <div class="variant-section">
                                        <?php if (!empty($variants)): ?>
                                            <label class="form__label prod-info__label">Chọn size</label>
                                            <div class="variant-list">
                                                <?php foreach ($variants as $v): ?>
                                                    <?php
                                                    $variantStock = isset($v['stock_quantity']) ? (int)$v['stock_quantity'] : 0;
                                                    $disabled = $variantStock <= 0;
                                                    ?>
                                                    <label class="variant-item <?= $disabled ? 'disabled' : '' ?>">
                                                        <input type="radio"
                                                            name="variant_id"
                                                            value="<?= $v['id'] ?>"
                                                            data-price="<?= $v['price'] ?>"
                                                            data-stock="<?= $variantStock ?>"
                                                            <?= $disabled ? 'disabled' : '' ?>>
                                                        <div class="variant-box">
                                                            <span class="variant-name"><?= $v['variant_name'] ?></span>
                                                            <span class="variant-price"><?= number_format($v['price']) ?>đ</span>
                                                            <?php if($disabled): ?>
                                                                <span class="sold-out">Hết hàng</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </label>
                                                <?php endforeach; ?>
                                            </div>
                                            <?php
                                            $anyVariantInStock = false;
                                            foreach ($variants as $v) {
                                                if (($v['stock_quantity'] ?? 0) > 0) {
                                                    $anyVariantInStock = true;
                                                    break;
                                                }
                                            }
                                            if (!$anyVariantInStock) $hasStock = false;
                                            ?>
                                        <?php else: ?>
                                            <div class="no-option-message">
                                                <i class="fas fa-info-circle"></i> Sản phẩm chỉ có một kích thước duy nhất.
                                            </div>
                                            <input type="hidden" name="variant_id" value="0">
                                        <?php endif; ?>
                                    </div>

                                    <!-- TOPPINGS -->
                                    <div class="topping-section">
                                        <?php if (!empty($toppings)): ?>
                                            <label class="form__label prod-info__label">Topping <span id="topping-count">(0)</span></label>
                                            <div class="topping-list">
                                                <?php foreach ($toppings as $t): ?>
                                                    <label class="topping-item">
                                                        <input type="checkbox"
                                                            name="toppings[]"
                                                            value="<?= $t['id'] ?>"
                                                            data-price="<?= $t['price'] ?>">
                                                        <div class="topping-box">
                                                            <span><?= $t['name'] ?></span>
                                                            <span class="price">+<?= number_format($t['price']) ?>đ</span>
                                                        </div>
                                                    </label>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="no-option-message">
                                                <i class="fas fa-leaf"></i> Sản phẩm này không có topping đi kèm.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="col-7 col-xxl-6 col-xl-12">
                                    <!-- PRICE -->
                                    <div class="prod-info__card">

                                        <!-- PRICE + DISCOUNT -->
                                        <div class="prod-info__row">
                                            <span class="prod-info__price" id="prod-price">
                                                <?= number_format($finalPrice) ?>đ
                                            </span>

                                            <?php if ($discount > 0): ?>
                                                <span class="prod-info__tax">
                                                    -<?= $discount ?>%
                                                </span>

                                                <del style="color:#999;font-size:13px;margin-left:8px;">
                                                    <?= number_format($basePrice) ?>đ
                                                </del>
                                            <?php endif; ?>
                                        </div>

                                        <!-- TOTAL PRICE -->
                                        <p class="prod-info__total-price" id="prod-total-price">
                                            <?= number_format($finalPrice) ?>đ
                                        </p>

                                        <!-- SOLD -->
                                        <div style="display:flex;gap:16px;font-size:13px;margin:8px 0;color:#666;">
                                            <span>📦 Đã bán: <b><?= number_format($product['sold_count'] ?? 0) ?></b></span>
                                            <span>🔥 Giảm: <b><?= $discount ?>%</b></span>
                                        </div>

                                        <!-- QUANTITY -->
                                        <div class="qty">
                                            <button type="button" class="qty-btn" onclick="changeQty(-1)">−</button>
                                            <input type="number" class="qty-input" name="quantity" value="1" min="1">
                                            <button type="button" class="qty-btn" onclick="changeQty(1)">+</button>
                                        </div>

                                        <!-- ADD TO CART / LIKE -->
                                        <div class="prod-info__row">
                                            <button
                                                type="submit"
                                                onclick="addCart()"
                                                class="btn btn--primary prod-info__add-to-cart"
                                                <?= !$hasStock ? 'disabled' : '' ?>
                                                style="<?= !$hasStock ? 'opacity:0.5; cursor:not-allowed;' : '' ?>"
                                            >
                                                <?= $hasStock ? 'Thêm vào giỏ hàng' : 'Hết hàng' ?>
                                            </button>

                                            <button
                                                type="button"
                                                class="like-btn prod-info__like-btn <?= $isFavorited ? 'like-btn--liked' : '' ?>"
                                                data-id="<?= $product['id'] ?>"
                                            >
                                                <img src="<?= $base ?>assets/icons/heart.svg" class="like-btn__icon icon" />
                                                <img src="<?= $base ?>assets/icons/heart-red.svg" class="like-btn__icon--liked" />
                                            </button>
                                        </div>

                                    </div>
                                </div>

                                <!-- DESCRIPTION -->
                                <div class="text-content">
                                    <h2>Mô tả sản phẩm</h2>
                                    <p><?= $product['description'] ?? 'No description' ?></p>
                                </div>
                            </div>
                        </section>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tabs: Description / Reviews / Similar -->
        <div class="product-container">
            <div class="prod-tab js-tabs">
                <ul class="prod-tab__list">
                    <li class="prod-tab__item tab-btn prod-tab__item--current" data-tab="tab-desc">Description</li>
                    <li class="prod-tab__item tab-btn" data-tab="tab-review">Review (<?= count($reviews) ?>)</li>
                    <li class="prod-tab__item tab-btn" data-tab="tab-similar">Similar</li>
                </ul>

                <div class="prod-tab__contents">
                    <?php
                        $productName = $product['name'] ?? 'Sản phẩm';
                        $category = mb_strtolower($product['category_name'] ?? '');
                        $descImage = $product['images'][0] ?? $product['image'] ?? 'default.png';

                        $isBurger = stripos($category, 'burger') !== false;
                        $isChicken = stripos($category, 'gà') !== false
                                || stripos($category, 'ga') !== false
                                || stripos($category, 'chicken') !== false;

                        if ($isBurger) {
                            $descBlocks = [
                                ['h' => 'Tinh hoa trong từng lớp bánh', 'p' => $productName . ' không chỉ là một chiếc burger thông thường, mà là sự kết hợp hoàn hảo giữa lớp bánh mì mềm mịn, phần thịt bò đậm vị được nướng chín tới và lớp sốt đặc trưng lan tỏa hương thơm quyến rũ. Mỗi lần cắn là một lần cảm nhận rõ rệt sự hòa quyện giữa các tầng hương vị.'],
                                ['h' => 'Trải nghiệm vị giác bùng nổ', 'p' => 'Lớp thịt bò juicy giữ trọn độ ngọt tự nhiên, kết hợp cùng rau tươi giòn và sốt béo nhẹ tạo nên một tổng thể cân bằng. Không quá ngấy, không quá khô – tất cả đều được tính toán để mang lại trải nghiệm ăn uống trọn vẹn nhất.'],
                                ['h' => 'Dành cho mọi khoảnh khắc', 'p' => 'Dù là bữa trưa nhanh gọn, bữa tối tiện lợi hay một buổi tụ tập bạn bè, ' . $productName . ' luôn là lựa chọn hoàn hảo. Thưởng thức ngon hơn khi dùng kèm khoai tây chiên giòn và một ly nước mát lạnh.'],
                                ['h' => 'Chất lượng tạo nên sự khác biệt', 'p' => 'Nguyên liệu được chọn lọc kỹ lưỡng, quy trình chế biến đảm bảo vệ sinh và giữ trọn hương vị. Đây không chỉ là một món ăn, mà là trải nghiệm fast food đúng nghĩa.']
                            ];
                        } elseif ($isChicken) {
                            $descBlocks = [
                                ['h' => 'Giòn rụm ngay từ miếng đầu tiên', 'p' => $productName . ' mang đến cảm giác giòn tan đầy kích thích với lớp vỏ vàng óng được chiên chuẩn nhiệt độ. Ngay khi cắn vào, bạn sẽ nghe thấy âm thanh "rộp rộp" đặc trưng – dấu hiệu của một món gà hoàn hảo.'],
                                ['h' => 'Mềm mọng bên trong', 'p' => 'Ẩn sau lớp vỏ giòn là phần thịt gà mềm, mọng nước và đậm đà gia vị. Từng thớ thịt giữ được độ ẩm tự nhiên, không bị khô, mang lại cảm giác ăn cực kỳ đã.'],
                                ['h' => 'Đậm vị – dễ nghiện', 'p' => 'Gia vị được tẩm ướp kỹ càng, tạo nên hương vị đặc trưng khó quên. Càng ăn càng cuốn, càng ăn càng ghiền – đúng chuẩn món ăn "comfort food".'],
                                ['h' => 'Kết hợp hoàn hảo', 'p' => 'Ngon hơn khi ăn nóng cùng tương ớt, sốt mayonnaise hoặc dùng kèm cơm, khoai tây chiên. Phù hợp cho cả ăn một mình lẫn chia sẻ cùng bạn bè.']
                            ];
                        } else {
                            $descBlocks = [['h' => 'Mô tả sản phẩm', 'p' => $product['description'] ?? 'No description']];
                        }
                    ?>

                    <div class="prod-tab__content prod-tab__content--current" id="tab-desc">
                        <div class="row">
                            <div class="col-8 col-xl-10 col-lg-12">
                                <div class="text-content prod-tab__text-content">
                                    <h2><?= htmlspecialchars($productName) ?></h2>
                                    <p><?= htmlspecialchars($descBlocks[0]['p']) ?></p>
                                    <p>
                                        <img src="<?= $base ?>assets/img/product/<?= htmlspecialchars($descImage) ?>"
                                            alt="<?= htmlspecialchars($productName) ?>" />
                                        <em>Hình ảnh thực tế của <?= htmlspecialchars($productName) ?></em>
                                    </p>
                                    <?php for ($i = 0; $i < count($descBlocks); $i++): ?>
                                        <?php if ($i > 0): ?><hr /><?php endif; ?>
                                        <h3><?= htmlspecialchars($descBlocks[$i]['h']) ?></h3>
                                        <p><?= htmlspecialchars($descBlocks[$i]['p']) ?></p>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Review Tab -->
                    <div class="prod-tab__content" id="tab-review">
                        <!-- FILTER -->
                        <div class="review-filter">
                            <a class="<?= $filterStar=='all'?'active':'' ?>" href="<?= $base ?>index.php?url=product&id=<?= $product['id'] ?>&star=all">Tất cả</a>
                            <?php for($i=5;$i>=1;$i--): ?>
                                <a class="<?= $filterStar==$i?'active':'' ?>" href="<?= $base ?>index.php?url=product&id=<?= $product['id'] ?>&star=<?= $i ?>"><?= $i ?> ★</a>
                            <?php endfor; ?>
                        </div>

                        <div style="margin:10px 0;">
                            <select onchange="location = this.value">
                                <option value="<?= $base ?>index.php?url=product&id=<?= $product['id'] ?>&sort=new" <?= ($_GET['sort'] ?? '')=='new'?'selected':'' ?>>Mới nhất</option>
                                <option value="<?= $base ?>index.php?url=product&id=<?= $product['id'] ?>&sort=like" <?= ($_GET['sort'] ?? '')=='like'?'selected':'' ?>>Hữu ích nhất</option>
                            </select>
                        </div>

                        <div class="review-summary">
                            <div class="review-summary__left">
                                <div class="avg"><?= $avg ?></div>
                                <div class="stars">
                                    <?php for($i=1;$i<=5;$i++): ?>
                                        <span class="<?= $i <= round($avg) ? 'active' : '' ?>">★</span>
                                    <?php endfor; ?>
                                </div>
                                <div class="total"><?= $totalReview ?> đánh giá</div>
                            </div>
                            <div class="review-summary__right">
                                <?php for($i=5;$i>=1;$i--):
                                    $percent = $totalReview ? ($starCount[$i]/$totalReview)*100 : 0;
                                ?>
                                <div class="row" style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                                    <span style="width: 30px;"><?= $i ?>★</span>
                                    <div class="bar" style="flex:1;"><div style="width:<?= $percent ?>%"></div></div>
                                    <span style="width: 30px;"><?= $starCount[$i] ?></span>
                                </div>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <!-- REVIEW FORM -->
                        <?php if(isset($_SESSION['user'])): ?>
                            <form method="POST" action="<?= $base ?>index.php?url=add-review" class="review-form" enctype="multipart/form-data">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <div class="review-form__group">
                                    <label>Đánh giá:</label>
                                    <div class="star-input">
                                        <?php for($i=5;$i>=1;$i--): ?>
                                            <input type="radio" name="rating" value="<?= $i ?>" id="star<?= $i ?>" required>
                                            <label for="star<?= $i ?>">★</label>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <div class="review-form__group">
                                    <textarea name="comment" rows="3" required placeholder="Chia sẻ cảm nhận của bạn về sản phẩm..."></textarea>
                                </div>
                                <div class="review-form__group">
                                    <label>Ảnh thực tế (tuỳ chọn):</label>
                                    <input type="file" name="images[]" multiple accept="image/*">
                                </div>
                                <div class="review-images" id="previewImages"></div>
                                <button class="btn btn--primary">Gửi đánh giá</button>
                            </form>
                        <?php endif; ?>

                        <!-- REVIEW LIST -->
                        <div class="review-list">
                            <?php foreach($reviewsShow as $rev): ?>
                            <div class="review-card">
                                <div class="review-like" onclick="likeReview(<?= $rev['id'] ?>, this)">
                                    👍 Hữu ích (<span class="like-count"><?= $rev['likes'] ?? 0 ?></span>)
                                </div>
                                <div class="review-card__top">
                                    <img src="<?= $base ?>assets/img/avatars/<?= $rev['avatar'] ?? 'avatar-1.png' ?>" class="review-card__avatar">
                                    <div class="review-card__info">
                                        <div class="review-card__name"><?= htmlspecialchars($rev['full_name']) ?></div>
                                        <div class="review-card__stars">
                                            <?php for($i=1;$i<=5;$i++): ?>
                                                <span class="<?= $i <= $rev['rating'] ? 'star active' : 'star' ?>">★</span>
                                            <?php endfor; ?>
                                        </div>
                                        <div class="review-card__time"><?= date('d/m/Y', strtotime($rev['created_at'])) ?></div>
                                    </div>
                                </div>
                                <div class="review-card__content"><?= nl2br(htmlspecialchars($rev['comment'])) ?></div>
                                <?php if(!empty($rev['images'])): ?>
                                    <div class="review-images">
                                        <?php foreach(explode(',', $rev['images']) as $img): ?>
                                            <img src="<?= $base ?>uploads/review/<?= $img ?>">
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if(empty($reviewsShow)): ?>
                            <div class="review-empty">Chưa có đánh giá nào 😢</div>
                        <?php endif; ?>
                    </div>


                </div>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ===== TOAST =====
    function showToast(message, type = 'error') {
        const oldToast = document.querySelector('.custom-toast');
        if (oldToast) oldToast.remove();

        const toast = document.createElement('div');
        toast.className = 'custom-toast';
        toast.innerText = message;
        toast.style.position = 'fixed';
        toast.style.bottom = '20px';
        toast.style.right = '20px';
        toast.style.backgroundColor = type === 'error' ? '#dc3545' : (type === 'success' ? '#28a745' : '#333');
        toast.style.color = '#fff';
        toast.style.padding = '12px 20px';
        toast.style.borderRadius = '8px';
        toast.style.zIndex = '9999';
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(20px)';
        toast.style.transition = '0.3s';

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateY(0)';
        }, 10);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(20px)';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // ===== BIẾN =====
    let currentStock = <?= $totalStock ?>;

    const qtyInput = document.querySelector('.qty-input');
    const priceEl = document.getElementById('prod-price');
    const totalEl = document.getElementById('prod-total-price');

    // ===== UPDATE TOTAL =====
    function updateTotal() {

        const basePrice = <?= $discountedBasePrice ?>; // đã giảm giá sẵn

        const variant = document.querySelector('input[name="variant_id"]:checked');
        const variantPrice = variant ? Number(variant.dataset.price) : 0;

        let toppingTotal = 0;

        document.querySelectorAll('input[name="toppings[]"]:checked').forEach(cb => {
            toppingTotal += Number(cb.dataset.price);
        });

        const qty = Number(qtyInput.value) || 1;

        const unitPrice = basePrice + variantPrice + toppingTotal;
        const finalPrice = unitPrice * qty;

        priceEl.innerText = unitPrice.toLocaleString() + 'đ';
        totalEl.innerText = finalPrice.toLocaleString() + 'đ';
    }
    // ===== UPDATE STOCK =====
    function updateVariantStock() {
        const selected = document.querySelector('input[name="variant_id"]:checked');

        if (selected) {
            currentStock = parseInt(selected.dataset.stock) || 0;
        } else {
            currentStock = <?= $totalStock ?>;
        }

        if (qtyInput.value > currentStock) {
            qtyInput.value = currentStock;
        }
    }

    // ===== QTY BUTTON =====
    window.changeQty = function (n) {
        let val = Number(qtyInput.value);
        val += n;

        if (val < 1) val = 1;

        if (val > currentStock) {
            showToast(`Chỉ còn ${currentStock} sản phẩm`, "error");
            val = currentStock;
        }

        qtyInput.value = val;
        updateTotal();
    };

    // ===== HOLD CLICK =====
    let holdInterval;
    document.querySelectorAll('.qty-btn').forEach(btn => {
        btn.addEventListener('mousedown', () => {
            holdInterval = setInterval(() => btn.click(), 120);
        });
    });

    document.addEventListener('mouseup', () => clearInterval(holdInterval));

    // ===== VARIANT =====
    const variants = document.querySelectorAll('input[name="variant_id"]');

    if (variants.length) {
        variants.forEach(v => {
            v.addEventListener('change', () => {
                updateVariantStock();
                updateTotal();
            });
        });

        const first = [...variants].find(v => !v.disabled);
        if (first && !document.querySelector('input[name="variant_id"]:checked')) {
            first.checked = true;
            first.dispatchEvent(new Event('change'));
        }
    }

    // ===== TOPPING =====
    document.querySelectorAll('input[name="toppings[]"]').forEach(cb => {
        cb.addEventListener('change', updateTotal);
    });

    // ===== TAB =====
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function () {

            document.querySelectorAll('.tab-btn')
                .forEach(b => b.classList.remove('prod-tab__item--current'));

            document.querySelectorAll('.prod-tab__content')
                .forEach(c => c.classList.remove('prod-tab__content--current'));

            this.classList.add('prod-tab__item--current');

            const tabId = this.dataset.tab;
            const tab = document.getElementById(tabId);

            if (tab) tab.classList.add('prod-tab__content--current');
        });
    });

    // ===== LIKE REVIEW =====
    window.likeReview = function (id, el) {
        fetch("index.php?url=like-review&id=" + id)
            .then(res => res.text())
            .then(data => {
                el.innerText = "👍 Đã thích (" + data + ")";
            });
    };

    // ===== PREVIEW IMAGE =====
    const input = document.querySelector('input[name="images[]"]');
    const preview = document.getElementById('previewImages');

    if (input) {
        input.addEventListener('change', function () {
            preview.innerHTML = '';
            [...this.files].forEach(file => {
                const reader = new FileReader();
                reader.onload = e => {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    preview.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        });
    }

    // ===== ADD CART =====
    window.addCart = function () {
        const isLogin = <?= isset($_SESSION['user']) ? 'true' : 'false' ?>;

        if (!isLogin) {
            showToast("Vui lòng đăng nhập!", "error");
            setTimeout(() => location.href = "index.php?url=login", 1500);
            return;
        }

        const hasStock = <?= $hasStock ? 'true' : 'false' ?>;

        if (!hasStock) {
            showToast("Hết hàng!", "error");
            return;
        }

        let qty = parseInt(qtyInput.value);
        if (qty > currentStock) {
            showToast(`Tối đa ${currentStock}`, "error");
            qtyInput.value = currentStock;
            return;
        }

        document.querySelector(".add-cart-form").submit();
    };

    // INIT
    updateVariantStock();
    updateTotal();
});
</script>
