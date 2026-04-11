<!-- resources/views/pages/admin/toppings.php -->
<div id="content-wrapper">
    <div id="content">

        <div class="container-fluid">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Quản lý topping</h1>
                <button class="btn btn-primary" data-toggle="modal" data-target="#addModal">
                    <i class="fas fa-plus"></i> Thêm topping
                </button>
            </div>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <!-- Modal thêm -->
            <div class="modal fade" id="addModal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST" action="admin.php?url=toppings">
                            <div class="modal-header">
                                <h5>Thêm topping</h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="action" value="add">
                                <div class="form-group">
                                    <label>Tên topping</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Giá</label>
                                    <input type="number" step="1000" name="price" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Trạng thái</label>
                                    <select name="status" class="form-control">
                                        <option value="1">Hoạt động</option>
                                        <option value="0">Ẩn</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Lưu</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Danh sách topping -->
            <div class="card shadow">
                <div class="card-header">Danh sách topping</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr><th>ID</th><th>Tên</th><th>Giá</th><th>Trạng thái</th><th>Hành động</th></tr>
                            </thead>
                            <tbody>
                                <?php if (empty($toppings)): ?>
                                    <tr><td colspan="5" class="text-center">Chưa có topping nào</td></tr>
                                <?php else: ?>
                                    <?php foreach ($toppings as $t): ?>
                                        <tr>
                                            <td><?= $t['id'] ?></td>
                                            <td><?= htmlspecialchars($t['name']) ?></td>
                                            <td><?= number_format($t['price']) ?>đ</td>
                                            <td>
                                                <?= $t['status'] == 1
                                                    ? '<span class="badge badge-success">Hiện</span>'
                                                    : '<span class="badge badge-secondary">Ẩn</span>' ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editModal<?= $t['id'] ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php if ($t['status'] == 1): ?>
                                                    <a href="admin.php?url=topping-delete&id=<?= $t['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Vô hiệu hóa?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <a href="admin.php?url=topping-restore&id=<?= $t['id'] ?>" class="btn btn-sm btn-success">
                                                        <i class="fas fa-undo-alt"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>

                                        <!-- Modal sửa -->
                                        <div class="modal fade" id="editModal<?= $t['id'] ?>">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST" action="admin.php?url=toppings">
                                                        <div class="modal-header">
                                                            <h5>Sửa topping</h5>
                                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="action" value="edit">
                                                            <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                                            <div class="form-group">
                                                                <label>Tên topping</label>
                                                                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($t['name']) ?>" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Giá</label>
                                                                <input type="number" step="1000" name="price" class="form-control" value="<?= $t['price'] ?>" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Trạng thái</label>
                                                                <select name="status" class="form-control">
                                                                    <option value="1" <?= $t['status'] == 1 ? 'selected' : '' ?>>Hoạt động</option>
                                                                    <option value="0" <?= $t['status'] == 0 ? 'selected' : '' ?>>Ẩn</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-primary">Lưu</button>
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
