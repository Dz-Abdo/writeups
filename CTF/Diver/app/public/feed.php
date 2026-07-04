<?php
$page_title = 'Feed';
require_once __DIR__ . '/includes/auth.php';
$cu = auth_require_login();
$db = get_db();

// Logs from followed users + own logs
$res = $db->prepare(
    'SELECT dl.id, dl.title, dl.body, dl.location, dl.depth_m, dl.duration_min, dl.visibility, dl.created_at,
            u.id as user_id, u.username, u.display_name,
            (SELECT COUNT(*) FROM likes WHERE log_id=dl.id) as like_count,
            (SELECT COUNT(*) FROM likes WHERE log_id=dl.id AND user_id=:me) as i_liked
     FROM dive_logs dl JOIN users u ON u.id=dl.user_id
     WHERE dl.user_id=:me
        OR dl.user_id IN (SELECT followee_id FROM follows WHERE follower_id=:me)
     ORDER BY dl.created_at DESC LIMIT 40'
);
$res->bindValue(':me', $cu['id'], SQLITE3_INTEGER);
$logs = [];
$r = $res->execute();
while ($row = $r->fetchArray(SQLITE3_ASSOC)) $logs[] = $row;

// Suggested users to follow (not already following, not self)
$sug = $db->prepare(
    'SELECT id, username, display_name, cert_level FROM users
     WHERE id != :me AND id NOT IN (SELECT followee_id FROM follows WHERE follower_id=:me)
     ORDER BY RANDOM() LIMIT 4'
);
$sug->bindValue(':me', $cu['id'], SQLITE3_INTEGER);
$suggested = [];
$sr = $sug->execute();
while ($row = $sr->fetchArray(SQLITE3_ASSOC)) $suggested[] = $row;

require __DIR__ . '/includes/header.php';
?>
<div class="container">
  <div class="layout-2col">
    <div>
      <div class="page-header">
        <h1>Your Feed</h1>
        <p>Latest logs from divers you follow.</p>
      </div>
      <?php if (empty($logs)): ?>
        <div class="empty-state">
          <div class="empty-icon">🌊</div>
          <h3>Nothing here yet</h3>
          <p>Follow some divers or <a href="/log_new.php">log your first dive</a>.</p>
        </div>
      <?php endif; ?>
      <?php foreach ($logs as $log): ?>
        <?php include __DIR__ . '/includes/log_card.php'; ?>
      <?php endforeach; ?>
    </div>
    <aside>
      <div class="widget">
        <div class="widget-title">Your Profile</div>
        <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem">
          <span class="avatar-circle-lg"><?= h(mb_strtoupper(mb_substr($cu['display_name'],0,1))) ?></span>
          <div>
            <div style="font-weight:700"><?= h($cu['display_name']) ?></div>
            <div class="text-muted text-sm">@<?= h($cu['username']) ?></div>
          </div>
        </div>
        <a href="/profile.php?u=<?= h($cu['username']) ?>" class="btn btn-secondary btn-sm btn-full">View Profile</a>
        <a href="/log_new.php" class="btn btn-primary btn-sm btn-full" style="margin-top:.5rem">+ Log a Dive</a>
      </div>
      <?php if ($suggested): ?>
      <div class="widget">
        <div class="widget-title">Divers to Follow</div>
        <?php foreach ($suggested as $s): ?>
          <div class="suggest-user">
            <span class="avatar-circle"><?= h(mb_strtoupper(mb_substr($s['display_name'],0,1))) ?></span>
            <div class="suggest-info">
              <div class="suggest-name"><a href="/profile.php?u=<?= h($s['username']) ?>"><?= h($s['display_name']) ?></a></div>
              <div class="suggest-username"><?= h($s['cert_level']) ?></div>
            </div>
            <button class="btn btn-follow btn-sm follow-btn" data-user-id="<?= (int)$s['id'] ?>">Follow</button>
          </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </aside>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
