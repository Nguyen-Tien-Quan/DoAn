<?php
$is_admin = ($_SESSION['user']['role_id'] ?? 0) === 1;

?>

<div id="content-wrapper" class="d-flex flex-column">
    <div id="content">

        <div class="container-fluid">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">📏 Quản lý Size / Biến thể</h1>
                <?php if ($is_admin): ?>
                    <button class="btn btn-primary" data-toggle="modal" data-target="#addVariantModal">
                        <i class="fas fa-plus"></i> Thêm size
                    </button>
                <?php endif; ?>
            </div>

            <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

            <!-- Modal Thêm -->
            <?php if ($is_admin): ?>
            <div class="modal fade" id="addVariantModal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST" action="admin.php?url=variants">
                            <div class="modal-header">
                                <h5 class="modal-title">Thêm size mới</h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="action" value="add">
                                <div class="form-group">
                                    <label>Chọn món ăn <span class="text-danger">*</span></label>
                                    <select name="product_id" class="form-control" required>
                                        <option value="">-- Chọn món --</option>
                                        <?php foreach ($products as $p): ?>
                                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Tên size <span class="text-danger">*</span></label>
                                    <input type="text" name="variant_name" class="form-control" placeholder="S, M, L, XL..." required>
                                </div>
                                <div class="form-group">
                                    <label>Giá <span class="text-danger">*</span></label>
                                    <input type="number" step="1000" name="price" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Tồn kho</label>
                                    <input type="number" name="stock_quantity" class="form-control" value="0">
                                </div>
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

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Danh sách Size</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Sản phẩm</th>
                                    <th>Tên Size</th>
                                    <th>Giá</th>
                                    <th>Tồn kho</th>
                                    <th>Trạng thái</th>
                                    <?php if ($is_admin): ?><th>Hành động</th><?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($variants as $v): ?>
                                <tr>
                                    <td><?= $v['id'] ?></td>
                                    <td><?= htmlspecialchars($v['product_name'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($v['variant_name']) ?></td>
                                    <td><?= number_format($v['price']) ?>₫</td>
                                    <td><?= (int)$v['stock_quantity'] ?></td>
                                    <td>
                                        <?= $v['status'] == 1
                                            ? '<span class="badge badge-success">Hoạt động</span>'
                                            : '<span class="badge badge-secondary">Đã khóa</span>' ?>
                                    </td>
                                    <?php if ($is_admin): ?>
                                    <td style="white-space: nowrap;">
                                        <button class="btn btn-sm btn-primary"
                                                data-toggle="modal"
                                                data-target="#editModal<?= $v['id'] ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <?php if ($v['status'] == 1): ?>
                                            <a href="admin.php?url=variants&soft_delete=<?= $v['id'] ?>"
                                               class="btn btn-sm btn-warning"
                                               onclick="return confirm('Vô hiệu hóa size này?')">
                                                <i class="fas fa-ban"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="admin.php?url=variants&restore=<?= $v['id'] ?>"
                                               class="btn btn-sm btn-success"
                                               onclick="return confirm('Khôi phục size này?')">
                                                <i class="fas fa-undo-alt"></i>
                                            </a>
                                        <?php endif; ?>

                                        <a href="admin.php?url=variants&hard_delete=<?= $v['id'] ?>"
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('XÓA VĨNH VIỄN size này?')">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </td>
                                    <?php endif; ?>
                                </tr>

                                <!-- Modal Sửa -->
                                <?php if ($is_admin): ?>
                                <div class="modal fade" id="editModal<?= $v['id'] ?>">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="admin.php?url=variants">
                                                <input type="hidden" name="action" value="edit">
                                                <input type="hidden" name="id" value="<?= $v['id'] ?>">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Sửa Size</h5>
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label>Chọn món ăn</label>
                                                        <select name="product_id" class="form-control" required>
                                                            <?php foreach ($products as $p): ?>
                                                                <option value="<?= $p['id'] ?>" <?= $p['id'] == $v['product_id'] ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($p['name']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Tên size</label>
                                                        <input type="text" name="variant_name" class="form-control"
                                                               value="<?= htmlspecialchars($v['variant_name']) ?>" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Giá</label>
                                                        <input type="number" step="1000" name="price" class="form-control"
                                                               value="<?= $v['price'] ?>" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Tồn kho</label>
                                                        <input type="number" name="stock_quantity" class="form-control"
                                                               value="<?= (int)$v['stock_quantity'] ?>">
                                                    </div>
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
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- PHÂN TRANG (thêm mới) -->
                    <?php if ($totalPages > 1): ?>
                    <nav class="mt-3">
                        <ul class="pagination justify-content-center">
                            <?php
                            // Giữ lại các tham số filter hiện tại (nếu có)
                            $queryParams = $_GET;
                            unset($queryParams['url']); // url đã được thêm riêng
                            ?>
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                    <a class="page-link" href="admin.php?<?= http_build_query(array_merge($queryParams, ['url' => 'variants', 'page' => $i])) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                    <!-- KẾT THÚC PHÂN TRANG -->

                </div>
            </div>
        </div>
    </div>
</div>
