USE QLBTAN;

-- =========================================
-- 1. ROLES - Phân quyền hệ thống
-- =========================================

CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, -- ID quyền
    name VARCHAR(50) UNIQUE NOT NULL, -- Tên quyền: admin, staff, customer
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- =========================================
-- 2. USERS - Tài khoản đăng nhập
-- =========================================
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, -- ID user
    role_id BIGINT UNSIGNED NOT NULL, -- Liên kết roles (xác định quyền)
    name VARCHAR(100) NOT NULL, -- Tên người dùng
    email VARCHAR(100) UNIQUE, -- Email đăng nhập
    password VARCHAR(255) NOT NULL, -- Mật khẩu
    phone VARCHAR(20), -- Số điện thoại
    avatar VARCHAR(255), -- Ảnh đại diện
    status TINYINT DEFAULT 1, -- 1: hoạt động, 0: khóa
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

ALTER TABLE users
ADD COLUMN remember_token VARCHAR(255) DEFAULT NULL AFTER password;
-- =========================================
-- 3. CUSTOMERS - Thông tin khách hàng
-- =========================================
CREATE TABLE customers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, -- ID khách
    user_id BIGINT UNSIGNED UNIQUE, -- Liên kết user (có thể null nếu không đăng nhập)
    full_name VARCHAR(100), -- Tên khách
    phone VARCHAR(20), -- SĐT
    email VARCHAR(100),
    address TEXT, -- Địa chỉ giao hàng
    gender ENUM('male','female','other'),
    birthday DATE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);



-- =========================================
-- 4. CATEGORIES - Danh mục món ăn
-- =========================================
CREATE TABLE categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100), -- Tên danh mục (Burger, Gà rán,...)
    slug VARCHAR(150) UNIQUE, -- Dùng cho URL
    description TEXT,
    image VARCHAR(255),
    status TINYINT DEFAULT 1, -- 1: hiển thị
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- =========================================
-- 5. PRODUCTS - Món ăn
-- =========================================
CREATE TABLE products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id BIGINT UNSIGNED, -- Thuộc danh mục nào
    name VARCHAR(150), -- Tên món
    slug VARCHAR(180) UNIQUE,
    description TEXT,
    base_price DECIMAL(12,2), -- Giá cơ bản
    image VARCHAR(255),
    is_featured TINYINT DEFAULT 0, -- Món nổi bật
    status TINYINT DEFAULT 1, -- 1: đang bán
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- =========================================
-- 6. PRODUCT_VARIANTS - Size / phiên bản
-- =========================================
CREATE TABLE product_variants (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED, -- Thuộc sản phẩm
    variant_name VARCHAR(100), -- Ví dụ: S, M, L
    price DECIMAL(12,2), -- Giá riêng
    stock_quantity INT DEFAULT 0, -- Tồn kho
    status TINYINT DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- =========================================
-- 7. TOPPINGS - Đồ thêm
-- =========================================
CREATE TABLE toppings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100), -- Tên topping (phô mai, trứng...)
    price DECIMAL(12,2), -- Giá thêm
    status TINYINT DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- =========================================
-- 8. PRODUCT_TOPPINGS - Gắn topping cho món
-- =========================================
CREATE TABLE product_toppings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED,
    topping_id BIGINT UNSIGNED,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (topping_id) REFERENCES toppings(id)
);

-- =========================================
-- 9. CARTS - Giỏ hàng
-- =========================================
CREATE TABLE carts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED, -- Giỏ của user nào
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- =========================================
-- 10. CART_ITEMS - Sản phẩm trong giỏ
-- =========================================
CREATE TABLE cart_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cart_id BIGINT UNSIGNED,
    product_id BIGINT UNSIGNED,
    variant_id BIGINT UNSIGNED, -- Size
    quantity INT DEFAULT 1, -- Số lượng
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (cart_id) REFERENCES carts(id),
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (variant_id) REFERENCES product_variants(id)
);

