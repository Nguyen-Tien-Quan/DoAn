<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../../config/database.php';

/**
 * Lấy danh sách đơn hàng
 */
function getOrders($page = 1, $limit = 10, $filters = []) {
    $conn = getDB();
    $offset = ($page - 1) * $limit;

    $search = $filters['search'] ?? '';
    $status = $filters['status'] ?? '';

    $where = "WHERE 1=1";
    $params = [];

    if (!empty($search)) {
        $where .= " AND order_code LIKE ?";
        $params[] = "%$search%";
    }

    if (!empty($status)) {
        $where .= " AND status = ?";
        $params[] = $status;
    }

    // COUNT
    $stmt = $conn->prepare("SELECT COUNT(*) FROM orders $where");
    $stmt->execute($params);
    $total = $stmt->fetchColumn();

    // DATA
    $sql = "SELECT o.*, c.full_name, c.phone, p.payment_status
            FROM orders o
            LEFT JOIN customers c ON o.customer_id = c.id
            LEFT JOIN payments p ON o.id = p.order_id
            $where
            ORDER BY o.id ASC
            LIMIT $limit OFFSET $offset";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    return [
        'data' => $stmt->fetchAll(),
        'total' => $total,
        'totalPages' => ceil($total / $limit)
    ];
}

/**
 * Chi tiết đơn
 */
function getOrderDetail($id) {
    $conn = getDB();

    $stmt = $conn->prepare("
        SELECT o.*, c.full_name, c.phone, c.address,
               p.payment_method, p.payment_status
        FROM orders o
        LEFT JOIN customers c ON o.customer_id = c.id
        LEFT JOIN payments p ON o.id = p.order_id
        WHERE o.id = ?
    ");
    $stmt->execute([$id]);
    $order = $stmt->fetch();

    if (!$order) return null;

    $itemsStmt = $conn->prepare("
        SELECT oi.*, pr.name as product_name, pv.variant_name
        FROM order_items oi
        LEFT JOIN products pr ON oi.product_id = pr.id
        LEFT JOIN product_variants pv ON oi.variant_id = pv.id
        WHERE oi.order_id = ?
    ");
    $itemsStmt->execute([$id]);
    $items = $itemsStmt->fetchAll();

    foreach ($items as &$item) {
        $topStmt = $conn->prepare("
            SELECT t.name, oit.price
            FROM order_item_toppings oit
            JOIN toppings t ON oit.topping_id = t.id
            WHERE oit.order_item_id = ?
        ");
        $topStmt->execute([$item['id']]);
        $item['toppings'] = $topStmt->fetchAll();
    }

    return [
        'order' => $order,
        'items' => $items
    ];
}

/**
 * UPDATE STATUS (FIX FULL)
 */
function updateOrderStatus($order_id, $new_status) {
    $conn = getDB();

    // 🔥 Normalize tránh lỗi DB bẩn
    $new_status = trim(strtolower($new_status));

    $allowed = [
        'pending',
        'confirmed',
        'preparing',
        'ready_for_delivery',
        'delivering',
        'completed',
        'cancelled'
    ];

    if (!in_array($new_status, $allowed)) {
        return ['success' => false, 'message' => 'Trạng thái không hợp lệ: ' . $new_status];
    }

    // Lấy trạng thái hiện tại
    $stmt = $conn->prepare("SELECT id, status FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    if (!$order) {
        return ['success' => false, 'message' => 'Không tìm thấy đơn #' . $order_id];
    }

    // 🔥 Normalize luôn DB
    $old = trim(strtolower($order['status']));

    // DEBUG (rất quan trọng)
    error_log("ORDER #$order_id | OLD: [$old] -> NEW: [$new_status]");

    // RULE FLOW CHUẨN
    $valid = [
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['preparing', 'cancelled'],
        'preparing' => ['ready_for_delivery', 'cancelled'],
        'ready_for_delivery' => ['delivering', 'cancelled'],
        'delivering' => ['completed', 'cancelled'],
        'completed' => [],
        'cancelled' => []
    ];

    if (!isset($valid[$old]) || !in_array($new_status, $valid[$old])) {
        return [
            'success' => false,
            'message' => "Không thể chuyển từ '$old' sang '$new_status'"
        ];
    }

    try {
        $conn->beginTransaction();

        $stmt = $conn->prepare("
            UPDATE orders
            SET status = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$new_status, $order_id]);

        $conn->commit();

        return [
            'success' => true,
            'message' => "Đã chuyển sang $new_status",
            'new_status' => $new_status
        ];

    } catch (Exception $e) {
        $conn->rollBack();
        error_log("ERROR UPDATE: " . $e->getMessage());

        return [
            'success' => false,
            'message' => 'Lỗi DB'
        ];
    }
}

/**
 * Render chi tiết đơn
 */
function renderOrderDetailHTML($id) {
    $data = getOrderDetail($id);

    if (!$data) {
        return '<p class="text-danger text-center">Không tìm thấy đơn</p>';
    }

    $order = $data['order'];
    $items = $data['items'];

    ob_start();
    ?>

    <div>
        <p><strong>Mã đơn:</strong> <?= htmlspecialchars($order['order_code']) ?></p>
        <p><strong>Khách:</strong> <?= htmlspecialchars($order['full_name'] ?? 'Khách lẻ') ?></p>
        <p><strong>Trạng thái:</strong> <?= htmlspecialchars($order['status']) ?></p>

        <hr>

        <?php foreach ($items as $item): ?>
            <div>
                <?= htmlspecialchars($item['product_name']) ?>
                x <?= $item['quantity'] ?>
            </div>
        <?php endforeach; ?>
    </div>

    <?php
    return ob_get_clean();
}
