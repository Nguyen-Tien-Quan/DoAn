<?php

// ==================== CART ====================
function addToCart()
{
    if (session_status() === PHP_SESSION_NONE) session_start();

    if (!isset($_SESSION['user'])) {
        $_SESSION['error'] = "Bạn chưa đăng nhập, không thể thêm vào giỏ hàng!";
        header("Location: index.php?url=login");
        exit;
    }

    $id       = $_GET['id'] ?? null;
    $quantity = (int)($_POST['quantity'] ?? 1);

    if (!$id) {
        header("Location: index.php");
        exit;
    }

    $product = getProductById($id);
    if (!$product) {
        header("Location: index.php");
        exit;
    }

    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$id] = [
            'id'       => $product['id'],
            'name'     => $product['name'],
            'price'    => $product['base_price'],
            'image'    => $product['image'],
            'quantity' => $quantity
        ];
    }

    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
    exit;
}

function updateCart()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Kiểm tra dữ liệu POST
    if (!isset($_POST['id']) || !isset($_POST['action'])) {
        echo json_encode(['success' => false, 'message' => 'Thiếu id hoặc action']);
        exit;
    }

    $id     = $_POST['id'];
    $action = $_POST['action'];

    if (!isset($_SESSION['cart'][$id])) {
        echo json_encode(['success' => false]);
        exit;
    }

    if ($action === 'plus') {
        $_SESSION['cart'][$id]['quantity']++;
    }
    elseif ($action === 'minus') {
        $_SESSION['cart'][$id]['quantity']--;

        if ($_SESSION['cart'][$id]['quantity'] <= 0) {
            unset($_SESSION['cart'][$id]);
            echo json_encode([
                'success' => true,
                'removed' => true
            ]);
            exit;
        }
    }
    else {
        echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
        exit;
    }

    // Trả về dữ liệu sau khi cập nhật
    $item = $_SESSION['cart'][$id];

    echo json_encode([
        'success'   => true,
        'quantity'  => $item['quantity'],
        'itemTotal' => $item['quantity'] * $item['price']
    ]);

    exit;
}

function removeCart()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $id = $_GET['id'] ?? null;

    if ($id && isset($_SESSION['cart'][$id])) {
        unset($_SESSION['cart'][$id]);
    }

    echo json_encode([
        'success' => true
    ]);
    exit;
}

function getCart($asJson = true) {
    $data = [
        'items' => $_SESSION['cart'] ?? [],
        'subtotal' => 0,
        'total' => 0
    ];

    foreach ($data['items'] as $item) {
        $data['subtotal'] += $item['price'] * $item['quantity'];
    }

    $data['total'] = $data['subtotal'] + 10000;

    if ($asJson) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    return $data; // ✅ QUAN TRỌNG
}

function getCartItems($user_id) {
    $conn = getDB();
    $stmt = $conn->prepare("
        SELECT ci.quantity, p.name, p.image, p.base_price
        FROM cart_items ci
        JOIN carts c ON c.id = ci.cart_id
        JOIN products p ON p.id = ci.product_id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

function saveToFavorite()
{
    if (session_status() === PHP_SESSION_NONE) session_start();

    if (!isset($_SESSION['user'])) {
        echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
        exit;
    }

    $id = $_POST['id'] ?? null;

    if (!$id) {
        echo json_encode(['success' => false]);
        exit;
    }

    require_once __DIR__ . '/FavoritesController.php';

    // 👉 thêm vào favorite
    addFavorite($id);

    // 👉 xóa khỏi cart
    if (isset($_SESSION['cart'][$id])) {
        unset($_SESSION['cart'][$id]);
    }

    echo json_encode([
        'success' => true
    ]);
    exit;
}
