<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user'])) {
    header("Location: index.php?url=login");
    exit;
}

$user_id = $_SESSION['user']['id'];
$conn = getDB();

// Lấy giỏ hàng từ session
$cart = $_SESSION['cart'] ?? [];
$subtotal = 0;
$itemCount = 0;
foreach ($cart as $item) {
    $itemCount += $item['quantity'];
    $subtotal += $item['price'] * $item['quantity'];
}

// Lấy mã giảm giá đã lưu
$discount = $_SESSION['discount'] ?? 0;
$couponCode = $_SESSION['coupon_code'] ?? '';

// Lấy địa chỉ giao hàng mặc định
$defaultAddress = null;
$stmt = $conn->prepare("SELECT * FROM shipping_addresses WHERE user_id = ? AND is_default = 1 LIMIT 1");
$stmt->execute([$user_id]);
$defaultAddress = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$defaultAddress) {
    $stmt = $conn->prepare("SELECT * FROM shipping_addresses WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $defaultAddress = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$defaultAddress) {
    header("Location: index.php?url=shipping");
    exit;
}

$shipping_fee = 10000;
$total = $subtotal - $discount + $shipping_fee;
if ($total < 0) $total = 0;

if (!function_exists('vnd')) {
    function vnd($number) {
        return number_format($number, 0, ',', '.') . 'đ';
    }
}
?>

<style>
.hide {
    display: none;
}

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 999;
    align-items: center;
    justify-content: center;
}
.modal.show {
    display: flex !important;
}
.modal.hide {
    display: none !important;
}
.modal__overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1;
}
.modal__content {
    position: relative;
    z-index: 2;
    background: #fff;
    border-radius: 16px;
    padding: 20px;
    max-width: 90%;
    max-height: 90%;
    overflow-y: auto;
}
</style>
<main class="checkout-page">
    <div class="container">
        <!-- Search bar -->
        <div class="checkout-container">
            <div class="search-bar d-none d-md-flex">
                <input type="text" placeholder="Tìm kiếm sản phẩm" class="search-bar__input" />
                <button class="search-bar__submit">
                    <img src="<?= $base ?>assets/icons/search.svg" alt="" class="search-bar__icon icon" />
                </button>
            </div>
        </div>

        <!-- Breadcrumbs -->
        <div class="checkout-container">
            <ul class="breadcrumbs checkout-page__breadcrumbs">
                <li><a href="./" class="breadcrumbs__link">Trang chủ <img src="<?= $base ?>assets/icons/arrow-right.svg" /></a></li>
                <li><a href="index.php?url=checkout" class="breadcrumbs__link">Thanh toán <img src="<?= $base ?>assets/icons/arrow-right.svg" /></a></li>
                <li><a href="index.php?url=shipping" class="breadcrumbs__link">Vận chuyển <img src="<?= $base ?>assets/icons/arrow-right.svg" /></a></li>
                <li><a href="#!" class="breadcrumbs__link breadcrumbs__link--current">Phương thức thanh toán</a></li>
            </ul>
        </div>

        <!-- TABS -->
        <div class="checkout-container">
            <div class="prod-tab js-tabs">
                <ul class="prod-tab__list">
                    <li class="prod-tab__item tab-btn prod-tab__item--current" data-tab="tab-card">💳 Thanh toán thẻ</li>
                    <li class="prod-tab__item tab-btn" data-tab="tab-alternative">📦 Thanh toán khác (COD / VNPAY)</li>
                </ul>

                <div class="prod-tab__contents">

                    <!-- ========== TAB 1: THANH TOÁN THẺ ========== -->
                    <div class="prod-tab__content prod-tab__content--current" id="tab-card">
                        <div class="row gy-xl-3">
                            <div class="col-8 col-xl-8 col-lg-12">
                                <!-- Shipping Info -->
                                <div class="cart-info cart-info--shadow">
                                    <div class="cart-info__top">
                                        <h2 class="cart-info__heading cart-info__heading--lv2">1. Giao hàng dự kiến từ <?= date('d/m/Y', strtotime('+3 days')) ?> đến <?= date('d/m/Y', strtotime('+8 days')) ?></h2>
                                        <a class="cart-info__edit-btn" href="index.php?url=shipping"><img class="icon" src="<?= $base ?>assets/icons/edit.svg" alt="" /> Sửa</a>
                                    </div>
                                    <article class="payment-item payment-item--card">
                                        <div class="payment-item__info">
                                            <h3 class="payment-item__title">Họ và tên: <?= htmlspecialchars($defaultAddress['full_name'] ?? '') ?></h3>
                                            <p class="payment-item__desc">Địa chỉ: <?= htmlspecialchars($defaultAddress['address'] ?? '') ?>, Thành phố: <?= htmlspecialchars($defaultAddress['city'] ?? '') ?></p>
                                            <?php if ($defaultAddress['is_default'] ?? 0): ?><span class="payment-item__badge">Mặc định</span><?php endif; ?>
                                        </div>
                                    </article>
                                    <article class="payment-item payment-item--card">
                                        <div class="payment-item__info">
                                            <h3 class="payment-item__title">Chi tiết sản phẩm</h3>
                                            <p class="payment-item__desc"><?= $itemCount ?> sản phẩm</p>
                                        </div>
                                        <a href="index.php?url=checkout" class="payment-item__detail">Xem chi tiết</a>
                                    </article>
                                </div>

                                <!-- Shipping Method -->
                                <div class="cart-info cart-info--shadow">
                                    <h2 class="cart-info__heading cart-info__heading--lv2">2. Phương thức vận chuyển</h2>
                                    <div class="cart-info__separate"></div>
                                    <h3 class="cart-info__sub-heading">Các phương thức có sẵn</h3>
                                    <div id="shipping-methods">
                                        <label>
                                            <article class="payment-item payment-item--pointer payment-item--highlight">
                                                <img src="<?= $base ?>assets/img/payment/delivery-1.png" class="payment-item__thumb" />
                                                <div class="payment-item__content">
                                                    <div class="payment-item__info">
                                                        <h3 class="payment-item__title">Giao hàng tiêu chuẩn</h3>
                                                        <p class="payment-item__desc payment-item__desc--low">Giao trong 2-3 ngày làm việc</p>
                                                        <small class="payment-item__note">Miễn phí vận chuyển cho đơn hàng trên <?= vnd(100000) ?></small>
                                                    </div>
                                                    <span class="cart-info__checkbox payment-item__checkbox">
                                                        <input type="radio" name="delivery-method" value="fedex" data-fee="0" checked />
                                                        <span class="payment-item__cost">Miễn phí</span>
                                                    </span>
                                                </div>
                                            </article>
                                        </label>
                                        <label>
                                            <article class="payment-item payment-item--pointer">
                                                <img src="<?= $base ?>assets/img/payment/delivery-2.png" class="payment-item__thumb" />
                                                <div class="payment-item__content">
                                                    <div class="payment-item__info">
                                                        <h3 class="payment-item__title">Giao hàng nhanh</h3>
                                                        <p class="payment-item__desc payment-item__desc--low">Giao trong 1-2 ngày làm việc</p>
                                                        <small class="payment-item__note">Giao hàng hỏa tốc</small>
                                                    </div>
                                                    <span class="cart-info__checkbox payment-item__checkbox">
                                                        <input type="radio" name="delivery-method" value="dhl" data-fee="12000" />
                                                        <span class="payment-item__cost"><?= vnd(12000) ?></span>
                                                    </span>
                                                </div>
                                            </article>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Payment Details -->
                            <div class="col-4 col-xl-4 col-lg-12">
                                <div class="cart-info cart-info--shadow">
                                    <h2 class="cart-info__heading cart-info__heading--lv2">Chi tiết thanh toán</h2>
                                    <p class="cart-info__desc">Hoàn tất mua hàng bằng cách cung cấp thông tin thẻ của bạn.</p>
                                    <form id="payment-form" class="form cart-info__form">
                                        <div class="form__group">
                                            <label class="form__label form__label--medium">Địa chỉ Email</label>
                                            <div class="form__text-input"><input type="email" id="email" value="<?= htmlspecialchars($_SESSION['user']['email'] ?? '') ?>" class="form__input" required /></div>
                                        </div>
                                        <div class="form__group">
                                            <label class="form__label form__label--medium">Chủ thẻ</label>
                                            <div class="form__text-input"><input type="text" id="card-holder" placeholder="Tên chủ thẻ" class="form__input" required /></div>
                                        </div>
                                        <div class="form__group">
                                            <label class="form__label form__label--medium">Số thẻ</label>
                                            <div class="form__text-input"><input type="text" id="card-details" placeholder="Số thẻ" class="form__input" required /></div>
                                        </div>
                                        <div class="form__row cart-info__form-row">
                                            <div class="form__group form__group--flex">
                                                <input type="text" id="card-expire" placeholder="MM/YY" class="form__input" required />
                                                <input type="text" id="card-cvc" placeholder="CVC" class="form__input" required />
                                            </div>
                                        </div>
                                    </form>
                                    <div class="cart-info__summary">
                                        <div class="cart-info__row"><span>Tạm tính (<?= $itemCount ?> sản phẩm)</span><span id="subtotal"><?= vnd($subtotal) ?></span></div>
                                        <?php if ($discount > 0): ?>
                                        <div class="cart-info__row"><span>Giảm giá (<?= htmlspecialchars($couponCode) ?>)</span><span id="discount">-<?= vnd($discount) ?></span></div>
                                        <?php endif; ?>
                                        <div class="cart-info__row"><span>Vận chuyển</span><span id="shipping-cost"><?= vnd($shipping_fee) ?></span></div>
                                        <div class="cart-info__separate"></div>
                                        <div class="cart-info__row cart-info__row--highlight"><span>Tổng cộng</span><span id="estimated-total"><?= vnd($total) ?></span></div>
                                    </div>
                                    <button type="button" id="pay-btn" class="cart-info__next-btn btn btn--primary btn--rounded">Thanh toán <?= vnd($total) ?></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ========== TAB 2: THANH TOÁN KHÁC (ĐÃ LÀM ĐẸP) ========== -->
                    <div class="prod-tab__content" id="tab-alternative">
                        <div class="row gy-xl-3">

                            <!-- LEFT: FORM NHẬP ĐỊA CHỈ -->
                            <div class="col-8 col-xl-8 col-lg-12">
                                <!-- Shipping Info -->
                                <div class="cart-info cart-info--shadow">
                                    <div class="cart-info__top">
                                        <h2 class="cart-info__heading cart-info__heading--lv2">1. Giao hàng dự kiến từ <?= date('d/m/Y', strtotime('+3 days')) ?> đến <?= date('d/m/Y', strtotime('+8 days')) ?></h2>
                                        <a class="cart-info__edit-btn" href="index.php?url=shipping">
                                            <img class="icon" src="<?= $base ?>assets/icons/edit.svg" alt="" /> Sửa
                                        </a>
                                    </div>
                                    <article class="payment-item payment-item--card">
                                        <div class="payment-item__info">
                                            <h3 class="payment-item__title"><?= htmlspecialchars($defaultAddress['full_name'] ?? '') ?></h3>
                                            <p class="payment-item__desc"><?= htmlspecialchars($defaultAddress['address'] ?? '') ?>, <?= htmlspecialchars($defaultAddress['city'] ?? '') ?></p>
                                            <?php if ($defaultAddress['is_default'] ?? 0): ?><span class="payment-item__badge">Mặc định</span><?php endif; ?>
                                        </div>
                                    </article>
                                    <article class="payment-item payment-item--card">
                                        <div class="payment-item__info">
                                            <h3 class="payment-item__title">Chi tiết sản phẩm</h3>
                                            <p class="payment-item__desc"><?= $itemCount ?> sản phẩm</p>
                                        </div>
                                        <a href="index.php?url=checkout" class="payment-item__detail">Xem chi tiết</a>
                                    </article>
                                </div>

                                <!-- Shipping Method -->
                                <div class="cart-info cart-info--shadow">
                                    <h2 class="cart-info__heading cart-info__heading--lv2">2. Phương thức vận chuyển</h2>
                                    <div class="cart-info__separate"></div>
                                    <h3 class="cart-info__sub-heading">Các phương thức có sẵn</h3>
                                    <div id="shipping-methods-alt">
                                        <label>
                                            <article class="payment-item payment-item--pointer payment-item--highlight">
                                                <img src="<?= $base ?>assets/img/payment/delivery-1.png" class="payment-item__thumb" />
                                                <div class="payment-item__content">
                                                    <div class="payment-item__info">
                                                        <h3 class="payment-item__title">Giao hàng tiêu chuẩn</h3>
                                                        <p class="payment-item__desc payment-item__desc--low">Giao trong 2-3 ngày làm việc</p>
                                                        <small class="payment-item__note">Miễn phí vận chuyển cho đơn hàng trên <?= vnd(100000) ?></small>
                                                    </div>
                                                    <span class="cart-info__checkbox payment-item__checkbox">
                                                        <input type="radio" name="delivery-method-alt" value="standard" data-fee="0" checked />
                                                        <span class="payment-item__cost">Miễn phí</span>
                                                    </span>
                                                </div>
                                            </article>
                                        </label>
                                        <label>
                                            <article class="payment-item payment-item--pointer">
                                                <img src="<?= $base ?>assets/img/payment/delivery-2.png" class="payment-item__thumb" />
                                                <div class="payment-item__content">
                                                    <div class="payment-item__info">
                                                        <h3 class="payment-item__title">Giao hàng nhanh</h3>
                                                        <p class="payment-item__desc payment-item__desc--low">Giao trong 1-2 ngày làm việc</p>
                                                        <small class="payment-item__note">Giao hàng hỏa tốc</small>
                                                    </div>
                                                    <span class="cart-info__checkbox payment-item__checkbox">
                                                        <input type="radio" name="delivery-method-alt" value="express" data-fee="12000" />
                                                        <span class="payment-item__cost"><?= vnd(12000) ?></span>
                                                    </span>
                                                </div>
                                            </article>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- RIGHT: TÓM TẮT + PHƯƠNG THỨC THANH TOÁN -->
                            <div class="col-4 col-xl-4 col-lg-12">
                                <div class="cart-info cart-info--shadow">
                                    <h2 class="cart-info__heading cart-info__heading--lv2">Đơn hàng của bạn</h2>

                                    <!-- Danh sách sản phẩm -->
                                    <div class="cart-info__list">
                                        <?php foreach ($cart as $item): ?>
                                            <div class="cart-info__row">
                                                <span><?= htmlspecialchars($item['name']) ?> <small>x<?= $item['quantity'] ?></small></span>
                                                <span><?= vnd($item['price'] * $item['quantity']) ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <div class="cart-info__separate"></div>

                                    <!-- Tổng tiền -->
                                    <div class="cart-info__summary">
                                        <div class="cart-info__row"><span>Tạm tính</span><span><?= vnd($subtotal) ?></span></div>
                                        <?php if ($discount > 0): ?>
                                        <div class="cart-info__row"><span>Giảm giá (<?= htmlspecialchars($couponCode) ?>)</span><span>-<?= vnd($discount) ?></span></div>
                                        <?php endif; ?>
                                        <div class="cart-info__row"><span>Vận chuyển</span><span><?= vnd($shipping_fee) ?></span></div>
                                        <div class="cart-info__separate"></div>
                                        <div class="cart-info__row cart-info__row--highlight"><span>Tổng cộng</span><span><?= vnd($total) ?></span></div>
                                    </div>

                                    <div class="cart-info__separate"></div>

                                    <!-- Chọn phương thức thanh toán -->
                                    <h3 class="cart-info__sub-heading">Chọn phương thức thanh toán</h3>

                                    <label class="payment-item payment-item--pointer payment-item--card">
                                        <input type="radio" name="alt_payment_method" value="online" checked>
                                        <div class="payment-item__content">
                                            <div class="payment-item__info">
                                                <h4 class="payment-item__title">💳 Thanh toán online</h4>
                                                <p class="payment-item__desc">Chọn VNPAY, MoMo, ZaloPay hoặc chuyển khoản QR</p>
                                            </div>
                                        </div>
                                    </label>

                                    <label class="payment-item payment-item--pointer payment-item--card">
                                        <input type="radio" name="alt_payment_method" value="cod">
                                        <div class="payment-item__content">
                                            <div class="payment-item__info">
                                                <h4 class="payment-item__title">📦 Thanh toán khi nhận hàng (COD)</h4>
                                                <p class="payment-item__desc">Trả tiền mặt khi nhận hàng</p>
                                            </div>
                                        </div>
                                    </label>

                                    <!-- Đồng ý điều khoản -->
                                    <div class="form__group mt-3">
                                        <label class="form__checkbox">
                                            <input type="checkbox" id="agree-terms" checked>
                                            <span>Tôi đồng ý với <a href="#">Điều khoản dịch vụ</a></span>
                                        </label>
                                    </div>

                                    <button type="button" id="alt-pay-btn" class="cart-info__next-btn btn btn--primary btn--rounded mt-3">
                                        Xác nhận thanh toán
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</main>

