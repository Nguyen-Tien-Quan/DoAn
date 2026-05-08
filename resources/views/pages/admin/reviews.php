<?php

$reviews         = $reviews ?? [];
$products        = $products ?? [];

$totalPages      = $totalPages ?? 1;
$page            = $page ?? 1;

$search          = $search ?? '';

$product_filter  = $product_filter ?? 0;
$rating_filter   = $rating_filter ?? 0;
$status_filter   = $status_filter ?? -1;

$success         = $success ?? null;
$error           = $error ?? null;
?>

<style>
.review-page .card{
    border:none;
    border-radius:16px;
    overflow:hidden;
    box-shadow:0 2px 12px rgba(0,0,0,.06);
}

.review-page .card-header{
    background:#fff;
    border-bottom:1px solid #f1f1f1;
    padding:18px 24px;
}

.review-page .filter-form .form-control{
    min-height:44px;
    border-radius:10px;
    border:1px solid #ddd;
    box-shadow:none !important;
}

.review-page .table{
    margin-bottom:0;
}

.review-page .table thead th{
    background:#f8f9fc;
    border:none;
    font-size:13px;
    font-weight:700;
    color:#555;
    white-space:nowrap;
}

.review-page .table td{
    vertical-align:middle;
    border-color:#f1f1f1;
}

.review-page .review-content{
    max-width:280px;
    white-space:normal;
    line-height:1.5;
    color:#555;
}

.review-page .customer-box{
    min-width:180px;
}

.review-page .customer-name{
    font-weight:600;
    color:#222;
}

.review-page .customer-email{
    font-size:12px;
    color:#888;
}

.review-page .rating-stars{
    white-space:nowrap;
}

.review-page .action-group{
    display:flex;
    gap:6px;
    flex-wrap:wrap;
}

.review-page .btn-action{
    width:34px;
    height:34px;
    border-radius:10px;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:0;
}

.review-page .badge{
    padding:7px 12px;
    border-radius:30px;
    font-size:12px;
    font-weight:600;
}

.review-page .pagination .page-link{
    border:none;
    margin:0 4px;
    border-radius:10px;
    color:#555;
}

.review-page .pagination .active .page-link{
    background:#4e73df;
    color:#fff;
}

.review-page .modal-content{
    border:none;
    border-radius:18px;
}

.review-page .review-detail-box{
    background:#f8f9fc;
    border-radius:12px;
    padding:14px;
    line-height:1.7;
}

.review-page .review-image{
    width:100px;
    height:100px;
    object-fit:cover;
    border-radius:12px;
    border:1px solid #eee;
}

@media(max-width:768px){

    .review-page .filter-form{
        display:block !important;
    }

    .review-page .filter-form .form-control,
    .review-page .filter-form .btn{
        width:100%;
        margin-bottom:10px;
    }

    .review-page .review-content{
        max-width:180px;
    }
}
</style>

