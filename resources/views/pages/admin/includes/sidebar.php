<?php
$roleId = $_SESSION['user']['role_id'] ?? 3;

$baseUrl = ($roleId == 1) ? 'admin.php' : 'staff.php';

$role = match ($roleId) {
    1 => 'admin',
    2 => 'staff',
    default => 'customer'
};
?>

<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- BRAND -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center"
       href="<?= $baseUrl ?>?url=dashboard">
        <div class="sidebar-brand-icon">
            <i class="fas fa-hamburger"></i>
        </div>
        <div class="sidebar-brand-text mx-2">
            FastFood <?= strtoupper($role) ?>
        </div>
    </a>

    <hr class="sidebar-divider my-0">

    <!-- DASHBOARD - Cho cả Admin & Staff -->
    <li class="nav-item active">
        <a class="nav-link" href="<?= $baseUrl ?>?url=dashboard">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <?php if ($role === 'admin'): ?>
        <!-- ==================== ADMIN MENU ==================== -->
        <div class="sidebar-heading">QUẢN LÝ BÁN HÀNG</div>

        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#productMenu">
                <i class="fas fa-utensils"></i><span>Món ăn</span>
            </a>
            <div id="productMenu" class="collapse">
                <div class="collapse-inner bg-white rounded">
                    <a class="collapse-item" href="<?= $baseUrl ?>?url=products">Danh sách</a>
                    <a class="collapse-item" href="<?= $baseUrl ?>?url=categories">Danh mục</a>
                    <a class="collapse-item" href="<?= $baseUrl ?>?url=variants">Size</a>
                    <a class="collapse-item" href="<?= $baseUrl ?>?url=toppings">Topping</a>
                </div>
            </div>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="<?= $baseUrl ?>?url=orders">
                <i class="fas fa-shopping-cart"></i><span>Đơn hàng</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="<?= $baseUrl ?>?url=reviews">
                <i class="fas fa-star"></i><span>Đánh giá</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="<?= $baseUrl ?>?url=vouchers">
                <i class="fas fa-percent"></i><span>Khuyến mãi</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="<?= $baseUrl ?>?url=report">
                <i class="fas fa-chart-line"></i><span>Báo cáo</span>
            </a>
        </li>

        <hr class="sidebar-divider">

        <div class="sidebar-heading">QUẢN TRỊ HỆ THỐNG</div>

        <li class="nav-item">
            <a class="nav-link" href="<?= $baseUrl ?>?url=users">
                <i class="fas fa-users"></i><span>Người dùng</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="<?= $baseUrl ?>?url=settings">
                <i class="fas fa-cog"></i><span>Cài đặt</span>
            </a>
        </li>

    <?php else: ?>
        <!-- ==================== STAFF MENU (Chỉ Đơn hàng) ==================== -->
        <div class="sidebar-heading">QUẢN LÝ BÁN HÀNG</div>

        <li class="nav-item">
            <a class="nav-link" href="<?= $baseUrl ?>?url=orders">
                <i class="fas fa-shopping-cart"></i><span>Đơn hàng</span>
            </a>
        </li>
    <?php endif; ?>

    <hr class="sidebar-divider">

    <!-- LOGOUT -->
    <li class="nav-item">
        <a class="nav-link" href="admin.php?url=logout">
            <i class="fas fa-sign-out-alt"></i><span>Đăng xuất</span>
        </a>
    </li>

</ul>
