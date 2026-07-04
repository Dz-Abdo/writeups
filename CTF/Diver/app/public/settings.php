<?php
$page_title = 'Edit Profile';
require_once __DIR__ . '/includes/auth.php';
$cu = auth_require_login();
$db = get_db();

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $display_name = trim($_POST['display_name'] ?? '');
    $bio          = trim($_POST['bio']          ?? '');
    $location     = trim($_POST['location']     ?? '');
    $cert_level   = trim($_POST['cert_level']   ?? '');
    $dive_count   = max(0, (int)($_POST['dive_count'] ?? 0));

    if (strlen($display_name) < 2 || strlen($display_name) > 60) {
        $error = 'Display name must be 2–60 characters.';
    } else {
        $upd = $db->prepare(
            'UPDATE users SET display_name=?,bio=?,location=?,cert_level=?,dive_count=? WHERE id=?'
        );
        $upd->bindValue(1, $display_name);
        $upd->bindValue(2, mb_substr($bio, 0, 300));
        $upd->bindValue(3, mb_substr($location, 0, 100));
        $upd->bindValue(4, mb_substr($cert_level, 0, 60));
        $upd->bindValue(5, $dive_count, SQLITE3_INTEGER);
        $upd->bindValue(6, $cu['id'],   SQLITE3_INTEGER);
        $upd->execute();
        $success = 'Profile updated.';
        $cu = auth_current_user();
    }
}
require __DIR__ . '/includes/header.php';
?>
<div class="container-narrow">
  <div class="page-header"><h1>Edit Profile</h1></div>
  <?php if ($error):   ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert alert-success"><?= h($success) ?></div><?php endif; ?>
  <div class="card">
    <div class="card-body">
      <form method="post">
        <div class="form-group">
          <label class="form-label">Display Name</label>
          <input class="form-input" type="text" name="display_name" required maxlength="60" value="<?= h($cu['display_name']) ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Bio <span class="text-muted">(max 300 chars)</span></label>
          <textarea class="form-textarea" name="bio" rows="4" maxlength="300"><?= h($cu['bio']) ?></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Location</label>
          <input class="form-input" type="text" name="location" maxlength="100" value="<?= h($cu['location']) ?>" placeholder="City, Country">
        </div>
        <div class="form-group">
          <label class="form-label">Certification Level</label>
          <input class="form-input" type="text" name="cert_level" maxlength="60" value="<?= h($cu['cert_level']) ?>" placeholder="e.g. PADI Advanced OW">
        </div>
        <div class="form-group">
          <label class="form-label">Total Dive Count</label>
          <input class="form-input" type="number" name="dive_count" min="0" max="99999" value="<?= (int)$cu['dive_count'] ?>">
        </div>
        <div style="display:flex;gap:.75rem;margin-top:.5rem">
          <button type="submit" class="btn btn-primary">Save Changes</button>
          <a href="/profile.php?u=<?= h($cu['username']) ?>" class="btn btn-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
