<?php
$role = $_SESSION['user']['role_id'];

$data = $data ?? [];
$totalOrders = $data['totalOrders'] ?? 0;
$totalProducts = $data['totalProducts'] ?? 0;
$totalCustomers = $data['totalCustomers'] ?? 0;
$totalRevenue = $data['totalRevenue'] ?? 0;
$pendingOrders = $data['pendingOrders'] ?? 0;
$lowStock = $data['lowStock'] ?? 0;

$today = $compare['today'] ?? 0;
$yesterday = $compare['yesterday'] ?? 0;
?>

<div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
        <?php require_once 'includes/topbar.php'; ?>
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
            <a href="?url=dashboard&export=1&<?= http_build_query(array_merge($_GET, ['export'=>1])) ?>" class="btn btn-success ml-2">Xuất CSV</a>
        </div>
        <div class="row">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body"><div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Doanh thu</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($totalRevenue) ?>đ</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
            <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Đơn hàng</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $totalOrders ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Sản phẩm</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalProducts ?></div>
                            </div><div class="col-auto"><i class="fas fa-utensils fa-2x text-gray-300"></i>
                        </div></div></div></div></div>
            <div class="col-xl-3 col-md-6 mb-4">

                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body"><div class="row no-gutters align-items-center">
                        <div class="col mr-2"><div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Khách hàng</div><div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalCustomers ?></div></div><div class="col-auto"><i class="fas fa-users fa-2x text-gray-300"></i></div></div></div></div></div>
        </div>
        <div class="row">
            <div class="col-lg-6 mb-4"><div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Đơn hàng chờ xử lý</h6>
                </div>
                <div class="card-body">
                    <h3 class="text-danger"><?= $pendingOrders ?> đơn</h3>
                    <a href="orders.php?status=pending" class="btn btn-sm btn-primary">Xem chi tiết</a>
                </div>
            </div>
        </div>
            <?php if ($role == 'admin'): ?>
            <div class="col-lg-6 mb-4"><div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">Nguyên liệu sắp hết</h6>
                </div>
                <div class="card-body">
                    <h3 class="text-warning"><?= $lowStock ?> loại</h3>
                    <a href="ingredients.php" class="btn btn-sm btn-warning">Kiểm tra kho</a>
                </div>
            </div>
        </div>
            <?php endif; ?>
        </div>
    </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold text-success">🔥 Sản phẩm bán chạy</h6>
    </div>
    <div class="card-body">
        <?php foreach ($topProducts as $p): ?>
            <div class="d-flex justify-content-between border-bottom py-2">
                <span><?= $p['name'] ?></span>
                <span class="badge badge-success"><?= $p['total_sold'] ?> đã bán</span>
            </div>
        <?php endforeach; ?>
    </div>
</div>


<div class="card shadow mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            📊 Biểu đồ doanh thu
        </h6>

        <!-- FILTER -->
        <div>
            <a href="?url=dashboard&type=day" class="btn btn-sm <?= ($_GET['type'] ?? 'day')=='day' ? 'btn-primary' : 'btn-outline-primary' ?>">Ngày</a>
            <a href="?url=dashboard&type=week" class="btn btn-sm <?= ($_GET['type'] ?? '')=='week' ? 'btn-primary' : 'btn-outline-primary' ?>">Tuần</a>
            <a href="?url=dashboard&type=month" class="btn btn-sm <?= ($_GET['type'] ?? '')=='month' ? 'btn-primary' : 'btn-outline-primary' ?>">Tháng</a>
        </div>
    </div>

    <div class="card-body">
        <canvas id="revenueChart" height="100"></canvas>
    </div>
</div>
<?php
$today = $compare['today'];
$yesterday = $compare['yesterday'];

$percent = $yesterday > 0
    ? (($today - $yesterday) / $yesterday) * 100
    : 100;
?>

<div class="card border-left-success shadow mb-4">
    <div class="card-body">
        <div class="text-xs font-weight-bold text-success mb-1">
            Doanh thu hôm nay
        </div>

        <div class="h5 font-weight-bold">
            <?= number_format($today) ?>đ
        </div>

        <small class="<?= $percent >= 0 ? 'text-success' : 'text-danger' ?>">
            <?= $percent >= 0 ? '▲' : '▼' ?>
            <?= number_format(abs($percent),1) ?>% so với hôm qua
        </small>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const ctx = document.getElementById('revenueChart').getContext('2d');

const gradient = ctx.createLinearGradient(0, 0, 0, 300);
gradient.addColorStop(0, 'rgba(78, 115, 223, 0.5)');
gradient.addColorStop(1, 'rgba(78, 115, 223, 0)');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($chartData ?? [], 'label')) ?>,
        datasets: [{
            label: 'Doanh thu',
            data: <?= json_encode(array_column($chartData ?? [], 'revenue')) ?>,
            borderColor: '#4e73df',
            backgroundColor: gradient,
            fill: true,
            tension: 0.4,
            pointRadius: 4,
            pointBackgroundColor: '#4e73df'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: true }
        },
        scales: {
            y: {
                ticks: {
                    callback: value => value.toLocaleString() + 'đ'
                }
            }
        }
    }
});
</script>
