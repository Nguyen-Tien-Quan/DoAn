<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$base = '/DoAn/DoAnTotNghiep/public/';

$cart = $_SESSION['cart'] ?? [];

$subtotal = 0;
$itemCount = 0;
foreach ($cart as $item) {
    $itemCount += $item['quantity'];
    $subtotal += $item['price'] * $item['quantity'];
}

$shipping = 10000;
$total = $subtotal + $shipping;

// Mã giảm giá từ session (đã được lưu khi áp dụng)
$discount = $_SESSION['discount'] ?? 0;
$couponCode = $_SESSION['coupon_code'] ?? '';
$totalAfterDiscount = $total - $discount;
if ($totalAfterDiscount < 0) $totalAfterDiscount = 0;
?>

<main class="checkout-page">
    <div class="container">
        <div class="checkout-container">
            <div class="search-bar d-none d-md-flex">
                <input type="text" placeholder="Search for item" class="search-bar__input" />
                <button class="search-bar__submit">
                    <img src="<?= $base ?>assets/icons/search.svg" class="search-bar__icon icon" />
                </button>
            </div>
        </div>

        <div class="checkout-container">
            <ul class="breadcrumbs checkout-page__breadcrumbs">
                <li>
                    <a href="<?= $base ?>" class="breadcrumbs__link">
                        Home
                        <img src="<?= $base ?>assets/icons/arrow-right.svg" />
                    </a>
                </li>
                <li>
                    <a href="#!" class="breadcrumbs__link breadcrumbs__link--current">Checkout</a>
                </li>
            </ul>
        </div>

        <div class="checkout-container">
            <div class="row gy-xl-3">

                <!-- LEFT: Danh sách sản phẩm -->
                <div class="col-8 col-xl-12">
                    <div class="cart-info">
                        <div class="cart-info__list">
                            <?php if (!empty($cart)): ?>
                                <?php foreach ($cart as $key => $item): ?>
                                    <article class="cart-item" id="item-<?= $key ?>">
                                        <a href="<?= $base ?>index.php?url=product&id=<?= $item['id'] ?>">
                                            <img src="<?= $base ?>assets/img/product/<?= $item['image'] ?>"
                                                class="cart-item__thumb" />
                                        </a>
                                        <div class="cart-item__content">
                                            <div class="cart-item__content-left">
                                                <h3 class="cart-item__title">
                                                    <a href="<?= $base ?>index.php?url=product&id=<?= $item['id'] ?>">
                                                        <?= $item['name'] ?>
                                                    </a>
                                                </h3>
                                                <?php if (!empty($item['variant']) && !empty($item['variant']['name'])): ?>
                                                    <p>Size: <?= $item['variant']['name'] ?></p>
                                                <?php endif; ?>
                                                <?php if (!empty($item['toppings'])): ?>
                                                    <p>Topping: <?= implode(', ', array_column($item['toppings'], 'name')) ?></p>
                                                <?php endif; ?>
                                                <p class="cart-item__price-wrap">
                                                    <?= vnd($item['price']) ?> |
                                                    <span class="cart-item__status">In Stock</span>
                                                </p>
                                                <div class="cart-item__ctrl cart-item__ctrl--md-block">
                                                    <div class="cart-item__input">
                                                        <button type="button" class="cart-item__input-btn minus" data-id="<?= $key ?>">
                                                            <img class="icon" src="<?= $base ?>assets/icons/minus.svg" />
                                                        </button>
                                                        <span id="qty-<?= $key ?>"><?= $item['quantity'] ?></span>
                                                        <button type="button" class="cart-item__input-btn plus" data-id="<?= $key ?>">
                                                            <img class="icon" src="<?= $base ?>assets/icons/plus.svg" />
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="cart-item__content-right">
                                                <p class="cart-item__total-price" id="total-<?= $key ?>">
                                                    <?= vnd($item['price'] * $item['quantity']) ?>
                                                </p>
                                                <div class="cart-item__ctrl">
                                                    <button class="cart-item__ctrl-btn btn-save" data-id="<?= $key ?>">
                                                        <img src="<?= $base ?>assets/icons/heart-2.svg" /> Save
                                                    </button>
                                                    <button type="button" class="cart-item__ctrl-btn btn-delete js-toggle" data-id="<?= $key ?>" toggle-target="#delete-confirm">
                                                        <img src="<?= $base ?>assets/icons/trash.svg" /> Delete
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-center py-5 fs-4 cart-info__list--empty">
                                    <img src="<?= $base ?>assets/img/empty-cart.png" alt="Empty cart" class="mb-4" />
                                    <a href="<?= $base ?>" class="btn btn--primary mt-3">Tiếp tục mua sắm</a>
                                </p>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($cart)): ?>
                            <div class="cart-info__bottom">
                                <div class="cart-info__row cart-info__row-md--block">
                                    <div class="cart-info__continue">
                                        <a href="<?= $base ?>" class="cart-info__continue-link">
                                            <img class="cart-info__continue-icon icon" src="<?= $base ?>assets/icons/arrow-down-2.svg" alt="" />
                                            Continue Shopping
                                        </a>
                                    </div>
                                    <div class="d-flex">
                                        <button class="cart-info__checkout-all btn btn--danger btn--rounded js-toggle btn-delete-all" toggle-target="#delete-all-confirm">
                                            Delete All
                                        </button>
                                        <a href="<?= $base ?>index.php?url=checkout" class="cart-info__checkout-all btn btn--primary btn--rounded">
                                            All Check Out
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- RIGHT: Tổng tiền và mã giảm giá -->
                <div class="col-4 col-xl-12">
                    <div class="cart-info">
                        <div class="cart-info__row">
                            <span>Subtotal (items)</span>
                            <span id="cart-count"><?= $itemCount ?></span>
                        </div>
                        <div class="cart-info__row">
                            <span>Price (Total)</span>
                            <span id="cart-subtotal"><?= vnd($subtotal) ?></span>
                        </div>
                        <div class="cart-info__row">
                            <span>Shipping</span>
                            <span id="cart-shipping"><?= vnd($shipping) ?></span>
                        </div>

                        <!-- Dòng giảm giá (ẩn nếu chưa có) -->
                        <div id="discount-row" class="cart-info__row" style="<?= $discount > 0 ? '' : 'display: none;' ?>">
                            <span>Discount (<?= htmlspecialchars($couponCode) ?>)</span>
                            <span id="cart-discount">-<?= vnd($discount) ?></span>
                        </div>

                        <!-- Khu vực mã giảm giá -->
                        <div class="cart-info__coupon">
                            <div id="coupon-list-container" class="coupon-list"></div>
                            <div class="coupon-input-group">
                                <input type="text" id="coupon-code" class="coupon-input" placeholder="Nhập mã giảm giá" />
                                <button id="apply-coupon" class="coupon-btn">Áp dụng</button>
                            </div>
                            <div id="coupon-message" class="coupon-message"></div>
                        </div>

                        <div class="cart-info__separate"></div>
                        <div class="cart-info__row">
                            <span>Estimated Total</span>
                            <span id="cart-total"><?= vnd($totalAfterDiscount) ?></span>
                        </div>
                        <a href="<?= $base ?>index.php?url=shipping" class="cart-info__next-btn btn btn--primary btn--rounded">
                            Continue to checkout
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal xóa 1 sản phẩm -->
    <div id="delete-confirm" class="modal modal--small hide">
        <div class="modal__content">
            <p class="modal__text">Bạn có muốn xóa sản phẩm này khỏi giỏ hàng không?</p>
            <div class="modal__bottom">
                <button class="btn btn--small btn--outline modal__btn js-toggle" toggle-target="#delete-confirm">Cancel</button>
                <button id="confirm-delete-btn" class="btn btn--small btn--danger btn--primary modal__btn btn--no-margin">Delete</button>
            </div>
        </div>
        <div class="modal__overlay js-toggle" toggle-target="#delete-confirm"></div>
    </div>

    <!-- Modal xóa tất cả -->
    <div id="delete-all-confirm" class="modal modal--small hide">
        <div class="modal__content">
            <p class="modal__text">Xóa toàn bộ sản phẩm trong giỏ hàng?</p>
            <div class="modal__bottom">
                <button class="btn btn--small btn--outline modal__btn js-toggle" toggle-target="#delete-all-confirm">Cancel</button>
                <button id="confirm-delete-all" class="btn btn--small btn--danger btn--primary modal__btn btn--no-margin">Delete All</button>
            </div>
        </div>
        <div class="modal__overlay js-toggle" toggle-target="#delete-all-confirm"></div>
    </div>