-- =========================================
-- 11. ORDERS - Đơn hàng
-- =========================================
CREATE TABLE orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_code VARCHAR(50) UNIQUE, -- Mã đơn
    customer_id BIGINT UNSIGNED, -- Khách hàng
    user_id BIGINT UNSIGNED, -- Nhân viên xử lý
    order_type ENUM('delivery','pickup','dine_in'),
    payment_method ENUM('cash','momo','vnpay','card'),
    total_amount DECIMAL(12,2), -- Tổng tiền gốc
    discount_amount DECIMAL(12,2), -- Giảm giá
    shipping_fee DECIMAL(12,2), -- Phí ship
    final_amount DECIMAL(12,2), -- Tiền cuối
    status ENUM('pending','confirmed','preparing','delivering','completed','cancelled') DEFAULT 'pending',
    delivery_address TEXT,
    note TEXT,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- =========================================
-- 12. ORDER_ITEMS - Chi tiết món trong đơn
-- =========================================
CREATE TABLE order_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED,
    product_id BIGINT UNSIGNED,
    variant_id BIGINT UNSIGNED,
    quantity INT,
    unit_price DECIMAL(12,2), -- Giá 1 món
    topping_price DECIMAL(12,2), -- Giá topping
    subtotal DECIMAL(12,2), -- Tổng món
    note TEXT,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (variant_id) REFERENCES product_variants(id)
);

-- =========================================
-- 13. ORDER_ITEM_TOPPINGS - Topping của món
-- =========================================
CREATE TABLE order_item_toppings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_item_id BIGINT UNSIGNED,
    topping_id BIGINT UNSIGNED,
    price DECIMAL(12,2), -- Giá topping
    FOREIGN KEY (order_item_id) REFERENCES order_items(id),
    FOREIGN KEY (topping_id) REFERENCES toppings(id)
);

-- =========================================
-- 14. PAYMENTS - Thanh toán
-- =========================================
CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED UNIQUE,
    payment_method ENUM('cash','momo','vnpay','card'),
    amount DECIMAL(12,2), -- Số tiền trả
    payment_status ENUM('pending','paid','failed','refunded'),
    transaction_code VARCHAR(100),
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id)
);

-- =========================================
-- 15. VOUCHERS - Mã giảm giá
-- =========================================
CREATE TABLE vouchers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE, -- Mã
    name VARCHAR(100),
    discount_type ENUM('percent','fixed'),
    discount_value DECIMAL(12,2),
    min_order_amount DECIMAL(12,2),
    max_discount_amount DECIMAL(12,2),
    start_date DATETIME,
    end_date DATETIME,
    usage_limit INT,
    used_count INT DEFAULT 0,
    status TINYINT DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- =========================================
-- 16. VOUCHER_USAGE - Lịch sử dùng mã
-- =========================================
CREATE TABLE voucher_usage (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    voucher_id BIGINT UNSIGNED,
    customer_id BIGINT UNSIGNED,
    order_id BIGINT UNSIGNED,
    used_at TIMESTAMP,
    FOREIGN KEY (voucher_id) REFERENCES vouchers(id),
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (order_id) REFERENCES orders(id)
);

-- =========================================
-- 17. REVIEWS - Đánh giá
-- =========================================
CREATE TABLE reviews (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED,
    product_id BIGINT UNSIGNED,
    rating INT, -- số sao
    comment TEXT,
    status TINYINT DEFAULT 1, -- ẩn/hiện
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

ALTER TABLE reviews ADD images TEXT NULL;
USE QLBTAN;
ALTER TABLE reviews 
ADD likes INT DEFAULT 0;

-- =========================================
-- 18. INGREDIENTS - Nguyên liệu
-- =========================================
CREATE TABLE ingredients (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100), -- Tên nguyên liệu
    unit VARCHAR(20), -- kg, cái...
    stock_quantity INT, -- tồn kho
    min_quantity INT, -- cảnh báo hết hàng
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- =========================================
-- 19. SUPPLIERS - Nhà cung cấp
-- =========================================
CREATE TABLE suppliers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- =========================================
-- 20. PURCHASE_ORDERS - Phiếu nhập hàng
-- =========================================
CREATE TABLE purchase_orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    supplier_id BIGINT UNSIGNED,
    user_id BIGINT UNSIGNED,
    total_amount DECIMAL(12,2),
    status TINYINT,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- =========================================
-- 21. PURCHASE_ORDER_ITEMS - Chi tiết nhập
-- =========================================
CREATE TABLE purchase_order_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    purchase_order_id BIGINT UNSIGNED,
    ingredient_id BIGINT UNSIGNED,
    quantity INT,
    unit_price DECIMAL(12,2),
    subtotal DECIMAL(12,2),
    FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id),
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(id)
);

