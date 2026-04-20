<?php
// Nhận dữ liệu từ controller
$success = getFlash('success');
$error = getFlash('error');
?>

<div class="container-fluid">

    <!-- TITLE -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 text-gray-800">Danh sách món ăn</h1>

        <?php if ($is_admin): ?>
            <a href="admin.php?url=product-add" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Thêm món
            </a>
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

                <input type="text" name="search"
                    class="form-control mr-2"
                    placeholder="Tìm theo tên..."
                    value="<?= htmlspecialchars($filters['search'] ?? '') ?>">

                <select name="category_id" class="form-control mr-2">
                    <option value="0">-- Tất cả danh mục --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"
                            <?= ($filters['category_id'] ?? 0) == $cat['id'] ? 'selected' : '' ?>>
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

                <a href="admin.php?url=products" class="btn btn-secondary ml-2">
                    Reset
                </a>

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
                            <tr>
                                <td colspan="8" class="text-center text-muted">
                                    Không có dữ liệu
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($products as $item): ?>
                            <tr>
                                <td><?= $item['id'] ?></td>

                                <td>
                                    <?php if (!empty($item['image'])): ?>
                                        <?php
                                            // Đảm bảo $base có sẵn (ví dụ: $base = '/DoAn/DoAnTotNghiep/public/')
                                            $imageUrl = $base . ltrim($item['image'], '/');
                                        ?>
                                        <img src="<?= $base ?>/assets/img/product/<?= htmlspecialchars($item['image']) ?>"
                                             alt="<?= htmlspecialchars($item['name']) ?>"
                                            width="50" height="50"
                                            style="object-fit:cover;border-radius:6px;">
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>

                                <td><?= htmlspecialchars($item['name']) ?></td>

                                <td>
                                    <?= htmlspecialchars($item['category_name'] ?? 'Chưa có') ?>
                                </td>

                                <td>
                                    <?= number_format($item['base_price'] ?? 0, 0, ',', '.') ?>đ
                                </td>

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

                                        <!-- EDIT -->
                                        <a href="#"
                                            class="btn btn-sm btn-primary btn-edit"
                                            data-id="<?= $item['id'] ?>">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <!-- SOFT DELETE / RESTORE -->
                                        <?php if ($item['status'] == 1): ?>
                                            <a href="admin.php?url=products&action=delete&id=<?= $item['id'] ?>"
                                               class="btn btn-sm btn-warning"
                                               onclick="return confirm('Vô hiệu hóa sản phẩm?')">
                                                <i class="fas fa-ban"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="admin.php?url=products&action=restore&id=<?= $item['id'] ?>"
                                               class="btn btn-sm btn-success"
                                               onclick="return confirm('Khôi phục sản phẩm?')">
                                                <i class="fas fa-undo"></i>
                                            </a>
                                        <?php endif; ?>

                                        <!-- HARD DELETE -->
                                        <a href="admin.php?url=products&action=hard_delete&id=<?= $item['id'] ?>"
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('⚠️ Xóa vĩnh viễn. Đồng ý?')">
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

                                    <a class="page-link" href="admin.php?<?= $query ?>">
                                        <?= $i ?>
                                    </a>

                            </li>
                        <?php endfor; ?>

                    </ul>
                </nav>
            <?php endif; ?>

        </div>
    </div>
</div>
<div class="modal fade" id="editModal">
  <div class="modal-dialog">
    <div class="modal-content" id="modalContent">
        <!-- nội dung sẽ load vào đây -->
    </div>
  </div>
</div>

<script>
$(document).ready(function(){

    $('.btn-edit').click(function(){
        let id = $(this).data('id');

        $('#modalContent').html('<div class="p-3">Loading...</div>');

        $('#modalContent').load(
            'admin.php?url=product-edit&id=' + id,
            function(){
                $('#editModal').modal('show');
            }
        );
    });

});
</script>
