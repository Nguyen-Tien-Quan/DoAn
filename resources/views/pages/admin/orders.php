<?php
// resources/views/pages/admin/orders.php
?>
<div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
        <div class="container-fluid">
            <h1 class="h3 mb-4 text-gray-800">Quản lý đơn hàng</h1>

            <!-- FILTER -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <form method="GET" class="form-inline">
                        <input type="hidden" name="url" value="orders">
                        <input type="text" name="search" class="form-control mr-2" placeholder="Mã đơn" value="<?= htmlspecialchars($search) ?>">

                        <select name="status" class="form-control mr-2">
                            <option value="">-- Trạng thái --</option>
                            <option value="pending" <?= $status_filter == 'pending' ? 'selected' : '' ?>>Chờ xác nhận</option>
                            <option value="confirmed" <?= $status_filter == 'confirmed' ? 'selected' : '' ?>>Đã xác nhận</option>
                            <option value="preparing" <?= $status_filter == 'preparing' ? 'selected' : '' ?>>Đang chuẩn bị</option>
                            <option value="ready_for_delivery" <?= $status_filter == 'ready_for_delivery' ? 'selected' : '' ?>>Sẵn sàng giao</option>
                            <option value="delivering" <?= $status_filter == 'delivering' ? 'selected' : '' ?>>Đang giao</option>
                            <option value="completed" <?= $status_filter == 'completed' ? 'selected' : '' ?>>Hoàn thành</option>
                            <option value="cancelled" <?= $status_filter == 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                        </select>

                        <button type="submit" class="btn btn-primary">Lọc</button>
                        <a href="admin.php?url=orders" class="btn btn-secondary ml-2">Reset</a>
                    </form>
                </div>
            </div>

            <!-- TABLE -->
            <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Mã đơn</th>
                                    <th>Khách hàng</th>
                                    <th>SĐT</th>
                                    <th>Tổng tiền</th>
                                    <th>Thanh toán</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày tạo</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php if (empty($orders)): ?>
                                    <tr><td colspan="9" class="text-center">Không có đơn hàng</td></tr>
                                <?php else: ?>
                                    <?php foreach ($orders as $o): ?>
                                        <tr>
                                            <td><?= $o['id'] ?></td>
                                            <td><?= htmlspecialchars($o['order_code']) ?></td>
                                            <td><?= htmlspecialchars($o['full_name'] ?? 'Khách lẻ') ?></td>
                                            <td><?= htmlspecialchars($o['phone'] ?? '') ?></td>
                                            <td><?= number_format($o['final_amount'] ?? 0, 0, ',', '.') ?>đ</td>

                                            <!-- PAYMENT -->
                                            <td>
                                                <?php
                                                $payBadge = match($o['payment_status'] ?? 'pending') {
                                                    'paid' => 'success',
                                                    'pending' => 'warning',
                                                    'failed' => 'danger',
                                                    'refunded' => 'secondary',
                                                    default => 'secondary'
                                                };
                                                $payText = match($o['payment_status'] ?? 'pending') {
                                                    'paid' => 'Đã thanh toán',
                                                    'pending' => 'Chưa thanh toán',
                                                    'failed' => 'Thất bại',
                                                    'refunded' => 'Hoàn tiền',
                                                    default => 'Khác'
                                                };
                                                ?>
                                                <span class="badge badge-<?= $payBadge ?>"><?= $payText ?></span>
                                            </td>

                                            <!-- STATUS -->
                                            <td>
                                                <?php
                                                $statusLabels = [
                                                    'pending' => '<span class="badge badge-warning">⏳ Chờ xác nhận</span>',
                                                    'confirmed' => '<span class="badge badge-primary">✔️ Đã xác nhận</span>',
                                                    'preparing' => '<span class="badge badge-info">🔪 Đang chuẩn bị</span>',
                                                    'ready_for_delivery' => '<span class="badge badge-primary">📦 Sẵn sàng giao</span>',
                                                    'delivering' => '<span class="badge badge-dark">🚚 Đang giao hàng</span>',
                                                    'completed' => '<span class="badge badge-success">✅ Hoàn thành</span>',
                                                    'cancelled' => '<span class="badge badge-danger">❌ Đã hủy</span>',
                                                ];
                                                echo $statusLabels[$o['status']] ?? $o['status'];
                                                ?>
                                            </td>

                                            <td><?= date('d/m/Y H:i', strtotime($o['created_at'] ?? 'now')) ?></td>

                                            <!-- ACTION -->
                                            <td>
                                                <button class="btn btn-sm btn-info btn-view-detail"
                                                    data-id="<?= $o['id'] ?>">
                                                    👁 Xem
                                                </button>

                                                <?php if ($o['status'] == 'pending'): ?>
                                                    <button class="btn btn-sm btn-success btn-update-status"
                                                        data-order-id="<?= $o['id'] ?>"
                                                        data-new-status="confirmed">
                                                        ✔️ Xác nhận
                                                    </button>
                                                    <button class="btn btn-sm btn-danger btn-cancel-order"
                                                        data-order-id="<?= $o['id'] ?>">
                                                        ❌ Hủy
                                                    </button>

                                                <?php elseif ($o['status'] == 'confirmed'): ?>
                                                    <button class="btn btn-sm btn-warning btn-update-status"
                                                        data-order-id="<?= $o['id'] ?>"
                                                        data-new-status="preparing">
                                                        🔪 Chuẩn bị
                                                    </button>
                                                    <button class="btn btn-sm btn-danger btn-cancel-order"
                                                        data-order-id="<?= $o['id'] ?>">
                                                        ❌ Hủy
                                                    </button>

                                                <?php elseif ($o['status'] == 'preparing'): ?>
                                                    <button class="btn btn-sm btn-primary btn-update-status"
                                                        data-order-id="<?= $o['id'] ?>"
                                                        data-new-status="ready_for_delivery">
                                                        📦 Sẵn sàng giao
                                                    </button>
                                                    <button class="btn btn-sm btn-danger btn-cancel-order"
                                                        data-order-id="<?= $o['id'] ?>">
                                                        ❌ Hủy
                                                    </button>

                                                <?php elseif ($o['status'] == 'ready_for_delivery'): ?>
                                                    <span class="badge badge-primary">
                                                        📦 Chờ shipper nhận đơn
                                                    </span>

                                                <?php elseif ($o['status'] == 'delivering'): ?>
                                                    <span class="badge badge-dark">🚚 Đang giao - Shipper xử lý</span>

                                                <?php elseif ($o['status'] == 'completed'): ?>
                                                    <span class="badge badge-success">✅ Hoàn thành</span>

                                                <?php elseif ($o['status'] == 'cancelled'): ?>
                                                    <span class="badge badge-danger">❌ Đã hủy</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- PAGINATION -->
                    <?php if ($totalPages > 1): ?>
                        <nav>
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                        <a class="page-link"
                                           href="admin.php?url=orders&page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= $status_filter ?>">
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

