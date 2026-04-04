<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';

function pagination() {
    $conn = getDB();

    $limit = 10;
    $page = $_GET['page'] ?? 1;
    $page = max(1, (int)$page);

    $total = (int)$conn->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $totalPages = max(1, ceil($total / $limit));
    if ($page > $totalPages) $page = $totalPages;

    $offset = ($page - 1) * $limit;

    $stmt = $conn->prepare("SELECT * FROM products ORDER BY id ASC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    // Trả dữ liệu về router
    return [
        'products' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        'page' => $page,
        'totalPages' => $totalPages,
        'favIds' => []
    ];
}

function getProducts() {
    $conn = getDB();

    $stmt = $conn->query("SELECT * FROM products");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $products;
}

function getProductById($id) {
    $conn = getDB();
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