<div id="payment-method-modal" class="modal hide">
    <div class="modal__content" style="max-width: 860px;">
        <div class="modal__header">
            <h2 class="modal__heading">Chọn phương thức thanh toán online</h2>
            <button class="modal__close js-toggle" toggle-target="#payment-method-modal">&times;</button>
        </div>

        <div class="modal__body">
            <div class="payment-methods-grid">
                <button type="button" class="payment-method-card" data-method="vnpay">
                    <div class="payment-method-icon">💳</div>
                    <h4>VNPAY</h4>
                    <p>Thanh toán qua ATM / QR VNPAY</p>
                    <div class="qr-placeholder">
                        <img src="<?= $base ?>assets/img/qr/vnpay-qr.png" alt="QR VNPAY" style="width:100%; border-radius:16px;">
                    </div>
                </button>

                <button type="button" class="payment-method-card" data-method="momo">
                    <div class="payment-method-icon">💜</div>
                    <h4>MoMo</h4>
                    <p>Ví điện tử MoMo</p>
                    <div class="qr-placeholder">
                        <img src="<?= $base ?>assets/img/qr/momo-qr.png" alt="QR MoMo" style="width:100%; border-radius:16px;">
                    </div>
                </button>

                <button type="button" class="payment-method-card" data-method="zalopay">
                    <div class="payment-method-icon">🟦</div>
                    <h4>ZaloPay</h4>
                    <p>Thanh toán qua ZaloPay</p>
                    <div class="qr-placeholder">
                        <img src="<?= $base ?>assets/img/qr/zalopay-qr.png" alt="QR ZaloPay" style="width:100%; border-radius:16px;">
                    </div>
                </button>

                <button type="button" class="payment-method-card" data-method="mbbank">
                    <div class="payment-method-icon">🏦</div>
                    <h4>MB Bank</h4>
                    <p>Chuyển khoản ngân hàng</p>
                    <div class="qr-placeholder">
                        <img src="<?= $base ?>assets/img/qr/mbbank-qr.png" alt="QR MB Bank" style="width:100%; border-radius:16px;">
                    </div>
                </button>
            </div>

            <div class="payment-selected-box" id="payment-selected-box" style="display:none; margin-top:18px;">
                <div class="payment-selected-box__title">Đang chọn: <span id="selected-method-name"></span></div>
                <div class="payment-selected-box__qr">
                    <img id="selected-method-qr" src="" alt="QR" style="width:220px; max-width:100%; border-radius:18px;">
                </div>
                <p id="selected-method-desc" style="margin-top:10px; color:#666;"></p>
            </div>
        </div>

        <div class="modal__footer">
            <button class="btn btn--small btn--text js-toggle" toggle-target="#payment-method-modal">Hủy</button>
            <button class="btn btn--small btn--primary" id="confirm-payment-method">Xác nhận thanh toán</button>
        </div>
    </div>
    <div class="modal__overlay js-toggle" toggle-target="#payment-method-modal"></div>
