<?php
// Nhận dữ liệu từ controller
$success = $_SESSION['success'] ?? '';
$error   = $_SESSION['error']   ?? '';
unset($_SESSION['success'], $_SESSION['error']);

// Đảm bảo $base có sẵn
if (!isset($base)) {
    $base = '/DoAn/DoAnTotNghiep/public/';
}
?>

<div class="container-fluid">

    <!-- TITLE & ADD BUTTON -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 text-gray-800">Danh sách món ăn</h1>
        <?php if ($is_admin): ?>
            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addProductModal">
                <i class="fas fa-plus"></i> Thêm món
            </button>
        <?php endif; ?>
    </div>

    <!-- ALERT -->
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- FILTER -->
    <div class="card shadow mb-4">
        <div class="card-header">
            <form method="GET" class="form-inline">
                <input type="hidden" name="url" value="products">
                <input type="text" name="search" class="form-control mr-2" placeholder="Tìm theo tên..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                <select name="category_id" class="form-control mr-2">
                    <option value="0">-- Tất cả danh mục --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($filters['category_id'] ?? 0) == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select name="status" class="form-control mr-2">
                    <option value="-1">-- Tất cả trạng thái --</option>
                    <option value="1" <?= ($filters['status'] ?? -1) == 1 ? 'selected' : '' ?>>Đang bán</option>
                    <option value="0" <?= ($filters['status'] ?? -1) == 0 ? 'selected' : '' ?>>Ngừng bán</option>
                </select>
                <button class="btn btn-primary">Lọc</button>
                <a href="admin.php?url=products" class="btn btn-secondary ml-2">Reset</a>
            </form>
        </div>

        <!-- TABLE -->
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Ảnh</th>
                            <th>Tên món</th>
                            <th>Danh mục</th>
                            <th>Giá</th>
                            <th>Nổi bật</th>
                            <th>Trạng thái</th>
                            <?php if ($is_admin): ?>
                                <th width="180">Hành động</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr><td colspan="8" class="text-center text-muted">Không có dữ liệu</td></tr>
                        <?php endif; ?>

                        <?php foreach ($products as $item): ?>
                            <tr>
                                <td><?= $item['id'] ?></td>
                                <td>
                                    <?php if (!empty($item['image'])): ?>
                                        <?php
                                            $img = $item['image'] ?? '';

                                            // Nếu DB chỉ lưu tên file → tự thêm đường dẫn
                                            if (!empty($img) && strpos($img, 'assets/') !== 0) {
                                                $img = 'assets/img/product/' . $img;
                                            }

                                            // Nếu vẫn rỗng → ảnh mặc định
                                            if (empty($img)) {
                                                $img = 'assets/img/product/product-default.png';
                                            }
                                            ?>

                                        <img src="<?=  $base . $img ?>" width="50" height="50" style="object-fit:cover;border-radius:6px;">
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($item['name']) ?></td>
                                <td><?= htmlspecialchars($item['category_name'] ?? 'Chưa có') ?></td>
                                <td><?= number_format($item['base_price'] ?? 0, 0, ',', '.') ?>đ</td>
                                <td>
                                    <?php if ($item['is_featured'] == 1): ?>
                                        <span class="badge badge-warning">Nổi bật</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Thường</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($item['status'] == 1): ?>
                                        <span class="badge badge-success">Đang bán</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Ngừng bán</span>
                                    <?php endif; ?>
                                </td>
                                <?php if ($is_admin): ?>
                                    <td style="white-space: nowrap;">
                                        <!-- Edit button -->
                                        <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editProductModal<?= $item['id'] ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <!-- Soft delete / restore -->
                                        <?php if ($item['status'] == 1): ?>
                                            <a href="admin.php?url=products&action=delete&id=<?= $item['id'] ?>" class="btn btn-sm btn-warning" onclick="return confirm('Vô hiệu hóa sản phẩm?')">
                                                <i class="fas fa-ban"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="admin.php?url=products&action=restore&id=<?= $item['id'] ?>" class="btn btn-sm btn-success" onclick="return confirm('Khôi phục sản phẩm?')">
                                                <i class="fas fa-undo"></i>
                                            </a>
                                        <?php endif; ?>

                                        <!-- Hard delete -->
                                        <a href="admin.php?url=products&action=hard_delete&id=<?= $item['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('⚠️ Xóa vĩnh viễn. Đồng ý?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            <?php if ($totalPages > 1): ?>
                <nav class="mt-3">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                <?php
                                    $query = http_build_query([
                                        'url' => 'products',
                                        'page' => $i,
                                        'search' => $filters['search'] ?? '',
                                        'category_id' => $filters['category_id'] ?? 0,
                                        'status' => $filters['status'] ?? -1
                                    ]);
                                ?>
                                <a class="page-link" href="admin.php?<?= $query ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ==================== MODAL THÊM SẢN PHẨM ==================== -->
