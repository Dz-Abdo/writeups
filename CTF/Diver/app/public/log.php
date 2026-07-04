<?php
require_once __DIR__ . '/includes/auth.php';
$cu = auth_current_user();
$db = get_db();

$id = (int)($_GET['id'] ?? 0);
$stmt = $db->prepare(
    'SELECT dl.*, u.username, u.display_name, u.bio, u.cert_level,
            (SELECT COUNT(*) FROM likes WHERE log_id=dl.id) as like_count,
            ' . ($cu ? '(SELECT COUNT(*) FROM likes WHERE log_id=dl.id AND user_id=' . (int)$cu['id'] . ')' : '0') . ' as i_liked
     FROM dive_logs dl JOIN users u ON u.id=dl.user_id
     WHERE dl.id=?'
);
$stmt->bindValue(1, $id, SQLITE3_INTEGER);
$log = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
if (!$log) { http_response_code(404); die('Log not found.'); }

$page_title = $log['title'];
require __DIR__ . '/includes/header.php';
?>
<div class="container-narrow">
  <div style="margin-bottom:1.5rem">
    <a href="javascript:history.back()" class="text-muted text-sm">← Back</a>
  </div>

  <div class="card">
    <div class="card-body">
      <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1.25rem">
        <a href="/profile.php?u=<?= h($log['username']) ?>">
          <span class="avatar-circle-lg"><?= h(mb_strtoupper(mb_substr($log['display_name'],0,1))) ?></span>
        </a>
        <div>
          <div style="font-weight:700"><a href="/profile.php?u=<?= h($log['username']) ?>" style="color:inherit"><?= h($log['display_name']) ?></a></div>
          <div class="text-muted text-sm">@<?= h($log['username']) ?> · <?= h($log['cert_level']) ?></div>
        </div>
        <span class="text-muted text-sm" style="margin-left:auto"><?= time_ago($log['created_at']) ?></span>
      </div>

      <h1 style="font-size:1.65rem;font-weight:700;margin-bottom:1rem"><?= h($log['title']) ?></h1>

      <div class="log-card-meta" style="margin-bottom:1.25rem">
        <?php if ($log['location']): ?><span class="meta-tag">📍 <?= h($log['location']) ?></span><?php endif; ?>
        <?php if ($log['depth_m'] > 0): ?><span class="meta-tag">⬇ <?= format_depth($log['depth_m']) ?></span><?php endif; ?>
        <?php if ($log['duration_min'] > 0): ?><span class="meta-tag">⏱ <?= format_duration($log['duration_min']) ?></span><?php endif; ?>
        <?php if ($log['visibility']): ?><span class="meta-tag">👁 <?= h($log['visibility']) ?></span><?php endif; ?>
      </div>

      <div style="font-size:.975rem;line-height:1.75;color:var(--text-secondary)">
        <?= nl2br(h($log['body'])) ?>
      </div>

      <div class="log-card-actions" style="margin-top:1.5rem">
        <?php if ($cu): ?>
          <button class="like-btn <?= $log['i_liked'] ? 'liked' : '' ?>" data-log-id="<?= (int)$log['id'] ?>">
            <span class="heart"><?= $log['i_liked'] ? '❤️' : '🤍' ?></span>
            <span class="like-count"><?= (int)$log['like_count'] ?></span> likes
          </button>
        <?php else: ?>
          <span>🤍 <?= (int)$log['like_count'] ?> likes</span>
        <?php endif; ?>

        <?php if ($cu && $cu['id'] === (int)$log['user_id']): ?>
          <a href="/log_edit.php?id=<?= (int)$log['id'] ?>" class="btn btn-secondary btn-sm" style="margin-left:auto">Edit</a>
          <form method="post" action="/log_delete.php" style="display:inline" onsubmit="return confirm('Delete this log?')">
            <input type="hidden" name="id" value="<?= (int)$log['id'] ?>">
            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
