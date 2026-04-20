<?php
session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh');

$base = '/DoAn/DoAnTotNghiep/public/';

// ======================
// 🔐 CHECK LOGIN
// ======================
if (!isset($_SESSION['user'])) {
    header('Location: index.php?url=login');
    exit;
}

// ======================
// 🔐 CHECK ROLE
// ======================
if (!in_array($_SESSION['user']['role_id'], [1, 2])) {
    header('Location: index.php');
    exit;
}

// ======================
// LOAD CONTROLLER
// ======================
require_once __DIR__ . '/../app/controllers/Admin/DashboardController.php';
require_once __DIR__ . '/../app/controllers/Admin/CategoryController.php';
require_once __DIR__ . '/../app/controllers/Admin/ProductController.php';
require_once __DIR__ . '/../app/controllers/Admin/UserController.php';
require_once __DIR__ . '/../app/controllers/Admin/OrderController.php';
require_once __DIR__ . '/../app/controllers/Admin/ReviewController.php';


// ======================
// HELPER VIEW
// ======================
function view($name) {
    return __DIR__ . "/../resources/views/pages/admin/$name.php";
}

// ======================
// ROUTER ADMIN
// ======================
$url = $_GET['url'] ?? 'dashboard';

$layout = __DIR__ . '/../resources/views/layouts/admin.php';

switch ($url) {

    // ===== DASHBOARD =====
    case 'dashboard':
        // Xử lý export CSV nếu có
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
        $role           = $_SESSION['user']['role_id'];

        // Lấy dữ liệu báo cáo
        require_once __DIR__ . '/../resources/views/pages/admin/includes/functions.php';
        $reportData = getReportData();
        $start_date = $reportData['start_date'];
        $end_date   = $reportData['end_date'];
        $report_type = $reportData['report_type'];
        $revenue    = $reportData['revenue'];
        $orders     = $reportData['orders'];
        $month      = $reportData['month'];
        $year       = $reportData['year'];

        $view = view('dashboard');
    break;

    // ===== CATEGORY =====
    case 'categories':
        $categories = getCategories();
        $view = view('categories');
        break;

    case 'category-add':
        handleAddCategory();
        break;

    case 'category-update':
        handleUpdateCategory();
        break;

    case 'category-delete':
        handleDeleteCategory();
        break;

    case 'category-restore':
        handleRestoreCategory();
        break;

    // ===== PRODUCT =====
    case 'products':

    // ✅ XỬ LÝ ACTION TRƯỚC
        if (isset($_GET['action'])) {
            switch ($_GET['action']) {
                case 'delete':
                    handleDeleteProduct();
                    break;

                case 'restore':
                    handleRestoreProduct();
                    break;

                case 'hard_delete':
                    handleHardDeleteProduct();
                    break;
            }
        }

        $page = $_GET['page'] ?? 1;

        $filters = [
            'search' => $_GET['search'] ?? '',
            'category_id' => $_GET['category_id'] ?? 0,
            'status' => $_GET['status'] ?? -1
        ];

        $result = getAllProducts($page, 10, $filters);

        $products     = $result['data'];
        $totalPages   = $result['totalPages'];
        $currentPage  = $result['currentPage'];

        $categories = getCategoryOptions();

        $is_admin = isset($_SESSION['user']['role_id']) && $_SESSION['user']['role_id'] == 1;

        $view = view('products');
    break;

    case 'product-edit':
    require_once __DIR__ . '/../app/controllers/Admin/ProductController.php';
    handleEditProduct(); // hoặc tự xử lý

    $view = view('product-edit');
    break;

        // ===== USER =====
    case 'users':
        $page = $_GET['page'] ?? 1;
        $filters = [
            'search' => $_GET['search'] ?? '',
            'role_id' => $_GET['role_id'] ?? 0,
            'status' => $_GET['status'] ?? -1
        ];
        $result = getUsers($page, 10, $filters);
        $users = $result['data'];
        $total = $result['total'];
        $totalPages = $result['totalPages'];
        $currentPage = $result['currentPage'];
        $roles = getAllRoles();
        $search = $filters['search'];
        $filter_role = $filters['role_id'];
        $filter_status = $filters['status'];
        $success = $_SESSION['success'] ?? null;
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['success'], $_SESSION['error']);
        $current_user_id = $_SESSION['user']['id'] ?? 0;
        $is_super_admin = ($current_user_id == 1);
        $current_role = getCurrentUserRole($current_user_id);
        $view = view('users');
        break;

    case 'user-add':
        handleAddUser();
        break;

    case 'user-edit':
        handleEditUser();
        break;

    case 'user-delete':
        handleDeleteUser();
        break;

    case 'user-restore':
        handleRestoreUser();
        break;

    case 'user-hard-delete':
        handleHardDeleteUser();
        break;


    // ===== ORDERS =====
    case 'orders':

    // ✅ AJAX UPDATE STATUS
    if (isset($_POST['ajax']) && $_POST['ajax'] === 'update_status') {
        header('Content-Type: application/json');

        $order_id = (int)($_POST['order_id'] ?? 0);
        $new_status = $_POST['new_status'] ?? '';

        $result = updateOrderStatus($order_id, $new_status);

        echo json_encode($result);
        exit; // ❗ BẮT BUỘC phải có
    }

    // ✅ AJAX LOAD DETAIL
    if (isset($_GET['ajax']) && $_GET['ajax'] === 'detail') {
        $id = (int)($_GET['id'] ?? 0);
        echo renderOrderDetailHTML($id);
        exit; // ❗ BẮT BUỘC phải có
    }

    // ===== NORMAL PAGE =====
    $page = $_GET['page'] ?? 1;
    $filters = [
        'search' => $_GET['search'] ?? '',
        'status' => $_GET['status'] ?? ''
    ];

    $result = getOrders($page, 10, $filters);

    $orders = $result['data'];
    $total = $result['total'];
    $totalPages = $result['totalPages'];
    $currentPage = $page;
    $search = $filters['search'];
    $status_filter = $filters['status'];

    $view = view('orders');
