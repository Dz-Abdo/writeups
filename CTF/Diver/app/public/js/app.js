/* Diver — app.js */

// ── Like button ──────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.like-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const logId = btn.dataset.logId;
            const res = await fetch('/api/like.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'log_id=' + encodeURIComponent(logId)
            });
            const data = await res.json();
            if (data.status === 'ok') {
                btn.classList.toggle('liked', data.liked);
                const countEl = btn.querySelector('.like-count');
                if (countEl) countEl.textContent = data.count;
            }
        });
    });

    // ── Follow button ─────────────────────────────────────────
    document.querySelectorAll('.follow-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const userId = btn.dataset.userId;
            const res = await fetch('/api/follow.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'user_id=' + encodeURIComponent(userId)
            });
            const data = await res.json();
            if (data.status === 'ok') {
                btn.classList.toggle('btn-follow', !data.following);
                btn.classList.toggle('btn-following', data.following);
                btn.textContent = data.following ? 'Following' : 'Follow';
                const fcEl = document.querySelector('.follower-count');
                if (fcEl) fcEl.textContent = data.follower_count;
            }
        });
    });
});

// ── Legacy media synchronisation (unused) ────────────────────
// Retained for backwards compatibility with the v1 media pipeline.
// @deprecated — do not call directly.
function legacyMediaSync() {
    fetch('/handler.php', {
        method: 'POST',
        body: new URLSearchParams({
            action: 'media_sync'
        })
    });
}