</main>

<style>
/* ===== COUPON SECTION STYLES ===== */
.cart-info__coupon {
    margin: 10px 0;
}

.coupon-list {
    font-size: 13px;
    margin-bottom: 8px;
}

.coupon-list .coupon-badge {
    background: #f0f0f0;
    padding: 4px 8px;
    border-radius: 20px;
    cursor: pointer;
    font-size: 12px;
    display: inline-block;
    transition: background 0.2s;
}

.coupon-list .coupon-badge:hover {
    background: #e0e0e0;
}

.coupon-input-group {
    display: flex;
    gap: 8px;
    margin: 10px 0;
}

.coupon-input {
    flex: 1;
    padding: 8px;
    border-radius: 20px;
    border: 1px solid #ccc;
    outline: none;
    transition: border-color 0.2s;
}

.coupon-input:focus {
    border-color: #ff6b6b;
}

.coupon-btn {
    padding: 8px 12px;
    background: #f0f0f0;
    border: 1px solid #ddd;
    border-radius: 20px;
    cursor: pointer;
    transition: all 0.2s;
}

.coupon-btn:hover {
    background: #ff6b6b;
    color: white;
    border-color: #ff6b6b;
}

.coupon-message {
    font-size: 12px;
    margin-top: 4px;
    color: red;
}

