<?php
require_once __DIR__ . '/includes/auth.php';
$cu = auth_current_user();
$db = get_db();

$username = trim($_GET['u'] ?? '');
if (!$username) { header('Location: /explore.php'); exit; }

$stmt = $db->prepare('SELECT * FROM users WHERE username=?');
$stmt->bindValue(1, $username);
$profile = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
if (!$profile) { http_response_code(404); die('User not found.'); }

$page_title = $profile['display_name'];

$logCount      = $db->querySingle('SELECT COUNT(*) FROM dive_logs WHERE user_id=' . (int)$profile['id']);
$followerCount = $db->querySingle('SELECT COUNT(*) FROM follows WHERE followee_id=' . (int)$profile['id']);
$followingCount= $db->querySingle('SELECT COUNT(*) FROM follows WHERE follower_id=' . (int)$profile['id']);
$iFollow = $cu ? ($db->querySingle(
    'SELECT COUNT(*) FROM follows WHERE follower_id=' . (int)$cu['id'] . ' AND followee_id=' . (int)$profile['id']
) > 0) : false;
$isOwn = $cu && $cu['id'] === (int)$profile['id'];

// Logs
$res = $db->prepare(
    'SELECT dl.*, u.username, u.display_name,
            (SELECT COUNT(*) FROM likes WHERE log_id=dl.id) as like_count,
            ' . ($cu ? '(SELECT COUNT(*) FROM likes WHERE log_id=dl.id AND user_id=' . (int)$cu['id'] . ')' : '0') . ' as i_liked
     FROM dive_logs dl JOIN users u ON u.id=dl.user_id
     WHERE dl.user_id=? ORDER BY dl.created_at DESC'
);
$res->bindValue(1, (int)$profile['id'], SQLITE3_INTEGER);
$logs = [];
$r = $res->execute();
while ($row = $r->fetchArray(SQLITE3_ASSOC)) $logs[] = $row;

require __DIR__ . '/includes/header.php';
?>
<div class="profile-hero">
  <div class="profile-hero-inner">
    <div class="profile-top">
      <span class="avatar-circle-lg" style="width:72px;height:72px;font-size:1.8rem"><?= h(mb_strtoupper(mb_substr($profile['display_name'],0,1))) ?></span>
      <div class="profile-info">
        <div class="profile-name"><?= h($profile['display_name']) ?></div>
        <div class="profile-username">@<?= h($profile['username']) ?><?= $profile['cert_level'] ? ' · ' . h($profile['cert_level']) : '' ?></div>
        <?php if ($profile['bio']): ?>
          <div class="profile-bio"><?= h($profile['bio']) ?></div>
        <?php endif; ?>
        <?php if ($profile['location']): ?>
          <div class="profile-location">📍 <?= h($profile['location']) ?></div>
        <?php endif; ?>
      </div>
      <div style="display:flex;flex-direction:column;gap:.5rem;align-self:flex-start;padding-top:.25rem">
        <?php if ($isOwn): ?>
          <a href="/settings.php" class="btn btn-secondary btn-sm">Edit Profile</a>
        <?php elseif ($cu): ?>
          <button class="btn btn-sm follow-btn <?= $iFollow ? 'btn-following' : 'btn-follow' ?>"
                  data-user-id="<?= (int)$profile['id'] ?>">
            <?= $iFollow ? 'Following' : 'Follow' ?>
          </button>
        <?php else: ?>
          <a href="/login.php" class="btn btn-follow btn-sm">Follow</a>
        <?php endif; ?>
      </div>
    </div>
    <div class="profile-stats">
      <div class="stat-item"><div class="stat-value"><?= (int)$profile['dive_count'] ?></div><div class="stat-label">Dives</div></div>
      <div class="stat-item"><div class="stat-value"><?= (int)$logCount ?></div><div class="stat-label">Logs</div></div>
      <div class="stat-item"><div class="stat-value follower-count"><?= (int)$followerCount ?></div><div class="stat-label">Followers</div></div>
      <div class="stat-item"><div class="stat-value"><?= (int)$followingCount ?></div><div class="stat-label">Following</div></div>
    </div>
  </div>
</div>

<div class="container" style="padding-top:2rem">
  <?php if ($isOwn): ?>
    <div style="margin-bottom:1.5rem"><a href="/log_new.php" class="btn btn-primary">+ Log a Dive</a></div>
  <?php endif; ?>
  <?php if (empty($logs)): ?>
    <div class="empty-state">
      <div class="empty-icon">🐠</div>
      <h3>No dive logs yet</h3>
      <?php if ($isOwn): ?><p><a href="/log_new.php">Log your first dive</a></p><?php endif; ?>
    </div>
  <?php else: ?>
    <?php foreach ($logs as $log): ?>
      <?php include __DIR__ . '/includes/log_card.php'; ?>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
