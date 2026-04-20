<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../includes/db.php';

class SettingsController
{
    private $pdo;
    private $success = '';
    private $error = '';
    private $settings = [];

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->initTable();
        $this->handlePost();
        $this->loadSettings();
    }

    private function initTable()
    {
        try {
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS settings (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                setting_key VARCHAR(100) UNIQUE NOT NULL,
                setting_value TEXT,
                setting_type ENUM('text','textarea','image','number','email','phone') DEFAULT 'text',
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL
            )");
        } catch (PDOException $e) {
            // Có thể log lỗi
        }
    }

    private function handlePost()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action']) || $_POST['action'] !== 'update') {
            return;
        }

        $settings = $_POST['settings'] ?? [];
        foreach ($settings as $key => $value) {
            $value = trim($value);

            if ($key === 'logo' && isset($_FILES['logo_file']) && $_FILES['logo_file']['error'] === UPLOAD_ERR_OK) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $ext = strtolower(pathinfo($_FILES['logo_file']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed)) {
                    $this->error = "Định dạng ảnh logo không hợp lệ (cho phép jpg, png, gif, webp).";
                    continue;
                }

                $uploadDir = __DIR__ . '/../../../public/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $new_name = 'logo_' . uniqid() . '.' . $ext;
                $dest = $uploadDir . $new_name;

                if (move_uploaded_file($_FILES['logo_file']['tmp_name'], $dest)) {
                    $value = 'uploads/' . $new_name;
                    // Xóa logo cũ
                    $stmt = $this->pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'logo'");
                    $stmt->execute();
                    $old = $stmt->fetchColumn();
                    if ($old && file_exists(__DIR__ . '/../../../public/' . $old) && $old !== 'uploads/logo_default.png') {
                        unlink(__DIR__ . '/../../../public/' . $old);
                    }
                } else {
                    $this->error = "Upload logo thất bại.";
                    continue;
                }
            }

            if (empty($this->error)) {
                $stmt = $this->pdo->prepare("INSERT INTO settings (setting_key, setting_value, updated_at)
                                             VALUES (?, ?, NOW())
                                             ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()");
                $stmt->execute([$key, $value, $value]);
            }
        }

        if (empty($this->error)) {
            $this->success = "Cập nhật cài đặt thành công!";
        }
    }

    private function loadSettings()
    {
        $stmt = $this->pdo->query("SELECT setting_key, setting_value FROM settings");
        while ($row = $stmt->fetch()) {
            $this->settings[$row['setting_key']] = $row['setting_value'];
        }

        $defaults = [
            'site_name'          => 'FastFood Admin',
            'site_email'         => 'contact@fastfood.com',
            'site_phone'         => '1900xxxx',
            'site_address'       => '123 Đường Lê Lợi, Quận 1, TP.HCM',
            'tax_rate'           => '10',
            'shipping_fee'       => '15000',
            'logo'               => 'uploads/logo_default.png',
            'timezone'           => 'Asia/Ho_Chi_Minh',
            'currency'           => 'VND',
            'order_auto_confirm' => '0',
            'google_analytics'   => '',
            'facebook_pixel'     => ''
        ];

        foreach ($defaults as $key => $default) {
            if (!isset($this->settings[$key])) {
                $this->settings[$key] = $default;
            }
        }
    }

    public function getSettings()
    {
        return $this->settings;
    }

    public function getSuccess()
    {
        return $this->success;
    }

    public function getError()
    {
        return $this->error;
    }
}

// Khởi tạo controller
$controller = new SettingsController($pdo);
$settings = $controller->getSettings();
$success = $controller->getSuccess();
$error = $controller->getError();

// Load view
$view = __DIR__ . '/../views/settings/index.php';
require_once __DIR__ . '/../layouts/admin.php'; // layout admin chứa header, sidebar, footer, scripts
