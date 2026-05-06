<style>
/* ===== LIGHT MODE (default) ===== */
:root {
    --contact-bg: #f8f9fa;
    --contact-card-bg: #ffffff;
    --contact-text: #222;
    --contact-subtext: #666;
    --contact-border: #ddd;

    --contact-primary: linear-gradient(135deg, #ff9800, #ff5722);
}

/* ===== DARK MODE ===== */
html.dark {
    --contact-bg: #1e2230;
    --contact-card-bg: #2a2f3c;
    --contact-text: #ffffff;
    --contact-subtext: #b9babe;
    --contact-border: #3a3f4b;

    --contact-primary: linear-gradient(135deg, #6366f1, #8b5cf6);
}

/* ===== PAGE ===== */
.contact-page {
    font-family: 'Segoe UI', sans-serif;
    background: var(--contact-bg);
    color: var(--contact-text);
}

/* ===== HERO ===== */
.contact-hero {
    background: var(--contact-primary);
    color: #fff;
    padding: 60px 20px;
    text-align: center;
}

.contact-hero h1 {
    font-size: 36px;
    font-weight: bold;
}

.contact-hero p {
    opacity: 0.9;
}

/* ===== MAIN ===== */
.contact-container {
    max-width: 1200px;
    margin: -40px auto 40px;
    display: flex;
    gap: 30px;
    padding: 0 20px;
}

/* ===== FORM ===== */
.contact-form {
    flex: 1;
    background: var(--contact-card-bg);
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.contact-form h3 {
    margin-bottom: 20px;
    color: #ff5722;
}

html.dark .contact-form h3 {
    color: #a5b4fc;
}

.form-group {
    position: relative;
    margin-bottom: 15px;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 12px 12px 12px 40px;
    border-radius: 12px;
    border: 1px solid var(--contact-border);
    outline: none;
    background: transparent;
    color: var(--contact-text);
}

.form-group input::placeholder,
.form-group textarea::placeholder {
    color: var(--contact-subtext);
}

.form-group i {
    position: absolute;
    top: 12px;
    left: 12px;
    color: var(--contact-subtext);
}

.contact-form button {
    width: 100%;
    padding: 14px;
    background: var(--contact-primary);
    border: none;
    border-radius: 14px;
    color: white;
    font-weight: bold;
    transition: 0.3s;
}

.contact-form button:hover {
    transform: scale(1.05);
}

/* ===== RIGHT ===== */
.contact-right {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* ===== INFO BOX ===== */
.contact-info {
    background: var(--contact-card-bg);
    padding: 20px;
    border-radius: 20px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
}

.contact-info h4 {
    margin-bottom: 10px;
    color: #ff5722;
}

html.dark .contact-info h4 {
    color: #a5b4fc;
}

.contact-info p {
    margin: 5px 0;
    color: var(--contact-subtext);
}

/* ===== MAP ===== */
.contact-map {
    height: 250px;
    border-radius: 20px;
    overflow: hidden;
    border: 1px solid var(--contact-border);
}

/* ===== RESPONSIVE ===== */
@media(max-width: 768px) {
    .contact-container {
        flex-direction: column;
    }
}
</style>

<div class="contact-page">

    <!-- HERO -->
    <div class="contact-hero">
        <h1>Liên hệ với chúng tôi</h1>
        <p>Đặt món nhanh - Giao tận nơi - Hỗ trợ 24/7</p>
    </div>

    <!-- CONTENT -->
    <div class="contact-container">

        <!-- FORM -->
        <div class="contact-form">
            <h3>Gửi tin nhắn</h3>

            <div class="form-group">
                <i class="fas fa-user"></i>
                <input type="text" placeholder="Họ và tên">
            </div>

            <div class="form-group">
                <i class="fas fa-envelope"></i>
                <input type="email" placeholder="Email">
            </div>

            <div class="form-group">
                <i class="fas fa-phone"></i>
                <input type="text" placeholder="Số điện thoại">
            </div>

            <div class="form-group">
                <i class="fas fa-comment"></i>
                <textarea rows="4" placeholder="Nội dung"></textarea>
            </div>

            <button>🚀 Gửi ngay</button>
        </div>

        <!-- RIGHT -->
        <div class="contact-right">

            <!-- INFO -->
            <div class="contact-info">
                <h4>Thông tin cửa hàng</h4>
                <p>📍 Địa chỉ: Hà Nội, Việt Nam</p>
                <p>📞 Hotline: 0123 456 789</p>
                <p>📧 Email: support@foodshop.vn</p>
                <p>⏰ Giờ mở cửa: 8:00 - 22:00</p>
            </div>

            <!-- MAP -->
            <div class="contact-map">
                <iframe
                    src="https://www.google.com/maps?q=Hà Nội&output=embed"
                    width="100%" height="100%" style="border:0;">
                </iframe>
            </div>

        </div>

    </div>
</div>

<!-- ICON -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
