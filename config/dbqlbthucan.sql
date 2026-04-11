-- =============================================
-- XÓA DATABASE CŨ NẾU CÓ VÀ TẠO MỚI
-- =============================================
DROP DATABASE IF EXISTS qlbthucan;
CREATE DATABASE qlbthucan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE qlbthucan;

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
-- 11. ORDERS (đã thêm khóa ngoại shipping_address_id ngay trong lệnh CREATE)
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
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (shipping_address_id) REFERENCES shipping_addresses(id)
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
-- 15. VOUCHERS (có thời gian)
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
-- 18. INGREDIENTS
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
-- 19. SUPPLIERS
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
-- 20. PURCHASE_ORDERS
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
-- 21. PURCHASE_ORDER_ITEMS
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
-- 22. NOTIFICATIONS
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
-- 23. PASSWORD_RESETS (có otp)
-- =========================================
CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255),
    token VARCHAR(255),
    expire_at DATETIME,
    otp VARCHAR(6)
);

-- =========================================
-- 24. FAVORITES
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
-- 25. SHIPPING_ADDRESSES
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
-- 27. BLOG_POSTS
-- =========================================
CREATE TABLE blog_posts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    excerpt TEXT,
    content LONGTEXT,
    image VARCHAR(255),
    category VARCHAR(100),
    views INT DEFAULT 0,
    status TINYINT DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- =========================================
-- 28. PROMOTIONS
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
-- 29. SUPPORT_ARTICLES
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
-- 30. PAGES
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

-- =========================================
-- DỮ LIỆU MẪU
-- =========================================

-- ROLES
INSERT INTO roles (id, name) VALUES
(1, 'admin'),
(2, 'staff'),
(3, 'customer');

-- USERS (password = 123456)
INSERT INTO users (id, role_id, name, email, password, phone, status) VALUES
(1, 1, 'Admin', 'admin@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9Q0p1R0tJ3Q0Q0Q0Q0Q0Q', '0123456789', 1),
(2, 3, 'Nguyen Van A', 'a@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9Q0p1R0tJ3Q0Q0Q0Q0Q0Q', '0987654321', 1),
(3, 3, 'Tran Thi B', 'b@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9Q0p1R0tJ3Q0Q0Q0Q0Q0Q', '0977777777', 1);

-- CUSTOMERS
INSERT INTO customers (id, user_id, full_name, phone, email, address) VALUES
(1, 2, 'Nguyen Van A', '0987654321', 'a@gmail.com', 'Ha Noi'),
(2, 3, 'Tran Thi B', '0977777777', 'b@gmail.com', 'Ha Noi');

-- CATEGORIES (có ảnh luôn)
INSERT INTO categories (id, name, slug, description, image, status) VALUES
(1, 'Burger', 'burger', 'Các loại burger', 'burger.png', 1),
(2, 'Gà rán', 'ga-ran', 'Gà rán giòn', 'ga-ran.png', 1),
(3, 'Đồ uống', 'do-uong', 'Nước giải khát', 'drink.png', 1),
(4, 'Combo', 'combo', 'Combo tiết kiệm', 'combo.png', 1);

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

-- PRODUCT VARIANTS (size và tồn kho)
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

-- BỔ SUNG VARIANT MẶC ĐỊNH CHO CÁC SẢN PHẨM CHƯA CÓ VARIANT
INSERT INTO product_variants (product_id, variant_name, price, stock_quantity)
SELECT p.id, 'Mặc định', 0, 100
FROM products p
WHERE NOT EXISTS (
    SELECT 1 FROM product_variants pv WHERE pv.product_id = p.id
);

-- TOPPINGS (có tồn kho)
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

-- CARTS
INSERT INTO carts (id, user_id) VALUES (1,2),(2,3),(3,4);
INSERT INTO cart_items (cart_id, product_id, variant_id, quantity) VALUES
(1,1,1,2),(1,5,5,1),(2,2,3,1),(3,2,3,1);

-- ORDERS
INSERT INTO orders (id, order_code, customer_id, order_type, payment_method, total_amount, final_amount, status) VALUES
(1,'ORD001',1,'delivery','cash',100000,100000,'completed'),
(2,'ORD002',2,'pickup','momo',65000,65000,'pending');

-- Lấy variant_id mặc định của sản phẩm 6 để dùng trong order_items
SET @default_variant_id = (SELECT id FROM product_variants WHERE product_id=6 AND variant_name='Mặc định');