<?php if ($is_admin): ?>
<div class="modal fade" id="addProductModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" action="admin.php?url=product-add" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm món ăn mới</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Danh mục <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-control" required>
                            <option value="">-- Chọn danh mục --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tên món <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Mô tả</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Giá cơ bản <span class="text-danger">*</span></label>
                        <input type="number" step="1000" name="base_price" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Ảnh sản phẩm</label>
                        <input type="file" name="image_file" class="form-control-file" accept="image/*">
                        <small class="text-muted">Hoặc nhập URL</small>
                        <input type="text" name="image_url" class="form-control mt-1" placeholder="https://...">
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" name="is_featured" class="form-check-input" id="addFeatured">
                        <label class="form-check-label" for="addFeatured">Món nổi bật</label>
                    </div>
                    <div class="form-group">
                        <label>Trạng thái</label>
                        <select name="status" class="form-control">
                            <option value="1">Đang bán</option>
                            <option value="0">Ngừng bán</option>
                        </select>
                    </div>

                    <h5 class="mt-4">Các size (tùy chọn)</h5>
                    <div id="addVariantsContainer">
                        <div class="variant-row form-row mb-2">
                            <div class="col"><input type="text" name="variant_names[]" class="form-control" placeholder="Tên size (S, M, L...)"></div>
                            <div class="col"><input type="number" step="1000" name="variant_prices[]" class="form-control" placeholder="Giá"></div>
                            <div class="col-auto"><button type="button" class="btn btn-danger remove-variant">Xóa</button></div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-secondary mb-3" id="addVariantBtn">+ Thêm size</button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ==================== MODAL SỬA CHO TỪNG SẢN PHẨM ==================== -->
