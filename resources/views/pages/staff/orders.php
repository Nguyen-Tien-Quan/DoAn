<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            Quản lý đơn hàng
        </h6>
    </div>

    <div class="card-body">

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Mã đơn</th>
                    <th>Khách hàng</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th>Thanh toán</th>
                    <th>Hành động</th>
                </tr>
            </thead>

            <tbody>
            <?php foreach (($orders ?? []) as $order): ?>

                <?php
                $badge = match($order['status']) {
                    'pending' => 'secondary',
                    'confirmed' => 'primary',
                    'preparing' => 'warning',
                    'ready_for_delivery' => 'info',
                    'delivering' => 'dark',
                    'completed' => 'success',
                    'cancelled' => 'danger',
                    default => 'secondary'
                };
                ?>

                <tr>
                    <td><?= $order['order_code'] ?></td>

                    <td><?= $order['receiver_name'] ?? 'Khách lẻ' ?></td>

                    <td><?= number_format($order['final_amount']) ?>đ</td>

                    <td>
                        <span class="badge badge-<?= $badge ?>">
                            <?= $order['status'] ?>
                        </span>
                    </td>

                    <td>
                        <span class="badge badge-success">
                            <?= $order['payment_status'] ?? 'pending' ?>
                        </span>
                    </td>

                    <td>

                        <a href="staff.php?url=order-detail&id=<?= $order['id'] ?>"
                           class="btn btn-sm btn-primary">
                            Xem
                        </a>

                        <?php if ($order['status'] === 'pending'): ?>
                            <button class="btn btn-sm btn-success"
                                    onclick="updateOrder(<?= $order['id'] ?>,'confirmed')">
                                Xác nhận
                            </button>
                        <?php endif; ?>

                        <?php if ($order['status'] === 'confirmed'): ?>
                            <button class="btn btn-sm btn-warning"
                                    onclick="updateOrder(<?= $order['id'] ?>,'preparing')">
                                Chuẩn bị
                            </button>
                        <?php endif; ?>

                        <?php if ($order['status'] === 'preparing'): ?>
                            <button class="btn btn-sm btn-info"
                                    onclick="updateOrder(<?= $order['id'] ?>,'ready_for_delivery')">
                                Sẵn sàng
                            </button>
                        <?php endif; ?>

                        <?php if (!in_array($order['status'], ['completed','cancelled'])): ?>
                            <button class="btn btn-sm btn-danger"
                                    onclick="updateOrder(<?= $order['id'] ?>,'cancelled')">
                                Hủy
                            </button>
                        <?php endif; ?>

                    </td>
                </tr>

            <?php endforeach; ?>
            </tbody>

        </table>

    </div>
</div>

<script>
function updateOrder(id, status) {
    fetch('staff.php?url=order-update', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `order_id=${id}&status=${status}`
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) location.reload();
        else alert('Lỗi cập nhật');
    });
}
</script>
