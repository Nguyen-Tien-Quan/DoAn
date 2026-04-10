<?php
require_once __DIR__ . '/../../../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ======================
// GET ALL
// ======================
function getAllCategories() {
    $pdo = getDB();
    return $pdo->query("SELECT * FROM categories ORDER BY id DESC")->fetchAll();
}

// ======================
// ADD
// ======================
function handleAddCategory() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $pdo = getDB();

        $name = trim($_POST['name']);
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));
        $description = trim($_POST['description'] ?? '');
        $status = (int)($_POST['status'] ?? 1);

        if (empty($name)) {
            $_SESSION['error'] = "Tên danh mục không được trống";
        } else {
            $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description, status) VALUES (?,?,?,?)");
            $stmt->execute([$name, $slug, $description, $status]);

            $_SESSION['success'] = "Thêm danh mục thành công";
        }
    }

    header("Location: admin.php?url=categories");
    exit;
}

// ======================
// UPDATE
// ======================
function handleUpdateCategory() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $pdo = getDB();

        $id = (int)$_POST['id'];
        $name = trim($_POST['name']);
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));
        $description = trim($_POST['description'] ?? '');
        $status = (int)($_POST['status'] ?? 1);

        if ($id <= 0 || empty($name)) {
            $_SESSION['error'] = "Dữ liệu không hợp lệ";
        } else {
            $stmt = $pdo->prepare("UPDATE categories SET name=?, slug=?, description=?, status=? WHERE id=?");
            $stmt->execute([$name, $slug, $description, $status, $id]);

            $_SESSION['success'] = "Cập nhật thành công";
        }
    }

    header("Location: admin.php?url=categories");
    exit;
}

// ======================
// DELETE (soft delete)
// ======================
function handleDeleteCategory() {
    if (isset($_GET['id'])) {
        $pdo = getDB();

        $id = (int)$_GET['id'];
        $pdo->prepare("UPDATE categories SET status = 0 WHERE id = ?")->execute([$id]);

        $_SESSION['success'] = "Đã vô hiệu hóa danh mục";
    }

    header("Location: admin.php?url=categories");
    exit;
}

// ======================
// RESTORE
// ======================
function handleRestoreCategory() {
    if (isset($_GET['id'])) {
        $pdo = getDB();

        $id = (int)$_GET['id'];
        $pdo->prepare("UPDATE categories SET status = 1 WHERE id = ?")->execute([$id]);

        $_SESSION['success'] = "Đã khôi phục danh mục";
    }

    header("Location: admin.php?url=categories");
    exit;
}
