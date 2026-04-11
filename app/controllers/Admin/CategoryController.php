<?php
require_once __DIR__ . '/../../../config/database.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


function getCategories() {
    $conn = getDB();
    return $conn->query("SELECT * FROM categories ORDER BY id DESC")->fetchAll();
}

function handleAddCategory() {
    if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['action']) || $_POST['action'] != 'add') return;
    $conn = getDB();
    $name = trim($_POST['name']);
    if (empty($name)) {
        $_SESSION['error'] = "Tên danh mục không được trống";
        header("Location: admin.php?url=categories");
        exit;
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

function handleUpdateCategory() {
    if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['action']) || $_POST['action'] != 'edit') return;
    $conn = getDB();
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    if ($id <= 0 || empty($name)) {
        $_SESSION['error'] = "Dữ liệu không hợp lệ";
        header("Location: admin.php?url=categories");
        exit;
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

function handleDeleteCategory() {
    if (!isset($_GET['id'])) return;
    $id = (int)$_GET['id'];
    $conn = getDB();
    $stmt = $conn->prepare("UPDATE categories SET status = 0 WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['success'] = "Đã vô hiệu hóa danh mục";
    header("Location: admin.php?url=categories");
    exit;
}

function handleRestoreCategory() {
    if (!isset($_GET['id'])) return;
    $id = (int)$_GET['id'];
    $conn = getDB();
    $stmt = $conn->prepare("UPDATE categories SET status = 1 WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['success'] = "Đã khôi phục danh mục";
    header("Location: admin.php?url=categories");
    exit;
}
