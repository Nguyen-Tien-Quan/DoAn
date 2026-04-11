<!-- resources/views/pages/admin/categories.php -->
<div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
        <div class="container-fluid">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Quản lý danh mục</h1>
                <button class="btn btn-primary" data-toggle="modal" data-target="#addModal">
                    <i class="fas fa-plus"></i> Thêm danh mục
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
                        <form method="POST" action="admin.php?url=categories">
                            <input type="hidden" name="action" value="add">
                            <div class="modal-header">
                                <h5>Thêm danh mục</h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Tên danh mục</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Mô tả</label>
                                    <textarea name="description" class="form-control" rows="3"></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Trạng thái</label>
                                    <select name="status" class="form-control">
                                        <option value="1">Hiển thị</option>
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

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Danh sách danh mục</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr><th>ID</th><th>Tên</th><th>Slug</th><th>Mô tả</th><th>Trạng thái</th><th>Hành động</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $cat): ?>
                                    <tr>
                                        <td><?= $cat['id'] ?></td>
                                        <td><?= htmlspecialchars($cat['name']) ?></td>
                                        <td><?= htmlspecialchars($cat['slug'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($cat['description'] ?? '') ?></td>
                                        <td>
                                            <?= $cat['status'] == 1
                                                ? '<span class="badge badge-success">Hiển thị</span>'
                                                : '<span class="badge badge-secondary">Ẩn</span>' ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editModal<?= $cat['id'] ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php if ($cat['status'] == 1): ?>
                                                <a href="admin.php?url=category-delete&id=<?= $cat['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Vô hiệu hóa?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="admin.php?url=category-restore&id=<?= $cat['id'] ?>" class="btn btn-sm btn-success">
                                                    <i class="fas fa-undo-alt"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>

                                    <!-- Modal sửa -->
                                    <div class="modal fade" id="editModal<?= $cat['id'] ?>">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST" action="admin.php?url=categories">
                                                    <input type="hidden" name="action" value="edit">
                                                    <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                                    <div class="modal-header">
                                                        <h5>Sửa danh mục</h5>
                                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label>Tên danh mục</label>
                                                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($cat['name']) ?>" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Mô tả</label>
                                                            <textarea name="description" class="form-control"><?= htmlspecialchars($cat['description'] ?? '') ?></textarea>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Trạng thái</label>
                                                            <select name="status" class="form-control">
                                                                <option value="1" <?= $cat['status'] == 1 ? 'selected' : '' ?>>Hiển thị</option>
                                                                <option value="0" <?= $cat['status'] == 0 ? 'selected' : '' ?>>Ẩn</option>
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
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
