<?php
// app/controllers/FavoritesController.php

// ===== SESSION =====
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ===== DB =====
require_once __DIR__ . '/../../config/database.php';
$conn = getDB();

// ===== USER =====
$user = $_SESSION['user'] ?? null;

/**
 * Lấy danh sách favorite
 */
function getFavorites() {
    global $user, $conn;

    if (!$user) return [];

    $stmt = $conn->prepare("
        SELECT p.id, p.name, p.base_price, p.image, f.id AS fav_id
        FROM favorites f
        JOIN products p ON f.product_id = p.id
        WHERE f.user_id = ?
    ");
    $stmt->execute([$user['id']]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Kiểm tra product đã được like chưa
 */
function isFavorite($productId) {
    global $user, $conn;

    if (!$user) return false;

    $stmt = $conn->prepare("
        SELECT id FROM favorites
        WHERE user_id = ? AND product_id = ?
    ");
    $stmt->execute([$user['id'], $productId]);

    return $stmt->fetch() ? true : false;
}

/**
 * Thêm vào favorite (CHỐNG DUPLICATE)
 */
function addFavorite($productId) {
    global $user, $conn;

    if (!$user || !$productId) return false;

    // 🔥 CHỐNG DUPLICATE CHẮC CHẮN
    $stmt = $conn->prepare("
        SELECT id FROM favorites
        WHERE user_id = ? AND product_id = ?
        LIMIT 1
    ");
    $stmt->execute([$user['id'], $productId]);

    if ($stmt->fetch()) {
        return false; // đã tồn tại
    }

    // Insert
    $stmt = $conn->prepare("
        INSERT INTO favorites (user_id, product_id)
        VALUES (?, ?)
    ");

    return $stmt->execute([$user['id'], $productId]);
}

/**
 * Xóa favorite theo fav_id
 */
function removeFavorite($favId) {
    global $user, $conn;

    if (!$user || !$favId) return false;

    $stmt = $conn->prepare("
        DELETE FROM favorites
        WHERE id = ? AND user_id = ?
    ");

    return $stmt->execute([$favId, $user['id']]);
}

/**
 * Xóa favorite theo product_id (dùng cho AJAX toggle)
 */
function removeFavoriteByProduct($productId) {
    global $user, $conn;

    if (!$user || !$productId) return false;

    $stmt = $conn->prepare("
        DELETE FROM favorites
        WHERE product_id = ? AND user_id = ?
    ");

    return $stmt->execute([$productId, $user['id']]);
}

/**
 * Đếm số lượng favorite
 */
function favoriteCount() {
    global $user, $conn;

    if (!$user) return 0;

    $stmt = $conn->prepare("
        SELECT COUNT(*) as cnt
        FROM favorites
        WHERE user_id = ?
    ");
    $stmt->execute([$user['id']]);

    return $stmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;
}

/**
 * Lấy danh sách product_id đã like (🔥 QUAN TRỌNG cho đổi màu tim)
 */
function getFavoriteIds() {
    global $user, $conn;

    if (!$user) return [];

    $stmt = $conn->prepare("
        SELECT product_id
        FROM favorites
        WHERE user_id = ?
    ");
    $stmt->execute([$user['id']]);

    return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'product_id');
}

/**
 * Lấy N sản phẩm gần nhất (mini dropdown)
 */
function getRecentFavorites($limit = 3) {
    global $user, $conn;

    if (!$user) return [];

    $stmt = $conn->prepare("
        SELECT p.id, p.name, p.base_price, p.image, f.id AS fav_id
        FROM favorites f
        JOIN products p ON f.product_id = p.id
        WHERE f.user_id = ?
        ORDER BY f.id DESC
        LIMIT $limit
    ");
    $stmt->execute([$user['id']]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
