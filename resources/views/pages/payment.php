<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user'])) {
    header("Location: index.php?url=login");
    exit;
}

$_SESSION['selected_address_id'] = $_POST['shipping_address_id'] ?? null;

$user_id = $_SESSION['user']['id'];
$conn = getDB();

// ===================== GIỎ HÀNG =====================
$cart = $_SESSION['cart'] ?? [];
$subtotal = 0;
$itemCount = 0;

foreach ($cart as $item) {
    $itemCount += $item['quantity'];
    $subtotal += $item['price'] * $item['quantity'];
}

// ===================== DISCOUNT =====================
$discount = $_SESSION['discount'] ?? 0;
$couponCode = $_SESSION['coupon_code'] ?? '';

// ===================== ADDRESS =====================
$defaultAddress = null;

$selectedAddressId = $_POST['shipping_address_id']
    ?? $_SESSION['selected_address_id']
    ?? null;

// lấy theo address user chọn
if ($selectedAddressId) {
    $stmt = $conn->prepare("
        SELECT * FROM shipping_addresses
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$selectedAddressId, $user_id]);
    $defaultAddress = $stmt->fetch(PDO::FETCH_ASSOC);
}

// fallback default
if (!$defaultAddress) {
    $stmt = $conn->prepare("
        SELECT * FROM shipping_addresses
        WHERE user_id = ? AND is_default = 1
        LIMIT 1
    ");
    $stmt->execute([$user_id]);
    $defaultAddress = $stmt->fetch(PDO::FETCH_ASSOC);
}

// nếu vẫn không có địa chỉ
if (!$defaultAddress) {
    header("Location: index.php?url=shipping");
    exit;
}

// ===================== FIX QUAN TRỌNG Ở ĐÂY =====================
// đảm bảo key tồn tại
$phone = $defaultAddress['phone'] ?? '';
$hasPhone = !empty(trim($phone));

// ===================== TOTAL =====================
$shipping_fee = ($subtotal >= 100000) ? 0 : 10000;

// ===================== TOTAL =====================
$total = $subtotal - $discount + $shipping_fee;

if ($total < 0) {
    $total = 0;
}

// ===================== FORMAT =====================
if (!function_exists('vnd')) {
    function vnd($number) {
        return number_format($number, 0, ',', '.') . 'đ';
    }
}
?>
<style>
/* CSS Modal sửa lỗi ẩn */
.hide { display: none !important; }
.modal {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    z-index: 9999;
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
    top: 0; left: 0; width: 100%; height: 100%;
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
                    <li class="prod-tab__item tab-btn prod-tab__item--current" data-tab="tab-alternative">📦 Thanh toán khác (COD / Online)</li>
                    <!-- <li class="prod-tab__item tab-btn" data-tab="tab-card">💳 Thanh toán thẻ</li> -->
                </ul>

                <div class="prod-tab__contents">

                    <!-- ========== TAB 1: THANH TOÁN KHÁC ========== -->
                    <div class="prod-tab__content prod-tab__content--current" id="tab-alternative">
                        <!-- ... nội dung Tab 2 giữ nguyên ... -->
                        <div class="row gy-xl-3">
                            <div class="col-8 col-xl-8 col-lg-12">
                                <div class="cart-info cart-info--shadow">
                                    <div class="cart-info__top">
                                        <h2 class="cart-info__heading cart-info__heading--lv2">1. Giao hàng dự kiến từ <?= date('d/m/Y', strtotime('+3 days')) ?> đến <?= date('d/m/Y', strtotime('+8 days')) ?></h2>
                                        <a class="cart-info__edit-btn" href="index.php?url=shipping"><img class="icon" src="<?= $base ?>assets/icons/edit.svg" alt="" /> Sửa</a>
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

                                    <?php if (!$hasPhone): ?>
                                        <p style="color:red; margin-top:10px;">
                                            ⚠ Vui lòng thêm số điện thoại trước khi thanh toán
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <div class="cart-info cart-info--shadow">
                                    <h2 class="cart-info__heading cart-info__heading--lv2">2. Phương thức vận chuyển</h2>
                                    <div class="cart-info__separate"></div>
                                    <h3 class="cart-info__sub-heading">Các phương thức có sẵn</h3>
                                    <!-- Trong #tab-alternative -->
                                    <div id="shipping-methods-alt">
                                        <label>
                                            <article class="payment-item payment-item--pointer payment-item--highlight">
                                                <img src="<?= $base ?>assets/img/payment/delivery-1.png" class="payment-item__thumb" />
                                                <div class="payment-item__content">
                                                    <div class="payment-item__info">
                                                        <h3 class="payment-item__title">Giao hàng tiêu chuẩn (FedEx)</h3>
                                                        <p class="payment-item__desc payment-item__desc--low">Giao trong 2-3 ngày làm việc</p>
                                                        <small class="payment-item__note">Miễn phí vận chuyển cho đơn hàng trên 100.000đ</small>
                                                    </div>
                                                    <span class="cart-info__checkbox payment-item__checkbox">
                                                        <input type="radio"
                                                            name="delivery-method"
                                                            value="fedex_standard"
                                                            data-fee="<?= $shipping_fee ?>"
                                                            checked />
                                                        <span class="payment-item__cost">
                                                            <?= $shipping_fee == 0 ? 'Miễn phí' : vnd($shipping_fee) ?>
                                                        </span>
                                                    </span>
                                                </div>
                                            </article>
                                        </label>

                                        <label>
                                            <article class="payment-item payment-item--pointer">
                                                <img src="<?= $base ?>assets/img/payment/delivery-2.png" class="payment-item__thumb" />
                                                <div class="payment-item__content">
                                                    <div class="payment-item__info">
                                                        <h3 class="payment-item__title">Giao hàng nhanh (DHL)</h3>
                                                        <p class="payment-item__desc payment-item__desc--low">Giao trong 1-2 ngày làm việc</p>
                                                        <small class="payment-item__note">Giao hàng hỏa tốc</small>
                                                    </div>
                                                    <span class="cart-info__checkbox payment-item__checkbox">
                                                        <input type="radio"
                                                            name="delivery-method"
                                                            value="dhl_express"
                                                            data-fee="12000" />
                                                        <span class="payment-item__cost"><?= vnd(12000) ?></span>
                                                    </span>
                                                </div>
                                            </article>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-4 col-xl-4 col-lg-12">
                                <div class="cart-info cart-info--shadow">
                                    <h2 class="cart-info__heading cart-info__heading--lv2">Đơn hàng của bạn</h2>
                                    <div class="cart-info__list">
                                        <?php foreach ($cart as $item): ?>
                                            <div class="cart-info__row">
                                                <span><?= htmlspecialchars($item['name']) ?> <small>x<?= $item['quantity'] ?></small></span>
                                                <span><?= vnd($item['price'] * $item['quantity']) ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="cart-info__separate"></div>
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
                                    <div class="form__group mt-3">
                                        <label class="form__checkbox">
                                            <input type="checkbox" id="agree-terms" checked>
                                            <span>Tôi đồng ý với <a href="#">Điều khoản dịch vụ</a></span>
                                        </label>
                                    </div>
                                    <button
                                        type="button"
                                        id="alt-pay-btn"
                                        class="cart-info__next-btn btn btn--primary btn--rounded mt-3"
                                        <?= !$hasPhone ? 'disabled style="opacity:0.5;cursor:not-allowed;"' : '' ?>
                                    >
                                        Xác nhận thanh toán
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ========== TAB 2: THANH TOÁN THẺ ========== -->
                    <!-- <div class="prod-tab__content " id="tab-card">
                        <div class="row gy-xl-3">
                            <div class="col-8 col-xl-8 col-lg-12">
                                 Shipping Info
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

                                    <?php if (!$hasPhone): ?>
                                        <p style="color:red; margin-top:10px;">
                                            ⚠ Vui lòng thêm số điện thoại trước khi thanh toán
                                        </p>
                                    <?php endif; ?>
                                </div>

                                 Shipping Method
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
                                                        <input type="radio"
                                                            name="delivery-method"
                                                            value="standard"
                                                            data-fee="<?= $shipping_fee ?>"
                                                            checked />
                                                        <span class="payment-item__cost">
                                                            <?= $shipping_fee == 0 ? 'Miễn phí' : vnd($shipping_fee) ?>
                                                        </span>
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

                             Right Column: Payment Details
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
                                    <button
                                        type="button"
                                        id="pay-btn"
                                        class="cart-info__next-btn btn btn--primary btn--rounded"
                                        <?= !$hasPhone ? 'disabled style="opacity:0.5;cursor:not-allowed;"' : '' ?>
                                    >
                                        Thanh toán <?= vnd($total) ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div> -->


                </div>
            </div>
        </div>
    </div>
</main>

<!-- ==================== MODALS ==================== -->
<div id="payment-method-modal" class="modal hide">
    <div class="modal__content" style="max-width: 500px;">
        <div class="modal__header">
            <h2 class="modal__heading">Chọn phương thức thanh toán online</h2>
            <button class="modal__close" onclick="closeModal('payment-method-modal')">&times;</button>
        </div>
        <div class="modal__body">
            <div class="form__group">
                <label class="form__label form__label--medium">Chọn ứng dụng / ngân hàng</label>
                <select id="payment-gateway-select" class="form__input" style="width:100%; padding:12px; border-radius:8px;">
                    <option value="vnpay" data-qr="<?= $base ?>assets/img/qr/vnpay-qr.png" data-desc="Quét mã VNPAY để thanh toán">💳 VNPAY</option>
                    <option value="momo" data-qr="<?= $base ?>assets/img/qr/momo-qr.png" data-desc="Mở ví Momo quét mã">💜 MoMo</option>
                    <option value="zalopay" data-qr="<?= $base ?>assets/img/qr/zalopay-qr.png" data-desc="Quét ZaloPay để thanh toán">🟦 ZaloPay</option>
                    <option value="mbbank" data-qr="<?= $base ?>assets/img/qr/mbbank-qr.png" data-desc="Chuyển khoản qua MB Bank">🏦 MB Bank</option>
                </select>
            </div>

        </div>
        <div class="modal__footer d-flex mt-3">
            <button class="btn btn--small btn--text" onclick="closeModal('payment-method-modal')">Hủy</button>
            <button class="btn btn--small btn--primary" id="confirm-payment-method">Xác nhận thanh toán</button>
        </div>
    </div>
    <div class="modal__overlay" onclick="closeModal('payment-method-modal')"></div>
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
(function() {
    function init() {
        console.log('✅ Payment page initialized');

        // Tab switching
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('prod-tab__item--current'));
                document.querySelectorAll('.prod-tab__content').forEach(c => c.classList.remove('prod-tab__content--current'));
                btn.classList.add('prod-tab__item--current');
                document.getElementById(btn.dataset.tab).classList.add('prod-tab__content--current');
            });
        });

        const formatVND = (amount) => amount.toLocaleString('vi-VN') + 'đ';

        // ========== UPDATE TOTAL khi đổi phương thức vận chuyển ==========
        function updateTotal(shippingFee) {
            const subtotal = <?= $subtotal ?>;
            const discount = <?= $discount ?>;
            const newTotal = Math.max(0, subtotal - discount + shippingFee);

            // Cập nhật cho tab đang active
            const totalSpan = document.querySelector('.prod-tab__content--current .cart-info__row--highlight span:last-child');
            if (totalSpan) totalSpan.textContent = formatVND(newTotal);

            // Cập nhật nút thanh toán
            const payBtn = document.getElementById('alt-pay-btn') || document.getElementById('pay-btn');
            if (payBtn) {
                payBtn.textContent = payBtn.id === 'pay-btn'
                    ? `Thanh toán ${formatVND(newTotal)}`
                    : `Xác nhận thanh toán ${formatVND(newTotal)}`;
            }
        }

        // Listen tất cả radio shipping (cả 2 tab)
        document.querySelectorAll('input[name="delivery-method"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const fee = parseInt(this.dataset.fee) || 0;
                updateTotal(fee);
            });
        });

        // ========== PROCESS ORDER ==========
        async function processOrder(paymentMethod, shippingMethod, shippingFee) {
            const btn = document.getElementById('alt-pay-btn') || document.getElementById('pay-btn');
            if (btn) {
                btn.disabled = true;
                btn.textContent = 'Đang xử lý...';
            }

            try {
                const payload = {
                    shipping_address_id: <?= json_encode($defaultAddress['id'] ?? null) ?>,
                    shipping_method: shippingMethod,
                    shipping_fee: shippingFee,
                    payment_method: paymentMethod
                };

                const endpoint = (paymentMethod === 'cod' || paymentMethod === 'cash')
                    ? 'index.php?url=create-order'
                    : 'index.php?url=prepare-payment';

                const res = await fetch(endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();

                if (data.success) {
                    if (data.payment_url) {
                        window.location.href = data.payment_url;
                    } else {
                        window.location.href = 'index.php?url=orders';
                    }
                } else {
                    showAlert('Lỗi', data.message || 'Đặt hàng thất bại');
                }
            } catch (err) {
                console.error(err);
                showAlert('Lỗi kết nối', err.message);
            } finally {
                if (btn) btn.disabled = false;
            }
        }

        // ========== ALT PAY BUTTON (Tab 1) ==========
        const altPayBtn = document.getElementById('alt-pay-btn');
        if (altPayBtn) {
            altPayBtn.addEventListener('click', async () => {
                if (!document.getElementById('agree-terms')?.checked) {
                    showAlert('Thông báo', 'Vui lòng đồng ý với Điều khoản dịch vụ');
                    return;
                }

                const selectedShipping = document.querySelector('input[name="delivery-method"]:checked');
                const shippingFee = selectedShipping ? parseInt(selectedShipping.dataset.fee) || 0 : <?= $shipping_fee ?>;
                const shippingMethod = selectedShipping?.value || 'fedex_standard';

                const paymentMethod = document.querySelector('input[name="alt_payment_method"]:checked')?.value;

                if (paymentMethod === 'cod') {
                    await processOrder('cod', shippingMethod, shippingFee);
                } else {
                    openModal('payment-method-modal');
                }
            });
        }

        // ========== Confirm Online Payment ==========
        document.getElementById('confirm-payment-method')?.addEventListener('click', async () => {
            closeModal('payment-method-modal');
            const selectedShipping = document.querySelector('input[name="delivery-method"]:checked');
            const shippingFee = selectedShipping ? parseInt(selectedShipping.dataset.fee) || 0 : <?= $shipping_fee ?>;
            const shippingMethod = selectedShipping?.value || 'fedex_standard';

            await processOrder(window.selectedOnlineMethod || 'vnpay', shippingMethod, shippingFee);
        });

        // ========== Card Payment (Tab 2) ==========
        document.getElementById('pay-btn')?.addEventListener('click', async function(e) {
            // ... validation card giữ nguyên ...

            const selectedShipping = document.querySelector('input[name="delivery-method"]:checked');
            const shippingFee = selectedShipping ? parseInt(selectedShipping.dataset.fee) || 0 : <?= $shipping_fee ?>;
            const shippingMethod = selectedShipping?.value || 'fedex_standard';

            await processOrder('card', shippingMethod, shippingFee);
        });

        // Modal functions (giữ nguyên)
        window.openModal = function(id) { /* ... */ };
        window.closeModal = function(id) { /* ... */ };
        window.showAlert = function(title, message) { /* ... */ };

        console.log('✅ All shipping & payment handlers attached');
    }

    document.readyState === 'loading'
        ? document.addEventListener('DOMContentLoaded', init)
        : init();
})();
</script>
