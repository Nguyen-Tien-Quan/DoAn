<?php
$base = '/DoAn/DoAnTotNghiep/public/';

// Gộp dữ liệu user + customer
$avatarPath = !empty($user['avatar'])
    ? $base . 'assets/img/avatars/' . $user['avatar'] . '?v=' . time()
    : $base . 'assets/img/avatar-default.png';
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
            <li class="active" data-tab="profile">👤 Thông tin chung</li>
            <li data-tab="password">🔒 Đổi mật khẩu</li>
            <li data-tab="address">📍 Địa chỉ giao hàng</li>
            <li data-tab="notifications">🔔 Thông báo</li>
        </ul>
    </div>

    <div class="settings-content">
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <!-- ================= PROFILE ================= -->
        <div id="profile-tab" class="tab-pane active">
            <h2 class="section-title">Thông tin cá nhân</h2>

            <form action="index.php?url=settings/updateProfile" method="POST" enctype="multipart/form-data">

                <!-- Avatar -->
                <div class="avatar-upload">
                    <img src="<?= $avatarPath ?>" class="avatar-preview" id="avatarPreview">
                    <div>
                        <input type="file" name="avatar" id="avatarFile" hidden>
                        <button type="button" class="btn btn-outline" onclick="avatarFile.click()">Chọn ảnh</button>
                    </div>
                </div>

                <!-- USER -->
                <div class="form-group">
                    <label>Tên hiển thị</label>
                    <input type="text" name="name" value="<?= $user['name'] ?? '' ?>" required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= $user['email'] ?? '' ?>" required>
                </div>

                <div class="form-group">
                    <label>SĐT</label>
                    <input type="text" name="phone" value="<?= $user['phone'] ?? '' ?>">
                </div>

                <!-- CUSTOMER -->
                <div class="form-group">
                    <label>Họ tên đầy đủ</label>
                    <input type="text" name="full_name" value="<?= $user['full_name'] ?? '' ?>">
                </div>

                <div class="form-group">
                    <label>Giới tính</label>
                    <select name="gender">
                        <option value="">-- Chọn --</option>
                        <option value="male" <?= ($user['gender'] ?? '')=='male'?'selected':'' ?>>Nam</option>
                        <option value="female" <?= ($user['gender'] ?? '')=='female'?'selected':'' ?>>Nữ</option>
                        <option value="other" <?= ($user['gender'] ?? '')=='other'?'selected':'' ?>>Khác</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Ngày sinh</label>
                    <input type="date" name="birthday"
                        value="<?= !empty($user['birthday']) ? date('Y-m-d', strtotime($user['birthday'])) : '' ?>">
                </div>

                <div class="form-group">
                    <label>Địa chỉ cá nhân</label>
                    <textarea name="address"><?= $user['address'] ?? '' ?></textarea>
                </div>

                <div style="text-align:right">
                    <button class="btn btn-primary">Lưu thay đổi</button>
                </div>
            </form>
        </div>

        <!-- ================= PASSWORD ================= -->
        <div id="password-tab" class="tab-pane" style="display:none;">
            <h2 class="section-title">Đổi mật khẩu</h2>

            <form action="index.php?url=settings/changePassword" method="POST">
                <div class="form-group">
                    <label>Mật khẩu cũ</label>
                    <input type="password" name="old_password" required>
                </div>

                <div class="form-group">
                    <label>Mật khẩu mới</label>
                    <input type="password" name="new_password" required>
                </div>

                <div class="form-group">
                    <label>Xác nhận</label>
                    <input type="password" name="confirm_password" required>
                </div>

                <button class="btn btn-primary">Cập nhật</button>
            </form>
        </div>

        <!-- ================= ADDRESS ================= -->
        <div id="address-tab" class="tab-pane" style="display:none;">
            <h2 class="section-title">Địa chỉ giao hàng</h2>

            <?php if (!empty($addresses)): ?>
                <?php foreach ($addresses as $addr): ?>
                    <div class="address-card">
                        <b><?= $addr['full_name'] ?></b> - <?= $addr['phone'] ?><br>
                        <?= $addr['address'] ?>, <?= $addr['city'] ?>

                        <?php if ($addr['is_default']): ?>
                            <span style="color:red">[Mặc định]</span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Chưa có địa chỉ nào</p>
            <?php endif; ?>
        </div>

        <!-- ================= NOTIFICATION ================= -->
        <div id="notifications-tab" class="tab-pane" style="display:none;">
            <h2 class="section-title">Thông báo</h2>
            <?php if (!empty($notifications)): ?>
            <?php foreach ($notifications as $n): ?>
                <div class="notif-item <?= !$n['is_read']?'unread':'' ?>">
                    <b><?= $n['title'] ?></b><br>
                    <?= $n['content'] ?>
                </div>
            <?php endforeach; ?>
            <?php else: ?>
                <p>Chưa có Thông báo nào</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function activateTab(tab){
    document.querySelectorAll('.tab-pane').forEach(p=>p.style.display='none');
    document.getElementById(tab+'-tab').style.display='block';

    document.querySelectorAll('.sidebar-menu li').forEach(i=>i.classList.remove('active'));
    document.querySelector(`[data-tab="${tab}"]`).classList.add('active');
}

document.querySelectorAll('.sidebar-menu li').forEach(item=>{
    item.onclick = ()=>activateTab(item.dataset.tab);
});

// preview avatar
avatarFile.onchange = e=>{
    const reader = new FileReader();
    reader.onload = ev => avatarPreview.src = ev.target.result;
    reader.readAsDataURL(e.target.files[0]);
};
</script>
