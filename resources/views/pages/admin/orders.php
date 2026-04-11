<div class="container-fluid">
    <h1 class="h3 mb-4">Quản lý đơn hàng</h1>

    <form method="GET" class="mb-3">
        <input type="hidden" name="url" value="orders">
        <input type="text" name="search" placeholder="Mã đơn">
        <select name="status">
            <option value="">-- Trạng thái --</option>
            <option value="pending">Chờ xác nhận</option>
            <option value="completed">Hoàn thành</option>
        </select>
        <button class="btn btn-primary">Lọc</button>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Mã đơn</th>
                <th>Khách</th>
                <th>Tổng</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $o): ?>
            <tr>
                <td><?= $o['id'] ?></td>
                <td><?= $o['order_code'] ?></td>
                <td><?= $o['full_name'] ?></td>
                <td><?= number_format($o['final_amount']) ?>đ</td>
                <td>
                    <button class="btn btn-info btn-detail" data-id="<?= $o['id'] ?>">
                        Xem
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="orderModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body" id="orderContent"></div>
        </div>
    </div>
</div>

<script>
$('.btn-detail').click(function(){
    let id = $(this).data('id');

    $('#orderModal').modal('show');

    $.get('admin.php', {
        url: 'order-detail',
        id: id
    }, function(res){
        $('#orderContent').html(res);
    });
});
</script>
