<?php
$base = '/DoAn/DoAnTotNghiep/public/';


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>TrQShop</title>
    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="76x76" href="<?= $base ?>assets/favicon/apple-touch-icon.png" />
    <link rel="icon" type="image/png" sizes="32x32" href="<?= $base ?>assets/favicon/favicon-32x32.png" />
    <link rel="icon" type="image/png" sizes="16x16" href="<?= $base ?>assets/favicon/favicon-16x16.png" />
    <link rel="manifest" href="<?= $base ?>assets/favicon/site.webmanifest" />
    <meta name="msapplication-TileColor" content="#da532c" />
    <meta name="theme-color" content="#ffffff" />

    <!-- CSS -->
    <link rel="stylesheet" href="<?= $base ?>assets/fonts/stylesheet.css">

    <link rel="stylesheet" href="<?= $base ?>assets/css/main.css">

    <script src="<?= $base ?>assets/js/scripts.js"></script>
</head>

<body>



    <?php
    if (!empty($view) && file_exists($view)) {
        include $view;
    } else {
        echo "❌ Không tìm thấy view: " . ($view ?? 'null');
    }
    ?>

    <script src="<?= $base ?>assets/js/main.js"></script>
</body>
</html>
