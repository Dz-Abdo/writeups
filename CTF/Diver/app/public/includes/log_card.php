<?php
// Expects $log array with fields: id, title, body, location, depth_m, duration_min,
// visibility, created_at, username, display_name, like_count, i_liked
// $cu must be available (current user), or null for public view.
?>
<div class="log-card">
  <div class="log-card-header">
    <div class="log-card-user">
      <a href="/profile.php?u=<?= h($log['username']) ?>">
        <span class="avatar-circle"><?= h(mb_strtoupper(mb_substr($log['display_name'],0,1))) ?></span>
      </a>
      <div>
        <div class="log-card-username">
          <a href="/profile.php?u=<?= h($log['username']) ?>" style="color:inherit;text-decoration:none"><?= h($log['display_name']) ?></a>
        </div>
        <div style="font-size:.78rem;color:var(--text-muted)">@<?= h($log['username']) ?></div>
      </div>
    </div>
    <span class="log-card-date"><?= time_ago($log['created_at']) ?></span>
  </div>
  <div class="log-card-title"><a href="/log.php?id=<?= (int)$log['id'] ?>"><?= h($log['title']) ?></a></div>
  <div class="log-card-meta">
    <?php if (!empty($log['location'])): ?>
      <span class="meta-tag">📍 <?= h($log['location']) ?></span>
    <?php endif; ?>
    <?php if (!empty($log['depth_m']) && $log['depth_m'] > 0): ?>
      <span class="meta-tag">⬇ <?= format_depth((float)$log['depth_m']) ?></span>
    <?php endif; ?>
    <?php if (!empty($log['duration_min']) && $log['duration_min'] > 0): ?>
      <span class="meta-tag">⏱ <?= format_duration((int)$log['duration_min']) ?></span>
    <?php endif; ?>
    <?php if (!empty($log['visibility'])): ?>
      <span class="meta-tag">👁 <?= h($log['visibility']) ?></span>
    <?php endif; ?>
  </div>
  <div class="log-card-body"><?= nl2br(h(mb_substr($log['body'], 0, 280))) ?><?= mb_strlen($log['body']) > 280 ? '…' : '' ?></div>
  <div class="log-card-actions">
    <?php if (!empty($cu)): ?>
      <button class="like-btn <?= !empty($log['i_liked']) ? 'liked' : '' ?>" data-log-id="<?= (int)$log['id'] ?>">
        <span class="heart"><?= !empty($log['i_liked']) ? '❤️' : '🤍' ?></span>
        <span class="like-count"><?= (int)$log['like_count'] ?></span>
      </button>
    <?php else: ?>
      <span class="like-btn" style="cursor:default">🤍 <span class="like-count"><?= (int)$log['like_count'] ?></span></span>
    <?php endif; ?>
    <a href="/log.php?id=<?= (int)$log['id'] ?>" class="log-link">Read log →</a>
  </div>
</div>
