<?php

$is_admin = ($_SESSION['user']['role_id'] ?? 0) === 1;

$categories = getCategories();

$success = $_SESSION['success'] ?? '';
$error   = $_SESSION['error'] ?? '';

unset($_SESSION['success'], $_SESSION['error']);
$base = '/DoAn/DoAnTotNghiep/public/';
function getParentCategoryName($parent_id, $categories) {

    if (!$parent_id) {
        return '---';
    }

    foreach ($categories as $c) {

        if ($c['id'] == $parent_id) {
            return $c['name'];
        }
    }

    return '---';
}
?>

<div id="content-wrapper" class="d-flex flex-column">

    <div id="content">

        <div class="container-fluid">

            <div class="d-sm-flex align-items-center justify-content-between mb-4">

                <h1 class="h3 mb-0 text-gray-800">
                    📂 Quản lý danh mục
                </h1>

                <?php if ($is_admin): ?>

                    <button class="btn btn-primary"
                            data-toggle="modal"
                            data-target="#addModal">

                        <i class="fas fa-plus"></i>
                        Thêm danh mục

                    </button>

                <?php endif; ?>

            </div>

            <?php if ($success): ?>

                <div class="alert alert-success">
                    <?= $success ?>
                </div>

            <?php endif; ?>

            <?php if ($error): ?>

                <div class="alert alert-danger">
                    <?= $error ?>
                </div>

            <?php endif; ?>

            <!-- ADD MODAL -->
            <?php if ($is_admin): ?>

            <div class="modal fade" id="addModal">

                <div class="modal-dialog">

                    <div class="modal-content">

                        <form method="POST"
                              action="admin.php?url=category-add"
                              enctype="multipart/form-data">

                            <div class="modal-header">

                                <h5 class="modal-title">
                                    Thêm danh mục mới
                                </h5>

                                <button type="button"
                                        class="close"
                                        data-dismiss="modal">

                                    &times;

                                </button>

                            </div>

                            <div class="modal-body">

                                <input type="hidden"
                                       name="action"
                                       value="add">

                                <!-- NAME -->
                                <div class="form-group">

                                    <label>Tên danh mục</label>

                                    <input type="text"
                                           name="name"
                                           class="form-control"
                                           required>

                                </div>

                                <!-- SLUG -->
                                <div class="form-group">

                                    <label>Slug</label>

                                    <input type="text"
                                           name="slug"
                                           class="form-control"
                                           placeholder="vi-du-danh-muc">

                                </div>

                                <!-- DESCRIPTION -->
                                <div class="form-group">

                                    <label>Mô tả</label>

                                    <textarea name="description"
                                              class="form-control"
                                              rows="3"></textarea>

                                </div>

                                <!-- IMAGE -->
                                <div class="form-group">

                                    <label>Ảnh danh mục</label>

                                    <input type="file"
                                           name="image"
                                           class="form-control-file">

                                </div>

                                <!-- PARENT -->
                                <div class="form-group">

                                    <label>Danh mục cha</label>

                                    <select name="parent_id"
                                            class="form-control">

                                        <option value="">
                                            -- Không có --
                                        </option>

                                        <?php foreach($categories as $parent): ?>

                                            <option value="<?= $parent['id'] ?>">

                                                <?= htmlspecialchars($parent['name']) ?>

                                            </option>

                                        <?php endforeach; ?>

                                    </select>

                                </div>

                                <!-- SORT -->
                                <div class="form-group">

                                    <label>Thứ tự hiển thị</label>

                                    <input type="number"
                                           name="sort_order"
                                           class="form-control"
                                           value="0">

                                </div>

                                <!-- STATUS -->
                                <div class="form-group">

                                    <label>Trạng thái</label>

                                    <select name="status"
                                            class="form-control">

                                        <option value="1">
                                            Hiển thị
                                        </option>

                                        <option value="0">
                                            Ẩn
                                        </option>

                                    </select>

                                </div>

                            </div>

                            <div class="modal-footer">

                                <button type="button"
                                        class="btn btn-secondary"
                                        data-dismiss="modal">

                                    Hủy

                                </button>

                                <button type="submit"
                                        class="btn btn-primary">

                                    Lưu

                                </button>

                            </div>

                        </form>

                    </div>

                </div>

            </div>

            <?php endif; ?>

            <!-- TABLE -->
            <div class="card shadow mb-4">

                <div class="card-header py-3">

                    <h6 class="m-0 font-weight-bold text-primary">
                        Danh sách danh mục
                    </h6>

                </div>

                <div class="card-body">

                    <div class="table-responsive">

                        <table class="table table-bordered table-hover">

                            <thead>

                                <tr>

                                    <th>ID</th>

                                    <th>Ảnh</th>

                                    <th>Tên danh mục</th>

                                    <th>Slug</th>

                                    <th>Danh mục cha</th>

                                    <th>Thứ tự</th>

                                    <th>Mô tả</th>

                                    <th>Trạng thái</th>

                                    <?php if ($is_admin): ?>

                                        <th width="180">
                                            Hành động
                                        </th>

                                    <?php endif; ?>

                                </tr>

                            </thead>

                            <tbody>

                                <?php foreach ($categories as $cat): ?>

                                <tr>

                                    <td>
                                        <?= $cat['id'] ?>
                                    </td>

                                    <!-- IMAGE -->
                                    <td width="90">

                                        <?php if(!empty($cat['image'])): ?>

                                            <img src="<?= $base ?>assets/img/category-item/<?= htmlspecialchars($cat['image']) ?>"
                                                 width="60"
                                                 height="60"
                                                 style="object-fit:cover"
                                                 class="rounded border">

                                        <?php else: ?>

                                            <span class="text-muted">
                                                No image
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                    <!-- NAME -->
                                    <td>

                                        <?= htmlspecialchars($cat['name']) ?>

                                    </td>

                                    <!-- SLUG -->
                                    <td>

                                        <?= htmlspecialchars($cat['slug'] ?? '') ?>

                                    </td>

                                    <!-- PARENT -->
                                    <td>

                                        <?= htmlspecialchars(
                                            getParentCategoryName(
                                                $cat['parent_id'] ?? null,
                                                $categories
                                            )
                                        ) ?>

                                    </td>

                                    <!-- SORT -->
                                    <td>

                                        <?= (int)($cat['sort_order'] ?? 0) ?>

                                    </td>

                                    <!-- DESC -->
                                    <td>

                                        <?= htmlspecialchars($cat['description'] ?? '') ?>

                                    </td>

                                    <!-- STATUS -->
                                    <td>

                                        <?= $cat['status'] == 1
                                            ? '<span class="badge badge-success">Hiển thị</span>'
                                            : '<span class="badge badge-secondary">Ẩn</span>' ?>

                                    </td>

                                    <?php if ($is_admin): ?>

                                    <td>

                                        <!-- EDIT -->
                                        <button class="btn btn-sm btn-primary"
                                                data-toggle="modal"
                                                data-target="#editModal<?= $cat['id'] ?>">

                                            <i class="fas fa-edit"></i>

                                        </button>

                                        <!-- SOFT DELETE -->
                                        <?php if ($cat['status'] == 1): ?>

                                            <a href="admin.php?url=categories&soft_delete=<?= $cat['id'] ?>"
                                               class="btn btn-sm btn-warning"
                                               onclick="return confirm('Vô hiệu hóa danh mục này?')">

                                                <i class="fas fa-ban"></i>

                                            </a>

                                        <?php else: ?>

                                            <a href="admin.php?url=categories&restore=<?= $cat['id'] ?>"
                                               class="btn btn-sm btn-success"
                                               onclick="return confirm('Khôi phục danh mục này?')">

                                                <i class="fas fa-undo-alt"></i>

                                            </a>

                                        <?php endif; ?>

                                        <!-- DELETE -->
                                        <a href="admin.php?url=categories&hard_delete=<?= $cat['id'] ?>"
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('XÓA VĨNH VIỄN?\nToàn bộ sản phẩm sẽ bị xóa!')">

                                            <i class="fas fa-trash-alt"></i>

                                        </a>

                                    </td>

                                    <?php endif; ?>

                                </tr>

                                <!-- EDIT MODAL -->
                                <?php if ($is_admin): ?>

                                <div class="modal fade"
                                     id="editModal<?= $cat['id'] ?>">

                                    <div class="modal-dialog">

                                        <div class="modal-content">

                                            <form method="POST"
                                                  action="admin.php?url=category-update"
                                                  enctype="multipart/form-data">

                                                <input type="hidden"
                                                       name="action"
                                                       value="edit">

                                                <input type="hidden"
                                                       name="id"
                                                       value="<?= $cat['id'] ?>">

                                                <div class="modal-header">

                                                    <h5 class="modal-title">
                                                        Sửa danh mục
                                                    </h5>

                                                    <button type="button"
                                                            class="close"
                                                            data-dismiss="modal">

                                                        &times;

                                                    </button>

                                                </div>

                                                <div class="modal-body">

                                                    <!-- NAME -->
                                                    <div class="form-group">

                                                        <label>Tên danh mục</label>

                                                        <input type="text"
                                                               name="name"
                                                               class="form-control"
                                                               value="<?= htmlspecialchars($cat['name']) ?>"
                                                               required>

                                                    </div>

                                                    <!-- SLUG -->
                                                    <div class="form-group">

                                                        <label>Slug</label>

                                                        <input type="text"
                                                               name="slug"
                                                               class="form-control"
                                                               value="<?= htmlspecialchars($cat['slug'] ?? '') ?>">

                                                    </div>

                                                    <!-- DESCRIPTION -->
                                                    <div class="form-group">

                                                        <label>Mô tả</label>

                                                        <textarea name="description"
                                                                  class="form-control"
                                                                  rows="3"><?= htmlspecialchars($cat['description'] ?? '') ?></textarea>

                                                    </div>

                                                    <!-- IMAGE -->
                                                    <div class="form-group">

                                                        <label>Ảnh hiện tại</label><br>

                                                        <?php if(!empty($cat['image'])): ?>

                                                            <img src="<?= $base ?>/assets/img/category-item/<?= htmlspecialchars($cat['image']) ?>"
                                                                 width="80"
                                                                 class="rounded border mb-2">

                                                        <?php endif; ?>

                                                        <input type="file"
                                                               name="image"
                                                               class="form-control-file mt-2">

                                                    </div>

                                                    <!-- PARENT -->
                                                    <div class="form-group">

                                                        <label>Danh mục cha</label>

                                                        <select name="parent_id"
                                                                class="form-control">

                                                            <option value="">
                                                                -- Không có --
                                                            </option>

                                                            <?php foreach($categories as $parent): ?>

                                                                <?php if($parent['id'] != $cat['id']): ?>

                                                                    <option value="<?= $parent['id'] ?>"
                                                                        <?= ($cat['parent_id'] == $parent['id'])
                                                                            ? 'selected'
                                                                            : '' ?>>

                                                                        <?= htmlspecialchars($parent['name']) ?>

                                                                    </option>

                                                                <?php endif; ?>

                                                            <?php endforeach; ?>

                                                        </select>

                                                    </div>

                                                    <!-- SORT -->
                                                    <div class="form-group">

                                                        <label>Thứ tự hiển thị</label>

                                                        <input type="number"
                                                               name="sort_order"
                                                               class="form-control"
                                                               value="<?= (int)($cat['sort_order'] ?? 0) ?>">

                                                    </div>

                                                    <!-- STATUS -->
                                                    <div class="form-group">

                                                        <label>Trạng thái</label>

                                                        <select name="status"
                                                                class="form-control">

                                                            <option value="1"
                                                                <?= $cat['status']==1 ? 'selected' : '' ?>>

                                                                Hiển thị

                                                            </option>

                                                            <option value="0"
                                                                <?= $cat['status']==0 ? 'selected' : '' ?>>

                                                                Ẩn

                                                            </option>

                                                        </select>

                                                    </div>

                                                </div>

                                                <div class="modal-footer">

                                                    <button type="button"
                                                            class="btn btn-secondary"
                                                            data-dismiss="modal">

                                                        Hủy

                                                    </button>

                                                    <button type="submit"
                                                            class="btn btn-primary">

                                                        Lưu thay đổi

                                                    </button>

                                                </div>

                                            </form>

                                        </div>

                                    </div>

                                </div>

                                <?php endif; ?>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>
