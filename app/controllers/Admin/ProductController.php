<?php
require_once __DIR__ . '/../../../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ================== GET ALL PRODUCTS (có phân trang + filter) ================== */
function getAllProducts($page = 1, $limit = 10, $filters = []) {
    $conn = getDB();
    $offset = ($page - 1) * $limit;

    $search      = $filters['search'] ?? '';
    $category_id = $filters['category_id'] ?? 0;
    $status      = $filters['status'] ?? -1;

    $where = " WHERE 1=1 ";
    $params = [];

    if ($search !== '') {
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
    $countSql = "SELECT COUNT(*) FROM products p $where";
    $stmt = $conn->prepare($countSql);
    $stmt->execute($params);
    $total = $stmt->fetchColumn();

    // DATA
    $sql = "SELECT p.*, c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            $where
            ORDER BY p.id ASC
            LIMIT $limit OFFSET $offset";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    return [
        'data'        => $stmt->fetchAll(),
        'total'       => $total,
        'totalPages'  => ceil($total / $limit),
        'currentPage' => $page
    ];
}

/* ================== EDIT PRODUCT (CHỈ XỬ LÝ LOGIC) ================== */
function editProduct() {
    $conn = getDB();
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($id <= 0) {
        $_SESSION['error'] = "ID sản phẩm không hợp lệ";
        header("Location: admin.php?url=products");
        exit;
    }

    // Lấy thông tin sản phẩm
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if (!$product) {
        $_SESSION['error'] = "Không tìm thấy sản phẩm";
        header("Location: admin.php?url=products");
        exit;
    }

    $categories = $conn->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();

    // Lấy variants hiện tại (chỉ những cái đang hoạt động)
    $stmt = $conn->prepare("SELECT * FROM product_variants WHERE product_id = ? AND status = 1 ORDER BY id");
    $stmt->execute([$id]);
    $variants = $stmt->fetchAll();

    $error = '';
    $success = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $category_id = (int)($_POST['category_id'] ?? 0);
        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $base_price  = (float)($_POST['base_price'] ?? 0);
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $status      = (int)($_POST['status'] ?? 1);

        $image_path = $product['image'] ?? '';

        // Upload ảnh mới
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['jpg','jpeg','png','gif','webp'];
            $ext = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));

            if (in_array($ext, $allowed)) {
                $uploadDir = __DIR__ . '/../../../public/uploads/products/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

                $newName = uniqid('prod_') . '.' . $ext;
                $dest = $uploadDir . $newName;

                if (move_uploaded_file($_FILES['image_file']['tmp_name'], $dest)) {
                    // Xóa ảnh cũ nếu tồn tại
                    if ($image_path && strpos($image_path, 'uploads/products/') === 0 && file_exists(__DIR__ . '/../../../public/' . $image_path)) {
                        unlink(__DIR__ . '/../../../public/' . $image_path);
                    }
                    $image_path = 'uploads/products/' . $newName;
                } else {
                    $error = "Tải ảnh lên thất bại";
                }
            } else {
                $error = "Định dạng ảnh không hợp lệ";
            }
        } elseif (!empty($_POST['image_url'])) {
            $image_path = trim($_POST['image_url']);
        }

        if (empty($error)) {
            if (empty($name)) {
                $error = "Tên sản phẩm không được để trống";
            } elseif ($category_id <= 0) {
                $error = "Vui lòng chọn danh mục";
            } elseif ($base_price <= 0) {
                $error = "Giá phải lớn hơn 0";
            } else {
                $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));

                // Kiểm tra slug trùng
                $check = $conn->prepare("SELECT id FROM products WHERE slug = ? AND id != ?");
                $check->execute([$slug, $id]);
                if ($check->fetch()) {
                    $slug .= '-' . time();
                }

                $sql = "UPDATE products SET category_id=?, name=?, slug=?, description=?,
                        base_price=?, image=?, is_featured=?, status=? WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$category_id, $name, $slug, $description, $base_price, $image_path, $is_featured, $status, $id]);

                // --- Xử lý variants an toàn (không xóa cứng) ---
                // Bước 1: Đánh dấu tất cả variants hiện có của sản phẩm là không hoạt động (status = 0)
                $conn->prepare("UPDATE product_variants SET status = 0 WHERE product_id = ?")->execute([$id]);

                // Bước 2: Duyệt danh sách variant gửi lên, cập nhật hoặc thêm mới
                if (!empty($_POST['variant_names']) && is_array($_POST['variant_names'])) {
                    foreach ($_POST['variant_names'] as $i => $vName) {
                        $vName = trim($vName);
                        if ($vName !== '') {
                            $vPrice = isset($_POST['variant_prices'][$i]) ? (float)$_POST['variant_prices'][$i] : $base_price;
                            // Kiểm tra xem variant với tên này đã tồn tại chưa (kể cả đã bị ẩn)
                            $checkVar = $conn->prepare("SELECT id FROM product_variants WHERE product_id = ? AND variant_name = ?");
                            $checkVar->execute([$id, $vName]);
                            $varId = $checkVar->fetchColumn();
                            if ($varId) {
                                // Cập nhật giá và kích hoạt lại
                                $conn->prepare("UPDATE product_variants SET price = ?, status = 1 WHERE id = ?")->execute([$vPrice, $varId]);
                            } else {
                                // Thêm mới
                                $conn->prepare("INSERT INTO product_variants (product_id, variant_name, price, status) VALUES (?,?,?,1)")->execute([$id, $vName, $vPrice]);
                            }
                        }
                    }
                }

                $_SESSION['success'] = "✅ Cập nhật sản phẩm thành công!";
            }
        }
    }

    // Nếu là AJAX request, trả về view modal
    $isAjax = isset($_GET['ajax']) && $_GET['ajax'] == 1;
    if ($isAjax) {
        if ($success) {
            echo '<div class="alert alert-success m-3">' . htmlspecialchars($success) . '</div>';
            echo '<script>setTimeout(function(){ location.reload(); }, 1000);</script>';
            exit;
        }
        if ($error) {
            echo '<div class="alert alert-danger m-3">' . htmlspecialchars($error) . '</div>';
        }
        // Load view modal
        $viewData = compact('product', 'categories', 'variants', 'error', 'success');
        extract($viewData);
        require __DIR__ . '/../resources/views/pages/admin/product-edit-modal.php';
        exit;
    }


    header("Location: admin.php?url=products");
    exit;
}

