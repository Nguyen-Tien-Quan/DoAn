<?php
// Xử lý ảnh sản phẩm
$product['images'] = isset($product['images']) ? explode(',', $product['images']) : [$product['image']];

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
    /* box-shadow: 0 6px 20px ; */
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

                                    <!-- VARIANTS -->
                                    <?php if (!empty($variants)): ?>
                                        <label class="form__label prod-info__label">Chọn size</label>

                                        <div class="variant-list">
                                            <?php foreach ($variants as $v): ?>
                                                <label class="variant-item <?= $v['stock_quantity'] <= 0 ? 'disabled' : '' ?>">
                                                    <input type="radio"
                                                        name="variant_id"
                                                        value="<?= $v['id'] ?>"
                                                        data-price="<?= $v['price'] ?>"
                                                        <?= $v['stock_quantity'] <= 0 ? 'disabled' : '' ?>>

                                                    <div class="variant-box">
                                                        <span class="variant-name"><?= $v['variant_name'] ?></span>
                                                        <span class="variant-price"><?= number_format($v['price']) ?>đ</span>

                                                        <?php if($v['stock_quantity'] <= 0): ?>
                                                            <span class="sold-out">Hết hàng</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($toppings)): ?>
                                        <label class="form__label prod-info__label">
                                            Topping <span id="topping-count">(0)</span>
                                        </label>

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
                                    <?php endif; ?>

                                </div>

                                <div class="col-7 col-xxl-6 col-xl-12">
                                    <!-- PRICE -->
                                    <div class="prod-info__card">
                                        <div class="prod-info__row">
                                            <span class="prod-info__price" id="prod-price"><?= number_format($product['base_price']) ?>đ</span>
                                            <span class="prod-info__tax">- 10%</span>
                                        </div>
                                        <p class="prod-info__total-price" id="prod-total-price"><?= number_format($product['base_price']) ?>đ</p>
                                        <div class="qty">
                                            <button type="button" class="qty-btn" onclick="changeQty(-1)">−</button>
                                            <input type="number" class="qty-input" name="quantity" value="1" min="1">
                                            <button type="button" class="qty-btn" onclick="changeQty(1)">+</button>
                                        </div>
                                        <!-- ADD TO CART / LIKE -->
                                        <div class="prod-info__row">
                                            <button type="submit" onclick="addCart()" class="btn btn--primary prod-info__add-to-cart">Add to cart</button>
                                            <button type="button" class="like-btn prod-info__like-btn">
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
                    <li class="prod-tab__item tab-btn prod-tab__item--current" data-tab="tab-desc" >Description</li>
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
                                [
                                    'h' => 'Tinh hoa trong từng lớp bánh',
                                    'p' => $productName . ' không chỉ là một chiếc burger thông thường, mà là sự kết hợp hoàn hảo giữa lớp bánh mì mềm mịn, phần thịt bò đậm vị được nướng chín tới và lớp sốt đặc trưng lan tỏa hương thơm quyến rũ. Mỗi lần cắn là một lần cảm nhận rõ rệt sự hòa quyện giữa các tầng hương vị.'
                                ],
                                [
                                    'h' => 'Trải nghiệm vị giác bùng nổ',
                                    'p' => 'Lớp thịt bò juicy giữ trọn độ ngọt tự nhiên, kết hợp cùng rau tươi giòn và sốt béo nhẹ tạo nên một tổng thể cân bằng. Không quá ngấy, không quá khô – tất cả đều được tính toán để mang lại trải nghiệm ăn uống trọn vẹn nhất.'
                                ],
                                [
                                    'h' => 'Dành cho mọi khoảnh khắc',
                                    'p' => 'Dù là bữa trưa nhanh gọn, bữa tối tiện lợi hay một buổi tụ tập bạn bè, ' . $productName . ' luôn là lựa chọn hoàn hảo. Thưởng thức ngon hơn khi dùng kèm khoai tây chiên giòn và một ly nước mát lạnh.'
                                ],
                                [
                                    'h' => 'Chất lượng tạo nên sự khác biệt',
                                    'p' => 'Nguyên liệu được chọn lọc kỹ lưỡng, quy trình chế biến đảm bảo vệ sinh và giữ trọn hương vị. Đây không chỉ là một món ăn, mà là trải nghiệm fast food đúng nghĩa.'
                                ],
                            ];
                        } elseif ($isChicken) {
                            $descBlocks = [
                                [
                                    'h' => 'Giòn rụm ngay từ miếng đầu tiên',
                                    'p' => $productName . ' mang đến cảm giác giòn tan đầy kích thích với lớp vỏ vàng óng được chiên chuẩn nhiệt độ. Ngay khi cắn vào, bạn sẽ nghe thấy âm thanh "rộp rộp" đặc trưng – dấu hiệu của một món gà hoàn hảo.'
                                ],
                                [
                                    'h' => 'Mềm mọng bên trong',
                                    'p' => 'Ẩn sau lớp vỏ giòn là phần thịt gà mềm, mọng nước và đậm đà gia vị. Từng thớ thịt giữ được độ ẩm tự nhiên, không bị khô, mang lại cảm giác ăn cực kỳ đã.'
                                ],
                                [
                                    'h' => 'Đậm vị – dễ nghiện',
                                    'p' => 'Gia vị được tẩm ướp kỹ càng, tạo nên hương vị đặc trưng khó quên. Càng ăn càng cuốn, càng ăn càng ghiền – đúng chuẩn món ăn "comfort food".'
                                ],
                                [
                                    'h' => 'Kết hợp hoàn hảo',
                                    'p' => 'Ngon hơn khi ăn nóng cùng tương ớt, sốt mayonnaise hoặc dùng kèm cơm, khoai tây chiên. Phù hợp cho cả ăn một mình lẫn chia sẻ cùng bạn bè.'
                                ],
                            ];
                        } else {
                            $descBlocks = [
                                [
                                    'h' => 'Mô tả sản phẩm',
                                    'p' => $product['description'] ?? 'No description'
                                ],
                            ];
                        }
                    ?>

                    <div class="prod-tab__content prod-tab__content--current" id="tab-desc">
                        <div class="row">
                            <div class="col-8 col-xl-10 col-lg-12">
                                <div class="text-content prod-tab__text-content">
                                    <h2><?= htmlspecialchars($productName) ?></h2>

                                    <p>
                                        <?= htmlspecialchars($descBlocks[0]['p']) ?>
                                    </p>

                                    <p>
                                        <img src="<?= $base ?>assets/img/product/<?= htmlspecialchars($descImage) ?>"
                                            alt="<?= htmlspecialchars($productName) ?>" />
                                        <em>Hình ảnh thực tế của <?= htmlspecialchars($productName) ?></em>
                                    </p>

                                    <?php for ($i = 0; $i < count($descBlocks); $i++): ?>
                                        <?php if ($i > 0): ?>
                                            <hr />
                                        <?php endif; ?>

                                        <h3><?= htmlspecialchars($descBlocks[$i]['h']) ?></h3>
                                        <p><?= htmlspecialchars($descBlocks[$i]['p']) ?></p>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Review -->
                    <div class="prod-tab__content" id="tab-review">

                        <!-- FILTER -->
                        <div class="review-filter">
                            <a class="<?= $filterStar=='all'?'active':'' ?>"
                            href="<?= $base ?>index.php?url=product&id=<?= $product['id'] ?>&star=all">
                            Tất cả
                            </a>

                            <?php for($i=5;$i>=1;$i--): ?>
                                <a class="<?= $filterStar==$i?'active':'' ?>"
                                href="<?= $base ?>index.php?url=product&id=<?= $product['id'] ?>&star=<?= $i ?>">
                                    <?= $i ?> ★
                                </a>
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
                                <div class="row">
                                    <span><?= $i ?>★</span>
                                    <div class="bar">
                                        <div style="width:<?= $percent ?>%"></div>
                                    </div>
                                    <span><?= $starCount[$i] ?></span>
                                </div>
                                <?php endfor; ?>
                            </div>

                        </div>

                        <!-- FORM -->
                        <?php if(isset($_SESSION['user'])): ?>
                            <form method="POST"
                                action="<?= $base ?>index.php?url=add-review"
                                class="review-form"
                                enctype="multipart/form-data">

                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

                                <!-- ⭐ RATING -->
                                <div class="review-form__group">
                                    <label>Đánh giá:</label>
                                    <div class="star-input">
                                        <?php for($i=5;$i>=1;$i--): ?>
                                            <input type="radio" name="rating" value="<?= $i ?>" id="star<?= $i ?>" required>
                                            <label for="star<?= $i ?>">★</label>
                                        <?php endfor; ?>
                                    </div>
                                </div>

                                <!-- 📝 COMMENT -->
                                <div class="review-form__group">
                                    <textarea name="comment"
                                            rows="3"
                                            required
                                            placeholder="Chia sẻ cảm nhận của bạn về sản phẩm..."></textarea>
                                </div>

                                <!-- 📸 UPLOAD IMAGE -->
                                <div class="review-form__group">
                                    <label>Ảnh thực tế (tuỳ chọn):</label>
                                    <input type="file" name="images[]" multiple accept="image/*">
                                </div>

                                <!-- 🔥 PREVIEW IMAGE -->
                                <div class="review-images" id="previewImages"></div>

                                <!-- 🚀 SUBMIT -->
                                <button class="btn btn--primary">Gửi đánh giá</button>
                            </form>
                        <?php endif; ?>

                        <!-- LIST -->
                        <div class="review-list">
                            <?php foreach($reviewsShow as $rev): ?>
                            <div class="review-card">

                                <!-- LIKE REVIEW -->
                                <div class="review-like"
                                    onclick="likeReview(<?= $rev['id'] ?>, this)">
                                    👍 Hữu ích (<?= $rev['likes'] ?? 0 ?>)
                                </div>

                                <!-- REVIEW INFO -->
                                <div class="review-card__top">
                                    <img src="<?= $base ?>assets/img/avatar/<?= $rev['avatar'] ?? 'avatar-1.png' ?>" class="review-card__avatar">

                                    <div class="review-card__info">
                                        <div class="review-card__name">
                                            <?= htmlspecialchars($rev['full_name']) ?>
                                        </div>

                                        <div class="review-card__stars">
                                            <?php for($i=1;$i<=5;$i++): ?>
                                                <span class="<?= $i <= $rev['rating'] ? 'star active' : 'star' ?>">★</span>
                                            <?php endfor; ?>
                                        </div>

                                        <div class="review-card__time">
                                            <?= date('d/m/Y', strtotime($rev['created_at'])) ?>
                                        </div>
                                    </div>
                                </div>


                                <div class="review-card__content">
                                    <?= nl2br(htmlspecialchars($rev['comment'])) ?>
                                </div>
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
                            <div class="review-empty">
                                Chưa có đánh giá nào 😢
                            </div>
                        <?php endif; ?>

                    </div>

                    <!-- Similar Tab -->
                    <div class="prod-tab__content" id="tab-similar">
                        <div class="prod-content">
                            <h2 class="prod-content__heading">Similar Products</h2>
                            <div class="row row-cols-6 row-cols-xl-4 row-cols-lg-3 row-cols-md-2 row-cols-sm-1 g-2">
                                <?php foreach ($similarProducts as $sim): ?>
                                    <div class="col">
                                        <article class="product-card">
                                            <div class="product-card__img-wrap">
                                                <a href="<?= $base ?>index.php?url=product-detail&id=<?= $sim['id'] ?>">
                                                    <img src="<?= $base ?>assets/img/product/<?= $sim['images'][0] ?>" class="product-card__thumb" />
                                                </a>
                                                <button class="like-btn product-card__like-btn">
                                                    <img src="<?= $base ?>assets/icons/heart.svg" class="like-btn__icon icon" />
                                                    <img src="<?= $base ?>assets/icons/heart-red.svg" class="like-btn__icon--liked" />
                                                </button>
                                            </div>
                                            <h3 class="product-card__title">
                                                <a href="<?= $base ?>index.php?url=product-detail&id=<?= $sim['id'] ?>"><?= $sim['name'] ?></a>
                                            </h3>
                                            <div class="product-card__row">
                                                <span class="product-card__price"><?= number_format($sim['base_price']) ?>đ</span>
                                                <img src="<?= $base ?>assets/icons/star.svg" class="product-card__star" />
                                                <span class="product-card__score"><?= $sim['avg_rating'] ?? 0 ?></span>
                                            </div>
                                        </article>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
