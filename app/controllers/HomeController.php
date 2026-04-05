<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';

function pagination() {
    $conn = getDB();

    $limit = 10;
    $page = $_GET['page'] ?? 1;
    $page = max(1, (int)$page);

    $total = (int)$conn->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $totalPages = max(1, ceil($total / $limit));
    if ($page > $totalPages) $page = $totalPages;

    $offset = ($page - 1) * $limit;

    $stmt = $conn->prepare("SELECT * FROM products ORDER BY id ASC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    // Trả dữ liệu về router
    return [
        'products' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        'page' => $page,
        'totalPages' => $totalPages,
        'favIds' => []
    ];
}

function getProducts() {
    $conn = getDB();

    $stmt = $conn->query("SELECT * FROM products");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $products;
}

function getProductById($id) {
    $conn = getDB();

    $stmt = $conn->prepare("
        SELECT p.*, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getReviewsByProductId($productId) {
    $conn = getDB();

    $stmt = $conn->prepare("
        SELECT r.*, c.full_name, u.avatar
        FROM reviews r
        LEFT JOIN customers c ON r.customer_id = c.id
        LEFT JOIN users u ON c.user_id = u.id
        WHERE r.product_id = ? AND r.status = 1
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$productId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAverageRating($productId) {
    $conn = getDB();

    $stmt = $conn->prepare("
        SELECT
            ROUND(AVG(rating), 1) as avg_rating,
            COUNT(*) as total_reviews
        FROM reviews
        WHERE product_id = ? AND status = 1
    ");
    $stmt->execute([$productId]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function addReview() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // ❌ Chưa login
    if (!isset($_SESSION['user'])) {
        header("Location: index.php?url=login");
        exit;
    }

    $conn = getDB();

    $product_id = $_POST['product_id'] ?? 0;
    $rating = $_POST['rating'] ?? 5;
    $comment = trim($_POST['comment'] ?? '');

    if (!$product_id || !$comment) {
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    // ================================
    // 🔥 LẤY / TẠO CUSTOMER
    // ================================
    $stmt = $conn->prepare("SELECT id FROM customers WHERE user_id = ?");
    $stmt->execute([$_SESSION['user']['id']]);
    $customer = $stmt->fetch();

    if (!$customer) {
        // 👉 Auto tạo nếu chưa có
        $stmt = $conn->prepare("
            INSERT INTO customers (user_id, created_at)
            VALUES (?, NOW())
        ");
        $stmt->execute([$_SESSION['user']['id']]);

        $customer_id = $conn->lastInsertId();
    } else {
        $customer_id = $customer['id'];
    }

    // ================================
    // 📸 UPLOAD ẢNH
    // ================================
    $images = [];

    if (!empty($_FILES['images']['name'][0])) {

        // Tạo folder nếu chưa có
        if (!is_dir("uploads/review")) {
            mkdir("uploads/review", 0777, true);
        }

        foreach ($_FILES['images']['tmp_name'] as $key => $tmp) {

            if ($_FILES['images']['error'][$key] === 0) {

                $ext = strtolower(pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION));

                // 👉 Chỉ cho phép ảnh
                $allow = ['jpg','jpeg','png','webp'];
                if (!in_array($ext, $allow)) continue;

                $name = time() . '_' . $key . '.' . $ext;

                if (move_uploaded_file($tmp, "uploads/review/" . $name)) {
                    $images[] = $name;
                }
            }
        }
    }

    $imageString = implode(',', $images);

    // ================================
    // 💾 INSERT REVIEW
    // ================================
    $stmt = $conn->prepare("
        INSERT INTO reviews (customer_id, product_id, rating, comment, images, likes, created_at)
        VALUES (?, ?, ?, ?, ?, 0, NOW())
    ");

    $stmt->execute([
        $customer_id,
        $product_id,
        $rating,
        $comment,
        $imageString
    ]);

    // ================================
    // 🔄 REDIRECT
    // ================================
    header("Location: index.php?url=product&id=" . $product_id);
    exit;
}
