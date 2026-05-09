<?php

session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh');
if (!function_exists('vnd')) {
    function vnd($number) {
        return number_format($number, 0, ',', '.') . 'đ';
    }
}

$base = '/DoAn/DoAnTotNghiep/public/';

require_once __DIR__ . '/../app/controllers/HomeController.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/CartController.php';
require_once __DIR__ . '/../app/controllers/FavoritesController.php';
require_once __DIR__ . '/../app/controllers/OrderController.php';
require_once __DIR__ . '/../app/controllers/PaymentController.php';
require_once __DIR__ . '/../app/controllers/SettingsController.php';
require_once __DIR__ . '/../app/helpers/order_helper.php';

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

    case 'reset-password':
        $view = view('reset-password');
        $layout = __DIR__ . '/../resources/views/layouts/auth.php';
        break;

    // ==================== SETTINGS ====================
    case 'settings':
        $settingsData = getSettingsData();

        $user = $settingsData['user'];
        $addresses = $settingsData['addresses'];
        $notifications = $settingsData['notifications'];
        $success = $settingsData['success'];
        $error = $settingsData['error'];

        $view = view('settings');
        break;


    // ===== PROFILE =====
    case 'settings/updateProfile':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            updateProfile();
        }
        header('Location: index.php?url=settings');
        exit;


    // ===== PASSWORD =====
    case 'settings/changePassword':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            changePassword();
        }
        header('Location: index.php?url=settings');
        exit;


    // ===== ADDRESS =====
    case 'settings/addAddress':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            addAddress();
        }
        header('Location: index.php?url=settings');
        exit;

    case 'settings/updateAddress':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            updateAddress();
        }
        header('Location: index.php?url=settings');
        exit;

    case 'settings/deleteAddress':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            deleteAddress();
        }
        header('Location: index.php?url=settings');
        exit;


    // ===== NOTIFICATION =====
    case 'settings/markNotificationRead':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            markNotificationRead();
        }
        exit;

    case 'settings/markAllRead':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            markAllRead();
        }
        exit;

    // ==================== CHECKOUT, PROFILE, SHIPPING ====================
    case 'checkout':
        $view = view('checkout');
        break;

    case 'profile':
        $view = view('profile');
        break;

    case 'shipping':
        $addresses = [];

        if (isset($_SESSION['user'])) {
            $addresses = getShippingAddresses($_SESSION['user']['id']);
        }
        $view = view('shipping');
        break;

        case 'add-shipping-address':

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            header('Content-Type: application/json; charset=utf-8');
            addShippingAddress();   // hàm này trả về JSON và exit
            exit;
        }
        break;

    case 'delete-shipping-address':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json; charset=utf-8');
            $id = $_POST['id'] ?? 0;
            $userId = $_SESSION['user']['id'];
            $conn = getDB();
            // Xóa địa chỉ của user
            $stmt = $conn->prepare("DELETE FROM shipping_addresses WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $userId]);
            echo json_encode(['success' => true]);
            exit;
        }
        break;

    case 'get-shipping-addresses':

        header('Content-Type: application/json');
        $addresses = getShippingAddresses($_SESSION['user']['id']);
        echo json_encode($addresses);
        exit;

    case 'payment':
        // Nếu có action (AJAX)
        if (isset($_GET['action'])) {
            $action = $_GET['action'];

            header('Content-Type: application/json; charset=utf-8');

            $conn = getDB();
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

    case 'vnpay-create':
        createVNPayPayment();
        exit;

    case 'vnpay-return':
    case 'payment-return':
        vnpayReturn();
        exit;

    case 'momo-create':
        createMoMoPayment();
        exit;

    case 'place-order':
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
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        $productId = $input['product_id'] ?? 0;
        if (!$productId || !isset($_SESSION['user'])) {
            echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ']);
            exit;
        }
        $userId = $_SESSION['user']['id'];
        $conn = getDB();
        // Kiểm tra tồn tại
        $stmt = $conn->prepare("SELECT id FROM favorites WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
        $exists = $stmt->fetch();
        if ($exists) {
            $stmt = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$userId, $productId]);
        } else {
            $stmt = $conn->prepare("INSERT INTO favorites (user_id, product_id, created_at) VALUES (?, ?, NOW())");
            $stmt->execute([$userId, $productId]);
        }
        // Lấy tổng số mới
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM favorites WHERE user_id = ?");
        $stmt->execute([$userId]);
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        echo json_encode(['success' => true, 'total_favorites' => (int)$total]);
        exit;
    }
    // fallback GET
    $productId = $_GET['id'] ?? 0;
        // Chuyển hướng về trang trước đó
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
        exit;
    break;

        case 'remove-favorite':
        if (!isset($_SESSION['user'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
            exit;
        }
        $productId = $_GET['id'] ?? 0;
        $userId = $_SESSION['user']['id'];
        $conn = getDB();
        $stmt = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM favorites WHERE user_id = ?");
        $stmt->execute([$userId]);
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'total_favorites' => (int)$total]);
        exit;
        break;

    // ==================== CART ====================
    case 'add-cart':
        addToCart();
        break;

    case 'add-fav-to-cart':
        addFavoriteToCart();
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

    // ==================== ORDERS ====================
     case 'orders':
        listOrders();
        $view = view('orders'); // ✅ THÊM DÒNG NÀY
        break;

    case 'prepare-payment':
        preparePayment();
    break;

    case 'create-order':
        createOrderAPI();
        break;

    case 'order-detail':
        orderDetail();
        $view = view('order-detail'); // ✅ THÊM DÒNG NÀY
        break;
    case 'cancel-order':
        cancelOrder();
        break;

    case 'load-orders':
        loadOrders();
        exit;

    // ==================== PRODUCT ====================
    case 'product':
        $favIds = getFavoriteIds();

        $product = getProductById($_GET['id'] ?? 0);

        // ❌ nếu không có sản phẩm
        if (!$product) {
            header("Location: index.php?url=home");
            exit;
        }

        // ✅ đảm bảo luôn là array
        $product = (array)$product;

        // reviews
        $product['reviews'] = getReviewsByProductId($product['id']) ?? [];

        // rating
        $ratingData = getAverageRating($product['id']);
        $product['avg_rating'] = $ratingData['avg_rating'] ?? 0;

        // 👉 nếu có variants/toppings thì nên add luôn
        $product['variants'] = getVariantsByProductId($product['id']) ?? [];
        $product['toppings'] = getToppingsByProductId($product['id']) ?? [];

        // =========================
        // LOAD SẢN PHẨM TƯƠNG TỰ
        // =========================
        $similarProducts = getSimilarProducts(
            $product['category_id'],
            $product['id'],
            8
        );

        $view = view('product-detail');
        break;

    case 'add-review':
        addReview();
        break;

    case 'search':
        $filters = [
            'keyword' => $_GET['keyword'] ?? ''
        ];

        $products = getFilteredProducts(1, 20, $filters);

        $view = view('home');
    break;

    case 'search-suggest':
        header('Content-Type: application/json');

        $keyword = $_GET['keyword'] ?? '';

        if (!$keyword) {
            echo json_encode([]);
            exit;
        }

        $conn = getDB();

        $stmt = $conn->prepare("
            SELECT id, name, image, base_price
            FROM products
            WHERE name LIKE ?
            LIMIT 5
        ");
        $stmt->execute(["%$keyword%"]);

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($data);
    exit;

    // ==================== NOTIFICATIONS ====================
    case 'notifications':
        require_once __DIR__ . '/../app/controllers/NotificationController.php';
        $view = showNotificationsPage(); // hàm trả về đường dẫn view
        break;

    case 'api/notifications':
        require_once __DIR__ . '/../app/controllers/NotificationController.php';
        handleNotificationApi();
        exit;

    // ==================== PAGES (Support, Blog, Promotion, About) ====================
    case 'support':
        $view = view('support');
        break;

    case 'support-chat':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_SESSION['support_chat'][] = $_POST['message'];
        }
        exit;

    case 'support-get':
        header('Content-Type: application/json');
        echo json_encode($_SESSION['support_chat'] ?? []);
        exit;

    case 'promotion':

        $promotionData = getPromotionPageData();

        $promo = $promotionData['promo'];
        $banner = $promotionData['banner'];
        $products = $promotionData['products'];

        $view = view('promotion');

    break;

    case 'contact':
        $view = view('contact');
        break;

    case 'about':
        $view = view('about');
    break;

    // ==================== HOME & DEFAULT ====================
    case 'home':
    default:
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 10;

        $filters = [
            'keyword'   => $_GET['keyword'] ?? '',
            'category'  => $_GET['category'] ?? '',
            'min_price' => $_GET['min_price'] ?? '',
            'max_price' => $_GET['max_price'] ?? '',
            'size'      => $_GET['size'] ?? '',
            'sort'      => $_GET['sort'] ?? ''
        ];

        // Lấy sản phẩm
        $products     = getFilteredProducts($page, $limit, $filters);
        $totalProducts = countFilteredProducts($filters);
        $totalPages   = ceil($totalProducts / $limit);

        // Danh mục
        $categories     = getParentCategories();           // cho slider Browse Categories
        $allCategories  = getAllCategoriesWithDepth();     // cho filter select

        // Variants cho filter
        $variants = getAllVariants();

        // Favorites
        $favIds = [];
        if (isset($_SESSION['user']['id'])) {
            $favIds = getUserFavorites($_SESSION['user']['id']);
        }

        // AJAX request (filter)
        if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
            include view('home');   // chỉ include phần nội dung
            exit;
        }

        $view = view('home');
    break;
    }

// Load layout + view
include $layout;
