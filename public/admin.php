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
require_once __DIR__ . '/../app/controllers/Admin/ProfileController.php';


// ======================
// HELPER VIEW
// ======================
function view($name, $data = []) {
    extract($data);
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

    case 'profile':

        // update profile
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            updateProfile();
        }

        $profile = getProfile($_SESSION['user']['id']);

        $success = $_SESSION['success'] ?? null;
        $error   = $_SESSION['error'] ?? null;

        unset($_SESSION['success'], $_SESSION['error']);

        $view = view('profile');
    break;

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
        // ===== USER =====
    case 'users':

        $search        = $_GET['search'] ?? '';
        $filter_role   = $_GET['role_id'] ?? 0;
        $filter_status = $_GET['status'] ?? -1;
        $page          = $_GET['page'] ?? 1;

        $roles = getAllRoles() ?? [];

        $result = getUsers($page, 10, [
            'search'   => $search,
            'role_id'  => $filter_role,
            'status'   => $filter_status
        ]);

        $users       = $result['data'];
        $totalPages  = $result['totalPages'];
        $currentPage = $result['currentPage'];

        $current_user_id = $_SESSION['user']['id'] ?? 0;
        $is_super_admin  = ($current_user_id == 1);
        $base            = $base ?? '/DoAn/DoAnTotNghiep/public/';

        // Flash messages
        $success = $_SESSION['success'] ?? null;
        $error   = $_SESSION['error']   ?? null;
        unset($_SESSION['success'], $_SESSION['error']);

        // Truyền tất cả biến vào view
        $data = [
            'users'          => $users,
            'roles'          => $roles,
            'search'         => $search,
            'filter_role'    => $filter_role,
            'filter_status'  => $filter_status,
            'totalPages'     => $totalPages,
            'currentPage'    => $currentPage,
            'current_user_id'=> $current_user_id,
            'is_super_admin' => $is_super_admin,
            'base'           => $base,
            'success'        => $success,
            'error'          => $error,
        ];

        $view = view('users', $data);
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

    // ======================
    // HANDLE REVIEW ACTIONS
    // ======================

    $actionResult = handleReviewActions();

    $success = $actionResult['success'] ?? null;
    $error   = $actionResult['error'] ?? null;

    // ======================
    // PAGINATION
    // ======================

    $page = isset($_GET['page'])
        ? max(1, (int)$_GET['page'])
        : 1;

    $limit = 15;

    // ======================
    // FILTERS
    // ======================

    $filters = [

        'search' => trim($_GET['search'] ?? ''),

        'product_id' => isset($_GET['product_id'])
            ? (int)$_GET['product_id']
            : 0,

        'rating' => isset($_GET['rating'])
            ? (int)$_GET['rating']
            : 0,

        'status' => isset($_GET['status'])
            ? (int)$_GET['status']
            : -1
    ];

    // ======================
    // GET REVIEWS
    // ======================

    $result = getReviews($page, $limit, $filters);

    $reviews = $result['data'] ?? [];

    $total = $result['total'] ?? 0;

    $totalPages = ceil($total / $limit);

    // ======================
    // PRODUCTS FILTER
    // ======================

    $products = getProductsForFilter();

    // ======================
    // FILTER VARIABLES
    // ======================

    $search          = $filters['search'];
    $product_filter  = $filters['product_id'];
    $rating_filter   = $filters['rating'];
    $status_filter   = $filters['status'];

    // ======================
    // FLASH SESSION
    // ======================

    if (isset($_SESSION['success'])) {
        $success = $_SESSION['success'];
        unset($_SESSION['success']);
    }

    if (isset($_SESSION['error'])) {
        $error = $_SESSION['error'];
        unset($_SESSION['error']);
    }

    // ======================
    // VIEW
    // ======================

    $view = view('reviews');

break;

    case 'vouchers':

    ensureVoucherTable();

    // ===== XỬ LÝ GET ACTION (delete, restore, hard_delete) =====
    $action = $_GET['action'] ?? '';
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($action === 'delete' && $id > 0) {
        $_GET['id'] = $id; // controller dùng $_GET['id']
        handleDeleteVoucher();
        exit;
    }
    if ($action === 'restore' && $id > 0) {
        $_GET['id'] = $id;
        handleRestoreVoucher();
        exit;
    }
    if ($action === 'hard_delete' && $id > 0) {
        $_GET['id'] = $id;
        handleHardDeleteVoucher();
        exit;
    }

    // (Giữ lại hỗ trợ link kiểu cũ nếu có: soft_delete=, restore=, hard_delete=)
    if (isset($_GET['soft_delete'])) {
        $_GET['id'] = (int)$_GET['soft_delete'];
        handleDeleteVoucher();
        exit;
    }
    if (isset($_GET['restore'])) {
        $_GET['id'] = (int)$_GET['restore'];
        handleRestoreVoucher();
        exit;
    }
    if (isset($_GET['hard_delete'])) {
        $_GET['id'] = (int)$_GET['hard_delete'];
        handleHardDeleteVoucher();
        exit;
    }

    // ===== XỬ LÝ POST (add, edit) =====
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $postAction = $_POST['action'] ?? '';
        if ($postAction === 'add') {
            handleAddVoucher();
            exit;
        }
        if ($postAction === 'edit') {
            handleEditVoucher();
            exit;
        }
    }

    // ===== HIỂN THỊ DANH SÁCH =====
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = 15;

    $filters = [
        'search'        => $_GET['search'] ?? '',
        'status'        => isset($_GET['status']) ? (int)$_GET['status'] : -1,
        'discount_type' => $_GET['discount_type'] ?? ''
    ];

    $result = getVouchers($page, $limit, $filters);

    $vouchers      = $result['data'];
    $totalPages    = $result['totalPages'];
    $search        = $filters['search'];
    $status_filter = $filters['status'];
    $type_filter   = $filters['discount_type'];

    $is_admin = ($_SESSION['user']['role_id'] ?? 0) == 1;

    $success = $_SESSION['success'] ?? null;
    $error   = $_SESSION['error']   ?? null;
    unset($_SESSION['success'], $_SESSION['error']);

    $view = view('vouchers');
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