-- =========================================
-- 22. NOTIFICATIONS - Thông báo
-- =========================================
CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED,
    title VARCHAR(150),
    content TEXT,
    is_read TINYINT DEFAULT 0, -- đã đọc hay chưa
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255),
    token VARCHAR(255),
    expire_at DATETIME
);

CREATE TABLE `favorites` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `product_id` BIGINT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `user_product_unique` (`user_id`, `product_id`),
    CONSTRAINT `fk_favorites_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_favorites_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
USE qlbtan;
ALTER TABLE orders
ADD shipping_address_id INT,
ADD receiver_name VARCHAR(255),
ADD receiver_phone VARCHAR(20),
ADD payment_status ENUM('pending','paid','failed','refunded') DEFAULT 'pending',
ADD delivery_status ENUM('pending','shipping','delivered','failed') DEFAULT 'pending',
ADD confirmed_at TIMESTAMP NULL,
ADD shipped_at TIMESTAMP NULL,
ADD completed_at TIMESTAMP NULL,
ADD cancelled_at TIMESTAMP NULL;

CREATE TABLE shipping_addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    full_name VARCHAR(255),
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    is_default TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE shipping_addresses
ADD CONSTRAINT fk_shipping_user
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

ALTER TABLE favorites 
ADD UNIQUE KEY unique_user_product (user_id, product_id);
SET FOREIGN_KEY_CHECKS=0;

-- =============================
-- ROLES
-- =============================
INSERT INTO roles (id, name) VALUES
(1, 'admin'),
(2, 'staff'),
(3, 'customer');

-- =============================
-- USERS (password = 123456)
-- =============================
INSERT INTO users (id, role_id, name, email, password, phone, status) VALUES
(1, 1, 'Admin', 'admin@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9Q0p1R0tJ3Q0Q0Q0Q0Q0Q', '0123456789', 1),
(2, 3, 'Nguyen Van A', 'a@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9Q0p1R0tJ3Q0Q0Q0Q0Q0Q', '0987654321', 1),
(3, 3, 'Tran Thi B', 'b@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9Q0p1R0tJ3Q0Q0Q0Q0Q0Q', '0977777777', 1);

-- =============================
-- CUSTOMERS
-- =============================
INSERT INTO customers (id, user_id, full_name, phone, email, address) VALUES
(1, 2, 'Nguyen Van A', '0987654321', 'a@gmail.com', 'Ha Noi'),
(2, 3, 'Tran Thi B', '0977777777', 'b@gmail.com', 'Ha Noi');

-- =============================
-- CATEGORIES
-- =============================
INSERT INTO categories (id, name, slug, description, status) VALUES
(1, 'Burger', 'burger', 'Các loại burger', 1),
(2, 'Gà rán', 'ga-ran', 'Gà rán giòn', 1),
(3, 'Đồ uống', 'do-uong', 'Nước giải khát', 1),
(4, 'Combo', 'combo', 'Combo tiết kiệm', 1);

-- =============================
-- PRODUCTS
-- =============================
INSERT INTO products (id, category_id, name, slug, description, base_price, image, is_featured, status) VALUES
(1, 1, 'Burger bò', 'burger-bo', 'Burger bò thơm ngon', 50000, 'burger1.png', 1, 1),
(2, 1, 'Burger gà', 'burger-ga', 'Burger gà giòn', 45000, 'burger2.png', 1, 1),
(3, 2, 'Gà rán 1 miếng', 'ga-ran-1', 'Gà rán giòn rụm', 30000, 'ga1.png', 0, 1),
(4, 2, 'Gà rán 3 miếng', 'ga-ran-3', 'Combo gà 3 miếng', 85000, 'ga3.png', 1, 1),
(5, 3, 'Coca Cola', 'coca', 'Nước ngọt có gas', 15000, 'coca.png', 0, 1),
(6, 4, 'Combo Burger + Coca', 'combo-1', 'Combo tiết kiệm', 65000, 'combo1.png', 1, 1),
(7, 1, 'Burger tôm', 'burger-tom', 'Burger nhân tôm giòn rụm kèm sốt mayo', 55000.00, 'burger-tom.png', 1, 1),
(8, 1, 'Burger gà phô mai', 'burger-ga-pho-mai', 'Burger gà sốt phô mai tan chảy', 59000.00, 'burger-ga-pm.png', 1, 1),
(9, 2, 'Gà rán sốt cay', 'ga-ran-sot-cay', '2 miếng gà rán tẩm sốt cay Hàn Quốc', 42000.00, 'ga-cay.png', 0, 1),
(10, 2, 'Cánh gà chiên nước mắm', 'canh-ga-mam', '3 cánh gà chiên mắm đậm đà', 48000.00, 'canh-ga.png', 0, 1),
(11, 3, 'Pepsi', 'pepsi', 'Nước ngọt Pepsi lon 330ml', 15000.00, 'pepsi.png', 0, 1),
(12, 3, 'Trà đào miếng', 'tra-dao', 'Trà đào thanh mát kèm 2 miếng đào', 25000.00, 'tra-dao.png', 1, 1),
(13, 4, 'Combo Gia đình', 'combo-gia-dinh', '2 Burger, 2 Gà rán, 2 Coca lớn', 185000.00, 'combo-gd.png', 1, 1),
(14, 4, 'Combo Trẻ em', 'combo-tre-em', '1 Burger nhỏ, 1 Khoai tây chiên, 1 Milo', 75000.00, 'combo-kid.png', 0, 1),
(15, 2, 'Khoai tây chiên', 'khoai-tay-chien', 'Khoai tây chiên size vừa', 20000.00, 'fries.png', 0, 1),
(16,4, 'Combo Độc thân', 'combo-doc-than', '1 Burger bò, 1 Pepsi, 1 Khoai tây', 79000.00, 'combo-solo.png', 1, 1);

-- =============================
-- PRODUCT VARIANTS
-- =============================
INSERT INTO product_variants (id, product_id, variant_name, price, stock_quantity) VALUES
(1, 1, 'S', 50000, 100),
(2, 1, 'M', 60000, 100),
(3, 2, 'S', 45000, 100),
(4, 2, 'L', 55000, 100),
(5, 5, 'M', 15000, 200);

-- =============================
-- TOPPINGS
-- =============================
INSERT INTO toppings (id, name, price) VALUES
(1, 'Phô mai', 5000),
(2, 'Trứng', 7000),
(3, 'Bacon', 10000);

-- =============================
-- PRODUCT TOPPINGS
-- =============================
INSERT INTO product_toppings (product_id, topping_id) VALUES
(1, 1),
(1, 2),
(2, 1),
(2, 3);

-- =============================
-- CARTS
-- =============================
INSERT INTO carts (id, user_id) VALUES
(3, 4),
(1, 2),
(2, 3);


-- =============================
-- CART ITEMS
-- =============================
INSERT INTO cart_items (cart_id, product_id, variant_id, quantity) VALUES
(1, 1, 1, 2),
(1, 5, 5, 1),
(2, 2, 3, 1),
(3, 2, 3, 1);

-- =============================
-- ORDERS
-- =============================
INSERT INTO orders (id, order_code, customer_id, order_type, payment_method, total_amount, final_amount, status) VALUES
(1, 'ORD001', 1, 'delivery', 'cash', 100000, 100000, 'completed'),
(2, 'ORD002', 2, 'pickup', 'momo', 65000, 65000, 'pending');

-- =============================
-- ORDER ITEMS
-- =============================
INSERT INTO order_items (order_id, product_id, variant_id, quantity, unit_price, subtotal) VALUES
(1, 1, 1, 2, 50000, 100000),
(2, 6, NULL, 1, 65000, 65000);

-- =============================
-- PAYMENTS
-- =============================
INSERT INTO payments (order_id, payment_method, amount, payment_status) VALUES
(1, 'cash', 100000, 'paid'),
(2, 'momo', 65000, 'pending');

-- =============================
-- VOUCHERS
-- =============================
INSERT INTO vouchers (code, name, discount_type, discount_value, min_order_amount) VALUES
('SALE10', 'Giảm 10%', 'percent', 10, 50000),
('SALE20K', 'Giảm 20k', 'fixed', 20000, 100000);

-- =============================
-- REVIEWS
-- =============================
INSERT INTO reviews (customer_id, product_id, rating, comment) VALUES
(1, 1, 5, 'Rất ngon'),
(2, 2, 4, 'Ổn áp');


SET FOREIGN_KEY_CHECKS=1;

