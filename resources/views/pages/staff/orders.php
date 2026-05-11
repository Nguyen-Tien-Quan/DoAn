<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            Quản lý đơn hàng
        </h6>
        <small class="text-muted">Nhân viên chỉ được thao tác theo quy trình</small>
    </div>

    <div class="card-body">

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
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
                        'pending'           => 'secondary',
                        'confirmed'         => 'primary',
                        'preparing'         => 'warning',
                        'ready_for_delivery'=> 'info',
                        'delivering'        => 'dark',
                        'completed'         => 'success',
                        'cancelled'         => 'danger',
                        default             => 'secondary'
                    };
                    ?>

                    <tr>
                        <td><strong><?= htmlspecialchars($order['order_code']) ?></strong></td>
                        <td><?= htmlspecialchars($order['receiver_name'] ?? 'Khách lẻ') ?></td>
                        <td class="text-end fw-bold"><?= number_format($order['final_amount']) ?>đ</td>

                        <td>
                            <span class="badge badge-<?= $badge ?>">
                                <?= ucfirst(str_replace('_', ' ', $order['status'])) ?>
                            </span>
                        </td>

                        <td>
                            <span class="badge <?= ($order['payment_status'] ?? '') === 'paid' ? 'badge-success' : 'badge-warning' ?>">
                                <?= ucfirst($order['payment_status'] ?? 'pending') ?>
                            </span>
                        </td>

                        <td>
                            <button onclick="viewOrderDetail(<?= $order['id'] ?>)"
                                    class="btn btn-sm btn-primary mb-1">
                                <i class="fas fa-eye"></i> Xem
                            </button>

                            <?php if ($order['status'] === 'pending'): ?>
                                <button class="btn btn-sm btn-success mb-1"
                                        onclick="updateOrder(<?= $order['id'] ?>, 'confirmed')">
                                    Xác nhận
                                </button>
                            <?php endif; ?>

                            <?php if ($order['status'] === 'confirmed'): ?>
                                <button class="btn btn-sm btn-warning mb-1"
                                        onclick="updateOrder(<?= $order['id'] ?>, 'preparing')">
                                    Chuẩn bị
                                </button>
                            <?php endif; ?>

                            <?php if ($order['status'] === 'preparing'): ?>
                                <button class="btn btn-sm btn-info mb-1"
                                        onclick="updateOrder(<?= $order['id'] ?>, 'ready_for_delivery')">
                                    Sẵn sàng giao
                                </button>
                            <?php endif; ?>

                            <?php if ($order['status'] === 'ready_for_delivery'): ?>
                                <button class="btn btn-sm btn-dark mb-1"
                                        onclick="updateOrder(<?= $order['id'] ?>, 'delivering')">
                                    Đang giao
                                </button>
                            <?php endif; ?>

                            <?php if ($order['status'] === 'pending'): ?>
                                <button class="btn btn-sm btn-danger mb-1"
                                        onclick="cancelOrder(<?= $order['id'] ?>)">
                                    Hủy đơn
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4">Chưa có đơn hàng nào.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==================== MODAL CHI TIẾT ĐƠN HÀNG ==================== -->
<div class="modal fade" id="orderDetailModal" tabindex="-1" aria-labelledby="orderDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderDetailModalLabel">Chi tiết đơn hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- AJAX content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script>
// Biến global để quản lý modal
let orderModal = null;

// ====================== XEM CHI TIẾT ĐƠN HÀNG ======================
async function viewOrderDetail(id) {
    const modalBody = document.getElementById('modalBody');
    modalBody.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-3">Đang tải chi tiết đơn hàng...</p>
        </div>`;

    try {
        const response = await fetch(`staff.php?url=order-detail&id=${id}`);
        const data = await response.json();

        if (!data.success) {
            modalBody.innerHTML = `<div class="alert alert-danger">${data.message || 'Không thể tải dữ liệu'}</div>`;
            return;
        }

        const order = data.order;
        const items = data.items || [];

        let html = `
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6 class="text-primary">Thông tin khách hàng</h6>
                    <p><strong>Họ tên:</strong> ${order.full_name || order.receiver_name || 'Không có'}</p>
                    <p><strong>Số điện thoại:</strong> ${order.phone || 'Không có'}</p>
                    <p><strong>Địa chỉ:</strong> ${order.address || 'Không có'}</p>
                </div>
                <div class="col-md-6">
                    <h6 class="text-primary">Thông tin đơn hàng</h6>
                    <p><strong>Mã đơn:</strong> #${order.order_code}</p>
                    <p><strong>Ngày đặt:</strong> ${order.created_at}</p>
                    <p><strong>Trạng thái:</strong>
                        <span class="badge badge-primary">${ucfirst(order.status)}</span>
                    </p>
                    <p><strong>Thanh toán:</strong>
                        <span class="badge ${order.payment_status === 'paid' ? 'badge-success' : 'badge-warning'}">
                            ${ucfirst(order.payment_status || 'pending')}
                        </span>
                    </p>
                    <p><strong>Tổng tiền:</strong> <strong class="text-danger">${Number(order.final_amount).toLocaleString('vi-VN')}đ</strong></p>
                </div>
            </div>

            <h6 class="text-primary">Danh sách sản phẩm</h6>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Biến thể</th>
                            <th>Topping</th>
                            <th>Số lượng</th>
                            <th>Đơn giá</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        items.forEach(item => {
            let toppingHtml = '<small class="text-muted">Không có</small>';
            if (item.toppings && item.toppings.length > 0) {
                toppingHtml = item.toppings.map(t => `+ ${t.name} (${Number(t.price).toLocaleString('vi-VN')}đ)`).join('<br>');
            }

            html += `
                <tr>
                    <td>${item.product_name}</td>
                    <td>${item.variant_name || 'Mặc định'}</td>
                    <td>${toppingHtml}</td>
                    <td class="text-center">${item.quantity}</td>
                    <td class="text-end">${Number(item.price).toLocaleString('vi-VN')}đ</td>
                    <td class="text-end fw-bold">${Number(item.price * item.quantity).toLocaleString('vi-VN')}đ</td>
                </tr>
            `;
        });

        html += `</tbody></table></div>`;

        modalBody.innerHTML = html;

        // Khởi tạo hoặc hiển thị modal
        if (!orderModal) {
            orderModal = new bootstrap.Modal(document.getElementById('orderDetailModal'));
        }
        orderModal.show();

    } catch (error) {
        console.error(error);
        modalBody.innerHTML = `<div class="alert alert-danger">Lỗi kết nối. Vui lòng thử lại!</div>`;
    }
}

// Helper
function ucfirst(str) {
    return str ? str.charAt(0).toUpperCase() + str.slice(1) : '';
}

// ====================== CẬP NHẬT TRẠNG THÁI & HỦY ĐƠN ======================
function updateOrder(id, status) {
    if (!confirm(`Xác nhận chuyển trạng thái đơn hàng này sang "${status}"?`)) return;

    fetch('staff.php?url=order-update', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `order_id=${id}&status=${status}`
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) location.reload();
        else alert(res.message || 'Cập nhật thất bại!');
    })
    .catch(() => alert('Lỗi kết nối'));
}

function cancelOrder(id) {
    if (!confirm('Bạn có chắc chắn muốn HỦY đơn hàng này?\n\nHành động này không thể hoàn tác!')) return;

    fetch('staff.php?url=order-update', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `order_id=${id}&status=cancelled`
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) location.reload();
        else alert(res.message || 'Hủy đơn thất bại!');
    });
}
</script>
