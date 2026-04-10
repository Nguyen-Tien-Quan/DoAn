<?php
require_once 'includes/auth.php';
requireStaffOrAdmin();
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

$role = $_SESSION['role_name'];
$totalOrders = countRecords($pdo, 'orders');
$totalProducts = countRecords($pdo, 'products', 'WHERE status = 1');
$totalCustomers = countRecords($pdo, 'customers');
$totalRevenue = getTotalRevenue($pdo);
$pendingOrders = getPendingOrdersCount($pdo);
$lowStock = ($role == 'admin') ? getLowStockIngredientsCount($pdo) : 0;
?>
<div id="content-wrapper" class="d-flex flex-column"><div id="content"><?php require_once 'includes/topbar.php'; ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i class="fas fa-download fa-sm text-white-50"></i> Xuất báo cáo</a>
    </div>
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-primary shadow h-100 py-2"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Doanh thu</div><div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($totalRevenue) ?>đ</div></div><div class="col-auto"><i class="fas fa-dollar-sign fa-2x text-gray-300"></i></div></div></div></div></div>
        <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-success shadow h-100 py-2"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-success text-uppercase mb-1">Đơn hàng</div><div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalOrders ?></div></div><div class="col-auto"><i class="fas fa-shopping-cart fa-2x text-gray-300"></i></div></div></div></div></div>
        <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-info shadow h-100 py-2"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-info text-uppercase mb-1">Sản phẩm</div><div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalProducts ?></div></div><div class="col-auto"><i class="fas fa-utensils fa-2x text-gray-300"></i></div></div></div></div></div>
        <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-warning shadow h-100 py-2"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Khách hàng</div><div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalCustomers ?></div></div><div class="col-auto"><i class="fas fa-users fa-2x text-gray-300"></i></div></div></div></div></div>
    </div>
    <div class="row">
        <div class="col-lg-6 mb-4"><div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Đơn hàng chờ xử lý</h6></div><div class="card-body"><h3 class="text-danger"><?= $pendingOrders ?> đơn</h3><a href="orders.php?status=pending" class="btn btn-sm btn-primary">Xem chi tiết</a></div></div></div>
        <?php if ($role == 'admin'): ?>
        <div class="col-lg-6 mb-4"><div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 font-weight-bold text-warning">Nguyên liệu sắp hết</h6></div><div class="card-body"><h3 class="text-warning"><?= $lowStock ?> loại</h3><a href="ingredients.php" class="btn btn-sm btn-warning">Kiểm tra kho</a></div></div></div>
        <?php endif; ?>
    </div>
</div></div></div>
<?php require_once 'includes/footer.php'; require_once 'includes/scripts.php'; ?>