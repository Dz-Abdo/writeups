<?php
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');

$cu = auth_current_user();
if (!$cu) { echo json_encode(['status'=>'error','message'=>'unauthenticated']); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['status'=>'error','message'=>'method']); exit; }

$log_id = (int)($_POST['log_id'] ?? 0);
if (!$log_id) { echo json_encode(['status'=>'error','message'=>'missing parameter']); exit; }

$db = get_db();

// Verify log exists
$chk = $db->prepare('SELECT id FROM dive_logs WHERE id=?');
$chk->bindValue(1, $log_id, SQLITE3_INTEGER);
if (!$chk->execute()->fetchArray()) {
    echo json_encode(['status'=>'error','message'=>'not found']); exit;
}

$existing = $db->querySingle(
    'SELECT id FROM likes WHERE user_id=' . (int)$cu['id'] . ' AND log_id=' . $log_id
);

if ($existing) {
    $db->exec('DELETE FROM likes WHERE user_id=' . (int)$cu['id'] . ' AND log_id=' . $log_id);
    $liked = false;
} else {
    $ins = $db->prepare('INSERT OR IGNORE INTO likes (user_id,log_id) VALUES (?,?)');
    $ins->bindValue(1, $cu['id'],  SQLITE3_INTEGER);
    $ins->bindValue(2, $log_id, SQLITE3_INTEGER);
    $ins->execute();
    $liked = true;
}

$count = $db->querySingle('SELECT COUNT(*) FROM likes WHERE log_id=' . $log_id);
echo json_encode(['status'=>'ok','liked'=>$liked,'count'=>(int)$count]);
