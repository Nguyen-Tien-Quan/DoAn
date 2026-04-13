<?php
session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh');

require_once __DIR__ . '/../app/controllers/shipper/ShipperController.php';

// Kiểm tra quyền shipper
checkShipperAuth();

$action = $_GET['action'] ?? 'dashboard';
$shipperId = $_SESSION['user']['id'];

// Xử lý action
switch ($action) {
    case 'dashboard':
        $statusFilter = $_GET['status'] ?? 'all';
        $stats = getShipperStats($shipperId);
        $orders = getShipperOrders($shipperId, $statusFilter);
        $view = __DIR__ . '/../resources/views/pages/shipper/dashboard.php';
        break;

    case 'available':
        $orders = getAvailableOrders();
        $view = __DIR__ . '/../resources/views/pages/shipper/available.php';
        break;

    case 'order-detail':
        $orderId = $_GET['id'] ?? 0;
        $order = getShipperOrderDetail($orderId);
        if (!$order) {
            die("Không tìm thấy đơn hàng hoặc bạn không có quyền xem.");
        }
        $view = __DIR__ . '/../resources/views/pages/shipper/order_detail.php';
        break;

    case 'history':
        $history = getShipperHistory($shipperId);
        $view = __DIR__ . '/../resources/views/pages/shipper/history.php';
        break;

    // AJAX endpoints
    case 'accept-order':
        acceptOrder();
        break;
    case 'update-status':
        updateDeliveryStatus();
        break;

    default:
        header('Location: shipper.php?action=dashboard');
        exit;
}

// Load layout shipper
include __DIR__ . '/../resources/views/layouts/shipper_layout.php';
