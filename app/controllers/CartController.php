<?php

// ==================== ADD TO CART ====================
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

// ==================== UPDATE CART (đã fix đầy đủ) ====================
function updateCart()
{
    if (session_status() === PHP_SESSION_NONE) session_start();

    if (!isset($_POST['id']) || !isset($_POST['action'])) {
        echo json_encode(['success' => false, 'message' => 'Missing parameters']);
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
    } elseif ($action === 'minus') {
        $_SESSION['cart'][$id]['quantity']--;
        if ($_SESSION['cart'][$id]['quantity'] <= 0) {
            unset($_SESSION['cart'][$id]);
            echo json_encode(['success' => true, 'removed' => true]);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit;
    }

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

    // 🔥 Nếu là AJAX → trả JSON
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        echo json_encode([
            'success' => true
        ]);
        return;
    }

    // 🔥 Nếu là link bình thường → redirect
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php?url=checkout'));
    exit;
}


function getCart()
{
    if (session_status() === PHP_SESSION_NONE) session_start();

    $cart = $_SESSION['cart'] ?? [];
    $items = [];
    $total = 0;

    foreach ($cart as $item) {
        $items[] = [
            'id'       => $item['id'],
            'name'     => $item['name'],
            'price'    => $item['price'],
            'image'    => $item['image'],
            'quantity' => $item['quantity']
        ];
        $total += $item['price'] * $item['quantity'];
    }

    echo json_encode([
        'items'    => $items,
        'subtotal' => $total,
        'total'    => $total + 10000
    ]);
    exit;
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

function vnd($money) {
    return number_format($money, 0, ',', '.') . 'đ';
}
