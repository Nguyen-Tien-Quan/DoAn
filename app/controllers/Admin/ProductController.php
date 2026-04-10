<?php
require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * =========================
 * GET PRODUCTS (LIST + FILTER + PAGINATION)
 * =========================
 */
function getAllProducts($page = 1, $limit = 10, $filters = []) {
    $pdo = getDB();

    $offset = ($page - 1) * $limit;

    $search = $filters['search'] ?? '';
    $category_id = $filters['category_id'] ?? 0;
    $status = $filters['status'] ?? -1;

    $where = " WHERE 1=1 ";
    $params = [];

    if ($search != '') {
        $where .= " AND p.name LIKE ? ";
        $params[] = "%$search%";
    }

    if ($category_id > 0) {
        $where .= " AND p.category_id = ? ";
        $params[] = $category_id;
    }

    if ($status != -1) {
        $where .= " AND p.status = ? ";
        $params[] = $status;
    }

    // Count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products p $where");
    $stmt->execute($params);
    $total = $stmt->fetchColumn();

    // Data
    $sql = "SELECT p.*, c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            $where
            ORDER BY p.id DESC
            LIMIT $limit OFFSET $offset";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll();

    return [
        'data' => $data,
        'total' => $total
    ];
}

/**
 * =========================
 * GET CATEGORIES (for select)
 * =========================
 */
function getCategoryOptions() {
    $pdo = getDB();
    try {
        return $pdo->query("SELECT id, name FROM categories WHERE status = 1")->fetchAll();
    } catch (PDOException $e) {
        return $pdo->query("SELECT id, name FROM categories")->fetchAll();
    }
}

/**
 * =========================
 * DELETE (SOFT)
 * =========================
 */
function handleDeleteProduct() {
    $pdo = getDB();

    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $pdo->prepare("UPDATE products SET status = 0 WHERE id = ?")->execute([$id]);

        $_SESSION['success'] = "Đã vô hiệu hóa món ăn";
    }

    header("Location: admin.php?url=products");
    exit;
}

/**
 * =========================
 * RESTORE
 * =========================
 */
function handleRestoreProduct() {
    $pdo = getDB();

    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $pdo->prepare("UPDATE products SET status = 1 WHERE id = ?")->execute([$id]);

        $_SESSION['success'] = "Đã khôi phục món ăn";
    }

    header("Location: admin.php?url=products");
    exit;
}
