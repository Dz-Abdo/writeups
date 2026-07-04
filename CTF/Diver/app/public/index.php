<?php
$page_title = 'Welcome';
require_once __DIR__ . '/includes/auth.php';
$cu = auth_current_user();
if ($cu) { header('Location: /feed.php'); exit; }

// Grab a few recent public logs for the landing preview
$db = get_db();
$res = $db->query(
    'SELECT dl.id, dl.title, dl.body, dl.location, dl.depth_m, dl.duration_min, dl.created_at,
            u.username, u.display_name
     FROM dive_logs dl JOIN users u ON u.id = dl.user_id
     ORDER BY dl.created_at DESC LIMIT 3'
);
$preview = [];
while ($row = $res->fetchArray(SQLITE3_ASSOC)) $preview[] = $row;

require __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <h1>Log your dives.<br>Share the deep.</h1>
  <p>Diver is the social logbook for scuba divers, freedivers, and underwater photographers. Document every dive, discover great spots, connect with the community.</p>
  <div class="hero-actions">
    <a href="/register.php" class="btn btn-primary" style="font-size:1.05rem;padding:.8rem 2rem;">Create free account</a>
    <a href="/explore.php"  class="btn btn-secondary" style="background:rgba(255,255,255,.12);color:#fff;border:1px solid rgba(255,255,255,.25);">Browse dive logs</a>
  </div>
</section>

<section class="features">
  <div class="features-inner">
    <h2>Everything divers need</h2>
    <div class="features-grid">
      <div class="feature-item">
        <div class="feature-icon">📓</div>
        <h3>Digital Dive Log</h3>
        <p>Record depth, duration, visibility, and your personal narrative for every dive.</p>
      </div>
      <div class="feature-item">
        <div class="feature-icon">🌊</div>
        <h3>Discover Sites</h3>
        <p>Browse logs from divers around the world and find your next underwater adventure.</p>
      </div>
      <div class="feature-item">
        <div class="feature-icon">🤝</div>
        <h3>Follow Divers</h3>
        <p>Follow fellow divers, like their logs, and build your underwater community.</p>
      </div>
      <div class="feature-item">
        <div class="feature-icon">🏅</div>
        <h3>Track Progress</h3>
        <p>See your dive count grow and showcase your certification level on your profile.</p>
      </div>
    </div>
  </div>
</section>

<section class="feed-preview">
  <div class="feed-preview-inner">
    <h2>Recent from the community</h2>
    <?php foreach ($preview as $log): ?>
    <div class="log-card">
      <div class="log-card-header">
        <div class="log-card-user">
          <span class="avatar-circle"><?= h(mb_strtoupper(mb_substr($log['display_name'],0,1))) ?></span>
          <div>
            <div class="log-card-username"><?= h($log['display_name']) ?></div>
            <div style="font-size:.78rem;color:var(--text-muted)">@<?= h($log['username']) ?></div>
          </div>
        </div>
        <span class="log-card-date"><?= time_ago($log['created_at']) ?></span>
      </div>
      <div class="log-card-title"><a href="/log.php?id=<?= (int)$log['id'] ?>"><?= h($log['title']) ?></a></div>
      <div class="log-card-meta">
        <?php if ($log['location']): ?><span class="meta-tag">📍 <?= h($log['location']) ?></span><?php endif; ?>
        <?php if ($log['depth_m'] > 0): ?><span class="meta-tag">⬇ <?= format_depth($log['depth_m']) ?></span><?php endif; ?>
        <?php if ($log['duration_min'] > 0): ?><span class="meta-tag">⏱ <?= format_duration($log['duration_min']) ?></span><?php endif; ?>
      </div>
      <div class="log-card-body"><?= nl2br(h(mb_substr($log['body'], 0, 240))) ?>…</div>
    </div>
    <?php endforeach; ?>
    <div style="text-align:center;margin-top:1.5rem;">
      <a href="/explore.php" class="btn btn-secondary">View all dive logs →</a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
