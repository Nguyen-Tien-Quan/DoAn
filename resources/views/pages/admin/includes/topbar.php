<?php
global $currentUser, $pdo;
if (!isset($currentUser) && isset($GLOBALS['currentUser'])) {
    $currentUser = $GLOBALS['currentUser'];
}
if (!isset($currentUser) && isset($_SESSION['user_id'])) {
    require_once __DIR__ . '/db.php';
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $currentUser = $stmt->fetch();
}
if (!function_exists('getPendingOrdersCount')) {
    require_once __DIR__ . '/functions.php';
}
$pendingOrdersCount = getPendingOrdersCount($pdo);
$lowStockCount = getLowStockIngredientsCount($pdo);
$totalNotifications = $pendingOrdersCount + $lowStockCount;
?>
<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3"><i class="fa fa-bars"></i></button>
    <form class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
        <div class="input-group">
            <input type="text" class="form-control bg-light border-0 small" placeholder="Tìm kiếm...">
            <div class="input-group-append"><button class="btn btn-primary" type="button"><i class="fas fa-search fa-sm"></i></button></div>
        </div>
    </form>
    <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown no-arrow mx-1">
            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" data-toggle="dropdown">
                <i class="fas fa-bell fa-fw"></i>
                <?php if ($totalNotifications > 0): ?>
                    <span class="badge badge-danger badge-counter"><?= $totalNotifications ?></span>
                <?php endif; ?>
            </a>
            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in">
                <h6 class="dropdown-header">Thông báo</h6>
                <?php if ($pendingOrdersCount > 0): ?>
                    <a class="dropdown-item" href="orders.php?status=pending"><i class="fas fa-shopping-cart mr-2 text-warning"></i><?= $pendingOrdersCount ?> đơn hàng chờ xử lý</a>
                <?php endif; ?>
                <?php if ($lowStockCount > 0): ?>
                    <a class="dropdown-item" href="ingredients.php"><i class="fas fa-boxes mr-2 text-danger"></i><?= $lowStockCount ?> nguyên liệu sắp hết</a>
                <?php endif; ?>
                <?php if ($totalNotifications == 0): ?>
                    <a class="dropdown-item text-center text-muted" href="#">Không có thông báo mới</a>
                <?php endif; ?>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item text-center small text-gray-500" href="#">Xem tất cả</a>
            </div>
        </li>
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" data-toggle="dropdown">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?= htmlspecialchars($currentUser['name'] ?? $_SESSION['user_name'] ?? 'Admin') ?></span>
                <?php 
                $avatar = !empty($currentUser['avatar']) ? $currentUser['avatar'] : 'img/undraw_profile.svg';
                ?>
                <img class="img-profile rounded-circle" src="<?= htmlspecialchars($avatar) ?>" width="32" height="32" style="object-fit: cover;">
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in">
                <a class="dropdown-item" href="profile.php"><i class="fas fa-user fa-sm fa-fw mr-2"></i> Hồ sơ</a>
                <a class="dropdown-item" href="settings.php"><i class="fas fa-cogs fa-sm fa-fw mr-2"></i> Cài đặt</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt fa-sm fa-fw mr-2"></i> Đăng xuất</a>
            </div>
        </li>
    </ul>
</nav>