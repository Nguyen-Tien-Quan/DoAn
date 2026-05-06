<?php
require_once __DIR__ . '/../../config/database.php';

/**
 * Lấy tất cả payments
 */
function getPayments(PDO $conn) {
    $stmt = $conn->query("
        SELECT p.*, o.order_code, o.customer_id
        FROM payments p
        JOIN orders o ON o.id = p.order_id
        ORDER BY p.created_at DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Lấy payment theo id
 */
function getPaymentById(PDO $conn, int $id) {
    $stmt = $conn->prepare("SELECT * FROM payments WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Lấy payment theo order_id
 */
function getPaymentByOrder(PDO $conn, int $order_id) {
    $stmt = $conn->prepare("SELECT * FROM payments WHERE order_id = ?");
    $stmt->execute([$order_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Lấy payment theo customer_id
 */
function getPaymentsByCustomer(PDO $conn, int $customer_id) {
    $stmt = $conn->prepare("
        SELECT p.*, o.order_code
        FROM payments p
        JOIN orders o ON o.id = p.order_id
        WHERE o.customer_id = ?
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$customer_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Lấy payment theo trạng thái
 */
function getPaymentsByStatus(PDO $conn, string $status) {
    $allowed = ['pending', 'completed', 'failed', 'cancelled'];
    if (!in_array($status, $allowed)) return [];

    $stmt = $conn->prepare("
        SELECT * FROM payments
        WHERE payment_status = ?
        ORDER BY created_at DESC
    ");
    $stmt->execute([$status]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Tạo payment mới
 */
function createPayment(PDO $conn, int $order_id, string $method, float $amount, ?string $transaction_code = null) {
    try {
        $conn->beginTransaction();

        $stmt = $conn->prepare("
            INSERT INTO payments
            (order_id, payment_method, amount, transaction_code, payment_status, created_at, updated_at)
            VALUES (?, ?, ?, ?, 'pending', NOW(), NOW())
        ");

        $stmt->execute([
            $order_id,
            $method,
            (float)$amount,
            $transaction_code
        ]);

        $id = $conn->lastInsertId();
        $conn->commit();

        return $id;

    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
}

/**
 * Cập nhật trạng thái payment
 */
function updatePaymentStatus(PDO $conn, int $payment_id, string $status, ?string $paid_at = null) {
    $allowed = ['pending', 'completed', 'failed', 'cancelled'];
    if (!in_array($status, $allowed)) return 0;

    if ($paid_at) {
        $stmt = $conn->prepare("
            UPDATE payments
            SET payment_status = ?, paid_at = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$status, $paid_at, $payment_id]);
    } else {
        $stmt = $conn->prepare("
            UPDATE payments
            SET payment_status = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$status, $payment_id]);
    }

    return $stmt->rowCount();
}

/**
 * Update toàn bộ payment
 */
function updatePayment(PDO $conn, int $payment_id, array $data) {
    if (empty($data)) return 0;

    $fields = [];
    $params = [];

    foreach ($data as $key => $value) {
        $fields[] = "$key = ?";
        $params[] = $value;
    }

    $params[] = $payment_id;

    $sql = "UPDATE payments SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    return $stmt->rowCount();
}

/**
 * Hoàn tất payment (online)
 */
function completePayment(PDO $conn, int $payment_id, ?string $transaction_code = null) {
    $stmt = $conn->prepare("
        UPDATE payments
        SET payment_status = 'completed',
            transaction_code = COALESCE(?, transaction_code),
            paid_at = NOW(),
            updated_at = NOW()
        WHERE id = ?
    ");

    $stmt->execute([$transaction_code, $payment_id]);
    return $stmt->rowCount();
}

/**
 * Xóa payment
 */
function deletePayment(PDO $conn, int $payment_id) {
    $stmt = $conn->prepare("DELETE FROM payments WHERE id = ?");
    $stmt->execute([$payment_id]);
    return $stmt->rowCount();
}

function createVNPayPayment() {

    if (!isset($_GET['order_id']) || !isset($_GET['amount'])) {
        header("Location: index.php?url=home");
        exit;
    }

    $order_id   = (int)$_GET['order_id'];
    $amount     = (int)$_GET['amount'];
    $order_code = $_GET['order_code'] ?? 'ORD' . time();

    // ================== CẤU HÌNH VNPAY (SANDBOX) ==================
    $vnp_Url        = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
    $vnp_Returnurl  = "http://localhost/DoAn/DoAnTotNghiep/public/index.php?url=vnpay-return";

    $vnp_TmnCode    = "CF1B4P54";           // ← Thay bằng code của bạn
    $vnp_HashSecret = "VTUT86XS46LYE2XP3294FSHP1NHIXAJ1";        // ← Thay bằng secret của bạn

    $inputData = [
        "vnp_Version"    => "2.1.0",
        "vnp_TmnCode"    => $vnp_TmnCode,
        "vnp_Amount"     => $amount * 100,
        "vnp_Command"    => "pay",
        "vnp_CreateDate" => date('YmdHis'),
        "vnp_CurrCode"   => "VND",
        "vnp_IpAddr"     => $_SERVER['REMOTE_ADDR'],
        "vnp_Locale"     => "vn",
        "vnp_OrderInfo"  => "Thanh toan don hang " . $order_code,
        "vnp_OrderType"  => "billpayment",
        "vnp_ReturnUrl"  => $vnp_Returnurl,
        "vnp_TxnRef"     => $order_code,
    ];

    // Sắp xếp tham số và tạo hash
    ksort($inputData);
    $hashdata = http_build_query($inputData);
    $vnp_SecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);

    $vnp_Url = $vnp_Url . "?" . $hashdata . "&vnp_SecureHash=" . $vnp_SecureHash;

    header("Location: " . $vnp_Url);
    exit;
}
// Callback từ VNPay
// Callback từ VNPay
function vnpayReturn() {
    $vnp_ResponseCode = $_GET['vnp_ResponseCode'] ?? '';
    $order_code       = $_GET['vnp_TxnRef'] ?? $_GET['order_code'] ?? '';

    if (empty($order_code)) {
        header("Location: index.php?url=checkout");
        exit;
    }

    $conn = getDB();

    if ($vnp_ResponseCode == '00') {

        if (!isset($_SESSION['pending_payment'])) {
            header("Location: index.php?url=checkout&error=session_expired");
            exit;
        }

        $pending = $_SESSION['pending_payment'];

        try {
            $conn->beginTransaction();

            // Tạo đơn hàng
            $stmt = $conn->prepare("
                INSERT INTO orders
                (order_code, user_id, shipping_address_id, order_type, total_amount,
                 discount_amount, shipping_fee, final_amount, payment_method,
                 payment_status, status, delivery_address, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'paid', 'confirmed', ?, NOW(), NOW())
            ");

            $delivery_address = $pending['delivery_address'] ?? 'Chưa cập nhật';

            $stmt->execute([
                $pending['order_code'],
                $_SESSION['user']['id'],
                $pending['shipping_address_id'],
                'delivery',
                $pending['subtotal'],
                $pending['discount'],
                $pending['shipping_fee'],
                $pending['total'],
                $pending['payment_method'],
                $delivery_address
            ]);

            $order_id = $conn->lastInsertId();

            // Thêm order items
            if (!empty($_SESSION['cart'])) {
                $stmtItem = $conn->prepare("
                    INSERT INTO order_items (order_id, product_id, variant_id, quantity, unit_price, subtotal)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");

                foreach ($_SESSION['cart'] as $item) {
                    $variant_id = $item['variant_id'] ?? null;
                    $subtotal = $item['price'] * $item['quantity'];

                    $stmtItem->execute([
                        $order_id,
                        $item['id'],
                        $variant_id,
                        $item['quantity'],
                        $item['price'],
                        $subtotal
                    ]);
                }
            }

            $conn->commit();

            // ==================== GỬI EMAIL CHO ADMIN ====================
            require_once __DIR__ . '/../services/MailService.php';   // ← Quan trọng

            if (class_exists('MailService')) {
                $html = "
                    <h2>📦 Đơn hàng mới - Thanh toán thành công</h2>
                    <p><strong>Mã đơn:</strong> {$pending['order_code']}</p>
                    <p><strong>Khách hàng:</strong> " . ($_SESSION['user']['name'] ?? 'Khách hàng') . "</p>
                    <p><strong>Tổng tiền:</strong> " . number_format($pending['total']) . "đ</p>
                    <p><strong>Phương thức:</strong> " . strtoupper($pending['payment_method']) . "</p>
                    <hr>
                    <p>Đơn hàng đã được xác nhận thanh toán.</p>
                ";

                $sent = MailService::send('nguyentienquan1st@gmail.com', 'Đơn hàng mới #' . $pending['order_code'], $html);

                if (!$sent) {
                    error_log("Gửi email thất bại cho đơn " . $pending['order_code']);
                }
            }

            // Xóa session
            unset($_SESSION['pending_payment'], $_SESSION['cart'], $_SESSION['discount'], $_SESSION['coupon_code']);

            header("Location: index.php?url=thank-you&order_code=" . urlencode($pending['order_code']));
            exit;

        } catch (Exception $e) {
            $conn->rollBack();
            error_log("Tạo đơn hàng thất bại: " . $e->getMessage());
            header("Location: index.php?url=checkout&error=order_create_failed");
            exit;
        }

    } else {
        unset($_SESSION['pending_payment']);
        header("Location: index.php?url=checkout&error=payment_cancelled");
        exit;
    }
}
// ====================== MOMO PAYMENT ======================
function createMoMoPayment() {
    if (!isset($_GET['order_id']) || !isset($_GET['amount'])) {
        header("Location: index.php?url=home");
        exit;
    }

    $order_id   = (int)$_GET['order_id'];
    $amount     = (int)$_GET['amount'];
    $order_code = $_GET['order_code'] ?? 'ORD' . time();

    // ================== CẤU HÌNH MOMO SANDBOX ==================
    $endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";

    $partnerCode = 'YOUR_MOMO_PARTNER_CODE';        // ← Thay bằng code của bạn
    $accessKey   = 'YOUR_MOMO_ACCESS_KEY';          // ← Thay bằng Access Key
    $secretKey   = 'YOUR_MOMO_SECRET_KEY';          // ← Thay bằng Secret Key

    $redirectUrl = "http://localhost/DoAn/DoAnTotNghiep/public/index.php?url=payment-return&method=momo";
    $ipnUrl      = "http://localhost/DoAn/DoAnTotNghiep/public/index.php?url=momo-ipn";

    $requestId   = time() . "";
    $orderInfo   = "Thanh toán đơn hàng " . $order_code;
    $extraData   = "";

    $rawHash = "accessKey=" . $accessKey .
               "&amount=" . $amount .
               "&extraData=" . $extraData .
               "&ipnUrl=" . $ipnUrl .
               "&orderId=" . $order_code .
               "&orderInfo=" . $orderInfo .
               "&partnerCode=" . $partnerCode .
               "&redirectUrl=" . $redirectUrl .
               "&requestId=" . $requestId .
               "&requestType=payWithMethod";

    $signature = hash_hmac("sha256", $rawHash, $secretKey);

    $data = [
        'partnerCode'  => $partnerCode,
        'partnerName'  => "TRQShop",
        'storeId'      => "MomoTestStore",
        'requestId'    => $requestId,
        'amount'       => $amount,
        'orderId'      => $order_code,
        'orderInfo'    => $orderInfo,
        'redirectUrl'  => $redirectUrl,
        'ipnUrl'       => $ipnUrl,
        'lang'         => 'vi',
        'extraData'    => $extraData,
        'requestType'  => 'payWithMethod',
        'signature'    => $signature
    ];

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    $result = curl_exec($ch);
    curl_close($ch);

    $jsonResult = json_decode($result, true);

    if (isset($jsonResult['payUrl'])) {
        header("Location: " . $jsonResult['payUrl']);
    } else {
        echo "Lỗi tạo thanh toán MoMo: " . ($jsonResult['message'] ?? 'Unknown error');
    }
    exit;
}

// Chuẩn bị thanh toán online (không tạo đơn hàng)
function preparePayment() {
    header('Content-Type: application/json');
    if (session_status() === PHP_SESSION_NONE) session_start();

    if (!isset($_SESSION['user'])) {
        echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);

    $shipping_address_id = (int)($input['shipping_address_id'] ?? 0);
    $payment_method      = $input['payment_method'] ?? 'vnpay';
    $shipping_fee        = (int)($input['shipping_fee'] ?? 10000);

    // Kiểm tra giỏ hàng
    if (empty($_SESSION['cart'])) {
        echo json_encode(['success' => false, 'message' => 'Giỏ hàng trống']);
        exit;
    }

    // Kiểm tra địa chỉ
    $conn = getDB();
    $stmt = $conn->prepare("SELECT id FROM shipping_addresses WHERE id = ? AND user_id = ?");
    $stmt->execute([$shipping_address_id, $_SESSION['user']['id']]);
    if (!$stmt->fetchColumn()) {
        echo json_encode(['success' => false, 'message' => 'Địa chỉ không hợp lệ']);
        exit;
    }

    // Tính tổng tiền
    $subtotal = 0;
    foreach ($_SESSION['cart'] as $item) {
        $subtotal += ($item['price'] ?? 0) * ($item['quantity'] ?? 0);
    }
    $discount = $_SESSION['discount'] ?? 0;
    $total = $subtotal - $discount + $shipping_fee;

    // Tạo order_code tạm để truyền sang VNPay
    $temp_order_code = 'ORD' . date('YmdHis') . rand(100, 999);

    // Lưu thông tin tạm vào session để sau callback sẽ tạo đơn
    $_SESSION['pending_payment'] = [
        'order_code'         => $temp_order_code,
        'shipping_address_id'=> $shipping_address_id,
        'shipping_method'    => $input['shipping_method'] ?? 'standard',
        'shipping_fee'       => $shipping_fee,
        'payment_method'     => $payment_method,
        'total'              => $total,
        'subtotal'           => $subtotal,
        'discount'           => $discount,
        'timestamp'          => time()
    ];

    $payment_url = generatePaymentUrl(0, $total, $payment_method, $temp_order_code);

    echo json_encode([
        'success'     => true,
        'payment_url' => $payment_url,
        'order_code'  => $temp_order_code
    ]);
    exit;
}
