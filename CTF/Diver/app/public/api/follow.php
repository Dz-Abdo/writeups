<?php
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');

$cu = auth_current_user();
if (!$cu) { echo json_encode(['status'=>'error','message'=>'unauthenticated']); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['status'=>'error','message'=>'method']); exit; }

$target_id = (int)($_POST['user_id'] ?? 0);
if (!$target_id || $target_id === $cu['id']) {
    echo json_encode(['status'=>'error','message'=>'missing parameter']); exit;
}

$db = get_db();
$chk = $db->prepare('SELECT id FROM users WHERE id=?');
$chk->bindValue(1, $target_id, SQLITE3_INTEGER);
if (!$chk->execute()->fetchArray()) {
    echo json_encode(['status'=>'error','message'=>'not found']); exit;
}

$existing = $db->querySingle(
    'SELECT id FROM follows WHERE follower_id=' . (int)$cu['id'] . ' AND followee_id=' . $target_id
);

if ($existing) {
    $db->exec('DELETE FROM follows WHERE follower_id=' . (int)$cu['id'] . ' AND followee_id=' . $target_id);
    $following = false;
} else {
    $ins = $db->prepare('INSERT OR IGNORE INTO follows (follower_id,followee_id) VALUES (?,?)');
    $ins->bindValue(1, $cu['id'],    SQLITE3_INTEGER);
    $ins->bindValue(2, $target_id, SQLITE3_INTEGER);
    $ins->execute();
    $following = true;
}

$follower_count = $db->querySingle('SELECT COUNT(*) FROM follows WHERE followee_id=' . $target_id);
echo json_encode(['status'=>'ok','following'=>$following,'follower_count'=>(int)$follower_count]);
