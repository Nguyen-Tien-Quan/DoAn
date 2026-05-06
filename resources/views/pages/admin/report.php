<?php
$report_type = $report_type ?? 'daily';
$start_date = $start_date ?? date('Y-m-01');
$end_date = $end_date ?? date('Y-m-d');
$month = $month ?? date('m');
$year = $year ?? date('Y');
$revenue = $revenue ?? 0;
$orders = $orders ?? [];
?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Báo cáo doanh thu</h1>
    </div>

    <!-- Form lọc -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <form method="GET" class="form-inline">
                <input type="hidden" name="url" value="report">
                <div class="form-group mr-2">
                    <label class="mr-2">Loại báo cáo:</label>
                    <select name="type" class="form-control" id="reportType">
                        <option value="daily" <?= $report_type == 'daily' ? 'selected' : '' ?>>Theo ngày</option>
                        <option value="monthly" <?= $report_type == 'monthly' ? 'selected' : '' ?>>Theo tháng</option>
                    </select>
                </div>
                <div id="dailyRange" style="display: <?= $report_type == 'daily' ? 'inline-block' : 'none' ?>">
                    <div class="form-group mr-2">
                        <label class="mr-2">Từ ngày:</label>
                        <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>">
                    </div>
                    <div class="form-group mr-2">
                        <label class="mr-2">Đến ngày:</label>
                        <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>">
                    </div>
                </div>
                <div id="monthlyRange" style="display: <?= $report_type == 'monthly' ? 'inline-block' : 'none' ?>">
                    <div class="form-group mr-2">
                        <label class="mr-2">Tháng:</label>
                        <select name="month" class="form-control">
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?= str_pad($m,2,'0',STR_PAD_LEFT) ?>" <?= (isset($month) && $month == str_pad($m,2,'0',STR_PAD_LEFT)) ? 'selected' : '' ?>><?= $m ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group mr-2">
                        <label class="mr-2">Năm:</label>
                        <select name="year" class="form-control">
                            <?php for ($y = date('Y')-2; $y <= date('Y'); $y++): ?>
                                <option value="<?= $y ?>" <?= (isset($year) && $year == $y) ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Xem báo cáo</button>
                <a href="?url=report&export=1&type=<?= urlencode($report_type) ?>&start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>&month=<?= urlencode($month ?? '') ?>&year=<?= urlencode($year ?? '') ?>" class="btn btn-success ml-2">Xuất CSV</a>
            </form>
        </div>
    </div>

    <!-- Tổng doanh thu -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Tổng doanh thu</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($revenue) ?>đ</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Số đơn hàng</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= count($orders) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Biểu đồ doanh thu (nếu có dữ liệu) -->
    <?php if (!empty($orders)): ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Biểu đồ doanh thu theo ngày</h6>
        </div>
        <div class="card-body">
            <canvas id="revenueChart" width="100%" height="30"></canvas>
        </div>
    </div>
    <?php endif; ?>

    <!-- Danh sách đơn hàng -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Chi tiết đơn hàng từ <?= htmlspecialchars($start_date) ?> đến <?= htmlspecialchars($end_date) ?></h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Khách hàng</th>
                            <th>Tổng tiền</th>
                            <th>Thanh toán</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($orders) == 0): ?>
                            <tr><td colspan="6" class="text-center">Không có đơn hàng nào trong khoảng thời gian này</td></tr>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?= htmlspecialchars($order['order_code']) ?></td>
                                <td><?= htmlspecialchars($order['full_name'] ?? 'Khách lẻ') ?></td>
                                <td><?= number_format($order['final_amount']) ?>đ</td>
                                <td>
                                    <?php if ($order['payment_status'] == 'paid'): ?>
                                        <span class="badge badge-success">Đã thanh toán</span>
                                    <?php elseif ($order['payment_status'] == 'pending'): ?>
                                        <span class="badge badge-warning">Chờ thanh toán</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary"><?= $order['payment_status'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $statusLabels = [
                                        'pending' => ['Chờ xác nhận', 'secondary'],
                                        'confirmed' => ['Đã xác nhận', 'info'],
                                        'preparing' => ['Đang chuẩn bị', 'primary'],
                                        'ready_for_delivery' => ['Sẵn sàng giao', 'info'],
                                        'delivering' => ['Đang giao', 'warning'],
                                        'completed' => ['Hoàn thành', 'success'],
                                        'cancelled' => ['Đã hủy', 'danger']
                                    ];
                                    $status = $order['status'];
                                    $label = $statusLabels[$status] ?? [$status, 'light'];
                                    ?>
                                    <span class="badge badge-<?= $label[1] ?>"><?= $label[0] ?></span>
                                </td>
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

<script>
// Toggle hiển thị form theo loại báo cáo
document.getElementById('reportType').addEventListener('change', function() {
    if (this.value == 'daily') {
        document.getElementById('dailyRange').style.display = 'inline-block';
        document.getElementById('monthlyRange').style.display = 'none';
    } else {
        document.getElementById('dailyRange').style.display = 'none';
        document.getElementById('monthlyRange').style.display = 'inline-block';
    }
});

<?php if (!empty($orders)): ?>
// Vẽ biểu đồ doanh thu (Chart.js)
document.addEventListener("DOMContentLoaded", function() {
    var ctx = document.getElementById('revenueChart').getContext('2d');

    // Nhóm doanh thu theo ngày từ dữ liệu orders
    var orders = <?= json_encode($orders) ?>;
    var revenueByDate = {};
    orders.forEach(function(order) {
        var date = order.created_at.split(' ')[0]; // Lấy phần ngày YYYY-MM-DD
        if (!revenueByDate[date]) revenueByDate[date] = 0;
        revenueByDate[date] += parseFloat(order.final_amount);
    });

    var sortedDates = Object.keys(revenueByDate).sort();
    var revenues = sortedDates.map(d => revenueByDate[d]);
    var labels = sortedDates.map(d => {
        var parts = d.split('-');
        return parts[2] + '/' + parts[1]; // dd/mm
    });

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Doanh thu (đ)',
                data: revenues,
                backgroundColor: 'rgba(78, 115, 223, 0.05)',
                borderColor: 'rgba(78, 115, 223, 1)',
                borderWidth: 2,
                pointRadius: 3,
                pointBackgroundColor: 'rgba(78, 115, 223, 1)',
                pointBorderColor: '#fff',
                pointHoverRadius: 5,
                tension: 0.1,
                fill: true
            }]
        },
        options: {
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('vi-VN').format(value) + 'đ';
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Doanh thu: ' + new Intl.NumberFormat('vi-VN').format(context.raw) + 'đ';
                        }
                    }
                }
            }
        }
    });
});
<?php endif; ?>
</script>
