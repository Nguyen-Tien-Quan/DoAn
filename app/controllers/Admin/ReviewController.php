<?php
require_once __DIR__ . '/../../../config/database.php';

function getReviews($page = 1, $limit = 15, $filters = []) {
    $conn = getDB();
    $offset = ($page - 1) * $limit;

    $search = $filters['search'] ?? '';
    $product_id = $filters['product_id'] ?? 0;
    $rating = $filters['rating'] ?? 0;
    $status = isset($filters['status']) ? (int)$filters['status'] : -1;

    $where = " WHERE 1=1 ";
    $params = [];

    if (!empty($search)) {
        $where .= " AND (c.full_name LIKE ? OR p.name LIKE ? OR r.comment LIKE ?) ";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    if ($product_id > 0) {
        $where .= " AND r.product_id = ? ";
        $params[] = $product_id;
    }

    if ($rating > 0) {
        $where .= " AND r.rating = ? ";
        $params[] = $rating;
    }

    if ($status != -1) {
        $where .= " AND r.status = ? ";
        $params[] = $status;
    }

    // COUNT
    $stmt = $conn->prepare("
        SELECT COUNT(*) FROM reviews r
        LEFT JOIN customers c ON r.customer_id = c.id
        LEFT JOIN products p ON r.product_id = p.id
        $where
    ");
    $stmt->execute($params);
    $total = $stmt->fetchColumn();

    // DATA
    $stmt = $conn->prepare("
        SELECT r.*, c.full_name as customer_name,
               p.name as product_name
        FROM reviews r
        LEFT JOIN customers c ON r.customer_id = c.id
        LEFT JOIN products p ON r.product_id = p.id
        $where
        ORDER BY r.id DESC
        LIMIT $limit OFFSET $offset
    ");
    $stmt->execute($params);

    return [
        'data' => $stmt->fetchAll(),
        'total' => $total
    ];
}

function getProductsForFilter() {
    $conn = getDB();
    return $conn->query("SELECT id, name FROM products")->fetchAll();
}
