<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../../app/controllers/CartController.php';
require_once __DIR__ . '/../../../app/controllers/OrderController.php';

$user = $_SESSION['user'] ?? null;
if (!$user) {
    header("Location: index.php?url=login");
    exit;
}

$cartData = getCart(false);
$cartItems = $cartData['items'] ?? [];
$subtotal = $cartData['subtotal'] ?? 0;
$total = $subtotal + 10000;

$addresses = getShippingAddresses($user['id']);

if (!function_exists('vnd')) {
    function vnd($amount) {
        return number_format($amount, 0, ',', '.') . 'đ';
    }
}
?>

<style>
    .text-muted { margin-top: 20px; }
#toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 99999;
}

.toast {
    min-width: 250px;
    margin-bottom: 10px;
    padding: 12px 16px;
    border-radius: 8px;
    color: #fff;
    font-size: 14px;
    box-shadow: 0 10px 20px rgba(0,0,0,0.15);
    animation: slideIn 0.3s ease, fadeOut 0.3s ease 3s forwards;
}

.toast.success { background: #22c55e; }
.toast.error { background: #ef4444; }
.toast.info { background: #3b82f6; }

@keyframes slideIn {
    from { transform: translateX(120%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

@keyframes fadeOut {
    to { opacity: 0; transform: translateX(120%); }
}
</style>

<main class="checkout-page">
    <div class="container">
        <div class="checkout-container">
            <ul class="breadcrumbs checkout-page__breadcrumbs">
                <li><a href="<?= $base ?>" class="breadcrumbs__link">Trang chủ <img src="<?= $base ?>assets/icons/arrow-right.svg" /></a></li>
                <li><a href="index.php?url=checkout" class="breadcrumbs__link">Giỏ hàng <img src="<?= $base ?>assets/icons/arrow-right.svg" /></a></li>
                <li><a href="#!" class="breadcrumbs__link breadcrumbs__link--current">Vận chuyển</a></li>
            </ul>
        </div>

        <div class="checkout-container">
            <div class="row gy-xl-3">
                <div class="col-8 col-xl-12">
                    <div class="cart-info">
                        <h1 class="cart-info__heading">1. Thông tin giao hàng</h1>

                        <div class="user-address">
                            <div class="user-address__top">
                                <h2 class="user-address__title">Địa chỉ giao hàng</h2>
                                <button class="user-address__btn btn btn--primary btn--rounded btn--small js-toggle"
                                        toggle-target="#add-new-address">
                                    <img src="./assets/icons/plus.svg" alt="" />
                                    Thêm địa chỉ mới
                                </button>
                            </div>

                            <div class="user-address__list">
                                <?php if (count($addresses) > 0): ?>
                                    <?php foreach ($addresses as $index => $addr): ?>
                                        <article class="address-card <?= $addr['is_default'] == 1 ? 'address-card--default' : '' ?>">
                                            <div class="address-card__left">
                                                <div class="address-card__choose">
                                                    <label class="cart-info__checkbox">
                                                        <input
                                                            type="radio"
                                                            name="shipping_address_id"
                                                            value="<?= $addr['id'] ?>"
                                                            <?= ($addr['is_default'] == 1 || $index == 0) ? 'checked' : '' ?>
                                                            class="cart-info__checkbox-input"
                                                        />
                                                    </label>

                                                </div>


                                                <div class="address-card__info">
                                                    <h3 class="address-card__title">Tên khách hàng: <?= htmlspecialchars($addr['full_name']) ?></h3>
                                                    <p class="address-card__phone">Số điện thoại: 📞 <?= htmlspecialchars($addr['phone']) ?></p>
                                                    <p class="address-card__desc">Địa chỉ: <?= htmlspecialchars($addr['address']) ?>, Thành phố <?= htmlspecialchars($addr['city']) ?></p>
                                                    <?php if ($addr['is_default'] == 1): ?>
                                                        <span class="address-card__default">Mặc định</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="address-card__right">
                                                <div class="address-card__ctrl">
                                                    <button
                                                        class="cart-info__edit-btn js-toggle edit-address-btn"
                                                        toggle-target="#add-new-address"
                                                        data-id="<?= $addr['id'] ?>"
                                                        data-name="<?= htmlspecialchars($addr['full_name']) ?>"
                                                        data-phone="<?= htmlspecialchars($addr['phone']) ?>"
                                                        data-address="<?= htmlspecialchars($addr['address']) ?>"
                                                        data-city="<?= htmlspecialchars($addr['city']) ?>"
                                                        data-is_default="<?= $addr['is_default'] ?>"
                                                    >
                                                        <img class="icon" src="./assets/icons/edit.svg" alt="" />
                                                        Sửa
                                                    </button>

                                                    <button
                                                    class="cart-info__edit-btn  delete-address-btn btn-delete"
                                                    data-id="<?= $addr['id'] ?>" style="margin-left: 10px;"
                                                >
                                                    <img src="<?= $base ?>assets/icons/trash.svg" />
                                                    Xóa
                                                </button>
                                                </div>


                                            </div>
                                        </article>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="user-address__message">
                                        Chưa có địa chỉ nào.
                                        <a class="user-address__link js-toggle" href="#!" toggle-target="#add-new-address">Thêm địa chỉ mới</a>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Items -->
                        <h2 class="cart-info__sub-heading mt-5">Chi tiết sản phẩm</h2>
                        <div class="cart-info__list">
                            <?php foreach ($cartItems as $item): ?>
                                <article class="cart-item">
                                    <img src="<?= $base ?>assets/img/product/<?= $item['image'] ?>" class="cart-item__thumb">
                                    <div class="cart-item__content">
                                        <div class="cart-item__content-left">
                                            <h3 class="cart-item__title"><?= htmlspecialchars($item['name']) ?></h3>
                                            <p class="cart-item__price-wrap"><?= vnd($item['price']) ?></p>
                                            <div class="cart-item__input">Số lượng: <?= $item['quantity'] ?></div>
                                        </div>
                                        <div class="cart-item__content-right">
                                            <p class="cart-item__total-price"><?= vnd($item['price'] * $item['quantity']) ?></p>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="col-4 col-xl-12">
                    <div class="cart-info">
                        <div class="cart-info__row">
                            <span>Tạm tính (<?= count($cartItems) ?> sản phẩm)</span>
                            <span><?= vnd($subtotal) ?></span>
                        </div>
                        <div class="cart-info__row">
                            <span>Vận chuyển</span>
                            <span><?= vnd(10000) ?></span>
                        </div>
                        <div class="cart-info__separate"></div>
                        <div class="cart-info__row cart-info__row--bold">
                            <span>Tổng cộng</span>
                            <span><?= vnd($total) ?></span>
                        </div>

                        <form action="index.php?url=payment" method="POST">
                            <input type="hidden" name="shipping_address_id" id="selected_address" value="">
                            <button type="submit" class="cart-info__next-btn btn btn--primary btn--rounded" <?= empty($addresses) ? 'disabled style="opacity:0.5;cursor:not-allowed;"' : '' ?>>
                                Tiếp tục đến thanh toán
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Modal thêm/sửa địa chỉ -->
<div id="add-new-address" class="modal hide" style="--content-width: 650px">
    <div class="modal__content">
        <form id="add-address-form" class="form" action="index.php?url=add-shipping-address" method="POST">
            <h2 class="modal__heading">Thêm địa chỉ giao hàng mới</h2>
            <div class="modal__body">
                <input type="hidden" name="address_id" value="">
                <div class="form__row">
                    <div class="form__group">
                        <label for="name" class="form__label form__label--small">Họ và tên <span class="text-danger">*</span></label>
                        <div class="form__text-input form__text-input--small">
                            <input type="text" name="recipient_name" id="name" placeholder="Nhập họ và tên" class="form__input" required />
                            <img src="./assets/icons/form-error.svg" alt="" class="form__input-icon-error" />
                        </div>
                        <p class="form__error">Họ tên phải có ít nhất 2 ký tự</p>
                    </div>
                    <div class="form__group">
                        <label for="phone" class="form__label form__label--small">Số điện thoại <span class="text-danger">*</span></label>
                        <div class="form__text-input form__text-input--small">
                            <input type="tel" name="phone" id="phone" placeholder="0123 456 789" class="form__input" required />
                            <img src="./assets/icons/form-error.svg" alt="" class="form__input-icon-error" />
                        </div>
                        <p class="form__error">Số điện thoại phải có ít nhất 10 ký tự</p>
                    </div>
                </div>
                <div class="form__group">
                    <label for="address" class="form__label form__label--small">Địa chỉ chi tiết <span class="text-danger">*</span></label>
                    <div class="form__text-area">
                        <textarea name="address" id="address" placeholder="Số nhà, tên đường, phường/xã..." class="form__text-area-input" required></textarea>
                        <img src="./assets/icons/form-error.svg" alt="" class="form__input-icon-error" />
                    </div>
                    <p class="form__error">Địa chỉ không được để trống</p>
                </div>
                <div class="form__group">
                    <label class="form__label form__label--small">Tỉnh/Thành phố <span class="text-danger">*</span></label>
                    <div class="form__text-input form__text-input--small">
                        <input type="text" id="city-input" name="city" readonly placeholder="Chọn tỉnh/thành phố" class="form__input js-toggle" toggle-target="#city-dialog" />
                        <img src="./assets/icons/form-error.svg" alt="" class="form__input-icon-error" />
                    </div>
                    <div id="city-dialog" class="form__select-dialog hide d-flex">
                        <h2 class="form__dialog-heading">Chọn Tỉnh/Thành phố</h2>
                        <button class="form__close-dialog js-toggle" toggle-target="#city-dialog">&times;</button>
                        <div class="form__search">
                            <input type="text" id="city-search" placeholder="Tìm kiếm..." class="form__search-input" />
                            <img src="./assets/icons/search.svg" alt="" class="form__search-icon icon" />
                        </div>
                        <ul id="city-list" class="form__options-list"></ul>
                    </div>
                </div>
                <div class="form__group form__group--inline">
                    <label class="form__checkbox">
                        <input type="checkbox" name="is_default" class="form__checkbox-input d-none" />
                        <span class="form__checkbox-label">Đặt làm địa chỉ mặc định</span>
                    </label>
                </div>
            </div>
            <div class="modal__bottom">
                <button type="button" class="btn btn--small btn--text modal__btn js-toggle" toggle-target="#add-new-address">Hủy</button>
                <button type="submit" class="btn btn--small btn--primary modal__btn">Lưu địa chỉ</button>
            </div>
        </form>
    </div>
    <div class="modal__overlay"></div>
</div>
<div id="toast-container"></div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ================= TOAST =================
    function showToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerText = message;
        container.appendChild(toast);
        setTimeout(() => toast.remove(), 3500);
    }

    // ================= TOGGLE MODAL (FIX CHUẨN) =================
    function toggleModal(modalId, show = true) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        if (show) {
            modal.classList.remove('hide');
            modal.classList.add('show');
        } else {
            modal.classList.remove('show');
            modal.classList.add('hide');
        }
    }

    // Mở modal
    document.querySelectorAll('.js-toggle, .user-address__btn, .user-address__link').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const target = this.getAttribute('toggle-target');
            if (target) {
                toggleModal(target.replace('#', ''), true);
            }
        });
    });

    // Đóng modal khi click Overlay
    document.querySelectorAll('.modal__overlay').forEach(overlay => {
        overlay.addEventListener('click', function () {
            const modal = this.closest('.modal');
            if (modal) toggleModal(modal.id, false);
        });
    });

    // Đóng modal bằng nút X
    document.querySelectorAll('.modal__close').forEach(btn => {
        btn.addEventListener('click', function () {
            const modal = this.closest('.modal');
            if (modal) toggleModal(modal.id, false);
        });
    });

    // Đóng modal bằng nút Hủy (Cancel)
    document.querySelectorAll('.btn--text, .modal__btn.btn--text').forEach(btn => {
        btn.addEventListener('click', function () {
            const modal = this.closest('.modal');
            if (modal) toggleModal(modal.id, false);
        });
    });

    // ================= CITY MODAL =================
    const cities = ["Hà Nội","TP. Hồ Chí Minh","Đà Nẵng","Hải Phòng","Cần Thơ","An Giang","Bà Rịa - Vũng Tàu","Bắc Giang","Bắc Kạn","Bạc Liêu","Bắc Ninh","Bến Tre","Bình Định","Bình Dương","Bình Phước","Bình Thuận","Cà Mau","Cao Bằng","Đắk Lắk","Đắk Nông","Điện Biên","Đồng Nai","Đồng Tháp","Gia Lai","Hà Giang","Hà Nam","Hà Tĩnh","Hải Dương","Hậu Giang","Hòa Bình","Hưng Yên","Khánh Hòa","Kiên Giang","Kon Tum","Lai Châu","Lâm Đồng","Lạng Sơn","Lào Cai","Long An","Nam Định","Nghệ An","Ninh Bình","Ninh Thuận","Phú Thọ","Phú Yên","Quảng Bình","Quảng Nam","Quảng Ngãi","Quảng Ninh","Quảng Trị","Sóc Trăng","Sơn La","Tây Ninh","Thái Bình","Thái Nguyên","Thanh Hóa","Thừa Thiên Huế","Tiền Giang","Trà Vinh","Tuyên Quang","Vĩnh Long","Vĩnh Phúc","Yên Bái"];

    const citySearch = document.getElementById('city-search');
    const cityList = document.getElementById('city-list');

    if (cityList) {
        function renderCities(filtered) {
            cityList.innerHTML = '';
            filtered.forEach(city => {
                const li = document.createElement('li');
                li.className = 'form__option';
                li.textContent = city;
                li.onclick = () => {
                    document.getElementById('city-input').value = city;
                    toggleModal('city-dialog', false);
                };
                cityList.appendChild(li);
            });
        }

        renderCities(cities);

        if (citySearch) {
            citySearch.addEventListener('input', function () {
                const val = this.value.toLowerCase().trim();
                const filtered = cities.filter(c => c.toLowerCase().includes(val));
                renderCities(filtered.length ? filtered : cities);
            });
        }
    }

    // ================= EDIT ADDRESS =================
    document.querySelectorAll('.edit-address-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const form = document.getElementById('add-address-form');
            if (!form) return;

            form.querySelector('[name="address_id"]').value = this.dataset.id || '';
            form.querySelector('[name="recipient_name"]').value = this.dataset.name || '';
            form.querySelector('[name="phone"]').value = this.dataset.phone || '';
            form.querySelector('[name="address"]').value = this.dataset.address || '';
            form.querySelector('[name="city"]').value = this.dataset.city || '';
            form.querySelector('[name="is_default"]').checked = this.dataset.is_default === '1';

            document.querySelector('#add-new-address .modal__heading').innerText = 'Cập nhật địa chỉ';
            toggleModal('add-new-address', true);
        });
    });

    // Reset form khi mở thêm mới
    document.querySelectorAll('.user-address__btn, .user-address__link').forEach(btn => {
        btn.addEventListener('click', () => {
            const form = document.getElementById('add-address-form');
            if (form) {
                form.reset();
                form.querySelector('[name="address_id"]').value = '';
                document.querySelector('#add-new-address .modal__heading').innerText = 'Thêm địa chỉ mới';
            }
            toggleModal('add-new-address', true);
        });
    });

    // ================= FORM SUBMIT =================
    const form = document.getElementById('add-address-form');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            fetch(this.action, {
                method: 'POST',
                body: new FormData(this)
            })
            .then(res => res.json())
            .then(data => {
                showToast(data.message || (data.success ? 'Thành công!' : 'Thất bại'),
                          data.success ? 'success' : 'error');

                if (data.success) {
                    setTimeout(() => location.reload(), 800);
                }
            })
            .catch(() => showToast('Lỗi kết nối', 'error'));
        });
    }

    // ================= DELETE ADDRESS =================
    document.querySelectorAll('.delete-address-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            if (!confirm('Bạn có chắc muốn xóa địa chỉ này?')) return;

            fetch('index.php?url=delete-shipping-address', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'id=' + this.dataset.id
            })
            .then(res => res.json())
            .then(data => {
                showToast(data.message || 'Đã xóa', data.success ? 'success' : 'error');
                if (data.success) setTimeout(() => location.reload(), 800);
            })
            .catch(() => showToast('Lỗi kết nối', 'error'));
        });
    });

    // Radio address
    const hiddenInput = document.getElementById('selected_address');
    document.querySelectorAll('input[name="shipping_address_id"]').forEach(radio => {
        radio.addEventListener('change', () => {
            if (hiddenInput) hiddenInput.value = radio.value;
        });
    });

});
</script>
