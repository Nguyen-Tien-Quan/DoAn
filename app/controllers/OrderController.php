<?php
// app/Controllers/OrderController.php
require_once __DIR__ . '/../helpers/notification_helper.php';
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

// Thêm hoặc cập nhật địa chỉ giao hàng
function addShippingAddress() {
    header('Content-Type: application/json; charset=utf-8');

    if (!isset($_SESSION['user'])) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
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

    // Validate bắt buộc
    if (!$full_name || !$phone || !$address || !$city) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin']);
        exit;
    }

    // Fix phone
    $phone = preg_replace('/\D/', '', $phone);
    if (substr($phone, 0, 2) === '84') {
        $phone = '0' . substr($phone, 2);
    }
    if (!preg_match('/^0[0-9]{9}$/', $phone)) {
        echo json_encode(['success' => false, 'message' => 'Số điện thoại không hợp lệ']);
        exit;
    }

    // ===== CHỐNG TRÙNG LẶP: kiểm tra trước khi thêm mới =====
    if (!$address_id) {
        $dupCheck = $conn->prepare("
            SELECT id FROM shipping_addresses
            WHERE user_id = ?
              AND full_name = ?
              AND phone = ?
              AND address = ?
              AND city = ?
            LIMIT 1
        ");
        $dupCheck->execute([$user_id, $full_name, $phone, $address, $city]);
        if ($dupCheck->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Địa chỉ này đã tồn tại, vui lòng kiểm tra lại']);
            exit;
        }
    }

    try {
        $conn->beginTransaction();

        // Set default
        if ($is_default == 1) {
            $conn->prepare("UPDATE shipping_addresses SET is_default = 0 WHERE user_id = ?")
                 ->execute([$user_id]);
        }

        if ($address_id) {
            // Cập nhật
            $stmt = $conn->prepare("
                UPDATE shipping_addresses
                SET full_name = ?, phone = ?, address = ?, city = ?, is_default = ?
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$full_name, $phone, $address, $city, $is_default, $address_id, $user_id]);
            $msg = 'Cập nhật địa chỉ thành công';
        } else {
            // Thêm mới
            $stmt = $conn->prepare("
                INSERT INTO shipping_addresses
                (user_id, full_name, phone, address, city, is_default, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$user_id, $full_name, $phone, $address, $city, $is_default]);
            $msg = 'Thêm địa chỉ thành công';
        }

        // Đồng bộ sang bảng customers (nếu có)
        $conn->prepare("
            UPDATE customers
            SET full_name = ?, phone = ?, address = ?
            WHERE user_id = ?
        ")->execute([$full_name, $phone, $address . ', ' . $city, $user_id]);

        $conn->commit();

        echo json_encode(['success' => true, 'message' => $msg]);

    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
    }
    exit;
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

        // ================= THÔNG BÁO ĐẶT HÀNG =================
        createNotification(
            $user_id,
            'Đặt hàng thành công',
            'Đơn hàng #' . $order_id . ' của bạn đã được tạo thành công.',
            'order',
            'index.php?url=order-detail&id=' . $order_id
        );

        header("Location: index.php?url=thank-you&order_id=$order_id");
        exit;

    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['error'] = "Đặt hàng thất bại: " . $e->getMessage();
        header("Location: index.php?url=shipping");
        exit;
    }
}

// Gửi email khi đặt hàng thành công
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

