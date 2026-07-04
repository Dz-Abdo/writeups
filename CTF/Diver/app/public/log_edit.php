<?php
$page_title = 'Edit Log';
require_once __DIR__ . '/includes/auth.php';
$cu = auth_require_login();
$db = get_db();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$stmt = $db->prepare('SELECT * FROM dive_logs WHERE id=?');
$stmt->bindValue(1, $id, SQLITE3_INTEGER);
$log = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
if (!$log || (int)$log['user_id'] !== $cu['id']) {
    http_response_code(403); die('Not found or forbidden.');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title    = trim($_POST['title']       ?? '');
    $body     = trim($_POST['body']        ?? '');
    $location = trim($_POST['location']    ?? '');
    $depth    = (float)($_POST['depth_m']     ?? 0);
    $duration = (int)($_POST['duration_min']  ?? 0);
    $vis      = trim($_POST['visibility']      ?? '');

    if (strlen($title) < 3)   $error = 'Title must be at least 3 characters.';
    elseif (strlen($body) < 10) $error = 'Log entry must be at least 10 characters.';
    else {
        $upd = $db->prepare(
            'UPDATE dive_logs SET title=?,body=?,location=?,depth_m=?,duration_min=?,visibility=? WHERE id=? AND user_id=?'
        );
        $upd->bindValue(1, $title);
        $upd->bindValue(2, $body);
        $upd->bindValue(3, $location);
        $upd->bindValue(4, $depth,    SQLITE3_FLOAT);
        $upd->bindValue(5, $duration, SQLITE3_INTEGER);
        $upd->bindValue(6, $vis);
        $upd->bindValue(7, $id,       SQLITE3_INTEGER);
        $upd->bindValue(8, $cu['id'], SQLITE3_INTEGER);
        $upd->execute();
        header('Location: /log.php?id=' . $id);
        exit;
    }
    // Re-populate from POST on error
    $log = array_merge($log, ['title'=>$title,'body'=>$body,'location'=>$location,
                               'depth_m'=>$depth,'duration_min'=>$duration,'visibility'=>$vis]);
}
require __DIR__ . '/includes/header.php';
?>
<div class="container-narrow">
  <div class="page-header"><h1>Edit Log</h1></div>
  <?php if ($error): ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>
  <div class="card">
    <div class="card-body">
      <form method="post">
        <input type="hidden" name="id" value="<?= (int)$id ?>">
        <div class="form-group">
          <label class="form-label">Dive Title</label>
          <input class="form-input" type="text" name="title" required maxlength="120" value="<?= h($log['title']) ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Location</label>
          <input class="form-input" type="text" name="location" maxlength="120" value="<?= h($log['location']) ?>">
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Max Depth (m)</label>
            <input class="form-input" type="number" name="depth_m" min="0" max="400" step="0.5" value="<?= h($log['depth_m']) ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Duration (min)</label>
            <input class="form-input" type="number" name="duration_min" min="0" max="999" value="<?= h($log['duration_min']) ?>">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Visibility</label>
          <select class="form-select" name="visibility">
            <option value="">— select —</option>
            <?php foreach (['Poor (<3m)','Moderate (8m)','Good (12m)','Very Good (18m)','Excellent (20m+)','Exceptional (30m+)'] as $v): ?>
              <option value="<?= h($v) ?>" <?= $log['visibility'] === $v ? 'selected' : '' ?>><?= h($v) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Dive Log Entry</label>
          <textarea class="form-textarea" name="body" required rows="10"><?= h($log['body']) ?></textarea>
        </div>
        <div style="display:flex;gap:.75rem">
          <button type="submit" class="btn btn-primary">Save Changes</button>
          <a href="/log.php?id=<?= (int)$id ?>" class="btn btn-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
