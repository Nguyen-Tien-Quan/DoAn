<?php
$notifications = $notifications ?? [];
$unreadCount = $unreadCount ?? 0;
function timeAgo($timestamp) {
    if (empty($timestamp)) return '';
    $time = strtotime($timestamp);
    $now = time();
    $diff = $now - $time;
    if ($diff < 60) return 'Vừa xong';
    if ($diff < 3600) return floor($diff / 60) . ' phút trước';
    if ($diff < 86400) return floor($diff / 3600) . ' giờ trước';
    return date('d/m/Y', $time);
}
?>
<div class="notifications-container">
    <div class="notifications-wrapper">
        <div class="notifications-header">
            <div class="header-title">
                <h1>Thông báo</h1>
                <?php if ($unreadCount > 0): ?>
                    <span class="unread-badge"><?= $unreadCount ?> chưa đọc</span>
                <?php endif; ?>
            </div>
            <?php if ($unreadCount > 0): ?>
                <button id="markAllReadBtn" class="btn-mark-all">Đánh dấu tất cả đã đọc</button>
            <?php endif; ?>
        </div>

        <?php if (empty($notifications)): ?>
            <div class="notifications-empty">
                <div class="empty-icon">🔔</div>
                <p class="empty-title">Chưa có thông báo</p>
                <p class="empty-desc">Khi có thông báo mới, chúng sẽ xuất hiện tại đây</p>
            </div>
        <?php else: ?>
            <div class="notifications-list">
                <?php foreach ($notifications as $noti): ?>
                    <div class="noti-card <?= $noti['is_read'] ? 'read' : 'unread' ?>" data-id="<?= $noti['id'] ?>">
                        <div class="noti-icon">
                            <?php if (!$noti['is_read']): ?>
                                <span class="unread-dot"></span>
                            <?php endif; ?>
                            <span class="icon-bell">🔔</span>
                        </div>
                        <div class="noti-content">
                            <div class="noti-title"><?= htmlspecialchars($noti['title']) ?></div>
                            <div class="noti-text"><?= htmlspecialchars($noti['content']) ?></div>
                            <div class="noti-time"><?= timeAgo($noti['created_at']) ?></div>
                        </div>
                        <?php if (!$noti['is_read']): ?>
                            <button class="mark-read-btn" data-id="<?= $noti['id'] ?>">✓ Đánh dấu đã đọc</button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* reset cục bộ cho component thông báo, dùng rem dựa trên root 10px */
.notifications-container {
    font-family: system-ui, -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    line-height: 1.5;
    color: #1e293b;
    max-width: 900px;
    margin: 0 auto;
    padding: 2rem 1.5rem;
}

.notifications-wrapper {
    background: #ffffff;
    border-radius: 28px;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.05);
    overflow: hidden;
    transition: box-shadow 0.2s ease;
}

/* header */
.notifications-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1.2rem;
    padding: 1.8rem 2rem;
    border-bottom: 1px solid #eef2f6;
    background: #fafcff;
}

.header-title {
    display: flex;
    align-items: center;
    gap: 1.2rem;
    flex-wrap: wrap;
}

.notifications-header h1 {
    font-size: 2.2rem; /* 22px với root 10px */
    font-weight: 700;
    margin: 0;
    background: linear-gradient(135deg, #1e293b, #2d3a4e);
    background-clip: text;
    -webkit-background-clip: text;
    color: transparent;
    letter-spacing: -0.3px;
}

.unread-badge {
    background: #eef2ff;
    color: #1e40af;
    font-size: 1.3rem;
    font-weight: 600;
    padding: 0.3rem 1rem;
    border-radius: 40px;
    line-height: 1;
}

.btn-mark-all {
    background: transparent;
    border: 1px solid #cbd5e1;
    padding: 0.7rem 1.6rem;
    border-radius: 60px;
    font-size: 1.3rem;
    font-weight: 500;
    color: #334155;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-mark-all:hover {
    background: #f1f5f9;
    border-color: #94a3b8;
    transform: translateY(-1px);
}

/* danh sách thông báo */
.notifications-list {
    display: flex;
    flex-direction: column;
}

.noti-card {
    display: flex;
    align-items: flex-start;
    gap: 1.5rem;
    padding: 1.6rem 2rem;
    border-bottom: 1px solid #edf2f7;
    transition: background 0.2s ease, transform 0.1s;
    position: relative;
}

.noti-card.unread {
    background: #fffef7;
}

.noti-card.unread::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: #3b82f6;
    border-radius: 0 4px 4px 0;
}

.noti-card:hover {
    background: #f8fafc;
}

/* icon và dot */
.noti-icon {
    position: relative;
    flex-shrink: 0;
    width: 4.4rem;
    height: 4.4rem;
    background: #f1f5f9;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.unread-dot {
    position: absolute;
    top: -2px;
    right: -2px;
    width: 12px;
    height: 12px;
    background: #ef4444;
    border: 2px solid white;
    border-radius: 50%;
}

