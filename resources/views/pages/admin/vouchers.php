

<div id="content-wrapper" class="d-flex flex-column">
    <div id="content">

        <div class="container-fluid">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Quản lý khuyến mãi</h1>
                <?php if ($is_admin): ?>
                    <button class="btn btn-primary" data-toggle="modal" data-target="#addModal"><i class="fas fa-plus"></i> Thêm mã</button>
                <?php endif; ?>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert"><?= htmlspecialchars($success) ?><button type="button" class="close" data-dismiss="alert">&times;</button></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert"><?= htmlspecialchars($error) ?><button type="button" class="close" data-dismiss="alert">&times;</button></div>
            <?php endif; ?>

            <!-- Bộ lọc -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <form method="GET" class="form-inline">
                        <input type="text" name="search" class="form-control mr-2" placeholder="Mã, tên" value="<?= htmlspecialchars($search) ?>">
                        <select name="discount_type" class="form-control mr-2">
                            <option value="">-- Loại --</option>
                            <option value="percent" <?= $type_filter == 'percent' ? 'selected' : '' ?>>Phần trăm</option>
                            <option value="fixed" <?= $type_filter == 'fixed' ? 'selected' : '' ?>>Tiền mặt</option>
                        </select>
                        <select name="status" class="form-control mr-2">
                            <option value="-1">-- Trạng thái --</option>
                            <option value="1" <?= $status_filter == 1 ? 'selected' : '' ?>>Hoạt động</option>
                            <option value="0" <?= $status_filter == 0 ? 'selected' : '' ?>>Vô hiệu</option>
                        </select>
                        <button type="submit" class="btn btn-primary">Lọc</button>
                        <a href="vouchers.php" class="btn btn-secondary ml-2">Reset</a>
                    </form>
                </div>
            </div>

            <!-- Danh sách voucher -->
            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Danh sách mã khuyến mãi</h6></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Mã</th>
                                    <th>Tên</th>
                                    <th>Loại</th>
                                    <th>Giá trị</th>
                                    <th>Đơn tối thiểu</th>
                                    <th>Giảm tối đa</th>
                                    <th>Ngày hiệu lực</th>
                                    <th>Lượt dùng</th>
                                    <th>Trạng thái</th>
                                    <?php if ($is_admin): ?>
                                        <th>Hành động</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($vouchers) == 0): ?>
                                    <tr>
                                        <td colspan="<?= $is_admin ? 11 : 10 ?>" class="text-center">Không có mã khuyến mãi nào</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($vouchers as $v): ?>
                                        <tr>
                                            <td><?= $v['id'] ?></td>
                                            <td><strong><?= htmlspecialchars($v['code']) ?></strong></td>
                                            <td><?= htmlspecialchars($v['name']) ?></td>
                                            <td><?= $v['discount_type'] == 'percent' ? 'Phần trăm' : 'Tiền mặt' ?></td>
                                            <td><?= $v['discount_type'] == 'percent' ? $v['discount_value'] . '%' : number_format($v['discount_value']) . 'đ' ?></td>
                                            <td><?= number_format($v['min_order_amount']) ?>đ</td>
                                            <td><?= number_format($v['max_discount_amount']) ?>đ</td>
                                            <td>
                                                <?php
                                                $start = $v['start_date'] ? date('d/m/Y H:i', strtotime($v['start_date'])) : '—';
                                                $end = $v['end_date'] ? date('d/m/Y H:i', strtotime($v['end_date'])) : '—';
                                                echo $start . ' → ' . $end;
                                                ?>
                                              </td>
                                            <td><?= (int)($v['used_count'] ?? 0) . ' / ' . ($v['usage_limit'] ? $v['usage_limit'] : '∞') ?></td>
                                            <td>
                                                <?php if ($v['status'] == 1): ?>
                                                    <span class="badge badge-success">Hoạt động</span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger">Vô hiệu</span>
                                                <?php endif; ?>
                                              </td>
                                            <?php if ($is_admin): ?>
                                                <td style="white-space: nowrap;">
                                                    <button class="btn btn-sm btn-primary"
                                                        data-toggle="modal"
                                                        data-target="#editModal<?= $v['id'] ?>"
                                                        title="Sửa">
                                                        <i class="fas fa-edit"></i>
                                                    </button>

                                                    <?php if ($v['status'] == 1): ?>
                                                        <a href="admin.php?url=voucher-delete&id=<?= $v['id'] ?>"
                                                        class="btn btn-sm btn-warning"
                                                        onclick="return confirm('Vô hiệu hóa mã này?')"
                                                        title="Vô hiệu hóa">
                                                            <i class="fas fa-ban"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="admin.php?url=voucher-restore&id=<?= $v['id'] ?>"
                                                        class="btn btn-sm btn-success"
                                                        onclick="return confirm('Khôi phục mã này?')"
                                                        title="Khôi phục">
                                                            <i class="fas fa-undo-alt"></i>
                                                        </a>
                                                    <?php endif; ?>

                                                    <a href="admin.php?url=voucher-hard-delete&id=<?= $v['id'] ?>"
                                                    class="btn btn-sm btn-danger"
                                                    onclick="return confirm('⚠️ XÓA VĨNH VIỄN! Mã này sẽ bị xóa khỏi hệ thống. Bạn chắc chắn?')"
                                                    title="Xóa vĩnh viễn">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </a>
                                                </td>
                                            <?php endif; ?>
                                        </tr>

                                        <?php if ($is_admin): ?>
                                        <!-- Modal sửa -->
                                        <div class="modal fade" id="editModal<?= $v['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <form method="POST">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Sửa mã khuyến mãi</h5>
                                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="action" value="edit">
                                                            <input type="hidden" name="id" value="<?= $v['id'] ?>">
                                                            <div class="form-row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group"><label>Mã</label><input type="text" name="code" class="form-control" value="<?= htmlspecialchars($v['code']) ?>" required></div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group"><label>Tên</label><input type="text" name="name" class="form-control" value="<?= htmlspecialchars($v['name']) ?>" required></div>
                                                                </div>
                                                            </div>
                                                            <div class="form-row">
                                                                <div class="col-md-4">
                                                                    <div class="form-group"><label>Loại</label>
                                                                        <select name="discount_type" class="form-control">
                                                                            <option value="percent" <?= $v['discount_type'] == 'percent' ? 'selected' : '' ?>>Phần trăm</option>
                                                                            <option value="fixed" <?= $v['discount_type'] == 'fixed' ? 'selected' : '' ?>>Tiền mặt</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="form-group"><label>Giá trị</label><input type="number" step="1000" name="discount_value" class="form-control" value="<?= $v['discount_value'] ?>" required></div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="form-group"><label>Đơn tối thiểu</label><input type="number" step="1000" name="min_order_amount" class="form-control" value="<?= $v['min_order_amount'] ?>"></div>
                                                                </div>
                                                            </div>
                                                            <div class="form-row">
                                                                <div class="col-md-4">
                                                                    <div class="form-group"><label>Giảm tối đa</label><input type="number" step="1000" name="max_discount_amount" class="form-control" value="<?= $v['max_discount_amount'] ?>"></div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="form-group"><label>Ngày bắt đầu</label><input type="datetime-local" name="start_date" class="form-control" value="<?= $v['start_date'] ? date('Y-m-d\TH:i', strtotime($v['start_date'])) : '' ?>"></div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="form-group"><label>Ngày kết thúc</label><input type="datetime-local" name="end_date" class="form-control" value="<?= $v['end_date'] ? date('Y-m-d\TH:i', strtotime($v['end_date'])) : '' ?>"></div>
                                                                </div>
                                                            </div>
                                                            <div class="form-row">
                                                                <div class="col-md-4">
                                                                    <div class="form-group"><label>Giới hạn lượt</label><input type="number" name="usage_limit" class="form-control" value="<?= $v['usage_limit'] ?>"></div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="form-group"><label>Đã dùng</label><input type="text" class="form-control" value="<?= (int)($v['used_count'] ?? 0) ?>" disabled></div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="form-group"><label>Trạng thái</label>
                                                                        <select name="status" class="form-control">
                                                                            <option value="1" <?= $v['status'] == 1 ? 'selected' : '' ?>>Hoạt động</option>
                                                                            <option value="0" <?= $v['status'] == 0 ? 'selected' : '' ?>>Vô hiệu</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
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
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                         </table>
                    </div>
                    <?php if ($totalPages > 1): ?>
                    <nav><ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&discount_type=<?= $type_filter ?>&status=<?= $status_filter ?>"><?= $i ?></a></li>
                        <?php endfor; ?>
                    </ul></nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($is_admin): ?>
