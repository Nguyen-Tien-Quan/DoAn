<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


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
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) return null;

    $product['variants'] = getVariantsByProductId($id);
    $product['toppings'] = getToppingsByProductId($id);

    return $product;
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

function getVariantsByProductId($productId) {
    $conn = getDB();
    // Chỉ lấy variant thuộc về sản phẩm này
    $stmt = $conn->prepare("
        SELECT * FROM product_variants
        WHERE product_id = ? AND status = 1
    ");
    $stmt->execute([$productId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function getToppingsByProductId($productId) {
    $conn = getDB();

    $stmt = $conn->prepare("
        SELECT t.*
        FROM toppings t
        JOIN product_toppings pt ON t.id = pt.topping_id
        WHERE pt.product_id = ? AND t.status = 1
    ");
    $stmt->execute([$productId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getVariantById($id) {
    $conn = getDB();

    $stmt = $conn->prepare("
        SELECT id, variant_name, price
        FROM product_variants
        WHERE id = ?
    ");
    $stmt->execute([$id]);
    $variant = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($variant) {
        $variant['name'] = $variant['variant_name'];
    }
    return $variant;
}

function getToppingById($id) {
    $conn = getDB();

    $stmt = $conn->prepare("
        SELECT * FROM toppings
        WHERE id = ? AND status = 1
    ");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function addReview() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

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

    // Lấy hoặc tạo customer
    $stmt = $conn->prepare("SELECT id FROM customers WHERE user_id = ?");
    $stmt->execute([$_SESSION['user']['id']]);
    $customer = $stmt->fetch();

    if (!$customer) {
        $stmt = $conn->prepare("
            INSERT INTO customers (user_id, created_at)
            VALUES (?, NOW())
        ");
        $stmt->execute([$_SESSION['user']['id']]);
        $customer_id = $conn->lastInsertId();
    } else {
        $customer_id = $customer['id'];
    }

    // Upload ảnh
    $images = [];
    if (!empty($_FILES['images']['name'][0])) {
        if (!is_dir("uploads/review")) {
            mkdir("uploads/review", 0777, true);
        }
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp) {
            if ($_FILES['images']['error'][$key] === 0) {
                $ext = strtolower(pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION));
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

    // Insert review
    $stmt = $conn->prepare("
        INSERT INTO reviews (customer_id, product_id, rating, comment, images, likes, status, created_at)
        VALUES (?, ?, ?, ?, ?, 0, 1, NOW())
    ");
    $stmt->execute([$customer_id, $product_id, $rating, $comment, $imageString]);

    header("Location: index.php?url=product&id=" . $product_id);
    exit;
}

function getCategories() {
    $conn = getDB();
    $stmt = $conn->query("SELECT * FROM categories ORDER BY id ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Lấy sản phẩm có lọc, phân trang (đã hỗ trợ lọc category)
 */
function getFilteredProducts($page = 1, $limit = 10, $filters = []) {
    $conn = getDB();
    $offset = ($page - 1) * $limit;
    $limit = (int)$limit;
    $offset = (int)$offset;

    $sql = "SELECT p.*, COALESCE(AVG(r.rating), 0) as rating
            FROM products p
            LEFT JOIN reviews r ON p.id = r.product_id
            WHERE p.status = 1";
    $params = [];

    if (!empty($filters['min_price'])) {
        $sql .= " AND p.base_price >= :min_price";
        $params[':min_price'] = (float)$filters['min_price'];
    }
    if (!empty($filters['max_price'])) {
        $sql .= " AND p.base_price <= :max_price";
        $params[':max_price'] = (float)$filters['max_price'];
    }
    if (!empty($filters['size'])) {
        $sql .= " AND EXISTS (SELECT 1 FROM product_variants pv WHERE pv.product_id = p.id AND pv.variant_name = :size)";
        $params[':size'] = $filters['size'];
    }
    if (!empty($filters['category'])) {
        $sql .= " AND p.category_id = :category";
        $params[':category'] = (int)$filters['category'];
    }
    if (!empty($filters['keyword'])) {
        $sql .= " AND p.name LIKE :keyword";
        $params[':keyword'] = '%' . $filters['keyword'] . '%';
    }

    $sql .= " GROUP BY p.id";

    if (!empty($filters['sort'])) {
        if ($filters['sort'] === 'price_asc') {
            $sql .= " ORDER BY p.base_price ASC";
        } elseif ($filters['sort'] === 'price_desc') {
            $sql .= " ORDER BY p.base_price DESC";
        } else {
            $sql .= " ORDER BY p.id DESC";
        }
    } else {
        $sql .= " ORDER BY p.id DESC";
    }

    $sql .= " LIMIT :limit OFFSET :offset";

    $stmt = $conn->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Đếm tổng sản phẩm thỏa mãn bộ lọc (đã hỗ trợ category)
 */
function countFilteredProducts($filters = []) {
    $conn = getDB();
    $sql = "SELECT COUNT(DISTINCT p.id) as total
            FROM products p
            WHERE p.status = 1";
    $params = [];

    if (!empty($filters['min_price'])) {
        $sql .= " AND p.base_price >= ?";
        $params[] = (float)$filters['min_price'];
    }
    if (!empty($filters['max_price'])) {
        $sql .= " AND p.base_price <= ?";
        $params[] = (float)$filters['max_price'];
    }
    if (!empty($filters['size'])) {
        $sql .= " AND EXISTS (SELECT 1 FROM product_variants pv WHERE pv.product_id = p.id AND pv.variant_name = ?)";
        $params[] = $filters['size'];
    }
    if (!empty($filters['category'])) {
        $sql .= " AND p.category_id = ?";
        $params[] = (int)$filters['category'];
    }
    if (!empty($filters['keyword'])) {
        $sql .= " AND p.name LIKE ?";
        $params[] = '%' . $filters['keyword'] . '%';
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['total'] ?? 0;
}

function getAllVariants() {
    $conn = getDB();
    $stmt = $conn->query("SELECT DISTINCT variant_name FROM product_variants ORDER BY variant_name");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>
