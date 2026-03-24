<?php
require_once dirname(__DIR__) . '/boot.php';
auth_check();

header('Content-Type: application/json; charset=utf-8');

$page_id = (int)($_GET['id'] ?? 0);
if (!$page_id) {
    echo json_encode(['ok' => false, 'error' => 'ID inválido.']);
    exit;
}

try {
    $pdo  = db();
    $stmt = $pdo->prepare(
        'SELECT id, saved_at FROM paginas_history WHERE pagina_id = ? ORDER BY saved_at DESC LIMIT 20'
    );
    $stmt->execute([$page_id]);
    $rows = $stmt->fetchAll();

    $items = array_map(fn($r) => [
        'id'       => $r['id'],
        'saved_at' => (new DateTime($r['saved_at']))->format('d/m/Y H:i'),
    ], $rows);

    echo json_encode(['ok' => true, 'items' => $items]);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
