<?php
require_once __DIR__ . '/boot.php';
auth_check();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método não permitido.']);
    exit;
}

// CSRF via header or post field
$token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Token inválido.']);
    exit;
}

if (empty($_FILES['file'])) {
    echo json_encode(['ok' => false, 'error' => 'Nenhum arquivo enviado.']);
    exit;
}

$file  = $_FILES['file'];
$error = $file['error'] ?? UPLOAD_ERR_NO_FILE;

if ($error !== UPLOAD_ERR_OK) {
    $msgs = [
        UPLOAD_ERR_INI_SIZE   => 'Arquivo excede o limite do servidor.',
        UPLOAD_ERR_FORM_SIZE  => 'Arquivo muito grande.',
        UPLOAD_ERR_NO_TMP_DIR => 'Pasta temporária não encontrada.',
        UPLOAD_ERR_CANT_WRITE => 'Sem permissão para salvar.',
    ];
    echo json_encode(['ok' => false, 'error' => $msgs[$error] ?? 'Erro no upload.']);
    exit;
}

// Validate type
$allowed_mime = [
    'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml',
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
];
$mime = mime_content_type($file['tmp_name']);
if (!in_array($mime, $allowed_mime)) {
    echo json_encode(['ok' => false, 'error' => 'Tipo de arquivo não permitido: ' . $mime]);
    exit;
}

// Max 10 MB
if ($file['size'] > 10 * 1024 * 1024) {
    echo json_encode(['ok' => false, 'error' => 'Arquivo deve ter no máximo 10 MB.']);
    exit;
}

// Build destination
$ext       = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$basename  = preg_replace('/[^a-z0-9\-_]/i', '-', pathinfo($file['name'], PATHINFO_FILENAME));
$basename  = substr($basename, 0, 60);
$filename  = $basename . '-' . substr(md5(uniqid()), 0, 8) . '.' . $ext;
$year_month = date('Y-m');
$dir       = $_SERVER['DOCUMENT_ROOT'] . '/site/assets/uploads/' . $year_month;

if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

$dest = $dir . '/' . $filename;
if (!move_uploaded_file($file['tmp_name'], $dest)) {
    echo json_encode(['ok' => false, 'error' => 'Falha ao mover arquivo.']);
    exit;
}

$url = '/site/assets/uploads/' . $year_month . '/' . $filename;
echo json_encode(['ok' => true, 'url' => $url, 'name' => $file['name']]);
