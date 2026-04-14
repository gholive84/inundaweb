<?php
/**
 * alterar/publish.php — Publica as alterações da homepage (sem autenticação, demo)
 */
ini_set('display_errors', '0');
error_reporting(0);

require_once dirname(__DIR__) . '/cms/boot.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'error' => 'Método não permitido.']);
    exit;
}

$input   = json_decode(file_get_contents('php://input'), true) ?? [];
$content = $input['content'] ?? '';

if (trim($content) === '') {
    echo json_encode(['ok' => false, 'error' => 'Conteúdo vazio.']);
    exit;
}

$homepage = SITE_ROOT . '/index.php';

// Backup antes de sobrescrever
$backup_dir = SITE_ROOT . '/cms/paginas/backups/';
if (!is_dir($backup_dir)) @mkdir($backup_dir, 0755, true);
$backup = $backup_dir . 'index_' . date('Ymd_His') . '.php';
@copy($homepage, $backup);

if (file_put_contents($homepage, $content) === false) {
    echo json_encode(['ok' => false, 'error' => 'Não foi possível salvar o arquivo.']);
    exit;
}

echo json_encode(['ok' => true]);