</div>

<div id="alert-modal" class="modal hide">
    <div class="modal__content" style="max-width:400px; text-align:center;">
        <div class="modal__body">
            <h3 id="alert-title" style="margin-bottom:10px;"></h3>
            <p id="alert-message" style="color:#666;"></p>
        </div>
        <div class="modal__footer" style="justify-content:center;">
            <button class="btn btn--primary" id="alert-ok">OK</button>
        </div>
    </div>
    <div class="modal__overlay"></div>
</div>

<script>
// ========== TAB SWITCHING ==========
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('prod-tab__item--current'));
        document.querySelectorAll('.prod-tab__content').forEach(c => c.classList.remove('prod-tab__content--current'));
        btn.classList.add('prod-tab__item--current');
        document.getElementById(btn.dataset.tab).classList.add('prod-tab__content--current');
    });
});

function formatVND(amount) {
    return amount.toLocaleString('vi-VN') + 'đ';
}

// ========== UPDATE TOTAL ==========
document.querySelectorAll('input[name="delivery-method-alt"]').forEach(radio => {
    radio.addEventListener('change', function () {
        const fee = parseInt(this.dataset.fee) || 0;
        const subtotalRaw = <?= $subtotal ?>;
        const discountRaw = <?= $discount ?>;
        const newTotal = subtotalRaw - discountRaw + fee;

        const totalSpan = document.querySelector('#tab-alternative .cart-info__row--highlight span:last-child');
        if (totalSpan) totalSpan.innerText = formatVND(newTotal);

        const btn = document.getElementById('alt-pay-btn');
        if (btn) btn.innerText = 'Xác nhận thanh toán ' + formatVND(newTotal);
    });
});

