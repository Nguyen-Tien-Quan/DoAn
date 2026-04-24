<?php
$base = '/DoAn/DoAnTotNghiep/public/';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= $base ?>assets/css/main.css">

    <script src="<?= $base ?>assets/js/scripts.js"></script>
</head>

<body>

    <!-- HEADER CONDITIONAL -->
    <?php if (isset($_SESSION['user']) && !empty($_SESSION['user'])): ?>
        <?php include __DIR__ . '/../partials/header-logined.php'; ?>
    <?php else: ?>
        <?php include __DIR__ . '/../partials/header.php'; ?>
    <?php endif; ?>



    <?php
    if (!empty($view) && file_exists($view)) {
        include $view;
    } else {
        echo "❌ Không tìm thấy view: " . ($view ?? 'null');
    }
    ?>

    <div class="floating-contact">

        <!-- Facebook -->
        <a href="https://facebook.com/yourpage" target="_blank" class="contact-btn fb">
            <i class="fab fa-facebook-f"></i>
        </a>

        <!-- Zalo -->
        <a href="https://zalo.me/0123456789" target="_blank" class="contact-btn zalo">
            <i class="fas fa-comment-dots"></i>
        </a>

        <!-- Phone -->
        <a href="tel:0979797807" class="contact-btn phone">
            <i class="fas fa-phone"></i>
        </a>

    </div>


    <?php include __DIR__ . '/../partials/footer.php'; ?>
    <script src="<?= $base ?>assets/js/main.js" ></script>
    <!-- Ví dụ đoạn mã nhúng (bạn sẽ nhận được mã thực tế từ nền tảng) -->
    <script src="https://your-chatbot-platform.com/widget.js?id=YOUR_BOT_ID" async></script>
</body>
</html>