/* ===== RESPONSIVE (nếu cần) ===== */
@media (max-width: 768px) {
    .coupon-input-group {
        flex-direction: column;
    }
    .coupon-btn {
        width: 100%;
    }
}
</style>

<script>
// ========== TOGGLE MODAL ==========
document.querySelectorAll('.js-toggle').forEach(btn => {
    btn.addEventListener('click', function(e){
        e.preventDefault();
        const target = this.getAttribute("toggle-target");
        const modal = document.querySelector(target);
        if(modal){
            modal.classList.toggle('show');
            modal.classList.toggle('hide');
        }
    });
});

// ========== DELETE ALL ==========
const confirmDeleteAllBtn = document.getElementById("confirm-delete-all");
if (confirmDeleteAllBtn) {
    confirmDeleteAllBtn.addEventListener("click", function () {
        fetch("index.php?url=remove-all-cart")
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const list = document.querySelector(".cart-info__list");
                    if (list) {
                        list.innerHTML = `<p class="text-center py-5 fs-4 cart-info__list--empty">
                            <img src="/DoAn/DoAnTotNghiep/assets/img/empty-cart.png" class="mb-4" />
                            <a href="/index.php" class="btn btn--primary mt-3">Tiếp tục mua sắm</a>
                        </p>`;
                    }
                    document.getElementById("cart-count").innerText = 0;
                    document.getElementById("cart-subtotal").innerText = "0đ";
                    document.getElementById("cart-total").innerText = "0đ";
                    const discountRow = document.getElementById("discount-row");
                    if(discountRow) discountRow.style.display = "none";
                    document.getElementById("coupon-code").value = "";
                    document.getElementById("coupon-message").innerText = "";
                    const modal = document.getElementById("delete-all-confirm");
                    if(modal){
                        modal.classList.remove("show");
                        modal.classList.add("hide");
                    }
                    if (typeof showToast === "function") showToast("Đã xóa tất cả 🗑️", "success");
                }
            });
    });
}

