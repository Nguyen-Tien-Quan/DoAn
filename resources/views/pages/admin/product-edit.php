<?php
require_once __DIR__ . '/includes/auth.php';
requireStaffOrAdmin();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

// Tự động tạo bảng nếu chưa có
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100),
        slug VARCHAR(150) UNIQUE,
        description TEXT,
        image VARCHAR(255),
        status TINYINT DEFAULT 1,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        category_id BIGINT UNSIGNED,
        name VARCHAR(150),
        slug VARCHAR(180) UNIQUE,
        description TEXT,
        base_price DECIMAL(12,2),
        image VARCHAR(255),
        is_featured TINYINT DEFAULT 0,
        status TINYINT DEFAULT 1,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL,
        FOREIGN KEY (category_id) REFERENCES categories(id)
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS product_variants (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        product_id BIGINT UNSIGNED,
        variant_name VARCHAR(100),
        price DECIMAL(12,2),
        stock_quantity INT DEFAULT 0,
        status TINYINT DEFAULT 1,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )");
} catch (PDOException $e) {}

// Thêm cột nếu thiếu
try { $pdo->exec("ALTER TABLE products ADD COLUMN image VARCHAR(255) NULL"); } catch(PDOException $e){}
try { $pdo->exec("ALTER TABLE products ADD COLUMN is_featured TINYINT DEFAULT 0"); } catch(PDOException $e){}
try { $pdo->exec("ALTER TABLE products ADD COLUMN status TINYINT DEFAULT 1"); } catch(PDOException $e){}
try { $pdo->exec("ALTER TABLE products ADD COLUMN slug VARCHAR(180) UNIQUE"); } catch(PDOException $e){}
try { $pdo->exec("ALTER TABLE products ADD COLUMN description TEXT NULL"); } catch(PDOException $e){}
try { $pdo->exec("ALTER TABLE categories ADD COLUMN status TINYINT DEFAULT 1"); } catch(PDOException $e){}

if (!is_dir('uploads/products')) mkdir('uploads/products', 0777, true);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: products.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) {
    header('Location: products.php');
    exit;
}

$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();
$variants = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY id");
$variants->execute([$id]);
$variants = $variants->fetchAll();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category_id = (int)($_POST['category_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $base_price = (float)($_POST['base_price'] ?? 0);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $status = (int)($_POST['status'] ?? 1);
    $image_path = $product['image'] ?? '';

    // Upload ảnh
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
        $allowed = ['jpg','jpeg','png','gif','webp'];
        $ext = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $new_name = uniqid() . '.' . $ext;
            $dest = 'uploads/products/' . $new_name;
            if (move_uploaded_file($_FILES['image_file']['tmp_name'], $dest)) {
                if (!empty($image_path) && file_exists($image_path) && strpos($image_path, 'uploads/products/') === 0) {
                    unlink($image_path);
                }
                $image_path = $dest;
            } else $error = "Upload ảnh thất bại.";
        } else $error = "Định dạng ảnh không hợp lệ.";
    } elseif (isset($_POST['image_url']) && !empty($_POST['image_url'])) {
        if (!empty($image_path) && file_exists($image_path) && strpos($image_path, 'uploads/products/') === 0) {
            unlink($image_path);
        }
        $image_path = trim($_POST['image_url']);
    }

    if (empty($error)) {
        if (empty($name)) $error = "Tên món không được trống";
        elseif ($category_id <= 0) $error = "Chọn danh mục";
        elseif ($base_price <= 0) $error = "Giá cơ bản phải lớn hơn 0";
        else {
            $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));
            $checkSlug = $pdo->prepare("SELECT id FROM products WHERE slug = ? AND id != ?");
            $checkSlug->execute([$slug, $id]);
            if ($checkSlug->fetch()) $slug .= '-' . uniqid();

            $sql = "UPDATE products SET category_id=?, name=?, slug=?, description=?, base_price=?, image=?, is_featured=?, status=? WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$category_id, $name, $slug, $description, $base_price, $image_path, $is_featured, $status, $id]);

            // Xử lý variants (size): xóa cũ, thêm mới
            $pdo->prepare("DELETE FROM product_variants WHERE product_id = ?")->execute([$id]);
            if (isset($_POST['variant_names']) && is_array($_POST['variant_names'])) {
                $v_names = $_POST['variant_names'];
                $v_prices = $_POST['variant_prices'] ?? [];
                for ($i = 0; $i < count($v_names); $i++) {
                    if (!empty($v_names[$i])) {
                        $price = isset($v_prices[$i]) && $v_prices[$i] !== '' ? (float)$v_prices[$i] : $base_price;
                        $ins = $pdo->prepare("INSERT INTO product_variants (product_id, variant_name, price) VALUES (?,?,?)");
                        $ins->execute([$id, $v_names[$i], $price]);
                    }
                }
            }

            $success = "Cập nhật món thành công!";
            // Refresh dữ liệu
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $product = $stmt->fetch();
            $variants = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY id");
            $variants->execute([$id]);
            $variants = $variants->fetchAll();
        }
    }
}
?>

