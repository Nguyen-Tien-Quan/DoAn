<?php
require_once __DIR__ . '/includes/auth.php';
requireStaffOrAdmin();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

// Tự động thêm cột image nếu chưa có
try { $pdo->exec("ALTER TABLE products ADD COLUMN image VARCHAR(255) NULL"); } catch(PDOException $e){}
if (!is_dir('uploads/products')) mkdir('uploads/products', 0777, true);

$error = $success = '';
$categories = $pdo->query("SELECT id, name FROM categories WHERE status = 1 ORDER BY name")->fetchAll();
$allToppings = $pdo->query("SELECT id, name, price FROM toppings WHERE status = 1 ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category_id = (int)($_POST['category_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $base_price = (float)($_POST['base_price'] ?? 0);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $status = (int)($_POST['status'] ?? 1);
    $image_path = '';

    // Upload ảnh
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
        $allowed = ['jpg','jpeg','png','gif','webp'];
        $ext = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $new_name = uniqid() . '.' . $ext;
            $dest = 'uploads/products/' . $new_name;
            if (move_uploaded_file($_FILES['image_file']['tmp_name'], $dest)) $image_path = $dest;
            else $error = "Upload ảnh thất bại.";
        } else $error = "Định dạng ảnh không hợp lệ.";
    } elseif (!empty($_POST['image_url'])) {
        $image_path = trim($_POST['image_url']);
    }

    if (empty($error)) {
        if (empty($name)) $error = "Tên món không được trống";
        elseif ($category_id <= 0) $error = "Chọn danh mục";
        elseif ($base_price <= 0) $error = "Giá cơ bản phải lớn hơn 0";
        else {
            $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));
            $checkSlug = $pdo->prepare("SELECT id FROM products WHERE slug = ?");
            $checkSlug->execute([$slug]);
            if ($checkSlug->fetch()) $slug .= '-' . uniqid();

            $sql = "INSERT INTO products (category_id, name, slug, description, base_price, image, is_featured, status) VALUES (?,?,?,?,?,?,?,?)";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$category_id, $name, $slug, $description, $base_price, $image_path, $is_featured, $status])) {
                $product_id = $pdo->lastInsertId();

                // Xử lý size (variants) nếu có
                if (isset($_POST['variant_names']) && is_array($_POST['variant_names'])) {
                    $v_names = $_POST['variant_names'];
                    $v_prices = $_POST['variant_prices'] ?? [];
                    for ($i = 0; $i < count($v_names); $i++) {
                        if (!empty($v_names[$i])) {
                            $price = isset($v_prices[$i]) ? (float)$v_prices[$i] : $base_price;
                            $pdo->prepare("INSERT INTO product_variants (product_id, variant_name, price) VALUES (?,?,?)")->execute([$product_id, $v_names[$i], $price]);
                        }
                    }
                }
                // Xử lý topping mặc định cho sản phẩm
                if (isset($_POST['toppings']) && is_array($_POST['toppings'])) {
                    $topping_ids = $_POST['toppings'];
                    foreach ($topping_ids as $tid) {
                        $pdo->prepare("INSERT INTO product_toppings (product_id, topping_id) VALUES (?,?)")->execute([$product_id, $tid]);
                    }
                }
                $success = "Thêm món thành công!";
                $_POST = [];
            } else $error = "Lỗi khi thêm dữ liệu.";
        }
    }
}
?>

<div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
        <?php require_once 'includes/topbar.php'; ?>
        <div class="container-fluid">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Thêm món ăn</h1>
                <a href="products.php" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Quay lại</a>
            </div>
            <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
            <div class="card shadow mb-4">
                <div class="card-header"><h6 class="m-0 font-weight-bold text-primary">Thông tin món ăn</h6></div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group"><label>Danh mục <span class="text-danger">*</span></label><select name="category_id" class="form-control" required><option value="">-- Chọn --</option><?php foreach ($categories as $cat): ?><option value="<?= $cat['id'] ?>" <?= (isset($_POST['category_id']) && $_POST['category_id'] == $cat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option><?php endforeach; ?></select></div>
                        <div class="form-group"><label>Tên món <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required></div>
                        <div class="form-group"><label>Mô tả</label><textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea></div>
                        <div class="form-group"><label>Giá cơ bản <span class="text-danger">*</span></label><input type="number" step="1000" name="base_price" class="form-control" value="<?= htmlspecialchars($_POST['base_price'] ?? '') ?>" required></div>
                        <div class="form-group"><label>Ảnh món ăn</label><input type="file" name="image_file" class="form-control-file" accept="image/*"><small class="text-muted">Hoặc nhập URL</small></div>
                        <div class="form-group"><label>Hoặc URL ảnh</label><input type="text" name="image_url" class="form-control" value="<?= htmlspecialchars($_POST['image_url'] ?? '') ?>"></div>
                        <div class="form-check mb-3"><input type="checkbox" name="is_featured" class="form-check-input" id="featured" <?= isset($_POST['is_featured']) ? 'checked' : '' ?>><label class="form-check-label" for="featured">Món nổi bật</label></div>
                        <div class="form-group"><label>Trạng thái</label><select name="status" class="form-control"><option value="1" selected>Đang bán</option><option value="0">Ngừng bán</option></select></div>

                        <h5 class="mt-4">Các size (tùy chọn)</h5>
                        <div id="variants-container">
                            <div class="variant-row form-row mb-2">
                                <div class="col"><input type="text" name="variant_names[]" class="form-control" placeholder="Tên size (S, M, L...)"></div>
                                <div class="col"><input type="number" step="1000" name="variant_prices[]" class="form-control" placeholder="Giá (để trống = giá cơ bản)"></div>
                                <div class="col-auto"><button type="button" class="btn btn-danger remove-variant">Xóa</button></div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-secondary mb-3" id="add-variant">+ Thêm size</button>

                        <h5 class="mt-4">Topping có thể chọn cho món</h5>
                        <div class="row">
                            <?php foreach ($allToppings as $top): ?>
                            <div class="col-md-3"><div class="form-check"><input type="checkbox" name="toppings[]" value="<?= $top['id'] ?>" class="form-check-input" <?= (isset($_POST['toppings']) && in_array($top['id'], $_POST['toppings'])) ? 'checked' : '' ?>><label class="form-check-label"><?= htmlspecialchars($top['name']) ?> (+<?= number_format($top['price']) ?>đ)</label></div></div>
                            <?php endforeach; ?>
                        </div>

                        <button type="submit" class="btn btn-primary mt-3">Lưu món</button>
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