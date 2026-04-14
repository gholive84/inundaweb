<?php
/**
 * alterar/publish.php — Confirma publicação ou desfaz (restaura backup)
 * action=publish → no-op (já foi salvo pelo ai.php)
 * action=discard → restaura o backup indicado
 */
ini_set('display_errors', '0');
error_reporting(0);

require_once dirname(__DIR__) . '/cms/boot.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'error' => 'Método não permitido.']);
    exit;
}

$input  = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $input['action'] ?? 'publish';

if ($action === 'publish') {
    echo json_encode(['ok' => true]);
    exit;
}

if ($action === 'discard') {
    $backup_name = basename($input['backup'] ?? '');

    if (!$backup_name || !preg_match('/^index_\d{8}_\d{6}\.php$/', $backup_name)) {
        echo json_encode(['ok' => false, 'error' => 'Backup inválido.']);
        exit;
    }

    $backup_path = SITE_ROOT . '/cms/paginas/backups/' . $backup_name;
    $homepage    = SITE_ROOT . '/index.php';

    if (!file_exists($backup_path)) {
        echo json_encode(['ok' => false, 'error' => 'Arquivo de backup não encontrado.']);
        exit;
    }

    if (!copy($backup_path, $homepage)) {
        echo json_encode(['ok' => false, 'error' => 'Falha ao restaurar backup.']);
        exit;
    }

    echo json_encode(['ok' => true]);
    exit;
}

echo json_encode(['ok' => false, 'error' => 'Ação desconhecida.']);
