<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';

// ==================== CÁC HÀM XỬ LÝ DB ====================


// Lấy danh sách thông báo của user
function getNotifications($userId, $limit = 20) {
    $conn = getDB();
    $stmt = $conn->prepare("
        SELECT * FROM notifications
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT ?
    ");
    $stmt->bindValue(1, $userId, PDO::PARAM_INT);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// Đếm số thông báo chưa đọc của user
function countUnreadNotifications($userId) {
    $conn = getDB();
    $stmt = $conn->prepare("
        SELECT COUNT(*) FROM notifications
        WHERE user_id = ? AND is_read = 0
    ");
    $stmt->execute([$userId]);
    return (int)$stmt->fetchColumn();
}

// Đánh dấu 1 thông báo là đã đọc
function markNotificationAsRead($notiId, $userId) {
    $conn = getDB();
    $stmt = $conn->prepare("
        UPDATE notifications SET is_read = 1
        WHERE id = ? AND user_id = ?
    ");
    return $stmt->execute([$notiId, $userId]);
}

// Đánh dấu tất cả thông báo của user là đã đọc
function markAllNotificationsAsRead($userId) {
    $conn = getDB();
    $stmt = $conn->prepare("
        UPDATE notifications SET is_read = 1
        WHERE user_id = ? AND is_read = 0
    ");
    return $stmt->execute([$userId]);
}

// ==================== XỬ LÝ API (JSON) ====================

// Xử lý API cho phần notification (dùng cho header)
function handleNotificationApi() {
    header('Content-Type: application/json');
    if (!isset($_SESSION['user'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Vui lòng đăng nhập']);
        exit;
    }

    $userId = $_SESSION['user']['id'];
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';

    if ($method === 'POST') {
        if ($action === 'mark-read' && isset($_GET['id'])) {
            markNotificationAsRead($_GET['id'], $userId);
            echo json_encode(['success' => true]);
        } elseif ($action === 'mark-all-read') {
            markAllNotificationsAsRead($userId);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Action không hợp lệ']);
        }
        exit;
    }

    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
    $notifications = getNotifications($userId, $limit);
    $unreadCount = countUnreadNotifications($userId);

    if ($action === 'count') {
        echo json_encode(['unread' => $unreadCount]);
        exit;
    }

    echo json_encode([
        'unread' => $unreadCount,
        'items' => $notifications
    ]);
    exit;
}

// ==================== HIỂN THỊ TRANG DANH SÁCH ====================


// Hiển thị trang danh sách thông báo
function showNotificationsPage() {
    if (!isset($_SESSION['user'])) {
        header("Location: index.php?url=login");
        exit;
    }

    $userId = $_SESSION['user']['id'];
    $notifications = getNotifications($userId, 100);
    $unreadCount = countUnreadNotifications($userId);

    // Gán vào biến toàn cục để view có thể truy cập (giống cách các controller khác đang dùng)
    $GLOBALS['notifications'] = $notifications;
    $GLOBALS['unreadCount'] = $unreadCount;

    // Trả về đường dẫn tuyệt đối đến file view
    return __DIR__ . '/../../resources/views/pages/notifications.php';
}

?>
