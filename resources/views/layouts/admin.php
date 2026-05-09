<?php
$base = '/DoAn/DoAnTotNghiep/public/';
if (session_status() === PHP_SESSION_NONE) session_start();

$user = $_SESSION['user'] ?? null;
$role = $user['role_id'] ?? null;

// 👉 ROUTE BASE THEO ROLE
$baseUrl = match ($role) {
    1 => 'admin.php',
    2 => 'staff.php',
    default => 'index.php'
};

// 👉 ROLE NAME
$roleName = match ($role) {
    1 => 'admin',
    2 => 'staff',
    default => 'guest'
};
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'TrQShop' ?></title>

    <link href="/DoAn/DoAnTotNghiep/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base ?>assets/css/sb-admin-2.css">
</head>


<body id="page-top">

<div id="wrapper">

    <!-- SIDEBAR -->
    <?php
    // 👉 chỉ dùng 1 sidebar, truyền role vào xử lý bên trong
    include __DIR__ . '/../pages/admin/includes/sidebar.php';
    ?>

    <div id="content-wrapper" class="d-flex flex-column">

        <div id="content">

            <!-- TOPBAR -->
            <?php include __DIR__ . '/../pages/admin/includes/topbar.php'; ?>

            <!-- CONTENT -->
            <div class="container-fluid">
                <?php
                if (!empty($view) && file_exists($view)) {
                    include $view;
                } else {
                    echo "❌ Không tìm thấy view";
                }
                ?>
            </div>

        </div>

        <!-- FOOTER -->
        <?php include __DIR__ . '/../pages/admin/includes/footer.php'; ?>

    </div>
</div>

<?php include __DIR__ . '/../pages/admin/includes/scripts.php'; ?>

</body>
</html>
