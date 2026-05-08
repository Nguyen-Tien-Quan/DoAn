<?php

// ==================== CART ====================
function addToCart()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // ===== CHECK LOGIN =====
    if (!isset($_SESSION['user'])) {

        $_SESSION['error'] = "Bạn cần đăng nhập để thêm giỏ hàng";

        header("Location: index.php?url=login");
        exit;
    }

    // ===== LẤY PRODUCT ID =====
    $id = 0;

    // ưu tiên POST
    if (isset($_POST['product_id'])) {
        $id = (int)$_POST['product_id'];
    }

    // fallback GET
    if ($id <= 0 && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
    }

    if ($id <= 0) {

        $_SESSION['error'] = "Sản phẩm không hợp lệ";

        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
        exit;
    }

    // ===== QUANTITY =====
    $quantity = max(1, (int)($_POST['quantity'] ?? 1));

    // ===== VARIANT =====
    $variantId = isset($_POST['variant_id'])
        ? (int)$_POST['variant_id']
        : 0;

    // ===== TOPPINGS =====
    $toppingIds = $_POST['toppings'] ?? [];

    if (!is_array($toppingIds)) {
        $toppingIds = [];
    }

    // ===== PRODUCT =====
    $product = getProductById($id);

    if (!$product) {

        $_SESSION['error'] = "Không tìm thấy sản phẩm";

        header("Location: index.php");
        exit;
    }

    // ===== STOCK =====
    $availableStock = isset($product['stock_quantity'])
        ? (int)$product['stock_quantity']
        : 999;

    // ===== VARIANT INFO =====
    $variant = null;
    $variantPrice = 0;
    $variantName = null;

    if ($variantId > 0) {

        $variant = getVariantById($variantId);

        if (!$variant) {

            $_SESSION['error'] = "Biến thể không hợp lệ";

            header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
            exit;
        }

        $availableStock = isset($variant['stock_quantity'])
            ? (int)$variant['stock_quantity']
            : $availableStock;

        $variantPrice = (float)($variant['price'] ?? 0);

        $variantName = $variant['variant_name'] ?? null;
    }

    // ===== CHECK STOCK =====
    if ($quantity > $availableStock) {

        $_SESSION['error'] = "Chỉ còn {$availableStock} sản phẩm trong kho";

        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
        exit;
    }

    // ===== PRICE =====
    $basePrice = (float)($product['base_price'] ?? 0);

    $discount = (float)($product['discount_percent'] ?? 0);

    $discountedBase = $basePrice;

    // giảm %
    if ($discount > 0) {

        $discountedBase = $basePrice
            - ($basePrice * $discount / 100);
    }

    // nếu có final_price thì ưu tiên
    if (!empty($product['final_price'])) {

        $discountedBase = (float)$product['final_price'];
    }

    // ===== TOPPINGS =====
    $toppingTotal = 0;

    $toppingList = [];

    foreach ($toppingIds as $tid) {

        $tid = (int)$tid;

        if ($tid <= 0) continue;

        $t = getToppingById($tid);

        if ($t) {

            $toppingTotal += (float)$t['price'];

            $toppingList[] = [
                'id'    => $t['id'],
                'name'  => $t['name'],
                'price' => (float)$t['price']
            ];
        }
    }

    // ===== FINAL PRICE =====
    $unitPrice = $discountedBase
        + $variantPrice
        + $toppingTotal;

    // ===== CART KEY =====
    $key = $id
        . '_'
        . ($variantId ?: 0)
        . '_'
        . implode('-', $toppingIds);

    // ===== INIT CART =====
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // ===== UPDATE EXIST =====
    if (isset($_SESSION['cart'][$key])) {

        $newQty = $_SESSION['cart'][$key]['quantity']
            + $quantity;

        if ($newQty > $availableStock) {

            $_SESSION['error'] = "Vượt quá tồn kho ({$availableStock})";

            header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
            exit;
        }

        $_SESSION['cart'][$key]['quantity'] = $newQty;

    } else {

        // ===== ADD NEW =====
        $_SESSION['cart'][$key] = [

            'id' => $product['id'],

            'name' => $product['name'] ?? 'Sản phẩm',

            'image' => $product['image'] ?? 'default-product.png',

            'price' => $unitPrice,

            'quantity' => $quantity,

            'variant' => $variant ? [
                'id'    => $variantId,
                'name'  => $variantName,
                'price' => $variantPrice
            ] : null,

            'toppings' => $toppingList
        ];
    }

    // ===== SUCCESS =====
    $_SESSION['success'] = "Đã thêm vào giỏ hàng";

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

function removeAllCart()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION['cart'] = [];

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

