<?php
$page_title = 'Explore';
require_once __DIR__ . '/includes/auth.php';
$cu = auth_current_user();
$db = get_db();

$page = max(1, (int)($_GET['page'] ?? 1));
$per  = 12;
$off  = ($page - 1) * $per;

$total = $db->querySingle('SELECT COUNT(*) FROM dive_logs');
$pages = (int)ceil($total / $per);

$res = $db->prepare(
    'SELECT dl.id, dl.title, dl.body, dl.location, dl.depth_m, dl.duration_min, dl.visibility, dl.created_at,
            u.id as user_id, u.username, u.display_name,
            (SELECT COUNT(*) FROM likes WHERE log_id=dl.id) as like_count,
            ' . ($cu ? '(SELECT COUNT(*) FROM likes WHERE log_id=dl.id AND user_id=' . (int)$cu['id'] . ')' : '0') . ' as i_liked
     FROM dive_logs dl JOIN users u ON u.id=dl.user_id
     ORDER BY dl.created_at DESC LIMIT :lim OFFSET :off'
);
$res->bindValue(':lim', $per, SQLITE3_INTEGER);
$res->bindValue(':off', $off, SQLITE3_INTEGER);
$logs = [];
$r = $res->execute();
while ($row = $r->fetchArray(SQLITE3_ASSOC)) $logs[] = $row;

require __DIR__ . '/includes/header.php';
?>
<div class="container">
  <div class="page-header">
    <h1>Explore Dive Logs</h1>
    <p>Discover dives from the community around the world.</p>
  </div>
  <?php foreach ($logs as $log): ?>
    <?php include __DIR__ . '/includes/log_card.php'; ?>
  <?php endforeach; ?>

  <?php if ($pages > 1): ?>
  <div style="display:flex;gap:.5rem;justify-content:center;margin-top:2rem;flex-wrap:wrap">
    <?php for ($p = 1; $p <= $pages; $p++): ?>
      <a href="?page=<?= $p ?>" class="btn <?= $p === $page ? 'btn-primary' : 'btn-secondary' ?> btn-sm"><?= $p ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
