<?php

$success = $_SESSION['success'] ?? '';
$error   = $_SESSION['error'] ?? '';

unset($_SESSION['success'], $_SESSION['error']);

$avatar = !empty($profile['avatar'])
    ? $profile['avatar']
    : 'assets/img/undraw_profile.svg';

$profile     = $profile ?? [];
?>

<div id="content-wrapper">
<div id="content">

<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            Hồ sơ của tôi
        </h1>
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

    <div class="row">

        <!-- AVATAR -->
        <div class="col-xl-4 col-lg-5">

            <div class="card shadow mb-4">

                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        Ảnh đại diện
                    </h6>
                </div>

                <div class="card-body text-center">

                    <img src="<?= $avatar ?>"
                         class="rounded-circle mb-3"
                         width="150"
                         height="150"
                         style="object-fit:cover;">

                    <form method="POST"
                          enctype="multipart/form-data">

                        <input type="hidden"
                               name="action"
                               value="update_avatar">

                        <div class="custom-file mb-3">

                            <input type="file"
                                   class="custom-file-input"
                                   name="avatar"
                                   accept="image/*"
                                   required>

                            <label class="custom-file-label">
                                Chọn ảnh
                            </label>

                        </div>

                        <button type="submit"
                                class="btn btn-primary btn-sm">
                            Cập nhật ảnh
                        </button>

                    </form>

                </div>
            </div>
        </div>

        <!-- PROFILE -->
        <div class="col-xl-8 col-lg-7">

            <div class="card shadow mb-4">

                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        Thông tin cá nhân
                    </h6>
                </div>

                <div class="card-body">

                    <form method="POST">

                        <input type="hidden"
                               name="action"
                               value="update_profile">

                        <div class="form-group">
                            <label>Họ tên</label>

                            <input type="text"
                                   name="name"
                                   class="form-control"
                                   value="<?= htmlspecialchars($profile['name']) ?>"
                                   required>
                        </div>

                        <div class="form-group">
                            <label>Email</label>

                            <input type="email"
                                   name="email"
                                   class="form-control"
                                   value="<?= htmlspecialchars($profile['email']) ?>"
                                   required>
                        </div>

                        <div class="form-group">
                            <label>Số điện thoại</label>

                            <input type="text"
                                   name="phone"
                                   class="form-control"
                                   value="<?= htmlspecialchars($profile['phone'] ?? '') ?>">
                        </div>

                        <button type="submit"
                                class="btn btn-primary">
                            Cập nhật hồ sơ
                        </button>

                    </form>

                </div>
            </div>

            <!-- PASSWORD -->
            <div class="card shadow mb-4">

                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        Đổi mật khẩu
                    </h6>
                </div>

                <div class="card-body">

                    <form method="POST">

                        <input type="hidden"
                               name="action"
                               value="change_password">

                        <div class="form-group">
                            <label>Mật khẩu hiện tại</label>

                            <input type="password"
                                   name="current_password"
                                   class="form-control"
                                   required>
                        </div>

                        <div class="form-group">
                            <label>Mật khẩu mới</label>

                            <input type="password"
                                   name="new_password"
                                   class="form-control"
                                   required>
                        </div>

                        <div class="form-group">
                            <label>Xác nhận mật khẩu</label>

                            <input type="password"
                                   name="confirm_password"
                                   class="form-control"
                                   required>
                        </div>

                        <button type="submit"
                                class="btn btn-warning">
                            Đổi mật khẩu
                        </button>

                    </form>

                </div>
            </div>

        </div>
    </div>
</div>
</div>
</div>
