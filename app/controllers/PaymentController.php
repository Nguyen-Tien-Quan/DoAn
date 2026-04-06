<?php
require_once __DIR__ . '/../../config/database.php';
$conn = getDB();

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
    $stmt = $conn->prepare("SELECT * FROM payments WHERE payment_status = ? ORDER BY created_at DESC");
    $stmt->execute([$status]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Tạo payment mới
 */
function createPayment(PDO $conn, int $order_id, string $method, float $amount, ?string $transaction_code = null) {
    $stmt = $conn->prepare("
        INSERT INTO payments (order_id, payment_method, amount, transaction_code, payment_status, created_at, updated_at)
        VALUES (?, ?, ?, ?, 'pending', NOW(), NOW())
    ");
    $stmt->execute([$order_id, $method, $amount, $transaction_code]);
    return $conn->lastInsertId();
}

/**
 * Cập nhật trạng thái payment
 */
function updatePaymentStatus(PDO $conn, int $payment_id, string $status, ?string $paid_at = null) {
    $paidAtSql = $paid_at ? ", paid_at = ?" : "";
    $params = $paid_at ? [$status, $paid_at, $payment_id] : [$status, $payment_id];

    $stmt = $conn->prepare("
        UPDATE payments
        SET payment_status = ? $paidAtSql, updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute($params);
    return $stmt->rowCount();
}

/**
 * Cập nhật payment đầy đủ (update tất cả các cột)
 * $data = ['payment_method'=>'...', 'amount'=>..., 'transaction_code'=>'...', 'payment_status'=>'completed']
 */
function updatePayment(PDO $conn, int $payment_id, array $data) {
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
 * Hoàn tất payment (chỉ dành cho online payment)
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
