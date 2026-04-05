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
.tab-btn.active{
    color:red;
}

.prod-tab__content{
    display:none;
}
.prod-tab__content.active{
    display:block;
}

/* FILTER */
.review-filter a{
    margin-right:10px;
}

/* REVIEW */
.review-item{
    border-bottom:1px solid #ddd;
    padding:10px 0;
}

/* PAGINATION */
.pagination a{
    margin:5px;
    padding:5px 10px;
    border:1px solid #ddd;
}
.pagination a.active{
    background:red;
    color:#fff;
}.prod-tab__content {
    display: none;
}

.prod-tab__content--current {
    display: block;
}

/* STAR */
.review-card__stars{
    color:#ccc;
    font-size:14px;
}

.review-card__stars .active{
    color:#f5a623;
}

/* STAR INPUT */


/* ===== REVIEW FILTER ===== */
.review-filter{
    margin-bottom:15px;
}
.review-filter a{
    padding:6px 12px;
    border-radius:20px;
    background:#f5f5f5;
    text-decoration:none;
    color:#333;
    margin-right:8px;
    font-size:13px;
    transition:0.2s;
}
.review-filter a:hover{
    background:#ffe0b2;
}
.review-filter a.active{
    background:#f5a623;
    color:#fff;
}

/* ===== REVIEW SUMMARY ===== */
.review-summary{
    display:flex;
    gap:40px;
    background:#fff;
    padding:20px;
    border-radius:16px;
    margin:20px 0;
    box-shadow:0 4px 15px rgba(0,0,0,0.05);
}

.review-summary__left{
    min-width:120px;
    text-align:center;
}

.review-summary__left .avg{
    font-size:40px;
    font-weight:bold;
    color:#f5a623;
}

.review-summary__left .stars span{
    font-size:18px;
    color:#ccc;
}
.review-summary__left .stars .active{
    color:#f5a623;
}

.review-summary__left .total{
    font-size:13px;
    color:#777;
}

/* RIGHT BAR */
.review-summary__right{
    flex:1;
}

.review-summary__right .row{
    display:flex;
    align-items:center;
    gap:10px;
    margin:6px 0;
    font-size:13px;
}

.review-summary__right .bar{
    flex:1;
    height:8px;
    background:#eee;
    border-radius:10px;
    overflow:hidden;
}

