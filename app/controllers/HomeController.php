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
    $product['reviews'] = getReviewsByProductId($id);
    $avg = getAverageRating($id);
    $product['avg_rating'] = $avg['avg_rating'] ?? 0;
    $product['total_reviews'] = $avg['total_reviews'] ?? 0;

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
 * Lấy sản phẩm có lọc, phân trang (hỗ trợ lọc category)
 */
function getFilteredProducts($page = 1, $limit = 10, $filters = []) {
    $conn = getDB();
    $offset = ($page - 1) * $limit;

    $sql = "SELECT p.*, COALESCE(AVG(r.rating), 0) as rating
            FROM products p
            LEFT JOIN reviews r ON p.id = r.product_id
            WHERE p.status = 1";

    $params = [];

    // ===== PRICE =====
    if (!empty($filters['min_price'])) {
        $sql .= " AND p.base_price >= :min_price";
        $params[':min_price'] = (float)$filters['min_price'];
    }

    if (!empty($filters['max_price'])) {
        $sql .= " AND p.base_price <= :max_price";
        $params[':max_price'] = (float)$filters['max_price'];
    }

    // ===== SIZE =====
    if (!empty($filters['size'])) {
        $sql .= " AND EXISTS (
            SELECT 1 FROM product_variants pv
            WHERE pv.product_id = p.id
            AND pv.variant_name = :size
        )";
        $params[':size'] = $filters['size'];
    }

    // ===== CATEGORY =====
    if (!empty($filters['category'])) {
        $sql .= " AND p.category_id = :category";
        $params[':category'] = (int)$filters['category'];
    }

    // ===== 🔥 SEARCH (FIX CHUẨN) =====
    if (!empty($filters['keyword'])) {
        $sql .= " AND p.name LIKE :keyword";
        $params[':keyword'] = '%' . $filters['keyword'] . '%';
    }
    // ===== GROUP =====
    $sql .= " GROUP BY p.id";

    // ===== SORT =====
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

    // ===== LIMIT =====
    $sql .= " LIMIT :limit OFFSET :offset";

    $stmt = $conn->prepare($sql);

    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Đếm tổng sản phẩm thỏa mãn bộ lọc
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
        $sql .= " AND EXISTS (
            SELECT 1 FROM product_variants pv
            WHERE pv.product_id = p.id
            AND pv.variant_name = ?
        )";
        $params[] = $filters['size'];
    }

    if (!empty($filters['category'])) {
        $sql .= " AND p.category_id = ?";
        $params[] = (int)$filters['category'];
    }

    // 🔥 FIX SEARCH
    if (!empty($filters['keyword'])) {
        $sql .= " AND LOWER(p.name) LIKE ?";
        $params[] = '%' . strtolower(trim($filters['keyword'])) . '%';
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchColumn();
}