<div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
        <?php require_once 'includes/topbar.php'; ?>
        <div class="container-fluid">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Sửa món ăn</h1>
                <a href="products.php" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Quay lại</a>
            </div>
            <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
            <div class="card shadow mb-4">
                <div class="card-header"><h6 class="m-0 font-weight-bold text-primary">Chỉnh sửa thông tin</h6></div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label>Danh mục <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-control" required>
                                <option value="">-- Chọn --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= ($cat['id'] == $product['category_id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Tên món <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Mô tả</label>
                            <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Giá cơ bản <span class="text-danger">*</span></label>
                            <input type="number" step="1000" name="base_price" class="form-control" value="<?= $product['base_price'] ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Ảnh hiện tại</label><br>
                            <?php if (!empty($product['image'])): ?>
                                <img src="<?= htmlspecialchars($product['image']) ?>" width="100" class="rounded mb-2"><br>
                            <?php else: ?>
                                <span class="text-muted">Chưa có ảnh</span><br>
                            <?php endif; ?>
                            <label>Đổi ảnh (tải lên)</label>
                            <input type="file" name="image_file" class="form-control-file" accept="image/*">
                            <small class="text-muted">Hoặc nhập URL mới</small>
                        </div>
                        <div class="form-group">
                            <label>Hoặc URL ảnh mới</label>
                            <input type="text" name="image_url" class="form-control" placeholder="https://...">
                        </div>
                        <div class="form-check mb-3">
                            <input type="checkbox" name="is_featured" class="form-check-input" id="featured" <?= (!empty($product['is_featured']) && $product['is_featured'] == 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="featured">Món nổi bật</label>
                        </div>
                        <div class="form-group">
                            <label>Trạng thái</label>
                            <select name="status" class="form-control">
                                <option value="1" <?= (isset($product['status']) && $product['status'] == 1) ? 'selected' : '' ?>>Đang bán</option>
                                <option value="0" <?= (isset($product['status']) && $product['status'] == 0) ? 'selected' : '' ?>>Ngừng bán</option>
                            </select>
                        </div>

                        <h5 class="mt-4">Các size (tùy chọn)</h5>
                        <div id="variants-container">
                            <?php if (count($variants) > 0): ?>
                                <?php foreach ($variants as $v): ?>
                                <div class="variant-row form-row mb-2">
                                    <div class="col"><input type="text" name="variant_names[]" class="form-control" placeholder="Tên size" value="<?= htmlspecialchars($v['variant_name']) ?>"></div>
                                    <div class="col"><input type="number" step="1000" name="variant_prices[]" class="form-control" placeholder="Giá" value="<?= $v['price'] ?>"></div>
                                    <div class="col-auto"><button type="button" class="btn btn-danger remove-variant">Xóa</button></div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                            <div class="variant-row form-row mb-2">
                                <div class="col"><input type="text" name="variant_names[]" class="form-control" placeholder="Tên size (S, M, L...)"></div>
                                <div class="col"><input type="number" step="1000" name="variant_prices[]" class="form-control" placeholder="Giá (để trống = giá cơ bản)"></div>
                                <div class="col-auto"><button type="button" class="btn btn-danger remove-variant">Xóa</button></div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="btn btn-sm btn-secondary mb-3" id="add-variant">+ Thêm size</button>

                        <button type="submit" class="btn btn-primary mt-3">Cập nhật</button>
                        <a href="products.php" class="btn btn-secondary mt-3">Hủy</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('add-variant').onclick = function(){
    let container = document.getElementById('variants-container');
    let newRow = document.createElement('div');
    newRow.className = 'variant-row form-row mb-2';
    newRow.innerHTML = '<div class="col"><input type="text" name="variant_names[]" class="form-control" placeholder="Tên size"></div><div class="col"><input type="number" step="1000" name="variant_prices[]" class="form-control" placeholder="Giá"></div><div class="col-auto"><button type="button" class="btn btn-danger remove-variant">Xóa</button></div>';
    container.appendChild(newRow);
    newRow.querySelector('.remove-variant').onclick = function(){ newRow.remove(); };
};
document.querySelectorAll('.remove-variant').forEach(btn => btn.onclick = function(){ btn.closest('.variant-row').remove(); });
</script>

<?php require_once 'includes/footer.php'; require_once 'includes/scripts.php'; ?>