<?php
// Đảm bảo file database.php đã được include trước (thường có ở admin.php hoặc db.php)
if (!function_exists('getDB')) {
    require_once __DIR__ . '/../../../../../config/database.php';
}

// Đếm số bản ghi
function countRecords($table, $where = '') {
    $pdo = getDB();
    $sql = "SELECT COUNT(*) FROM $table $where";
    return (int) $pdo->query($sql)->fetchColumn();
}

// Tổng doanh thu
function getTotalRevenue() {
    $pdo = getDB();
    $sql = "SELECT COALESCE(SUM(final_amount), 0) FROM orders WHERE status = 'completed'";
    return (float) $pdo->query($sql)->fetchColumn();
}

// Doanh thu hôm nay
function getTodayRevenue() {
    $pdo = getDB();
    $today = date('Y-m-d');
    $sql = "SELECT COALESCE(SUM(final_amount), 0) FROM orders WHERE status = 'completed' AND DATE(created_at) = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$today]);
    return (float) $stmt->fetchColumn();
}

// Số đơn hàng hoàn thành hôm nay
function getTodayCompletedOrdersCount() {
    $pdo = getDB();
    $today = date('Y-m-d');
    $sql = "SELECT COUNT(*) FROM orders WHERE status = 'completed' AND DATE(created_at) = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$today]);
    return (int) $stmt->fetchColumn();
}

// Doanh thu theo khoảng ngày
function getRevenueByDateRange($start, $end) {
    $pdo = getDB();
    $sql = "SELECT COALESCE(SUM(final_amount), 0) FROM orders WHERE status = 'completed' AND DATE(created_at) BETWEEN ? AND ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$start, $end]);
    return (float) $stmt->fetchColumn();
}

// Danh sách đơn hàng theo khoảng ngày
function getOrdersByDateRange($start, $end) {
    $pdo = getDB();
    $sql = "SELECT o.*, c.full_name
            FROM orders o
            LEFT JOIN customers c ON o.customer_id = c.id
            WHERE o.status = 'completed' AND DATE(o.created_at) BETWEEN ? AND ?
            ORDER BY o.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$start, $end]);
    return $stmt->fetchAll();
}

// Đơn hàng chờ xử lý
function getPendingOrdersCount() {
    $pdo = getDB();
    $sql = "SELECT COUNT(*) FROM orders WHERE status = 'pending'";
    return (int) $pdo->query($sql)->fetchColumn();
}

// Nguyên liệu sắp hết
function getLowStockIngredientsCount() {
    $pdo = getDB();
    try {
        $result = $pdo->query("SHOW TABLES LIKE 'ingredients'");
        if ($result->rowCount() == 0) return 0;
        $stmt = $pdo->query("SHOW COLUMNS FROM ingredients LIKE 'min_quantity'");
        if ($stmt->rowCount() > 0) {
            return countRecords('ingredients', 'WHERE stock_quantity <= min_quantity');
        } else {
            return countRecords('ingredients', 'WHERE stock_quantity <= 5');
        }
    } catch (PDOException $e) {
        return 0;
    }
}

// ========== BÁO CÁO ==========
function getReportData() {
    $pdo = getDB();
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
    $report_type = isset($_GET['type']) ? $_GET['type'] : 'daily';

    if ($report_type == 'monthly') {
        $month = isset($_GET['month']) ? $_GET['month'] : date('m');
        $year = isset($_GET['year']) ? $_GET['year'] : date('Y');
        $start_date = "$year-$month-01";
        $end_date = date('Y-m-t', strtotime($start_date));
    }

    $revenue = getRevenueByDateRange($start_date, $end_date);
    $orders = getOrdersByDateRange($start_date, $end_date);

    return [
        'start_date' => $start_date,
        'end_date' => $end_date,
        'report_type' => $report_type,
        'revenue' => $revenue,
        'orders' => $orders,
        'month' => $month ?? date('m'),
        'year' => $year ?? date('Y')
    ];
}

function exportReportCSV() {
    $pdo = getDB();
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
    $report_type = isset($_GET['type']) ? $_GET['type'] : 'daily';

    if ($report_type == 'monthly') {
        $month = isset($_GET['month']) ? $_GET['month'] : date('m');
        $year = isset($_GET['year']) ? $_GET['year'] : date('Y');
        $start_date = "$year-$month-01";
        $end_date = date('Y-m-t', strtotime($start_date));
    }

    $orders = getOrdersByDateRange($start_date, $end_date);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="report_' . $start_date . '_to_' . $end_date . '.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Mã đơn', 'Khách hàng', 'Tổng tiền', 'Trạng thái thanh toán', 'Ngày tạo']);
    foreach ($orders as $row) {
        fputcsv($output, [
            $row['order_code'],
            $row['full_name'] ?? 'Khách lẻ',
            number_format($row['final_amount']),
            $row['payment_status'],
            $row['created_at']
        ]);
    }
    fclose($output);
    exit;
}
?>
