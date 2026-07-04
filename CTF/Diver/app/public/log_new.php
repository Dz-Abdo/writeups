<?php
$page_title = 'Log a Dive';
require_once __DIR__ . '/includes/auth.php';
$cu = auth_require_login();
$db = get_db();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title    = trim($_POST['title']    ?? '');
    $body     = trim($_POST['body']     ?? '');
    $location = trim($_POST['location'] ?? '');
    $depth    = (float)($_POST['depth_m']      ?? 0);
    $duration = (int)($_POST['duration_min']   ?? 0);
    $vis      = trim($_POST['visibility']       ?? '');

    if (strlen($title) < 3)  $error = 'Title must be at least 3 characters.';
    elseif (strlen($body) < 10) $error = 'Log entry must be at least 10 characters.';
    else {
        $stmt = $db->prepare(
            'INSERT INTO dive_logs (user_id,title,body,location,depth_m,duration_min,visibility) VALUES (?,?,?,?,?,?,?)'
        );
        $stmt->bindValue(1, $cu['id'],  SQLITE3_INTEGER);
        $stmt->bindValue(2, $title);
        $stmt->bindValue(3, $body);
        $stmt->bindValue(4, $location);
        $stmt->bindValue(5, $depth,    SQLITE3_FLOAT);
        $stmt->bindValue(6, $duration, SQLITE3_INTEGER);
        $stmt->bindValue(7, $vis);
        $stmt->execute();
        $newId = $db->lastInsertRowID();
        // Update dive count
        $db->prepare('UPDATE users SET dive_count=dive_count+1 WHERE id=?')
           ->bindValue(1, $cu['id'], SQLITE3_INTEGER);
        $db->exec('UPDATE users SET dive_count=dive_count+1 WHERE id=' . (int)$cu['id']);
        header('Location: /log.php?id=' . $newId);
        exit;
    }
}
require __DIR__ . '/includes/header.php';
?>
<div class="container-narrow">
  <div class="page-header">
    <h1>Log a Dive</h1>
    <p>Document your underwater adventure.</p>
  </div>
  <?php if ($error): ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>
  <div class="card">
    <div class="card-body">
      <form method="post">
        <div class="form-group">
          <label class="form-label" for="title">Dive Title</label>
          <input class="form-input" type="text" id="title" name="title" required maxlength="120"
                 value="<?= h($_POST['title'] ?? '') ?>" placeholder="Manta Night at Lanai Point">
        </div>
        <div class="form-group">
          <label class="form-label" for="location">Location</label>
          <input class="form-input" type="text" id="location" name="location" maxlength="120"
                 value="<?= h($_POST['location'] ?? '') ?>" placeholder="North Malé Atoll, Maldives">
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="depth_m">Max Depth (m)</label>
            <input class="form-input" type="number" id="depth_m" name="depth_m" min="0" max="400" step="0.5"
                   value="<?= h($_POST['depth_m'] ?? '') ?>" placeholder="18">
          </div>
          <div class="form-group">
            <label class="form-label" for="duration_min">Duration (min)</label>
            <input class="form-input" type="number" id="duration_min" name="duration_min" min="0" max="999"
                   value="<?= h($_POST['duration_min'] ?? '') ?>" placeholder="60">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label" for="visibility">Visibility</label>
          <select class="form-select" id="visibility" name="visibility">
            <option value="">— select —</option>
            <?php foreach (['Poor (<3m)','Moderate (8m)','Good (12m)','Very Good (18m)','Excellent (20m+)','Exceptional (30m+)'] as $v): ?>
              <option value="<?= h($v) ?>" <?= ($_POST['visibility'] ?? '') === $v ? 'selected' : '' ?>><?= h($v) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label" for="body">Dive Log Entry</label>
          <textarea class="form-textarea" id="body" name="body" required rows="10"
                    placeholder="Describe your dive — what you saw, conditions, highlights…"><?= h($_POST['body'] ?? '') ?></textarea>
        </div>
        <div style="display:flex;gap:.75rem">
          <button type="submit" class="btn btn-primary">Save Log</button>
          <a href="/feed.php" class="btn btn-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
