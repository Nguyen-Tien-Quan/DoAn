<?php
require_once __DIR__ . '/includes/auth.php';
requireAdmin(); // Chỉ admin mới được vào trang cài đặt
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

// Tạo bảng settings nếu chưa tồn tại
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        setting_value TEXT,
        setting_type ENUM('text','textarea','image','number','email','phone') DEFAULT 'text',
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL
    )");
} catch (PDOException $e) {}

// Xử lý cập nhật cài đặt
$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update') {
    $settings = $_POST['settings'] ?? [];
    foreach ($settings as $key => $value) {
        $value = trim($value);
        // Xử lý upload logo
        if ($key == 'logo' && isset($_FILES['logo_file']) && $_FILES['logo_file']['error'] == 0) {
            $allowed = ['jpg','jpeg','png','gif','webp'];
            $ext = strtolower(pathinfo($_FILES['logo_file']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                if (!is_dir('uploads')) mkdir('uploads', 0777, true);
                $new_name = 'logo_' . uniqid() . '.' . $ext;
                $dest = 'uploads/' . $new_name;
                if (move_uploaded_file($_FILES['logo_file']['tmp_name'], $dest)) {
                    $value = $dest;
                    // Xóa logo cũ nếu tồn tại và không phải logo mặc định
                    $old_logo = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'logo'");
                    $old_logo->execute();
                    $old = $old_logo->fetchColumn();
                    if ($old && file_exists($old) && $old != 'uploads/logo_default.png') {
                        unlink($old);
                    }
                } else {
                    $error = "Upload logo thất bại.";
                }
            } else {
                $error = "Định dạng ảnh logo không hợp lệ (cho phép jpg, png, gif, webp).";
            }
        }
        
        if (empty($error)) {
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value, updated_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()");
            $stmt->execute([$key, $value, $value]);
            $success = "Cập nhật cài đặt thành công!";
        }
    }
}

// Lấy tất cả cài đặt hiện có
$settings = [];
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Gán giá trị mặc định nếu chưa có
$default_settings = [
    'site_name' => 'FastFood Admin',
    'site_email' => 'contact@fastfood.com',
    'site_phone' => '1900xxxx',
    'site_address' => '123 Đường Lê Lợi, Quận 1, TP.HCM',
    'tax_rate' => '10',
    'shipping_fee' => '15000',
    'logo' => 'uploads/logo_default.png',
    'timezone' => 'Asia/Ho_Chi_Minh',
    'currency' => 'VND',
    'order_auto_confirm' => '0',
    'google_analytics' => '',
    'facebook_pixel' => ''
];
foreach ($default_settings as $key => $default) {
    if (!isset($settings[$key])) {
        $settings[$key] = $default;
    }
}

// Tạo thư mục uploads nếu chưa có
if (!is_dir('uploads')) {
    mkdir('uploads', 0777, true);
}
?>

<div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
        <?php require_once __DIR__ . '/includes/topbar.php'; ?>
        <div class="container-fluid">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Cài đặt hệ thống</h1>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($success) ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Thông tin chung -->
                <div class="col-lg-6">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Thông tin cửa hàng</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="update">
                                
                                <div class="form-group">
                                    <label>Tên cửa hàng</label>
                                    <input type="text" name="settings[site_name]" class="form-control" value="<?= htmlspecialchars($settings['site_name']) ?>">
                                </div>
                                <div class="form-group">
                                    <label>Email liên hệ</label>
                                    <input type="email" name="settings[site_email]" class="form-control" value="<?= htmlspecialchars($settings['site_email']) ?>">
                                </div>
                                <div class="form-group">
                                    <label>Số điện thoại</label>
                                    <input type="text" name="settings[site_phone]" class="form-control" value="<?= htmlspecialchars($settings['site_phone']) ?>">
                                </div>
                                <div class="form-group">
                                    <label>Địa chỉ</label>
                                    <textarea name="settings[site_address]" class="form-control" rows="2"><?= htmlspecialchars($settings['site_address']) ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Logo cửa hàng</label><br>
                                    <?php if (!empty($settings['logo']) && file_exists($settings['logo'])): ?>
                                        <img src="<?= htmlspecialchars($settings['logo']) ?>" width="120" class="img-thumbnail mb-2"><br>
                                    <?php endif; ?>
                                    <input type="file" name="logo_file" class="form-control-file" accept="image/*">
                                    <input type="hidden" name="settings[logo]" value="<?= htmlspecialchars($settings['logo']) ?>">
                                    <small class="text-muted">Để trống nếu không muốn thay đổi logo</small>
                                </div>
                                <button type="submit" class="btn btn-primary">Lưu cài đặt</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Cấu hình đơn hàng & thuế -->
                <div class="col-lg-6">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Cấu hình đơn hàng & thuế</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="update">
                                
                                <div class="form-group">
                                    <label>Thuế VAT (%)</label>
                                    <input type="number" step="0.1" name="settings[tax_rate]" class="form-control" value="<?= htmlspecialchars($settings['tax_rate']) ?>">
                                </div>
                                <div class="form-group">
                                    <label>Phí ship mặc định (VNĐ)</label>
                                    <input type="number" step="1000" name="settings[shipping_fee]" class="form-control" value="<?= htmlspecialchars($settings['shipping_fee']) ?>">
                                </div>
                                <div class="form-group">
                                    <label>Múi giờ</label>
                                    <select name="settings[timezone]" class="form-control">
                                        <option value="Asia/Ho_Chi_Minh" <?= $settings['timezone'] == 'Asia/Ho_Chi_Minh' ? 'selected' : '' ?>>Asia/Ho_Chi_Minh (GMT+7)</option>
                                        <option value="Asia/Bangkok" <?= $settings['timezone'] == 'Asia/Bangkok' ? 'selected' : '' ?>>Asia/Bangkok (GMT+7)</option>
                                        <option value="Asia/Singapore" <?= $settings['timezone'] == 'Asia/Singapore' ? 'selected' : '' ?>>Asia/Singapore (GMT+8)</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Đơn vị tiền tệ</label>
                                    <select name="settings[currency]" class="form-control">
                                        <option value="VND" <?= $settings['currency'] == 'VND' ? 'selected' : '' ?>>VND - Việt Nam Đồng</option>
                                        <option value="USD" <?= $settings['currency'] == 'USD' ? 'selected' : '' ?>>USD - US Dollar</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="autoConfirm" name="settings[order_auto_confirm]" value="1" <?= $settings['order_auto_confirm'] == 1 ? 'checked' : '' ?>>
                                        <label class="custom-control-label" for="autoConfirm">Tự động xác nhận đơn hàng (không cần duyệt)</label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">Lưu cài đặt</button>
                            </form>
                        </div>
                    </div>

                    <!-- Cài đặt bảo mật / Thông tin khác -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Thông tin khác</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="update">
                                <div class="form-group">
                                    <label>Mã Google Analytics (nếu có)</label>
                                    <input type="text" name="settings[google_analytics]" class="form-control" value="<?= htmlspecialchars($settings['google_analytics'] ?? '') ?>" placeholder="UA-xxxxxx-x">
                                </div>
                                <div class="form-group">
                                    <label>Facebook Pixel ID</label>
                                    <input type="text" name="settings[facebook_pixel]" class="form-control" value="<?= htmlspecialchars($settings['facebook_pixel'] ?? '') ?>">
                                </div>
                                <button type="submit" class="btn btn-primary">Lưu cài đặt</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; require_once __DIR__ . '/includes/scripts.php'; ?>