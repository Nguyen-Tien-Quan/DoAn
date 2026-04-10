<?php
// Có thể lấy sản phẩm khuyến mãi từ DB, hiện tại dùng mẫu
?>
<main class="container promotion-page">
    <div class="page-header">
        <h1>🎉 Khuyến mãi đặc biệt</h1>
        <p>Nhận ngay ưu đãi lên đến 50% khi mua sắm hôm nay</p>
    </div>

    <div class="promo-banner">
        <img src="<?= $base ?>assets/img/promo/banner-summer.jpg" alt="Summer Sale">
        <div class="promo-text">
            <h2>Flash Sale</h2>
            <p>Giảm 30% cho đơn hàng từ 200k</p>
            <span class="countdown" data-end="2025-04-30 23:59:59"></span>
            <a href="<?= $base ?>index.php?url=products" class="btn btn--primary">Mua ngay</a>
        </div>
    </div>

    <div class="promo-grid">
        <div class="promo-card">
            <div class="promo-tag">-20%</div>
            <img src="<?= $base ?>assets/img/product/item-1.png" alt="Burger bò">
            <h3>Burger bò phô mai</h3>
            <p class="old-price">50.000đ</p>
            <p class="new-price">40.000đ</p>
            <button class="btn btn--outline">Thêm vào giỏ</button>
        </div>
        <div class="promo-card">
            <div class="promo-tag">-15%</div>
            <img src="<?= $base ?>assets/img/product/item-2.png" alt="Gà rán">
            <h3>Gà rán giòn 3 miếng</h3>
            <p class="old-price">85.000đ</p>
            <p class="new-price">72.250đ</p>
            <button class="btn btn--outline">Thêm vào giỏ</button>
        </div>
        <div class="promo-card">
            <div class="promo-tag">-10%</div>
            <img src="<?= $base ?>assets/img/product/item-3.png" alt="Combo">
            <h3>Combo Gia đình</h3>
            <p class="old-price">185.000đ</p>
            <p class="new-price">166.500đ</p>
            <button class="btn btn--outline">Thêm vào giỏ</button>
        </div>
    </div>
</main>

<style>
    .promotion-page {
        padding: 2rem 0;
    }
    .promo-banner {
        position: relative;
        border-radius: 28px;
        overflow: hidden;
        margin-bottom: 2.5rem;
    }
    .promo-banner img {
        width: 100%;
        height: 300px;
        object-fit: cover;
    }
    .promo-text {
        position: absolute;
        bottom: 2rem;
        left: 2rem;
        background: rgba(0,0,0,0.6);
        color: white;
        padding: 1rem 1.8rem;
        border-radius: 40px;
        backdrop-filter: blur(6px);
    }
    .promo-text h2 {
        margin: 0 0 0.3rem;
    }
    .promo-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 1.8rem;
    }
    .promo-card {
        background: #fff;
        border-radius: 20px;
        padding: 1rem;
        text-align: center;
        position: relative;
        box-shadow: 0 5px 12px rgba(0,0,0,0.05);
    }
    .promo-tag {
        position: absolute;
        top: 12px;
        left: 12px;
        background: #ef4444;
        color: white;
        padding: 4px 10px;
        border-radius: 30px;
        font-weight: bold;
        font-size: 0.8rem;
    }
    .promo-card img {
        width: 140px;
        margin: 0 auto;
    }
    .old-price {
        text-decoration: line-through;
        color: #94a3b8;
        font-size: 0.85rem;
    }
    .new-price {
        font-size: 1.3rem;
        font-weight: 700;
        color: #e67e22;
        margin: 0.5rem 0;
    }
</style>

<script>
    // Simple countdown (nếu muốn)
    document.querySelectorAll('.countdown').forEach(el => {
        const end = new Date(el.dataset.end).getTime();
        const update = () => {
            const now = new Date().getTime();
            const diff = end - now;
            if(diff <= 0) {
                el.innerText = "Đã kết thúc";
                return;
            }
            const days = Math.floor(diff / (1000*60*60*24));
            const hours = Math.floor((diff % (86400000))/3600000);
            const mins = Math.floor((diff % 3600000)/60000);
            const secs = Math.floor((diff % 60000)/1000);
            el.innerText = `${days}d ${hours}h ${mins}m ${secs}s`;
        };
        update();
        setInterval(update, 1000);
    });
</script>
