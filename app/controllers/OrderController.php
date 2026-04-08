<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Định nghĩa getDB nếu chưa có (phòng khi config/database.php không tồn tại)
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

// Nếu có file config thì bỏ comment dòng dưới
// require_once __DIR__ . '/../../config/database.php';

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
    $address_id   = $_POST['address_id'] ?? null;
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

        if ($is_default) {
            $conn->prepare("UPDATE shipping_addresses SET is_default = 0 WHERE user_id=?")
                 ->execute([$user_id]);
        }

        if ($address_id) {
            $stmt = $conn->prepare("
                UPDATE shipping_addresses
                SET full_name=?, phone=?, address=?, city=?, is_default=?
                WHERE id=? AND user_id=?
            ");
            $success = $stmt->execute([$full_name, $phone, $address, $city, $is_default, $address_id, $user_id]);
            $message = $success ? 'Cập nhật địa chỉ thành công' : 'Cập nhật thất bại';
        } else {
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
        $subtotal = 0;
        foreach ($_SESSION['cart'] ?? [] as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        $shipping_fee = 10000;
        $total = $subtotal + $shipping_fee;

        $stmt = $conn->prepare("
            INSERT INTO orders (user_id, shipping_address_id, subtotal, shipping_fee, total, payment_method, status)
            VALUES (?, ?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->execute([$user_id, $shipping_address_id, $subtotal, $shipping_fee, $total, $payment_method]);
        $order_id = $conn->lastInsertId();

        foreach ($_SESSION['cart'] ?? [] as $item) {
            $stmt = $conn->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, price)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$order_id, $item['id'], $item['quantity'], $item['price']]);
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

    $user_id = $_SESSION['user']['id'];
    $shipping_address_id = $input['shipping_address_id'] ?? 0;
    $shipping_method = $input['shipping_method'] ?? 'fedex';
    $shipping_fee = intval($input['shipping_fee'] ?? 0);
    $payment_method = $input['payment_method'] ?? 'card';

    $cart = $_SESSION['cart'] ?? [];
    if (empty($cart)) {
        echo json_encode(['success' => false, 'message' => 'Giỏ hàng trống']);
        exit;
    }

    $subtotal = 0;
    foreach ($cart as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }

    $discount = $_SESSION['discount'] ?? 0;
    $total = $subtotal - $discount + $shipping_fee;
    if ($total < 0) $total = 0;

    $conn = getDB();
    $stmt = $conn->prepare("SELECT * FROM shipping_addresses WHERE id = ? AND user_id = ?");
    $stmt->execute([$shipping_address_id, $user_id]);
    $address = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$address) {
        echo json_encode(['success' => false, 'message' => 'Địa chỉ giao hàng không hợp lệ']);
        exit;
    }

    $conn->beginTransaction();
    try {
        $order_code = 'ORD' . date('Ymd') . '_' . strtoupper(uniqid());

        $stmt = $conn->prepare("
            INSERT INTO orders
            (order_code, user_id, shipping_address_id, total_amount, discount_amount, shipping_fee, final_amount, payment_method, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW(), NOW())
        ");
        $stmt->execute([
            $order_code,
            $user_id,
            $shipping_address_id,
            $subtotal,
            $discount,
            $shipping_fee,
            $total,
            $payment_method
        ]);
        $order_id = $conn->lastInsertId();

        $stmtItem = $conn->prepare("
            INSERT INTO order_items (order_id, product_id, variant_id, quantity, unit_price, topping_price, subtotal)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        foreach ($cart as $item) {
            $variant_id = $item['variant']['id'] ?? null;
            $topping_price = 0;
            if (!empty($item['toppings'])) {
                foreach ($item['toppings'] as $topping) {
                    $topping_price += $topping['price'];
                }
            }
            $subtotal_item = $item['price'] * $item['quantity'];
            $stmtItem->execute([
                $order_id,
                $item['id'],
                $variant_id,
                $item['quantity'],
                $item['price'],
                $topping_price,
                $subtotal_item
            ]);
            $order_item_id = $conn->lastInsertId();

            if (!empty($item['toppings'])) {
                $stmtTopping = $conn->prepare("
                    INSERT INTO order_item_toppings (order_item_id, topping_id, price)
                    VALUES (?, ?, ?)
                ");
                foreach ($item['toppings'] as $topping) {
                    $stmtTopping->execute([$order_item_id, $topping['id'], $topping['price']]);
                }
            }

            // ========== CẬP NHẬT TỒN KHO ==========
            if ($variant_id) {
                $stmtStock = $conn->prepare("UPDATE product_variants SET stock_quantity = stock_quantity - ? WHERE id = ? AND stock_quantity >= ?");
                $stmtStock->execute([$item['quantity'], $variant_id, $item['quantity']]);
                if ($stmtStock->rowCount() == 0) {
                    throw new Exception("Sản phẩm '{$item['name']}' không đủ số lượng tồn kho.");
                }
            } else {
                // Nếu sản phẩm không có variant, trừ từ bảng products (cần có cột stock_quantity)
                $stmtStock = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ? AND stock_quantity >= ?");
                $stmtStock->execute([$item['quantity'], $item['id'], $item['quantity']]);
                if ($stmtStock->rowCount() == 0) {
                    throw new Exception("Sản phẩm '{$item['name']}' không đủ số lượng tồn kho.");
                }
            }
            // ====================================
        }

        try {
            $stmtLog = $conn->prepare("INSERT INTO order_statuses (order_id, status, note) VALUES (?, 'pending', ?)");
            $stmtLog->execute([$order_id, 'Đơn hàng được tạo từ thanh toán']);
        } catch (Exception $e) {}

        if (!empty($_SESSION['coupon_data']) && !empty($_SESSION['coupon_code'])) {
            $voucher_code = $_SESSION['coupon_code'];
            $stmtVoucher = $conn->prepare("UPDATE vouchers SET used_count = used_count + 1 WHERE code = ?");
            $stmtVoucher->execute([$voucher_code]);

            try {
                $voucher_id = $_SESSION['coupon_data']['id'] ?? null;
                if ($voucher_id) {
                    $stmtUsage = $conn->prepare("INSERT INTO voucher_usage (voucher_id, customer_id, order_id, used_at) VALUES (?, ?, ?, NOW())");
                    $stmtUsage->execute([$voucher_id, $user_id, $order_id]);
                }
            } catch (Exception $e) {}
        }

        $conn->commit();
        echo json_encode(['success' => true, 'order_id' => $order_id]);
        exit;

    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Lỗi tạo đơn hàng: ' . $e->getMessage()]);
        exit;
    }
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

    $view = __DIR__ . '/../../resources/views/pages/orders.php';
    $layout = __DIR__ . '/../../resources/views/layouts/layout.php';
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

    // Sửa đường dẫn: lên 2 cấp từ app/Controllers đến root
    $view = __DIR__ . '/../../resources/views/pages/order-detail.php';
    $layout = __DIR__ . '/../../resources/views/layouts/layout.php';
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
?>
