<?php
// app/Controllers/OrderController.php

if (session_status() === PHP_SESSION_NONE) session_start();

// Hàm kết nối DB
if (!function_exists('getDB')) {
    function getDB() {
        static $conn = null;
        if ($conn === null) {
            $host = 'localhost';
            $dbname = 'QlBANTHUCAN';
            $user = 'root';
            $pass = '';
            try {
                $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die('Lỗi kết nối DB: ' . $e->getMessage());
            }
        }
        return $conn;
    }
}

// ---------- ĐỊA CHỈ GIAO HÀNG ----------
function getShippingAddresses($user_id) {
    $conn = getDB();
    $stmt = $conn->prepare("
        SELECT * FROM shipping_addresses
        WHERE user_id = ?
        ORDER BY is_default DESC, created_at DESC
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addShippingAddress() {
    header('Content-Type: application/json; charset=utf-8');

    if (!isset($_SESSION['user'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Vui lòng đăng nhập'
        ]);
        exit;
    }

    $conn = getDB();
    $user_id = $_SESSION['user']['id'];

    $address_id = $_POST['address_id'] ?? null;
    $full_name  = trim($_POST['recipient_name'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $address    = trim($_POST['address'] ?? '');
    $city       = trim($_POST['city'] ?? '');
    $is_default = isset($_POST['is_default']) ? 1 : 0;

    // ================= VALIDATE =================
    if (!$full_name || !$phone || !$address || !$city) {
        echo json_encode([
            'success' => false,
            'message' => 'Vui lòng nhập đầy đủ thông tin'
        ]);
        exit;
    }

    // ================= FIX PHONE =================
    $phone = preg_replace('/\D/', '', $phone);

    // convert +84 -> 0
    if (substr($phone, 0, 2) === '84') {
        $phone = '0' . substr($phone, 2);
    }

    if (!preg_match('/^0[0-9]{9}$/', $phone)) {
        echo json_encode([
            'success' => false,
            'message' => 'Số điện thoại phải 10 số và bắt đầu bằng 0'
        ]);
        exit;
    }

    try {

        // ================= CHECK DUPLICATE (ONLY ADD) =================
        $check = $conn->prepare("
            SELECT id FROM shipping_addresses
            WHERE user_id = ?
            AND phone = ?
            AND address = ?
            AND city = ?
        ");
        $check->execute([$user_id, $phone, $address, $city]);

        $exist = $check->fetchColumn();

        if ($exist && !$address_id) {
            echo json_encode([
                'success' => false,
                'message' => 'Địa chỉ này đã tồn tại'
            ]);
            exit;
        }

        // ================= HANDLE DEFAULT =================
        if ($is_default == 1) {
            $conn->prepare("
                UPDATE shipping_addresses
                SET is_default = 0
                WHERE user_id = ?
            ")->execute([$user_id]);
        }

        // ================= UPDATE =================
        if ($address_id) {

            $stmt = $conn->prepare("
                UPDATE shipping_addresses
                SET full_name = ?,
                    phone = ?,
                    address = ?,
                    city = ?,
                    is_default = ?
                WHERE id = ? AND user_id = ?
            ");

            $ok = $stmt->execute([
                $full_name,
                $phone,
                $address,
                $city,
                $is_default,
                $address_id,
                $user_id
            ]);

            echo json_encode([
                'success' => $ok,
                'message' => $ok ? 'Cập nhật địa chỉ thành công' : 'Cập nhật thất bại'
            ]);
            exit;
        }

        // ================= INSERT =================
        $stmt = $conn->prepare("
            INSERT INTO shipping_addresses
            (user_id, full_name, phone, address, city, is_default, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");

        $ok = $stmt->execute([
            $user_id,
            $full_name,
            $phone,
            $address,
            $city,
            $is_default
        ]);

        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Thêm địa chỉ thành công' : 'Thêm thất bại'
        ]);
        exit;

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi hệ thống: ' . $e->getMessage()
        ]);
        exit;
    }
}

// ---------- ĐẶT HÀNG (từ form shipping → payment) ----------
function placeOrder() {
    if (!isset($_SESSION['user'])) {
        header("Location: index.php?url=login");
        exit;
    }

    $user_id = $_SESSION['user']['id'];
    $shipping_address_id = $_POST['shipping_address_id'] ?? null;
    $payment_method = $_POST['payment_method'] ?? 'cod';

    if (!$shipping_address_id) {
        $_SESSION['error'] = "Vui lòng chọn địa chỉ giao hàng";
        header("Location: index.php?url=shipping");
        exit;
    }

    $conn = getDB();

    // ✅ CHECK địa chỉ thuộc user
    $stmt = $conn->prepare("SELECT * FROM shipping_addresses WHERE id=? AND user_id=?");
    $stmt->execute([$shipping_address_id, $user_id]);
    $address = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$address) {
        $_SESSION['error'] = "Địa chỉ không hợp lệ";
        header("Location: index.php?url=shipping");
        exit;
    }

    $cart = $_SESSION['cart'] ?? [];

    // ✅ CHECK giỏ hàng
    if (empty($cart)) {
        $_SESSION['error'] = "Giỏ hàng trống";
        header("Location: index.php?url=cart");
        exit;
    }

    $conn->beginTransaction();

    try {
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $shipping_fee = 10000;
        $total = $subtotal + $shipping_fee;

        // ✅ thêm delivery_address
        $delivery_address = $address['address'] . ', ' . $address['city'];

        $stmt = $conn->prepare("
            INSERT INTO orders
            (user_id, shipping_address_id, total_amount, shipping_fee, final_amount, payment_method, status, delivery_address)
            VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)
        ");
        $stmt->execute([
            $user_id,
            $shipping_address_id,
            $subtotal,
            $shipping_fee,
            $total,
            $payment_method,
            $delivery_address
        ]);

        $order_id = $conn->lastInsertId();

        foreach ($cart as $item) {
            $stmt = $conn->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, unit_price)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $order_id,
                $item['id'],
                $item['quantity'],
                $item['price']
            ]);
        }

        unset($_SESSION['cart']);

        $conn->commit();

        header("Location: index.php?url=thank-you&order_id=$order_id");
        exit;

    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['error'] = "Đặt hàng thất bại: " . $e->getMessage();
        header("Location: index.php?url=shipping");
        exit;
    }
}
function generatePaymentUrl($order_id, $amount, $method, $order_code) {
    $base = 'http://' . $_SERVER['HTTP_HOST'] . '/DoAn/DoAnTotNghiep/public/';

    switch ($method) {
        case 'vnpay':
            // Giả lập VNPay (thực tế bạn cần tích hợp SDK)
            return $base . "index.php?url=vnpay-create&order_id=$order_id&amount=$amount&order_code=$order_code";

        case 'momo':
            return $base . "index.php?url=momo-create&order_id=$order_id&amount=$amount";

        case 'zalopay':
            return $base . "index.php?url=zalopay-create&order_id=$order_id&amount=$amount";

        default:
            return $base . "index.php?url=payment-return&method=$method&order_code=$order_code";
    }
}

function sendOrderSuccessEmail($order_code, $total, $payment_method = 'cod') {
    if (!class_exists('MailService')) {
        error_log("MailService class not found");
        return false;
    }

    $customer_name = $_SESSION['user']['name'] ?? 'Khách hàng';

    $html = "
        <h2>📦 Đơn hàng mới - Thành công</h2>
        <p><strong>Mã đơn hàng:</strong> {$order_code}</p>
        <p><strong>Khách hàng:</strong> {$customer_name}</p>
        <p><strong>Tổng tiền:</strong> " . number_format($total) . "đ</p>
        <p><strong>Phương thức:</strong> " . strtoupper($payment_method) . "</p>
        <hr>
        <p>Đơn hàng đã được tạo thành công.</p>
    ";

    return MailService::send('nguyentienquan1st@gmail.com', 'Đơn hàng mới #' . $order_code, $html);
}
// ---------- API TẠO ĐƠN HÀNG (dùng cho AJAX) ----------
function createOrderAPI() {
    header('Content-Type: application/json');
    if (session_status() === PHP_SESSION_NONE) session_start();

    if (!isset($_SESSION['user'])) {
        echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
        exit;
    }

    $user_id            = $_SESSION['user']['id'];
    $shipping_address_id = (int)($input['shipping_address_id'] ?? 0);
    $shipping_method    = $input['shipping_method'] ?? 'standard';
    $shipping_fee       = (int)($input['shipping_fee'] ?? 10000);
    $payment_method     = $input['payment_method'] ?? 'cod';

    $cart = $_SESSION['cart'] ?? [];
    if (empty($cart)) {
        echo json_encode(['success' => false, 'message' => 'Giỏ hàng trống']);
        exit;
    }

    $subtotal = 0;
    foreach ($cart as $item) {
        $subtotal += ($item['price'] ?? 0) * ($item['quantity'] ?? 0);
    }

    $discount = $_SESSION['discount'] ?? 0;
    $total    = $subtotal - $discount + $shipping_fee;
    if ($total < 0) $total = 0;

    $conn = getDB();

    // Kiểm tra địa chỉ
    $stmt = $conn->prepare("SELECT * FROM shipping_addresses WHERE id = ? AND user_id = ?");
    $stmt->execute([$shipping_address_id, $user_id]);
    $address = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$address) {
        echo json_encode(['success' => false, 'message' => 'Địa chỉ giao hàng không hợp lệ']);
        exit;
    }

    $conn->beginTransaction();

    try {
        $order_code = 'ORD' . date('YmdHis') . rand(100, 999);

        $delivery_address = $address['address'] . ', ' . ($address['city'] ?? '');

        $stmt = $conn->prepare("
            INSERT INTO orders
            (order_code, user_id, shipping_address_id, order_type, total_amount,
             discount_amount, shipping_fee, final_amount, payment_method,
             payment_status, status, delivery_address, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending', ?, NOW(), NOW())
        ");

        $stmt->execute([
            $order_code,
            $user_id,
            $shipping_address_id,
            'delivery',
            $subtotal,
            $discount,
            $shipping_fee,
            $total,
            $payment_method,
            $delivery_address
        ]);

        $order_id = $conn->lastInsertId();

        // Thêm sản phẩm vào đơn hàng
        $stmtItem = $conn->prepare("
            INSERT INTO order_items (order_id, product_id, variant_id, quantity, unit_price, subtotal)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        foreach ($cart as $item) {
            $variant_id = $item['variant_id'] ?? null;
            $subtotal_item = $item['price'] * $item['quantity'];

            $stmtItem->execute([
                $order_id,
                $item['id'],
                $variant_id,
                $item['quantity'],
                $item['price'],
                $subtotal_item
            ]);
        }

        // Gửi mail cho Admin
        sendOrderSuccessEmail($order_code, $total, $payment_method);

        $conn->commit();

        unset($_SESSION['cart'], $_SESSION['discount'], $_SESSION['coupon_code']);

        echo json_encode([
            'success'    => true,
            'order_id'   => $order_id,
            'order_code' => $order_code,
            'total'      => $total,
            'message'    => 'Đặt hàng thành công!'
        ]);
        exit;

    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi tạo đơn hàng: ' . $e->getMessage()
        ]);
        exit;
    }
}
/**
 * Xử lý callback từ cổng thanh toán (VNPAY, MoMo, ZaloPay...)
 * Được gọi khi khách hoàn tất thanh toán hoặc bị hủy
 */
function paymentReturn() {
    $method = $_GET['method'] ?? 'vnpay';
    $order_code = $_GET['order_code'] ?? '';

    if (empty($order_code)) {
        header("Location: index.php?url=checkout");
        exit;
    }

    $conn = getDB();

    $responseCode = $_GET['vnp_ResponseCode'] ?? $_GET['errorCode'] ?? $_GET['status'] ?? '';

    $is_success = false;

    if ($method === 'vnpay' && $responseCode == '00') {
        $is_success = true;
    } elseif (in_array($method, ['momo', 'zalopay']) && $responseCode == '0') {
        $is_success = true;
    }

    if ($is_success) {
        // Thanh toán thành công
        $stmt = $conn->prepare("
            UPDATE orders
            SET payment_status = 'paid',
                status = 'confirmed',
                updated_at = NOW()
            WHERE order_code = ?
        ");
        $stmt->execute([$order_code]);

        unset($_SESSION['cart'], $_SESSION['discount'], $_SESSION['coupon_code']);

        header("Location: index.php?url=thank-you&order_code=" . urlencode($order_code));
    } else {
        // Hủy hoặc thất bại
        $stmt = $conn->prepare("
            UPDATE orders
            SET status = 'cancelled',
                cancelled_at = NOW(),
                updated_at = NOW()
            WHERE order_code = ? AND status = 'pending'
        ");
        $stmt->execute([$order_code]);

        header("Location: index.php?url=checkout&error=payment_cancelled");
    }
    exit;
}

function listOrders() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['user'])) {
        header("Location: index.php?url=login");
        exit;
    }
    $user_id = $_SESSION['user']['id'];
    $conn = getDB();

    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;

    $countStmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
    $countStmt->execute([$user_id]);
    $totalOrders = $countStmt->fetchColumn();
    $totalPages = ceil($totalOrders / $limit);

    $stmt = $conn->prepare("
        SELECT id, order_code, created_at, total_amount, discount_amount, shipping_fee, final_amount, status
        FROM orders
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
    $stmt->bindParam(2, $limit, PDO::PARAM_INT);
    $stmt->bindParam(3, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $GLOBALS['orders'] = $orders;
    $GLOBALS['page'] = $page;
    $GLOBALS['totalPages'] = $totalPages;

}

function orderDetail() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['user'])) {
        header("Location: index.php?url=login");
        exit;
    }
    $user_id = $_SESSION['user']['id'];
    $order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if (!$order_id) {
        header("Location: index.php?url=orders");
        exit;
    }

    $conn = getDB();

    $stmt = $conn->prepare("
        SELECT o.*, sa.full_name as receiver_name, sa.phone as receiver_phone, sa.address as delivery_address, sa.city
        FROM orders o
        LEFT JOIN shipping_addresses sa ON sa.id = o.shipping_address_id
        WHERE o.id = ? AND o.user_id = ?
    ");
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        header("Location: index.php?url=orders");
        exit;
    }

    $stmt = $conn->prepare("
        SELECT oi.*, p.name as product_name, p.image
        FROM order_items oi
        JOIN products p ON p.id = oi.product_id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $statusHistory = [];
    try {
        $stmt = $conn->prepare("SELECT * FROM order_statuses WHERE order_id = ? ORDER BY created_at ASC");
        $stmt->execute([$order_id]);
        $statusHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}

    $GLOBALS['order'] = $order;
    $GLOBALS['items'] = $items;
    $GLOBALS['statusHistory'] = $statusHistory;

}

