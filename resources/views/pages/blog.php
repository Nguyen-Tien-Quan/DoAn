<?php
// Giả sử có thể lấy bài viết từ DB sau, hiện tại dùng dữ liệu mẫu
?>
<main class="container blog-page">
    <div class="page-header">
        <h1>Blog Ẩm thực</h1>
        <p>Khám phá công thức, mẹo vặt và xu hướng mới</p>
    </div>

    <div class="blog-grid">
        <article class="blog-card">
            <img src="<?= $base ?>assets/img/blog/blog-1.jpg" alt="Burger ngon">
            <div class="blog-card__content">
                <span class="blog-category">Công thức</span>
                <h3><a href="#">Cách làm burger bò phô mai tại nhà</a></h3>
                <p>Chỉ 15 phút với nguyên liệu đơn giản, bạn đã có bữa sáng hoàn hảo...</p>
                <div class="blog-meta">15/04/2025 • 5 phút đọc</div>
            </div>
        </article>
        <article class="blog-card">
            <img src="<?= $base ?>assets/img/blog/blog-2.jpg" alt="Cà phê">
            <div class="blog-card__content">
                <span class="blog-category">Kiến thức</span>
                <h3><a href="#">Phân biệt Arabica và Robusta</a></h3>
                <p>Hương vị, độ caffeine và cách chọn cà phê phù hợp với khẩu vị...</p>
                <div class="blog-meta">10/04/2025 • 8 phút đọc</div>
            </div>
        </article>
        <article class="blog-card">
            <img src="<?= $base ?>assets/img/blog/blog-3.jpg" alt="Trà sữa">
            <div class="blog-card__content">
                <span class="blog-category">Review</span>
                <h3><a href="#">Top 5 loại trà đào được yêu thích nhất</a></h3>
                <p>Thanh mát, giải nhiệt – lựa chọn hàng đầu cho mùa hè...</p>
                <div class="blog-meta">05/04/2025 • 3 phút đọc</div>
            </div>
        </article>
    </div>

    <div class="pagination">
        <span class="active">1</span>
        <a href="#">2</a>
        <a href="#">3</a>
    </div>
</main>

<style>
    .blog-page {
        padding: 2rem 0;
    }
    .blog-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 2rem;
        margin-bottom: 2.5rem;
    }
    .blog-card {
        background: #fff;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        transition: 0.2s;
    }
    .blog-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.1);
    }
    .blog-card img {
        width: 100%;
        height: 200px;
        object-fit: cover;
    }
    .blog-card__content {
        padding: 1.2rem;
    }
    .blog-category {
        display: inline-block;
        background: #f1f5f9;
        padding: 4px 12px;
        border-radius: 30px;
        font-size: 0.75rem;
        font-weight: 600;
        color: #e67e22;
        margin-bottom: 0.5rem;
    }
    .blog-card h3 {
        font-size: 1.2rem;
        margin: 0.5rem 0;
    }
    .blog-card h3 a {
        text-decoration: none;
        color: #1e293b;
    }
    .blog-meta {
        font-size: 0.75rem;
        color: #64748b;
        margin-top: 0.8rem;
    }
    .pagination {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-top: 2rem;
    }
    .pagination a, .pagination span {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 8px;
        background: #f1f5f9;
        color: #1e293b;
        text-decoration: none;
    }
    .pagination .active {
        background: #e67e22;
        color: white;
    }
</style>
