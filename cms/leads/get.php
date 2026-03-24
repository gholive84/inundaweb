<?php
require_once dirname(__DIR__) . '/boot.php';
auth_check();

header('Content-Type: application/json; charset=utf-8');

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    echo json_encode(['ok' => false, 'error' => 'ID inválido.']);
    exit;
}

$stmt = db()->prepare('SELECT * FROM leads WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$lead = $stmt->fetch();

if (!$lead) {
    echo json_encode(['ok' => false, 'error' => 'Lead não encontrado.']);
    exit;
}

echo json_encode(['ok' => true, 'lead' => $lead]);