<!-- MODAL CHI TIẾT ĐƠN HÀNG -->
<div class="modal fade" id="orderDetailModal" tabindex="-1" role="dialog" aria-labelledby="orderDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderDetailModalLabel">Chi tiết đơn hàng</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="orderDetailContent">
                Đang tải...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL HỦY ĐƠN HÀNG -->
<div class="modal fade" id="cancelOrderModal" tabindex="-1" role="dialog" aria-labelledby="cancelOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelOrderModalLabel">Hủy đơn hàng</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="cancelOrderForm">
                    <input type="hidden" id="cancelOrderId" name="order_id">
                    <div class="form-group">
                        <label for="cancelReason">Lý do hủy đơn</label>
                        <textarea class="form-control" id="cancelReason" name="reason" rows="3" placeholder="Nhập lý do hủy đơn hàng..." required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-danger" id="confirmCancelBtn">Xác nhận hủy</button>
            </div>
        </div>
    </div>
</div>

<!-- SCRIPT -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
$(document).ready(function() {

    // ========== XEM CHI TIẾT ==========
    $('.btn-view-detail').click(function() {
        let orderId = $(this).data('id');
        $('#orderDetailContent').html('Đang tải...');
        $('#orderDetailModal').modal('show');

        $.ajax({
            url: 'admin.php?url=orders&ajax=detail&id=' + orderId,
            method: 'GET',
            success: function(html) {
                $('#orderDetailContent').html(html);
            },
            error: function() {
                $('#orderDetailContent').html('<div class="alert alert-danger">Không thể tải chi tiết đơn hàng.</div>');
            }
        });
    });

    // ========== CẬP NHẬT TRẠNG THÁI ==========
    $('.btn-update-status').click(function() {
        let orderId = $(this).data('order-id');
        let newStatus = $(this).data('new-status');

        if (!confirm("Xác nhận đổi trạng thái?")) return;

        $.ajax({
            url: 'admin.php?url=orders',
            method: 'POST',
            data: {
                ajax: 'update_status',
                order_id: orderId,
                new_status: newStatus
            },
            success: function(res) {
                console.log("RES:", res);
                location.reload();
            },
            error: function(xhr) {
                console.log("ERROR:", xhr.responseText);
                alert('Có lỗi xảy ra!');
            }
        });
    });

    // ========== HỦY ĐƠN (mở modal) ==========
    $('.btn-cancel-order').click(function() {
        let orderId = $(this).data('order-id');
        $('#cancelOrderId').val(orderId);
        $('#cancelReason').val('');
        $('#cancelOrderModal').modal('show');
    });

    // ========== XÁC NHẬN HỦY ==========
    $('#confirmCancelBtn').click(function() {
        let orderId = $('#cancelOrderId').val();
        let reason = $('#cancelReason').val().trim();

        if (!reason) {
            alert('Vui lòng nhập lý do hủy đơn.');
            return;
        }

        if (!confirm('Bạn chắc chắn muốn hủy đơn hàng này?')) return;

        $.ajax({
            url: 'admin.php?url=orders',
            method: 'POST',
            data: {
                ajax: 'cancel',
                order_id: orderId,
                reason: reason
            },
            success: function(res) {
                console.log("Cancel response:", res);
                // Giả sử server trả về JSON có success: true
                // Nếu thành công thì reload trang
                location.reload();
            },
            error: function(xhr) {
                console.log("ERROR:", xhr.responseText);
                alert('Hủy đơn thất bại!');
            }
        });
    });

});
</script>
