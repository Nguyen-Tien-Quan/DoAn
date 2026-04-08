<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user'])) {
    header("Location: index.php?url=login");
    exit;
}

$user = $_SESSION['user'];

// Lấy thông tin chi tiết từ bảng customers
$conn = getDB();
$stmt = $conn->prepare("SELECT * FROM customers WHERE user_id = ?");
$stmt->execute([$user['id']]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

// Lấy danh sách địa chỉ
$stmt = $conn->prepare("SELECT * FROM shipping_addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
$stmt->execute([$user['id']]);
$addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy danh sách yêu thích
$stmt = $conn->prepare("
    SELECT p.*, f.created_at as favorited_at
    FROM favorites f
    JOIN products p ON p.id = f.product_id
    WHERE f.user_id = ?
    ORDER BY f.created_at DESC
");
$stmt->execute([$user['id']]);
$favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy 5 đơn hàng gần nhất
$stmt = $conn->prepare("
    SELECT id, order_code, created_at, final_amount, status
    FROM orders
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 5
");
$stmt->execute([$user['id']]);
$recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Xử lý thông báo thành công/lỗi
$success = $_SESSION['profile_success'] ?? null;
$error = $_SESSION['profile_error'] ?? null;
unset($_SESSION['profile_success'], $_SESSION['profile_error']);
?>

<main class="profile">
    <div class="container">
        <!-- Search bar -->
        <div class="profile-container">
            <div class="search-bar d-none d-md-flex">
                <input type="text" placeholder="Search for item" class="search-bar__input" />
                <button class="search-bar__submit">
                    <img src="<?= $base ?>assets/icons/search.svg" class="search-bar__icon icon" />
                </button>
            </div>
        </div>

        <div class="profile-container">
            <div class="row gy-md-3">

                <!-- SIDEBAR -->
                <div class="col-3 col-xl-4 col-lg-5 col-md-12">
                    <aside class="profile__sidebar">
                        <div class="profile-user">
                            <img src="<?= $base ?>assets/img/avatars/<?= htmlspecialchars($_SESSION['user']['avatar'] ?? 'default.png') ?>" class="profile-user__avatar" id="avatar-preview" />
                            <h1 class="profile-user__name"><?= htmlspecialchars($user['name']) ?></h1>
                            <p class="profile-user__desc">Registered: <?= date('d M Y', strtotime($user['created_at'] ?? 'now')) ?></p>
                        </div>

                        <div class="profile-menu">
                            <h3 class="profile-menu__title">Manage Account</h3>
                            <ul class="profile-menu__list">
                                <li><a href="#personal-info" class="profile-menu__link active">Personal info</a></li>
                                <li><a href="#addresses" class="profile-menu__link">Addresses</a></li>
                                <li><a href="#change-password" class="profile-menu__link">Change password</a></li>
                            </ul>
                        </div>

                        <div class="profile-menu">
                            <h3 class="profile-menu__title">My items</h3>
                            <ul class="profile-menu__list">
                                <li><a href="index.php?url=orders" class="profile-menu__link">Orders</a></li>
                                <li><a href="#favorites" class="profile-menu__link">Favorites</a></li>
                            </ul>
                        </div>

                        <div class="profile-menu">
                            <h3 class="profile-menu__title">Customer Service</h3>
                            <ul class="profile-menu__list">
                                <li><a href="#" class="profile-menu__link">Help & Support</a></li>
                                <li><a href="index.php?url=logout" class="profile-menu__link text-danger">Logout</a></li>
                            </ul>
                        </div>
                    </aside>
                </div>

                <!-- CONTENT -->
                <div class="col-9 col-xl-8 col-lg-7 col-md-12">
                    <div class="cart-info">
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                        <?php endif; ?>
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>

                        <!-- ========== PERSONAL INFO SECTION ========== -->
                        <div id="personal-info" class="profile-section">
                            <h2 class="cart-info__heading">Personal Information</h2>
                            <form method="POST" action="index.php?url=update-profile" class="mt-3">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Phone</label>
                                        <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($customer['phone'] ?? $user['phone'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Gender</label>
                                        <select name="gender" class="form-select">
                                            <option value="">Select</option>
                                            <option value="male" <?= ($customer['gender'] ?? '') == 'male' ? 'selected' : '' ?>>Male</option>
                                            <option value="female" <?= ($customer['gender'] ?? '') == 'female' ? 'selected' : '' ?>>Female</option>
                                            <option value="other" <?= ($customer['gender'] ?? '') == 'other' ? 'selected' : '' ?>>Other</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Birthday</label>
                                        <input type="date" name="birthday" class="form-control" value="<?= htmlspecialchars($customer['birthday'] ?? '') ?>">
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn--primary btn--rounded">Update Information</button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="cart-info__separate"></div>

                        <!-- ========== ADDRESSES SECTION ========== -->
                        <div id="addresses" class="profile-section">
                            <div class="d-flex justify-content-between align-items-center">
                                <h2 class="cart-info__heading">Shipping Addresses</h2>
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addAddressModal">+ Add New</button>
                            </div>
                            <div class="row g-3 mt-2">
                                <?php if (empty($addresses)): ?>
                                    <div class="col-12 text-muted">No address yet. Please add one.</div>
                                <?php else: ?>
                                    <?php foreach ($addresses as $addr): ?>
                                        <div class="col-md-6">
                                            <div class="card address-card h-100 <?= $addr['is_default'] ? 'border-primary' : '' ?>">
                                                <div class="card-body">
                                                    <h6 class="card-title"><?= htmlspecialchars($addr['full_name']) ?></h6>
                                                    <p class="card-text small"><?= htmlspecialchars($addr['phone']) ?><br>
                                                    <?= htmlspecialchars($addr['address']) ?>, <?= htmlspecialchars($addr['city']) ?></p>
                                                    <?php if ($addr['is_default']): ?>
                                                        <span class="badge bg-primary">Default</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="card-footer bg-transparent d-flex justify-content-end gap-2">
                                                    <button class="btn btn-sm btn-outline-secondary edit-address" data-id="<?= $addr['id'] ?>" data-name="<?= htmlspecialchars($addr['full_name']) ?>" data-phone="<?= htmlspecialchars($addr['phone']) ?>" data-address="<?= htmlspecialchars($addr['address']) ?>" data-city="<?= htmlspecialchars($addr['city']) ?>" data-default="<?= $addr['is_default'] ?>">Edit</button>
                                                    <button class="btn btn-sm btn-outline-danger delete-address" data-id="<?= $addr['id'] ?>">Delete</button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="cart-info__separate"></div>

                        <!-- ========== CHANGE PASSWORD SECTION ========== -->
                        <div id="change-password" class="profile-section">
                            <h2 class="cart-info__heading">Change Password</h2>
                            <form method="POST" action="index.php?url=change-password" class="mt-3">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Current Password</label>
                                        <input type="password" name="current_password" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">New Password</label>
                                        <input type="password" name="new_password" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Confirm New Password</label>
                                        <input type="password" name="confirm_password" class="form-control" required>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn--primary btn--rounded">Change Password</button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="cart-info__separate"></div>

                        <!-- ========== FAVORITES SECTION ========== -->
                        <div id="favorites" class="profile-section">
                            <h2 class="cart-info__heading">Favorite Products</h2>
                            <?php if (empty($favorites)): ?>
                                <p class="text-muted">You have no favorite products yet. <a href="<?= $base ?>">Explore now</a></p>
                            <?php else: ?>
                                <div class="row g-3">
                                    <?php foreach ($favorites as $item): ?>
                                        <div class="col-md-6">
                                            <div class="favourite-item d-flex gap-3 p-2 border rounded">
                                                <img src="<?= $base ?>assets/img/product/<?= $item['image'] ?>" class="favourite-item__thumb" style="width: 80px; height: 80px; object-fit: cover;">
                                                <div class="flex-grow-1">
                                                    <h3 class="h6"><?= htmlspecialchars($item['name']) ?></h3>
                                                    <p class="text-primary fw-bold"><?= number_format($item['base_price']) ?>đ</p>
                                                    <div class="d-flex gap-2">
                                                        <a href="index.php?url=product&id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                                                        <form method="POST" action="index.php?url=add-cart&id=<?= $item['id'] ?>" class="d-inline">
                                                            <button class="btn btn-sm btn-primary">Add to Cart</button>
                                                        </form>
                                                        <button class="btn btn-sm btn-outline-danger remove-fav" data-id="<?= $item['id'] ?>">Remove</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="cart-info__separate"></div>

                        <!-- ========== RECENT ORDERS ========== -->
                        <div class="profile-section">
                            <div class="d-flex justify-content-between align-items-center">
                                <h2 class="cart-info__heading">Recent Orders</h2>
                                <a href="index.php?url=orders" class="btn btn-sm btn-link">View all</a>
                            </div>
                            <?php if (empty($recentOrders)): ?>
                                <p class="text-muted">No orders yet.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr><th>Order #</th><th>Date</th><th>Total</th><th>Status</th><th></th></tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recentOrders as $order): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($order['order_code'] ?? '#'.$order['id']) ?></td>
                                                <td><?= date('d/m/Y', strtotime($order['created_at'])) ?></td>
                                                <td><?= number_format($order['final_amount'], 0, ',', '.') ?>đ</td>
                                                <td><?= ucfirst($order['status']) ?></td>
                                                <td><a href="index.php?url=order-detail&id=<?= $order['id'] ?>" class="btn btn-sm btn-link">Detail</a></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Modal thêm/sửa địa chỉ -->
<div class="modal fade" id="addressModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="address-form" method="POST" action="index.php?url=add-shipping-address">
                <div class="modal-header">
                    <h5 class="modal-title">Add / Edit Address</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="address_id" id="address_id">
                    <div class="mb-3">
                        <label>Full Name</label>
                        <input type="text" name="recipient_name" id="address_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Phone</label>
                        <input type="text" name="phone" id="address_phone" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Address</label>
                        <input type="text" name="address" id="address_line" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>City</label>
                        <input type="text" name="city" id="address_city" class="form-control" required>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_default" id="address_default" class="form-check-input" value="1">
                        <label class="form-check-label">Set as default</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Smooth scroll cho các link sidebar
document.querySelectorAll('.profile-menu__link').forEach(link => {
    link.addEventListener('click', function(e) {
        const hash = this.getAttribute('href');
        if (hash && hash.startsWith('#')) {
            e.preventDefault();
            const target = document.querySelector(hash);
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
                // active class
                document.querySelectorAll('.profile-menu__link').forEach(l => l.classList.remove('active'));
                this.classList.add('active');
            }
        }
    });
});

// Address edit
document.querySelectorAll('.edit-address').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('address_id').value = this.dataset.id;
        document.getElementById('address_name').value = this.dataset.name;
        document.getElementById('address_phone').value = this.dataset.phone;
        document.getElementById('address_line').value = this.dataset.address;
        document.getElementById('address_city').value = this.dataset.city;
        document.getElementById('address_default').checked = this.dataset.default == '1';
        new bootstrap.Modal(document.getElementById('addressModal')).show();
    });
});

// Delete address
document.querySelectorAll('.delete-address').forEach(btn => {
    btn.addEventListener('click', function() {
        if (confirm('Are you sure?')) {
            const id = this.dataset.id;
            fetch(`index.php?url=delete-address&id=${id}`, { method: 'GET' })
                .then(res => res.json())
                .then(data => {
                    if (data.success) location.reload();
                    else alert('Delete failed');
                });
        }
    });
});

// Remove favorite
document.querySelectorAll('.remove-fav').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        fetch(`index.php?url=remove-favorite&id=${id}`, { method: 'GET' })
            .then(res => res.json())
            .then(data => {
                if (data.success) location.reload();
                else alert('Remove failed');
            });
    });
});
</script>
