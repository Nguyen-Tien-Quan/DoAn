<?php

// BASE URL
$base = '/DoAn/DoAnTotNghiep/public/';

require_once __DIR__ . '/../app/controllers/HomeController.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
$products = getProducts();

// Hàm view
function view($name) {
    return __DIR__ . "/../resources/views/pages/$name.php";
}

// Router
$url = $_GET['url'] ?? 'home';

// 👉 THÊM: mặc định layout thường
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
        // 👉 nếu submit form thì xử lý
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $error = handleLogin();
        }
        break;

    case 'logout':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        handleLogout();
        break;


    case 'forgot-password':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        $view = view('forgot-password');
        $layout = __DIR__ . '/../resources/views/layouts/auth.php';
        break;

    case 'checkout':
        $view = view('checkout');
        break;

    case 'profile':
        $view = view('profile');
        break;

    case 'add-favorite':
        require_once __DIR__ . '/../app/controllers/FavoritesController.php';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Xử lý AJAX
            $input = json_decode(file_get_contents('php://input'), true);
            $productId = $input['product_id'] ?? 0;

            if(addFavorite($productId)){
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Sản phẩm đã có trong favorites']);
            }
            exit;
        } else {
            // Xử lý click trực tiếp (GET)
            $productId = $_GET['id'] ?? 0;
            addFavorite($productId);
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit;
        }
    break;

    case 'remove-favorite':
        require_once __DIR__ . '/../app/controllers/FavoritesController.php';
        $favId = $_GET['id'] ?? 0;
        removeFavorite($favId);
        header("Location: " . $_SERVER['HTTP_REFERER']);
        break;

    case 'favorite':
        require_once __DIR__ . '/../app/controllers/FavoritesController.php';
        $favorites = getFavorites();
        $view = view('favorite');
        break;

    case 'get-favorites':
        require_once __DIR__ . '/../app/controllers/FavoritesController.php';

        header('Content-Type: application/json');

        $favorites = getFavorites();

        echo json_encode([
            'items' => $favorites,
            'count' => count($favorites)
        ]);

        exit;
    break;

    case 'product':
        require_once __DIR__ . '/../app/controllers/HomeController.php';
        $product = getProductById($_GET['id']);
        $view = view('product-detail');
        break;


    case 'add-cart':
        require_once __DIR__ . '/../app/controllers/CartController.php';
        addToCart();
        break;

    case 'update-cart':
        require_once __DIR__ . '/../app/controllers/CartController.php';
        updateCart();
        break;

    case 'remove-cart':
        require_once __DIR__ . '/../app/controllers/CartController.php';
        removeCart();
        break;

    case 'get-cart':
        require_once __DIR__ . '/../app/controllers/CartController.php';
        getCart();
        break;

    default:
        $view = view('home');
        break;
}


// Load layout
include $layout;