function addFavoriteToCart()
{
    if (session_status() === PHP_SESSION_NONE) session_start();

    if (!isset($_SESSION['user'])) {
        echo json_encode(['success' => false]);
        exit;
    }

    $conn = getDB();
    $userId = $_SESSION['user']['id'];

    $stmt = $conn->prepare("
        SELECT p.id, p.name, p.base_price, p.image
        FROM favorites f
        JOIN products p ON f.product_id = p.id
        WHERE f.user_id = ?
    ");
    $stmt->execute([$userId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

    foreach ($items as $p) {
        $key = $p['id'];

        if (isset($_SESSION['cart'][$key])) {
            $_SESSION['cart'][$key]['quantity']++;
        } else {
            $_SESSION['cart'][$key] = [
                'id' => $p['id'],
                'name' => $p['name'],
                'image' => $p['image'],
                'price' => $p['base_price'],
                'quantity' => 1,
                'variant' => null,
                'toppings' => []
            ];
        }
    }

    echo json_encode(['success' => true]);
    exit;
}

// ==================== COUPON (VOUCHER) ====================

function getActiveCoupons() {
    header('Content-Type: application/json');
    $conn = getDB();
    $now = date('Y-m-d H:i:s');

    $sql = "SELECT code, discount_type as type, discount_value as value,
                   max_discount_amount as max_discount, min_order_amount as min_order
            FROM vouchers
            WHERE status = 1
              AND (start_date IS NULL OR start_date <= :now)
              AND (end_date IS NULL OR end_date > :now)
              AND (
                    usage_limit IS NULL
                    OR usage_limit = 0
                    OR used_count < usage_limit
                  )
            ORDER BY discount_value DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':now' => $now
    ]);

    $coupons = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'count' => count($coupons), // debug
        'coupons' => $coupons
    ]);
    exit;
}
function applyCoupon() {
    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit;
    }

    $code = trim($_POST['code'] ?? '');
    $subtotal = floatval($_POST['subtotal'] ?? 0);

    if (empty($code)) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng nhập mã']);
        exit;
    }

    $conn = getDB();
    $now = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("SELECT * FROM vouchers WHERE code = ? LIMIT 1");
    $stmt->execute([$code]);
    $voucher = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$voucher) {
        echo json_encode(['success' => false, 'message' => 'Mã không tồn tại']);
        exit;
    }

    if ($voucher['status'] != 1) {
        echo json_encode(['success' => false, 'message' => 'Mã đã bị vô hiệu hóa']);
        exit;
    }

    if (!empty($voucher['start_date']) && $voucher['start_date'] > $now) {
        echo json_encode(['success' => false, 'message' => 'Mã chưa đến ngày bắt đầu']);
        exit;
    }

    if (!empty($voucher['end_date']) && $voucher['end_date'] <= $now) {
        echo json_encode(['success' => false, 'message' => 'Mã đã hết hạn']);
        exit;
    }

    if (!empty($voucher['usage_limit']) && $voucher['usage_limit'] > 0 && $voucher['used_count'] >= $voucher['usage_limit']) {
        echo json_encode(['success' => false, 'message' => 'Mã đã hết lượt sử dụng']);
        exit;
    }

    if (!empty($voucher['min_order_amount']) && $subtotal < $voucher['min_order_amount']) {
        echo json_encode([
            'success' => false,
            'message' => 'Đơn hàng tối thiểu ' . number_format($voucher['min_order_amount']) . 'đ'
        ]);
        exit;
    }

    // ===== TÍNH GIẢM =====
    if ($voucher['discount_type'] == 'percent') {
        $discount = $subtotal * $voucher['discount_value'] / 100;

        if (!empty($voucher['max_discount_amount']) && $discount > $voucher['max_discount_amount']) {
            $discount = $voucher['max_discount_amount'];
        }
    } else {
        $discount = $voucher['discount_value'];
    }

    if ($discount > $subtotal) $discount = $subtotal;

    // ===== SESSION =====
    if (session_status() === PHP_SESSION_NONE) session_start();

    $_SESSION['discount'] = $discount;
    $_SESSION['coupon_code'] = $code;
    $_SESSION['coupon_data'] = $voucher;

    $shipping = 10000;
    $total = $subtotal + $shipping;
    $newTotal = max(0, $total - $discount);

    echo json_encode([
        'success' => true,
        'discount' => $discount,
        'formatted_discount' => number_format($discount, 0, ',', '.') . 'đ',
        'new_total' => $newTotal,
        'formatted_new_total' => number_format($newTotal, 0, ',', '.') . 'đ'
    ]);
    exit;
}
function clearCoupon() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    unset($_SESSION['discount'], $_SESSION['coupon_code'], $_SESSION['coupon_data']);
    echo json_encode(['success' => true]);
    exit;
}
