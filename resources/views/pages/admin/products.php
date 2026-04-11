<div id="content-wrapper" class="d-flex flex-column">
    <div id="content">

        <div class="container-fluid">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Danh sách món ăn</h1>
                <a href="admin.php?url=product-add" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Thêm món</a>
            </div>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <form method="GET" action="admin.php" class="form-inline">
                        <input type="hidden" name="url" value="products">
                        <input type="text" name="search" class="form-control mr-2" placeholder="Tìm theo tên" value="<?= htmlspecialchars($search ?? '') ?>">
                        <select name="category_id" class="form-control mr-2">
                            <option value="0">-- Tất cả danh mục --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($category_id ?? 0) == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="status" class="form-control mr-2">
                            <option value="-1">-- Tất cả trạng thái --</option>
                            <option value="1" <?= ($status_filter ?? -1) == 1 ? 'selected' : '' ?>>Đang bán</option>
                            <option value="0" <?= ($status_filter ?? -1) == 0 ? 'selected' : '' ?>>Ngừng bán</option>
                        </select>
                        <button type="submit" class="btn btn-primary">Lọc</button>
                        <a href="admin.php?url=products" class="btn btn-secondary ml-2">Reset</a>
                    </form>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr><th>ID</th><th>Hình ảnh</th><th>Tên món</th><th>Danh mục</th><th>Giá cơ bản</th><th>Nổi bật</th><th>Trạng thái</th><th>Hành động</th>
                            </thead>
                            <tbody>
                                <?php if (empty($products)): ?>
                                    <tr><td colspan="8" class="text-center">Không có dữ liệu</td></tr>
                                <?php else: ?>
                                    <?php foreach ($products as $item): ?>
                                    <tr>
                                        <td><?= $item['id'] ?></td>
                                        <td>
                                            <?php if (!empty($item['image'])): ?>
                                                <img src="<?= htmlspecialchars($item['image']) ?>" width="50" height="50" class="rounded">
                                            <?php else: ?>
                                                —
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($item['name']) ?></td>
                                        <td><?= htmlspecialchars($item['category_name'] ?? 'Chưa phân loại') ?></td>
                                        <td><?= number_format($item['base_price'] ?? 0, 0, ',', '.') ?>đ</td>
                                        <td>
                                            <?= ($item['is_featured'] ?? 0) == 1 ? '<span class="badge badge-warning">Nổi bật</span>' : '<span class="badge badge-secondary">Thường</span>' ?>
                                        </td>
                                        <td>
                                            <?= ($item['status'] ?? 1) == 1 ? '<span class="badge badge-success">Đang bán</span>' : '<span class="badge badge-danger">Ngừng bán</span>' ?>
                                        </td>
                                        <td>
                                            <a href="admin.php?url=product-edit&id=<?= $item['id'] ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                                            <?php if (($item['status'] ?? 1) == 1): ?>
                                                <a href="javascript:void(0)" onclick="confirmDelete(<?= $item['id'] ?>)" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></a>
                                            <?php else: ?>
                                                <a href="admin.php?url=product-restore&id=<?= $item['id'] ?>" class="btn btn-sm btn-success" onclick="return confirm('Khôi phục món này?')"><i class="fas fa-undo-alt"></i></a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (isset($totalPages) && $totalPages > 1): ?>
                    <nav><ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == ($page ?? 1) ? 'active' : '' ?>">
                                <a class="page-link" href="admin.php?url=products&page=<?= $i ?>&search=<?= urlencode($search ?? '') ?>&category_id=<?= $category_id ?? 0 ?>&status=<?= $status_filter ?? -1 ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul></nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(id) {
    if (confirm('Vô hiệu hóa món ăn này?')) {
        window.location.href = 'admin.php?url=product-delete&id=' + id;
    }
}
</script>
