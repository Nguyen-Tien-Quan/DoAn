<?php
$report_type = $report_type ?? 'daily';
$start_date  = $start_date ?? date('Y-m-01');
$end_date    = $end_date ?? date('Y-m-d');
$month       = $month ?? date('m');
$year        = $year ?? date('Y');

$revenue = $revenue ?? 0;
$orders  = $orders ?? [];

$total_orders = count($orders);
?>

<div class="container-fluid">

    <!-- HEADER -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Báo cáo doanh thu</h1>
    </div>

    <!-- FILTER -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <form method="GET" class="form-inline">
                <input type="hidden" name="url" value="report">

                <div class="form-group mr-2">
                    <label class="mr-2">Loại:</label>
                    <select name="type" id="reportType" class="form-control">
                        <option value="daily" <?= $report_type == 'daily' ? 'selected' : '' ?>>Theo ngày</option>
                        <option value="monthly" <?= $report_type == 'monthly' ? 'selected' : '' ?>>Theo tháng</option>
                    </select>
                </div>

                <!-- DAILY -->
                <div id="dailyRange" style="display: <?= $report_type == 'daily' ? 'inline-block' : 'none' ?>">
                    <input type="date" name="start_date" class="form-control mr-2" value="<?= htmlspecialchars($start_date) ?>">
                    <input type="date" name="end_date" class="form-control mr-2" value="<?= htmlspecialchars($end_date) ?>">
                </div>

                <!-- MONTHLY -->
                <div id="monthlyRange" style="display: <?= $report_type == 'monthly' ? 'inline-block' : 'none' ?>">
                    <select name="month" class="form-control mr-2">
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>" <?= $month == str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : '' ?>>
                                <?= $m ?>
                            </option>
                        <?php endfor; ?>
                    </select>

                    <select name="year" class="form-control mr-2">
                        <?php for ($y = date('Y') - 2; $y <= date('Y'); $y++): ?>
                            <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>>
                                <?= $y ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <button class="btn btn-primary">Xem</button>
            </form>
        </div>
    </div>

    <!-- STATS -->
    <div class="row">

        <!-- REVENUE -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                        Tổng doanh thu
                    </div>
                    <div class="h5 font-weight-bold text-gray-800">
                        <?= number_format($revenue) ?>đ
                    </div>
                </div>
            </div>
        </div>

        <!-- ORDERS -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                        Số đơn hàng
                    </div>
                    <div class="h5 font-weight-bold text-gray-800">
                        <?= $total_orders ?>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- CHART -->
    <?php if (!empty($orders)): ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Biểu đồ doanh thu & đơn hàng theo ngày
            </h6>
        </div>

        <div class="card-body" style="height: 350px;">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>
    <?php endif; ?>

    <!-- TABLE -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Danh sách đơn hàng
            </h6>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Khách hàng</th>
                            <th>Tổng tiền</th>
                            <th>Thanh toán</th>
                            <th>Trạng thái</th>
                            <th>Ngày</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="6" class="text-center">Không có dữ liệu</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><?= htmlspecialchars($order['order_code']) ?></td>
                                    <td><?= htmlspecialchars($order['full_name'] ?? 'Khách') ?></td>
                                    <td><?= number_format($order['final_amount']) ?>đ</td>
                                    <td><?= $order['payment_status'] ?></td>
                                    <td><?= $order['status'] ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>

                </table>
            </div>
        </div>
    </div>
</div>

<!-- SCRIPT -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// toggle filter
document.getElementById('reportType').addEventListener('change', function () {
    document.getElementById('dailyRange').style.display =
        this.value === 'daily' ? 'inline-block' : 'none';

    document.getElementById('monthlyRange').style.display =
        this.value === 'monthly' ? 'inline-block' : 'none';
});

<?php if (!empty($orders)): ?>
document.addEventListener("DOMContentLoaded", function () {

    const ctx = document.getElementById('revenueChart');
    if (!ctx) return;

    const orders = <?= json_encode($orders) ?>;

    let revenueByDate = {};
    let orderCountByDate = {};

    orders.forEach(o => {
        const date = (o.created_at || '').split(' ')[0];
        if (!date) return;

        revenueByDate[date] = (revenueByDate[date] || 0) + Number(o.final_amount || 0);
        orderCountByDate[date] = (orderCountByDate[date] || 0) + 1;
    });

    const dates = Object.keys(revenueByDate).sort();

    const labels = dates.map(d => {
        const p = d.split('-');
        return `${p[2]}/${p[1]}`;
    });

    const revenueData = dates.map(d => revenueByDate[d]);
    const orderData   = dates.map(d => orderCountByDate[d]);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Doanh thu (đ)',
                    data: revenueData,
                    borderColor: 'blue',
                    tension: 0.3
                },
                {
                    label: 'Số đơn hàng',
                    data: orderData,
                    borderColor: 'green',
                    tension: 0.3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

});
<?php endif; ?>
</script>
