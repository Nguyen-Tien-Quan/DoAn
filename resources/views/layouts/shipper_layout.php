<?php
$base = '/DoAn/DoAnTotNghiep/public/';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Lấy thông tin shipper từ session
$shipperName = $_SESSION['user']['name'] ?? 'Shipper';
$avatarFromSession = $_SESSION['user']['avatar'] ?? '';
// Nếu không có avatar hoặc file không tồn tại thì dùng placeholder
$shipperAvatar = $base . 'assets/img/avatar-placeholder.png';
if (!empty($avatarFromSession)) {
    // Nếu avatar là đường dẫn đầy đủ hoặc tương đối
    if (strpos($avatarFromSession, 'http') === 0) {
        $shipperAvatar = $avatarFromSession;
    } else {
        $avatarPath = $_SERVER['DOCUMENT_ROOT'] . $base . ltrim($avatarFromSession, '/');
        if (file_exists($avatarPath)) {
            $shipperAvatar = $base . ltrim($avatarFromSession, '/');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Shipper - TRQshop</title>

    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="76x76" href="<?= $base ?>assets/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= $base ?>assets/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= $base ?>assets/favicon/favicon-16x16.png">

    <!-- CSS -->
    <link rel="stylesheet" href="<?= $base ?>assets/fonts/stylesheet.css">
    <link rel="stylesheet" href="<?= $base ?>assets/css/main.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
    /* ========== RESET & BASE (1rem = 10px) ========== */
    html {
        font-size: 62.5%; /* 10px */
        box-sizing: border-box;
    }
    *, *::before, *::after {
        box-sizing: inherit;
        margin: 0;
        padding: 0;
    }
    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        font-size: 1.4rem; /* 14px */
        line-height: 1.5;
        color: #1e293b;
        background-color: #f8fafc;
        margin: 0;
    }
    a {
        text-decoration: none;
        color: inherit;
    }
    ul {
        list-style: none;
    }
    .container {
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1.6rem;
    }

    /* ========== SHIPPER LAYOUT ========== */
    .shipper-app {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    /* ----- Header ----- */
    .shipper-header {
        background: #ffffff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        padding: 1.2rem 0;
        position: sticky;
        top: 0;
        z-index: 100;
    }
    .header-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .header-left {
        display: flex;
        align-items: center;
        gap: 1.6rem;
    }
    .menu-toggle {
        background: none;
        border: none;
        font-size: 2rem;
        color: #475569;
        cursor: pointer;
        padding: 0.8rem;
        border-radius: 0.8rem;
        display: none;
    }
    .menu-toggle:hover {
        background: #f1f5f9;
    }
    .logo {
        font-size: 2rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
    }
    .header-right .shipper-profile {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        cursor: pointer;
        padding: 0.6rem 1.2rem;
        border-radius: 3rem;
        background: #f8fafc;
        transition: background 0.2s;
        position: relative;
    }
    .shipper-profile:hover {
        background: #e2e8f0;
    }
    .shipper-profile .avatar {
        width: 3.2rem;
        height: 3.2rem;
        border-radius: 50%;
        object-fit: cover;
    }
    .shipper-name {
        font-weight: 500;
        font-size: 1.4rem;
    }
    .shipper-profile i {
        font-size: 1.2rem;
        color: #64748b;
    }
    .profile-dropdown-menu {
        position: absolute;
        top: 100%;
        right: 0;
        margin-top: 0.8rem;
        background: white;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        border-radius: 0.8rem;
        min-width: 180px;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.2s;
        z-index: 200;
    }
    .shipper-profile.active .profile-dropdown-menu {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
    .profile-dropdown-menu a {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.2rem 1.6rem;
        font-size: 1.4rem;
        color: #334155;
        transition: background 0.2s;
    }
    .profile-dropdown-menu a:hover {
        background: #f1f5f9;
    }
    .profile-dropdown-menu a i {
        width: 2rem;
        color: #64748b;
    }

    /* ----- Main Area (Sidebar + Content) ----- */
    .shipper-main {
        display: flex;
        flex: 1;
    }

    /* ----- Sidebar ----- */
    .shipper-sidebar {
        width: 260px;
        background: #ffffff;
        border-right: 1px solid #e2e8f0;
        padding: 2rem 0;
        transition: transform 0.3s ease;
    }
    .sidebar-nav ul {
        display: flex;
        flex-direction: column;
    }
    .nav-item {
        margin-bottom: 0.4rem;
    }
    .nav-item a {
        display: flex;
        align-items: center;
        gap: 1.2rem;
        padding: 1.2rem 2rem;
        font-size: 1.5rem;
        font-weight: 500;
        color: #334155;
        border-radius: 0.8rem;
        margin: 0 0.8rem;
        transition: all 0.2s;
    }
    .nav-item a i {
        width: 2rem;
        font-size: 1.8rem;
        color: #64748b;
    }
    .nav-item a:hover {
        background: #f1f5f9;
        color: #0f172a;
    }
    .nav-item.active a {
        background: #eff6ff;
        color: #2563eb;
    }
    .nav-item.active a i {
        color: #2563eb;
    }

    /* ----- Content Area ----- */
    .shipper-content {
        flex: 1;
        padding: 2.4rem 0;
        background: #f8fafc;
        min-height: calc(100vh - 7rem);
    }

    /* ----- Page Header ----- */
    .page-header {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 2.4rem;
    }
    .page-header h2 {
        font-size: 2.4rem;
        font-weight: 700;
        color: #0f172a;
    }
    .order-tabs {
        display: flex;
        gap: 0.8rem;
        flex-wrap: wrap;
    }
    .tab-btn {
        padding: 0.8rem 1.6rem;
        font-size: 1.4rem;
        font-weight: 500;
        color: #475569;
        background: transparent;
        border: 1px solid #cbd5e1;
        border-radius: 3rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    .tab-btn:hover {
        background: #e2e8f0;
    }
    .tab-btn.active {
        background: #2563eb;
        color: white;
        border-color: #2563eb;
    }

    /* ----- Stats Grid ----- */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.6rem;
        margin-bottom: 2.4rem;
    }
    .stat-card {
        background: white;
        border-radius: 1.2rem;
        padding: 1.6rem;
        display: flex;
        align-items: center;
        gap: 1.2rem;
        box-shadow: 0 2px 6px rgba(0,0,0,0.04);
    }
    .stat-card i {
        font-size: 2.4rem;
        color: #2563eb;
        width: 4rem;
        height: 4rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #eff6ff;
        border-radius: 1rem;
    }
    .stat-info {
        display: flex;
        flex-direction: column;
    }
    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #0f172a;
    }
    .stat-label {
        font-size: 1.3rem;
        color: #64748b;
    }

    /* ----- Order List ----- */
    .order-list {
        display: flex;
        flex-direction: column;
        gap: 1.6rem;
    }
    .order-card {
        background: #ffffff;
        border-radius: 1.2rem;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        transition: box-shadow 0.2s;
    }
    .order-card:hover {
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
    }
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.6rem 2rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .order-code {
        font-weight: 700;
        font-size: 1.6rem;
        color: #0f172a;
    }
    .order-status {
        padding: 0.4rem 1.2rem;
        border-radius: 2rem;
        font-size: 1.3rem;
        font-weight: 600;
    }
    .status-pending {
        background: #fef3c7;
        color: #b45309;
    }
    .status-shipping {
        background: #dbeafe;
        color: #1e40af;
    }
    .status-completed {
        background: #dcfce7;
        color: #15803d;
    }
    .status-failed {
        background: #fee2e2;
        color: #b91c1c;
    }
    .card-body {
        padding: 1.6rem 2rem;
    }
    .customer-info, .address-info {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
        font-size: 1.5rem;
    }
    .customer-info i, .address-info i {
        width: 2rem;
        color: #64748b;
        font-size: 1.6rem;
    }
    .customer-info .phone {
        margin-left: auto;
        color: #475569;
        font-weight: 500;
    }
    .address-info {
        margin-bottom: 1.6rem;
    }
    .address-info span {
        line-height: 1.4;
    }
    .order-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 1.6rem;
        background: #f8fafc;
        padding: 1rem 1.6rem;
        border-radius: 0.8rem;
    }
    .meta-item {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        font-size: 1.3rem;
        color: #334155;
    }
    .meta-item i {
        color: #94a3b8;
        width: 1.6rem;
    }
    .card-footer {
        padding: 1.2rem 2rem 1.6rem;
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        border-top: 1px solid #f1f5f9;
    }

    /* ----- Buttons ----- */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 1rem 1.8rem;
        font-size: 1.4rem;
        font-weight: 600;
        border-radius: 0.8rem;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        background: #f1f5f9;
        color: #1e293b;
        border: 1px solid transparent;
        text-decoration: none;
    }
    .btn:hover {
        opacity: 0.9;
    }
    .btn-primary {
        background: #2563eb;
        color: white;
    }
    .btn-success {
        background: #16a34a;
        color: white;
    }
    .btn-danger {
        background: #dc2626;
        color: white;
    }
    .btn-outline {
        background: transparent;
        border-color: #cbd5e1;
        color: #475569;
    }
    .btn-outline:hover {
        background: #f8fafc;
    }
    .btn-accept {
        background: #ea580c;
        color: white;
    }

    /* ----- Empty State ----- */
    .empty-state {
        text-align: center;
        padding: 4rem;
        background: white;
        border-radius: 1.2rem;
        color: #64748b;
    }
    .empty-state i {
        font-size: 4rem;
        margin-bottom: 1rem;
    }

    /* ========== RESPONSIVE ========== */
    @media (max-width: 768px) {
        .menu-toggle {
            display: block;
        }
        .shipper-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 200;
            transform: translateX(-100%);
            box-shadow: 2px 0 12px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .shipper-sidebar.open {
            transform: translateX(0);
        }
        .shipper-content {
            padding: 1.6rem 0;
        }
        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1.2rem;
        }
        .page-header h2 {
            font-size: 2rem;
        }
        .order-tabs {
            width: 100%;
            overflow-x: auto;
            padding-bottom: 0.4rem;
            -webkit-overflow-scrolling: touch;
        }
        .tab-btn {
            white-space: nowrap;
        }
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .order-card .card-header,
        .order-card .card-body,
        .order-card .card-footer {
            padding-left: 1.6rem;
            padding-right: 1.6rem;
        }
        .card-footer .btn {
            flex: 1;
        }
        .customer-info, .address-info {
            font-size: 1.4rem;
        }
        .order-meta {
            flex-direction: column;
            gap: 0.8rem;
        }
    }

    @media (max-width: 480px) {
        html {
            font-size: 56.25%; /* 1rem = 9px */
        }
        .container {
            padding: 0 1.2rem;
        }
        .logo {
            font-size: 1.8rem;
        }
        .shipper-name {
            display: none;
        }
        .stats-grid {
            grid-template-columns: 1fr;
        }
    }
    </style>
