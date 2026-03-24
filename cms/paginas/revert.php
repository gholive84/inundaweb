<?php
require_once dirname(__DIR__) . '/boot.php';
auth_check();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'error' => 'Método não permitido.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];

// CSRF
$token = $input['csrf_token'] ?? '';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
    echo json_encode(['ok' => false, 'error' => 'Token inválido.']);
    exit;
}

$history_id = (int)($input['history_id'] ?? 0);
$page_id    = (int)($input['page_id']    ?? 0);

if (!$history_id || !$page_id) {
    echo json_encode(['ok' => false, 'error' => 'Dados inválidos.']);
    exit;
}

$pdo = db();

// Load history entry
$stmt = $pdo->prepare('SELECT * FROM paginas_history WHERE id = ? AND pagina_id = ? LIMIT 1');
$stmt->execute([$history_id, $page_id]);
$hist = $stmt->fetch();

if (!$hist) {
    echo json_encode(['ok' => false, 'error' => 'Entrada de histórico não encontrada.']);
    exit;
}

// Get current page
$stmt2 = $pdo->prepare('SELECT * FROM paginas WHERE id = ? LIMIT 1');
$stmt2->execute([$page_id]);
$pagina = $stmt2->fetch();

if (!$pagina || !file_exists($pagina['file_path'])) {
    echo json_encode(['ok' => false, 'error' => 'Arquivo da página não encontrado.']);
    exit;
}

// Save current as new history entry before reverting
$current = file_get_contents($pagina['file_path']);
try {
    $pdo->prepare(
        'INSERT INTO paginas_history (pagina_id, file_path, content, saved_by) VALUES (?, ?, ?, ?)'
    )->execute([$page_id, $pagina['file_path'], $current, auth_user()['id'] ?? null]);
} catch (Exception $e) {}

// Write historic content back to file
$written = file_put_contents($pagina['file_path'], $hist['content']);
if ($written === false) {
    echo json_encode(['ok' => false, 'error' => 'Sem permissão para escrever o arquivo.']);
    exit;
}

echo json_encode(['ok' => true]);
