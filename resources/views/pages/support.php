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
    padding: 50px 0;
    background: var(--bg-page);
    min-height: 100vh;
    color: var(--text-main);
}

/* ===== HEADER ===== */
.page-header {
    text-align: center;
    margin-bottom: 50px;
}
.page-header h1 {
    font-size: 36px;
    font-weight: 700;
    background: var(--primary);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
.page-header p {
    color: var(--text-sub);
    font-size: 15px;
}

/* ===== GRID ===== */
.support-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 28px;
}

/* ===== CARD ===== */
.support-card {
    background: var(--card-bg);
    border-radius: 20px;
    padding: 30px 25px;
    text-align: center;
    box-shadow: 0 10px 30px var(--shadow-1);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}
.support-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px var(--shadow-2);
}
.support-icon {
    width: 60px;
    height: 60px;
    margin: 0 auto 15px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #4f46e5, #9333ea);
    color: #fff;
    font-size: 24px;
}
.support-card h3 { font-size: 20px; margin-bottom: 10px; }
.support-card p { color: var(--text-sub); font-size: 14px; margin-bottom: 12px; }

/* ===== BUTTON ===== */
.btns {
    display: inline-block;
    padding: 12px 22px;
    border-radius: 999px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: 0.25s;
}
.btns-primary {
    background: var(--primary);
    color: #fff;
}
.btns-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(79,70,229,0.4);
}

/* ===== FAQ ===== */
.faq-section {
    margin-top: 60px;
    max-width: 800px;
    margin-left: auto;
    margin-right: auto;
}
.faq-section h2 {
    text-align: center;
    margin-bottom: 25px;
}
.faq-search {
    width: 100%;
    max-width: 600px;
    margin: 0 auto 30px;
    padding: 14px 20px;
    border: 2px solid #e5e7eb;
    border-radius: 50px;
    font-size: 16px;
}
.faq-item {
    background: var(--card-bg);
    border-radius: 14px;
    margin-bottom: 12px;
    box-shadow: 0 5px 15px var(--shadow-1);
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
}
.faq-item[open] summary::after {
    content: "-";
}
.faq-item p {
    padding: 0 18px 15px;
    color: var(--text-faq);
}

/* ===== CHAT ===== */
#chat-toggle {
    position: fixed;
    bottom: 110px;
    right: 25px;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #4f46e5, #9333ea);
    color: #fff;
    font-size: 26px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 9999;
    box-shadow: 0 10px 25px rgba(0,0,0,0.25);
}
#chat-box {
    position: fixed;
    bottom: 180px;
    right: 25px;
    width: 340px;
    height: 420px;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.3);
    display: none;
    flex-direction: column;
    overflow: hidden;
    z-index: 9999;
}
.chat-header {
    background: linear-gradient(45deg, #4f46e5, #9333ea);
    color: #fff;
    padding: 12px;
    font-weight: 600;
    display: flex;
    justify-content: space-between;
}
.chat-content {
    flex: 1;
    padding: 10px;
    overflow-y: auto;
    background: #f9fafb;
}
.chat-msg {
    padding: 8px 12px;
    background: #e5e7eb;
    border-radius: 12px;
    margin-bottom: 6px;
    display: inline-block;
}
.chat-input {
    border: none;
    border-top: 1px solid #eee;
    padding: 10px;
    width: 100%;
    outline: none;
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
// Tra cứu đơn hàng
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

// Chat functions
function toggleChat() {
    const box = document.getElementById('chat-box');
    box.style.display = (box.style.display === 'flex') ? 'none' : 'flex';
}
function openChat() {
    document.getElementById('chat-box').style.display = 'flex';
}
document.getElementById('chat-toggle').onclick = toggleChat;

// Chat input
document.getElementById('chat-input').addEventListener('keypress', function(e){
    if(e.key === 'Enter'){
        let msg = this.value.trim();
        if(!msg) return;
        const content = document.getElementById('chat-content');
        content.innerHTML += `<div class="chat-msg" style="background:#4f46e5;color:white;">${msg}</div>`;
        this.value = '';
        content.scrollTop = content.scrollHeight;
    }
});

// Tìm kiếm FAQ
document.getElementById('faq-search').addEventListener('input', function(){
    let term = this.value.toLowerCase();
    document.querySelectorAll('.faq-item').forEach(item => {
        let q = item.querySelector('summary').textContent.toLowerCase();
        item.style.display = q.includes(term) ? '' : 'none';
    });
});
</script>
