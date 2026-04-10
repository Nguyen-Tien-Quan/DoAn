<?php
$base = '/DoAn/DoAnTotNghiep/public/';
if (session_status() === PHP_SESSION_NONE) session_start();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Admin - TrQShop</title>

    <link href="/DoAn/DoAnTotNghiep/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base ?>assets/css/sb-admin-2.css">
    <script src="<?= $base ?>assets/js/scripts.js"></script>
</head>

<body id="page-top">

<div id="wrapper">

    <!-- Sidebar -->
    <?php include __DIR__ . '/../pages/admin/includes/sidebar.php'; ?>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">

            <!-- Topbar -->
            <?php include __DIR__ . '/../pages/admin/includes/topbar.php'; ?>

            <!-- 🔥 CONTENT -->
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

        <!-- Footer -->
        <?php include __DIR__ . '/../pages/admin/includes/footer.php'; ?>
    </div>

</div>

<?php include __DIR__ . '/../pages/admin/includes/scripts.php'; ?>

</body>
</html>
