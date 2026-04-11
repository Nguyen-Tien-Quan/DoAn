<?php
require_once __DIR__ . '/../../../config/database.php';

function getUsers() {
    $pdo = getDB();
    return $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
}

function handleDeleteUser() {
    $pdo = getDB();

    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];

        $pdo->prepare("UPDATE users SET status = 0 WHERE id = ?")->execute([$id]);

        $_SESSION['success'] = "Đã khóa user";
    }

    header("Location: admin.php?url=users");
    exit;
}
