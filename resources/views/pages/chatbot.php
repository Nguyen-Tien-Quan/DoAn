<?php
require_once __DIR__ . '/../../../config/database.php';

// Kiểm tra biến $db có tồn tại không
if (!isset($db) || $db === null) {
    // Fallback nếu không kết nối được
    echo json_encode([
        'success' => false,
        'reply' => '❌ Lỗi kết nối database. Vui lòng thử lại sau.'
    ]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$message = trim($input['message'] ?? '');

if (empty($message)) {
    echo json_encode([
        'success' => false,
        'reply' => 'Bạn muốn hỏi gì ạ?'
    ]);
    exit;
}

// Tìm trong FAQ
$stmt = $db->prepare("SELECT answer FROM support_articles
                     WHERE status = 1
                     AND (question LIKE ? OR answer LIKE ?)
                     LIMIT 1");
$search = "%$message%";
$stmt->execute([$search, $search]);
$faq = $stmt->fetch(PDO::FETCH_ASSOC);

if ($faq) {
    $reply = $faq['answer'];
} else {
    $reply = "Mình chưa có câu trả lời chính xác cho câu hỏi này. Bạn có thể gọi hotline 1900 1234 hoặc để lại email để nhân viên hỗ trợ nhé!";
}

echo json_encode(['reply' => $reply]);