.review-summary__right .bar div{
    height:100%;
    background:linear-gradient(90deg,#f5a623,#ffcc80);
}

/* ===== REVIEW FORM ===== */
.review-form{
    background:#fff;
    padding:15px;
    border-radius:12px;
    box-shadow:0 2px 10px rgba(0,0,0,0.05);
}

.review-form textarea{
    width:100%;
    border:1px solid #ddd;
    border-radius:10px;
    padding:10px;
    margin-top:10px;
    resize:none;
    font-size:14px;
}

.review-form button{
    margin-top:10px;
}

/* ===== STAR INPUT ===== */
.star-input{
    direction: rtl;
    display:inline-flex;
}
.star-input input{display:none}

.star-input label{
    font-size:28px;
    color:#ccc;
    cursor:pointer;
    transition:0.2s;
}

.star-input input:checked ~ label,
.star-input label:hover,
.star-input label:hover ~ label{
    color:#f5a623;
}

/* ===== REVIEW CARD ===== */
.review-list{
    margin-top:20px;
}

.review-card{
    background:#fff;
    border-radius:16px;
    padding:16px;
    margin-bottom:15px;
    box-shadow:0 3px 12px rgba(0,0,0,0.05);
    transition:0.25s;
}

.review-card:hover{
    transform:translateY(-3px);
    box-shadow:0 6px 18px rgba(0,0,0,0.08);
}

/* TOP */
.review-card__top{
    display:flex;
    gap:12px;
    align-items:center;
}

.review-card__avatar{
    width:45px;
    height:45px;
    border-radius:50%;
    object-fit:cover;
}

.review-card__info{
    flex:1;
}

.review-card__name{
    font-weight:600;
    font-size:14px;
}

.review-card__time{
    font-size:12px;
    color:#999;
}

/* STAR */
.review-card__stars{
    margin-top:3px;
}
.review-card__stars .star{
    color:#ccc;
}
.review-card__stars .active{
    color:#f5a623;
}

/* CONTENT */
.review-card__content{
    margin-top:10px;
    font-size:14px;
    line-height:1.6;
    color:#444;
}

/* ===== IMAGE REVIEW ===== */
.review-images{
    margin-top:10px;
    display:flex;
    gap:10px;
}
.review-images img{
    width:70px;
    height:70px;
    object-fit:cover;
    border-radius:8px;
}

/* ===== LIKE ===== */
.review-like{
    margin-top:10px;
    font-size:13px;
    color:#777;
    cursor:pointer;
}
.review-like.active{
    color:#f5a623;
}


/* EMPTY */
.review-empty{
    text-align:center;
    padding:40px;
    color:#aaa;
    font-size:14px;
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

                            <!-- NAME -->
                            <h1 class="prod-info__heading"><?= $product['name'] ?></h1>
                            <div class="prod-prop">
                                <img src="<?= $base ?>assets/icons/star.svg" class="prod-prop__icon"/>
                                <span class="prod-prop__title">(<?= $product['avg_rating'] ?? 0 ?>) <?= count($reviews) ?> reviews</span>
                            </div>

                            <!-- VARIANTS -->
                            <?php if (!empty($variants)): ?>
                                <label class="form__label">Size / Variant</label>
                                <div class="filter__form-group">
                                    <div class="form__tags">
                                        <?php foreach ($variants as $v): ?>
                                            <button type="button" class="form__tag prod-info__tag variant-btn" data-price="<?= $v['price'] ?>" data-id="<?= $v['id'] ?>">
                                                <?= $v['variant_name'] ?> - <?= number_format($v['price']) ?>đ
                                            </button>
                                        <?php endforeach; ?>
                                    </div>
                                    <input type="hidden" name="variant_id" value="<?= $variants[0]['id'] ?? '' ?>" />
                                </div>
                            <?php endif; ?>

                            <!-- TOPPINGS -->
                            <?php if (!empty($toppings)): ?>
                                <label class="form__label">Toppings</label>
                                <div class="filter__form-group">
                                    <div class="form__tags">
                                        <?php foreach ($toppings as $t): ?>
                                            <label class="form__tag">
                                                <input type="checkbox" name="toppings[]" value="<?= $t['id'] ?>" data-price="<?= $t['price'] ?>" />
                                                <?= $t['name'] ?> (+<?= number_format($t['price']) ?>đ)
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- PRICE -->
                            <div class="prod-info__card">
                                <div class="prod-info__row">
                                    <span class="prod-info__price" id="prod-price"><?= number_format($product['base_price']) ?>đ</span>
                                    <span class="prod-info__tax">10%</span>
                                </div>
                                <p class="prod-info__total-price" id="prod-total-price"><?= number_format($product['base_price'] * 1.1) ?>đ</p>

                                <!-- ADD TO CART / LIKE -->
                                <div class="prod-info__row">
                                    <button type="submit" onclick="addCart()" class="btn btn--primary prod-info__add-to-cart">Add to cart</button>
                                    <button type="button" class="like-btn prod-info__like-btn">
                                        <img src="<?= $base ?>assets/icons/heart.svg" class="like-btn__icon icon" />
                                        <img src="<?= $base ?>assets/icons/heart-red.svg" class="like-btn__icon--liked" />
                                    </button>
                                </div>
                            </div>

                            <!-- DESCRIPTION -->

                                <div class="text-content">
                                    <h2>Mô tả sản phẩm</h2>
                                    <p><?= $product['description'] ?? 'No description' ?></p>
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

                    <!-- Description -->
                    <div class="prod-tab__content prod-tab__content--current" id="tab-desc">
                        <div class="text-content">
                            <h2>Product Description</h2>
                            <p><?= $product['description'] ?? 'No description' ?></p>
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
function addCart() {
    const isLogin = <?= isset($_SESSION['user']) ? 'true' : 'false' ?>;
    if (!isLogin) {
        alert("Chưa đăng nhập!");
        window.location.href = "index.php?url=login";
        return;
    }
    document.querySelector(".add-cart-form").submit();
}

// Variant selection update price
document.querySelectorAll('.variant-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.variant-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        document.querySelector('[name="variant_id"]').value = btn.dataset.id;
        document.getElementById('prod-price').innerText = Number(btn.dataset.price).toLocaleString() + 'đ';
        const totalPrice = Number(btn.dataset.price) * 1.1; // add tax 10%
        document.getElementById('prod-total-price').innerText = totalPrice.toLocaleString() + 'đ';
    });
});

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
