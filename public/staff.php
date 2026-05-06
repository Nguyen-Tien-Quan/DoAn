<?php
session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh');

$base = '/DoAn/DoAnTotNghiep/public/';

// ======================
// LOGIN CHECK
// ======================
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php?url=login');
    exit;
}

// ======================
// ROLE CHECK
// ======================
if (!in_array($_SESSION['user']['role_id'], [1, 2])) {
    header('Location: ../index.php');
    exit;
}

// ======================
// CONTROLLER
// ======================
require_once __DIR__ . '/../app/controllers/Staff/OrderController.php';

// ======================
// VIEW HELPER (FIX ĐÃ ĐỔI orders.php)
// ======================
function view($name, $data = [])
{
    extract($data);

    $path = __DIR__ . "/../resources/views/pages/staff/$name.php";

    if (!file_exists($path)) {
        die("❌ Không tìm thấy view: " . $path);
    }

    return $path;
}

// ======================
// ROUTER
// ======================
$url = $_GET['url'] ?? 'orders';

// layout staff
$layout = __DIR__ . '/../resources/views/layouts/staff.php';

switch ($url) {

    // ======================
    // DASHBOARD
    // ======================
    case 'dashboard':
        $view = view('dashboard', []);
        break;

    // ======================
    // ORDERS (CHỈ DÙNG orders.php)
    // ======================
    case 'orders':

        $page = $_GET['page'] ?? 1;
        $status = $_GET['status'] ?? '';

        $result = staffGetOrders($page, 10, $status);

        $orders = $result['data'] ?? [];
        $totalPages = $result['totalPages'] ?? 1;
        $currentPage = $page;

        // 👉 FIX QUAN TRỌNG: chỉ orders.php
        $view = view('orders', [
            'orders' => $orders,
            'totalPages' => $totalPages,
            'currentPage' => $currentPage,
            'status' => $status
        ]);
        break;

    // ======================
    // ORDER DETAIL (vẫn dùng orders.php nếu muốn)
    // ======================
    case 'order-detail':

        $id = $_GET['id'] ?? 0;

        $data = staffGetOrderDetail($id);

        if (!$data || !isset($data['order'])) {
            echo "Không tìm thấy đơn";
            exit;
        }

        $view = view('orders', [
            'order_detail' => $data['order'],
            'items' => $data['items'] ?? []
        ]);
        break;

    // ======================
    // UPDATE STATUS
    // ======================
    case 'order-update':

        header('Content-Type: application/json');

        $order_id = $_POST['order_id'] ?? 0;
        $status = $_POST['status'] ?? '';

        echo json_encode(staffUpdateOrderStatus($order_id, $status));
        exit;

    // ======================
    // CANCEL ORDER
    // ======================
    case 'order-cancel':

        header('Content-Type: application/json');

        $order_id = $_POST['order_id'] ?? 0;

        echo json_encode(staffCancelOrder($order_id));
        exit;

    // ======================
    // DEFAULT
    // ======================
    default:
        $view = view('orders', [
            'orders' => [],
            'totalPages' => 1,
            'currentPage' => 1,
            'status' => ''
        ]);
        break;
}

// ======================
// LOAD LAYOUT
// ======================
include $layout;