</main>

<script>
    // add to cart
    function addCart() {
        const isLogin = <?= isset($_SESSION['user']) ? 'true' : 'false' ?>;
        if (!isLogin) {
            alert("Chưa đăng nhập!");
            window.location.href = "index.php?url=login";
            return;
        }
        document.querySelector(".add-cart-form").submit();
    }

    let holdInterval;

    function changeQty(n){
        const input = document.querySelector('.qty-input');
        let val = Number(input.value);
        val += n;
        if(val < 1) val = 1;

        input.value = val;

        // animation nhẹ
        input.style.transform = "scale(1.2)";
        setTimeout(() => input.style.transform = "scale(1)", 150);
    }

    // giữ nút để tăng nhanh
    document.querySelectorAll('.qty-btn').forEach(btn => {
        btn.addEventListener('mousedown', () => {
            holdInterval = setInterval(() => {
                btn.click();
            }, 120);
        });

        document.addEventListener('mouseup', () => {
            clearInterval(holdInterval);
        });
    });


    function updateTotal() {
        const basePrice = <?= $product['base_price'] ?>;

        const variant = document.querySelector('input[name="variant_id"]:checked');
        const variantPrice = variant ? Number(variant.dataset.price) : 0;

        let toppingTotal = 0;
        let toppingCount = 0;

        document.querySelectorAll('input[name="toppings[]"]:checked').forEach(cb => {
            toppingTotal += Number(cb.dataset.price);
            toppingCount++;
        });

        document.getElementById('topping-count').innerText = "(" + toppingCount + ")";

        const qty = Number(document.querySelector('.qty-input').value) || 1;

        const finalPrice = (basePrice + variantPrice + toppingTotal) * qty;

        // 👇 HIỂN THỊ GIÁ ĐÚNG
        document.getElementById('prod-price').innerText =
            (basePrice + variantPrice).toLocaleString() + 'đ';

        document.getElementById('prod-total-price').innerText =
            finalPrice.toLocaleString() + 'đ';
    }
    // change variant
    document.querySelectorAll('input[name="variant_id"]').forEach(radio => {
        radio.addEventListener('change', updateTotal);
    });

    // change topping
    document.querySelectorAll('input[name="toppings[]"]').forEach(cb => {
        cb.addEventListener('change', updateTotal);
    });

