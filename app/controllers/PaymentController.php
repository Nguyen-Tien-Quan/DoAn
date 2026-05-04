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
function vnpayReturn() {
    // Xử lý kết quả thanh toán (bạn có thể copy code mẫu từ VNPay)
    $vnp_ResponseCode = $_GET['vnp_ResponseCode'] ?? '';
    $order_code = $_GET['vnp_TxnRef'] ?? '';

    if ($vnp_ResponseCode == '00') {
        // Thanh toán thành công
        // Cập nhật trạng thái order
        $conn = getDB();
        $stmt = $conn->prepare("UPDATE orders SET payment_status = 'paid', status = 'confirmed' WHERE order_code = ?");
        $stmt->execute([$order_code]);

        header("Location: index.php?url=thank-you&order_code=" . $order_code);
    } else {
        header("Location: index.php?url=checkout&error=payment_failed");
    }
    exit;
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
