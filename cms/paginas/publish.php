<?php
require_once dirname(__DIR__) . '/boot.php';
auth_check();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'error' => 'Método não permitido.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];

$token    = $input['csrf_token'] ?? '';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
    echo json_encode(['ok' => false, 'error' => 'Token inválido.']);
    exit;
}

$page_id = (int)($input['page_id'] ?? 0);
$content = $input['content'] ?? '';

if (!$page_id || $content === '') {
    echo json_encode(['ok' => false, 'error' => 'Dados inválidos.']);
    exit;
}

$pdo  = db();
$stmt = $pdo->prepare('SELECT * FROM paginas WHERE id = ? LIMIT 1');
$stmt->execute([$page_id]);
$pagina = $stmt->fetch();

if (!$pagina || !file_exists($pagina['file_path'])) {
    echo json_encode(['ok' => false, 'error' => 'Página não encontrada.']);
    exit;
}

$file_path = $pagina['file_path'];

// Save current content to history BEFORE overwriting
$current_content = file_get_contents($file_path);
try {
    $pdo->prepare(
        'INSERT INTO paginas_history (pagina_id, file_path, content, saved_by) VALUES (?, ?, ?, ?)'
    )->execute([$page_id, $file_path, $current_content, auth_user()['id'] ?? null]);
} catch (Exception $e) {
    // history table might not exist yet — continue anyway
}

// Write new content
$written = file_put_contents($file_path, $content);
if ($written === false) {
    echo json_encode(['ok' => false, 'error' => 'Sem permissão para escrever o arquivo. Verifique as permissões do servidor.']);
    exit;
}

echo json_encode(['ok' => true]);
