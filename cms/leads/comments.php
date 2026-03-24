<?php
require_once dirname(__DIR__) . '/boot.php';
auth_check();

header('Content-Type: application/json; charset=utf-8');

$pdo    = db();
$method = $_SERVER['REQUEST_METHOD'];

// Auto-create table if missing
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS lead_comments (
        id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        lead_id    INT UNSIGNED NOT NULL,
        content    TEXT NOT NULL,
        created_by INT UNSIGNED NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX (lead_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (Exception $e) {}

if ($method === 'GET') {
    $lead_id = (int)($_GET['lead_id'] ?? 0);
    if (!$lead_id) {
        echo json_encode(['ok' => false, 'error' => 'lead_id obrigatório.']);
        exit;
    }
    $stmt = $pdo->prepare(
        'SELECT lc.*, u.name as user_name
         FROM lead_comments lc
         LEFT JOIN cms_users u ON u.id = lc.created_by
         WHERE lc.lead_id = ?
         ORDER BY lc.created_at ASC'
    );
    $stmt->execute([$lead_id]);
    $comments = $stmt->fetchAll();
    echo json_encode(['ok' => true, 'comments' => $comments]);
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];

    $token = $input['csrf_token'] ?? '';
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Token inválido.']);
        exit;
    }

    $lead_id = (int)($input['lead_id'] ?? 0);
    $content = trim($input['content'] ?? '');

    if (!$lead_id || $content === '') {
        echo json_encode(['ok' => false, 'error' => 'Dados inválidos.']);
        exit;
    }

    $user_id = auth_user()['id'] ?? null;
    $stmt = $pdo->prepare(
        'INSERT INTO lead_comments (lead_id, content, created_by) VALUES (?, ?, ?)'
    );
    $stmt->execute([$lead_id, $content, $user_id]);

    $id = (int)$pdo->lastInsertId();
    $row = $pdo->prepare(
        'SELECT lc.*, u.name as user_name
         FROM lead_comments lc
         LEFT JOIN cms_users u ON u.id = lc.created_by
         WHERE lc.id = ?'
    );
    $row->execute([$id]);
    echo json_encode(['ok' => true, 'comment' => $row->fetch()]);
    exit;
}

http_response_code(405);
echo json_encode(['ok' => false, 'error' => 'Método não permitido.']);
