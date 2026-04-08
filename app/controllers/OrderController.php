<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../config/database.php';

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

    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['user'])) {
        echo json_encode(['success'=>false,'message'=>'Vui lòng đăng nhập']);
        exit;
    }

    $user_id = $_SESSION['user']['id'];
    $address_id   = $_POST['address_id'] ?? null; // NEW: hidden input khi edit
    $full_name    = trim($_POST['recipient_name'] ?? '');
    $phone        = trim($_POST['phone'] ?? '');
    $address      = trim($_POST['address'] ?? '');
    $city         = trim($_POST['city'] ?? '');
    $is_default   = isset($_POST['is_default']) ? 1 : 0;

    if (!$full_name || !$phone || !$address || !$city) {
        echo json_encode(['success'=>false,'message'=>'Vui lòng điền đầy đủ thông tin']);
        exit;
    }

    try {
        $conn = getDB();

        // Nếu đặt mặc định → reset mặc định các address khác
        if ($is_default) {
            $conn->prepare("UPDATE shipping_addresses SET is_default = 0 WHERE user_id=?")
                 ->execute([$user_id]);
        }

        if ($address_id) {
            // Update address cũ
            $stmt = $conn->prepare("
                UPDATE shipping_addresses
                SET full_name=?, phone=?, address=?, city=?, is_default=?
                WHERE id=? AND user_id=?
            ");
            $success = $stmt->execute([$full_name, $phone, $address, $city, $is_default, $address_id, $user_id]);
            $message = $success ? 'Cập nhật địa chỉ thành công' : 'Cập nhật thất bại';
        } else {
            // Thêm mới
            $stmt = $conn->prepare("
                INSERT INTO shipping_addresses
                (user_id, full_name, phone, address, city, is_default, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $success = $stmt->execute([$user_id, $full_name, $phone, $address, $city, $is_default]);
            $message = $success ? 'Thêm địa chỉ thành công' : 'Thêm thất bại';
        }

        echo json_encode(['success'=>$success,'message'=>$message]);
        exit;

    } catch (\PDOException $e) {
        echo json_encode(['success'=>false,'message'=>'Lỗi DB: '.$e->getMessage()]);
        exit;
    }
}

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
    $conn->beginTransaction();

    try {
        // Tính tổng tiền từ session cart
        $subtotal = 0;
        foreach ($_SESSION['cart'] ?? [] as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        $shipping_fee = 10000;
        $total = $subtotal + $shipping_fee;

        // Tạo order
        $stmt = $conn->prepare("
            INSERT INTO orders (user_id, shipping_address_id, subtotal, shipping_fee, total, payment_method, status)
            VALUES (?, ?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->execute([$user_id, $shipping_address_id, $subtotal, $shipping_fee, $total, $payment_method]);
        $order_id = $conn->lastInsertId();

        // Thêm order items
        foreach ($_SESSION['cart'] ?? [] as $item) {
            $stmt = $conn->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, price)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$order_id, $item['id'], $item['quantity'], $item['price']]);
        }

        // Xóa giỏ hàng
        unset($_SESSION['cart']);

        $conn->commit();

        // Chuyển sang trang cảm ơn
        header("Location: index.php?url=thank-you&order_id=$order_id");
        exit;

    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['error'] = "Đặt hàng thất bại: " . $e->getMessage();
        header("Location: index.php?url=shipping");
        exit;
    }
}

// ==================== ORDER LISTING ====================

function listOrders() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['user'])) {
        header("Location: index.php?url=login");
        exit;
    }
    $user_id = $_SESSION['user']['id'];
    $conn = getDB();

    // Phân trang
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;

    // Đếm tổng số đơn hàng
    $countStmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
    $countStmt->execute([$user_id]);
    $totalOrders = $countStmt->fetchColumn();
    $totalPages = ceil($totalOrders / $limit);

    // Lấy danh sách đơn hàng
    $stmt = $conn->prepare("
        SELECT id, order_code, created_at, total_amount, discount_amount, shipping_fee, final_amount, status
        FROM orders
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$user_id, $limit, $offset]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Lưu vào biến để dùng trong view
    $GLOBALS['orders'] = $orders;
    $GLOBALS['page'] = $page;
    $GLOBALS['totalPages'] = $totalPages;

    // Load view
    $view = __DIR__ . '/../resources/views/pages/orders.php';
    $layout = __DIR__ . '/../resources/views/layouts/layout.php';
    include $layout;
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

    // Lấy thông tin đơn hàng
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

    // Lấy danh sách sản phẩm trong đơn hàng
    $stmt = $conn->prepare("
        SELECT oi.*, p.name as product_name, p.image
        FROM order_items oi
        JOIN products p ON p.id = oi.product_id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Lấy lịch sử trạng thái (nếu có bảng order_statuses)
    $statusHistory = [];
    try {
        $stmt = $conn->prepare("SELECT * FROM order_statuses WHERE order_id = ? ORDER BY created_at ASC");
        $stmt->execute([$order_id]);
        $statusHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // Nếu chưa có bảng thì bỏ qua
    }

    $GLOBALS['order'] = $order;
    $GLOBALS['items'] = $items;
    $GLOBALS['statusHistory'] = $statusHistory;

    $view = __DIR__ . '/../resources/views/pages/order-detail.php';
    $layout = __DIR__ . '/../resources/views/layouts/layout.php';
    include $layout;
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
    // Kiểm tra đơn hàng thuộc user và trạng thái có thể hủy (pending hoặc confirmed)
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

    // Cập nhật trạng thái thành cancelled
    $update = $conn->prepare("UPDATE orders SET status = 'cancelled', cancelled_at = NOW() WHERE id = ?");
    $update->execute([$order_id]);

    // Ghi log lịch sử nếu có bảng
    try {
        $log = $conn->prepare("INSERT INTO order_statuses (order_id, status, note) VALUES (?, 'cancelled', ?)");
        $log->execute([$order_id, 'Người dùng hủy đơn hàng']);
    } catch (Exception $e) {}

    echo json_encode(['success' => true, 'message' => 'Đã hủy đơn hàng']);
    exit;
}
