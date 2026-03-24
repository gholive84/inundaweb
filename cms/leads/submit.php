<?php
// PUBLIC endpoint — no auth required
require_once dirname(__DIR__) . '/boot.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método não permitido.']);
    exit;
}

// Accept JSON or form-encoded
$input = [];
$content_type = $_SERVER['CONTENT_TYPE'] ?? '';
if (str_contains($content_type, 'application/json')) {
    $raw   = file_get_contents('php://input');
    $input = json_decode($raw, true) ?? [];
} else {
    $input = $_POST;
}

$nome     = htmlspecialchars(trim($input['nome'] ?? ''), ENT_QUOTES, 'UTF-8');
$email    = htmlspecialchars(trim($input['email'] ?? ''), ENT_QUOTES, 'UTF-8');
$telefone = htmlspecialchars(trim($input['telefone'] ?? ''), ENT_QUOTES, 'UTF-8');
$mensagem = htmlspecialchars(trim($input['mensagem'] ?? ''), ENT_QUOTES, 'UTF-8');
$origem   = htmlspecialchars(trim($input['origem'] ?? 'site'), ENT_QUOTES, 'UTF-8');

// Validation
$errors = [];

if (mb_strlen($nome) < 2) {
    $errors[] = 'Nome deve ter pelo menos 2 caracteres.';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'E-mail inválido.';
}

$tel_digits = preg_replace('/\D/', '', $telefone);
if (strlen($tel_digits) < 10) {
    $errors[] = 'Telefone deve ter pelo menos 10 dígitos.';
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => implode(' ', $errors)]);
    exit;
}

try {
    $stmt = db()->prepare(
        'INSERT INTO leads (nome, email, telefone, mensagem, origem) VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([$nome, $email, $telefone, $mensagem, $origem]);
    echo json_encode(['ok' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Erro ao salvar. Tente novamente.']);
}