INSERT INTO order_items (order_id, product_id, variant_id, quantity, unit_price, subtotal) VALUES
(1,1,1,2,50000,100000),
(2,6, @default_variant_id, 1, 65000, 65000);

INSERT INTO payments (order_id, payment_method, amount, payment_status) VALUES
(1,'cash',100000,'paid'),
(2,'momo',65000,'pending');

-- VOUCHERS (có thời gian)
INSERT INTO vouchers (code, name, discount_type, discount_value, min_order_amount, start_date, end_date, usage_limit, status) VALUES
('SALE10', 'Giảm 10%', 'percent', 10, 50000, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), 100, 1),
('SALE20K', 'Giảm 20k', 'fixed', 20000, 100000, NOW(), DATE_ADD(NOW(), INTERVAL 15 DAY), 50, 1);

-- REVIEWS
INSERT INTO reviews (customer_id, product_id, rating, comment) VALUES
(1,1,5,'Rất ngon'),
(2,2,4,'Ổn áp');

-- MENU ITEMS
INSERT INTO menu_items (parent_id, title, url, position, sort_order, status, created_at) VALUES
(0,'Khuyến mãi','index.php?url=promotion','header',1,1,NOW()),
(0,'Blog','index.php?url=blog','header',2,1,NOW()),
(0,'Hỗ trợ','index.php?url=support','header',3,1,NOW()),
(0,'Giới thiệu','index.php?url=about','header',4,1,NOW());

-- BLOG POSTS
INSERT INTO blog_posts (title, slug, excerpt, content, image, category, views, status, created_at) VALUES
('Cách làm burger bò phô mai tại nhà','cach-lam-burger-bo-pho-mai','Chỉ 15 phút với nguyên liệu đơn giản...','<p>Nội dung chi tiết...</p>','assets/img/blog/blog-1.jpg','Công thức',120,1,NOW()),
('Phân biệt Arabica và Robusta','phan-biet-arabica-robusta','Hương vị, độ caffeine và cách chọn...','<p>Nội dung chi tiết...</p>','assets/img/blog/blog-2.jpg','Kiến thức',85,1,NOW()),
('Top 5 loại trà đào được yêu thích nhất','top-5-tra-dao','Thanh mát, giải nhiệt – lựa chọn hàng đầu...','<p>Nội dung chi tiết...</p>','assets/img/blog/blog-3.jpg','Review',200,1,NOW());

-- PROMOTIONS
INSERT INTO promotions (name, slug, discount_percent, start_date, end_date, description, image, status, created_at) VALUES
('Flash Sale tháng 4','flash-sale-thang-4',20,NOW(),DATE_ADD(NOW(), INTERVAL 30 DAY),'Giảm 20% toàn bộ burger và gà rán','assets/img/promo/sale.jpg',1,NOW());

-- SUPPORT ARTICLES
INSERT INTO support_articles (question, answer, category, sort_order, status, created_at) VALUES
('Làm thế nào để đặt hàng?','Bạn chọn sản phẩm, thêm vào giỏ, sau đó thanh toán. Hệ thống sẽ gửi email xác nhận.','Đặt hàng',1,1,NOW()),
('Phí vận chuyển tính thế nào?','Phí ship = 10.000đ cho đơn hàng dưới 200.000đ, miễn phí ship cho đơn từ 200.000đ.','Vận chuyển',2,1,NOW()),
('Chính sách đổi trả?','Đổi trả trong vòng 7 ngày nếu sản phẩm lỗi hoặc không đúng mô tả.','Chính sách',3,1,NOW());

-- PAGES
INSERT INTO pages (title, slug, content, image, meta_description, status, created_at) VALUES
('Giới thiệu','about','<p>TRQshop được thành lập năm 2023 với mong muốn cung cấp các sản phẩm burger, gà rán và đồ uống chất lượng cao, giá cả hợp lý. Chúng tôi tự hào về nguồn nguyên liệu tươi ngon, quy trình chế biến an toàn và dịch vụ khách hàng tận tâm.</p>','assets/img/about.jpg','TRQshop - Thương hiệu đồ ăn nhanh hàng đầu tại Việt Nam',1,NOW());

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

-- PURCHASE ORDERS
INSERT INTO purchase_orders (supplier_id, user_id, total_amount, status) VALUES (1,1,5000000,1);
INSERT INTO purchase_order_items (purchase_order_id, ingredient_id, quantity, unit_price, subtotal) VALUES
(1,1,20,200000,4000000),
(1,2,100,10000,1000000);

SET FOREIGN_KEY_CHECKS=1;