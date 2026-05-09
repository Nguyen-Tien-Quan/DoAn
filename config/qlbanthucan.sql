-- =============================================
-- XÓA DATABASE CŨ NẾU CÓ VÀ TẠO MỚI
-- =============================================
DROP DATABASE IF EXISTS qlbthucan;
CREATE DATABASE qlbthucan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE qlbthucan;
  SELECT * FROM customers WHERE user_id = 3;
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
-- 4. CATEGORIES (đã thêm parent_id, sort_order)
-- =========================================
CREATE TABLE categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(150) UNIQUE NOT NULL,
    description TEXT,
    image VARCHAR(255),
    parent_id BIGINT UNSIGNED DEFAULT NULL,
    sort_order INT DEFAULT 0,
    status TINYINT DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
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

-- =========================================
-- 6. PRODUCT_VARIANTS (có tồn kho)
-- =========================================
CREATE TABLE product_variants (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED,
    variant_name VARCHAR(100),
    price DECIMAL(12,2),
    stock_quantity INT DEFAULT 0,
    status TINYINT DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- =========================================
-- 7. TOPPINGS (có tồn kho)
-- =========================================
CREATE TABLE toppings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    price DECIMAL(12,2),
    stock_quantity INT DEFAULT 0,
    status TINYINT DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
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
-- 11. SHIPPING_ADDRESSES (đặt trước orders)
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


ALTER TABLE shipping_addresses
ADD INDEX idx_user_id (user_id);
USE qlbthucan;
UPDATE orders
SET created_at = '2026-05-01'
WHERE id = 1;

UPDATE orders
SET created_at = '2026-05-02'
WHERE id = 2;

UPDATE orders
SET created_at = '2026-05-03'
WHERE id = 3;

UPDATE orders
SET created_at = '2026-05-04'
WHERE id = 4;

SELECT o.id, o.customer_id, c.id, c.full_name, c.phone
FROM orders o
LEFT JOIN customers c ON o.customer_id = c.id;
SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE shipping_addresses
DROP INDEX unique_user_default;

SET FOREIGN_KEY_CHECKS = 1;

ALTER TABLE shipping_addresses
DROP INDEX unique_user_default;


-- =========================================
-- 12. ORDERS (đã gộp shipper_id, shipping_address_id)
-- =========================================
CREATE TABLE orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_code VARCHAR(50) UNIQUE,
    customer_id BIGINT UNSIGNED,
    user_id BIGINT UNSIGNED,
    shipper_id BIGINT UNSIGNED NULL,
    order_type ENUM('delivery','pickup','dine_in'),
    payment_method ENUM('cash','momo','vnpay','card'),
    total_amount DECIMAL(12,2),
    discount_amount DECIMAL(12,2),
    shipping_fee DECIMAL(12,2),
    final_amount DECIMAL(12,2),
    status ENUM('pending','confirmed','preparing','ready_for_delivery','delivering','completed','cancelled') DEFAULT 'pending',
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
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (shipper_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (shipping_address_id) REFERENCES shipping_addresses(id)
);

-- =========================================
-- 13. ORDER_ITEMS
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
-- 14. ORDER_ITEM_TOPPINGS
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
-- 15. PAYMENTS
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
-- 16. VOUCHERS (có thời gian)
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
-- 17. VOUCHER_USAGE
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
-- 18. REVIEWS
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
-- 19. INGREDIENTS
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
-- 20. SUPPLIERS
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
-- 21. PURCHASE_ORDERS
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
-- 22. PURCHASE_ORDER_ITEMS
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
-- 23. NOTIFICATIONS
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
-- 24. PASSWORD_RESETS (có otp)
-- =========================================
CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255),
    token VARCHAR(255),
    expire_at DATETIME,
    otp VARCHAR(6)
);

