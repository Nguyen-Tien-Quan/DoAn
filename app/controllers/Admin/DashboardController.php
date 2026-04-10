<?php
require_once __DIR__ . '/../../config/database.php';

function getDashboardData() {
    $pdo = getDB();

    return [
        'totalOrders' => $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
        'totalProducts' => $pdo->query("SELECT COUNT(*) FROM products WHERE status = 1")->fetchColumn(),
        'totalCustomers' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
        'totalRevenue' => $pdo->query("SELECT SUM(total_price) FROM orders")->fetchColumn(),
        'pendingOrders' => $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn(),
        'lowStock' => $pdo->query("SELECT COUNT(*) FROM product_variants WHERE stock_quantity < 10")->fetchColumn()
    ];
}