function handleAddProduct() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

    $conn = getDB();

    $category_id = (int)($_POST['category_id'] ?? 0);
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $base_price  = (float)($_POST['base_price'] ?? 0);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $status      = (int)($_POST['status'] ?? 1);

    $image_path = '';

    // ================= UPLOAD ẢNH =================
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {

        $allowed = ['jpg','jpeg','png','gif','webp'];
        $ext = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {

            // 📌 THƯ MỤC ĐÚNG
            $uploadDir = __DIR__ . '/../../../public/assets/img/product/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $newName = 'prod_' . time() . '_' . uniqid() . '.' . $ext;
            $dest = $uploadDir . $newName;

            if (move_uploaded_file($_FILES['image_file']['tmp_name'], $dest)) {
                // 📌 LƯU PATH CHUẨN (KHÔNG có public)
                $image_path = $newName;
            }
        }
    }

    // ================= URL ẢNH =================
    if (!empty($_POST['image_url'])) {
        $image_path = trim($_POST['image_url']);
    }

    // ================= ẢNH MẶC ĐỊNH =================
    if (empty($image_path)) {
        $image_path = 'assets/img/product/product-default.png';
    }

    // ================= VALIDATE =================
    if (empty($name) || $category_id <= 0 || $base_price <= 0) {
        $_SESSION['error'] = "Vui lòng nhập đầy đủ thông tin hợp lệ";
        header("Location: admin.php?url=products");
        exit;
    }

    // ================= SLUG =================
    $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));

    $check = $conn->prepare("SELECT id FROM products WHERE slug = ?");
    $check->execute([$slug]);
    if ($check->fetch()) {
        $slug .= '-' . time();
    }

    try {
        $conn->beginTransaction();

        // ================= INSERT PRODUCT =================
        $stmt = $conn->prepare("
            INSERT INTO products
            (category_id, name, slug, description, base_price, image, is_featured, status)
            VALUES (?,?,?,?,?,?,?,?)
        ");

        $stmt->execute([
            $category_id,
            $name,
            $slug,
            $description,
            $base_price,
            $image_path,
            $is_featured,
            $status
        ]);

        $product_id = $conn->lastInsertId();

        // ================= INSERT VARIANTS =================
        if (!empty($_POST['variant_names'])) {
            foreach ($_POST['variant_names'] as $i => $vName) {

                $vName = trim($vName);

                if ($vName !== '') {

                    $vPrice = isset($_POST['variant_prices'][$i]) && $_POST['variant_prices'][$i] > 0
                        ? (float)$_POST['variant_prices'][$i]
                        : $base_price;

                    $conn->prepare("
                        INSERT INTO product_variants
                        (product_id, variant_name, price, status)
                        VALUES (?,?,?,1)
                    ")->execute([$product_id, $vName, $vPrice]);
                }
            }
        }

        $conn->commit();

        $_SESSION['success'] = "✅ Thêm sản phẩm thành công!";
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['error'] = "Lỗi: " . $e->getMessage();
    }

    header("Location: admin.php?url=products");
    exit;
}
/* ================== SOFT DELETE ================== */
function handleDeleteProduct() {
    if (!isset($_GET['id'])) return;
    $conn = getDB();
    $id = (int)$_GET['id'];

    $conn->prepare("UPDATE products SET status = 0 WHERE id = ?")->execute([$id]);
    $_SESSION['success'] = "Đã vô hiệu hóa sản phẩm";
    header("Location: admin.php?url=products");
    exit;
}

/* ================== RESTORE ================== */
function handleRestoreProduct() {
    if (!isset($_GET['id'])) return;
    $conn = getDB();
    $id = (int)$_GET['id'];

    $conn->prepare("UPDATE products SET status = 1 WHERE id = ?")->execute([$id]);
    $_SESSION['success'] = "Đã khôi phục sản phẩm";
    header("Location: admin.php?url=products");
    exit;
}

/* ================== HARD DELETE ================== */
function handleHardDeleteProduct() {
    if (!isset($_GET['id'])) return;
    $conn = getDB();
    $id = (int)$_GET['id'];

    try {
        $conn->beginTransaction();

        $conn->prepare("DELETE FROM cart_items WHERE product_id = ?")->execute([$id]);
        $conn->prepare("DELETE FROM product_variants WHERE product_id = ?")->execute([$id]);
        $conn->prepare("DELETE FROM product_toppings WHERE product_id = ?")->execute([$id]);
        $conn->prepare("DELETE FROM order_items WHERE product_id = ?")->execute([$id]);
        $conn->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);

        $conn->commit();
        $_SESSION['success'] = "Đã xóa vĩnh viễn sản phẩm";
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['error'] = "Xóa thất bại: " . $e->getMessage();
    }

    header("Location: admin.php?url=products");
    exit;
}

