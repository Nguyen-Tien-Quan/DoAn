
USE QlBANTHUCAN;

SET FOREIGN_KEY_CHECKS=0;
SET time_zone = '+07:00';

-- =========================================
-- 1. ROLES
-- =========================================
CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- =========================================
-- 2. USERS
-- =========================================
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    avatar VARCHAR(255),
    status TINYINT DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    remember_token VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- =========================================
-- 3. CUSTOMERS
-- =========================================
CREATE TABLE customers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED UNIQUE,
    full_name VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    gender ENUM('male','female','other'),
    birthday DATE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- =========================================
-- 4. CATEGORIES
-- =========================================
CREATE TABLE categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    slug VARCHAR(150) UNIQUE,
    description TEXT,
    image VARCHAR(255),
    status TINYINT DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- =========================================
-- 5. PRODUCTS
-- =========================================
CREATE TABLE products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id BIGINT UNSIGNED,
    name VARCHAR(150),
    slug VARCHAR(180) UNIQUE,
    description TEXT,
    base_price DECIMAL(12,2),
    image VARCHAR(255),
    is_featured TINYINT DEFAULT 0,
    status TINYINT DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);


-- 1. Thêm cột stock_quantity vào bảng products (nếu chưa có)
ALTER TABLE products ADD COLUMN stock_quantity INT DEFAULT 0 AFTER image;

USE QlBANTHUCAN;
SET SQL_SAFE_UPDATES = 0;
UPDATE products SET stock_quantity = 999;

-- 3. Tạo bảng order_statuses (lịch sử trạng thái đơn hàng)
CREATE TABLE IF NOT EXISTS order_statuses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL COMMENT 'Mã đơn hàng',
    status ENUM('pending','confirmed','preparing','delivering','completed','cancelled') NOT NULL COMMENT 'Trạng thái',
    note TEXT COMMENT 'Ghi chú',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Thời gian thay đổi',
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET SQL_SAFE_UPDATES = 1;

-- =========================================
-- 6. PRODUCT_VARIANTS (FIX)
-- =========================================
CREATE TABLE product_variants (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NULL,
    variant_name VARCHAR(100),
    price DECIMAL(12,2),
    stock_quantity INT DEFAULT 0,
    status TINYINT DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- =========================================
-- 7. TOPPINGS (FIX)
-- =========================================
CREATE TABLE toppings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NULL,
    name VARCHAR(100),
    price DECIMAL(12,2),
    status TINYINT DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- =========================================
-- 8. PRODUCT_TOPPINGS
-- =========================================
CREATE TABLE product_toppings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED,
    topping_id BIGINT UNSIGNED,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (topping_id) REFERENCES toppings(id)
);

-- =========================================
-- 9. CARTS
-- =========================================
CREATE TABLE carts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- =========================================
-- 10. CART_ITEMS
-- =========================================
CREATE TABLE cart_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cart_id BIGINT UNSIGNED,
    product_id BIGINT UNSIGNED,
    variant_id BIGINT UNSIGNED,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (cart_id) REFERENCES carts(id),
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (variant_id) REFERENCES product_variants(id)
);

-- =========================================
-- 11. ORDERS
-- =========================================
CREATE TABLE orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_code VARCHAR(50) UNIQUE,
    customer_id BIGINT UNSIGNED,
    user_id BIGINT UNSIGNED,
    order_type ENUM('delivery','pickup','dine_in'),
    payment_method ENUM('cash','momo','vnpay','card'),
    total_amount DECIMAL(12,2),
    discount_amount DECIMAL(12,2),
    shipping_fee DECIMAL(12,2),
    final_amount DECIMAL(12,2),
    status ENUM('pending','confirmed','preparing','delivering','completed','cancelled') DEFAULT 'pending',
    delivery_address TEXT,
    note TEXT,
    shipping_address_id INT,
    receiver_name VARCHAR(255),
    receiver_phone VARCHAR(20),
    payment_status ENUM('pending','paid','failed','refunded') DEFAULT 'pending',
    delivery_status ENUM('pending','shipping','delivered','failed') DEFAULT 'pending',
    confirmed_at TIMESTAMP NULL,
    shipped_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    cancelled_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- =========================================
-- 12. ORDER_ITEMS
-- =========================================
CREATE TABLE order_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED,
    product_id BIGINT UNSIGNED,
    variant_id BIGINT UNSIGNED,
    quantity INT,
    unit_price DECIMAL(12,2),
    topping_price DECIMAL(12,2),
    subtotal DECIMAL(12,2),
    note TEXT,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (variant_id) REFERENCES product_variants(id)
);

-- =========================================
-- 13. ORDER_ITEM_TOPPINGS
-- =========================================
CREATE TABLE order_item_toppings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_item_id BIGINT UNSIGNED,
    topping_id BIGINT UNSIGNED,
    price DECIMAL(12,2),
    FOREIGN KEY (order_item_id) REFERENCES order_items(id),
    FOREIGN KEY (topping_id) REFERENCES toppings(id)
);

-- =========================================
-- 14. PAYMENTS
-- =========================================
CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED UNIQUE,
    payment_method ENUM('cash','momo','vnpay','card'),
    amount DECIMAL(12,2),
    payment_status ENUM('pending','paid','failed','refunded'),
    transaction_code VARCHAR(100),
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id)
);

