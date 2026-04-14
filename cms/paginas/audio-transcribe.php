<?php
/**
 * audio-transcribe.php
 * Recebe um arquivo de áudio via multipart e retorna a transcrição via OpenAI Whisper.
 */
ini_set('display_errors', '0');
error_reporting(0);

require_once dirname(__DIR__) . '/boot.php';
auth_check();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método não permitido.']);
    exit;
}

/* ── CSRF ── */
if (session_status() === PHP_SESSION_NONE) session_start();
$csrf_token = $_POST['csrf_token'] ?? '';
$expected   = $_SESSION['csrf_token'] ?? '';
if (!$expected || !hash_equals($expected, $csrf_token)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Token CSRF inválido.']);
    exit;
}

/* ── Chave OpenAI ── */
$api_key = setting('openai_key', '');
if (!$api_key) {
    echo json_encode(['ok' => false, 'error' => 'Chave OpenAI não configurada. Vá em Configurações → Inteligência Artificial.']);
    exit;
}

/* ── Arquivo de áudio ── */
if (empty($_FILES['audio']) || $_FILES['audio']['error'] !== UPLOAD_ERR_OK) {
    $upload_error = $_FILES['audio']['error'] ?? -1;
    echo json_encode(['ok' => false, 'error' => 'Nenhum áudio recebido (código ' . $upload_error . ').']);
    exit;
}

$tmp_path  = $_FILES['audio']['tmp_name'];
$orig_name = $_FILES['audio']['name'] ?? 'recording.webm';
$file_size = $_FILES['audio']['size'] ?? 0;

// Validação de tamanho (Whisper aceita até 25 MB)
if ($file_size > 25 * 1024 * 1024) {
    echo json_encode(['ok' => false, 'error' => 'Arquivo de áudio muito grande (máximo 25 MB).']);
    exit;
}

if ($file_size < 100) {
    echo json_encode(['ok' => false, 'error' => 'Gravação muito curta. Tente novamente.']);
    exit;
}

// Detecta extensão pelo nome original
$ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION)) ?: 'webm';
$allowed_exts = ['webm', 'ogg', 'mp3', 'mp4', 'm4a', 'wav', 'flac'];
if (!in_array($ext, $allowed_exts, true)) {
    $ext = 'webm'; // fallback seguro
}

// Cria cópia temporária com extensão correta (cURL precisa do nome do arquivo)
$named_tmp = sys_get_temp_dir() . '/jz_audio_' . bin2hex(random_bytes(6)) . '.' . $ext;
move_uploaded_file($tmp_path, $named_tmp);

/* ── Chamada à API Whisper ── */
$ch = curl_init('https://api.openai.com/v1/audio/transcriptions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer ' . $api_key,
    ],
    CURLOPT_POSTFIELDS => [
        'file'     => new CURLFile($named_tmp, 'audio/' . $ext, 'recording.' . $ext),
        'model'    => 'whisper-1',
        'language' => 'pt',   // Português — melhora a precisão
        'response_format' => 'json',
    ],
    CURLOPT_TIMEOUT        => 60,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => true,
]);

$response  = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_err  = curl_error($ch);
curl_close($ch);

// Remove arquivo temporário
@unlink($named_tmp);

if ($response === false || $response === '') {
    echo json_encode(['ok' => false, 'error' => 'Erro de conexão com OpenAI: ' . ($curl_err ?: 'sem resposta.')]);
    exit;
}

$result = json_decode($response, true);

if ($http_code !== 200 || empty($result['text'])) {
    $err = $result['error']['message'] ?? ('Falha na transcrição (HTTP ' . $http_code . ').');
    echo json_encode(['ok' => false, 'error' => $err]);
    exit;
}

echo json_encode([
    'ok'   => true,
    'text' => trim($result['text']),
]);
