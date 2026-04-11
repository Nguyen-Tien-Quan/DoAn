

<div id="content-wrapper" class="d-flex flex-column">
<div id="content">


<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Quản lý khuyến mãi</h1>
        <button class="btn btn-primary" data-toggle="modal" data-target="#addModal">
            <i class="fas fa-plus"></i> Thêm mã
        </button>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- FILTER -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <form method="GET" action="admin.php" class="form-inline">
                <input type="hidden" name="url" value="vouchers">

                <input type="text" name="search" class="form-control mr-2"
                    placeholder="Mã, tên"
                    value="<?= htmlspecialchars($search ?? '') ?>">

                <select name="discount_type" class="form-control mr-2">
                    <option value="">-- Loại --</option>
                    <option value="percent" <?= ($type_filter ?? '')=='percent'?'selected':'' ?>>Phần trăm</option>
                    <option value="fixed" <?= ($type_filter ?? '')=='fixed'?'selected':'' ?>>Tiền mặt</option>
                </select>

                <select name="status" class="form-control mr-2">
                    <option value="-1">-- Trạng thái --</option>
                    <option value="1" <?= ($status_filter ?? '')==1?'selected':'' ?>>Hoạt động</option>
                    <option value="0" <?= ($status_filter ?? '')==0?'selected':'' ?>>Vô hiệu</option>
                </select>

                <button type="submit" class="btn btn-primary">Lọc</button>
            </form>
        </div>
    </div>

    <!-- TABLE -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách mã khuyến mãi</h6>
        </div>

        <div class="card-body">
            <div class="table-responsive">

                <table class="table table-bordered">
                    <thead>
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
                            <th>Hành động</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($vouchers as $v): ?>
                        <tr>
                            <td><?= $v['id'] ?></td>
                            <td><strong><?= htmlspecialchars($v['code']) ?></strong></td>
                            <td><?= htmlspecialchars($v['name']) ?></td>

                            <td><?= $v['discount_type']=='percent'?'Phần trăm':'Tiền mặt' ?></td>

                            <td>
                                <?= $v['discount_type']=='percent'
                                    ? $v['discount_value'].'%'
                                    : number_format($v['discount_value']).'đ' ?>
                            </td>

                            <td><?= number_format($v['min_order_amount']) ?>đ</td>
                            <td><?= number_format($v['max_discount_amount']) ?>đ</td>

                            <td>
                                <?= ($v['start_date']?date('d/m/Y',strtotime($v['start_date'])):'—') ?>
                                →
                                <?= ($v['end_date']?date('d/m/Y',strtotime($v['end_date'])):'—') ?>
                            </td>

                            <td>
                                <?= ($v['used_count'] ?? 0) ?>
                                /
                                <?= ($v['usage_limit'] ?: '∞') ?>
                            </td>

                            <td>
                                <?= $v['status']==1
                                    ? '<span class="badge badge-success">Hoạt động</span>'
                                    : '<span class="badge badge-danger">Vô hiệu</span>' ?>
                            </td>

                            <td>
                                <a href="admin.php?url=voucher-delete&id=<?= $v['id'] ?>"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Vô hiệu hóa?')">
                                   <i class="fas fa-trash"></i>
                                </a>

                                <a href="admin.php?url=voucher-restore&id=<?= $v['id'] ?>"
                                   class="btn btn-sm btn-success">
                                   <i class="fas fa-undo"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- PAGINATION -->
                <?php if ($totalPages > 1): ?>
                <nav>
                    <ul class="pagination justify-content-center">
                        <?php for ($i=1;$i<=$totalPages;$i++): ?>
                        <li class="page-item <?= $i==$page?'active':'' ?>">
                            <a class="page-link"
                               href="admin.php?url=vouchers&page=<?= $i ?>">
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

</div>
</div>
</div>

<!-- Modal Thêm mã khuyến mãi -->
<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="admin.php?url=voucher-add">
                <div class="modal-header">
                    <h5 class="modal-title" id="addModalLabel">Thêm mã khuyến mãi</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Mã code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control" required placeholder="VD: SUMMER20">
                    </div>
                    <div class="form-group">
                        <label>Tên khuyến mãi</label>
                        <input type="text" name="name" class="form-control" placeholder="VD: Giảm 20k cho đơn 100k">
                    </div>
                    <div class="form-group">
                        <label>Loại giảm giá</label>
                        <select name="discount_type" class="form-control">
                            <option value="percent">Phần trăm (%)</option>
                            <option value="fixed">Tiền mặt (VNĐ)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Giá trị giảm</label>
                        <input type="number" name="discount_value" class="form-control" required step="0.01" placeholder="VD: 20 (nếu %), 20000 (nếu tiền)">
                    </div>
                    <div class="form-group">
                        <label>Đơn hàng tối thiểu (VNĐ)</label>
                        <input type="number" name="min_order_amount" class="form-control" value="0">
                    </div>
                    <div class="form-group">
                        <label>Giảm tối đa (VNĐ)</label>
                        <input type="number" name="max_discount_amount" class="form-control" value="0">
                    </div>
                    <div class="form-group">
                        <label>Ngày bắt đầu</label>
                        <input type="datetime-local" name="start_date" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Ngày kết thúc</label>
                        <input type="datetime-local" name="end_date" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Giới hạn lượt sử dụng</label>
                        <input type="number" name="usage_limit" class="form-control" value="0" placeholder="0 = không giới hạn">
                    </div>
                    <div class="form-group">
                        <label>Trạng thái</label>
                        <select name="status" class="form-control">
                            <option value="1">Hoạt động</option>
                            <option value="0">Vô hiệu</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Lưu lại</button>
                </div>
            </form>
        </div>
    </div>
</div>