-- =========================================
-- 15. VOUCHERS
-- =========================================
CREATE TABLE vouchers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE,
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
-- 16. VOUCHER_USAGE
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
-- 17. REVIEWS
-- =========================================
CREATE TABLE reviews (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED,
    product_id BIGINT UNSIGNED,
    rating INT,
    comment TEXT,
    images TEXT NULL,
    likes INT DEFAULT 0,
    status TINYINT DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- =========================================
-- 18. ingredients
-- =========================================
CREATE TABLE ingredients (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    unit VARCHAR(20),
    stock_quantity INT,
    min_quantity INT,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- =========================================
-- 19. suppliers
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
-- 20. purchase_orders
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
-- 21. purchase_order_items
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
-- 22. notifications
-- =========================================
CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED,
    title VARCHAR(150),
    content TEXT,
    is_read TINYINT DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- =========================================
-- 23. password_resets
-- =========================================
CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255),
    token VARCHAR(255),
    expire_at DATETIME
);

-- =========================================
-- 24. shipping_addresses
-- =========================================
CREATE TABLE shipping_addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    full_name VARCHAR(255),
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    is_default TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =========================================
-- 25. favorites
-- =========================================
CREATE TABLE favorites (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_product (user_id, product_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

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

USE QlBANTHUCAN;
UPDATE categories SET image = 'burger.png' WHERE id = 1;
UPDATE categories SET image = 'ga-ran.png' WHERE id = 2;
UPDATE categories SET image = 'drink.png' WHERE id = 3;
UPDATE categories SET image = 'combo.png' WHERE id = 4;

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

-- =========================================
-- VARIANTS (SIZE DÙNG CHUNG)
-- =========================================
INSERT INTO product_variants (product_id, variant_name, price, stock_quantity) VALUES
(NULL, 'S', 0, 999),
(NULL, 'M', 10000, 999),
(NULL, 'L', 20000, 999),
(NULL, 'XL', 30000, 999);

-- =========================================
-- TOPPINGS (DÙNG CHUNG)
-- =========================================
INSERT INTO toppings (product_id, name, price) VALUES
(NULL, 'Phô mai', 5000),
(NULL, 'Trứng', 7000),
(NULL, 'Bacon', 10000),
(NULL, 'Xúc xích', 8000),
(NULL, 'Rau thêm', 3000),
(NULL, 'Sốt cay', 4000);

-- =========================================
-- MAP TOPPING CHO PRODUCT (OPTIONAL)
-- =========================================
INSERT INTO product_toppings (product_id, topping_id) VALUES
(1,1),(1,2),(1,3),(1,6), -- burger bò
(2,1),(2,3),(2,6),       -- burger gà
(7,1),(7,2),(7,3),(7,5), -- burger tôm
(8,1),(8,2),(8,6);       -- burger phô mai

-- =========================================
-- CART TEST
-- =========================================
INSERT INTO carts (id, user_id) VALUES
(10, 2);

INSERT INTO cart_items (cart_id, product_id, variant_id, quantity) VALUES
(10, 1, 1, 2), -- burger bò size S
(10, 2, 2, 1), -- burger gà size M
(10, 5, 1, 3); -- coca

-- =========================================
-- ORDER TEST
-- =========================================
INSERT INTO orders (
    id, order_code, customer_id, order_type, payment_method,
    total_amount, discount_amount, shipping_fee, final_amount, status
) VALUES
(10, 'ORD_TEST_01', 1, 'delivery', 'cash', 150000, 0, 10000, 160000, 'pending');

INSERT INTO order_items (
    order_id, product_id, variant_id, quantity, unit_price, topping_price, subtotal
) VALUES
(10, 1, 1, 2, 50000, 5000, 105000),
(10, 2, 2, 1, 45000, 0, 45000);

-- =========================================
-- ORDER TOPPING
-- =========================================
INSERT INTO order_item_toppings (order_item_id, topping_id, price) VALUES
(1, 1, 5000),
(1, 3, 10000);

-- =========================================
-- SHIPPING ADDRESS
-- =========================================
INSERT INTO shipping_addresses (user_id, full_name, phone, address, city, is_default) VALUES
(2, 'Nguyen Van A', '0987654321', '123 Hoan Kiem', 'Ha Noi', 1);

-- =========================================
-- FAVORITES
-- =========================================
INSERT INTO favorites (user_id, product_id) VALUES
(2,1),
(2,2),
(3,3);

-- =========================================
-- NOTIFICATIONS
-- =========================================
INSERT INTO notifications (user_id, title, content) VALUES
(4, 'Đặt hàng thành công', 'Đơn hàng ORD_TEST_01 đã được tạo'),
(4, 'Khuyến mãi', 'Giảm 20% toàn bộ burger hôm nay');

-- =========================================
-- INGREDIENTS
-- =========================================
INSERT INTO ingredients (name, unit, stock_quantity, min_quantity) VALUES
('Thịt bò', 'kg', 50, 10),
('Bánh mì burger', 'cái', 200, 50),
('Phô mai', 'kg', 20, 5),
('Trứng', 'quả', 100, 20);

-- =========================================
-- SUPPLIERS
-- =========================================
INSERT INTO suppliers (name, phone, email, address) VALUES
('Công ty thực phẩm A', '0901111111', 'a@supplier.com', 'Hà Nội'),
('Công ty thực phẩm B', '0902222222', 'b@supplier.com', 'Hồ Chí Minh');

-- =========================================
-- PURCHASE ORDER
-- =========================================
INSERT INTO purchase_orders (supplier_id, user_id, total_amount, status) VALUES
(1, 4, 5000000, 1);

INSERT INTO purchase_order_items (purchase_order_id, ingredient_id, quantity, unit_price, subtotal) VALUES
(1, 1, 20, 200000, 4000000),
(1, 2, 100, 10000, 1000000);

USE QlBANTHUCAN;
INSERT INTO vouchers (code, name, discount_type, discount_value, max_discount_amount, min_order_amount, start_date, end_date, usage_limit, used_count, status)
VALUES ('GIAM20K', 'Giảm 20K', 'fixed', 20000, 0, 100000, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), 50, 0, 1);

SET FOREIGN_KEY_CHECKS=1;