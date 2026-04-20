<?php
$settings = $settings ?? [];
$success  = $success ?? '';
$error    = $error ?? '';
?>
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Cài đặt hệ thống</h1>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($success) ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($error) ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
<?php endif; ?>

<div class="row">
    <!-- Cột trái: Thông tin cửa hàng -->
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
                        <input type="text" name="settings[site_name]" class="form-control"
                               value="<?= htmlspecialchars($settings['site_name'] ?? 'FastFood Admin') ?>">
                    </div>
                    <div class="form-group">
                        <label>Email liên hệ</label>
                        <input type="email" name="settings[site_email]" class="form-control"
                               value="<?= htmlspecialchars($settings['site_email'] ?? 'contact@fastfood.com') ?>">
                    </div>
                    <div class="form-group">
                        <label>Số điện thoại</label>
                        <input type="text" name="settings[site_phone]" class="form-control"
                               value="<?= htmlspecialchars($settings['site_phone'] ?? '1900xxxx') ?>">
                    </div>
                    <div class="form-group">
                        <label>Địa chỉ</label>
                        <textarea name="settings[site_address]" class="form-control" rows="2"><?= htmlspecialchars($settings['site_address'] ?? '123 Đường Lê Lợi, Quận 1, TP.HCM') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Logo cửa hàng</label><br>
                        <?php
                        $logoPath = $settings['logo'] ?? 'uploads/logo_default.png';
                        $fullLogoPath = __DIR__ . '/../../../public/' . $logoPath;
                        if (!empty($logoPath) && file_exists($fullLogoPath)):
                        ?>
                            <img src="<?= htmlspecialchars($base . $logoPath) ?>" width="120" class="img-thumbnail mb-2"><br>
                        <?php endif; ?>
                        <input type="file" name="logo_file" class="form-control-file" accept="image/*">
                        <input type="hidden" name="settings[logo]" value="<?= htmlspecialchars($logoPath) ?>">
                        <small class="text-muted">Để trống nếu không muốn thay đổi logo</small>
                    </div>
                    <button type="submit" class="btn btn-primary">Lưu cài đặt</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Cột phải: Cấu hình đơn hàng & thuế -->
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
                        <input type="number" step="0.1" name="settings[tax_rate]" class="form-control"
                               value="<?= htmlspecialchars($settings['tax_rate'] ?? '10') ?>">
                    </div>
                    <div class="form-group">
                        <label>Phí ship mặc định (VNĐ)</label>
                        <input type="number" step="1000" name="settings[shipping_fee]" class="form-control"
                               value="<?= htmlspecialchars($settings['shipping_fee'] ?? '15000') ?>">
                    </div>
                    <div class="form-group">
                        <label>Múi giờ</label>
                        <select name="settings[timezone]" class="form-control">
                            <?php $tz = $settings['timezone'] ?? 'Asia/Ho_Chi_Minh'; ?>
                            <option value="Asia/Ho_Chi_Minh" <?= $tz == 'Asia/Ho_Chi_Minh' ? 'selected' : '' ?>>Asia/Ho_Chi_Minh (GMT+7)</option>
                            <option value="Asia/Bangkok" <?= $tz == 'Asia/Bangkok' ? 'selected' : '' ?>>Asia/Bangkok (GMT+7)</option>
                            <option value="Asia/Singapore" <?= $tz == 'Asia/Singapore' ? 'selected' : '' ?>>Asia/Singapore (GMT+8)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Đơn vị tiền tệ</label>
                        <select name="settings[currency]" class="form-control">
                            <?php $cur = $settings['currency'] ?? 'VND'; ?>
                            <option value="VND" <?= $cur == 'VND' ? 'selected' : '' ?>>VND - Việt Nam Đồng</option>
                            <option value="USD" <?= $cur == 'USD' ? 'selected' : '' ?>>USD - US Dollar</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="autoConfirm" name="settings[order_auto_confirm]" value="1" <?= ($settings['order_auto_confirm'] ?? 0) == 1 ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="autoConfirm">Tự động xác nhận đơn hàng (không cần duyệt)</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Lưu cài đặt</button>
                </form>
            </div>
        </div>

        <!-- Thông tin khác -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Thông tin khác</h6>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="update">
                    <div class="form-group">
                        <label>Mã Google Analytics (nếu có)</label>
                        <input type="text" name="settings[google_analytics]" class="form-control"
                               value="<?= htmlspecialchars($settings['google_analytics'] ?? '') ?>" placeholder="UA-xxxxxx-x">
                    </div>
                    <div class="form-group">
                        <label>Facebook Pixel ID</label>
                        <input type="text" name="settings[facebook_pixel]" class="form-control"
                               value="<?= htmlspecialchars($settings['facebook_pixel'] ?? '') ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Lưu cài đặt</button>
                </form>
            </div>
        </div>
    </div>
</div>
