<?php
require_once __DIR__ . '/../../../config/database.php';


function getDashboardData() {
    $pdo = getDB();

    return [
        'totalOrders'    => $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
        'totalProducts'  => $pdo->query("SELECT COUNT(*) FROM products WHERE status = 1")->fetchColumn(),
        'totalCustomers' => $pdo->query("SELECT COUNT(*) FROM users WHERE role_id = 3")->fetchColumn(), // chỉ đếm khách hàng
        'totalRevenue'   => $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status = 'completed'")->fetchColumn(),
        'pendingOrders'  => $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn(),
        'lowStock'       => $pdo->query("SELECT COUNT(*) FROM product_variants WHERE stock_quantity < 10 AND stock_quantity > 0")->fetchColumn()
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

    // Đếm tổng
    $countSql = "SELECT COUNT(*) FROM vouchers $where";
    $stmt = $conn->prepare($countSql);
    $stmt->execute($params);
    $total = $stmt->fetchColumn();

    // Lấy dữ liệu
    $sql = "SELECT * FROM vouchers $where ORDER BY id DESC LIMIT $limit OFFSET $offset";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $vouchers = $stmt->fetchAll();

    return [
        'data' => $vouchers,
        'total' => $total,
        'totalPages' => ceil($total / $limit)
    ];
}

/**
 * Thêm voucher mới
 */
function handleAddVoucher() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: admin.php?url=vouchers');
        exit;
    }
    $conn = getDB();
    $code = trim($_POST['code'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $discount_type = $_POST['discount_type'] ?? 'percent';
    $discount_value = (float)($_POST['discount_value'] ?? 0);
    $min_order_amount = (float)($_POST['min_order_amount'] ?? 0);
    $max_discount_amount = (float)($_POST['max_discount_amount'] ?? 0);
    $start_date = $_POST['start_date'] ?? null;
    $end_date = $_POST['end_date'] ?? null;
    $usage_limit = (int)($_POST['usage_limit'] ?? 0);
    $status = (int)($_POST['status'] ?? 1);

    // Kiểm tra trùng mã
    $check = $conn->prepare("SELECT id FROM vouchers WHERE code = ?");
    $check->execute([$code]);
    if ($check->fetch()) {
        $_SESSION['error'] = "Mã khuyến mãi đã tồn tại.";
        header('Location: admin.php?url=vouchers');
        exit;
    }

    $sql = "INSERT INTO vouchers (code, name, discount_type, discount_value, min_order_amount, max_discount_amount, start_date, end_date, usage_limit, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $success = $stmt->execute([$code, $name, $discount_type, $discount_value, $min_order_amount, $max_discount_amount, $start_date, $end_date, $usage_limit, $status]);

    $_SESSION['success'] = $success ? "Thêm mã thành công." : "Thêm thất bại.";
    header('Location: admin.php?url=vouchers');
    exit;
}

/**
 * Xóa (vô hiệu hóa) voucher
 */
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

/**
 * Khôi phục voucher
 */
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


function ensureToppingTable() {
    $conn = getDB();
    try { $conn->exec("ALTER TABLE toppings ADD COLUMN status TINYINT DEFAULT 1"); } catch(PDOException $e) {}
}

function getAllToppings() {
    $conn = getDB();
    return $conn->query("SELECT * FROM toppings ORDER BY id DESC")->fetchAll();
}

function handleAddTopping() {
    if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['action']) || $_POST['action'] != 'add') return;
    $conn = getDB();
    $name = trim($_POST['name']);
    $price = (float)$_POST['price'];
    $status = (int)$_POST['status'];
    if (!empty($name) && $price > 0) {
        $stmt = $conn->prepare("INSERT INTO toppings (name, price, status) VALUES (?,?,?)");
        $stmt->execute([$name, $price, $status]);
        $_SESSION['success'] = "Thêm topping thành công";
    } else {
        $_SESSION['error'] = "Vui lòng nhập đầy đủ thông tin";
    }
    header("Location: admin.php?url=toppings");
    exit;
}

function handleUpdateTopping() {
    if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['action']) || $_POST['action'] != 'edit') return;
    $conn = getDB();
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $price = (float)$_POST['price'];
    $status = (int)$_POST['status'];
    if ($id > 0 && !empty($name) && $price > 0) {
        $stmt = $conn->prepare("UPDATE toppings SET name=?, price=?, status=? WHERE id=?");
        $stmt->execute([$name, $price, $status, $id]);
        $_SESSION['success'] = "Cập nhật thành công";
    } else {
        $_SESSION['error'] = "Dữ liệu không hợp lệ";
    }
    header("Location: admin.php?url=toppings");
    exit;
}

function handleDeleteTopping() {
    if (!isset($_GET['id'])) return;
    $id = (int)$_GET['id'];
    $conn = getDB();
    $conn->prepare("UPDATE toppings SET status = 0 WHERE id = ?")->execute([$id]);
    $_SESSION['success'] = "Đã vô hiệu hóa topping";
    header("Location: admin.php?url=toppings");
    exit;
}

function handleRestoreTopping() {
    if (!isset($_GET['id'])) return;
    $id = (int)$_GET['id'];
    $conn = getDB();
    $conn->prepare("UPDATE toppings SET status = 1 WHERE id = ?")->execute([$id]);
    $_SESSION['success'] = "Đã khôi phục topping";
    header("Location: admin.php?url=toppings");
    exit;
}