function getAllVariants() {
    $conn = getDB();
    $stmt = $conn->query("SELECT DISTINCT variant_name FROM product_variants ORDER BY variant_name");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function getProductsByCategoryId($categoryId, $limit = 4) {
    $conn = getDB();
    $limit = (int)$limit;
    $sql = "
        SELECT id, name, image, base_price
        FROM products
        WHERE category_id = ? AND status = 1
        ORDER BY id DESC
        LIMIT " . $limit;
    $stmt = $conn->prepare($sql);
    $stmt->execute([$categoryId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ========== THÊM MỚI: LẤY SẢN PHẨM TƯƠNG TỰ ==========
function getSimilarProducts($productId, $categoryId, $limit = 4) {
    $conn = getDB();
    $limit = (int)$limit;
    $sql = "
        SELECT id, name, image, base_price,
               (SELECT COALESCE(AVG(rating), 0) FROM reviews WHERE product_id = p.id AND status = 1) as avg_rating
        FROM products p
        WHERE category_id = ? AND id != ? AND status = 1
        ORDER BY id DESC
        LIMIT " . $limit;
    $stmt = $conn->prepare($sql);
    $stmt->execute([$categoryId, $productId]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Đảm bảo mỗi sản phẩm có trường images (mảng)
    foreach ($products as &$prod) {
        $prod['images'] = [$prod['image']];
    }
    return $products;
}

// ========== THÊM MỚI: LẤY DANH SÁCH YÊU THÍCH CỦA USER ==========
function getUserFavorites($userId) {
    $conn = getDB();
    $stmt = $conn->prepare("SELECT product_id FROM favorites WHERE user_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// ========== THÊM MỚI: LẤY SẢN PHẨM YÊU THÍCH (KÈM THÔNG TIN) ==========
function getFavoriteProducts($userId, $limit = 10) {
    $conn = getDB();
    $limit = (int)$limit;
    $sql = "
        SELECT p.*, COALESCE(AVG(r.rating), 0) as rating
        FROM favorites f
        JOIN products p ON f.product_id = p.id
        LEFT JOIN reviews r ON p.id = r.product_id
        WHERE f.user_id = ? AND p.status = 1
        GROUP BY p.id
        ORDER BY f.created_at DESC
        LIMIT " . $limit;
    $stmt = $conn->prepare($sql);
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ========== THÊM MỚI: THÊM / XÓA YÊU THÍCH ==========
function toggleFavorite($userId, $productId) {
    $conn = getDB();
    // Kiểm tra đã tồn tại chưa
    $stmt = $conn->prepare("SELECT id FROM favorites WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$userId, $productId]);
    $exists = $stmt->fetch();
    if ($exists) {
        // Xóa yêu thích
        $stmt = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
        return ['status' => 'removed'];
    } else {
        // Thêm yêu thích
        $stmt = $conn->prepare("INSERT INTO favorites (user_id, product_id, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$userId, $productId]);
        return ['status' => 'liked'];
    }
}


function getAllCategoriesWithDepth() {
    $conn = getDB();
    $stmt = $conn->query("SELECT id, name, parent_id FROM categories ORDER BY COALESCE(parent_id, 0), id");
    $all = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Xây dựng cây
    $tree = buildCatTree($all);
    // Trải phẳng kèm depth
    $flat = [];
    flattenCatTree($tree, $flat, 0);
    return $flat;
}

function buildCatTree(array $elements, $parentId = null) {
    $branch = [];
    foreach ($elements as $el) {
        if ($el['parent_id'] == $parentId) {
            $children = buildCatTree($elements, $el['id']);
            if ($children) {
                $el['children'] = $children;
            }
            $branch[] = $el;
        }
    }
    return $branch;
}

function flattenCatTree(array $tree, array &$result, $depth) {
    foreach ($tree as $node) {
        $result[] = [
            'id'    => $node['id'],
            'name'  => $node['name'],
            'depth' => $depth,
        ];
        if (!empty($node['children'])) {
            flattenCatTree($node['children'], $result, $depth + 1);
        }
    }
}


function getParentCategories() {
    $conn = getDB();
    // Lấy id của danh mục gốc "Thực đơn"
    $stmt = $conn->prepare("SELECT id FROM categories WHERE slug = 'thuc-don'");
    $stmt->execute();
    $rootId = $stmt->fetchColumn();

    if (!$rootId) {
        // Nếu không có "Thực đơn", lấy tất cả các category có parent_id IS NULL (nếu dùng cách khác)
        $stmt = $conn->query("SELECT * FROM categories WHERE parent_id IS NULL ORDER BY sort_order");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy các danh mục con trực tiếp của "Thực đơn"
    $stmt = $conn->prepare("SELECT * FROM categories WHERE parent_id = ? ORDER BY sort_order");
    $stmt->execute([$rootId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// ================== THÊM MỚI: LẤY KHUYẾN MÃI ĐANG DIỄN RA ==================
function getActivePromotion() {
    $conn = getDB();

    $stmt = $conn->prepare("
        SELECT *
        FROM promotions
        WHERE status = 1
        AND NOW() BETWEEN start_date AND end_date
        ORDER BY id DESC
        LIMIT 1
    ");
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getPromotionProducts($promotion) {
    if (!$promotion) return [];

    $conn = getDB();

    // Lấy sản phẩm random để áp khuyến mãi
    $stmt = $conn->prepare("
        SELECT id, name, image, base_price
        FROM products
        WHERE status = 1
        ORDER BY RAND()
        LIMIT 8
    ");
    $stmt->execute();

    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Gán discount từ promotion
    foreach ($products as &$p) {
        $p['discount'] = $promotion['discount_percent'];
    }

    return $products;
}

?>
