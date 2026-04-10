<main class="container about-page">
    <div class="about-hero">
        <h1>Về TRQshop</h1>
        <p>Mang ẩm thực chất lượng đến mọi nhà</p>
    </div>

    <div class="about-content">
        <div class="about-story">
            <h2>Câu chuyện của chúng tôi</h2>
            <p>TRQshop được thành lập năm 2023 với mong muốn cung cấp các sản phẩm burger, gà rán và đồ uống chất lượng cao, giá cả hợp lý. Chúng tôi tự hào về nguồn nguyên liệu tươi ngon, quy trình chế biến an toàn và dịch vụ khách hàng tận tâm.</p>
            <p>Với hơn 10.000 khách hàng hài lòng, TRQshop đang từng bước khẳng định vị thế là thương hiệu F&B đáng tin cậy tại Việt Nam.</p>
        </div>
        <div class="about-mission">
            <h2>Sứ mệnh</h2>
            <p>Mang lại niềm vui ẩm thực mỗi ngày, kết nối mọi người qua những bữa ăn ngon và tiện lợi.</p>
        </div>
        <div class="about-values">
            <h2>Giá trị cốt lõi</h2>
            <ul>
                <li>✔ Chất lượng hàng đầu</li>
                <li>✔ Phục vụ tận tâm</li>
                <li>✔ Đổi mới không ngừng</li>
            </ul>
        </div>
    </div>

    <div class="about-team">
        <h2>Đội ngũ của chúng tôi</h2>
        <div class="team-grid">
            <div class="team-member">
                <img src="<?= $base ?>assets/img/team/ceo.jpg" alt="CEO">
                <h4>Nguyễn Văn A</h4>
                <p>CEO & Founder</p>
            </div>
            <div class="team-member">
                <img src="<?= $base ?>assets/img/team/chef.jpg" alt="Chef">
                <h4>Trần Thị B</h4>
                <p>Bếp trưởng</p>
            </div>
        </div>
    </div>
</main>

<style>
    .about-page {
        padding: 2rem 0;
    }
    .about-hero {
        text-align: center;
        background: linear-gradient(135deg, #1e293b, #2d3a4f);
        color: white;
        padding: 3rem 2rem;
        border-radius: 32px;
        margin-bottom: 2rem;
    }
    .about-hero h1 {
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
    }
    .about-content {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 2rem;
        margin-bottom: 3rem;
    }
    .about-story, .about-mission, .about-values {
        background: #fff;
        padding: 1.5rem;
        border-radius: 24px;
        box-shadow: 0 5px 12px rgba(0,0,0,0.05);
    }
    .about-values ul {
        list-style: none;
        padding-left: 0;
    }
    .about-values li {
        margin-bottom: 0.6rem;
    }
    .about-team {
        text-align: center;
    }
    .team-grid {
        display: flex;
        justify-content: center;
        gap: 2rem;
        flex-wrap: wrap;
        margin-top: 1.5rem;
    }
    .team-member {
        background: #f8fafc;
        border-radius: 24px;
        padding: 1rem;
        width: 200px;
    }
    .team-member img {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        margin-bottom: 0.8rem;
    }
</style>
