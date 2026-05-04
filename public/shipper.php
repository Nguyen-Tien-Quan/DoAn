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
        $page = $_GET['page'] ?? 1;
        $status = $_GET['status'] ?? 'all';

        $result = getShipperOrders($shipperId, $status, $page, 10);

        // ✅ lấy từ result
        $orders = $result['data'];
        $totalPages = $result['totalPages'];
        $currentPage = $result['currentPage'];

        $statusFilter = $status;

        // stats
        $stats = getShipperStats($shipperId);

        $view = __DIR__ . '/../resources/views/pages/shipper/dashboard.php';
    break;

    case 'available':
        $page = $_GET['page'] ?? 1;

        $result = getAvailableOrders($page, 10);

        $orders = $result['data'];
        $totalPages = $result['totalPages'];
        $currentPage = $result['currentPage'];

        $view = __DIR__ . '/../resources/views/pages/shipper/available.php';
        break;

    case 'order-detail':
        $orderId = $_GET['id'] ?? 0;
        $order = getShipperOrderDetail($orderId);

        if (!$order) {
            die("Không tìm thấy đơn hàng hoặc bạn không có quyền xem.");
        }

        $data = ['order' => $order];
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
if (isset($data)) extract($data);
include __DIR__ . '/../resources/views/layouts/shipper_layout.php';
