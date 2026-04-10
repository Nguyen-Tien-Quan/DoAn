<?php
// Không cần session hay layout, chỉ nội dung
?>

<style>
    .page-support {
        padding: 2rem 0;
    }
    .page-header {
        text-align: center;
        margin-bottom: 2.5rem;
    }
    .page-header h1 {
        font-size: 2.2rem;
        margin-bottom: 0.5rem;
    }
    .support-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 2rem;
        margin-bottom: 3rem;
    }
    .support-card {
        background: #fff;
        border-radius: 20px;
        padding: 2rem 1.5rem;
        text-align: center;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        transition: transform 0.2s;
    }
    .support-card:hover {
        transform: translateY(-5px);
    }
    .support-card img {
        width: 60px;
        margin-bottom: 1rem;
    }
    .support-card h3 {
        font-size: 1.3rem;
        margin: 0.5rem 0;
    }
    .faq-section {
        max-width: 800px;
        margin: 0 auto;
    }
    .faq-section h2 {
        text-align: center;
        margin-bottom: 1.5rem;
    }
    details {
        background: #f8fafc;
        padding: 1rem 1.2rem;
        margin-bottom: 1rem;
        border-radius: 16px;
        cursor: pointer;
    }
    details summary {
        font-weight: 600;
        outline: none;
    }
    details p {
        margin-top: 0.8rem;
        padding-left: 1rem;
        color: #475569;
    }
</style>
<main class="container page-support">
    <div class="page-header">
        <h1>Trung tâm hỗ trợ</h1>
        <p>Chúng tôi luôn sẵn sàng giúp đỡ bạn 24/7</p>
    </div>

    <div class="support-grid">
        <div class="support-card">
            <img src="<?= $base ?>assets/icons/chat.svg" alt="Chat">
            <h3>Chat trực tuyến</h3>
            <p>Trò chuyện với nhân viên hỗ trợ ngay lập tức</p>
            <a href="#" class="btn btn--primary">Bắt đầu chat</a>
        </div>
        <div class="support-card">
            <img src="<?= $base ?>assets/icons/email.svg" alt="Email">
            <h3>Gửi email</h3>
            <p>support@trqshop.com</p>
            <p>Phản hồi trong vòng 24h</p>
            <a href="mailto:support@trqshop.com" class="btn btn--outline">Gửi email</a>
        </div>
        <div class="support-card">
            <img src="<?= $base ?>assets/icons/phone.svg" alt="Hotline">
            <h3>Hotline</h3>
            <p>1900 1234</p>
            <p>Thời gian: 8:00 - 21:00 (T2-CN)</p>
        </div>
    </div>

    <div class="faq-section">
        <h2>Câu hỏi thường gặp</h2>
        <div class="faq-list">
            <details>
                <summary>Làm thế nào để đặt hàng?</summary>
                <p>Bạn chọn sản phẩm, thêm vào giỏ, sau đó thanh toán. Hệ thống sẽ gửi email xác nhận.</p>
            </details>
            <details>
                <summary>Phí vận chuyển tính thế nào?</summary>
                <p>Phí ship = 10.000đ cho đơn hàng dưới 200.000đ, miễn phí ship cho đơn từ 200.000đ.</p>
            </details>
            <details>
                <summary>Chính sách đổi trả?</summary>
                <p>Đổi trả trong vòng 7 ngày nếu sản phẩm lỗi hoặc không đúng mô tả.</p>
            </details>
        </div>
    </div>
</main>