<?php if ($is_admin): ?>
    <?php foreach ($products as $item): ?>
        <?php
            // Lấy variants hiện có của sản phẩm (giả sử controller đã cung cấp biến $productVariants hoặc ta query tại đây)
            // Để đơn giản, ta sẽ query trực tiếp (cần đảm bảo có $conn)
            // Tuy nhiên trong view không nên query. Tốt nhất controller nên truyền vào mảng $productVariantsMap hoặc $item['variants'].
            // Ở đây tôi giả sử bạn đã join hoặc eager load variants và có $item['variants'] là mảng.
            $variants = $item['variants'] ?? [];
        ?>
        <div class="modal fade" id="editProductModal<?= $item['id'] ?>" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <form method="POST" action="admin.php?url=product-edit&id=<?= $item['id'] ?>" enctype="multipart/form-data">
                        <div class="modal-header">
                            <h5 class="modal-title">Sửa món ăn #<?= $item['id'] ?></h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Danh mục <span class="text-danger">*</span></label>
                                <select name="category_id" class="form-control" required>
                                    <option value="">-- Chọn danh mục --</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" <?= ($cat['id'] == $item['category_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Tên món <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($item['name']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Mô tả</label>
                                <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($item['description'] ?? '') ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Giá cơ bản <span class="text-danger">*</span></label>
                                <input type="number" step="1000" name="base_price" class="form-control" value="<?= $item['base_price'] ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Ảnh hiện tại</label><br>
                                <?php if (!empty($item['image'])): ?>
                                    <?php
                                        $img = $item['image'];
                                        $imgSrc = (strpos($img, 'http') === 0) ? $img : $base . ltrim($img, '/');
                                    ?>
                                    <img src="<?= $base ?>/assets/img/product/<?= basename($imgSrc) ?>" width="100" class="rounded mb-2"><br>
                                <?php else: ?>
                                    <span class="text-muted">Chưa có ảnh</span><br>
                                <?php endif; ?>
                                <label>Đổi ảnh (tải lên)</label>
                                <input type="file" name="image_file" class="form-control-file" accept="image/*">
                                <small class="text-muted">Hoặc nhập URL mới</small>
                                <input type="text" name="image_url" class="form-control mt-1" placeholder="https://...">
                            </div>
                            <div class="form-check mb-3">
                                <input type="checkbox" name="is_featured" class="form-check-input" id="editFeatured<?= $item['id'] ?>" <?= ($item['is_featured'] == 1) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="editFeatured<?= $item['id'] ?>">Món nổi bật</label>
                            </div>
                            <div class="form-group">
                                <label>Trạng thái</label>
                                <select name="status" class="form-control">
                                    <option value="1" <?= ($item['status'] == 1) ? 'selected' : '' ?>>Đang bán</option>
                                    <option value="0" <?= ($item['status'] == 0) ? 'selected' : '' ?>>Ngừng bán</option>
                                </select>
                            </div>

                            <h5 class="mt-4">Các size (tùy chọn)</h5>
                            <div id="editVariantsContainer<?= $item['id'] ?>">
                                <?php if (!empty($variants)): ?>
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
                                        <div class="col"><input type="number" step="1000" name="variant_prices[]" class="form-control" placeholder="Giá"></div>
                                        <div class="col-auto"><button type="button" class="btn btn-danger remove-variant">Xóa</button></div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <button type="button" class="btn btn-sm btn-secondary mb-3" data-product-id="<?= $item['id'] ?>" onclick="addVariantRow(this)">+ Thêm size</button>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-primary">Cập nhật</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<script>
// Xử lý thêm/xóa variant cho modal thêm sản phẩm
document.addEventListener('DOMContentLoaded', function() {
    // Modal Thêm
    const addContainer = document.getElementById('addVariantsContainer');
    const addBtn = document.getElementById('addVariantBtn');
    if (addBtn) {
        addBtn.addEventListener('click', function() {
            const newRow = document.createElement('div');
            newRow.className = 'variant-row form-row mb-2';
            newRow.innerHTML = `
                <div class="col"><input type="text" name="variant_names[]" class="form-control" placeholder="Tên size"></div>
                <div class="col"><input type="number" step="1000" name="variant_prices[]" class="form-control" placeholder="Giá"></div>
                <div class="col-auto"><button type="button" class="btn btn-danger remove-variant">Xóa</button></div>
            `;
            addContainer.appendChild(newRow);
            newRow.querySelector('.remove-variant').addEventListener('click', function() {
                newRow.remove();
            });
        });
        // Gắn sự kiện xóa cho các nút remove có sẵn
        addContainer.querySelectorAll('.remove-variant').forEach(btn => {
            btn.addEventListener('click', function() {
                btn.closest('.variant-row').remove();
            });
        });
    }

    // Modal Sửa (các nút thêm variant được gọi bằng onclick inline để dễ truyền product ID)
    window.addVariantRow = function(btn) {
        const productId = btn.getAttribute('data-product-id');
        const container = document.getElementById('editVariantsContainer' + productId);
        const newRow = document.createElement('div');
        newRow.className = 'variant-row form-row mb-2';
        newRow.innerHTML = `
            <div class="col"><input type="text" name="variant_names[]" class="form-control" placeholder="Tên size"></div>
            <div class="col"><input type="number" step="1000" name="variant_prices[]" class="form-control" placeholder="Giá"></div>
            <div class="col-auto"><button type="button" class="btn btn-danger remove-variant">Xóa</button></div>
        `;
        container.appendChild(newRow);
        newRow.querySelector('.remove-variant').addEventListener('click', function() {
            newRow.remove();
        });
    };

    // Gắn sự kiện xóa cho các nút remove có sẵn trong tất cả modal sửa
    document.querySelectorAll('[id^="editVariantsContainer"] .remove-variant').forEach(btn => {
        btn.addEventListener('click', function() {
            btn.closest('.variant-row').remove();
        });
    });
});
</script>
