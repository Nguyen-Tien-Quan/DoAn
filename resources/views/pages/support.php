<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Lấy danh sách FAQ từ database
$faqs = [];
if (isset($db)) {
    $stmt = $db->query("SELECT * FROM support_articles
                       WHERE status = 1
                       ORDER BY sort_order ASC, id DESC");
    $faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<style>
/* ===== VARIABLES ===== */
:root {
    --bg-page: linear-gradient(135deg, #eef2ff, #f8fafc);
    --card-bg: #ffffff;
    --text-main: #111827;
    --text-sub: #6b7280;
    --text-faq: #4b5563;
    --primary: linear-gradient(45deg, #4f46e5, #9333ea);
    --shadow-1: rgba(0,0,0,0.08);
    --shadow-2: rgba(0,0,0,0.12);
}

html.dark {
    --bg-page: linear-gradient(135deg, #1e2230, #151821);
    --card-bg: #2a2f3c;
    --text-main: #ffffff;
    --text-sub: #b9babe;
    --text-faq: #d1d5db;
    --primary: linear-gradient(45deg, #6366f1, #8b5cf6);
}

/* ===== PAGE ===== */
.page-support {
    padding: 60px 0;
    background: var(--bg-page);
    min-height: 100vh;
    color: var(--text-main);
}

/* ===== HEADER ===== */
.page-header {
    text-align: center;
    margin-bottom: 60px;
}
.page-header h1 {
    font-size: 38px;
    font-weight: 700;
    background: var(--primary);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
.page-header p {
    color: var(--text-sub);
    font-size: 16px;
    max-width: 520px;
    margin: 12px auto 0;
}

/* ===== GRID - FIX 3 CARD CÂN ĐỐI ===== */
.support-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(310px, 1fr));
    gap: 30px;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

/* ===== CARD ===== */
.support-card {
    background: var(--card-bg);
    border-radius: 24px;
    padding: 40px 25px;
    text-align: center;
    box-shadow: 0 10px 30px var(--shadow-1);
    transition: all 0.3s ease;
    height: 100%;           /* Quan trọng: card cùng chiều cao */
    display: flex;
    flex-direction: column;
    align-items: center;
}
.support-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 25px 50px var(--shadow-2);
}
.support-icon {
    width: 78px;
    height: 78px;
    margin: 0 auto 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--primary);
    color: #fff;
    font-size: 30px;
    box-shadow: 0 8px 20px rgba(79,70,229,0.3);
}
.support-card h3 {
    font-size: 22px;
    margin-bottom: 12px;
    font-weight: 600;
}
.support-card p {
    color: var(--text-sub);
    font-size: 15px;
    line-height: 1.6;
    flex-grow: 1;
    margin-bottom: 20px;
}

/* ===== BUTTON ===== */
.btns {
    display: inline-block;
    padding: 13px 30px;
    border-radius: 999px;
    font-size: 15px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    margin-top: auto;
}
.btns-primary {
    background: var(--primary);
    color: #fff;
}
.btns-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 25px rgba(79,70,229,0.4);
}

/* ===== CHAT BOX - ĐÃ TĂNG KÍCH THƯỚC & ĐẸP HƠN ===== */
#chat-box {
    position: fixed;
    bottom: 90px;
    right: 30px;
    width: 380px;
    height: 520px;
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.35);
    display: none;
    flex-direction: column;
    overflow: hidden;
    z-index: 9999;
}
.chat-header {
    background: linear-gradient(45deg, #4f46e5, #9333ea);
    color: #fff;
    padding: 16px 20px;
    font-weight: 600;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 17px;
}
.chat-content {
    flex: 1;
    padding: 18px;
    overflow-y: auto;
    background: #f9fafb;
}
.chat-msg {
    padding: 10px 14px;
    border-radius: 14px;
    margin-bottom: 10px;
    max-width: 85%;
    display: flex;
}
.chat-input {
    border: none;
    border-top: 1px solid #eee;
    padding: 16px 20px;
    width: 100%;
    outline: none;
    font-size: 15px;
}
</style>

<main class="container page-support">

    <!-- HEADER -->
    <div class="page-header">
        <h1>🎧 Trung tâm hỗ trợ</h1>
        <p>Chúng tôi luôn sẵn sàng hỗ trợ bạn 24/7</p>
    </div>

    <!-- GRID -->
    <div class="support-grid">
        <div class="support-card">
            <div class="support-icon">💬</div>
            <h3>Chat trực tuyến</h3>
            <p>Nhận hỗ trợ ngay lập tức từ nhân viên</p>
            <a href="#" class="btns btns-primary" onclick="openChat()">Chat ngay</a>
        </div>

        <div class="support-card">
            <div class="support-icon">📧</div>
            <h3>Email hỗ trợ</h3>
            <p>support@trqshop.com</p>
            <p>Phản hồi trong 24 giờ</p>
            <a href="mailto:support@trqshop.com" class="btns btns-primary">Gửi email</a>
        </div>

        <div class="support-card">
            <div class="support-icon">📞</div>
            <h3>Hotline</h3>
            <p>1900 1234</p>
            <p>8:00 - 21:00 mỗi ngày</p>
        </div>
    </div>

    <!-- TRA CỨU ĐƠN HÀNG -->
    <div style="max-width:600px; margin:40px auto; background:var(--card-bg); padding:25px; border-radius:16px; box-shadow:0 10px 30px var(--shadow-1);">
        <h3 style="text-align:center; margin-bottom:15px;">🔍 Tra cứu đơn hàng</h3>
        <input type="text" id="order_code" placeholder="Nhập mã đơn hàng (ORD001)" style="width:100%; padding:12px; border-radius:8px; border:1px solid #ddd;">
        <button onclick="trackOrder()" class="btns btns-primary" style="width:100%; margin-top:10px;">Tra cứu</button>
        <div id="order-result" style="margin-top:15px;"></div>
    </div>

    <!-- FAQ -->
    <div class="faq-section">
        <h2>❓ Câu hỏi thường gặp</h2>
        <input type="text" id="faq-search" class="faq-search" placeholder="🔎 Tìm kiếm câu hỏi...">

        <?php foreach($faqs as $faq): ?>
        <details class="faq-item">
            <summary><?= htmlspecialchars($faq['question']) ?></summary>
            <p><?= nl2br(htmlspecialchars($faq['answer'])) ?></p>
        </details>
        <?php endforeach; ?>

        <?php if(empty($faqs)): ?>
        <p style="text-align:center; color:#888;">Chưa có câu hỏi thường gặp.</p>
        <?php endif; ?>
    </div>

</main>

<!-- FLOAT CHAT BUTTON -->
<div id="chat-toggle">💬</div>

<!-- CHAT BOX -->
<div id="chat-box">
    <div class="chat-header">
        Hỗ trợ
        <span onclick="toggleChat()">✖</span>
    </div>
    <div id="chat-content" class="chat-content"></div>
    <input id="chat-input" class="chat-input" placeholder="Nhập tin nhắn...">
</div>

<script>
// ==================== TRA CỨU ĐƠN HÀNG ====================
// ==================== TRA CỨU ĐƠN HÀNG ====================
async function trackOrder() {
    const code = document.getElementById('order_code').value.trim();
    const result = document.getElementById('order-result');
    if (!code) {
        result.innerHTML = `<div style="color:red;">Vui lòng nhập mã đơn hàng!</div>`;
        return;
    }
    try {
        const res = await fetch('index.php?url=track-order', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `order_code=${encodeURIComponent(code)}`
        });
        const data = await res.json();
        if (data.success) {
            result.innerHTML = `<div style="background:#d1fae5;color:#065f46;padding:12px;border-radius:8px;">${data.message}</div>`;
        } else {
            result.innerHTML = `<div style="background:#fee2e2;color:#b91c1c;padding:12px;border-radius:8px;">${data.message}</div>`;
        }
    } catch(e) {
        result.innerHTML = `<div style="color:red;">Lỗi kết nối!</div>`;
    }
}

// ==================== CHAT FUNCTIONS ====================
function toggleChat() {
    const box = document.getElementById('chat-box');
    box.style.display = (box.style.display === 'flex') ? 'none' : 'flex';
}
function openChat() {
    const box = document.getElementById('chat-box');
    box.style.display = 'flex';
    document.getElementById('chat-input').focus();
}

// ==================== CHAT FUNCTIONS ====================
function toggleChat() {
    const box = document.getElementById('chat-box');
    box.style.display = (box.style.display === 'flex') ? 'none' : 'flex';
}
function openChat() {
    const box = document.getElementById('chat-box');
    box.style.display = 'flex';
    // Tự động focus input
    document.getElementById('chat-input').focus();
}

// Chat logic
const chatContent = document.getElementById('chat-content');
const chatInput = document.getElementById('chat-input');

// Dữ liệu trả lời chatbot
// Dữ liệu trả lời chatbot - ĐÃ MỞ RỘNG NHIỀU
const botResponses = {
    "xin chào": "Chào bạn! Mình là trợ lý hỗ trợ của TRQshop. Bạn cần hỗ trợ gì hôm nay?",
    "chào": "Chào bạn! ❤️",
    "hello": "Chào bạn! ❤️",
    "hi": "Chào bạn! ❤️",

    // Đặt hàng
    "đặt hàng": "Bạn chọn món → Thêm vào giỏ hàng → Điền thông tin giao hàng → Thanh toán. Rất đơn giản chỉ 3 phút!",
    "làm sao để đặt hàng": "Bạn chọn món → Thêm vào giỏ hàng → Điền thông tin giao hàng → Thanh toán. Rất đơn giản!",
    "cách đặt hàng": "Bạn chọn món → Thêm vào giỏ hàng → Điền thông tin giao hàng → Thanh toán.",
    "order": "Bạn chọn món → Thêm vào giỏ hàng → Điền thông tin giao hàng → Thanh toán.",

    // Phí ship & giao hàng
    "phí ship": "Miễn phí ship cho đơn từ 200.000đ. Dưới mức đó phí là 10.000đ.",
    "ship": "Miễn phí ship cho đơn từ 200.000đ. Dưới mức đó phí là 10.000đ.",
    "giao hàng": "Thường giao trong 25-45 phút tùy khu vực. Miễn phí ship từ 200k.",
    "thời gian giao": "Thường giao trong 25-45 phút tùy khu vực.",
    "khu vực": "Hiện tại TRQshop giao hàng tại Hà Nội và một số khu vực lân cận.",

    // Đổi trả & chính sách
    "đổi trả": "Bạn có 7 ngày để đổi/trả nếu sản phẩm lỗi hoặc không đúng mô tả.",
    "hủy đơn": "Bạn có thể hủy đơn nếu đơn hàng chưa được chuẩn bị. Liên hệ hotline để được hỗ trợ nhanh nhất.",
    "chính sách": "Chính sách đổi trả trong 7 ngày. Đơn hàng trên 200k được miễn phí ship.",

    // Tra cứu đơn hàng
    "tra cứu đơn": "Bạn có thể tra cứu đơn hàng ngay tại trang này bằng mã đơn (ORDxxxx).",
    "tình trạng đơn": "Bạn có thể tra cứu đơn hàng ngay tại trang này bằng mã đơn (ORDxxxx).",
    "đơn hàng": "Bạn có thể tra cứu đơn hàng ngay tại trang này bằng mã đơn (ORDxxxx).",
    "kiểm tra đơn": "Nhập mã đơn hàng (ORDxxxx) vào ô tra cứu bên trên nhé!",

    // Thanh toán
    "thanh toán": "Chúng mình hỗ trợ thanh toán tiền mặt, MoMo, VNPay và thẻ ngân hàng.",
    "cod": "Hỗ trợ thanh toán khi nhận hàng (COD).",
    "momo": "Hỗ trợ thanh toán qua MoMo rất tiện lợi.",

    // Sản phẩm & Menu
    "combo": "Hiện có rất nhiều combo hấp dẫn từ 65.000đ. Bạn có thể xem ở mục Thực đơn.",
    "burger": "Burger của chúng mình rất ngon, đặc biệt là Burger bò và Burger gà phô mai!",
    "gà rán": "Gà rán giòn tan, có sốt cay Hàn Quốc rất ngon.",

    // Liên hệ
    "hotline": "Hotline: 1900 1234 (8:00 - 21:00 hàng ngày)",
    "số điện thoại": "Hotline: 1900 1234 (8:00 - 21:00)",
    "email": "Email hỗ trợ: support@trqshop.com (phản hồi trong 24h)",

    // Khác
    "giờ mở cửa": "Chúng mình phục vụ từ 8:00 đến 21:00 hàng ngày.",
    "khuyến mãi": "Hiện đang có Flash Sale giảm đến 20% một số món. Bạn check mục Khuyến mãi nhé!",
    "default": "Mình chưa hiểu rõ lắm ạ 😅\nBạn thử hỏi về: đặt hàng, phí ship, đổi trả, tra cứu đơn hàng, combo, hoặc gọi hotline 1900 1234 nhé!"
};

// Hàm lấy câu trả lời hardcode
function getBotResponse(message) {
    message = message.toLowerCase().trim();
    for (let key in botResponses) {
        if (message.includes(key)) {
            return botResponses[key];
        }
    }
    return botResponses.default;
}

// Gửi tin nhắn đến chatbot (ưu tiên database)
async function sendToBot(message) {
    try {
        const res = await fetch('chatbot.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ message: message })
        });
        const data = await res.json();
        addMessage(data.reply || "Mình chưa hiểu rõ lắm ạ.", false);
    } catch (e) {
        // Fallback nếu database lỗi
        const reply = getBotResponse(message);
        addMessage(reply, false);
    }
}

