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
    // Nếu không có địa chỉ mặc định, lấy địa chỉ mới nhất
    $stmt = $conn->prepare("SELECT * FROM shipping_addresses WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $defaultAddress = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$defaultAddress) {
    // Chưa có địa chỉ nào, chuyển về trang thêm địa chỉ
    header("Location: index.php?url=shipping");
    exit;
}

$shipping_fee = 10000; // mặc định Fedex Free? Sẽ tính sau
$total = $subtotal - $discount + $shipping_fee;
if ($total < 0) $total = 0;

// Hàm định dạng tiền nếu chưa có
if (!function_exists('vnd')) {
    function vnd($number) {
        return number_format($number, 0, ',', '.') . 'đ';
    }
}
?>
<main class="checkout-page">
    <div class="container">
        <!-- Search bar -->
        <div class="checkout-container">
            <div class="search-bar d-none d-md-flex">
                <input type="text" placeholder="Search for item" class="search-bar__input" />
                <button class="search-bar__submit">
                    <img src="<?= $base ?>assets/icons/search.svg" alt="" class="search-bar__icon icon" />
                </button>
            </div>
        </div>

        <!-- Breadcrumbs -->
        <div class="checkout-container">
            <ul class="breadcrumbs checkout-page__breadcrumbs">
                <li>
                    <a href="./" class="breadcrumbs__link">
                        Home <img src="<?= $base ?>assets/icons/arrow-right.svg" alt="" />
                    </a>
                </li>
                <li>
                    <a href="index.php?url=checkout" class="breadcrumbs__link">
                        Checkout <img src="<?= $base ?>assets/icons/arrow-right.svg" alt="" />
                    </a>
                </li>
                <li>
                    <a href="index.php?url=shipping" class="breadcrumbs__link">
                        Shipping <img src="<?= $base ?>assets/icons/arrow-right.svg" alt="" />
                    </a>
                </li>
                <li>
                    <a href="#!" class="breadcrumbs__link breadcrumbs__link--current">Payment method</a>
                </li>
            </ul>
        </div>

        <div class="checkout-container">
            <div class="row gy-xl-3">
                <!-- Left Column -->
                <div class="col-8 col-xl-8 col-lg-12">
                    <!-- Shipping Info -->
                    <div class="cart-info cart-info--shadow">
                        <div class="cart-info__top">
                            <h2 class="cart-info__heading cart-info__heading--lv2">
                                1. Shipping, arrives between <?= date('l, M d', strtotime('+3 days')) ?>—<?= date('l, M d', strtotime('+8 days')) ?>
                            </h2>
                            <a class="cart-info__edit-btn" href="index.php?url=shipping">
                                <img class="icon" src="<?= $base ?>assets/icons/edit.svg" alt="" /> Edit
                            </a>
                        </div>

                        <!-- Shipping Address -->
                        <article class="payment-item payment-item--card">
                            <div class="payment-item__info">
                                <h3 class="payment-item__title"><?= htmlspecialchars($defaultAddress['full_name'] ?? '') ?></h3>
                                <p class="payment-item__desc"><?= htmlspecialchars($defaultAddress['address'] ?? '') ?>, <?= htmlspecialchars($defaultAddress['city'] ?? '') ?></p>
                                <?php if ($defaultAddress['is_default'] ?? 0): ?>
                                    <span class="payment-item__badge">Default</span>
                                <?php endif; ?>
                            </div>
                        </article>

                        <!-- Items Details -->
                        <article class="payment-item payment-item--card">
                            <div class="payment-item__info">
                                <h3 class="payment-item__title">Items details</h3>
                                <p class="payment-item__desc"><?= $itemCount ?> item(s)</p>
                            </div>
                            <a href="index.php?url=checkout" class="payment-item__detail">View details</a>
                        </article>
                    </div>

                    <!-- Shipping Method -->
                    <div class="cart-info cart-info--shadow">
                        <h2 class="cart-info__heading cart-info__heading--lv2">2. Shipping method</h2>
                        <div class="cart-info__separate"></div>
                        <h3 class="cart-info__sub-heading">Available Shipping method</h3>

                        <div id="shipping-methods">
                            <label>
                                <article class="payment-item payment-item--pointer payment-item--highlight">
                                    <img src="<?= $base ?>assets/img/payment/delivery-1.png" alt="" class="payment-item__thumb" />
                                    <div class="payment-item__content">
                                        <div class="payment-item__info">
                                            <h3 class="payment-item__title">Fedex Delivery</h3>
                                            <p class="payment-item__desc payment-item__desc--low">Delivery: 2-3 days work</p>
                                            <small class="payment-item__note">Free shipping for orders over <?= vnd(100000) ?></small>
                                        </div>
                                        <span class="cart-info__checkbox payment-item__checkbox">
                                            <input type="radio" name="delivery-method" value="fedex" data-fee="0" checked class="cart-info__checkbox-input payment-item__checkbox-input" />
                                            <span class="payment-item__cost">Free</span>
                                        </span>
                                    </div>
                                </article>
                            </label>

                            <label>
                                <article class="payment-item payment-item--pointer">
                                    <img src="<?= $base ?>assets/img/payment/delivery-2.png" alt="" class="payment-item__thumb" />
                                    <div class="payment-item__content">
                                        <div class="payment-item__info">
                                            <h3 class="payment-item__title">DHL Delivery</h3>
                                            <p class="payment-item__desc payment-item__desc--low">Delivery: 1-2 days work</p>
                                            <small class="payment-item__note">Express shipping</small>
                                        </div>
                                        <span class="cart-info__checkbox payment-item__checkbox">
                                            <input type="radio" name="delivery-method" value="dhl" data-fee="12000" class="cart-info__checkbox-input payment-item__checkbox-input" />
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
                        <h2 class="cart-info__heading cart-info__heading--lv2">Payment Details</h2>
                        <p class="cart-info__desc">Complete your purchase by providing your payment details.</p>

                        <form id="payment-form" class="form cart-info__form">
                            <div class="form__group">
                                <label for="email" class="form__label form__label--medium">Email Address</label>
                                <div class="form__text-input">
                                    <input type="email" name="email" id="email" value="<?= htmlspecialchars($_SESSION['user']['email'] ?? '') ?>" class="form__input" required />
                                </div>
                            </div>

                            <div class="form__group">
                                <label for="card-holder" class="form__label form__label--medium">Card Holder</label>
                                <div class="form__text-input">
                                    <input type="text" name="card-holder" id="card-holder" placeholder="Card Holder" class="form__input" required />
                                </div>
                            </div>

                            <div class="form__group">
                                <label for="card-details" class="form__label form__label--medium">Card Details</label>
                                <div class="form__text-input">
                                    <input type="text" name="card-details" id="card-details" placeholder="Card Number" class="form__input" required />
                                </div>
                            </div>

                            <div class="form__row cart-info__form-row">
                                <div class="form__group form__group--flex">
                                    <input type="text" name="card-expire" id="card-expire" placeholder="MM/YY" class="form__input" required />
                                    <input type="text" name="card-cvc" id="card-cvc" placeholder="CVC" class="form__input" required />
                                </div>
                            </div>
                        </form>

                        <div class="cart-info__summary">
                            <div class="cart-info__row">
                                <span>Subtotal <span class="cart-info__sub-label">(<?= $itemCount ?> items)</span></span>
                                <span id="subtotal"><?= vnd($subtotal) ?></span>
                            </div>
                            <?php if ($discount > 0): ?>
                            <div class="cart-info__row">
                                <span>Discount (<?= htmlspecialchars($couponCode) ?>)</span>
                                <span id="discount">-<?= vnd($discount) ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="cart-info__row">
                                <span>Shipping</span>
                                <span id="shipping-cost"><?= vnd($shipping_fee) ?></span>
                            </div>
                            <div class="cart-info__separate"></div>
                            <div class="cart-info__row cart-info__row--highlight">
                                <span>Estimated Total</span>
                                <span id="estimated-total"><?= vnd($total) ?></span>
                            </div>
                        </div>

                        <button type="button" id="pay-btn" class="cart-info__next-btn btn btn--primary btn--rounded">
                            Pay <?= vnd($total) ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
