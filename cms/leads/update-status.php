<?php
require_once dirname(__DIR__) . '/boot.php';
auth_check();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

csrf_verify();

$id     = (int)($_POST['id'] ?? 0);
$status = $_POST['status'] ?? '';
$valid  = ['novo','contactado','fechado','descartado'];

if ($id <= 0 || !in_array($status, $valid)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Dados inválidos']);
    exit;
}

$stmt = db()->prepare('UPDATE leads SET status = ? WHERE id = ?');
$stmt->execute([$status, $id]);

echo json_encode(['ok' => true]);