// tạo đơn hàng qua API (dùng cho trang thanh toán)
function createOrderAPI() {
    header('Content-Type: application/json');

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Chưa đăng nhập'
        ]);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true) ?? [];

    $user_id              = $_SESSION['user']['id'];
    $shipping_address_id  = (int)($input['shipping_address_id'] ?? 0);
    $shipping_method      = $input['shipping_method'] ?? 'standard';
    $shipping_fee         = (int)($input['shipping_fee'] ?? 10000);
    $payment_method       = 'cash'; // DB của m đang dùng ENUM cash,momo,vnpay,card

    $cart = $_SESSION['cart'] ?? [];

    if (empty($cart)) {
        echo json_encode([
            'success' => false,
            'message' => 'Giỏ hàng trống'
        ]);
        exit;
    }

    // ==================== TÍNH TIỀN ====================
    $subtotal = 0;

    foreach ($cart as $item) {
        $subtotal += ($item['price'] ?? 0) * ($item['quantity'] ?? 0);
    }

    $discount = $_SESSION['discount'] ?? 0;

    $total = $subtotal - $discount + $shipping_fee;

    if ($total < 0) {
        $total = 0;
    }

    $conn = getDB();

    // ==================== CHECK ĐỊA CHỈ ====================
    $stmt = $conn->prepare("
        SELECT *
        FROM shipping_addresses
        WHERE id = ? AND user_id = ?
    ");

    $stmt->execute([$shipping_address_id, $user_id]);

    $address = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$address) {
        echo json_encode([
            'success' => false,
            'message' => 'Địa chỉ không hợp lệ'
        ]);
        exit;
    }

    $conn->beginTransaction();

    try {

        // ==================== CUSTOMER ID ====================
        $customerStmt = $conn->prepare("
            SELECT id
            FROM customers
            WHERE user_id = ?
            LIMIT 1
        ");

        $customerStmt->execute([$user_id]);

        $customer = $customerStmt->fetch(PDO::FETCH_ASSOC);

        $customer_id = $customer['id'] ?? null;

        // ==================== TẠO MÃ ĐƠN ====================
        $order_code = 'ORD' . date('YmdHis') . rand(100, 999);

        $delivery_address = trim(
            ($address['address'] ?? '') . ', ' .
            ($address['city'] ?? '')
        );

        // ==================== TẠO ORDER ====================
        $stmt = $conn->prepare("
            INSERT INTO orders (
                order_code,
                customer_id,
                user_id,
                shipping_address_id,
                order_type,
                payment_method,
                total_amount,
                discount_amount,
                shipping_fee,
                final_amount,
                payment_status,
                status,
                delivery_address,
                receiver_name,
                receiver_phone,
                created_at
            )
            VALUES (
                ?, ?, ?, ?,
                'delivery',
                ?,
                ?, ?, ?, ?,
                'pending',
                'pending',
                ?, ?, ?,
                NOW()
            )
        ");

        $stmt->execute([
            $order_code,
            $customer_id,
            $user_id,
            $shipping_address_id,
            $payment_method,
            $subtotal,
            $discount,
            $shipping_fee,
            $total,
            $delivery_address,
            $address['full_name'],
            $address['phone']
        ]);

        $order_id = $conn->lastInsertId();

        // ==================== PREPARE QUERY ====================
        $stmtItem = $conn->prepare("
            INSERT INTO order_items (
                order_id,
                product_id,
                variant_id,
                quantity,
                unit_price,
                subtotal,
                created_at
            )
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmtStock = $conn->prepare("
            UPDATE product_variants
            SET stock_quantity = stock_quantity - ?
            WHERE id = ?
            AND stock_quantity >= ?
        ");

        // ==================== LOOP CART ====================
        foreach ($cart as $item) {

            $product_id = (int)($item['id'] ?? 0);
            $variant_id = (int)($item['variant_id'] ?? 0);
            $quantity   = (int)($item['quantity'] ?? 1);
            $price      = (float)($item['price'] ?? 0);

            // fallback variant nếu cart không có variant_id
            if (!$variant_id) {

                $variantStmt = $conn->prepare("
                    SELECT id
                    FROM product_variants
                    WHERE product_id = ?
                    LIMIT 1
                ");

                $variantStmt->execute([$product_id]);

                $variant_id = $variantStmt->fetchColumn();
            }

            if (!$variant_id) {
                throw new Exception("Sản phẩm không có biến thể tồn kho");
            }

            // ==================== CHECK STOCK ====================
            $checkStock = $conn->prepare("
                SELECT stock_quantity
                FROM product_variants
                WHERE id = ?
                LIMIT 1
            ");

            $checkStock->execute([$variant_id]);

            $currentStock = (int)$checkStock->fetchColumn();

            if ($currentStock < $quantity) {
                throw new Exception("Sản phẩm không đủ tồn kho");
            }

            // ==================== INSERT ORDER ITEM ====================
            $stmtItem->execute([
                $order_id,
                $product_id,
                $variant_id,
                $quantity,
                $price,
                $price * $quantity
            ]);

            // ==================== TRỪ KHO ====================
            $stmtStock->execute([
                $quantity,
                $variant_id,
                $quantity
            ]);

            // nếu update fail => rollback
            if ($stmtStock->rowCount() <= 0) {
                throw new Exception("Không thể cập nhật tồn kho");
            }
        }

        // ==================== PAYMENT ====================
        $paymentStmt = $conn->prepare("
            INSERT INTO payments (
                order_id,
                payment_method,
                amount,
                payment_status,
                created_at
            )
            VALUES (?, ?, ?, 'pending', NOW())
        ");

        $paymentStmt->execute([
            $order_id,
            $payment_method,
            $total
        ]);



        $conn->commit();

         // ================= THÔNG BÁO ĐẶT HÀNG =================
        createNotification(
            $user_id,
            'Đặt hàng thành công',
            'Đơn hàng ' . $order_code . ' đã được tạo thành công.',
            'order',
            'index.php?url=order-detail&id=' . $order_id
        );

        // ==================== SEND MAIL ====================
        if (file_exists(__DIR__ . '/../services/MailService.php')) {

            require_once __DIR__ . '/../services/MailService.php';

            sendOrderSuccessEmail(
                $order_code,
                $total,
                $payment_method
            );
        }

        // ==================== CLEAR CART ====================
        unset(
            $_SESSION['cart'],
            $_SESSION['discount'],
            $_SESSION['coupon_code']
        );

        echo json_encode([
            'success'    => true,
            'order_code' => $order_code,
            'redirect'   => 'orders'
        ]);

    } catch (Exception $e) {

        $conn->rollBack();

        echo json_encode([
            'success' => false,
            'message' => 'Lỗi tạo đơn: ' . $e->getMessage()
        ]);
    }

    exit;
}

// Danh sách đơn hàng của user
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

// Chi tiết đơn hàng
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

// Hủy đơn hàng
function cancelOrder() {

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    header('Content-Type: application/json');

    // ================= LOGIN =================
    if (!isset($_SESSION['user'])) {

        echo json_encode([
            'success' => false,
            'message' => 'Chưa đăng nhập'
        ]);

        exit;
    }

    $user_id  = (int)$_SESSION['user']['id'];
    $order_id = (int)($_POST['order_id'] ?? 0);

    if ($order_id <= 0) {

        echo json_encode([
            'success' => false,
            'message' => 'Thiếu mã đơn hàng'
        ]);

        exit;
    }

    $conn = getDB();

    try {

        $conn->beginTransaction();

        // ================= CHECK ORDER =================
        $stmt = $conn->prepare("
            SELECT *
            FROM orders
            WHERE id = ?
            AND user_id = ?
            LIMIT 1
        ");

        $stmt->execute([
            $order_id,
            $user_id
        ]);

        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {

            throw new Exception(
                'Đơn hàng không tồn tại'
            );
        }

        // ================= CHECK STATUS =================
        $allowCancel = [
            'pending',
            'confirmed'
        ];

        if (!in_array($order['status'], $allowCancel)) {

            throw new Exception(
                'Không thể hủy đơn hàng ở trạng thái hiện tại'
            );
        }

        // ================= HOÀN KHO =================
        $itemStmt = $conn->prepare("
            SELECT
                variant_id,
                quantity
            FROM order_items
            WHERE order_id = ?
        ");

        $itemStmt->execute([$order_id]);

        $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

        $stockStmt = $conn->prepare("
            UPDATE product_variants
            SET stock_quantity = stock_quantity + ?
            WHERE id = ?
        ");

        foreach ($items as $item) {

            $variant_id = (int)($item['variant_id'] ?? 0);
            $quantity   = (int)($item['quantity'] ?? 0);

            if ($variant_id > 0 && $quantity > 0) {

                $stockStmt->execute([
                    $quantity,
                    $variant_id
                ]);
            }
        }

        // ================= UPDATE ORDER =================
        $updateStmt = $conn->prepare("
            UPDATE orders
            SET
                status = 'cancelled',
                cancelled_at = NOW(),
                updated_at = NOW()
            WHERE id = ?
        ");

        $updateStmt->execute([$order_id]);

        // ================= UPDATE PAYMENT =================
        try {

            $paymentStmt = $conn->prepare("
                UPDATE payments
                SET
                    payment_status = 'cancelled',
                    updated_at = NOW()
                WHERE order_id = ?
                AND payment_status = 'pending'
            ");

            $paymentStmt->execute([$order_id]);

        } catch (Exception $e) {
            // bỏ qua nếu bảng payments khác structure
        }

        // ================= LOG STATUS =================
        try {

            $logStmt = $conn->prepare("
                INSERT INTO order_statuses (
                    order_id,
                    status,
                    note,
                    created_at
                )
                VALUES (
                    ?,
                    'cancelled',
                    ?,
                    NOW()
                )
            ");

            $logStmt->execute([
                $order_id,
                'Người dùng hủy đơn hàng'
            ]);

        } catch (Exception $e) {
            // bỏ qua nếu chưa có bảng log
        }

        $conn->commit();

        // ================= THÔNG BÁO =================
        try {

            createNotification(
                $user_id,
                'Đã hủy đơn hàng',
                'Đơn hàng #' . ($order['order_code'] ?? $order_id) . ' đã được hủy.',
                'order',
                'index.php?url=orders'
            );

        } catch (Exception $e) {
            // notification lỗi không ảnh hưởng order
        }

        echo json_encode([
            'success' => true,
            'message' => 'Đã hủy đơn hàng thành công'
        ]);

    } catch (Exception $e) {

        if ($conn->inTransaction()) {
            $conn->rollBack();
        }

        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }

    exit;
}


// chạy khi load trang danh sách đơn hàng (dùng cho infinite scroll)
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
