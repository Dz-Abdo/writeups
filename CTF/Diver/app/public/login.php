<?php
$page_title = 'Log In';
require_once __DIR__ . '/includes/auth.php';
if (auth_current_user()) { header('Location: /feed.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (auth_login($username, $password)) {
        header('Location: /feed.php'); exit;
    }
    $error = 'Invalid username or password.';
}
require __DIR__ . '/includes/header.php';
?>
<div class="container">
  <div class="form-card">
    <h1>Welcome back</h1>
    <p class="subtitle">Log in to see your feed and dive logs.</p>
    <?php if ($error): ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>
    <form method="post">
      <div class="form-group">
        <label class="form-label" for="username">Username</label>
        <input class="form-input" type="text" id="username" name="username"
               value="<?= h($_POST['username'] ?? '') ?>" required autofocus placeholder="your_username">
      </div>
      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <input class="form-input" type="password" id="password" name="password" required placeholder="••••••••">
      </div>
      <button type="submit" class="btn btn-primary btn-full" style="margin-top:.5rem">Log In</button>
    </form>
    <p class="text-muted text-sm mt-2" style="text-align:center">
      No account? <a href="/register.php">Join Diver</a>
    </p>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
