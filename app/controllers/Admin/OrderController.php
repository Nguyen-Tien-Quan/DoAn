<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../../config/database.php';

/**
 * LẤY DANH SÁCH ĐƠN HÀNG
 */
function getOrders($page = 1, $limit = 10, $filters = [])
{
    $conn = getDB();

    $offset = ($page - 1) * $limit;

    $search = $filters['search'] ?? '';
    $status = $filters['status'] ?? '';

    $where = "WHERE 1=1";
    $params = [];

    // SEARCH
    if (!empty($search)) {
        $where .= " AND o.order_code LIKE ?";
        $params[] = "%$search%";
    }

    // STATUS
    if (!empty($status)) {
        $where .= " AND o.status = ?";
        $params[] = $status;
    }

    // COUNT
    $countSql = "
        SELECT COUNT(*)
        FROM orders o
        $where
    ";

    $stmt = $conn->prepare($countSql);
    $stmt->execute($params);

    $total = $stmt->fetchColumn();

    // DATA
    $sql = "
        SELECT
            o.*,

            COALESCE(
                o.receiver_name,
                c.full_name,
                u.name,
                'Khách lẻ'
            ) AS full_name,

            COALESCE(
                o.receiver_phone,
                c.phone,
                u.phone
            ) AS phone,

            p.payment_status

        FROM orders o

        LEFT JOIN customers c
            ON o.customer_id = c.id

        LEFT JOIN users u
            ON o.user_id = u.id

        LEFT JOIN payments p
            ON o.id = p.order_id

        $where

        ORDER BY o.id DESC

        LIMIT $limit OFFSET $offset
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    return [
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        'total' => $total,
        'totalPages' => ceil($total / $limit)
    ];
}

/**
 * CHI TIẾT ĐƠN HÀNG
 */
