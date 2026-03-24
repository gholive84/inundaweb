<?php
require_once dirname(__DIR__) . '/boot.php';
auth_check();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método não permitido.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];

$token = $input['csrf_token'] ?? '';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Token inválido.']);
    exit;
}

$nome     = trim($input['nome']     ?? '');
$email    = trim($input['email']    ?? '');
$telefone = trim($input['telefone'] ?? '');
$mensagem = trim($input['mensagem'] ?? '');
$status   = $input['status'] ?? 'novo';
$valid_statuses = ['novo','contactado','fechado','descartado'];
if (!in_array($status, $valid_statuses)) $status = 'novo';

if ($nome === '') {
    echo json_encode(['ok' => false, 'error' => 'Nome é obrigatório.']);
    exit;
}

$pdo  = db();
$stmt = $pdo->prepare(
    'INSERT INTO leads (nome, email, telefone, mensagem, status, origem) VALUES (?, ?, ?, ?, ?, ?)'
);
$stmt->execute([$nome, $email, $telefone, $mensagem, $status, 'cms-manual']);

$id = (int)$pdo->lastInsertId();
$lead = $pdo->prepare('SELECT * FROM leads WHERE id = ? LIMIT 1');
$lead->execute([$id]);
$row = $lead->fetch();

echo json_encode(['ok' => true, 'lead' => $row]);