</head>
<body>
    <div class="shipper-app">
        <!-- Header -->
        <header class="shipper-header">
            <div class="container">
                <div class="header-content">
                    <div class="header-left">
                        <button class="menu-toggle" id="menuToggle">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h1 class="logo">Shipper TRQ</h1>
                    </div>
                    <div class="header-right">
                        <div class="shipper-profile" id="profileDropdown">
                            <img src="<?= $shipperAvatar ?>" alt="Avatar" class="avatar">
                            <span class="shipper-name"><?= htmlspecialchars($shipperName) ?></span>
                            <i class="fas fa-chevron-down"></i>
                            <!-- Dropdown menu -->
                            <div class="profile-dropdown-menu">
                                <a href="shipper.php?action=profile"><i class="fas fa-user"></i> Tài khoản</a>
                                <a href="index.php?url=logout"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Layout: Sidebar + Content -->
        <div class="shipper-main">
            <!-- Sidebar -->
            <aside class="shipper-sidebar" id="sidebar">
                <nav class="sidebar-nav">
                    <ul>
                        <li class="nav-item <?= ($action == 'dashboard') ? 'active' : '' ?>">
                            <a href="shipper.php?action=dashboard"><i class="fas fa-tasks"></i> <span>Đơn hàng của tôi</span></a>
                        </li>
                        <li class="nav-item <?= ($action == 'available') ? 'active' : '' ?>">
                            <a href="shipper.php?action=available"><i class="fas fa-box-open"></i> <span>Đơn chưa nhận</span></a>
                        </li>
                        <li class="nav-item <?= ($action == 'history') ? 'active' : '' ?>">
                            <a href="shipper.php?action=history"><i class="fas fa-history"></i> <span>Lịch sử giao hàng</span></a>
                        </li>
                        <li class="nav-item <?= ($action == 'profile') ? 'active' : '' ?>">
                            <a href="shipper.php?action=profile"><i class="fas fa-user-circle"></i> <span>Tài khoản</span></a>
                        </li>
                        <li class="nav-item">
                            <a href="index.php?url=logout"><i class="fas fa-sign-out-alt"></i> <span>Đăng xuất</span></a>
                        </li>
                    </ul>
                </nav>
            </aside>

            <!-- Content chính -->
            <main class="shipper-content">
                <div class="container">
                    <?php if (!empty($view) && file_exists($view)): ?>
                        <?php include $view; ?>
                    <?php else: ?>
                        <p>❌ Không tìm thấy view: <?= $view ?? 'null' ?></p>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Hàm hiển thị toast thay cho alert
        function showToast(message, type = "success") {
            const toast = document.createElement("div");
            toast.innerText = message;
            toast.style.position = "fixed";
            toast.style.bottom = "20px";
            toast.style.right = "20px";
            toast.style.padding = "12px 18px";
            toast.style.borderRadius = "8px";
            toast.style.color = "#fff";
            toast.style.fontSize = "14px";
            toast.style.zIndex = 9999;
            toast.style.opacity = "0";
            toast.style.transform = "translateY(20px)";
            toast.style.transition = "all 0.3s ease";
            if (type === "success") toast.style.background = "#28a745";
            else if (type === "error") toast.style.background = "#dc3545";
            else toast.style.background = "#333";

            document.body.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = "1";
                toast.style.transform = "translateY(0)";
            }, 10);
            setTimeout(() => {
                toast.style.opacity = "0";
                toast.style.transform = "translateY(20px)";
                setTimeout(() => toast.remove(), 300);
            }, 2500);
        }

        // Toggle sidebar trên mobile
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        if (menuToggle && sidebar) {
            menuToggle.addEventListener('click', () => {
                sidebar.classList.toggle('open');
            });
        }

        // Đóng sidebar khi click ra ngoài (mobile)
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
                    sidebar.classList.remove('open');
                }
            }
        });

        // Dropdown profile
        const profileDropdown = document.getElementById('profileDropdown');
        if (profileDropdown) {
            profileDropdown.addEventListener('click', function(e) {
                this.classList.toggle('active');
            });
        }

        // Các hàm dùng chung cho AJAX (sẽ được gọi từ view)
        function acceptOrder(orderId) {
            if (!confirm('Nhận đơn hàng này?')) return;
            fetch('shipper.php?action=accept-order', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'order_id=' + orderId
            })
            .then(res => res.json())
            .then(data => {
                showToast(data.message, data.success ? 'success' : 'error');
                if (data.success) setTimeout(() => location.reload(), 800);
            })
            .catch(err => showToast('Lỗi kết nối', 'error'));
        }

        function updateStatus(orderId, status) {
            let note = '';
            if (status === 'failed') {
                note = prompt('Nhập lý do thất bại:');
                if (note === null) return;
            }
            fetch('shipper.php?action=update-status', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'order_id=' + orderId + '&status=' + status + '&note=' + encodeURIComponent(note)
            })
            .then(res => res.json())
            .then(data => {
                showToast(data.message || (data.success ? 'Thành công' : 'Lỗi'), data.success ? 'success' : 'error');
                if (data.success) setTimeout(() => location.reload(), 800);
            })
            .catch(err => showToast('Lỗi kết nối', 'error'));
        }
    </script>
</body>
</html>
