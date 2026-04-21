<?php
require_once __DIR__ . '/../../../config/database.php';
function getFlash($key) {
    if (!empty($_SESSION[$key])) {
        $msg = $_SESSION[$key];
        unset($_SESSION[$key]);
        return $msg;
    }
    return '';
}

function getDashboardData() {
    $pdo = getDB();

    return [
        'totalOrders' => $pdo->query("
            SELECT COUNT(*) FROM orders
        ")->fetchColumn(),

        'totalProducts' => $pdo->query("
            SELECT COUNT(*) FROM products WHERE status = 1
        ")->fetchColumn(),

        'totalCustomers' => $pdo->query("
            SELECT COUNT(*) FROM users WHERE role_id = 3
        ")->fetchColumn(),

        // ✅ FIX: dùng chung 1 field total_amount
        'totalRevenue' => $pdo->query("
            SELECT COALESCE(SUM(total_amount), 0)
            FROM orders
            WHERE status = 'completed'
        ")->fetchColumn(),

        'pendingOrders' => $pdo->query("
            SELECT COUNT(*) FROM orders WHERE status = 'pending'
        ")->fetchColumn(),

        'lowStock' => $pdo->query("
            SELECT COUNT(*)
            FROM product_variants
            WHERE stock_quantity < 10 AND stock_quantity > 0
        ")->fetchColumn()
    ];
}

function ensureVoucherTable() {
    $conn = getDB();
    $conn->exec("CREATE TABLE IF NOT EXISTS vouchers (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) UNIQUE NOT NULL,
        name VARCHAR(100) NOT NULL,
        discount_type ENUM('percent','fixed') NOT NULL,
        discount_value DECIMAL(12,2) NOT NULL,
        min_order_amount DECIMAL(12,2) DEFAULT 0,
        max_discount_amount DECIMAL(12,2) DEFAULT 0,
        start_date DATETIME,
        end_date DATETIME,
        usage_limit INT DEFAULT 0,
        used_count INT DEFAULT 0,
        status TINYINT DEFAULT 1,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL
    )");
}

function getVouchers($page = 1, $limit = 15, $filters = []) {
    $conn = getDB();
    $offset = ($page - 1) * $limit;
    $search = $filters['search'] ?? '';
    $status = isset($filters['status']) ? (int)$filters['status'] : -1;
    $discount_type = $filters['discount_type'] ?? '';
    $where = " WHERE 1=1 ";
    $params = [];
    if (!empty($search)) {
        $where .= " AND (code LIKE ? OR name LIKE ?) ";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    if ($status != -1) {
        $where .= " AND status = ? ";
        $params[] = $status;
    }
    if (!empty($discount_type)) {
        $where .= " AND discount_type = ? ";
        $params[] = $discount_type;
    }
    $countSql = "SELECT COUNT(*) FROM vouchers $where";
    $stmt = $conn->prepare($countSql);
    $stmt->execute($params);
    $total = $stmt->fetchColumn();
    $sql = "SELECT * FROM vouchers $where ORDER BY id ASC LIMIT $limit OFFSET $offset";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $vouchers = $stmt->fetchAll();
    return [
        'data' => $vouchers,
        'total' => $total,
        'totalPages' => ceil($total / $limit)
    ];
}

function handleAddVoucher() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: admin.php?url=vouchers');
        exit;
    }
    if (session_status() === PHP_SESSION_NONE) session_start();
    $conn = getDB();
    $code = trim($_POST['code'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $discount_type = $_POST['discount_type'] ?? 'percent';
    $discount_value = (float)($_POST['discount_value'] ?? 0);
    $min_order_amount = (float)($_POST['min_order_amount'] ?? 0);
    $max_discount_amount = (float)($_POST['max_discount_amount'] ?? 0);
    $start_date = !empty($_POST['start_date']) ? date('Y-m-d H:i:s', strtotime($_POST['start_date'])) : null;
    $end_date = !empty($_POST['end_date']) ? date('Y-m-d H:i:s', strtotime($_POST['end_date'])) : null;
    $usage_limit = (int)($_POST['usage_limit'] ?? 0);
    $status = (int)($_POST['status'] ?? 1);
    $check = $conn->prepare("SELECT id FROM vouchers WHERE code = ?");
    $check->execute([$code]);
    if ($check->fetch()) {
        $_SESSION['error'] = "Mã khuyến mãi đã tồn tại.";
        header('Location: admin.php?url=vouchers');
        exit;
    }
    $sql = "INSERT INTO vouchers (code, name, discount_type, discount_value, min_order_amount, max_discount_amount, start_date, end_date, usage_limit, used_count, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?)";
    $stmt = $conn->prepare($sql);
    $success = $stmt->execute([$code, $name, $discount_type, $discount_value, $min_order_amount, $max_discount_amount, $start_date, $end_date, $usage_limit, $status]);
    $_SESSION['success'] = $success ? "Thêm mã thành công." : "Thêm thất bại.";
    header('Location: admin.php?url=vouchers');
    exit;
}

function handleDeleteVoucher() {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id > 0) {
        $conn = getDB();
        $stmt = $conn->prepare("UPDATE vouchers SET status = 0 WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['success'] = "Đã vô hiệu mã khuyến mãi.";
    }
    header('Location: admin.php?url=vouchers');
    exit;
}

function handleRestoreVoucher() {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id > 0) {
        $conn = getDB();
        $stmt = $conn->prepare("UPDATE vouchers SET status = 1 WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['success'] = "Đã kích hoạt lại mã khuyến mãi.";
    }
    header('Location: admin.php?url=vouchers');
    exit;
}

/**
 * 📈 Lấy dữ liệu biểu đồ doanh thu
 * @param string $type (day | month)
 */
function getRevenueChart($type = 'day') {
    $conn = getDB();

    switch ($type) {

        case 'week':
            $sql = "
                SELECT YEARWEEK(created_at, 1) as label,
                       SUM(final_amount) as revenue
                FROM orders
                WHERE status = 'completed'
                GROUP BY YEARWEEK(created_at, 1)
                ORDER BY label ASC
                LIMIT 6
            ";
        break;

        case 'month':
            $sql = "
                SELECT DATE_FORMAT(created_at, '%Y-%m') as label,
                       SUM(final_amount) as revenue
                FROM orders
                WHERE status = 'completed'
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY label ASC
                LIMIT 6
            ";
        break;

        default: // day
            $sql = "
                SELECT DATE(created_at) as label,
                       SUM(final_amount) as revenue
                FROM orders
                WHERE status = 'completed'
                GROUP BY DATE(created_at)
                ORDER BY label ASC
                LIMIT 7
            ";
    }

    return $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

function getRevenueCompare() {
    $conn = getDB();

    $sql = "
        SELECT
        COALESCE(SUM(CASE WHEN DATE(created_at)=CURDATE() THEN final_amount END),0) as today,
        COALESCE(SUM(CASE WHEN DATE(created_at)=CURDATE()-1 THEN final_amount END),0) as yesterday
        FROM orders
        WHERE status = 'completed'
    ";

    return $conn->query($sql)->fetch(PDO::FETCH_ASSOC);
}

function getTopProducts($limit = 5) {
    $conn = getDB();

    return $conn->query("
        SELECT
            p.id,
            p.name,
            SUM(oi.quantity) as total_sold
        FROM order_items oi
        JOIN products p ON p.id = oi.product_id
        GROUP BY p.id
        ORDER BY total_sold DESC
        LIMIT $limit
    ")->fetchAll(PDO::FETCH_ASSOC);
}

?>
