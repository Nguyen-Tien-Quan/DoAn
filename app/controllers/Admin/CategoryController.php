<?php
require_once __DIR__ . '/../../../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ================= ROLE ================= */
$is_admin = ($_SESSION['role_name'] ?? '') === 'admin';

/* ================= GET LIST ================= */
function getCategories() {
    $conn = getDB();
    return $conn->query("SELECT * FROM categories ORDER BY id ASC")->fetchAll();
}

/* ================= ADD ================= */
function handleAddCategory() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['action'] ?? '') !== 'add') return;

    $conn = getDB();
    $name = trim($_POST['name']);

    if (empty($name)) {
        $_SESSION['error'] = "Tên danh mục không được trống";
        return;
    }

    $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));
    $description = trim($_POST['description'] ?? '');
    $status = (int)($_POST['status'] ?? 1);

    $stmt = $conn->prepare("INSERT INTO categories (name, slug, description, status) VALUES (?,?,?,?)");
    $stmt->execute([$name, $slug, $description, $status]);

    $_SESSION['success'] = "Thêm danh mục thành công";
    header("Location: admin.php?url=categories");
    exit;
}

/* ================= UPDATE ================= */
function handleUpdateCategory() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['action'] ?? '') !== 'edit') return;

    $conn = getDB();
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name']);

    if ($id <= 0 || empty($name)) {
        $_SESSION['error'] = "Dữ liệu không hợp lệ";
        return;
    }

    $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));
    $description = trim($_POST['description'] ?? '');
    $status = (int)($_POST['status'] ?? 1);

    $stmt = $conn->prepare("UPDATE categories SET name=?, slug=?, description=?, status=? WHERE id=?");
    $stmt->execute([$name, $slug, $description, $status, $id]);

    $_SESSION['success'] = "Cập nhật thành công";
    header("Location: admin.php?url=categories");
    exit;
}

/* ================= SOFT DELETE ================= */
function handleSoftDeleteCategory() {
    if (!isset($_GET['soft_delete'])) return;

    $id = (int)$_GET['soft_delete'];
    $conn = getDB();

    $conn->prepare("UPDATE categories SET status = 0 WHERE id = ?")->execute([$id]);

    $_SESSION['success'] = "Đã vô hiệu hóa danh mục";
    header("Location: admin.php?url=categories");
    exit;
}

/* ================= RESTORE ================= */
function handleRestoreCategory() {
    if (!isset($_GET['restore'])) return;

    $id = (int)$_GET['restore'];
    $conn = getDB();

    $conn->prepare("UPDATE categories SET status = 1 WHERE id = ?")->execute([$id]);

    $_SESSION['success'] = "Đã khôi phục danh mục";
    header("Location: admin.php?url=categories");
    exit;
}

/* ================= HARD DELETE (BỔ SUNG THIẾU) ================= */
function handleHardDeleteCategory() {
    if (!isset($_GET['hard_delete'])) return;

    $id = (int)$_GET['hard_delete'];
    $conn = getDB();

    try {
        $conn->beginTransaction();

        // lấy product
        $products = $conn->prepare("SELECT id FROM products WHERE category_id = ?");
        $products->execute([$id]);
        $productIds = $products->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($productIds)) {

            $in = implode(',', array_fill(0, count($productIds), '?'));

            $conn->prepare("DELETE FROM cart_items WHERE product_id IN ($in)")
                ->execute($productIds);

            $conn->prepare("DELETE FROM product_variants WHERE product_id IN ($in)")
                ->execute($productIds);

            $conn->prepare("DELETE FROM product_toppings WHERE product_id IN ($in)")
                ->execute($productIds);

            $conn->prepare("DELETE FROM order_items WHERE product_id IN ($in)")
                ->execute($productIds);

            $conn->prepare("DELETE FROM products WHERE category_id = ?")
                ->execute([$id]);
        }

        $conn->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);

        $conn->commit();

        $_SESSION['success'] = "Xóa vĩnh viễn thành công";
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['error'] = "Lỗi: " . $e->getMessage();
    }

    header("Location: admin.php?url=categories");
    exit;
}
