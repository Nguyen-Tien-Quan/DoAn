<?php
// resources/views/pages/admin/reviews.php
// Các biến đã được truyền từ router: $reviews, $products, $totalPages, $page, $search, $product_filter, $rating_filter, $status_filter, $success, $error
?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Quản lý đánh giá</h1>
    </div>

    <?php if (isset($success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert"><?= htmlspecialchars($success) ?><button type="button" class="close" data-dismiss="alert">&times;</button></div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert"><?= htmlspecialchars($error) ?><button type="button" class="close" data-dismiss="alert">&times;</button></div>
    <?php endif; ?>

    <!-- Bộ lọc -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <form method="GET" class="form-inline">
                <input type="text" name="search" class="form-control mr-2" placeholder="Tìm theo tên, email, sản phẩm, nội dung" value="<?= htmlspecialchars($search ?? '') ?>">
                <select name="product_id" class="form-control mr-2">
                    <option value="0">-- Tất cả sản phẩm --</option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= ($product_filter ?? 0) == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="rating" class="form-control mr-2">
                    <option value="0">-- Tất cả số sao --</option>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <option value="<?= $i ?>" <?= ($rating_filter ?? 0) == $i ? 'selected' : '' ?>><?= $i ?> sao</option>
                    <?php endfor; ?>
                </select>
                <select name="status" class="form-control mr-2">
                    <option value="-1">-- Tất cả trạng thái --</option>
                    <option value="1" <?= ($status_filter ?? -1) == 1 ? 'selected' : '' ?>>Hiển thị</option>
                    <option value="0" <?= ($status_filter ?? -1) == 0 ? 'selected' : '' ?>>Ẩn</option>
                </select>
                <button type="submit" class="btn btn-primary">Lọc</button>
                <a href="?page=1" class="btn btn-secondary ml-2">Reset</a>
            </form>
        </div>
    </div>

    <!-- Danh sách đánh giá -->
    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Danh sách đánh giá</h6></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr><th>ID</th><th>Sản phẩm</th><th>Khách hàng</th><th>Đánh giá</th><th>Nội dung</th><th>Trạng thái</th><th>Ngày tạo</th><th>Thao tác</th>
                    </thead>
                    <tbody>
                        <?php if (empty($reviews)): ?>
                            <tr><td colspan="8" class="text-center">Không có đánh giá nào</td></tr>
                        <?php else: ?>
                            <?php foreach ($reviews as $rv): ?>
                            <tr>
                                <td><?= $rv['id'] ?></td>
                                <td><?= htmlspecialchars($rv['product_name'] ?? 'Sản phẩm đã xóa') ?></td>
                                <td><?= htmlspecialchars($rv['customer_name'] ?? 'Khách ẩn danh') ?><br><small><?= htmlspecialchars($rv['email'] ?? '') ?></small></td>
                                <td>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= $rv['rating']): ?>
                                            <i class="fas fa-star text-warning"></i>
                                        <?php else: ?>
                                            <i class="far fa-star text-muted"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                 </td>
                                <td><?= nl2br(htmlspecialchars(substr($rv['comment'] ?? '', 0, 100))) ?><?= strlen($rv['comment'] ?? '') > 100 ? '...' : '' ?> </td>
                                <td>
                                    <?php if ($rv['status'] == 1): ?>
                                        <span class="badge badge-success">Hiển thị</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Ẩn</span>
                                    <?php endif; ?>
                                 </td>
                                <td><?= date('d/m/Y H:i', strtotime($rv['created_at'] ?? 'now')) ?> </td>
                                <td>
                                    <form method="POST" style="display:inline-block">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="review_id" value="<?= $rv['id'] ?>">
                                        <input type="hidden" name="current_status" value="<?= $rv['status'] ?>">
                                        <button type="submit" class="btn btn-sm btn-primary" title="Đổi trạng thái">
                                            <?= $rv['status'] == 1 ? '<i class="fas fa-eye-slash"></i>' : '<i class="fas fa-eye"></i>' ?>
                                        </button>
                                    </form>
                                    <?php if ($rv['status'] == 1): ?>
                                        <a href="?delete=<?= $rv['id'] ?>&<?= http_build_query($_GET) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Ẩn đánh giá này?')"><i class="fas fa-trash"></i></a>
                                    <?php else: ?>
                                        <a href="?restore=<?= $rv['id'] ?>&<?= http_build_query($_GET) ?>" class="btn btn-sm btn-success" onclick="return confirm('Khôi phục đánh giá này?')"><i class="fas fa-undo-alt"></i></a>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#detailModal<?= $rv['id'] ?>"><i class="fas fa-eye"></i></button>
                                  </td>
                            </tr>

                            <!-- Modal chi tiết -->
                            <div class="modal fade" id="detailModal<?= $rv['id'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5>Chi tiết đánh giá #<?= $rv['id'] ?></h5>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class="modal-body">
                                            <p><strong>Sản phẩm:</strong> <?= htmlspecialchars($rv['product_name'] ?? 'Đã xóa') ?></p>
                                            <p><strong>Khách hàng:</strong> <?= htmlspecialchars($rv['customer_name'] ?? 'Ẩn danh') ?></p>
                                            <p><strong>Email:</strong> <?= htmlspecialchars($rv['email'] ?? '') ?></p>
                                            <p><strong>Số điện thoại:</strong> <?= htmlspecialchars($rv['phone'] ?? '') ?></p>
                                            <p><strong>Đánh giá:</strong>
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <?= $i <= $rv['rating'] ? '⭐' : '☆' ?>
                                                <?php endfor; ?>
                                            </p>
                                            <p><strong>Nội dung:</strong></p>
                                            <div class="border rounded p-2 bg-light"><?= nl2br(htmlspecialchars($rv['comment'] ?? '')) ?></div>
                                            <?php if (!empty($rv['images'])): ?>
                                                <p><strong>Hình ảnh:</strong> <a href="<?= htmlspecialchars($rv['images']) ?>" target="_blank">Xem</a></p>
                                            <?php endif; ?>
                                            <p><strong>Trạng thái:</strong> <?= $rv['status'] == 1 ? 'Hiển thị' : 'Ẩn' ?></p>
                                            <p><strong>Ngày tạo:</strong> <?= date('d/m/Y H:i:s', strtotime($rv['created_at'] ?? 'now')) ?></p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if (isset($totalPages) && $totalPages > 1): ?>
            <nav><ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == ($page ?? 1) ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search ?? '') ?>&product_id=<?= $product_filter ?? 0 ?>&rating=<?= $rating_filter ?? 0 ?>&status=<?= $status_filter ?? -1 ?>"><?= $i ?></a></li>
                <?php endfor; ?>
            </ul></nav>
            <?php endif; ?>
        </div>
    </div>
</div>