-- =========================================
-- 25. FAVORITES
-- =========================================
CREATE TABLE favorites (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY user_product_unique (user_id, product_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================
-- 26. MENU_ITEMS
-- =========================================
CREATE TABLE menu_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parent_id BIGINT UNSIGNED DEFAULT 0,
    title VARCHAR(100) NOT NULL,
    url VARCHAR(255) NOT NULL,
    position ENUM('header', 'footer', 'sidebar') DEFAULT 'header',
    sort_order INT DEFAULT 0,
    status TINYINT DEFAULT 1,
    roles TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_parent (parent_id),
    INDEX idx_position (position),
    INDEX idx_status (status)
);

-- =========================================
-- 27. PROMOTIONS
-- =========================================
CREATE TABLE promotions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE,
    discount_percent INT DEFAULT 0,
    start_date DATETIME,
    end_date DATETIME,
    description TEXT,
    image VARCHAR(255),
    status TINYINT DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- =========================================
-- 28. SUPPORT_ARTICLES
-- =========================================
CREATE TABLE support_articles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    question VARCHAR(300) NOT NULL,
    answer TEXT NOT NULL,
    category VARCHAR(100),
    sort_order INT DEFAULT 0,
    status TINYINT DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- =========================================
-- 29. PAGES
-- =========================================
CREATE TABLE pages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    content LONGTEXT,
    image VARCHAR(255),
    meta_description VARCHAR(300),
    status TINYINT DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

USE qlbthucan;
-- =========================================
-- 30. Promotion Products (Sản phẩm khuyễn mãi)
-- =========================================
CREATE TABLE promotion_products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    promotion_id BIGINT UNSIGNED,
    product_id BIGINT UNSIGNED,

    discount_type ENUM('percent','fixed') DEFAULT 'percent',
    discount_value DECIMAL(12,2) DEFAULT 0,

    start_date DATETIME NULL,
    end_date DATETIME NULL,

    status TINYINT DEFAULT 1,

    FOREIGN KEY (promotion_id) REFERENCES promotions(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

ALTER TABLE products
ADD discount_percent INT DEFAULT 0,
ADD is_on_sale TINYINT DEFAULT 0,
ADD sold_count INT DEFAULT 0,
ADD final_price DECIMAL(12,2) NULL;

-- =========================================
-- DỮ LIỆU MẪU
-- =========================================

-- ROLES
INSERT INTO roles (id, name) VALUES
(1, 'admin'),
(2, 'staff'),
(3, 'customer'),
(4, 'shipper');

-- USERS: CHỈ 1 TÀI KHOẢN ADMIN
-- ⚠️ PHẢI THAY HASH THẬT CỦA MẬT KHẨU "123456" BÊN DƯỚI
-- Chạy lệnh sau trong terminal để lấy hash:
-- php -r "echo password_hash('123456', PASSWORD_BCRYPT) . PHP_EOL;"
-- Sau đó dán kết quả vào chỗ 'REPLACE_WITH_BCRYPT_HASH'
INSERT INTO users (id, role_id, name, email, password, phone, status) VALUES
(1, 1, 'Admin', 'admin@gmail.com', 'REPLACE_WITH_BCRYPT_HASH', '0123456789', 1);

-- CUSTOMER cho admin (để test order nếu cần)
INSERT INTO customers (id, user_id, full_name, phone, email, address) VALUES
(1, 1, 'Admin', '0123456789', 'admin@gmail.com', 'Hà Nội');

-- CATEGORIES: PHÂN CẤP
USE qlbthucan;
UPDATE categories
SET image = 'thucdon.png'
WHERE id = 5;

INSERT INTO categories (id, name, slug, description, image, parent_id, sort_order, status) VALUES
(5, 'Thực đơn', 'thuc-don', 'Tất cả các món ngon', 'thucdon.png', NULL, 0, 1),
(1, 'Burger', 'burger', 'Các loại burger', 'burger.png', 5, 1, 1),
(2, 'Gà rán', 'ga-ran', 'Gà rán giòn', 'ga-ran.png', 5, 2, 1),
(3, 'Đồ uống', 'do-uong', 'Nước giải khát', 'drink.png', 5, 3, 1),
(4, 'Combo', 'combo', 'Combo tiết kiệm', 'combo.png', 5, 4, 1),
(6, 'Burger bò', 'burger-bo', 'Burger nhân bò', NULL, 1, 1, 1),
(7, 'Burger gà', 'burger-ga', 'Burger nhân gà', NULL, 1, 2, 1),
(8, 'Nước ngọt', 'nuoc-ngot', 'Các loại nước ngọt có gas', NULL, 3, 1, 1),
(9, 'Trà & Cà phê', 'tra-cafe', 'Đồ uống nóng/lạnh', NULL, 3, 2, 1);


-- PRODUCTS
INSERT INTO products (id, category_id, name, slug, description, base_price, image, is_featured, status) VALUES
(1, 1, 'Burger bò', 'burger-bo', 'Burger bò thơm ngon', 50000, 'burger1.png', 1, 1),
(2, 1, 'Burger gà', 'burger-ga', 'Burger gà giòn', 45000, 'burger2.png', 1, 1),
(3, 2, 'Gà rán 1 miếng', 'ga-ran-1', 'Gà rán giòn rụm', 30000, 'ga1.png', 0, 1),
(4, 2, 'Gà rán 3 miếng', 'ga-ran-3', 'Combo gà 3 miếng', 85000, 'ga3.png', 1, 1),
(5, 3, 'Coca Cola', 'coca', 'Nước ngọt có gas', 15000, 'coca.png', 0, 1),
(6, 4, 'Combo Burger + Coca', 'combo-1', 'Combo tiết kiệm', 65000, 'combo1.png', 1, 1),
(7, 1, 'Burger tôm', 'burger-tom', 'Burger nhân tôm giòn rụm kèm sốt mayo', 55000, 'burger-tom.png', 1, 1),
(8, 1, 'Burger gà phô mai', 'burger-ga-pho-mai', 'Burger gà sốt phô mai tan chảy', 59000, 'burger-ga-pm.png', 1, 1),
(9, 2, 'Gà rán sốt cay', 'ga-ran-sot-cay', '2 miếng gà rán tẩm sốt cay Hàn Quốc', 42000, 'ga-cay.png', 0, 1),
(10,2, 'Cánh gà chiên nước mắm','canh-ga-mam','3 cánh gà chiên mắm đậm đà',48000,'canh-ga.png',0,1),
(11,3, 'Pepsi','pepsi','Nước ngọt Pepsi lon 330ml',15000,'pepsi.png',0,1),
(12,3, 'Trà đào miếng','tra-dao','Trà đào thanh mát kèm 2 miếng đào',25000,'tra-dao.png',1,1),
(13,4, 'Combo Gia đình','combo-gia-dinh','2 Burger, 2 Gà rán, 2 Coca lớn',185000,'combo-gd.png',1,1),
(14,4, 'Combo Trẻ em','combo-tre-em','1 Burger nhỏ, 1 Khoai tây chiên, 1 Milo',75000,'combo-kid.png',0,1),
(15,2, 'Khoai tây chiên','khoai-tay-chien','Khoai tây chiên size vừa',20000,'fries.png',0,1),
(16,4, 'Combo Độc thân','combo-doc-than','1 Burger bò, 1 Pepsi, 1 Khoai tây',79000,'combo-solo.png',1,1);

-- PRODUCT VARIANTS (size + tồn kho)
INSERT INTO product_variants (id, product_id, variant_name, price, stock_quantity) VALUES
(1, 1, 'S', 50000, 100),
(2, 1, 'M', 60000, 100),
(3, 2, 'S', 45000, 100),
(4, 2, 'L', 55000, 100),
(5, 5, 'M', 15000, 200),
(6, 7, 'S', 55000, 50),
(7, 7, 'M', 65000, 50),
(8, 8, 'S', 59000, 80),
(9, 8, 'M', 69000, 80);

-- Variant mặc định cho sản phẩm chưa có
INSERT INTO product_variants (product_id, variant_name, price, stock_quantity)
SELECT p.id, 'Mặc định', 0, 100
FROM products p
WHERE NOT EXISTS (
    SELECT 1 FROM product_variants pv WHERE pv.product_id = p.id
);

-- TOPPINGS
INSERT INTO toppings (id, name, price, stock_quantity) VALUES
(1, 'Phô mai', 5000, 200),
(2, 'Trứng', 7000, 150),
(3, 'Bacon', 10000, 100),
(4, 'Xúc xích', 8000, 120),
(5, 'Rau thêm', 3000, 500),
(6, 'Sốt cay', 4000, 300);

-- PRODUCT TOPPINGS
INSERT INTO product_toppings (product_id, topping_id) VALUES
(1,1),(1,2),(1,3),(1,6),
(2,1),(2,3),(2,6),
(7,1),(7,2),(7,3),(7,5),
(8,1),(8,2),(8,6);

-- MENU ITEMS (bỏ Blog)
INSERT INTO menu_items (parent_id, title, url, position, sort_order, status, created_at) VALUES
(0,'Khuyến mãi','index.php?url=promotion','header',1,1,NOW()),
(0,'Hỗ trợ','index.php?url=support','header',2,1,NOW()),
(0,'Giới thiệu','index.php?url=about','header',3,1,NOW()),
(0,'Liên hệ','index.php?url=contact','header',4,1,NOW());

-- PAGES (giới thiệu, liên hệ)
INSERT INTO pages (title, slug, content, image, meta_description, status, created_at) VALUES
('Giới thiệu','about','<p>TRQshop được thành lập năm 2023 với mong muốn cung cấp các sản phẩm burger, gà rán và đồ uống chất lượng cao, giá cả hợp lý.</p>','assets/img/about.jpg','TRQshop - Thương hiệu đồ ăn nhanh hàng đầu',1,NOW()),
('Liên hệ','contact','<p>Vui lòng liên hệ với chúng tôi qua form bên dưới hoặc thông tin cửa hàng.</p>',NULL,'Liên hệ TRQshop',1,NOW());

-- PROMOTIONS
INSERT INTO promotions (name, slug, discount_percent, start_date, end_date, description, image, status, created_at) VALUES
('Flash Sale tháng 5','flash-sale-thang-5',20,NOW(),DATE_ADD(NOW(), INTERVAL 30 DAY),'Giảm 20% toàn bộ burger và gà rán','assets/img/promo/sale.jpg',1,NOW());

-- SUPPORT ARTICLES
INSERT INTO support_articles (question, answer, category, sort_order, status, created_at) VALUES
('Làm thế nào để đặt hàng?','Bạn chọn sản phẩm, thêm vào giỏ, sau đó thanh toán. Hệ thống sẽ gửi email xác nhận.','Đặt hàng',1,1,NOW()),
('Phí vận chuyển tính thế nào?','Phí ship = 10.000đ cho đơn hàng dưới 200.000đ, miễn phí ship cho đơn từ 200.000đ.','Vận chuyển',2,1,NOW()),
('Chính sách đổi trả?','Đổi trả trong vòng 7 ngày nếu sản phẩm lỗi hoặc không đúng mô tả.','Chính sách',3,1,NOW());

-- ORDERS mẫu (sử dụng account admin)
INSERT INTO orders (id, order_code, customer_id, user_id, order_type, payment_method, total_amount, final_amount, status) VALUES
(1,'ORD001',1,1,'delivery','cash',100000,100000,'completed'),
(2,'ORD002',1,1,'pickup','momo',65000,65000,'pending');

-- ORDER_ITEMS
SET @default_variant_id = (SELECT id FROM product_variants WHERE product_id=6 AND variant_name='Mặc định');
INSERT INTO order_items (order_id, product_id, variant_id, quantity, unit_price, subtotal) VALUES
(1,1,1,2,50000,100000),
(2,6, @default_variant_id, 1, 65000, 65000);

-- PAYMENTS
INSERT INTO payments (order_id, payment_method, amount, payment_status) VALUES
(1,'cash',100000,'paid'),
(2,'momo',65000,'pending');

-- VOUCHERS
INSERT INTO vouchers (code, name, discount_type, discount_value, min_order_amount, start_date, end_date, usage_limit, status) VALUES
('SALE10', 'Giảm 10%', 'percent', 10, 50000, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), 100, 1),
('SALE20K', 'Giảm 20k', 'fixed', 20000, 100000, NOW(), DATE_ADD(NOW(), INTERVAL 15 DAY), 50, 1);

-- REVIEWS
INSERT INTO reviews (customer_id, product_id, rating, comment) VALUES
(1,1,5,'Rất ngon'),
(1,2,4,'Ổn áp');

-- INGREDIENTS
INSERT INTO ingredients (name, unit, stock_quantity, min_quantity) VALUES
('Thịt bò','kg',50,10),
('Bánh mì burger','cái',200,50),
('Phô mai','kg',20,5),
('Trứng','quả',100,20);

-- SUPPLIERS
INSERT INTO suppliers (name, phone, email, address) VALUES
('Công ty thực phẩm A','0901111111','a@supplier.com','Hà Nội'),
('Công ty thực phẩm B','0902222222','b@supplier.com','Hồ Chí Minh');

-- =========================================
-- GIẢ LẬP SỐ LƯỢNG ĐÃ BÁN
-- =========================================
UPDATE products SET sold_count = 120 WHERE id = 1;
UPDATE products SET sold_count = 95  WHERE id = 2;
UPDATE products SET sold_count = 60  WHERE id = 7;
UPDATE products SET sold_count = 80  WHERE id = 8;
UPDATE products SET sold_count = 45  WHERE id = 12;
UPDATE products SET sold_count = 150 WHERE id = 13;

-- =========================================
-- GIẢ LẬP SALE
-- =========================================
UPDATE products
SET is_on_sale = 1,
    discount_percent = 20
WHERE id IN (1,2,7,8,12,13,17,18);
-- =========================================
-- TÍNH GIÁ SAU KHI GIẢM
-- =========================================
UPDATE products
SET final_price = ROUND(base_price - (base_price * discount_percent / 100), 2)
WHERE is_on_sale = 1
AND id > 0;


-- =========================================
-- GÁN SẢN PHẨM VÀO KHUYẾN MÃI (PROMOTION ID = 1)
-- =========================================
INSERT INTO promotion_products (promotion_id, product_id, discount_type, discount_value, start_date, end_date, status)
VALUES
(1,1,'percent',20,NOW(),DATE_ADD(NOW(), INTERVAL 30 DAY),1),
(1,2,'percent',20,NOW(),DATE_ADD(NOW(), INTERVAL 30 DAY),1),
(1,7,'percent',20,NOW(),DATE_ADD(NOW(), INTERVAL 30 DAY),1),
(1,8,'percent',20,NOW(),DATE_ADD(NOW(), INTERVAL 30 DAY),1),
(1,12,'percent',20,NOW(),DATE_ADD(NOW(), INTERVAL 30 DAY),1),
(1,13,'percent',20,NOW(),DATE_ADD(NOW(), INTERVAL 30 DAY),1);

SET FOREIGN_KEY_CHECKS=1;