function getOrderDetail($id)
{
    $conn = getDB();

    $stmt = $conn->prepare("
        SELECT
            o.*,

            COALESCE(
                o.receiver_name,
                c.full_name,
                u.name,
                'Khách lẻ'
            ) AS full_name,

            COALESCE(
                o.receiver_phone,
                c.phone,
                u.phone
            ) AS phone,

            COALESCE(
                o.delivery_address,
                c.address
            ) AS address,

            p.payment_method,
            p.payment_status

        FROM orders o

        LEFT JOIN customers c
            ON o.customer_id = c.id

        LEFT JOIN users u
            ON o.user_id = u.id

        LEFT JOIN payments p
            ON o.id = p.order_id

        WHERE o.id = ?
    ");

    $stmt->execute([$id]);

    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        return null;
    }

    // ITEMS
    $itemsStmt = $conn->prepare("
    SELECT
        oi.*,

        pr.name AS product_name,
        pr.image AS product_image,

        pv.variant_name

        FROM order_items oi

        LEFT JOIN products pr
            ON oi.product_id = pr.id

        LEFT JOIN product_variants pv
            ON oi.variant_id = pv.id

        WHERE oi.order_id = ?
    ");

    $itemsStmt->execute([$id]);

    $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

    // TOPPINGS
    foreach ($items as &$item) {

        $topStmt = $conn->prepare("
            SELECT
                t.name,
                oit.price

            FROM order_item_toppings oit

            JOIN toppings t
                ON oit.topping_id = t.id

            WHERE oit.order_item_id = ?
        ");

        $topStmt->execute([$item['id']]);

        $item['toppings'] = $topStmt->fetchAll(PDO::FETCH_ASSOC);
    }

    return [
        'order' => $order,
        'items' => $items
    ];
}

/**
 * UPDATE STATUS
 */
function updateOrderStatus($order_id, $new_status)
{
    $conn = getDB();

    $new_status = strtolower(trim($new_status));

    $allowed = [
        'pending',
        'confirmed',
        'preparing',
        'ready_for_delivery',
        'delivering',
        'completed',
        'cancelled'
    ];

    if (!in_array($new_status, $allowed)) {
        return [
            'success' => false,
            'message' => 'Trạng thái không hợp lệ'
        ];
    }

    $stmt = $conn->prepare("
        SELECT id, status
        FROM orders
        WHERE id = ?
    ");

    $stmt->execute([$order_id]);

    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        return [
            'success' => false,
            'message' => 'Không tìm thấy đơn'
        ];
    }

    $old = strtolower($order['status']);

    $valid = [
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['preparing', 'cancelled'],
        'preparing' => ['ready_for_delivery', 'cancelled'],
        'ready_for_delivery' => ['delivering', 'cancelled'],
        'delivering' => ['completed', 'cancelled'],
        'completed' => [],
        'cancelled' => []
    ];

    if (!in_array($new_status, $valid[$old] ?? [])) {
        return [
            'success' => false,
            'message' => "Không thể chuyển từ $old sang $new_status"
        ];
    }

    try {

        $conn->beginTransaction();

        $delivery_status = null;

        if ($new_status === 'ready_for_delivery') {
            $delivery_status = 'pending';
        }

        elseif ($new_status === 'delivering') {
            $delivery_status = 'shipping';
        }

        elseif ($new_status === 'completed') {
            $delivery_status = 'delivered';
        }

        elseif ($new_status === 'cancelled') {
            $delivery_status = 'failed';
        }

        if ($delivery_status) {

            $stmt = $conn->prepare("
                UPDATE orders
                SET
                    status = ?,
                    delivery_status = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");

            $stmt->execute([
                $new_status,
                $delivery_status,
                $order_id
            ]);

        } else {

            $stmt = $conn->prepare("
                UPDATE orders
                SET
                    status = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");

            $stmt->execute([
                $new_status,
                $order_id
            ]);
        }

        $conn->commit();

        return ['success' => true];

    } catch (Exception $e) {

        $conn->rollBack();

        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

/**
 * RENDER HTML DETAIL
 */
function renderOrderDetailHTML($id)
{
    $data = getOrderDetail($id);
    $base = '/DoAn/DoAnTotNghiep/public/';
    if (!$data) {
        return '
            <div class="text-center py-5">
                <h5 class="text-danger">Không tìm thấy đơn hàng</h5>
            </div>
        ';
    }

    $order = $data['order'];
    $items = $data['items'];

    ob_start();
    ?>

    <style>
        .od-wrap{
            font-size:14px;
        }

        .od-top{
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:20px;
            margin-bottom:20px;
        }

        .od-card{
            background:#f8f9fc;
            border-radius:12px;
            padding:16px;
            border:1px solid #eee;
        }

        .od-card h6{
            font-weight:700;
            margin-bottom:12px;
            color:#4e73df;
        }

        .od-info{
            margin-bottom:8px;
        }

        .od-info strong{
            color:#333;
            min-width:120px;
            display:inline-block;
        }

        .od-items{
            margin-top:10px;
        }

        .od-item{
            display:flex;
            gap:15px;
            padding:15px 0;
            border-bottom:1px solid #eee;
        }

        .od-item:last-child{
            border-bottom:none;
        }

        .od-thumb{
            width:80px;
            height:80px;
            border-radius:10px;
            overflow:hidden;
            background:#f2f2f2;
            flex-shrink:0;
        }

        .od-thumb img{
            width:100%;
            height:100%;
            object-fit:cover;
        }

        .od-content{
            flex:1;
        }

        .od-name{
            font-weight:700;
            margin-bottom:5px;
        }

        .od-variant{
            font-size:13px;
            color:#777;
        }

        .od-price{
            color:#e74a3b;
            font-weight:700;
            margin-top:5px;
        }

        .od-topping{
            font-size:13px;
            color:#666;
            margin-top:4px;
        }

        .od-total{
            text-align:right;
            margin-top:20px;
            padding-top:15px;
            border-top:2px dashed #ddd;
        }

        .od-total h4{
            color:#e74a3b;
            font-weight:700;
        }

        @media(max-width:768px){
            .od-top{
                grid-template-columns:1fr;
            }
        }
    </style>

    <div class="od-wrap">

        <!-- TOP INFO -->
        <div class="od-top">

            <!-- ORDER INFO -->
            <div class="od-card">

                <h6>Thông tin đơn hàng</h6>

                <div class="od-info">
                    <strong>Mã đơn:</strong>
                    <?= htmlspecialchars($order['order_code']) ?>
                </div>

                <div class="od-info">
                    <strong>Ngày đặt:</strong>
                    <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                </div>

                <div class="od-info">
                    <strong>Trạng thái:</strong>
                    <?= htmlspecialchars($order['status']) ?>
                </div>

                <div class="od-info">
                    <strong>Thanh toán:</strong>
                    <?= htmlspecialchars($order['payment_status'] ?? 'pending') ?>
                </div>

            </div>

            <!-- CUSTOMER -->
            <div class="od-card">

                <h6>Thông tin khách hàng</h6>

                <div class="od-info">
                    <strong>Khách hàng:</strong>
                    <?= htmlspecialchars($order['full_name']) ?>
                </div>

                <div class="od-info">
                    <strong>Số điện thoại:</strong>
                    <?= htmlspecialchars($order['phone']) ?>
                </div>

                <div class="od-info">
                    <strong>Địa chỉ:</strong>
                    <?= htmlspecialchars($order['address']) ?>
                </div>

            </div>

        </div>

        <!-- ITEMS -->
        <div class="od-card">

            <h6>Danh sách món ăn</h6>

            <div class="od-items">

                <?php foreach ($items as $item): ?>

                    <div class="od-item">

                        <!-- IMAGE -->
                        <div class="od-thumb">

                            <?php
                            $img = !empty($item['product_image'])
                                ? $base . 'assets/img/product/' . $item['product_image']
                                : $base . 'assets/img/product/product-default.png';
                            ?>

                            <img src="<?= $img ?>" alt="<?= htmlspecialchars($item['product_name']) ?>">

                        </div>

                        <!-- CONTENT -->
                        <div class="od-content">

                            <div class="od-name">
                                <?= htmlspecialchars($item['product_name']) ?>
                            </div>

                            <?php if (!empty($item['variant_name'])): ?>
                                <div class="od-variant">
                                    Phân loại:
                                    <?= htmlspecialchars($item['variant_name']) ?>
                                </div>
                            <?php endif; ?>

                            <div>
                                Số lượng:
                                <strong><?= $item['quantity'] ?></strong>
                            </div>

                            <?php if (!empty($item['toppings'])): ?>

                                <div class="od-topping">

                                    <strong>Topping:</strong>

                                    <?php foreach ($item['toppings'] as $top): ?>

                                        <div>
                                            + <?= htmlspecialchars($top['name']) ?>
                                            (+<?= number_format($top['price']) ?>đ)
                                        </div>

                                    <?php endforeach; ?>

                                </div>

                            <?php endif; ?>

                            <div class="od-price">
                                <?= number_format($item['subtotal']) ?>đ
                            </div>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

            <!-- TOTAL -->
            <div class="od-total">

                <div>
                    Tạm tính:
                    <?= number_format($order['total_amount'] ?? 0) ?>đ
                </div>

                <div>
                    Phí ship:
                    <?= number_format($order['shipping_fee'] ?? 0) ?>đ
                </div>

                <div>
                    Giảm giá:
                    <?= number_format($order['discount_amount'] ?? 0) ?>đ
                </div>

                <h4>
                    Tổng cộng:
                    <?= number_format($order['final_amount']) ?>đ
                </h4>

            </div>

        </div>

    </div>

    <?php

    return ob_get_clean();
}

/**
 * HỦY ĐƠN
 */
function cancelOrder($order_id)
{
    $conn = getDB();

    try {

        $stmt = $conn->prepare("
            UPDATE orders
            SET
                status = 'cancelled',
                delivery_status = 'failed',
                cancelled_at = NOW(),
                updated_at = NOW()
            WHERE id = ?
        ");

        $stmt->execute([$order_id]);

        return ['success' => true];

    } catch (Exception $e) {

        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}