/* ================== ĐẢM BẢO BẢNG TỒN TẠI ================== */
function ensureVariantTable() {
    $conn = getDB();
    try {
        $conn->exec("ALTER TABLE product_variants ADD COLUMN IF NOT EXISTS stock_quantity INT DEFAULT 0");
        $conn->exec("ALTER TABLE product_variants ADD COLUMN IF NOT EXISTS status TINYINT DEFAULT 1");
    } catch (PDOException $e) {
        // Bảng đã có cột rồi thì bỏ qua
    }
}

/* ================== LẤY TẤT CẢ VARIANTS ================== */
function getAllVariants() {
    $conn = getDB();
    $sql = "SELECT v.*, p.name as product_name
            FROM product_variants v
            LEFT JOIN products p ON v.product_id = p.id
            ORDER BY v.id ASC";
    return $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

/* ================== LẤY DANH SÁCH SẢN PHẨM ĐỂ CHỌN ================== */
function getAllProductsForVariant() {
    $conn = getDB();
    return $conn->query("SELECT id, name FROM products WHERE status = 1 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Lấy danh sách variants có phân trang và tìm kiếm/lọc
 */
function getVariantsPaginated($page = 1, $limit = 10, $filters = []) {
    $conn = getDB();
    $offset = ($page - 1) * $limit;

    $where = " WHERE 1=1 ";
    $params = [];

    // Lọc theo tên sản phẩm hoặc tên size
    if (!empty($filters['search'])) {
        $where .= " AND (p.name LIKE ? OR v.variant_name LIKE ?) ";
        $params[] = "%{$filters['search']}%";
        $params[] = "%{$filters['search']}%";
    }

    // Lọc theo sản phẩm cụ thể
    if (!empty($filters['product_id'])) {
        $where .= " AND v.product_id = ? ";
        $params[] = (int)$filters['product_id'];
    }

    // Lọc theo trạng thái
    if (isset($filters['status']) && $filters['status'] != -1) {
        $where .= " AND v.status = ? ";
        $params[] = (int)$filters['status'];
    }

    // Đếm tổng
    $countSql = "SELECT COUNT(*) FROM product_variants v
                 LEFT JOIN products p ON v.product_id = p.id $where";
    $stmt = $conn->prepare($countSql);
    $stmt->execute($params);
    $total = $stmt->fetchColumn();

    // Lấy dữ liệu
    $sql = "SELECT v.*, p.name as product_name
            FROM product_variants v
            LEFT JOIN products p ON v.product_id = p.id
            $where
            ORDER BY v.id ASC
            LIMIT $limit OFFSET $offset";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    return [
        'data'        => $stmt->fetchAll(PDO::FETCH_ASSOC),
        'total'       => $total,
        'totalPages'  => ceil($total / $limit),
        'currentPage' => $page
    ];
}

/* ================== THÊM VARIANT ================== */
function handleAddVariant() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['action'] ?? '') !== 'add') return;

    $conn = getDB();
    $product_id   = (int)($_POST['product_id'] ?? 0);
    $variant_name = trim($_POST['variant_name'] ?? '');
    $price        = (float)($_POST['price'] ?? 0);
    $stock        = (int)($_POST['stock_quantity'] ?? 0);

    if ($product_id <= 0 || empty($variant_name) || $price <= 0) {
        $_SESSION['error'] = "Vui lòng nhập đầy đủ thông tin (món ăn, tên size, giá)";
    } else {
        $stmt = $conn->prepare("INSERT INTO product_variants (product_id, variant_name, price, stock_quantity, status) VALUES (?,?,?,?,1)");
        $stmt->execute([$product_id, $variant_name, $price, $stock]);
        $_SESSION['success'] = "✅ Thêm size thành công!";
    }

    header("Location: admin.php?url=variants");
    exit;
}

