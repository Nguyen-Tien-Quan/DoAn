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
// ROLE CHECK (Chỉ Admin & Staff)
// ======================
if (!in_array($_SESSION['user']['role_id'], [1, 2])) {
    header('Location: ../index.php');
    exit;
}

// ======================
// CONTROLLER
// ======================
require_once __DIR__ . '/../app/controllers/Staff/OrderController.php';
require_once __DIR__ . '/../app/controllers/Staff/DashboardController.php';

// ======================
// VIEW HELPER
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
        if (isset($_GET['export'])) {
            require_once __DIR__ . '/../resources/views/pages/admin/includes/functions.php';
            exportReportCSV();
            exit;
        }

        $data = getDashboardData();
        $totalOrders    = $data['totalOrders'];
        $totalProducts  = $data['totalProducts'];
        $totalCustomers = $data['totalCustomers'];
        $totalRevenue   = $data['totalRevenue'];
        $pendingOrders  = $data['pendingOrders'];
        $lowStock       = $data['lowStock'];

        // Báo cáo
        require_once __DIR__ . '/../resources/views/pages/admin/includes/functions.php';
        $reportData = getReportData();
        $start_date = $reportData['start_date'];
        $end_date   = $reportData['end_date'];
        $report_type = $reportData['report_type'];
        $revenue    = $reportData['revenue'];
        $orders     = $reportData['orders'];
        $month      = $reportData['month'];
        $year       = $reportData['year'];
        $type = $_GET['type'] ?? 'day';
        $chartData = getRevenueChart($type);
        $topProducts = getTopProducts();
        $compare = getRevenueCompare();

        $view = view('dashboard');
        break;


    // ======================
    // DANH SÁCH ĐƠN HÀNG
    // ======================
    case 'orders':
        $page = (int)($_GET['page'] ?? 1);
        $status = $_GET['status'] ?? '';

        $result = staffGetOrders($page, 10, $status);

        $orders = $result['data'] ?? [];
        $totalPages = $result['totalPages'] ?? 1;
        $currentPage = $page;

        $view = view('orders', [
            'orders'       => $orders,
            'totalPages'   => $totalPages,
            'currentPage'  => $currentPage,
            'status'       => $status
        ]);
        break;


    // ======================
    // CHI TIẾT ĐƠN HÀNG (AJAX - JSON cho Modal)
    // ======================
    case 'order-detail':
        header('Content-Type: application/json');

        $id = (int)($_GET['id'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID đơn hàng không hợp lệ']);
            exit;
        }

        $data = staffGetOrderDetail($id);

        if (!$data || !isset($data['order'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Không tìm thấy đơn hàng'
            ]);
            exit;
        }

        echo json_encode([
            'success' => true,
            'order'   => $data['order'],
            'items'   => $data['items'] ?? []
        ]);
        exit;


    // ======================
    // CẬP NHẬT TRẠNG THÁI
    // ======================
    case 'order-update':
        header('Content-Type: application/json');

        $order_id = (int)($_POST['order_id'] ?? 0);
        $status   = $_POST['status'] ?? '';

        if ($order_id <= 0 || empty($status)) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
            exit;
        }

        echo json_encode(staffUpdateOrderStatus($order_id, $status));
        exit;


    // ======================
    // DEFAULT
    // ======================
    default:
        $view = view('dashboard');
        break;
}

// ======================
// LOAD LAYOUT
// ======================
include $layout;
