<?php
require_once __DIR__ . '/includes/auth.php';
$cu = auth_require_login();
$db = get_db();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /feed.php'); exit;
}

$id = (int)($_POST['id'] ?? 0);
$stmt = $db->prepare('SELECT user_id FROM dive_logs WHERE id=?');
$stmt->bindValue(1, $id, SQLITE3_INTEGER);
$log = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

if ($log && (int)$log['user_id'] === $cu['id']) {
    $db->prepare('DELETE FROM likes WHERE log_id=?')->bindValue(1, $id, SQLITE3_INTEGER);
    $db->exec('DELETE FROM likes WHERE log_id=' . $id);
    $db->exec('DELETE FROM dive_logs WHERE id=' . $id);
    $db->exec('UPDATE users SET dive_count=MAX(0,dive_count-1) WHERE id=' . (int)$cu['id']);
}
header('Location: /profile.php?u=' . urlencode($cu['username']));
exit;