// ========== MODAL ==========
let selectedOnlineMethod = 'vnpay';

function openModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;

    modal.classList.remove('hide');
    modal.classList.add('show');
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;

    modal.classList.remove('show');
    modal.classList.add('hide');
}

// ❌ REMOVE js-toggle conflict → FIX
// chỉ dùng close riêng cho nút close
document.querySelectorAll('.modal__close').forEach(btn => {
    btn.addEventListener('click', function () {
        const target = this.getAttribute('toggle-target');
        if (target) closeModal(target.replace('#', ''));
    });
});

// ✅ overlay chỉ đóng modal cha
document.querySelectorAll('.modal__overlay').forEach(overlay => {
    overlay.addEventListener('click', function () {
        const modal = this.closest('.modal');
        if (modal) closeModal(modal.id);
    });
});

// ❌ chặn bubbling để tránh auto close
document.querySelectorAll('.modal__content').forEach(content => {
    content.addEventListener('click', e => e.stopPropagation());
});

// ========== CHỌN METHOD ==========
document.querySelectorAll('.payment-method-card').forEach(card => {
    card.addEventListener('click', function () {
        document.querySelectorAll('.payment-method-card').forEach(c => c.classList.remove('selected'));
        this.classList.add('selected');
        selectedOnlineMethod = this.dataset.method;
    });
});

