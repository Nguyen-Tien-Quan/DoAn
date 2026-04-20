<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Báo cáo doanh thu</h1>
    </div>
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
                                <option value="<?= str_pad($m,2,'0',STR_PAD_LEFT) ?>" <?= (isset($_GET['month']) && $_GET['month'] == str_pad($m,2,'0',STR_PAD_LEFT)) ? 'selected' : '' ?>><?= $m ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group mr-2">
                        <label class="mr-2">Năm:</label>
                        <select name="year" class="form-control">
                            <?php for ($y = date('Y')-2; $y <= date('Y'); $y++): ?>
                                <option value="<?= $y ?>" <?= (isset($_GET['year']) && $_GET['year'] == $y) ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Xem báo cáo</button>
                <a href="?url=report&export=1&<?= http_build_query(array_merge($_GET, ['export'=>1])) ?>" class="btn btn-success ml-2">Xuất CSV</a>
            </form>
        </div>
        <div class="card-body">
            <h4>Tổng doanh thu từ <?= htmlspecialchars($start_date) ?> đến <?= htmlspecialchars($end_date) ?>: <strong class="text-danger"><?= number_format($revenue) ?>đ</strong></h4>
            <div class="table-responsive mt-3">
                <table class="table table-bordered">
                    <thead>
                        <tr><th>Mã đơn</th><th>Khách hàng</th><th>Tổng tiền</th><th>Thanh toán</th><th>Ngày tạo</th></tr>
                    </thead>
                    <tbody>
                        <?php if (count($orders) == 0): ?>
                            <tr><td colspan="5" class="text-center">Không có đơn hàng nào trong khoảng thời gian này</td></tr>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?= htmlspecialchars($order['order_code']) ?></td>
                                <td><?= htmlspecialchars($order['full_name'] ?? 'Khách lẻ') ?></td>
                                <td><?= number_format($order['final_amount']) ?>đ</td>
                                <td><?= $order['payment_status'] ?></td>
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
    document.getElementById('reportType').addEventListener('change', function() {
        if (this.value == 'daily') {
            document.getElementById('dailyRange').style.display = 'inline-block';
            document.getElementById('monthlyRange').style.display = 'none';
        } else {
            document.getElementById('dailyRange').style.display = 'none';
            document.getElementById('monthlyRange').style.display = 'inline-block';
        }
    });
</script>
