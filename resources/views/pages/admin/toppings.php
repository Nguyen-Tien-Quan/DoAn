<div id="content-wrapper">
<div id="content">

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="font-weight-bold text-dark">Quản lý topping</h4>

        <button class="btn btn-primary shadow-sm" data-toggle="modal" data-target="#addModal">
            <i class="fas fa-plus"></i> Thêm topping
        </button>
    </div>

    <!-- ALERT -->
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- TABLE -->
    <div class="card shadow border-0">
        <div class="card-body table-responsive">

            <table class="table table-hover align-middle text-center">
                <thead class="thead-light">
                    <tr>
                        <th>ID</th>
                        <th>Tên</th>
                        <th>Giá</th>
                        <th>Trạng thái</th>
                        <th width="200">Hành động</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($toppings as $t): ?>
                    <tr>
                        <td><?= $t['id'] ?></td>
                        <td><?= htmlspecialchars($t['name']) ?></td>
                        <td class="text-success font-weight-bold">
                            <?= number_format($t['price']) ?>đ
                        </td>

                        <td>
                            <?= $t['status'] == 1
                                ? '<span class="badge badge-success">Hiển thị</span>'
                                : '<span class="badge badge-secondary">Ẩn</span>' ?>
                        </td>

                        <td>
                            <!-- EDIT -->
                            <button class="btn btn-sm btn-info"
                                data-toggle="modal"
                                data-target="#editModal<?= $t['id'] ?>">
                                <i class="fas fa-edit"></i>
                            </button>

                            <!-- SOFT DELETE -->
                            <?php if ($t['status'] == 1): ?>
                                <a href="admin.php?url=toppings&delete=<?= $t['id'] ?>"
                                   class="btn btn-sm btn-warning"
                                   onclick="return confirm('Ẩn topping này?')">
                                   <i class="fas fa-ban"></i>
                                </a>
                            <?php else: ?>
                                <!-- RESTORE -->
                                <a href="admin.php?url=toppings&restore=<?= $t['id'] ?>"
                                   class="btn btn-sm btn-success"
                                   onclick="return confirm('Khôi phục topping?')">
                                   <i class="fas fa-undo"></i>
                                </a>
                            <?php endif; ?>

                            <!-- HARD DELETE -->
                            <a href="admin.php?url=toppings&hard_delete=<?= $t['id'] ?>"
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Xóa vĩnh viễn?')">
                               <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>

                    <!-- EDIT MODAL -->
                    <div class="modal fade" id="editModal<?= $t['id'] ?>">
                        <div class="modal-dialog">
                            <div class="modal-content">

                                <form method="POST" action="admin.php?url=toppings">
                                    <input type="hidden" name="action" value="edit">
                                    <input type="hidden" name="id" value="<?= $t['id'] ?>">

                                    <div class="modal-header">
                                        <h5>Sửa topping</h5>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>

                                    <div class="modal-body">
                                        <input class="form-control mb-2"
                                               name="name"
                                               value="<?= htmlspecialchars($t['name']) ?>"
                                               required>

                                        <input class="form-control mb-2"
                                               type="number"
                                               name="price"
                                               value="<?= $t['price'] ?>"
                                               required>

                                        <select class="form-control" name="status">
                                            <option value="1" <?= $t['status']==1?'selected':'' ?>>Hiển thị</option>
                                            <option value="0" <?= $t['status']==0?'selected':'' ?>>Ẩn</option>
                                        </select>
                                    </div>

                                    <div class="modal-footer">
                                        <button class="btn btn-primary">Lưu</button>
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


<!-- ADD MODAL -->
<div class="modal fade" id="addModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <form method="POST" action="admin.php?url=toppings">
                <input type="hidden" name="action" value="add">

                <div class="modal-header">
                    <h5>Thêm topping</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                    <input class="form-control mb-2"
                           name="name"
                           placeholder="Tên topping"
                           required>

                    <input class="form-control mb-2"
                           type="number"
                           name="price"
                           placeholder="Giá"
                           required>

                    <select class="form-control" name="status">
                        <option value="1">Hiển thị</option>
                        <option value="0">Ẩn</option>
                    </select>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary">Thêm</button>
                </div>

            </form>

        </div>
    </div>
</div>
