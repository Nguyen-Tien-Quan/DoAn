<?php
/**
 * ShipperController.php
 * Xử lý các chức năng dành cho shipper (người giao hàng)
 */

require_once __DIR__ . '/../../../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Kiểm tra quyền truy cập shipper
 */
function checkShipperAuth() {
    if (!isset($_SESSION['user'])) {
        header('Location: index.php?url=login');
        exit;
    }
    if ($_SESSION['user']['role_id'] != 4) {
        switch ($_SESSION['user']['role_id']) {
            case 1: header('Location: admin.php'); break;
            case 2: header('Location: staff.php'); break;
            default: header('Location: index.php'); break;
        }
        exit;
    }
}

/**
 * Lấy danh sách đơn hàng được giao cho shipper hiện tại
 */
function getShipperOrders($shipperId, $statusFilter = 'all') {
    $conn = getDB();
    $sql = "
        SELECT o.*,
               c.full_name AS customer_name,
               c.phone AS customer_phone,
               c.address AS customer_address
        FROM orders o
        LEFT JOIN customers c ON o.customer_id = c.id
        WHERE o.shipper_id = ?
    ";
    $params = [$shipperId];
    if ($statusFilter !== 'all' && in_array($statusFilter, ['pending', 'shipping', 'delivered', 'failed'])) {
        $sql .= " AND o.delivery_status = ?";
        $params[] = $statusFilter;
    }
    $sql .= " ORDER BY o.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Lấy danh sách đơn hàng sẵn sàng cho shipper nhận
 * Chỉ lấy đơn đã được xác nhận (status = 'confirmed') và chưa có shipper
 */
function getAvailableOrders() {
    $conn = getDB();
    $sql = "
        SELECT o.*,
               c.full_name AS customer_name,
               c.phone AS customer_phone,
               c.address AS customer_address
        FROM orders o
        LEFT JOIN customers c ON o.customer_id = c.id
        WHERE o.shipper_id IS NULL
          AND o.order_type = 'delivery'
          AND o.status = 'confirmed'
          AND o.delivery_status = 'pending'
        ORDER BY o.created_at ASC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Lấy chi tiết đơn hàng
 */
function getShipperOrderDetail($orderId) {
    $conn = getDB();
    $stmt = $conn->prepare("
        SELECT o.*,
               c.full_name AS customer_name,
               c.phone AS customer_phone,
               c.address AS customer_address,
               c.email AS customer_email
        FROM orders o
        LEFT JOIN customers c ON o.customer_id = c.id
        WHERE o.id = ?
    ");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$order) return null;

    $currentUserId = $_SESSION['user']['id'] ?? 0;
    if ($order['shipper_id'] && $order['shipper_id'] != $currentUserId) {
        return null;
    }

    // Lấy items
    $stmt = $conn->prepare("
        SELECT oi.*, p.name AS product_name, p.image AS product_image,
               pv.variant_name
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        LEFT JOIN product_variants pv ON oi.variant_id = pv.id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$orderId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($items as &$item) {
        $stmt = $conn->prepare("
            SELECT oit.*, t.name AS topping_name
            FROM order_item_toppings oit
            JOIN toppings t ON oit.topping_id = t.id
            WHERE oit.order_item_id = ?
        ");
        $stmt->execute([$item['id']]);
        $item['toppings'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($item);
    $order['items'] = $items;

    $stmt = $conn->prepare("SELECT * FROM payments WHERE order_id = ?");
    $stmt->execute([$orderId]);
    $order['payment'] = $stmt->fetch(PDO::FETCH_ASSOC);

    return $order;
}

/**
 * Shipper nhận đơn hàng
 */
function acceptOrder() {
    checkShipperAuth();

    $orderId = $_POST['order_id'] ?? 0;
    $shipperId = $_SESSION['user']['id'];

    if (!$orderId) {
        echo json_encode(['success' => false, 'message' => 'Thiếu mã đơn hàng']);
        exit;
    }

    $conn = getDB();

    // Kiểm tra đơn hàng: đã confirm, chưa có shipper, là giao hàng
    $stmt = $conn->prepare("
        SELECT id, shipper_id FROM orders
        WHERE id = ?
          AND order_type = 'delivery'
          AND status = 'confirmed'
          AND delivery_status = 'pending'
    ");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Đơn hàng không tồn tại hoặc không khả dụng']);
        exit;
    }

    if ($order['shipper_id'] !== null) {
        echo json_encode(['success' => false, 'message' => 'Đơn hàng đã có shipper khác nhận']);
        exit;
    }

    $stmt = $conn->prepare("
        UPDATE orders
        SET shipper_id = ?,
            delivery_status = 'pending',
            updated_at = NOW()
        WHERE id = ?
    ");
    $result = $stmt->execute([$shipperId, $orderId]);

    echo json_encode(['success' => $result, 'message' => $result ? 'Nhận đơn thành công' : 'Lỗi cập nhật']);
    exit;
}

/**
 * Cập nhật trạng thái giao hàng
 */
function updateDeliveryStatus() {
    checkShipperAuth();

    $orderId = $_POST['order_id'] ?? 0;
    $newStatus = $_POST['status'] ?? '';
    $note = $_POST['note'] ?? '';
    $shipperId = $_SESSION['user']['id'];

    $allowed = ['shipping', 'delivered', 'failed'];
    if (!$orderId || !in_array($newStatus, $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
        exit;
    }

    $conn = getDB();

    $stmt = $conn->prepare("
        SELECT id, delivery_status, payment_method, note
        FROM orders
        WHERE id = ? AND shipper_id = ?
    ");
    $stmt->execute([$orderId, $shipperId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Bạn không có quyền cập nhật đơn này']);
        exit;
    }

    $updateData = [
        'delivery_status' => $newStatus,
        'updated_at' => date('Y-m-d H:i:s')
    ];

    if ($newStatus === 'delivered') {
        $updateData['status'] = 'completed';
        $updateData['completed_at'] = date('Y-m-d H:i:s');
        if ($order['payment_method'] === 'cash') {
            $stmt = $conn->prepare("UPDATE payments SET payment_status = 'paid', paid_at = NOW() WHERE order_id = ?");
            $stmt->execute([$orderId]);
            $updateData['payment_status'] = 'paid';
        }
    }

    if ($newStatus === 'failed' && $note) {
        $updateData['note'] = $order['note'] ? $order['note'] . "\n[Lỗi giao]: " . $note : "[Lỗi giao]: " . $note;
    }

    $fields = [];
    $params = [];
    foreach ($updateData as $field => $value) {
        $fields[] = "$field = ?";
        $params[] = $value;
    }
    $params[] = $orderId;

    $sql = "UPDATE orders SET " . implode(', ', $fields) . " WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute($params);

    echo json_encode(['success' => $result, 'message' => $result ? 'Cập nhật thành công' : 'Lỗi cập nhật']);
    exit;
}

/**
 * Lịch sử giao hàng
 */
function getShipperHistory($shipperId, $limit = 50) {
    $conn = getDB();
    // Ép kiểu limit là integer
    $limit = (int)$limit;
    if ($limit <= 0) $limit = 50;

    $stmt = $conn->prepare("
        SELECT o.*,
               c.full_name AS customer_name,
               c.phone AS customer_phone
        FROM orders o
        LEFT JOIN customers c ON o.customer_id = c.id
        WHERE o.shipper_id = ?
          AND o.delivery_status IN ('delivered', 'failed')
        ORDER BY o.updated_at DESC
        LIMIT " . $limit   // 👈 Không dùng placeholder cho LIMIT (hoặc dùng bindValue với PDO::PARAM_INT)
    );
    $stmt->execute([$shipperId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
/**
 * Thống kê nhanh
 */
function getShipperStats($shipperId) {
    $conn = getDB();
    $today = date('Y-m-d');

    $stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE shipper_id = ? AND delivery_status = 'shipping'");
    $stmt->execute([$shipperId]);
    $shipping = $stmt->fetchColumn();

    $stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE shipper_id = ? AND delivery_status = 'pending'");
    $stmt->execute([$shipperId]);
    $pending = $stmt->fetchColumn();

    $stmt = $conn->prepare("
        SELECT COUNT(*), COALESCE(SUM(final_amount), 0)
        FROM orders
        WHERE shipper_id = ?
          AND delivery_status = 'delivered'
          AND DATE(completed_at) = ?
    ");
    $stmt->execute([$shipperId, $today]);
    $row = $stmt->fetch(PDO::FETCH_NUM);
    $deliveredToday = $row[0] ?? 0;
    $totalCashToday = $row[1] ?? 0;

    return [
        'pending' => $pending,
        'shipping' => $shipping,
        'delivered_today' => $deliveredToday,
        'total_cash_today' => $totalCashToday
    ];
}

// Các hàm tiện ích giữ nguyên
function formatMoney($amount) {
    return number_format($amount, 0, ',', '.') . 'đ';
}
function getDeliveryStatusText($status) {
    $map = ['pending' => 'Chờ nhận', 'shipping' => 'Đang giao', 'delivered' => 'Đã giao', 'failed' => 'Thất bại'];
    return $map[$status] ?? $status;
}
function getStatusClass($status) {
    $map = ['pending' => 'status-pending', 'shipping' => 'status-shipping', 'delivered' => 'status-completed', 'failed' => 'status-failed'];
    return $map[$status] ?? '';
}
