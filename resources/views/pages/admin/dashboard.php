
<?php
$role = $_SESSION['user']['role_id'] ?? 0;

$data = $data ?? [];

$totalOrders    = $data['totalOrders'] ?? 0;
$totalProducts  = $data['totalProducts'] ?? 0;
$totalCustomers = $data['totalCustomers'] ?? 0;
$totalRevenue   = $data['totalRevenue'] ?? 0;
$pendingOrders  = $data['pendingOrders'] ?? 0;

$today      = $compare['today'] ?? 0;
$yesterday  = $compare['yesterday'] ?? 0;

$percent = $yesterday > 0
    ? (($today - $yesterday) / $yesterday) * 100
    : 100;
?>

<style>

body{
    background:#f4f6fb;
}

/* =========================================================
   DASHBOARD
========================================================= */

.dashboard-page{
    padding:24px;
    width:100%;
    overflow:hidden;
}

.dashboard-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:15px;
    margin-bottom:25px;
    flex-wrap:wrap;
}

.dashboard-title{
    font-size:30px;
    font-weight:700;
    color:#1f2937;
}

.dashboard-sub{
    color:#6b7280;
    font-size:14px;
}

/* =========================================================
   CARDS
========================================================= */

.dashboard-card{
    border:none;
    border-radius:20px;
    overflow:hidden;
    transition:0.25s;
    background:#fff;
    box-shadow:0 4px 20px rgba(0,0,0,0.05);
}

.dashboard-card:hover{
    transform:translateY(-5px);
}

.dashboard-card .card-body{
    padding:24px;
}

.dashboard-icon{
    width:62px;
    height:62px;
    border-radius:18px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:24px;
}

.dashboard-label{
    font-size:13px;
    font-weight:600;
    color:#9ca3af;
    text-transform:uppercase;
    margin-bottom:8px;
}

.dashboard-value{
    font-size:30px;
    font-weight:700;
    color:#111827;
}

.dashboard-mini{
    font-size:13px;
    margin-top:6px;
}

/* =========================================================
   SECTION
========================================================= */

.dashboard-section{
    border:none;
    border-radius:20px;
    overflow:hidden;
    background:#fff;
    box-shadow:0 4px 20px rgba(0,0,0,0.05);
}

.dashboard-section .card-header{
    background:#fff;
    border-bottom:1px solid #eee;
    padding:20px 24px;
    font-weight:700;
    font-size:16px;
}

.dashboard-section .card-body{
    padding:24px;
}

.dashboard-alert{
    background:#f8faff;
    border-radius:16px;
    padding:20px;
}

/* =========================================================
   CHART
========================================================= */

.dashboard-chart{
    border:none;
    border-radius:20px;
    overflow:hidden;
    background:#fff;
    box-shadow:0 4px 20px rgba(0,0,0,0.05);
}

.dashboard-chart .card-header{
    background:#fff;
    border-bottom:1px solid #eee;
    padding:20px 24px;
}

.dashboard-chart .card-body{
    padding:24px;
}

.chart-wrapper{
    position:relative;
    height:350px;
    width:100%;
    overflow:hidden;
}

canvas{
    max-width:100% !important;
}

/* =========================================================
   PRODUCTS
========================================================= */

.dashboard-product-item{
    padding:15px 0;
    border-bottom:1px solid #eee;
}

.dashboard-product-item:last-child{
    border-bottom:none;
}

.dashboard-empty{
    text-align:center;
    color:#999;
    padding:30px 0;
}

/* =========================================================
   GRID FIX
========================================================= */

.row{
    margin-left:-10px;
    margin-right:-10px;
}

.row > div{
    padding-left:10px;
    padding-right:10px;
}

/* =========================================================
   TABLET
========================================================= */