<!-- Modal thêm -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm mã khuyến mãi</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="form-row">
                        <div class="col-md-6"><div class="form-group"><label>Mã</label><input type="text" name="code" class="form-control" required></div></div>
                        <div class="col-md-6"><div class="form-group"><label>Tên</label><input type="text" name="name" class="form-control" required></div></div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-4"><div class="form-group"><label>Loại</label><select name="discount_type" class="form-control"><option value="percent">Phần trăm</option><option value="fixed">Tiền mặt</option></select></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Giá trị</label><input type="number" step="1000" name="discount_value" class="form-control" required></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Đơn tối thiểu</label><input type="number" step="1000" name="min_order_amount" class="form-control" value="0"></div></div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-4"><div class="form-group"><label>Giảm tối đa</label><input type="number" step="1000" name="max_discount_amount" class="form-control" value="0"></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Ngày bắt đầu</label><input type="datetime-local" name="start_date" class="form-control"></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Ngày kết thúc</label><input type="datetime-local" name="end_date" class="form-control"></div></div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-4"><div class="form-group"><label>Giới hạn lượt</label><input type="number" name="usage_limit" class="form-control" value="0"></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Trạng thái</label><select name="status" class="form-control"><option value="1">Hoạt động</option><option value="0">Vô hiệu</option></select></div></div>
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
<?php endif; ?>
