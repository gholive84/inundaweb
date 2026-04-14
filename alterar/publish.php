<?php
/**
 * alterar/publish.php — Confirma ou desfaz alterações em arquivos de seção
 * action=publish → no-op (arquivos já foram salvos pelo ai.php)
 * action=discard → restaura os backups indicados
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
    $backups     = $input['backups'] ?? [];
    $files       = $input['files']   ?? [];
    $backup_dir  = SITE_ROOT . '/cms/paginas/backups/';

    if (empty($backups) || empty($files) || count($backups) !== count($files)) {
        echo json_encode(['ok' => false, 'error' => 'Dados de backup inválidos.']);
        exit;
    }

    $errors = [];
    foreach ($backups as $i => $backup_name) {
        // Valida nome do backup (evita path traversal)
        $backup_name = basename($backup_name);
        if (!preg_match('/^[\w\-]+\.bak$/', $backup_name)) {
            $errors[] = "Backup inválido: {$backup_name}";
            continue;
        }

        $backup_path = $backup_dir . $backup_name;
        $file_rel    = $files[$i] ?? '';

        // Valida que o arquivo destino está dentro do SITE_ROOT/site/
        $full_path = SITE_ROOT . '/' . $file_rel;
        $real      = realpath(SITE_ROOT . '/site');
        if (!$real || strpos(realpath(dirname($full_path)), $real) !== 0) {
            $errors[] = "Arquivo fora do escopo permitido: {$file_rel}";
            continue;
        }

        if (!file_exists($backup_path)) {
            $errors[] = "Backup não encontrado: {$backup_name}";
            continue;
        }

        if (!copy($backup_path, $full_path)) {
            $errors[] = "Falha ao restaurar: {$file_rel}";
        }

        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($full_path, true);
        }
    }

    if (!empty($errors)) {
        echo json_encode(['ok' => false, 'error' => implode('; ', $errors)]);
        exit;
    }

    echo json_encode(['ok' => true]);
    exit;
}

echo json_encode(['ok' => false, 'error' => 'Ação desconhecida.']);
