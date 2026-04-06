<?php

$base = '/DoAn/DoAnTotNghiep/public/';

require_once __DIR__ . '/../app/controllers/HomeController.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/CartController.php';
require_once __DIR__ . '/../app/controllers/FavoritesController.php';
require_once __DIR__ . '/../app/controllers/OrderController.php';
 require_once __DIR__ . '/../app/controllers/PaymentController.php';

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

    // Login, Register, Logout, Forgot Password
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

    // checkout, profile, shipping
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
            // Bắt buộc header JSON
            header('Content-Type: application/json; charset=utf-8');

            // Bắt lỗi PDO / PHP
            try {
                addShippingAddress();
            } catch (\Throwable $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Lỗi server: ' . $e->getMessage()
                ]);
            }
            exit; // rất quan trọng, tránh output HTML layout
        }
        break;

    case 'payment':

    // Nếu có action (AJAX)
    if (isset($_GET['action'])) {
        $action = $_GET['action'];

        header('Content-Type: application/json; charset=utf-8');

        try {
            switch ($action) {

                // Lấy danh sách tất cả payments
                case 'list':
                    $payments = getPayments($conn);
                    echo json_encode($payments);
                    break;

                // Lấy payment theo order_id
                case 'detail':
                    $order_id = $_GET['order_id'] ?? 0;
                    $payment = getPaymentByOrder($conn, $order_id);
                    echo json_encode($payment);
                    break;

                // Tạo payment mới
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

                // Cập nhật trạng thái payment
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

                // Xóa payment
                case 'delete':
                    $data = json_decode(file_get_contents('php://input'), true);
                    $count = deletePayment($conn, $data['payment_id']);
                    echo json_encode(['deleted_rows' => $count]);
                    break;

                // Hoàn tất payment (mark paid + transaction code)
                case 'complete':
                    $data = json_decode(file_get_contents('php://input'), true);
                    $count = completePayment(
                        $conn,
                        $data['payment_id'],
                        $data['transaction_code'] ?? null
                    );
                    echo json_encode(['completed_rows' => $count]);
                    break;

                // Cập nhật tất cả field payment (nếu cần)
                case 'full-update':
                    $data = json_decode(file_get_contents('php://input'), true);
                    $payment_id = $data['payment_id'];
                    unset($data['payment_id']); // loại bỏ id
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
        exit; // quan trọng: dừng load layout HTML
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

    case 'get-mini-cart':
    case 'get-cart':
        getCart();
        break;

    // ==================== PRODUCT ====================
    case 'product':
        $product = getProductById($_GET['id'] ?? 0);

        // 🔥 THÊM DÒNG NÀY
        $product['reviews'] = getReviewsByProductId($product['id']);

        // 🔥 thêm rating luôn cho chuẩn
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

    // ==================== DEFAULT ====================
    case 'home':
    default:
        $data = pagination(); // lấy dữ liệu
        $products = $data['products'];
        $page = $data['page'];
        $totalPages = $data['totalPages'];
        $favIds = getFavoriteIds();
        $categories = getCategories();

        $view = view('home'); // chỉ include view, không include controller nữa
        break;
    }

// Load layout + view
include $layout;
