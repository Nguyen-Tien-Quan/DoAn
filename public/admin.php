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
        $type = $_GET['type'] ?? 'day';
        $chartData = getRevenueChart($type);
        $topProducts = getTopProducts();
        $compare = getRevenueCompare();

        $view = view('dashboard');
    break;

    // ===== CATEGORY =====
        // ===== CATEGORY =====
    case 'categories':
        // XỬ LÝ CÁC HÀNH ĐỘNG TRƯỚC KHI LOAD DANH SÁCH
        if (isset($_GET['soft_delete'])) {
            handleSoftDeleteCategory();
        }
        if (isset($_GET['restore'])) {
            handleRestoreCategory();
        }
        if (isset($_GET['hard_delete'])) {
            handleHardDeleteCategory();
        }

        $categories = getCategories();
        $view = view('categories');
        break;

    case 'category-add':
        handleAddCategory();
        break;

    case 'category-update':
        handleUpdateCategory();
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

    case 'product-add':
    handleAddProduct();
    break;

    case 'product-edit':
    editProduct();   // Dùng hàm mới, KHÔNG dùng handleEditProduct()
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

    header('Content-Type: text/html; charset=utf-8');

    // ================= UPDATE STATUS =================
    if (isset($_POST['ajax']) && $_POST['ajax'] === 'update_status') {
        header('Content-Type: application/json');

        $order_id = (int)($_POST['order_id'] ?? 0);
        $new_status = trim($_POST['new_status'] ?? '');

        if ($order_id <= 0 || $new_status === '') {
            echo json_encode([
                'success' => false,
                'message' => 'Thiếu dữ liệu'
            ]);
            exit;
        }

        $result = updateOrderStatus($order_id, $new_status);

        echo json_encode($result);
        exit;
    }

    // ================= CANCEL ORDER =================
    if (isset($_POST['ajax']) && $_POST['ajax'] === 'cancel') {
        header('Content-Type: application/json');

        $order_id = (int)($_POST['order_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');

        if ($order_id <= 0 || $reason === '') {
            echo json_encode([
                'success' => false,
                'message' => 'Thiếu dữ liệu'
            ]);
            exit;
        }

        try {
            $conn = getDB();
            $conn->beginTransaction();

            $stmt = $conn->prepare("
                UPDATE orders
                SET status = 'cancelled',
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$order_id]);

            $affected = $stmt->rowCount();

            if ($affected === 0) {
                $conn->rollBack();

                echo json_encode([
                    'success' => false,
                    'message' => 'Không tìm thấy đơn hoặc không thể cập nhật'
                ]);
                exit;
            }

            $conn->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Hủy đơn thành công'
            ]);
        } catch (Exception $e) {
            $conn->rollBack();

            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }

        exit;
    }

    // ================= ORDER DETAIL =================
    if (isset($_GET['ajax']) && $_GET['ajax'] === 'detail') {
        $id = (int)($_GET['id'] ?? 0);
        echo renderOrderDetailHTML($id);
        exit;
    }

    // ================= NORMAL PAGE =================
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


        // ===== VARIANTS (SIZE / BIẾN THỂ) =====
    case 'variants':
    // Xử lý các action GET (vô hiệu hóa - khôi phục - xóa vĩnh viễn)
    if (isset($_GET['soft_delete'])) {
        handleSoftDeleteVariant();
    }
    if (isset($_GET['restore'])) {
        handleRestoreVariant();
    }
    if (isset($_GET['hard_delete'])) {
        handleHardDeleteVariant();
    }

    // Xử lý POST (thêm / sửa)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        if ($_POST['action'] === 'add')  handleAddVariant();
        if ($_POST['action'] === 'edit') handleUpdateVariant();
    }

    ensureVariantTable();

    // --- Phân trang và filter ---
    $page  = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = 10;
    $filters = [
        'search'     => $_GET['search'] ?? '',
        'product_id' => isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0,
        'status'     => isset($_GET['status']) ? (int)$_GET['status'] : -1
    ];

    $variantsData = getVariantsPaginated($page, $limit, $filters);
    $variants     = $variantsData['data'];
    $totalPages   = $variantsData['totalPages'];
    $currentPage  = $variantsData['currentPage'];

    // Danh sách sản phẩm cho modal
    $products = getAllProductsForVariant();

    // Flash messages
    $success = $_SESSION['success'] ?? null;
    $error   = $_SESSION['error']   ?? null;
    unset($_SESSION['success'], $_SESSION['error']);

    $view = view('variants');
    break;

    // Nếu bạn vẫn muốn giữ case riêng cho delete
    case 'variant-delete':
        require_once __DIR__ . '/../app/controllers/Admin/VariantController.php';
        handleHardDeleteVariant();
        break;

    case 'toppings':
    ensureToppingTable();

    // ✅ XỬ LÝ GET (QUAN TRỌNG)
    if (isset($_GET['delete'])) {
        handleDeleteTopping();
    }

    if (isset($_GET['restore'])) {
        handleRestoreTopping();
    }

    if (isset($_GET['hard_delete'])) {
        handleHardDeleteTopping();
    }

    // ✅ XỬ LÝ POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        if ($_POST['action'] === 'add') handleAddTopping();
        elseif ($_POST['action'] === 'edit') handleUpdateTopping();
    }

    $toppings = getAllToppings();

    $success = $_SESSION['success'] ?? null;
    $error   = $_SESSION['error'] ?? null;
    unset($_SESSION['success'], $_SESSION['error']);

    $view = view('toppings');
    break;

    case 'topping-hard-delete':
        handleHardDeleteTopping();
        break;

    case 'topping-restore':
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

        // ✅ đảm bảo có bảng
        ensureVoucherTable();

        // ✅ xử lý action giống UI cũ
        if (isset($_GET['soft_delete'])) {
            $_GET['id'] = $_GET['soft_delete'];
            handleDeleteVoucher();
        }

        if (isset($_GET['restore'])) {
            $_GET['id'] = $_GET['restore'];
            handleRestoreVoucher();
        }

        if (isset($_GET['hard_delete'])) {
            $_GET['id'] = $_GET['hard_delete'];
            handleHardDeleteVoucher();
        }

        // ✅ xử lý POST (add + edit)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            if ($_POST['action'] === 'add') handleAddVoucher();
            if ($_POST['action'] === 'edit') handleEditVoucher();
        }

        // ===== load data =====
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
        $error   = $_SESSION['error'] ?? null;
        unset($_SESSION['success'], $_SESSION['error']);

        $view = view('vouchers');
    break;


    case 'voucher-delete':
        handleDeleteVoucher();
        break;

    case 'voucher-restore':
        handleRestoreVoucher();
        break;

    case 'voucher-hard-delete':
        handleHardDeleteVoucher();
        break;

    case 'voucher-edit':
        handleEditVoucher();
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