@media(max-width:768px){

    .dashboard-page{
        padding:15px 10px;
    }

    .dashboard-header{
        align-items:flex-start;
    }

    .dashboard-title{
        font-size:24px;
    }

    .dashboard-value{
        font-size:24px;
    }

    .dashboard-card .card-body{
        padding:18px;
    }

    .dashboard-icon{
        width:52px;
        height:52px;
        font-size:18px;
    }

    .chart-wrapper{
        height:280px;
    }

    .dashboard-chart .card-header{
        flex-direction:column;
        align-items:flex-start !important;
        gap:10px;
    }
}

/* =========================================================
   MOBILE
========================================================= */

@media(max-width:576px){

    .dashboard-page{
        padding:10px 0;
    }

    .dashboard-title{
        font-size:22px;
    }

    .dashboard-sub{
        font-size:13px;
    }

    .dashboard-card{
        border-radius:16px;
    }

    .dashboard-section{
        border-radius:16px;
    }

    .dashboard-chart{
        border-radius:16px;
    }

    .dashboard-value{
        font-size:22px;
    }

    .dashboard-label{
        font-size:12px;
    }

    .dashboard-mini{
        font-size:12px;
    }

    .dashboard-card .card-body{
        padding:16px;
    }

    .dashboard-section .card-body{
        padding:18px;
    }

    .dashboard-chart .card-body{
        padding:15px;
    }

    .chart-wrapper{
        height:240px;
    }
}

</style>

<div class="dashboard-page">

    <!-- HEADER -->
    <div class="dashboard-header">

        <div>

            <div class="dashboard-title">
                Dashboard
            </div>

            <div class="dashboard-sub">
                Tổng quan hệ thống quản lý cửa hàng
            </div>

        </div>

        <a href="?url=dashboard&export=1"
           class="btn btn-success shadow-sm">

            <i class="fas fa-file-csv mr-1"></i>
            Xuất CSV

        </a>

    </div>

    <!-- STATS -->
    <div class="row">

        <!-- REVENUE -->
        <div class="col-xl-3 col-md-6 col-sm-6 mb-4">

            <div class="card dashboard-card">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <div class="dashboard-label">
                                Doanh thu
                            </div>

                            <div class="dashboard-value">
                                <?= number_format($totalRevenue) ?>đ
                            </div>

                            <div class="dashboard-mini <?= $percent >= 0 ? 'text-success' : 'text-danger' ?>">

                                <?= $percent >= 0 ? '▲' : '▼' ?>

                                <?= number_format(abs($percent),1) ?>%
                                so với hôm qua

                            </div>

                        </div>

                        <div class="dashboard-icon bg-primary text-white">
                            <i class="fas fa-dollar-sign"></i>
                        </div>

                    </div>

                </div>

            </div>

        </div>

        <!-- ORDERS -->
        <div class="col-xl-3 col-md-6 col-sm-6 mb-4">

            <div class="card dashboard-card">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <div class="dashboard-label">
                                Đơn hàng
                            </div>

                            <div class="dashboard-value">
                                <?= $totalOrders ?>
                            </div>

                            <div class="dashboard-mini text-muted">
                                Tổng đơn hàng
                            </div>

                        </div>

                        <div class="dashboard-icon bg-success text-white">
                            <i class="fas fa-shopping-cart"></i>
                        </div>

                    </div>

                </div>

            </div>

        </div>

        <!-- PRODUCTS -->
        <div class="col-xl-3 col-md-6 col-sm-6 mb-4">

            <div class="card dashboard-card">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <div class="dashboard-label">
                                Sản phẩm
                            </div>

                            <div class="dashboard-value">
                                <?= $totalProducts ?>
                            </div>

                            <div class="dashboard-mini text-muted">
                                Đang kinh doanh
                            </div>

                        </div>

                        <div class="dashboard-icon bg-info text-white">
                            <i class="fas fa-utensils"></i>
                        </div>

                    </div>

                </div>

            </div>

        </div>

        <!-- CUSTOMERS -->
        <div class="col-xl-3 col-md-6 col-sm-6 mb-4">

            <div class="card dashboard-card">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <div class="dashboard-label">
                                Khách hàng
                            </div>

                            <div class="dashboard-value">
                                <?= $totalCustomers ?>
                            </div>

                            <div class="dashboard-mini text-muted">
                                Người dùng hệ thống
                            </div>

                        </div>

                        <div class="dashboard-icon bg-warning text-white">
                            <i class="fas fa-users"></i>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <!-- PENDING -->
    <div class="row">

        <div class="col-lg-12 mb-4">

            <div class="card dashboard-section">

                <div class="card-header">
                    ⏳ Đơn hàng chờ xử lý
                </div>

                <div class="card-body">

                    <div class="dashboard-alert">

                        <h2 class="text-danger font-weight-bold mb-3">
                            <?= $pendingOrders ?> đơn
                        </h2>

                        <a href="admin.php?url=orders&status=pending"
                           class="btn btn-primary">

                            Xem đơn hàng

                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <!-- CHART -->
    <div class="card dashboard-chart mb-4">

        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">

            <div class="font-weight-bold">
                📈 Biểu đồ doanh thu
            </div>

            <div class="mt-2 mt-md-0">

                <a href="?url=dashboard&type=day"
                   class="btn btn-sm <?= ($_GET['type'] ?? 'day') == 'day'
                        ? 'btn-primary'
                        : 'btn-outline-primary' ?>">
                    Ngày
                </a>

                <a href="?url=dashboard&type=week"
                   class="btn btn-sm <?= ($_GET['type'] ?? '') == 'week'
                        ? 'btn-primary'
                        : 'btn-outline-primary' ?>">
                    Tuần
                </a>

                <a href="?url=dashboard&type=month"
                   class="btn btn-sm <?= ($_GET['type'] ?? '') == 'month'
                        ? 'btn-primary'
                        : 'btn-outline-primary' ?>">
                    Tháng
                </a>

            </div>

        </div>

        <div class="card-body">

            <div class="chart-wrapper">
                <canvas id="revenueChart"></canvas>
            </div>

        </div>

    </div>

    <!-- TOP PRODUCTS -->
    <div class="card dashboard-section mb-4">

        <div class="card-header">
            🔥 Sản phẩm bán chạy
        </div>

        <div class="card-body">

            <?php if (!empty($topProducts)): ?>

                <?php foreach ($topProducts as $p): ?>

                    <div class="dashboard-product-item d-flex justify-content-between align-items-center">

                        <div>

                            <strong>
                                <?= htmlspecialchars($p['name']) ?>
                            </strong>

                        </div>

                        <span class="badge badge-success px-3 py-2">

                            <?= (int)$p['total_sold'] ?> đã bán

                        </span>

                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <div class="dashboard-empty">
                    Chưa có dữ liệu sản phẩm
                </div>

            <?php endif; ?>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

