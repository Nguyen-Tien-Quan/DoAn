<?php
require_once __DIR__ . '/../../../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * =========================
 * GET PRODUCTS
 * =========================
 */
function getAllProducts($page = 1, $limit = 10, $filters = []) {
    $conn = getDB(); // 🔥 dùng $conn

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

    // COUNT
    $stmt = $conn->prepare("SELECT COUNT(*) FROM products p $where");
    $stmt->execute($params);
    $total = $stmt->fetchColumn();

    // DATA
    $sql = "SELECT p.*, c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            $where
            ORDER BY p.id DESC
            LIMIT $limit OFFSET $offset";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll();

    return [
        'data' => $data,
        'total' => $total
    ];
}

/**
 * =========================
 * CATEGORY OPTIONS
 * =========================
 */
function getCategoryOptions() {
    $conn = getDB();
    return $conn->query("SELECT id, name FROM categories")->fetchAll();
}

/**
 * =========================
 * DELETE
 * =========================
 */
function handleDeleteProduct() {
    $conn = getDB();

    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];

        $stmt = $conn->prepare("UPDATE products SET status = 0 WHERE id = ?");
        $stmt->execute([$id]);

        $_SESSION['success'] = "Đã vô hiệu hóa sản phẩm";
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
    $conn = getDB();

    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];

        $stmt = $conn->prepare("UPDATE products SET status = 1 WHERE id = ?");
        $stmt->execute([$id]);

        $_SESSION['success'] = "Đã khôi phục sản phẩm";
    }

    header("Location: admin.php?url=products");
    exit;
}

function ensureVariantTable() {
    $conn = getDB();
    try { $conn->exec("ALTER TABLE product_variants ADD COLUMN stock_quantity INT DEFAULT 0"); } catch(PDOException $e) {}
    try { $conn->exec("ALTER TABLE product_variants ADD COLUMN status TINYINT DEFAULT 1"); } catch(PDOException $e) {}
}

function getAllVariants() {
    $conn = getDB();
    $sql = "SELECT v.*, p.name as product_name
            FROM product_variants v
            LEFT JOIN products p ON v.product_id = p.id
            ORDER BY v.id DESC";
    return $conn->query($sql)->fetchAll();
}

function getAllProductsForVariant() {
    $conn = getDB();
    return $conn->query("SELECT id, name FROM products WHERE status = 1 ORDER BY name")->fetchAll();
}

function handleAddVariant() {
    if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['action']) || $_POST['action'] != 'add') return;
    $conn = getDB();
    $product_id = (int)($_POST['product_id'] ?? 0);
    $variant_name = trim($_POST['variant_name'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock_quantity'] ?? 0);
    if ($product_id > 0 && !empty($variant_name) && $price > 0) {
        $stmt = $conn->prepare("INSERT INTO product_variants (product_id, variant_name, price, stock_quantity) VALUES (?,?,?,?)");
        $stmt->execute([$product_id, $variant_name, $price, $stock]);
        $_SESSION['success'] = "Thêm size thành công";
    } else {
        $_SESSION['error'] = "Vui lòng nhập đầy đủ thông tin (sản phẩm, tên size, giá)";
    }
    header("Location: admin.php?url=variants");
    exit;
}

function handleUpdateVariant() {
    if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['action']) || $_POST['action'] != 'edit') return;
    $conn = getDB();
    $id = (int)($_POST['id'] ?? 0);
    $product_id = (int)($_POST['product_id'] ?? 0);
    $variant_name = trim($_POST['variant_name'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock_quantity'] ?? 0);
    if ($id > 0 && $product_id > 0 && !empty($variant_name) && $price > 0) {
        $stmt = $conn->prepare("UPDATE product_variants SET product_id=?, variant_name=?, price=?, stock_quantity=? WHERE id=?");
        $stmt->execute([$product_id, $variant_name, $price, $stock, $id]);
        $_SESSION['success'] = "Cập nhật size thành công";
    } else {
        $_SESSION['error'] = "Dữ liệu không hợp lệ";
    }
    header("Location: admin.php?url=variants");
    exit;
}

function handleDeleteVariant() {
    if (!isset($_GET['id'])) return;
    $id = (int)$_GET['id'];
    $conn = getDB();
    $conn->prepare("DELETE FROM product_variants WHERE id = ?")->execute([$id]);
    $_SESSION['success'] = "Đã xóa size";
    header("Location: admin.php?url=variants");
    exit;
}
