<?php
require_once __DIR__ . '/auth.php';
$_cu = auth_current_user();
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= h($page_title ?? 'Diver') ?> — Diver</title>
<link rel="stylesheet" href="/css/app.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
</head>
<body>
<nav class="navbar">
  <div class="nav-inner">
    <a href="/" class="nav-brand"><span class="brand-icon">🤿</span><span class="brand-name">Diver</span></a>
    <div class="nav-links">
      <?php if ($_cu): ?>
        <a href="/feed.php"    class="nav-link">Feed</a>
        <a href="/explore.php" class="nav-link">Explore</a>
        <a href="/log_new.php" class="nav-link nav-link-cta">+ Log Dive</a>
        <a href="/profile.php?u=<?= h($_cu['username']) ?>" class="nav-link nav-avatar">
          <span class="avatar-circle"><?= h(mb_strtoupper(mb_substr($_cu['display_name'],0,1))) ?></span>
          <?= h($_cu['display_name']) ?>
        </a>
        <a href="/logout.php" class="nav-link nav-link-muted">Log out</a>
      <?php else: ?>
        <a href="/explore.php" class="nav-link">Explore</a>
        <a href="/login.php"   class="nav-link">Log in</a>
        <a href="/register.php" class="nav-link nav-link-cta">Join</a>
      <?php endif; ?>
    </div>
  </div>
</nav>
<main class="main-content">