function cancelOrder() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['user'])) {
        echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
        exit;
    }
    $user_id = $_SESSION['user']['id'];
    $order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
    if (!$order_id) {
        echo json_encode(['success' => false, 'message' => 'Thiếu mã đơn hàng']);
        exit;
    }

    $conn = getDB();
    $stmt = $conn->prepare("SELECT status FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Đơn hàng không tồn tại']);
        exit;
    }
    if (!in_array($order['status'], ['pending', 'confirmed'])) {
        echo json_encode(['success' => false, 'message' => 'Không thể hủy đơn hàng ở trạng thái hiện tại']);
        exit;
    }

    $update = $conn->prepare("UPDATE orders SET status = 'cancelled', cancelled_at = NOW() WHERE id = ?");
    $update->execute([$order_id]);

    try {
        $log = $conn->prepare("INSERT INTO order_statuses (order_id, status, note) VALUES (?, 'cancelled', ?)");
        $log->execute([$order_id, 'Người dùng hủy đơn hàng']);
    } catch (Exception $e) {}

    echo json_encode(['success' => true, 'message' => 'Đã hủy đơn hàng']);
    exit;
}

function loadOrders() {
    if (session_status() === PHP_SESSION_NONE) session_start();

    if (!isset($_SESSION['user'])) exit;

    $user_id = $_SESSION['user']['id'];
    $status = $_GET['status'] ?? '';
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

    $limit = 10;
    $offset = ($page - 1) * $limit;

    $conn = getDB();

    // COUNT TOTAL
    $countSql = "SELECT COUNT(*) FROM orders WHERE user_id = ?";
    $countParams = [$user_id];

    if ($status !== '') {
        $countSql .= " AND status = ?";
        $countParams[] = $status;
    }

    $countStmt = $conn->prepare($countSql);
    $countStmt->execute($countParams);
    $totalOrders = $countStmt->fetchColumn();

    $totalPages = ceil($totalOrders / $limit);

    // DATA
    $sql = "SELECT * FROM orders WHERE user_id = ?";
    $params = [$user_id];

    if ($status !== '') {
        $sql .= " AND status = ?";
        $params[] = $status;
    }

    $sql .= " ORDER BY created_at DESC LIMIT $limit OFFSET $offset";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($orders as $order) {
        echo renderOrder($order);
    }

    // trả thêm pagination info cho JS (QUAN TRỌNG)
    echo "<script>
        window.totalPages = $totalPages;
    </script>";
}
