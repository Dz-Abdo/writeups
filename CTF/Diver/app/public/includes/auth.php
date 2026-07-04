<?php
require_once __DIR__ . '/db.php';

function auth_start_session(): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
}

function auth_current_user(): ?array {
    auth_start_session();
    if (empty($_SESSION['user_id'])) return null;
    $stmt = get_db()->prepare(
        'SELECT id,username,display_name,bio,location,cert_level,dive_count,created_at FROM users WHERE id=?'
    );
    $stmt->bindValue(1, (int)$_SESSION['user_id'], SQLITE3_INTEGER);
    $row = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
    return $row ?: null;
}

function auth_require_login(): array {
    $u = auth_current_user();
    if (!$u) { header('Location: /login.php'); exit; }
    return $u;
}

function auth_login(string $username, string $password): bool {
    $stmt = get_db()->prepare('SELECT id,password_hash FROM users WHERE username=?');
    $stmt->bindValue(1, $username);
    $row = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
    if (!$row || !password_verify($password, $row['password_hash'])) return false;
    auth_start_session();
    session_regenerate_id(true);
    $_SESSION['user_id'] = $row['id'];
    return true;
}

function auth_logout(): void {
    auth_start_session();
    $_SESSION = [];
    session_destroy();
}

function auth_register(string $username, string $email, string $password, string $display_name): bool|string {
    if (!preg_match('/^[a-z0-9_]{3,30}$/', $username))
        return 'Username must be 3–30 chars: lowercase letters, numbers, underscores.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        return 'Invalid email address.';
    if (strlen($password) < 8)
        return 'Password must be at least 8 characters.';
    if (strlen($display_name) < 2 || strlen($display_name) > 60)
        return 'Display name must be 2–60 characters.';
    $db = get_db();
    $chk = $db->prepare('SELECT id FROM users WHERE username=? OR email=?');
    $chk->bindValue(1, $username); $chk->bindValue(2, $email);
    if ($chk->execute()->fetchArray()) return 'Username or email already taken.';
    $ins = $db->prepare('INSERT INTO users (username,email,password_hash,display_name) VALUES (?,?,?,?)');
    $ins->bindValue(1, $username);
    $ins->bindValue(2, $email);
    $ins->bindValue(3, password_hash($password, PASSWORD_BCRYPT));
    $ins->bindValue(4, $display_name);
    $ins->execute();
    auth_start_session();
    session_regenerate_id(true);
    $_SESSION['user_id'] = $db->lastInsertRowID();
    return true;
}

function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function format_depth(float $m): string {
    return $m > 0 ? number_format($m, 1) . ' m' : '—';
}

function format_duration(int $min): string {
    return $min > 0 ? $min . ' min' : '—';
}

function time_ago(string $dt): string {
    $diff = time() - strtotime($dt);
    if ($diff < 60)     return 'just now';
    if ($diff < 3600)   return floor($diff/60) . 'm ago';
    if ($diff < 86400)  return floor($diff/3600) . 'h ago';
    if ($diff < 604800) return floor($diff/86400) . 'd ago';
    return date('M j, Y', strtotime($dt));
}
