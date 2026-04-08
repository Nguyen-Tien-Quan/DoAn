<?php
session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh');

$base = '/DoAn/DoAnTotNghiep/public/';

require_once __DIR__ . '/../app/controllers/HomeController.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/CartController.php';
require_once __DIR__ . '/../app/controllers/FavoritesController.php';
require_once __DIR__ . '/../app/controllers/OrderController.php';
require_once __DIR__ . '/../app/controllers/PaymentController.php';
require_once __DIR__ . '/../app/controllers/SettingsController.php';

// Lấy danh sách sản phẩm (dùng cho home)
$products = getProducts();

function view($name) {
    return __DIR__ . "/../resources/views/pages/$name.php";
}

// Router
$url = $_GET['url'] ?? 'home';

// Layout mặc định
$layout = __DIR__ . '/../resources/views/layouts/layout.php';

switch ($url) {

    // ==================== AUTH ====================
    case 'register':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $error = handleRegister();
        }
        $view = view('register');
        $layout = __DIR__ . '/../resources/views/layouts/auth.php';
        break;

    case 'login':
        $view = view('login');
        $layout = __DIR__ . '/../resources/views/layouts/auth.php';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $error = handleLogin();
        }
        break;

    case 'logout':
        handleLogout();
        break;

    case 'forgot-password':
        $view = view('forgot-password');
        $layout = __DIR__ . '/../resources/views/layouts/auth.php';
        break;

    // ==================== SETTINGS ====================
    case 'settings':
        // Lấy dữ liệu từ hàm trong SettingsController
        $settingsData = getSettingsData();
        $user = $settingsData['user'];
        $addresses = $settingsData['addresses'];
        $notifications = $settingsData['notifications'];
        $success = $settingsData['success'];
        $error = $settingsData['error'];
        $view = view('settings');
        // KHÔNG exit, để layout tự include
        break;

    case 'settings/updateProfile':
        updateProfile();
        exit;
        break;

    case 'settings/changePassword':
        changePassword();
        exit;
        break;

    case 'settings/addAddress':
        addAddress();
        exit;
        break;

    case 'settings/updateAddress':
        updateAddress();
        exit;
        break;

    case 'settings/deleteAddress':
        deleteAddress();
        exit;
        break;

    case 'settings/markNotificationRead':
        markNotificationRead();
        exit;
        break;

    case 'settings/markAllRead':
        markAllRead();
        exit;
        break;

    // ==================== CHECKOUT, PROFILE, SHIPPING ====================
    case 'checkout':
        $view = view('checkout');
        break;

    case 'profile':
        $view = view('profile');
        break;

    case 'shipping':
        require_once __DIR__ . '/../app/controllers/OrderController.php';
        $addresses = [];
        if (isset($_SESSION['user'])) {
            $addresses = getShippingAddresses($_SESSION['user']['id']);
        }
        $view = view('shipping');
        break;

    case 'add-shipping-address':
        require_once __DIR__ . '/../app/controllers/OrderController.php';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json; charset=utf-8');
            try {
                addShippingAddress();
            } catch (\Throwable $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Lỗi server: ' . $e->getMessage()
                ]);
            }
            exit;
        }
        break;

    case 'payment':
        // Nếu có action (AJAX)
        if (isset($_GET['action'])) {
            $action = $_GET['action'];

            header('Content-Type: application/json; charset=utf-8');

            try {
                switch ($action) {
                    case 'list':
                        $payments = getPayments($conn);
                        echo json_encode($payments);
                        break;
                    case 'detail':
                        $order_id = $_GET['order_id'] ?? 0;
                        $payment = getPaymentByOrder($conn, $order_id);
                        echo json_encode($payment);
                        break;
                    case 'create':
                        $data = json_decode(file_get_contents('php://input'), true);
                        $id = createPayment(
                            $conn,
                            $data['order_id'],
                            $data['method'],
                            $data['amount'],
                            $data['transaction_code'] ?? null
                        );
                        echo json_encode(['payment_id' => $id]);
                        break;
                    case 'update':
                        $data = json_decode(file_get_contents('php://input'), true);
                        $count = updatePaymentStatus(
                            $conn,
                            $data['payment_id'],
                            $data['status'],
                            $data['paid_at'] ?? null
                        );
                        echo json_encode(['updated_rows' => $count]);
                        break;
                    case 'delete':
                        $data = json_decode(file_get_contents('php://input'), true);
                        $count = deletePayment($conn, $data['payment_id']);
                        echo json_encode(['deleted_rows' => $count]);
                        break;
                    case 'complete':
                        $data = json_decode(file_get_contents('php://input'), true);
                        $count = completePayment(
                            $conn,
                            $data['payment_id'],
                            $data['transaction_code'] ?? null
                        );
                        echo json_encode(['completed_rows' => $count]);
                        break;
                    case 'full-update':
                        $data = json_decode(file_get_contents('php://input'), true);
                        $payment_id = $data['payment_id'];
                        unset($data['payment_id']);
                        $count = updatePayment($conn, $payment_id, $data);
                        echo json_encode(['updated_rows' => $count]);
                        break;
                    default:
                        echo json_encode(['message' => 'Invalid action']);
                        break;
                }
            } catch (\Throwable $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Lỗi server: ' . $e->getMessage()
                ]);
            }
            exit;
        }

        // Nếu không có action, load view payment bình thường
        $view = view('payment');
        break;

    case 'place-order':
        require_once __DIR__ . '/../app/controllers/OrderController.php';
        placeOrder();
        break;

    // ==================== FAVORITES ====================
    case 'favorite':
        $favorites = getFavorites();
        $view = view('favorite');
        break;

    case 'get-favorites':
        header('Content-Type: application/json');
        $favorites = getFavorites();
        echo json_encode([
            'items' => $favorites,
            'count' => count($favorites)
        ]);
        exit;
        break;

    case 'add-favorite':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            $productId = $input['product_id'] ?? 0;

            if (addFavorite($productId)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Sản phẩm đã có trong favorites']);
            }
            exit;
        }
        // GET (fallback)
        $productId = $_GET['id'] ?? 0;
        addFavorite($productId);
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
        exit;
        break;

    case 'remove-favorite':
        $productId = $_GET['id'] ?? 0;
        $success = removeFavoriteByProduct($productId);
        header('Content-Type: application/json');
        echo json_encode(['success' => $success]);
        exit;
        break;

    // ==================== CART ====================
    case 'add-cart':
        addToCart();
        break;

    case 'update-cart':
        updateCart();
        break;

    case 'remove-cart':
        removeCart();
        break;

    case 'remove-all-cart':
        removeAllCart();
        break;

    case 'get-mini-cart':
    case 'get-cart':
        getCart();
        break;

        // ==================== COUPON ====================
    case 'getActiveCoupons':
        // Nếu bạn đã include CartController, gọi hàm
        if (function_exists('getActiveCoupons')) {
            getActiveCoupons();
        } else {
            // Nếu hàm chưa được định nghĩa, có thể định nghĩa tạm ở đây
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Chưa cấu hình coupon']);
        }
        exit;
        break;
    case 'applyCoupon':
        if (function_exists('applyCoupon')) {
            applyCoupon();
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Chưa cấu hình coupon']);
        }
        exit;
        break;
    case 'clearCoupon':
        if (function_exists('clearCoupon')) {
            clearCoupon();
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        }
        exit;
        break;

    // ==================== PRODUCT ====================
    case 'product':
        $product = getProductById($_GET['id'] ?? 0);
        $product['reviews'] = getReviewsByProductId($product['id']);
        $ratingData = getAverageRating($product['id']);
        $product['avg_rating'] = $ratingData['avg_rating'] ?? 0;
        $view = view('product-detail');
        break;

    case 'add-review':
        addReview();
        break;

    case 'search':
        if (isset($_GET['ajax'])) {
            $q = $_GET['q'] ?? '';
            $stmt = $conn->prepare("
                SELECT id, name
                FROM products
                WHERE name LIKE ?
                LIMIT 5
            ");
            $stmt->execute(["%$q%"]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            exit;
        }
        break;

    // ==================== NOTIFICATIONS ====================
    case 'notifications':
        require_once __DIR__ . '/../app/controllers/NotificationController.php';
        $view = showNotificationsPage(); // hàm trả về đường dẫn view
        break;

    case 'api/notifications':
        require_once __DIR__ . '/../app/controllers/NotificationController.php';
        handleNotificationApi();
        exit;

    // ==================== HOME & DEFAULT ====================
   case 'home':
    default:
        // Lấy tham số từ $_GET
        $page = $_GET['page'] ?? 1;
        $limit = 10; // số sản phẩm mỗi trang
        $filters = [
            'min_price' => $_GET['min_price'] ?? '',
            'max_price' => $_GET['max_price'] ?? '',
            'size'      => $_GET['size'] ?? '',
            'sort'      => $_GET['sort'] ?? '',
            'keyword'   => $_GET['keyword'] ?? ''
        ];

        // Nếu là AJAX, chỉ trả về phần product-list và pagination
        if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
            $products = getFilteredProducts($page, $limit, $filters);
            $totalProducts = countFilteredProducts($filters);
            $totalPages = ceil($totalProducts / $limit);
            // Chỉ hiển thị product list và pagination
            include view('product-list-ajax'); // tạo file này hoặc inline
            exit;
        }

        // Load normal page
        $products = getFilteredProducts($page, $limit, $filters);
        $totalProducts = countFilteredProducts($filters);
        $totalPages = ceil($totalProducts / $limit);
        $favIds = getFavoriteIds();
        $categories = getCategories();
        $variants = getAllVariants(); // lấy danh sách size từ DB

        $view = view('home');
        break;
    }

// Load layout + view
include $layout;
