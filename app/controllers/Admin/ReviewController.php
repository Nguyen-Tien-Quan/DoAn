<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| GET REVIEWS
|--------------------------------------------------------------------------
*/
function getReviews($page = 1, $limit = 15, $filters = [])
{
    $conn = getDB();

    $offset = ($page - 1) * $limit;

    $search     = trim($filters['search'] ?? '');
    $product_id = (int)($filters['product_id'] ?? 0);
    $rating     = (int)($filters['rating'] ?? 0);
    $status     = isset($filters['status'])
        ? (int)$filters['status']
        : -1;

    $where  = " WHERE 1=1 ";
    $params = [];

    // Search
    if (!empty($search)) {

        $where .= "
            AND (
                c.full_name LIKE ?
                OR c.email LIKE ?
                OR p.name LIKE ?
                OR r.comment LIKE ?
            )
        ";

        $keyword = "%$search%";

        $params[] = $keyword;
        $params[] = $keyword;
        $params[] = $keyword;
        $params[] = $keyword;
    }

    // Product
    if ($product_id > 0) {
        $where .= " AND r.product_id = ? ";
        $params[] = $product_id;
    }

    // Rating
    if ($rating > 0) {
        $where .= " AND r.rating = ? ";
        $params[] = $rating;
    }

    // Status
    if ($status != -1) {
        $where .= " AND r.status = ? ";
        $params[] = $status;
    }

    /*
    |--------------------------------------------------------------------------
    | COUNT
    |--------------------------------------------------------------------------
    */
    $countSql = "
        SELECT COUNT(*)
        FROM reviews r
        LEFT JOIN customers c ON r.customer_id = c.id
        LEFT JOIN products p ON r.product_id = p.id
        $where
    ";

    $stmt = $conn->prepare($countSql);
    $stmt->execute($params);

    $total = $stmt->fetchColumn();

    /*
    |--------------------------------------------------------------------------
    | DATA
    |--------------------------------------------------------------------------
    */
    $dataSql = "
        SELECT
            r.*,
            c.full_name AS customer_name,
            c.email,
            c.phone,
            p.name AS product_name
        FROM reviews r
        LEFT JOIN customers c ON r.customer_id = c.id
        LEFT JOIN products p ON r.product_id = p.id
        $where
        ORDER BY r.created_at DESC
        LIMIT :limit OFFSET :offset
    ";

    $stmt = $conn->prepare($dataSql);

    // Bind params thường
    foreach ($params as $index => $value) {

        $type = PDO::PARAM_STR;

        if (is_int($value)) {
            $type = PDO::PARAM_INT;
        }

        $stmt->bindValue($index + 1, $value, $type);
    }

    // Bind limit offset
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

    $stmt->execute();

    return [
        'data'  => $stmt->fetchAll(PDO::FETCH_ASSOC),
        'total' => $total
    ];
}

/*
|--------------------------------------------------------------------------
| GET PRODUCTS FOR FILTER
|--------------------------------------------------------------------------
*/
function getProductsForFilter()
{
    $conn = getDB();

    $stmt = $conn->query("
        SELECT id, name
        FROM products
        ORDER BY name ASC
    ");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/*
|--------------------------------------------------------------------------
| UPDATE REVIEW STATUS
|--------------------------------------------------------------------------
*/
function updateReviewStatus($reviewId, $status)
{
    $conn = getDB();

    $stmt = $conn->prepare("
        UPDATE reviews
        SET status = ?,
            updated_at = NOW()
        WHERE id = ?
    ");

    return $stmt->execute([
        (int)$status,
        (int)$reviewId
    ]);
}

/*
|--------------------------------------------------------------------------
| DELETE REVIEW
|--------------------------------------------------------------------------
*/
function deleteReview($reviewId)
{
    $conn = getDB();

    $stmt = $conn->prepare("
        DELETE FROM reviews
        WHERE id = ?
    ");

    return $stmt->execute([
        (int)$reviewId
    ]);
}

/*
|--------------------------------------------------------------------------
| HANDLE REVIEW ACTIONS
|--------------------------------------------------------------------------
*/
function handleReviewActions()
{
    $success = null;
    $error   = null;

    /*
    |--------------------------------------------------------------------------
    | TOGGLE STATUS
    |--------------------------------------------------------------------------
    */
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $action = $_POST['action'] ?? '';

        if ($action === 'toggle_status') {

            $reviewId      = (int)($_POST['review_id'] ?? 0);
            $currentStatus = (int)($_POST['current_status'] ?? 0);

            $newStatus = $currentStatus == 1 ? 0 : 1;

            if ($reviewId > 0) {

                if (updateReviewStatus($reviewId, $newStatus)) {

                    $success = $newStatus == 1
                        ? 'Đã hiển thị đánh giá'
                        : 'Đã ẩn đánh giá';

                } else {

                    $error = 'Không thể cập nhật trạng thái đánh giá';
                }
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | HIDE REVIEW
    |--------------------------------------------------------------------------
    */
    if (isset($_GET['hide'])) {

        $reviewId = (int)$_GET['hide'];

        if ($reviewId > 0) {

            if (updateReviewStatus($reviewId, 0)) {
                $success = 'Đã ẩn đánh giá';
            } else {
                $error = 'Ẩn đánh giá thất bại';
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW REVIEW
    |--------------------------------------------------------------------------
    */
    if (isset($_GET['show'])) {

        $reviewId = (int)$_GET['show'];

        if ($reviewId > 0) {

            if (updateReviewStatus($reviewId, 1)) {
                $success = 'Đã hiển thị lại đánh giá';
            } else {
                $error = 'Khôi phục đánh giá thất bại';
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE REVIEW
    |--------------------------------------------------------------------------
    */
    if (isset($_GET['delete'])) {

        $reviewId = (int)$_GET['delete'];

        if ($reviewId > 0) {

            if (deleteReview($reviewId)) {
                $success = 'Đã xóa đánh giá';
            } else {
                $error = 'Xóa đánh giá thất bại';
            }
        }
    }

    return [
        'success' => $success,
        'error'   => $error
    ];
}

/*
|--------------------------------------------------------------------------
| ADMIN REVIEW PAGE DATA
|--------------------------------------------------------------------------
*/
function getAdminReviewPageData()
{
    // Handle action trước
    $actionResult = handleReviewActions();

    // Filter
    $page = max(1, (int)($_GET['page'] ?? 1));

    $filters = [
        'search'     => $_GET['search'] ?? '',
        'product_id' => (int)($_GET['product_id'] ?? 0),
        'rating'     => (int)($_GET['rating'] ?? 0),
        'status'     => isset($_GET['status'])
            ? (int)$_GET['status']
            : -1
    ];

    // Data
    $reviewData = getReviews($page, 15, $filters);

    $totalReviews = $reviewData['total'];

    $totalPages = ceil($totalReviews / 15);

    return [

        // reviews
        'reviews' => $reviewData['data'],

        // products
        'products' => getProductsForFilter(),

        // pagination
        'page'       => $page,
        'totalPages' => $totalPages,

        // filters
        'search'         => $filters['search'],
        'product_filter' => $filters['product_id'],
        'rating_filter'  => $filters['rating'],
        'status_filter'  => $filters['status'],

        // messages
        'success' => $actionResult['success'],
        'error'   => $actionResult['error']
    ];
}