// Cập nhật phí ship và tổng tiền khi chọn phương thức
document.querySelectorAll('input[name="delivery-method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const fee = parseInt(this.getAttribute('data-fee')) || 0;
        const subtotalRaw = <?= $subtotal ?>;
        const discountRaw = <?= $discount ?>;
        const newTotal = subtotalRaw - discountRaw + fee;
        document.getElementById('shipping-cost').innerText = formatVND(fee);
        document.getElementById('estimated-total').innerText = formatVND(newTotal);
        // Cập nhật nút pay
        document.getElementById('pay-btn').innerText = 'Pay ' + formatVND(newTotal);
    });
});

function formatVND(amount) {
    return amount.toLocaleString('vi-VN') + 'đ';
}

document.getElementById('pay-btn').addEventListener('click', async function(e) {
    e.preventDefault();

    // Lấy thông tin từ form
    const email = document.getElementById('email').value;
    const cardHolder = document.getElementById('card-holder').value;
    const cardDetails = document.getElementById('card-details').value;
    const expire = document.getElementById('card-expire').value;
    const cvc = document.getElementById('card-cvc').value;

    if (!email || !cardHolder || !cardDetails || !expire || !cvc) {
        alert('Vui lòng điền đầy đủ thông tin thẻ');
        return;
    }

    // Lấy phương thức vận chuyển
    const selectedMethod = document.querySelector('input[name="delivery-method"]:checked');
    const shippingMethod = selectedMethod ? selectedMethod.value : 'fedex';
    const shippingFee = parseInt(selectedMethod.getAttribute('data-fee')) || 0;

    // Gửi request tạo order và thanh toán
    const payBtn = this;
    payBtn.disabled = true;
    payBtn.innerText = 'Processing...';

    try {
        // Bước 1: Tạo order
        const orderRes = await fetch('index.php?url=create-order', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                shipping_address_id: <?= $defaultAddress['id'] ?? 0 ?>,
                shipping_method: shippingMethod,
                shipping_fee: shippingFee,
                payment_method: 'card'
            })
        });
        const orderData = await orderRes.json();
        if (!orderData.success) {
            alert(orderData.message || 'Không thể tạo đơn hàng');
            payBtn.disabled = false;
            payBtn.innerText = 'Pay <?= vnd($total) ?>';
            return;
        }
        const orderId = orderData.order_id;

        // Bước 2: Tạo payment (giả lập, vì thực tế cần cổng thanh toán)
        const paymentRes = await fetch('index.php?url=payment&action=create', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                order_id: orderId,
                method: 'card',
                amount: <?= $total ?>,
                transaction_code: cardDetails.slice(-4) // lấy 4 số cuối
            })
        });
        const paymentData = await paymentRes.json();
        if (paymentData.payment_id) {
            // Cập nhật trạng thái payment thành paid
            await fetch('index.php?url=payment&action=complete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    payment_id: paymentData.payment_id,
                    transaction_code: cardDetails.slice(-4)
                })
            });
            // Xóa giỏ hàng và discount session
            await fetch('index.php?url=clearCoupon', { method: 'POST' });
            await fetch('index.php?url=remove-all-cart');
            alert('Thanh toán thành công! Cảm ơn bạn đã mua hàng.');
            window.location.href = 'index.php?url=orders';
        } else {
            alert('Thanh toán thất bại, vui lòng thử lại');
            payBtn.disabled = false;
            payBtn.innerText = 'Pay <?= vnd($total) ?>';
        }
    } catch (err) {
        console.error(err);
        alert('Có lỗi xảy ra, vui lòng thử lại sau');
        payBtn.disabled = false;
        payBtn.innerText = 'Pay <?= vnd($total) ?>';
    }
});
</script>

<!-- Các modal (giữ nguyên của bạn, có thể bỏ qua nếu không cần) -->
<div id="delete-confirm" class="modal modal--small hide">...</div>
<div id="add-new-address" class="modal hide" style="--content-width: 650px">...</div>