function handleUpdateVariant() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['action'] ?? '') !== 'edit') return;

    $conn = getDB();
    $id           = (int)($_POST['id'] ?? 0);
    $product_id   = (int)($_POST['product_id'] ?? 0);
    $variant_name = trim($_POST['variant_name'] ?? '');
    $price        = (float)($_POST['price'] ?? 0);
    $stock        = (int)($_POST['stock_quantity'] ?? 0);

    if ($id <= 0 || $product_id <= 0 || empty($variant_name) || $price <= 0) {
        $_SESSION['error'] = "Dữ liệu không hợp lệ";
    } else {
        $stmt = $conn->prepare("UPDATE product_variants SET product_id=?, variant_name=?, price=?, stock_quantity=? WHERE id=?");
        $stmt->execute([$product_id, $variant_name, $price, $stock, $id]);
        $_SESSION['success'] = "✅ Cập nhật size thành công!";
    }

    header("Location: admin.php?url=variants");
    exit;
}

/* ================== VÔ HIỆU HÓA (SOFT DELETE) ================== */
function handleSoftDeleteVariant() {
    if (!isset($_GET['soft_delete'])) return;
    $id = (int)$_GET['soft_delete'];

    $conn = getDB();
    $conn->prepare("UPDATE product_variants SET status = 0 WHERE id = ?")->execute([$id]);

    $_SESSION['success'] = "✅ Đã vô hiệu hóa size";
    header("Location: admin.php?url=variants");
    exit;
}

/* ================== KHÔI PHỤC ================== */
function handleRestoreVariant() {
    if (!isset($_GET['restore'])) return;
    $id = (int)$_GET['restore'];

    $conn = getDB();
    $conn->prepare("UPDATE product_variants SET status = 1 WHERE id = ?")->execute([$id]);

    $_SESSION['success'] = "✅ Đã khôi phục size";
    header("Location: admin.php?url=variants");
    exit;
}

