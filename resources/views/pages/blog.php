<?php
// Lấy kết nối database
require_once __DIR__ . '/../../../config/database.php';
$conn = getDB();

// Phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 6;
$offset = ($page - 1) * $limit;

// Đếm tổng số bài viết
$totalStmt = $conn->prepare("SELECT COUNT(*) FROM blog_posts WHERE status = 1");
$totalStmt->execute();
$totalPosts = $totalStmt->fetchColumn();
$totalPages = ceil($totalPosts / $limit);

// Lấy danh sách bài viết
$stmt = $conn->prepare("SELECT * FROM blog_posts WHERE status = 1 ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll();
?>
<main class="container blog-page">
    <div class="page-header">
        <h1>Blog Ẩm thực</h1>
        <p>Khám phá công thức, mẹo vặt và xu hướng mới</p>
    </div>

    <div class="blog-grid">
        <?php if (empty($posts)): ?>
            <p class="text-center">Chưa có bài viết nào.</p>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <article class="blog-card">
                    <img src="<?= $base . htmlspecialchars($post['image'] ?? 'assets/img/blog/default.jpg') ?>" alt="<?= htmlspecialchars($post['title']) ?>">
                    <div class="blog-card__content">
                        <span class="blog-category"><?= htmlspecialchars($post['category'] ?? 'Chung') ?></span>
                        <h3><a href="<?= $base ?>index.php?url=blog-detail&slug=<?= urlencode($post['slug']) ?>"><?= htmlspecialchars($post['title']) ?></a></h3>
                        <p><?= htmlspecialchars(substr($post['excerpt'] ?? $post['content'], 0, 120)) ?>...</p>
                        <div class="blog-meta"><?= date('d/m/Y', strtotime($post['created_at'])) ?> • <?= $post['views'] ?? 0 ?> lượt xem</div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="<?= $base ?>index.php?url=blog&page=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
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
