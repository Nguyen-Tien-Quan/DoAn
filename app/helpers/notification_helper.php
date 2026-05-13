<?php

// Tạo thông báo mới
function createNotification($userId, $title, $content) {

    $conn = getDB();

    $stmt = $conn->prepare("
        INSERT INTO notifications
        (
            user_id,
            title,
            content,
            is_read,
            created_at,
            updated_at
        )
        VALUES
        (
            ?,
            ?,
            ?,
            0,
            NOW(),
            NOW()
        )
    ");

    return $stmt->execute([
        $userId,
        $title,
        $content
    ]);
}
