<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../../config/database.php';

/**
 * =========================
 * STAFF - LẤY DANH SÁCH ĐƠN
 * =========================
 */
function staffGetOrders($page = 1, $limit = 10, $status = '')
{
    $conn = getDB();
    $offset = ($page - 1) * $limit;

    $where = "WHERE 1=1";
    $params = [];

    if (!empty($status)) {
        $where .= " AND o.status = ?";
        $params[] = $status;
    }

    // COUNT
    $stmt = $conn->prepare("SELECT COUNT(*) FROM orders o $where");
    $stmt->execute($params);
    $total = $stmt->fetchColumn();

    // DATA (lọc gọn cho staff)
    $sql = "
        SELECT
            o.id,
            o.order_code,
            o.status,
            o.final_amount,
            o.payment_status,
            o.created_at,
            c.full_name,
            c.phone
        FROM orders o
        LEFT JOIN customers c ON o.customer_id = c.id
        $where
        ORDER BY o.id DESC
        LIMIT $limit OFFSET $offset
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    return [
        'data' => $stmt->fetchAll(),
        'total' => $total,
        'totalPages' => ceil($total / $limit)
    ];
}


/**
 * =========================
 * STAFF - CHI TIẾT ĐƠN
 * =========================
 */
function staffGetOrderDetail($id)
{
    $conn = getDB();

    $stmt = $conn->prepare("
        SELECT o.*, c.full_name, c.phone, c.address
        FROM orders o
        LEFT JOIN customers c ON o.customer_id = c.id
        WHERE o.id = ?
    ");
    $stmt->execute([$id]);
    $order = $stmt->fetch();

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
 * =========================
 * STAFF - CẬP NHẬT TRẠNG THÁI
 * (GIỚI HẠN QUYỀN)
 * =========================
 */
function staffUpdateOrderStatus($order_id, $new_status)
{
    $conn = getDB();

    $allowed = [
        'confirmed',
        'preparing',
        'ready_for_delivery'
    ];

    if (!in_array($new_status, $allowed)) {
        return [
            'success' => false,
            'message' => 'Không có quyền chuyển trạng thái này'
        ];
    }

    $stmt = $conn->prepare("SELECT status FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    if (!$order) {
        return [
            'success' => false,
            'message' => 'Không tìm thấy đơn'
        ];
    }

    try {
        $stmt = $conn->prepare("
            UPDATE orders
            SET status = ?, updated_at = NOW()
            WHERE id = ?
        ");

        $stmt->execute([$new_status, $order_id]);

        return [
            'success' => true,
            'message' => 'Cập nhật thành công'
        ];

    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Lỗi hệ thống'
        ];
    }
}


/**
 * =========================
 * STAFF - HỦY ĐƠN (GIỚI HẠN)
 * =========================
 */
function staffCancelOrder($order_id)
{
    $conn = getDB();

    try {
        $stmt = $conn->prepare("
            UPDATE orders
            SET status = 'cancelled',
                delivery_status = 'failed',
                updated_at = NOW()
            WHERE id = ?
        ");

        $stmt->execute([$order_id]);

        return ['success' => true];

    } catch (Exception $e) {
        return ['success' => false];
    }
}