<div class="container-fluid review-page">

    <!-- HEADER -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">Quản lý đánh giá</h1>
            <p class="mb-0 text-muted">
                Quản lý toàn bộ đánh giá sản phẩm của khách hàng
            </p>
        </div>
    </div>

    <!-- ALERT -->
    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle mr-2"></i>
            <?= htmlspecialchars($success) ?>
            <button type="button" class="close" data-dismiss="alert">
                &times;
            </button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <?= htmlspecialchars($error) ?>
            <button type="button" class="close" data-dismiss="alert">
                &times;
            </button>
        </div>
    <?php endif; ?>

    <!-- FILTER -->
    <div class="card mb-4">
        <div class="card-header">

            <form method="GET" class="form-inline filter-form">

                <input
                    type="text"
                    name="search"
                    class="form-control mr-2"
                    placeholder="Tìm tên khách hàng, email, sản phẩm..."
                    value="<?= htmlspecialchars($search ?? '') ?>"
                >

                <select name="product_id" class="form-control mr-2">
                    <option value="0">Tất cả sản phẩm</option>

                    <?php foreach ($products as $p): ?>
                        <option
                            value="<?= $p['id'] ?>"
                            <?= ($product_filter ?? 0) == $p['id'] ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($p['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="rating" class="form-control mr-2">
                    <option value="0">Tất cả số sao</option>

                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <option
                            value="<?= $i ?>"
                            <?= ($rating_filter ?? 0) == $i ? 'selected' : '' ?>
                        >
                            <?= $i ?> sao
                        </option>
                    <?php endfor; ?>
                </select>

                <select name="status" class="form-control mr-2">
                    <option value="-1">Tất cả trạng thái</option>

                    <option
                        value="1"
                        <?= ($status_filter ?? -1) == 1 ? 'selected' : '' ?>
                    >
                        Hiển thị
                    </option>

                    <option
                        value="0"
                        <?= ($status_filter ?? -1) == 0 ? 'selected' : '' ?>
                    >
                        Đã ẩn
                    </option>
                </select>

                <button class="btn btn-primary mr-2">
                    <i class="fas fa-search mr-1"></i>
                    Lọc
                </button>

                <a href="?page=1" class="btn btn-light border">
                    Reset
                </a>

            </form>

        </div>
    </div>

    <!-- TABLE -->
    <div class="card">

        <div class="card-header d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                Danh sách đánh giá
            </h6>

            <span class="badge badge-primary px-3 py-2">
                <?= count($reviews ?? []) ?> đánh giá
            </span>
        </div>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>
                        <tr>
                            <th width="70">ID</th>
                            <th>Sản phẩm</th>
                            <th>Khách hàng</th>
                            <th width="130">Đánh giá</th>
                            <th>Nội dung</th>
                            <th width="120">Trạng thái</th>
                            <th width="150">Ngày tạo</th>
                            <th width="180">Thao tác</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (empty($reviews)): ?>

                        <tr>
                            <td colspan="8" class="text-center py-5">

                                <img
                                    src="https://cdn-icons-png.flaticon.com/512/7486/7486740.png"
                                    width="120"
                                    class="mb-3"
                                >

                                <h5 class="text-muted">
                                    Không có đánh giá nào
                                </h5>

                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($reviews as $rv): ?>

                            <tr>

                                <td>
                                    <strong>#<?= $rv['id'] ?></strong>
                                </td>

                                <td>
                                    <div class="font-weight-bold">
                                        <?= htmlspecialchars($rv['product_name'] ?? 'Sản phẩm đã xóa') ?>
                                    </div>
                                </td>

                                <td>

                                    <div class="customer-box">

                                        <div class="customer-name">
                                            <?= htmlspecialchars($rv['customer_name'] ?? 'Khách ẩn danh') ?>
                                        </div>

                                        <div class="customer-email">
                                            <?= htmlspecialchars($rv['email'] ?? '') ?>
                                        </div>

                                    </div>

                                </td>

                                <td>

                                    <div class="rating-stars">

                                        <?php for ($i = 1; $i <= 5; $i++): ?>

                                            <?php if ($i <= $rv['rating']): ?>
                                                <i class="fas fa-star text-warning"></i>
                                            <?php else: ?>
                                                <i class="far fa-star text-muted"></i>
                                            <?php endif; ?>

                                        <?php endfor; ?>

                                    </div>

                                </td>

                                <td>

                                    <div class="review-content">

                                        <?= nl2br(htmlspecialchars(mb_substr($rv['comment'] ?? '', 0, 120))) ?>

                                        <?= mb_strlen($rv['comment'] ?? '') > 120 ? '...' : '' ?>

                                    </div>

                                </td>

                                <td>

                                    <?php if ($rv['status'] == 1): ?>

                                        <span class="badge badge-success">
                                            Hiển thị
                                        </span>

                                    <?php else: ?>

                                        <span class="badge badge-secondary">
                                            Đã ẩn
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>
                                    <?= date('d/m/Y', strtotime($rv['created_at'])) ?>
                                    <br>
                                    <small class="text-muted">
                                        <?= date('H:i', strtotime($rv['created_at'])) ?>
                                    </small>
                                </td>

                                <td>

                                    <div class="action-group">

                                        <!-- Toggle -->
                                        <form method="POST">

                                            <input type="hidden" name="action" value="toggle_status">

                                            <input
                                                type="hidden"
                                                name="review_id"
                                                value="<?= $rv['id'] ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="current_status"
                                                value="<?= $rv['status'] ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="btn btn-action btn-primary"
                                                title="Đổi trạng thái"
                                            >

                                                <?=
                                                    $rv['status'] == 1
                                                    ? '<i class="fas fa-eye-slash"></i>'
                                                    : '<i class="fas fa-eye"></i>'
                                                ?>

                                            </button>

                                        </form>

                                        <!-- Detail -->
                                        <button
                                            class="btn btn-action btn-info"
                                            data-toggle="modal"
                                            data-target="#detailModal<?= $rv['id'] ?>"
                                            title="Chi tiết"
                                        >
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        <!-- Delete -->
                                        <a
                                            href="?delete=<?= $rv['id'] ?>&<?= http_build_query($_GET) ?>"
                                            class="btn btn-action btn-danger"
                                            onclick="return confirm('Xóa vĩnh viễn đánh giá này?')"
                                            title="Xóa"
                                        >
                                            <i class="fas fa-trash"></i>
                                        </a>

                                    </div>

                                </td>

                            </tr>

                            <!-- MODAL -->
                            <div
                                class="modal fade"
                                id="detailModal<?= $rv['id'] ?>"
                                tabindex="-1"
                            >

                                <div class="modal-dialog modal-lg modal-dialog-centered">

                                    <div class="modal-content">

                                        <div class="modal-header border-0 pb-0">

                                            <h5 class="font-weight-bold">
                                                Chi tiết đánh giá #<?= $rv['id'] ?>
                                            </h5>

                                            <button
                                                type="button"
                                                class="close"
                                                data-dismiss="modal"
                                            >
                                                &times;
                                            </button>

                                        </div>

                                        <div class="modal-body">

                                            <div class="review-detail-box">

                                                <div class="row">

                                                    <div class="col-md-6 mb-3">
                                                        <strong>Sản phẩm:</strong><br>
                                                        <?= htmlspecialchars($rv['product_name'] ?? 'Đã xóa') ?>
                                                    </div>

                                                    <div class="col-md-6 mb-3">
                                                        <strong>Khách hàng:</strong><br>
                                                        <?= htmlspecialchars($rv['customer_name'] ?? 'Ẩn danh') ?>
                                                    </div>

                                                    <div class="col-md-6 mb-3">
                                                        <strong>Email:</strong><br>
                                                        <?= htmlspecialchars($rv['email'] ?? '') ?>
                                                    </div>

                                                    <div class="col-md-6 mb-3">
                                                        <strong>Số điện thoại:</strong><br>
                                                        <?= htmlspecialchars($rv['phone'] ?? '') ?>
                                                    </div>

                                                    <div class="col-md-6 mb-3">
                                                        <strong>Trạng thái:</strong><br>

                                                        <?php if ($rv['status'] == 1): ?>
                                                            <span class="badge badge-success">
                                                                Hiển thị
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge badge-secondary">
                                                                Đã ẩn
                                                            </span>
                                                        <?php endif; ?>

                                                    </div>

                                                    <div class="col-md-6 mb-3">
                                                        <strong>Ngày tạo:</strong><br>
                                                        <?= date('d/m/Y H:i:s', strtotime($rv['created_at'])) ?>
                                                    </div>

                                                    <div class="col-12 mb-3">

                                                        <strong>Đánh giá:</strong><br>

                                                        <?php for ($i = 1; $i <= 5; $i++): ?>

                                                            <?php if ($i <= $rv['rating']): ?>
                                                                <i class="fas fa-star text-warning"></i>
                                                            <?php else: ?>
                                                                <i class="far fa-star text-muted"></i>
                                                            <?php endif; ?>

                                                        <?php endfor; ?>

                                                    </div>

                                                    <div class="col-12">

                                                        <strong>Nội dung đánh giá:</strong>

                                                        <div class="border rounded bg-white p-3 mt-2">
                                                            <?= nl2br(htmlspecialchars($rv['comment'] ?? '')) ?>
                                                        </div>

                                                    </div>

                                                    <?php if (!empty($rv['images'])): ?>

                                                        <div class="col-12 mt-4">

                                                            <strong>Hình ảnh:</strong>

                                                            <div class="mt-2">

                                                                <img
                                                                    src="<?= htmlspecialchars($rv['images']) ?>"
                                                                    class="review-image"
                                                                >

                                                            </div>

                                                        </div>

                                                    <?php endif; ?>

                                                </div>

                                            </div>

                                        </div>

                                        <div class="modal-footer border-0">

                                            <button
                                                type="button"
                                                class="btn btn-secondary px-4"
                                                data-dismiss="modal"
                                            >
                                                Đóng
                                            </button>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

        <!-- PAGINATION -->
        <?php if (($totalPages ?? 1) > 1): ?>

            <div class="card-footer bg-white">

                <nav>

                    <ul class="pagination justify-content-center mb-0">

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>

                            <li class="page-item <?= $i == ($page ?? 1) ? 'active' : '' ?>">

                                <a
                                    class="page-link"
                                    href="?page=<?= $i ?>
                                    &search=<?= urlencode($search ?? '') ?>
                                    &product_id=<?= $product_filter ?? 0 ?>
                                    &rating=<?= $rating_filter ?? 0 ?>
                                    &status=<?= $status_filter ?? -1 ?>"
                                >
                                    <?= $i ?>
                                </a>

                            </li>

                        <?php endfor; ?>

                    </ul>

                </nav>

            </div>

        <?php endif; ?>

    </div>

</div>