break;


    case 'variants':
        ensureVariantTable();

        // Xử lý các action
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            if ($_POST['action'] === 'add') handleAddVariant();
            elseif ($_POST['action'] === 'edit') handleUpdateVariant();
        }
        if (isset($_GET['id']) && isset($_GET['delete'])) handleDeleteVariant();

        // Lấy dữ liệu cho view
        $variants = getAllVariants();
        $products = getAllProductsForVariant();
        $success = $_SESSION['success'] ?? null;
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['success'], $_SESSION['error']);

        $view = view('variants');
        break;

    case 'variant-delete':
        require_once __DIR__ . '/../app/controllers/Admin/VariantController.php';
        handleDeleteVariant();
    break;

    case 'toppings':
        ensureToppingTable();

        // Xử lý POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            if ($_POST['action'] === 'add') handleAddTopping();
            elseif ($_POST['action'] === 'edit') handleUpdateTopping();
        }

        // Lấy dữ liệu
        $toppings = getAllToppings();
        $success = $_SESSION['success'] ?? null;
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['success'], $_SESSION['error']);

        $view = view('toppings');
        break;

    case 'topping-delete':
        require_once __DIR__ . '/../app/controllers/Admin/ToppingController.php';
        handleDeleteTopping();
        break;

    case 'topping-restore':
        require_once __DIR__ . '/../app/controllers/Admin/ToppingController.php';
        handleRestoreTopping();
        break;

    case 'reviews':
    $page = $_GET['page'] ?? 1;

    $filters = [
        'search' => $_GET['search'] ?? '',
        'product_id' => $_GET['product_id'] ?? 0,
        'rating' => $_GET['rating'] ?? 0,
        'status' => $_GET['status'] ?? -1
    ];

    $result = getReviews($page, 15, $filters);

    $reviews = $result['data'];
    $total = $result['total'];

    $products = getProductsForFilter();

    $view = view('reviews');
    break;

    case 'vouchers':

        // Lấy tham số lọc và phân trang
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 15;
        $filters = [
            'search' => $_GET['search'] ?? '',
            'status' => isset($_GET['status']) ? (int)$_GET['status'] : -1,
            'discount_type' => $_GET['discount_type'] ?? ''
        ];

        $result = getVouchers($page, $limit, $filters);
        $vouchers = $result['data'];
        $totalPages = $result['totalPages'];

        $search = $filters['search'];
        $type_filter = $filters['discount_type'];
        $status_filter = $filters['status'];
        $success = $_SESSION['success'] ?? null;
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['success'], $_SESSION['error']);

        $view = view('vouchers');
    break;


    case 'voucher-add':
        handleAddVoucher();
        break;

    case 'voucher-delete':
        handleDeleteVoucher();
        break;

    case 'voucher-restore':
        handleRestoreVoucher();
        break;

    case 'settings':
        $view = view('settings');
        break;

    case 'report':
    require_once __DIR__ . '/../resources/views/pages/admin/includes/functions.php';
    if (isset($_GET['export'])) {
        exportReportCSV();
        exit;
    }
    $reportData = getReportData();
    $start_date = $reportData['start_date'];
    $end_date   = $reportData['end_date'];
    $report_type = $reportData['report_type'];
    $revenue    = $reportData['revenue'];
    $orders     = $reportData['orders'];
    $month      = $reportData['month'];
    $year       = $reportData['year'];
    $view = view('report');
    break;

    // ===== LOGOUT =====
    case 'logout':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        handleLogout();
        break;

    default:
        $view = view('dashboard');
        break;
}

// ======================
// LOAD LAYOUT
// ======================
include $layout;
