<?php
// resources/views/pages/admin/users.php
// Các biến được truyền từ controller (admin.php case 'users')
// $users, $roles, $totalPages, $currentPage, $search, $filter_role, $filter_status
// $success, $error, $current_user_id, $is_super_admin, $current_role, $base

?>
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Quản lý người dùng</h1>
    <button class="btn btn-primary" data-toggle="modal" data-target="#addUserModal"><i class="fas fa-plus"></i> Thêm người dùng</button>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <form method="GET" action="admin.php?url=users" class="form-inline">
            <input type="text" name="search" class="form-control mr-2" placeholder="Tìm kiếm..." value="<?= htmlspecialchars($search) ?>">
            <select name="role_id" class="form-control mr-2">
                <option value="0">-- Tất cả vai trò --</option>
                <?php foreach ($roles as $role): ?>
                    <option value="<?= $role['id'] ?>" <?= $filter_role == $role['id'] ? 'selected' : '' ?>><?= ucfirst($role['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="status" class="form-control mr-2">
                <option value="-1">-- Tất cả trạng thái --</option>
                <option value="1" <?= $filter_status == 1 ? 'selected' : '' ?>>Hoạt động</option>
                <option value="0" <?= $filter_status == 0 ? 'selected' : '' ?>>Đã khóa</option>
            </select>
            <button type="submit" class="btn btn-primary">Lọc</button>
            <a href="admin.php?url=users" class="btn btn-secondary ml-2">Reset</a>
        </form>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th><th>Avatar</th><th>Họ tên</th><th>Email</th><th>Điện thoại</th><th>Vai trò</th><th>Trạng thái</th><th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr><td colspan="8" class="text-center">Không có người dùng nào.</td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $user):
                            $is_target_admin = ($user['role_id'] == 1);
                            $is_self = ($user['id'] == $current_user_id);
                            $can_edit = true;
                            if ($is_target_admin && !$is_super_admin && !$is_self) $can_edit = false;
                            // Xử lý avatar: nếu có thì dùng, không thì dùng mặc định
                            $avatarFile = !empty($user['avatar']) ? $user['avatar'] : 'avatar-default.png';
                            $avatarPath = $base . 'assets/img/avatars/' . $avatarFile;
                        ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><img src="<?= $avatarPath ?>" width="40" height="40" class="rounded-circle" onerror="this.src='<?= $base ?>assets/img/avatars/avatar-default.png'"></td>
                            <td><?= htmlspecialchars($user['name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['phone'] ?? '') ?></td>
                            <td><span class="badge badge-<?= $user['role_name'] == 'admin' ? 'danger' : ($user['role_name'] == 'staff' ? 'warning' : 'secondary') ?>"><?= ucfirst($user['role_name']) ?></span></td>
                            <td><?= $user['status'] == 1 ? '<span class="badge badge-success">Hoạt động</span>' : '<span class="badge badge-danger">Đã khóa</span>' ?></td>
                            <td style="white-space: nowrap;">
                                <?php if ($can_edit): ?>
                                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editModal<?= $user['id'] ?>" title="Sửa"><i class="fas fa-edit"></i></button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-secondary" disabled title="Không có quyền sửa admin này"><i class="fas fa-edit"></i></button>
                                <?php endif; ?>

                                <?php if ($user['status'] == 1): ?>
                                    <?php if ($user['id'] != $current_user_id): ?>
                                        <?php if ($is_target_admin && $user['id'] == 1 && !$is_super_admin): ?>
                                            <button class="btn btn-sm btn-secondary" disabled title="Không thể vô hiệu hóa super admin"><i class="fas fa-ban"></i></button>
                                        <?php else: ?>
                                            <a href="admin.php?url=user-delete&id=<?= $user['id'] ?>" class="btn btn-sm btn-warning" title="Vô hiệu hóa" onclick="return confirm('Vô hiệu hóa người dùng này?')"><i class="fas fa-ban"></i></a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-secondary" disabled title="Không thể vô hiệu hóa chính mình"><i class="fas fa-ban"></i></button>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <a href="admin.php?url=user-restore&id=<?= $user['id'] ?>" class="btn btn-sm btn-success" title="Khôi phục" onclick="return confirm('Khôi phục người dùng này?')"><i class="fas fa-undo-alt"></i></a>
                                <?php endif; ?>

                                <?php if ($user['id'] != $current_user_id): ?>
                                    <?php if ($is_target_admin && $user['id'] == 1 && !$is_super_admin): ?>
                                        <button class="btn btn-sm btn-secondary" disabled title="Không thể xóa super admin"><i class="fas fa-trash-alt"></i></button>
                                    <?php else: ?>
                                        <a href="admin.php?url=user-hard-delete&id=<?= $user['id'] ?>" class="btn btn-sm btn-danger" title="Xóa vĩnh viễn" onclick="return confirm('⚠️ XÓA VĨNH VIỄN! Bạn chắc chắn?')"><i class="fas fa-trash-alt"></i></a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-secondary" disabled title="Không thể xóa chính mình"><i class="fas fa-trash-alt"></i></button>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <!-- Modal sửa user -->
                        <div class="modal fade" id="editModal<?= $user['id'] ?>">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="admin.php?url=user-edit">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Sửa người dùng</h5>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="action" value="edit">
                                            <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                            <div class="form-group"><label>Họ tên</label><input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required></div>
                                            <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required></div>
                                            <div class="form-group"><label>Điện thoại</label><input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>"></div>
                                            <div class="form-group"><label>Mật khẩu mới (để trống nếu không đổi)</label><input type="password" name="password" class="form-control" placeholder="Nhập nếu muốn đổi"></div>
                                            <div class="form-group"><label>Vai trò</label>
                                                <select name="role_id" class="form-control" <?= ($user['role_id'] == 1 && !$is_super_admin && $user['id'] != $current_user_id) ? 'disabled' : '' ?>>
                                                    <?php foreach ($roles as $role): ?>
                                                        <option value="<?= $role['id'] ?>" <?= $user['role_id'] == $role['id'] ? 'selected' : '' ?>><?= ucfirst($role['name']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <?php if ($user['role_id'] == 1 && !$is_super_admin && $user['id'] != $current_user_id): ?>
                                                    <small class="text-muted">Bạn không thể thay đổi vai trò của admin này.</small>
                                                <?php endif; ?>
                                            </div>
                                            <div class="form-group"><label>Trạng thái</label>
                                                <select name="status" class="form-control">
                                                    <option value="1" <?= $user['status']==1?'selected':'' ?>>Hoạt động</option>
                                                    <option value="0" <?= $user['status']==0?'selected':'' ?>>Khóa</option>
                                                </select>
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
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <!-- Phân trang -->
        <?php if ($totalPages > 1): ?>
            <nav><ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                        <a class="page-link" href="admin.php?url=users&page=<?= $i ?>&search=<?= urlencode($search) ?>&role_id=<?= $filter_role ?>&status=<?= $filter_status ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul></nav>
        <?php endif; ?>
    </div>
</div>

<!-- Modal thêm user -->
<div class="modal fade" id="addUserModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="admin.php?url=user-add">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm người dùng mới</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group"><label>Họ tên</label><input type="text" name="name" class="form-control" required></div>
                    <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" required></div>
                    <div class="form-group"><label>Điện thoại</label><input type="text" name="phone" class="form-control"></div>
                    <div class="form-group"><label>Mật khẩu</label><input type="password" name="password" class="form-control" required></div>
                    <div class="form-group"><label>Vai trò</label>
                        <select name="role_id" class="form-control">
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role['id'] ?>"><?= ucfirst($role['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group"><label>Trạng thái</label>
                        <select name="status" class="form-control">
                            <option value="1">Hoạt động</option>
                            <option value="0">Khóa</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Thêm</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; require_once 'includes/scripts.php'; ?>
