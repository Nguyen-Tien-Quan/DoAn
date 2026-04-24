<style>
    .contact-container {
    padding: 40px;
    background: #f5f5f5;
}

.contact-box {
    display: flex;
    gap: 30px;
}

.contact-form {
    width: 40%;
    background: #fff;
    padding: 25px;
    border-radius: 16px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
}

.contact-form h3 {
    color: #f4b400;
    margin-bottom: 20px;
}

.contact-form input,
.contact-form textarea {
    width: 100%;
    margin-bottom: 12px;
    padding: 10px;
    border-radius: 10px;
    border: 1px solid #ddd;
}

.contact-form button {
    width: 100%;
    padding: 12px;
    background: #f4b400;
    border: none;
    border-radius: 12px;
    color: #fff;
    font-weight: bold;
}

.contact-map {
    width: 60%;
    height: 400px;
    border-radius: 16px;
    overflow: hidden;
}
</style>
<div class="contact-container">
    <div class="contact-box">

        <!-- LEFT -->
        <div class="contact-form">
            <h3>NHẬP THÔNG TIN LIÊN HỆ</h3>

            <input type="text" placeholder="Họ và tên">
            <input type="email" placeholder="Email">
            <input type="text" placeholder="Số điện thoại">
            <textarea placeholder="Nội dung"></textarea>

            <button>Gửi ngay</button>
        </div>

        <!-- RIGHT -->
        <div class="contact-map">
            <iframe
                src="https://www.google.com/maps?q=Hà Nội&output=embed"
                width="100%" height="100%" style="border:0;">
            </iframe>
        </div>

    </div>
</div>