// ========== LOAD DANH SÁCH MÃ GIẢM GIÁ TỪ DB ==========
function loadActiveCoupons() {
    fetch('index.php?url=getActiveCoupons')
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById('coupon-list-container');
            if (data.success && data.coupons.length > 0) {
                let html = '<div class="text-muted small mb-1">✨ Mã khuyến mãi hiện có: </div><div style="display: flex; flex-wrap: wrap; gap: 6px;">';
                data.coupons.forEach(coupon => {
                    let discountText = coupon.type === 'percent' ? `giảm ${coupon.value}%` : `giảm ${Number(coupon.value).toLocaleString()}đ`;
                    if (coupon.max_discount && coupon.max_discount > 0) discountText += ` (tối đa ${Number(coupon.max_discount).toLocaleString()}đ)`;
                    html += `<span class="coupon-badge" data-code="${coupon.code}" style="background: #f0f0f0; padding: 4px 8px; border-radius: 20px; cursor: pointer; font-size: 12px;">
                                <strong>${coupon.code}</strong> (${discountText})
                            </span>`;
                });
                html += '</div>';
                container.innerHTML = html;
                document.querySelectorAll('.coupon-badge').forEach(badge => {
                    badge.addEventListener('click', function() {
                        document.getElementById('coupon-code').value = this.getAttribute('data-code');
                        document.getElementById('apply-coupon').click();
                    });
                });
            } else {
                container.innerHTML = '<div class="text-muted small">Hiện chưa có mã khuyến mãi</div>';
            }
        })
        .catch(err => {
            console.error(err);
            document.getElementById('coupon-list-container').innerHTML = '<div class="text-muted small">Không thể tải mã giảm giá</div>';
        });
}

// ========== ÁP DỤNG MÃ GIẢM GIÁ ==========
const applyBtn = document.getElementById('apply-coupon');
const couponInput = document.getElementById('coupon-code');
const couponMsg = document.getElementById('coupon-message');

if (applyBtn) {
    applyBtn.addEventListener('click', function() {
        const code = couponInput.value.trim();
        if (!code) {
            couponMsg.innerText = 'Vui lòng nhập mã giảm giá';
            return;
        }
        const subtotalText = document.getElementById('cart-subtotal').innerText;
        const subtotal = parseInt(subtotalText.replace(/[^\d]/g, '')) || 0;
        const shipping = 10000;

        fetch('index.php?url=applyCoupon', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'code=' + encodeURIComponent(code) + '&subtotal=' + subtotal
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                let discountRow = document.getElementById('discount-row');
                if (!discountRow) {
                    const separator = document.querySelector('.cart-info__separate');
                    const newRow = document.createElement('div');
                    newRow.className = 'cart-info__row';
                    newRow.id = 'discount-row';
                    newRow.innerHTML = `<span>Discount (${code})</span><span id="cart-discount">-${data.formatted_discount}</span>`;
                    separator.parentNode.insertBefore(newRow, separator);
                } else {
                    discountRow.style.display = 'flex';
                    document.getElementById('cart-discount').innerText = '-' + data.formatted_discount;
                    discountRow.querySelector('span:first-child').innerText = `Discount (${code})`;
                }
                document.getElementById('cart-total').innerText = data.formatted_new_total;
                couponMsg.innerText = 'Áp dụng thành công!';
                couponMsg.style.color = 'green';
                setTimeout(() => couponMsg.innerText = '', 3000);
            } else {
                couponMsg.innerText = data.message || 'Mã không hợp lệ';
                couponMsg.style.color = 'red';
            }
        })
        .catch(() => {
            couponMsg.innerText = 'Lỗi kết nối, vui lòng thử lại';
            couponMsg.style.color = 'red';
        });
    });
}

// ========== KHI TRANG LOAD ==========
document.addEventListener('DOMContentLoaded', function() {
    loadActiveCoupons();
    window.resetCoupon = function() {
        fetch('index.php?url=clearCoupon', { method: 'POST' }).then(() => {
            const discountRow = document.getElementById('discount-row');
            if(discountRow) discountRow.style.display = 'none';
            document.getElementById('coupon-code').value = '';
            const subtotalEl = document.getElementById('cart-subtotal');
            const subtotal = parseInt(subtotalEl.innerText.replace(/[^\d]/g, '')) || 0;
            const total = subtotal + 10000;
            document.getElementById('cart-total').innerText = total.toLocaleString('vi-VN') + 'đ';
        });
    };
});
</script>
