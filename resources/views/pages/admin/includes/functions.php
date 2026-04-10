<?php
require_once __DIR__ . '/../../../../../config/database.php';

/**
 * Đếm bản ghi
 */
function countRecords($table, $where = '') {
    $pdo = getDB();

    $sql = "SELECT COUNT(*) FROM $table $where";
    return (int) $pdo->query($sql)->fetchColumn();
}

/**
 * Tổng doanh thu
 */
function getTotalRevenue() {
    $pdo = getDB();

    $sql = "SELECT COALESCE(SUM(final_amount), 0) FROM orders WHERE status = 'completed'";
    return (float) $pdo->query($sql)->fetchColumn();
}

/**
 * Đơn hàng pending
 */
function getPendingOrdersCount() {
    return countRecords('orders', "WHERE status = 'pending'");
}

/**
 * Nguyên liệu sắp hết
 */
function getLowStockIngredientsCount() {
    $pdo = getDB();

    try {
        // check bảng tồn tại
        $result = $pdo->query("SHOW TABLES LIKE 'ingredients'");
        if ($result->rowCount() == 0) return 0;

        // check có cột min_quantity không
        $stmt = $pdo->query("SHOW COLUMNS FROM ingredients LIKE 'min_quantity'");

        if ($stmt->rowCount() > 0) {
            return countRecords('ingredients', 'WHERE stock_quantity <= min_quantity');
        } else {
            return countRecords('ingredients', 'WHERE stock_quantity <= 5');
        }

    } catch (PDOException $e) {
        return 0;
    }
}
?>
