<?php
// Các biến được truyền từ index.php: $user, $addresses, $notifications, $success, $error
$base = '/DoAn/DoAnTotNghiep/public/';
$avatarPath = !empty($user['avatar']) ? $base . 'assets/img/avatars/' . $user['avatar'] : $base . 'assets/img/avatar-default.png';
?>
<style>
    /* ========== DÙNG BIẾN TỪ LIGHT THEME (TỰ ĐỘNG THEO DARK) ========== */
    .settings-layout {
        max-width: 1280px;
        margin: 0 auto;
        padding: 24px 16px;
        display: flex;
        gap: 24px;
        flex-wrap: wrap;
    }
    .settings-sidebar {
        flex: 0 0 260px;
        background: var(--sidebar-bg, #fff);
        border-radius: 16px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.05);
        padding: 20px 0;
        height: fit-content;
        position: sticky;
        top: 80px;
    }
    .settings-content {
        flex: 1;
        min-width: 0;
        background: var(--product-card-bg, #fff);
        border-radius: 16px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.05);
        padding: 24px 28px;
    }
    .sidebar-menu {
        list-style: none;
    }
    .sidebar-menu li {
        padding: 12px 24px;
        margin: 4px 12px;
        border-radius: 12px;
        font-weight: 500;
        font-size: 1.6rem;
        cursor: pointer;
        transition: all 0.2s;
        color: var(--text-color, #4a4a4a);
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .sidebar-menu li.active {
        background: #fff1e6;
        color: #ee4d2d;
        font-weight: 600;
    }
    .sidebar-menu li:hover:not(.active) {
        background: var(--form-tag-bg, #f8f8fb);
    }
    .section-title {
        font-size: 1.6rem;
        font-weight: 600;
        margin-bottom: 24px;
        padding-bottom: 12px;
        border-bottom: 2px solid #ee4d2d;
        display: inline-block;
        color: var(--text-color, #1a162e);
    }
    .form-group {
        margin-bottom: 24px;
    }
    .form-group label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        font-size: 1.65rem;
        color: var(--text-color, #2c2c2c);
    }
    .form-group input,
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 14px 16px;
        font-size: 1.6rem;
        border: 1px solid var(--separate-color, #ddd);
        border-radius: 12px;
        background: var(--top-act-group-bg-color, #fff);
        color: var(--text-color, #333);
        transition: 0.2s;
        font-family: inherit;
    }
    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #ee4d2d;
        box-shadow: 0 0 0 3px rgba(238,77,45,0.1);
    }
    .avatar-upload {
        display: flex;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
        margin-bottom: 24px;
    }
    .avatar-preview {
        width: 96px;
        height: 96px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--separate-color, #eee);
        background: var(--form-tag-bg, #f0f0f0);
    }
    .avatar-input {
        flex: 1;
    }
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 24px;
        font-size: 1.65rem;
        font-weight: 600;
        border-radius: 40px;
        border: none;
        cursor: pointer;
        transition: 0.2s;
        background: var(--form-tag-bg, #f0f0f0);
        color: var(--text-color, #333);
    }
    .btn-primary {
        background: #ee4d2d;
        color: white;
    }
    .btn-primary:hover {
        background: #d73211;
    }
    .btn-outline {
        background: transparent;
        border: 1px solid var(--separate-color, #ddd);
    }
    .btn-outline:hover {
        border-color: #ee4d2d;
        color: #ee4d2d;
    }
    .btn-danger {
        background: #fff2f0;
        color: #ee4d2d;
    }
    .btn-danger:hover {
        background: #ffe6e2;
    }
    .address-card {
        background: var(--review-card-bg, #fafafa);
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 20px;
        border: 1px solid var(--separate-color, #eee);
    }
    .address-card .row-2col {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    .address-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 20px;
    }
    .notif-item {
        background: var(--top-act-group-bg-color, #fff);
        border: 1px solid var(--separate-color, #efefef);
        border-radius: 16px;
        padding: 16px;
        margin-bottom: 12px;
        cursor: pointer;
    }
    .notif-item.unread {
        background: #fff9f5;
        border-left: 4px solid #ee4d2d;
    }
    .notif-title {
        font-weight: 600;
        margin-bottom: 6px;
        color: var(--text-color, #333);
    }
    .notif-time {
        font-size: 1.65rem;
        color: #999;
        margin-top: 8px;
    }
    .alert {
        padding: 12px 18px;
        border-radius: 12px;
        margin-bottom: 24px;
        font-size: 1.6rem;
    }
    .alert-success {
        background: #e8f5e9;
        color: #2e7d32;
        border-left: 4px solid #2e7d32;
    }
    .alert-error {
        background: #ffebee;
        color: #c62828;
        border-left: 4px solid #c62828;
    }
    .menu-icon {
        font-size: 1.6rem;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 1024px) {
        .settings-layout { gap: 16px; }
        .settings-sidebar {
            flex: none;
            width: 100%;
            position: static;
            overflow-x: auto;
            white-space: nowrap;
            padding: 8px 12px;
            border-radius: 12px;
        }
        .section-title { font-size: 1.6rem; }
    }
    @media (max-width: 768px) {
        .settings-layout {
            flex-direction: column;
            padding: 12px;
            gap: 12px;
        }
        .settings-sidebar { width: 100%; position: static; overflow-x: auto; white-space: nowrap; padding: 8px 12px; }
        .sidebar-menu { display: inline-flex; gap: 8px; white-space: nowrap; }
        .sidebar-menu li { display: inline-flex; margin: 0; padding: 8px 16px; font-size: 1.6rem; }
        .settings-content { padding: 16px; overflow-x: hidden; }
        .form-group { margin-bottom: 16px; }
        .form-group label { font-size: 1.65rem; }
        .form-group input, .form-group textarea, .form-group select { padding: 10px 12px; font-size: 1.6rem; }
        .btn { padding: 8px 16px; font-size: 1.6rem; }
        .section-title { font-size: 1.6rem; margin-bottom: 16px; }
        .address-card .row-2col { grid-template-columns: 1fr; gap: 12px; }
        .address-actions { flex-direction: column; gap: 8px; }
        .address-actions .btn { width: 100%; }
        .avatar-upload { flex-direction: column; align-items: center; text-align: center; }
        .avatar-input { width: 100%; }
        .avatar-preview { width: 80px; height: 80px; }
    }
    @media (max-width: 480px) {
        .settings-content { padding: 12px; }
        .form-group input, .form-group textarea, .form-group select { padding: 8px 10px; font-size: 1.6rem; }
        .btn { padding: 6px 12px; font-size: 1.6rem; }
        .section-title { font-size: 1.6rem; }
        .address-card { padding: 12px; }
    }
</style>

<div class="settings-layout">
    <div class="settings-sidebar">
        <ul class="sidebar-menu">
            <li class="active" data-tab="profile"><span class="menu-icon">👤</span> Thông tin chung</li>
            <li data-tab="password"><span class="menu-icon">🔒</span> Đổi mật khẩu</li>
            <li data-tab="address"><span class="menu-icon">📍</span> Địa chỉ giao hàng</li>
            <li data-tab="notifications"><span class="menu-icon">🔔</span> Thông báo</li>
        </ul>
    </div>
    <div class="settings-content">
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div id="profile-tab" class="tab-pane active">
            <h2 class="section-title">Thông tin chung</h2>
            <form action="index.php?url=settings/updateProfile" method="POST" enctype="multipart/form-data">
                <div class="avatar-upload">
                    <img src="<?= $avatarPath ?>" class="avatar-preview" id="avatarPreview" alt="Avatar">
                    <div class="avatar-input">
                        <input type="file" name="avatar" accept="image/*" id="avatarFile" style="display:none;">
                        <button type="button" class="btn btn-outline" onclick="document.getElementById('avatarFile').click();">Chọn ảnh đại diện</button>
                        <small style="display:block; color:#777; margin-top:6px;">JPG, PNG, GIF, tối đa 2MB</small>
                    </div>
                </div>
                <div class="form-group"><label>Họ tên</label><input type="text" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required></div>
                <div class="form-group"><label>Email</label><input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required></div>
                <div class="form-group"><label>Số điện thoại</label><input type="tel" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="Nhập số điện thoại"></div>
                <div style="text-align: right;"><button type="submit" class="btn btn-primary">Lưu thay đổi</button></div>
            </form>
        </div>

        <div id="password-tab" class="tab-pane" style="display:none;">
            <h2 class="section-title">Đổi mật khẩu</h2>
            <form action="index.php?url=settings/changePassword" method="POST">
                <div class="form-group"><label>Mật khẩu hiện tại</label><input type="password" name="old_password" required></div>
                <div class="form-group"><label>Mật khẩu mới</label><input type="password" name="new_password" required></div>
                <div class="form-group"><label>Xác nhận mật khẩu</label><input type="password" name="confirm_password" required></div>
                <div style="text-align: right;"><button type="submit" class="btn btn-primary">Đổi mật khẩu</button></div>
            </form>
        </div>

        <div id="address-tab" class="tab-pane" style="display:none;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; margin-bottom: 20px;">
                <h2 class="section-title" style="margin-bottom:0;">Địa chỉ của tôi</h2>
                <button class="btn btn-primary" onclick="toggleAddressForm()">+ Thêm địa chỉ mới</button>
            </div>
            <div id="addressForm" style="display: none; background: var(--review-card-bg, #f9f9f9); border-radius: 20px; padding: 20px; margin-bottom: 24px;">
                <form action="index.php?url=settings/addAddress" method="POST">
                    <div class="form-group"><label>Họ tên người nhận</label><input type="text" name="full_name" required></div>
                    <div class="form-group"><label>Số điện thoại</label><input type="text" name="phone" required></div>
                    <div class="form-group"><label>Địa chỉ</label><textarea name="address" rows="2" required></textarea></div>
                    <div class="form-group"><label>Thành phố / Tỉnh</label><input type="text" name="city" required></div>
                    <div class="form-group"><label><input type="checkbox" name="is_default" value="1"> Đặt làm địa chỉ mặc định</label></div>
                    <div style="display: flex; gap: 12px; justify-content: flex-end;">
                        <button type="submit" class="btn btn-primary">Lưu</button>
                        <button type="button" class="btn btn-outline" onclick="toggleAddressForm()">Hủy</button>
                    </div>
                </form>
            </div>
            <div>
                <?php if (!empty($addresses)): ?>
                    <?php foreach ($addresses as $addr): ?>
                        <div class="address-card">
                            <form action="index.php?url=settings/updateAddress" method="POST">
                                <input type="hidden" name="address_id" value="<?= $addr['id'] ?>">
                                <div class="row-2col">
                                    <div class="form-group"><label>Họ tên</label><input type="text" name="full_name" value="<?= htmlspecialchars($addr['full_name'] ?? '') ?>" required></div>
                                    <div class="form-group"><label>Số điện thoại</label><input type="text" name="phone" value="<?= htmlspecialchars($addr['phone'] ?? '') ?>" required></div>
                                    <div class="form-group"><label>Địa chỉ</label><textarea name="address" rows="2" required><?= htmlspecialchars($addr['address'] ?? '') ?></textarea></div>
                                    <div class="form-group"><label>Thành phố</label><input type="text" name="city" value="<?= htmlspecialchars($addr['city'] ?? '') ?>" required></div>
                                </div>
                                <div class="form-group"><label><input type="checkbox" name="is_default" value="1" <?= (!empty($addr['is_default']) ? 'checked' : '') ?>> Địa chỉ mặc định</label></div>
                                <div class="address-actions">
                                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                                    <button type="button" class="btn btn-danger" onclick="deleteAddress(<?= $addr['id'] ?>)">Xóa</button>
                                </div>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert" style="background:#fafafa; text-align:center;">Bạn chưa có địa chỉ nào.</div>
                <?php endif; ?>
            </div>
        </div>

        <div id="notifications-tab" class="tab-pane" style="display:none;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; margin-bottom: 20px;">
                <h2 class="section-title" style="margin-bottom:0;">Thông báo</h2>
                <button class="btn btn-outline" onclick="markAllRead()">Đánh dấu tất cả đã đọc</button>
            </div>
            <div id="notifList">
                <?php if (!empty($notifications)): ?>
                    <?php foreach ($notifications as $notif): ?>
                        <div class="notif-item <?= empty($notif['is_read']) ? 'unread' : '' ?>" data-id="<?= $notif['id'] ?>" onclick="markRead(this)">
                            <div class="notif-title"><?= htmlspecialchars($notif['title'] ?? '') ?></div>
                            <div><?= nl2br(htmlspecialchars($notif['content'] ?? '')) ?></div>
                            <div class="notif-time"><?= htmlspecialchars($notif['created_at'] ?? '') ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert" style="background:#fafafa; text-align:center;">Không có thông báo nào.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    const menuItems = document.querySelectorAll('.sidebar-menu li');
    const panes = {
        profile: document.getElementById('profile-tab'),
        password: document.getElementById('password-tab'),
        address: document.getElementById('address-tab'),
        notifications: document.getElementById('notifications-tab')
    };
    function activateTab(tabId) {
        Object.keys(panes).forEach(id => { if (panes[id]) panes[id].style.display = 'none'; });
        if (panes[tabId]) panes[tabId].style.display = 'block';
        menuItems.forEach(item => {
            item.classList.remove('active');
            if (item.getAttribute('data-tab') === tabId) item.classList.add('active');
        });
    }
    menuItems.forEach(item => {
        item.addEventListener('click', () => { const tabId = item.getAttribute('data-tab'); if (tabId) activateTab(tabId); });
    });
    function previewAvatar(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = e => { const preview = document.getElementById('avatarPreview'); if (preview) preview.src = e.target.result; };
            reader.readAsDataURL(file);
        }
    }
    const avatarFile = document.getElementById('avatarFile');
    if (avatarFile) avatarFile.addEventListener('change', previewAvatar);
    function toggleAddressForm() { const formDiv = document.getElementById('addressForm'); if (formDiv) formDiv.style.display = formDiv.style.display === 'none' ? 'block' : 'none'; }
    function deleteAddress(id) { if (confirm('Xóa địa chỉ này?')) { const form = document.createElement('form'); form.method = 'POST'; form.action = 'index.php?url=settings/deleteAddress'; const input = document.createElement('input'); input.type = 'hidden'; input.name = 'address_id'; input.value = id; form.appendChild(input); document.body.appendChild(form); form.submit(); } }
    function markRead(element) { const notifId = element.dataset.id; fetch('index.php?url=settings/markNotificationRead', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'notification_id=' + notifId }).then(res => res.json()).then(data => { if (data.success) element.classList.remove('unread'); }); }
    function markAllRead() { fetch('index.php?url=settings/markAllRead', { method: 'POST' }).then(() => location.reload()); }
</script>
