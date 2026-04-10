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
require_once __DIR__ . '/../app/controllers/Admin/CategoryController.php';
// require_once __DIR__ . '/../app/controllers/Admin/ProductController.php';
// require_once __DIR__ . '/../app/controllers/Admin/OrderController.php';
// require_once __DIR__ . '/../app/controllers/Admin/UserController.php';

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

// ✅ FIX layout đúng
$layout = __DIR__ . '/../resources/views/layouts/admin.php';

switch ($url) {

    // ===== DASHBOARD =====
    case 'dashboard':
        $view = view('dashboard');
        break;

    // ===== CATEGORY =====
    case 'categories':
        $categories = getCategories(); // 🔥 thêm dòng này
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

    // // ===== USERS =====
    // case 'users':
    //     $users = getUsers(); // nếu có
    //     $view = view('users');
    //     break;

    // ===== LOGOUT =====
    case 'logout':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        handleLogout();
        break;

    // ===== DEFAULT =====
    default:
        $view = view('dashboard');
        break;
}

// ======================
// LOAD LAYOUT
// ======================
include $layout;