const chartLabels =
<?= json_encode(array_column($chartData ?? [], 'label')) ?>;

const chartRevenue =
<?= json_encode(array_column($chartData ?? [], 'revenue')) ?>;

const ctx =
document.getElementById('revenueChart').getContext('2d');

const gradient =
ctx.createLinearGradient(0, 0, 0, 350);

gradient.addColorStop(0, 'rgba(78,115,223,0.35)');
gradient.addColorStop(1, 'rgba(78,115,223,0)');

new Chart(ctx, {

    type: 'line',

    data: {

        labels: chartLabels,

        datasets: [{

            label: 'Doanh thu',

            data: chartRevenue,

            borderColor: '#4e73df',

            backgroundColor: gradient,

            fill: true,

            tension: 0.5,

            borderWidth: 4,

            pointRadius: 5,

            pointHoverRadius: 8,

            pointBackgroundColor: '#4e73df',

            pointBorderColor: '#fff',

            pointBorderWidth: 2

        }]
    },

    options: {

        responsive: true,

        maintainAspectRatio: false,

        plugins: {

            legend: {
                display: true
            }
        },

        scales: {

            y: {

                beginAtZero: true,

                ticks: {

                    callback: function(value) {
                        return value.toLocaleString() + 'đ';
                    }
                }
            }
        }
    }
});

</script>
