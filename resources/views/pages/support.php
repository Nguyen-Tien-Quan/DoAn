<?php
// Trang support UI cải tiến
?>

<style>
    .page-support {
        padding: 40px 0;
    }

    .page-header {
        text-align: center;
        margin-bottom: 40px;
    }

    .page-header h1 {
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .page-header p {
        color: #6b7280;
    }

    /* GRID */
    .support-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 24px;
        margin-bottom: 50px;
    }

    .support-card {
        background: #fff;
        border-radius: 20px;
        padding: 25px 20px;
        text-align: center;
        box-shadow: 0 10px 25px rgba(0,0,0,0.06);
        transition: all 0.25s ease;
        position: relative;
        overflow: hidden;
    }

    .support-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, #4f46e5, #06b6d4);
    }

    .support-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    }

    .support-card img {
        width: 55px;
        margin-bottom: 12px;
    }

    .support-card h3 {
        font-size: 18px;
        margin: 10px 0;
    }

    .support-card p {
        font-size: 14px;
        color: #6b7280;
        margin-bottom: 10px;
    }

    .btn {
        display: inline-block;
        padding: 10px 18px;
        border-radius: 10px;
        text-decoration: none;
        font-size: 14px;
        transition: 0.2s;
    }

    .btn--primary {
        background: #4f46e5;
        color: #fff;
    }

    .btn--primary:hover {
        background: #4338ca;
    }

    .btn--outline {
        border: 1px solid #4f46e5;
        color: #4f46e5;
    }

    .btn--outline:hover {
        background: #4f46e5;
        color: #fff;
    }

    /* FAQ */
    .faq-section {
        max-width: 850px;
        margin: auto;
    }

    .faq-section h2 {
        text-align: center;
        margin-bottom: 25px;
        font-size: 24px;
    }

    .faq-item {
        background: #fff;
        border-radius: 14px;
        margin-bottom: 12px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        overflow: hidden;
    }

    .faq-item summary {
        padding: 15px 18px;
        cursor: pointer;
        font-weight: 600;
        list-style: none;
        position: relative;
    }

    .faq-item summary::after {
        content: "+";
        position: absolute;
        right: 20px;
        font-size: 18px;
    }

    .faq-item[open] summary::after {
        content: "-";
    }

    .faq-item p {
        padding: 0 18px 15px;
        color: #4b5563;
        font-size: 14px;
    }
</style>

<main class="container page-support">

    <div class="page-header">
        <h1>🎧 Trung tâm hỗ trợ</h1>
        <p>Luôn sẵn sàng hỗ trợ bạn mọi lúc mọi nơi</p>
    </div>

    <div class="support-grid">

        <div class="support-card">
            <img src="<?= $base ?>assets/icons/chat.svg">
            <h3>Chat trực tuyến</h3>
            <p>Nhận hỗ trợ ngay lập tức từ nhân viên</p>
            <a href="#" class="btn btn--primary">Chat ngay</a>
        </div>

        <div class="support-card">
            <img src="<?= $base ?>assets/icons/email.svg">
            <h3>Email hỗ trợ</h3>
            <p>support@trqshop.com</p>
            <p>Phản hồi trong 24h</p>
            <a href="mailto:support@trqshop.com" class="btn btn--outline">Gửi email</a>
        </div>

        <div class="support-card">
            <img src="<?= $base ?>assets/icons/phone.svg">
            <h3>Hotline</h3>
            <p>1900 1234</p>
            <p>8:00 - 21:00 (T2 - CN)</p>
        </div>

    </div>

    <div class="faq-section">
        <h2>❓ Câu hỏi thường gặp</h2>

        <details class="faq-item">
            <summary>Làm sao để đặt hàng?</summary>
            <p>Chọn sản phẩm → Thêm vào giỏ → Thanh toán → Nhận email xác nhận.</p>
        </details>

        <details class="faq-item">
            <summary>Phí vận chuyển?</summary>
            <p>10.000đ với đơn dưới 200k. Miễn phí cho đơn từ 200k.</p>
        </details>

        <details class="faq-item">
            <summary>Đổi trả như thế nào?</summary>
            <p>Đổi trả trong 7 ngày nếu lỗi hoặc sai mô tả.</p>
        </details>

    </div>

</main>
