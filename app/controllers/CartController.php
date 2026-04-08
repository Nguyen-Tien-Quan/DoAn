<?php

// ==================== CART ====================
function addToCart()
{
    if (session_status() === PHP_SESSION_NONE) session_start();

    if (!isset($_SESSION['user'])) {
        $_SESSION['error'] = "Bạn chưa đăng nhập!";
        header("Location: index.php?url=login");
        exit;
    }

    $id         = $_GET['id'] ?? null;
    $quantity   = (int)($_POST['quantity'] ?? 1);
    $variantId  = $_POST['variant_id'] ?? null;
    $toppingIds = $_POST['toppings'] ?? [];

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

    /* ===== GIÁ ===== */
    $basePrice    = $product['base_price'];
    $variantPrice = 0;
    $variantName  = null;

    /* ===== SIZE ===== */
    if ($variantId) {
        $variant = getVariantById($variantId);

        if ($variant) {
            $variantPrice = $variant['price']; // 👉 GIÁ CỘNG THÊM
            $variantName  = $variant['variant_name']; // ⚠️ sửa key cho đúng DB
        }
    }

    /* ===== TOPPING ===== */
    $toppingTotal = 0;
    $toppingList  = [];

    if (!empty($toppingIds)) {
        foreach ($toppingIds as $tid) {
            $t = getToppingById($tid);
            if ($t) {
                $toppingTotal += $t['price'];

                $toppingList[] = [
                    'id'    => $t['id'],
                    'name'  => $t['name'],
                    'price' => $t['price']
                ];
            }
        }
    }

    /* ===== FINAL (FIX CHUẨN) ===== */
    $finalPrice = $basePrice + $variantPrice + $toppingTotal;

    /* ===== KEY ===== */
    $key = $id . '_' . ($variantId ?? 0) . '_' . implode('-', $toppingIds);

    if (isset($_SESSION['cart'][$key])) {
        $_SESSION['cart'][$key]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$key] = [
            'id'       => $product['id'],
            'name'     => $product['name'],
            'image'    => $product['image'],
            'price'    => $finalPrice,
            'quantity' => $quantity,

            'variant' => $variantName ? [
                'id'    => $variantId,
                'name'  => $variantName,
                'price' => $variantPrice // 👉 chỉ phần cộng thêm
            ] : null,

            'toppings' => $toppingList
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

// ==================== COUPON (VOUCHER) ====================

function getActiveCoupons() {
    header('Content-Type: application/json');
    $conn = getDB();
    $now = date('Y-m-d H:i:s');
    $sql = "SELECT code, discount_type as type, discount_value as value,
                   max_discount_amount as max_discount, min_order_amount as min_order
            FROM vouchers
            WHERE status = 1
              AND start_date <= '$now'
              AND end_date > '$now'
              AND (used_count < usage_limit OR usage_limit = 0)
            ORDER BY discount_value DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $coupons = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'coupons' => $coupons]);
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
    // Lấy thông tin voucher
    $stmt = $conn->prepare("SELECT * FROM vouchers WHERE code = ? LIMIT 1");
    $stmt->execute([$code]);
    $voucher = $stmt->fetch(PDO::FETCH_ASSOC);

    // Kiểm tra voucher tồn tại và còn hiệu lực
    if (!$voucher) {
        echo json_encode(['success' => false, 'message' => 'Mã không tồn tại']);
        exit;
    }
    if ($voucher['status'] != 1) {
        echo json_encode(['success' => false, 'message' => 'Mã đã bị vô hiệu hóa']);
        exit;
    }
    if ($voucher['start_date'] > $now) {
        echo json_encode(['success' => false, 'message' => 'Mã chưa đến ngày bắt đầu']);
        exit;
    }
    if ($voucher['end_date'] <= $now) {
        echo json_encode(['success' => false, 'message' => 'Mã đã hết hạn']);
        exit;
    }
    if ($voucher['usage_limit'] > 0 && $voucher['used_count'] >= $voucher['usage_limit']) {
        echo json_encode(['success' => false, 'message' => 'Mã đã hết lượt sử dụng']);
        exit;
    }
    if ($voucher['min_order_amount'] > 0 && $subtotal < $voucher['min_order_amount']) {
        echo json_encode(['success' => false, 'message' => 'Đơn hàng tối thiểu ' . number_format($voucher['min_order_amount']) . 'đ để dùng mã này']);
        exit;
    }

    // Tính giảm giá
    if ($voucher['discount_type'] == 'percent') {
        $discount = $subtotal * $voucher['discount_value'] / 100;
        if ($voucher['max_discount_amount'] > 0 && $discount > $voucher['max_discount_amount']) {
            $discount = $voucher['max_discount_amount'];
        }
    } else { // fixed
        $discount = $voucher['discount_value'];
    }
    if ($discount > $subtotal) $discount = $subtotal;

    // Lưu vào session
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['discount'] = $discount;
    $_SESSION['coupon_code'] = $code;
    $_SESSION['coupon_data'] = $voucher;

    $shipping = 10000;
    $total = $subtotal + $shipping;
    $newTotal = $total - $discount;
    if ($newTotal < 0) $newTotal = 0;

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