function addMessage(text, isUser) {
    const div = document.createElement('div');
    div.className = `chat-msg ${isUser ? 'user-msg' : 'bot-msg'}`;
    div.style.cssText = isUser
        ? 'background:#4f46e5; color:white; margin-left:auto;'
        : 'background:#e5e7eb; color:#111827; margin-right:auto;';
    div.textContent = text;
    chatContent.appendChild(div);
    chatContent.scrollTop = chatContent.scrollHeight;
}

// ==================== XỬ LÝ NHẬP TIN NHẮN ====================
chatInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        const msg = this.value.trim();
        if (!msg) return;

        addMessage(msg, true);        // Tin nhắn người dùng
        this.value = '';

        setTimeout(() => {
            sendToBot(msg);           // ← Dùng hàm mới này
        }, 600);
    }
});

// ==================== TÌM KIẾM FAQ ====================
document.getElementById('faq-search').addEventListener('input', function(){
    let term = this.value.toLowerCase();
    document.querySelectorAll('.faq-item').forEach(item => {
        let q = item.querySelector('summary').textContent.toLowerCase();
        item.style.display = q.includes(term) ? '' : 'none';
    });
});

// Khởi tạo
document.getElementById('chat-toggle').onclick = toggleChat;

// Style cho tin nhắn
const style = document.createElement('style');
style.textContent = `
    .bot-msg { margin-right: auto; }
    .user-msg { margin-left: auto; }
`;
document.head.appendChild(style);
</script>
