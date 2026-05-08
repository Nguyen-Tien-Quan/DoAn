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

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $vnp_ResponseCode = $_GET['vnp_ResponseCode'] ?? '';
    $order_code       = $_GET['vnp_TxnRef'] ?? '';

    if (empty($order_code)) {
        header("Location: index.php?url=checkout");
        exit;
    }

    $conn = getDB();

    // ================= THANH TOÁN THÀNH CÔNG =================
    if ($vnp_ResponseCode == '00') {

        if (!isset($_SESSION['pending_payment'])) {
            header("Location: index.php?url=orders");
            exit;
        }

        $pending = $_SESSION['pending_payment'];

        try {

            $conn->beginTransaction();

            // ================= CUSTOMER =================
            $customerStmt = $conn->prepare("
                SELECT id
                FROM customers
                WHERE user_id = ?
                LIMIT 1
            ");

            $customerStmt->execute([
                $_SESSION['user']['id']
            ]);

            $customer_id = $customerStmt->fetchColumn();

            if (!$customer_id) {
                throw new Exception("Không tìm thấy customer");
            }

            // ================= ADDRESS =================
            $addressStmt = $conn->prepare("
                SELECT *
                FROM shipping_addresses
                WHERE id = ?
                LIMIT 1
            ");

            $addressStmt->execute([
                $pending['shipping_address_id']
            ]);

            $address = $addressStmt->fetch(PDO::FETCH_ASSOC);

            if (!$address) {
                throw new Exception("Không tìm thấy địa chỉ");
            }

            // ================= TOTAL =================
            $subtotal      = (float)($pending['subtotal'] ?? 0);
            $shipping_fee  = (float)($pending['shipping_fee'] ?? 0);
            $discount      = (float)($pending['discount'] ?? 0);

            $total_amount  = $subtotal + $shipping_fee;
            $final_amount  = $total_amount - $discount;

            if ($final_amount < 0) {
                $final_amount = 0;
            }

            // ================= ADDRESS STRING =================
            $addressText = trim($address['address'] ?? '');
            $ward        = trim($address['ward'] ?? '');
            $district    = trim($address['district'] ?? '');
            $city        = trim($address['city'] ?? '');

            $addressParts = [];

            if ($addressText !== '') {
                $addressParts[] = $addressText;
            }

            if ($ward !== '') {
                $addressParts[] = $ward;
            }

            if ($district !== '') {
                $addressParts[] = $district;
            }

            if ($city !== '') {
                $addressParts[] = $city;
            }

            $delivery_address = implode(', ', $addressParts);

            // ================= PAYMENT STATUS FIX =================
            // CHECK ENUM payment_status trong DB
            // nếu DB dùng paid/unpaid thì đổi completed -> paid

            $payment_status = 'paid';

            // ================= CREATE ORDER =================
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
                    created_at,
                    updated_at
                )
                VALUES (
                    ?, ?, ?, ?,
                    'delivery',
                    ?,
                    ?, ?, ?, ?,
                    ?,
                    'confirmed',
                    ?, ?, ?,
                    NOW(),
                    NOW()
                )
            ");

            $stmt->execute([
                $pending['order_code'],
                $customer_id,
                $_SESSION['user']['id'],
                $pending['shipping_address_id'],

                // payment_method
                $pending['payment_method'] ?? 'vnpay',

                // total_amount
                $total_amount,

                // discount_amount
                $discount,

                // shipping_fee
                $shipping_fee,

                // final_amount
                $final_amount,

                // payment_status
                $payment_status,

                // delivery_address
                $delivery_address,

                // receiver_name
                $address['full_name'] ?? '',

                // receiver_phone
                $address['phone'] ?? ''
            ]);

            $order_id = $conn->lastInsertId();

            if (!$order_id) {
                throw new Exception("Không tạo được đơn hàng");
            }

            // ================= INSERT ORDER ITEMS =================
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

            // ================= UPDATE STOCK =================
            $stmtStock = $conn->prepare("
                UPDATE product_variants
                SET stock_quantity = stock_quantity - ?
                WHERE id = ?
                AND stock_quantity >= ?
            ");

            // ================= LOOP CART =================
            foreach ($_SESSION['cart'] as $item) {

                $product_id = (int)($item['id'] ?? 0);
                $quantity   = (int)($item['quantity'] ?? 0);
                $price      = (float)($item['price'] ?? 0);

                $variant_id = (int)($item['variant_id'] ?? 0);

                // fallback variant
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
                    throw new Exception(
                        "Sản phẩm ID {$product_id} chưa có variant"
                    );
                }

                // CHECK STOCK
                $checkStock = $conn->prepare("
                    SELECT stock_quantity
                    FROM product_variants
                    WHERE id = ?
                    LIMIT 1
                ");

                $checkStock->execute([$variant_id]);

                $stock = (int)$checkStock->fetchColumn();

                if ($stock < $quantity) {
                    throw new Exception(
                        "Sản phẩm không đủ tồn kho"
                    );
                }

                // INSERT ITEM
                $stmtItem->execute([
                    $order_id,
                    $product_id,
                    $variant_id,
                    $quantity,
                    $price,
                    $price * $quantity
                ]);

                // UPDATE STOCK
                $stmtStock->execute([
                    $quantity,
                    $variant_id,
                    $quantity
                ]);

                if ($stmtStock->rowCount() <= 0) {
                    throw new Exception(
                        "Không thể cập nhật tồn kho"
                    );
                }
            }

            // ================= INSERT PAYMENT =================
            $paymentStmt = $conn->prepare("
                INSERT INTO payments (
                    order_id,
                    payment_method,
                    amount,
                    payment_status,
                    transaction_code,
                    paid_at,
                    created_at,
                    updated_at
                )
                VALUES (
                    ?, ?, ?, ?,
                    ?, NOW(),
                    NOW(),
                    NOW()
                )
            ");

            $paymentStmt->execute([
                $order_id,
                $pending['payment_method'] ?? 'vnpay',
                $final_amount,
                $payment_status,
                $_GET['vnp_TransactionNo'] ?? null
            ]);

            // ================= COMMIT =================
            $conn->commit();

            // ================= SEND MAIL =================
            if (file_exists(__DIR__ . '/../services/MailService.php')) {

                require_once __DIR__ . '/../services/MailService.php';

                sendOrderSuccessEmail(
                    $pending['order_code'],
                    $final_amount,
                    $pending['payment_method'] ?? 'vnpay'
                );
            }

            // ================= CLEAR SESSION =================
            unset(
                $_SESSION['pending_payment'],
                $_SESSION['cart'],
                $_SESSION['discount'],
                $_SESSION['coupon_code']
            );

            header("Location: index.php?url=orders&success=payment_success");
            exit;

        } catch (Exception $e) {

            if ($conn->inTransaction()) {
                $conn->rollBack();
            }

            error_log(
                "VNPay Return Error: " . $e->getMessage()
            );

            echo $e->getMessage();
            exit;
        }

    } else {

        unset($_SESSION['pending_payment']);

        header(
            "Location: index.php?url=checkout&error=payment_failed"
        );

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
