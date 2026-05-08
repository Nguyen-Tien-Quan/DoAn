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

    return $conn->query("
        SELECT *
        FROM categories
        ORDER BY sort_order ASC, id ASC
    ")->fetchAll(PDO::FETCH_ASSOC);
}

/* ================= CREATE SLUG ================= */
function createSlug($string) {

    $string = strtolower(trim($string));

    $search = [
        'á','à','ả','ã','ạ',
        'ă','ắ','ằ','ẳ','ẵ','ặ',
        'â','ấ','ầ','ẩ','ẫ','ậ',
        'đ',
        'é','è','ẻ','ẽ','ẹ',
        'ê','ế','ề','ể','ễ','ệ',
        'í','ì','ỉ','ĩ','ị',
        'ó','ò','ỏ','õ','ọ',
        'ô','ố','ồ','ổ','ỗ','ộ',
        'ơ','ớ','ờ','ở','ỡ','ợ',
        'ú','ù','ủ','ũ','ụ',
        'ư','ứ','ừ','ử','ữ','ự',
        'ý','ỳ','ỷ','ỹ','ỵ'
    ];

    $replace = [
        'a','a','a','a','a',
        'a','a','a','a','a','a',
        'a','a','a','a','a','a',
        'd',
        'e','e','e','e','e',
        'e','e','e','e','e','e',
        'i','i','i','i','i',
        'o','o','o','o','o',
        'o','o','o','o','o','o',
        'o','o','o','o','o','o',
        'u','u','u','u','u',
        'u','u','u','u','u','u',
        'y','y','y','y','y'
    ];

    $string = str_replace($search, $replace, $string);

    $string = preg_replace('/[^a-z0-9]+/', '-', $string);

    return trim($string, '-');
}