.icon-bell {
    font-size: 2rem;
    line-height: 1;
}

/* nội dung */
.noti-content {
    flex: 1;
    min-width: 0;
}

.noti-title {
    font-size: 1.6rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 0.4rem;
    line-height: 1.3;
}

.noti-card.unread .noti-title {
    color: #0f172a;
}

.noti-text {
    font-size: 1.4rem;
    color: #334155;
    margin-bottom: 0.6rem;
    line-height: 1.45;
    word-break: break-word;
}

.noti-time {
    font-size: 1.2rem;
    color: #64748b;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
}

/* nút đánh dấu đã đọc */
.mark-read-btn {
    background: none;
    border: 1px solid #cbd5e1;
    border-radius: 40px;
    padding: 0.5rem 1.2rem;
    font-size: 1.2rem;
    font-weight: 500;
    color: #475569;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
    margin-left: 0.5rem;
}

.mark-read-btn:hover {
    background: #eef2ff;
    border-color: #3b82f6;
    color: #1e40af;
    transform: scale(0.98);
}

/* empty state */
.notifications-empty {
    text-align: center;
    padding: 4rem 2rem;
}

.empty-icon {
    font-size: 5rem;
    margin-bottom: 1.2rem;
    opacity: 0.6;
}

.empty-title {
    font-size: 1.8rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.5rem;
}

.empty-desc {
    font-size: 1.4rem;
    color: #64748b;
}

/* responsive */
@media (max-width: 640px) {
    .notifications-container {
        padding: 1rem;
    }
    .notifications-header {
        padding: 1.2rem 1.5rem;
        flex-direction: column;
        align-items: flex-start;
    }
    .noti-card {
        padding: 1.2rem 1.5rem;
        gap: 1rem;
        flex-wrap: wrap;
    }
    .noti-icon {
        width: 3.6rem;
        height: 3.6rem;
    }
    .icon-bell {
        font-size: 1.6rem;
    }
    .noti-title {
        font-size: 1.5rem;
    }
    .noti-text {
        font-size: 1.3rem;
    }
    .mark-read-btn {
        margin-left: auto;
        margin-top: 0.5rem;
        font-size: 1.1rem;
    }
}
</style>

<script>
(function() {
    // Mark single notification as read (AJAX + update UI)
    const markReadButtons = document.querySelectorAll('.mark-read-btn');
    const markAllBtn = document.getElementById('markAllReadBtn');

    async function markAsRead(notificationId, element) {
        try {
            const response = await fetch('<?= $base ?>index.php?url=api/notifications&action=mark-read&id=' + notificationId, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (response.ok) {
                // update UI: remove unread class, hide button, update unread badge
                const card = element.closest('.noti-card');
                if (card) {
                    card.classList.remove('unread');
                    card.classList.add('read');
                    const btn = card.querySelector('.mark-read-btn');
                    if (btn) btn.remove();
                    const dot = card.querySelector('.unread-dot');
                    if (dot) dot.remove();
                    // remove left blue border effect (pseudo-element will disappear because class unread removed)
                }
                // update global unread count badge
                updateUnreadBadge(-1);
            } else {
                console.error('Failed to mark as read');
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    async function markAllAsRead() {
        try {
            const response = await fetch('<?= $base ?>index.php?url=api/notifications&action=mark-all-read', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (response.ok) {
                // update all unread cards to read
                const unreadCards = document.querySelectorAll('.noti-card.unread');
                unreadCards.forEach(card => {
                    card.classList.remove('unread');
                    card.classList.add('read');
                    const btn = card.querySelector('.mark-read-btn');
                    if (btn) btn.remove();
                    const dot = card.querySelector('.unread-dot');
                    if (dot) dot.remove();
                });
                // remove unread badge in header and hide mark all button
                const badge = document.querySelector('.unread-badge');
                if (badge) badge.remove();
                if (markAllBtn) markAllBtn.style.display = 'none';
            } else {
                console.error('Failed to mark all as read');
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    function updateUnreadBadge(delta) {
        const badgeSpan = document.querySelector('.unread-badge');
        if (!badgeSpan) return;
        let current = parseInt(badgeSpan.innerText);
        if (isNaN(current)) return;
        let newCount = current + delta;
        if (newCount <= 0) {
            badgeSpan.remove();
            if (markAllBtn) markAllBtn.style.display = 'none';
            // if no unread left, update header message but keep all good
        } else {
            badgeSpan.innerText = newCount + ' chưa đọc';
        }
        // if unread count becomes zero, also remove mark all button
        if (newCount <= 0 && markAllBtn) markAllBtn.style.display = 'none';
    }

    // event listeners
    markReadButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const notiId = this.dataset.id;
            if (notiId) markAsRead(notiId, this);
        });
    });

    if (markAllBtn) {
        markAllBtn.addEventListener('click', function(e) {
            e.preventDefault();
            markAllAsRead();
        });
    }
})();
</script>