// ========== CONFIRM MODAL ==========
document.getElementById('confirm-payment-method').addEventListener('click', async function () {
    closeModal('payment-method-modal');
    await processOrder(selectedOnlineMethod);
});

// ========== BUTTON MAIN ==========
document.getElementById('alt-pay-btn').addEventListener('click', async function () {
    const agree = document.getElementById('agree-terms').checked;

    if (!agree) {
        showAlert('Thông báo', 'Vui lòng chấp nhận điều khoản');
        return;
    }

    const method = document.querySelector('input[name="alt_payment_method"]:checked')?.value;

    if (method === 'cod') {
        await processOrder('cash');
    } else {
        // 🔥 FIX: delay nhẹ tránh conflict event
        setTimeout(() => {
            openModal('payment-method-modal');
        }, 50);
    }
});

// ========== PROCESS ORDER ==========
async function processOrder(paymentMethod) {
    const selectedShipping = document.querySelector('input[name="delivery-method-alt"]:checked');
    const shippingFee = selectedShipping ? parseInt(selectedShipping.dataset.fee) || 0 : 0;
    const shippingMethod = selectedShipping?.value || 'standard';

    const btn = document.getElementById('alt-pay-btn');
    btn.disabled = true;
    btn.innerText = 'Đang xử lý...';

    try {
        const payload = {
            shipping_method: shippingMethod,
            shipping_fee: shippingFee,
            payment_method: paymentMethod
        };

        const res = await fetch('index.php?url=create-order', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const data = await res.json();
        if (!data.success) throw new Error(data.message);

        if (data.payment_url) {
            window.location.href = data.payment_url;
            return;
        }

        await fetch('index.php?url=clearCoupon', { method: 'POST' });
        await fetch('index.php?url=remove-all-cart');

        showAlert('Thành công', 'Đặt hàng thành công!', () => {
            window.location.href = 'index.php?url=orders';
        });

    } catch (err) {
        showAlert('Lỗi', err.message);
        btn.disabled = false;
        btn.innerText = 'Xác nhận thanh toán';
    }
}

// ========== ALERT ==========
function showAlert(title, message, callback = null) {
    const modal = document.getElementById('alert-modal');

    document.getElementById('alert-title').innerText = title;
    document.getElementById('alert-message').innerText = message;

    modal.classList.remove('hide');
    modal.classList.add('show');

    const okBtn = document.getElementById('alert-ok');

    const handler = () => {
        modal.classList.remove('show');
        modal.classList.add('hide');
        okBtn.removeEventListener('click', handler);
        if (callback) callback();
    };

    okBtn.addEventListener('click', handler);
}
</script>