/* ================= UPLOAD IMAGE ================= */
function uploadCategoryImage($file) {

    if (empty($file['name'])) {
        return null;
    }

    // ĐÚNG theo yêu cầu
    $uploadDir = __DIR__ . '/../../../public/assets/img/category-item/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileExt = pathinfo($file['name'], PATHINFO_EXTENSION);

    $fileName = time() . '_' . uniqid() . '.' . $fileExt;

    $targetPath = $uploadDir . $fileName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $fileName;
    }

    return null;
}
/* ================= ADD ================= */
function handleAddCategory() {

    if (
        $_SERVER['REQUEST_METHOD'] !== 'POST'
        || ($_POST['action'] ?? '') !== 'add'
    ) {
        return;
    }

    $conn = getDB();

    $name = trim($_POST['name'] ?? '');

    if (empty($name)) {

        $_SESSION['error'] = "Tên danh mục không được trống";

        header("Location: admin.php?url=categories");
        exit;
    }

    $slug = !empty($_POST['slug'])
        ? createSlug($_POST['slug'])
        : createSlug($name);

    $description = trim($_POST['description'] ?? '');

    $parent_id = !empty($_POST['parent_id'])
        ? (int)$_POST['parent_id']
        : null;

    $sort_order = (int)($_POST['sort_order'] ?? 0);

    $status = (int)($_POST['status'] ?? 1);

    $image = uploadCategoryImage($_FILES['image'] ?? []);

    $stmt = $conn->prepare("
        INSERT INTO categories
        (
            name,
            slug,
            description,
            image,
            parent_id,
            sort_order,
            status,
            created_at
        )
        VALUES
        (
            ?, ?, ?, ?, ?, ?, ?, NOW()
        )
    ");

    $stmt->execute([
        $name,
        $slug,
        $description,
        $image,
        $parent_id,
        $sort_order,
        $status
    ]);

    $_SESSION['success'] = "Thêm danh mục thành công";

    header("Location: admin.php?url=categories");
    exit;
}

/* ================= UPDATE ================= */
function handleUpdateCategory() {

    if (
        $_SERVER['REQUEST_METHOD'] !== 'POST'
        || ($_POST['action'] ?? '') !== 'edit'
    ) {
        return;
    }

    $conn = getDB();

    $id = (int)($_POST['id'] ?? 0);

    $name = trim($_POST['name'] ?? '');

    if ($id <= 0 || empty($name)) {

        $_SESSION['error'] = "Dữ liệu không hợp lệ";

        header("Location: admin.php?url=categories");
        exit;
    }

    $slug = !empty($_POST['slug'])
        ? createSlug($_POST['slug'])
        : createSlug($name);

    $description = trim($_POST['description'] ?? '');

    $parent_id = !empty($_POST['parent_id'])
        ? (int)$_POST['parent_id']
        : null;

    $sort_order = (int)($_POST['sort_order'] ?? 0);

    $status = (int)($_POST['status'] ?? 1);

    $old = $conn->prepare("
        SELECT image
        FROM categories
        WHERE id = ?
    ");

    $old->execute([$id]);

    $oldData = $old->fetch(PDO::FETCH_ASSOC);

    $image = $oldData['image'] ?? null;

    if (!empty($_FILES['image']['name'])) {

        $image = uploadCategoryImage($_FILES['image']);

    }

    $stmt = $conn->prepare("
        UPDATE categories
        SET
            name = ?,
            slug = ?,
            description = ?,
            image = ?,
            parent_id = ?,
            sort_order = ?,
            status = ?,
            updated_at = NOW()
        WHERE id = ?
    ");

    $stmt->execute([
        $name,
        $slug,
        $description,
        $image,
        $parent_id,
        $sort_order,
        $status,
        $id
    ]);

    $_SESSION['success'] = "Cập nhật thành công";

    header("Location: admin.php?url=categories");
    exit;
}

/* ================= SOFT DELETE ================= */
function handleSoftDeleteCategory() {

    if (!isset($_GET['soft_delete'])) {
        return;
    }

    $id = (int)$_GET['soft_delete'];

    $conn = getDB();

    $conn->prepare("
        UPDATE categories
        SET status = 0
        WHERE id = ?
    ")->execute([$id]);

    $_SESSION['success'] = "Đã vô hiệu hóa danh mục";

    header("Location: admin.php?url=categories");
    exit;
}

/* ================= RESTORE ================= */
function handleRestoreCategory() {

    if (!isset($_GET['restore'])) {
        return;
    }

    $id = (int)$_GET['restore'];

    $conn = getDB();

    $conn->prepare("
        UPDATE categories
        SET status = 1
        WHERE id = ?
    ")->execute([$id]);

    $_SESSION['success'] = "Đã khôi phục danh mục";

    header("Location: admin.php?url=categories");
    exit;
}

/* ================= HARD DELETE ================= */
function handleHardDeleteCategory() {

    if (!isset($_GET['hard_delete'])) {
        return;
    }

    $id = (int)$_GET['hard_delete'];

    $conn = getDB();

    try {

        $conn->beginTransaction();

        $cat = $conn->prepare("
            SELECT image
            FROM categories
            WHERE id = ?
        ");

        $cat->execute([$id]);

        $catData = $cat->fetch(PDO::FETCH_ASSOC);

        $products = $conn->prepare("
            SELECT id
            FROM products
            WHERE category_id = ?
        ");

        $products->execute([$id]);

        $productIds = $products->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($productIds)) {

            $in = implode(',', array_fill(0, count($productIds), '?'));

            $conn->prepare("
                DELETE FROM cart_items
                WHERE product_id IN ($in)
            ")->execute($productIds);

            $conn->prepare("
                DELETE FROM product_variants
                WHERE product_id IN ($in)
            ")->execute($productIds);

            $conn->prepare("
                DELETE FROM product_toppings
                WHERE product_id IN ($in)
            ")->execute($productIds);

            $conn->prepare("
                DELETE FROM order_items
                WHERE product_id IN ($in)
            ")->execute($productIds);

            $conn->prepare("
                DELETE FROM products
                WHERE category_id = ?
            ")->execute([$id]);
        }

        $conn->prepare("
            DELETE FROM categories
            WHERE parent_id = ?
        ")->execute([$id]);

        $conn->prepare("
            DELETE FROM categories
            WHERE id = ?
        ")->execute([$id]);

        if (!empty($catData['image'])) {

            $img = __DIR__ . '/../../../public/assets/img/category-item/' . $catData['image'];

            if (file_exists($img)) {
                unlink($img);
            }
        }

        $conn->commit();

        $_SESSION['success'] = "Xóa vĩnh viễn thành công";

    } catch (Exception $e) {

        $conn->rollBack();

        $_SESSION['error'] = "Lỗi: " . $e->getMessage();
    }

    header("Location: admin.php?url=categories");
    exit;
}