// hiệu ứng tiền bay
    function showFloat(text){
        const el = document.createElement("div");
        el.className = "float-price";
        el.innerText = text;

        document.body.appendChild(el);

        setTimeout(() => el.remove(), 600);
    }

    // tabs
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {

            document.querySelectorAll('.tab-btn').forEach(b =>
                b.classList.remove('prod-tab__item--current')
            );

            document.querySelectorAll('.prod-tab__content').forEach(c =>
                c.classList.remove('prod-tab__content--current')
            );

            btn.classList.add('prod-tab__item--current');

            const tabId = btn.dataset.tab;
            document.getElementById(tabId).classList.add('prod-tab__content--current');
        });
    });

    // like review
    function likeReview(id, el){
        fetch("index.php?url=like-review&id=" + id)
        .then(res => res.text())
        .then(data => {
            el.classList.add('active');
            el.innerText = "👍 Đã thích (" + data + ")";
        });
    }

    const input = document.querySelector('input[name="images[]"]');
    const preview = document.getElementById('previewImages');

    if(input){
        input.addEventListener('change', function(){
            preview.innerHTML = '';
            [...this.files].forEach(file => {
                const reader = new FileReader();
                reader.onload = e => {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    preview.appendChild(img);
                }
                reader.readAsDataURL(file);
            });
        });
    }
</script>
