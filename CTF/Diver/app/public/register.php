<?php
$page_title = 'Join Diver';
require_once __DIR__ . '/includes/auth.php';
if (auth_current_user()) { header('Location: /feed.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username     = trim($_POST['username']     ?? '');
    $email        = trim($_POST['email']        ?? '');
    $password     = $_POST['password']          ?? '';
    $display_name = trim($_POST['display_name'] ?? '');
    $result = auth_register($username, $email, $password, $display_name);
    if ($result === true) { header('Location: /feed.php'); exit; }
    $error = $result;
}
require __DIR__ . '/includes/header.php';
?>
<div class="container">
  <div class="form-card">
    <h1>Create your account</h1>
    <p class="subtitle">Join thousands of divers logging their underwater world.</p>
    <?php if ($error): ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>
    <form method="post" autocomplete="off">
      <div class="form-group">
        <label class="form-label" for="display_name">Display Name</label>
        <input class="form-input" type="text" id="display_name" name="display_name"
               value="<?= h($_POST['display_name'] ?? '') ?>" required maxlength="60" placeholder="Marina Voss">
      </div>
      <div class="form-group">
        <label class="form-label" for="username">Username</label>
        <input class="form-input" type="text" id="username" name="username"
               value="<?= h($_POST['username'] ?? '') ?>" required maxlength="30"
               pattern="[a-z0-9_]{3,30}" placeholder="marina_deep">
        <small class="text-muted">Lowercase letters, numbers, underscores. 3–30 chars.</small>
      </div>
      <div class="form-group">
        <label class="form-label" for="email">Email</label>
        <input class="form-input" type="email" id="email" name="email"
               value="<?= h($_POST['email'] ?? '') ?>" required placeholder="you@example.com">
      </div>
      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <input class="form-input" type="password" id="password" name="password" required minlength="8" placeholder="Min. 8 characters">
      </div>
      <button type="submit" class="btn btn-primary btn-full" style="margin-top:.5rem">Create Account</button>
    </form>
    <p class="text-muted text-sm mt-2" style="text-align:center">
      Already have an account? <a href="/login.php">Log in</a>
    </p>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
