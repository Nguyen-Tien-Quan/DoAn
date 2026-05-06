<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../../config/database.php';

/**
 * LẤY DANH SÁCH ĐƠN HÀNG
 */
function getOrders($page = 1, $limit = 10, $filters = []) {
    $conn = getDB();
    $offset = ($page - 1) * $limit;

    $search = $filters['search'] ?? '';
    $status = $filters['status'] ?? '';

    $where = "WHERE 1=1";
    $params = [];

    // SEARCH
    if (!empty($search)) {
        $where .= " AND o.order_code LIKE ?";
        $params[] = "%$search%";
    }

    // FILTER STATUS
    if (!empty($status)) {
        $where .= " AND o.status = ?";
        $params[] = $status;
    }

    // COUNT
    $stmt = $conn->prepare("SELECT COUNT(*) FROM orders o $where");
    $stmt->execute($params);
    $total = $stmt->fetchColumn();

    // DATA (FIX FULL NAME + PHONE + SORT DESC)
    $sql = "
        SELECT
    o.*,

    COALESCE(o.receiver_name, c.name) AS full_name,
    COALESCE(o.receiver_phone, c.phone) AS phone,

    p.payment_status


        FROM orders o
        LEFT JOIN users c ON o.user_id = c.id
        LEFT JOIN payments p ON o.id = p.order_id

        $where
        ORDER BY o.id DESC
        LIMIT $limit OFFSET $offset
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    return [
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        'total' => $total,
        'totalPages' => ceil($total / $limit)
    ];
}

/**
 * CHI TIẾT ĐƠN
 */
function getOrderDetail($id) {
    $conn = getDB();

    $stmt = $conn->prepare("
        SELECT
            o.*,
            COALESCE(o.receiver_name, c.full_name) AS full_name,
            COALESCE(o.receiver_phone, c.phone) AS phone,
            COALESCE(o.delivery_address, c.address) AS address,

            p.payment_method,
            p.payment_status

        FROM orders o
        LEFT JOIN users c ON o.user_id = c.id
        LEFT JOIN payments p ON o.id = p.order_id
        WHERE o.id = ?
    ");
    $stmt->execute([$id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) return null;

    $itemsStmt = $conn->prepare("
        SELECT
            oi.*,
            pr.name AS product_name,
            pv.variant_name
        FROM order_items oi
        LEFT JOIN products pr ON oi.product_id = pr.id
        LEFT JOIN product_variants pv ON oi.variant_id = pv.id
        WHERE oi.order_id = ?
    ");
    $itemsStmt->execute([$id]);
    $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($items as &$item) {
        $topStmt = $conn->prepare("
            SELECT t.name, oit.price
            FROM order_item_toppings oit
            JOIN toppings t ON oit.topping_id = t.id
            WHERE oit.order_item_id = ?
        ");
        $topStmt->execute([$item['id']]);
        $item['toppings'] = $topStmt->fetchAll(PDO::FETCH_ASSOC);
    }

    return [
        'order' => $order,
        'items' => $items
    ];
}

/**
 * UPDATE STATUS
 */
function updateOrderStatus($order_id, $new_status) {
    $conn = getDB();

    $new_status = strtolower(trim($new_status));

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
        return ['success' => false, 'message' => 'Trạng thái không hợp lệ'];
    }

    $stmt = $conn->prepare("SELECT id, status FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        return ['success' => false, 'message' => 'Không tìm thấy đơn'];
    }

    $old = strtolower($order['status']);

    $valid = [
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['preparing', 'cancelled'],
        'preparing' => ['ready_for_delivery', 'cancelled'],
        'ready_for_delivery' => ['delivering', 'cancelled'],
        'delivering' => ['completed', 'cancelled'],
        'completed' => [],
        'cancelled' => []
    ];

    if (!in_array($new_status, $valid[$old] ?? [])) {
        return [
            'success' => false,
            'message' => "Không thể chuyển từ $old sang $new_status"
        ];
    }

    try {
        $conn->beginTransaction();

        $delivery_status = null;

        if ($new_status === 'ready_for_delivery') {
            $delivery_status = 'pending';
        } elseif ($new_status === 'delivering') {
            $delivery_status = 'shipping';
        } elseif ($new_status === 'completed') {
            $delivery_status = 'delivered';
        } elseif ($new_status === 'cancelled') {
            $delivery_status = 'failed';
        }

        if ($delivery_status) {
            $stmt = $conn->prepare("
                UPDATE orders
                SET status=?, delivery_status=?, updated_at=NOW()
                WHERE id=?
            ");
            $stmt->execute([$new_status, $delivery_status, $order_id]);
        } else {
            $stmt = $conn->prepare("
                UPDATE orders
                SET status=?, updated_at=NOW()
                WHERE id=?
            ");
            $stmt->execute([$new_status, $order_id]);
        }

        $conn->commit();

        return ['success' => true];

    } catch (Exception $e) {
        $conn->rollBack();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * RENDER DETAIL
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
        <p><strong>SĐT:</strong> <?= htmlspecialchars($order['phone'] ?? '') ?></p>
        <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($order['address'] ?? '') ?></p>
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

/**
 * CANCEL ORDER
 */
function cancelOrder($order_id) {
    $conn = getDB();

    try {
        $stmt = $conn->prepare("
            UPDATE orders
            SET status='cancelled',
                delivery_status='failed',
                updated_at=NOW()
            WHERE id=?
        ");
        $stmt->execute([$order_id]);

        return ['success' => true];

    } catch (Exception $e) {
        return ['success' => false];
    }
}
