<?php

$base = '/DoAn/DoAnTotNghiep/public/';

require_once __DIR__ . '/../app/controllers/HomeController.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/CartController.php';
require_once __DIR__ . '/../app/controllers/FavoritesController.php';

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

    case 'checkout':
        $view = view('checkout');
        break;

    case 'profile':
        $view = view('profile');
        break;

    case 'shipping':
        $view = view('shipping');
        break;

    // ==================== FAVORITES ====================
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
        $view = view('product-detail');
        break;

    // ==================== DEFAULT ====================
    case 'home':
    default:
        $data = pagination(); // lấy dữ liệu
        $products = $data['products'];
        $page = $data['page'];
        $totalPages = $data['totalPages'];
        $favIds = getFavoriteIds();

        $view = view('home'); // chỉ include view, không include controller nữa
        break;
    }

// Load layout + view
include $layout;
