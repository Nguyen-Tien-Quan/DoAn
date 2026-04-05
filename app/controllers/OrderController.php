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
