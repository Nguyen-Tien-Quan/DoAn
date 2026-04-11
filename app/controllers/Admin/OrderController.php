<?php
require_once __DIR__ . '/../../../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * =========================
 * GET ORDERS (LIST)
 * =========================
 */
function getOrders($page = 1, $limit = 10, $filters = []) {
    $conn = getDB();

    $offset = ($page - 1) * $limit;

    $search = $filters['search'] ?? '';
    $status = $filters['status'] ?? '';

    $where = "WHERE 1=1";
    $params = [];

    if ($search) {
        $where .= " AND order_code LIKE ?";
        $params[] = "%$search%";
    }

    if ($status) {
        $where .= " AND status = ?";
        $params[] = $status;
    }

    // count
    $stmt = $conn->prepare("SELECT COUNT(*) FROM orders $where");
    $stmt->execute($params);
    $total = $stmt->fetchColumn();

    // data
    $sql = "SELECT o.*, c.full_name, c.phone, p.payment_status
            FROM orders o
            LEFT JOIN customers c ON o.customer_id = c.id
            LEFT JOIN payments p ON o.id = p.order_id
            $where
            ORDER BY o.id DESC
            LIMIT $limit OFFSET $offset";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    return [
        'data' => $stmt->fetchAll(),
        'total' => $total
    ];
}

/**
 * =========================
 * AJAX ORDER DETAIL
 * =========================
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

    $items = $conn->prepare("
        SELECT oi.*, pr.name as product_name, pv.variant_name
        FROM order_items oi
        LEFT JOIN products pr ON oi.product_id = pr.id
        LEFT JOIN product_variants pv ON oi.variant_id = pv.id
        WHERE oi.order_id = ?
    ");
    $items->execute([$id]);
    $items = $items->fetchAll();

    foreach ($items as &$item) {
        $top = $conn->prepare("
            SELECT t.name, oit.price
            FROM order_item_toppings oit
            JOIN toppings t ON oit.topping_id = t.id
            WHERE oit.order_item_id = ?
        ");
        $top->execute([$item['id']]);
        $item['toppings'] = $top->fetchAll();
    }

    return [
        'order' => $order,
        'items' => $items
    ];
}