/* ================== XÓA VĨNH VIỄN ================== */
function handleHardDeleteVariant() {
    if (!isset($_GET['hard_delete'])) return;
    $id = (int)$_GET['hard_delete'];

    $conn = getDB();

    try {
        $conn->beginTransaction();
        $conn->prepare("DELETE FROM product_variants WHERE id = ?")->execute([$id]);
        $conn->commit();
        $_SESSION['success'] = "✅ Đã xóa vĩnh viễn size";
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['error'] = "Lỗi: " . $e->getMessage();
    }

    header("Location: admin.php?url=variants");
    exit;
}

function getCategoryOptions() {
    $conn = getDB();
    return $conn->query("SELECT id, name FROM categories")->fetchAll();
}

function ensureToppingTable() {
    $conn = getDB();
    try { $conn->exec("ALTER TABLE toppings ADD COLUMN status TINYINT DEFAULT 1"); } catch(PDOException $e) {}
}

/* ================== GET ALL ================== */
function getAllToppings() {
    $conn = getDB();
    return $conn->query("SELECT * FROM toppings ORDER BY id ASC")->fetchAll();
}

/* ================== ADD ================== */
function handleAddTopping() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['action'] ?? '') !== 'add') return;

    $conn = getDB();
    $name = trim($_POST['name']);
    $price = (float)$_POST['price'];
    $status = (int)$_POST['status'];

    if (!empty($name) && $price > 0) {
        $stmt = $conn->prepare("INSERT INTO toppings (name, price, status) VALUES (?,?,?)");
        $stmt->execute([$name, $price, $status]);
        $_SESSION['success'] = "Thêm topping thành công";
    } else {
        $_SESSION['error'] = "Vui lòng nhập đầy đủ thông tin";
    }

    header("Location: admin.php?url=toppings");
    exit;
}

/* ================== UPDATE ================== */
function handleUpdateTopping() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['action'] ?? '') !== 'edit') return;

    $conn = getDB();
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $price = (float)$_POST['price'];
    $status = (int)$_POST['status'];

    if ($id > 0 && !empty($name) && $price > 0) {
        $stmt = $conn->prepare("UPDATE toppings SET name=?, price=?, status=? WHERE id=?");
        $stmt->execute([$name, $price, $status, $id]);
        $_SESSION['success'] = "Cập nhật thành công";
    } else {
        $_SESSION['error'] = "Dữ liệu không hợp lệ";
    }

    header("Location: admin.php?url=toppings");
    exit;
}

/* ================== DELETE ================== */
function handleDeleteTopping() {
    if (!isset($_GET['delete'])) return;

    $conn = getDB();
    $id = (int)$_GET['delete'];

    $conn->prepare("UPDATE toppings SET status = 0 WHERE id=?")->execute([$id]);

    $_SESSION['success'] = "Đã vô hiệu hóa topping";
    header("Location: admin.php?url=toppings");
    exit;
}

/* ================== RESTORE ================== */
function handleRestoreTopping() {
    if (!isset($_GET['restore'])) return;

    $conn = getDB();
    $id = (int)$_GET['restore'];

    $conn->prepare("UPDATE toppings SET status = 1 WHERE id=?")->execute([$id]);

    $_SESSION['success'] = "Đã khôi phục topping";
    header("Location: admin.php?url=toppings");
    exit;
}

/* ================== HARD DELETE ================== */
function handleHardDeleteTopping() {
    if (!isset($_GET['hard_delete'])) return;

    $conn = getDB();
    $id = (int)$_GET['hard_delete'];

    $check = $conn->prepare("SELECT COUNT(*) FROM order_item_toppings WHERE topping_id=?");
    $check->execute([$id]);

    if ($check->fetchColumn() > 0) {
        $_SESSION['error'] = "Không thể xóa vì đã có trong đơn hàng";
    } else {
        $conn->prepare("DELETE FROM toppings WHERE id=?")->execute([$id]);
        $_SESSION['success'] = "Đã xóa vĩnh viễn";
    }

    header("Location: admin.php?url=toppings");
    exit;
}
