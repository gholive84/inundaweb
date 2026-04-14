<?php
/**
 * alterar/transcribe.php — Transcrição de áudio via Whisper (sem autenticação, demo)
 */
ini_set('display_errors', '0');
error_reporting(0);

require_once dirname(__DIR__) . '/cms/boot.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'error' => 'Método não permitido.']);
    exit;
}

$api_key = setting('openai_key', '');
if (!$api_key) {
    echo json_encode(['ok' => false, 'error' => 'Chave OpenAI não configurada.']);
    exit;
}

if (empty($_FILES['audio']) || $_FILES['audio']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['ok' => false, 'error' => 'Nenhum áudio recebido.']);
    exit;
}

$tmp_path  = $_FILES['audio']['tmp_name'];
$orig_name = $_FILES['audio']['name'] ?? 'recording.webm';
$file_size = $_FILES['audio']['size'] ?? 0;

if ($file_size > 25 * 1024 * 1024) {
    echo json_encode(['ok' => false, 'error' => 'Arquivo muito grande (máx 25MB).']);
    exit;
}
if ($file_size < 100) {
    echo json_encode(['ok' => false, 'error' => 'Gravação muito curta. Tente novamente.']);
    exit;
}

$ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION)) ?: 'webm';
if (!in_array($ext, ['webm','ogg','mp3','mp4','m4a','wav','flac'], true)) $ext = 'webm';

$named_tmp = sys_get_temp_dir() . '/demo_audio_' . bin2hex(random_bytes(6)) . '.' . $ext;
move_uploaded_file($tmp_path, $named_tmp);

$ch = curl_init('https://api.openai.com/v1/audio/transcriptions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $api_key],
    CURLOPT_POSTFIELDS     => [
        'file'            => new CURLFile($named_tmp, 'audio/' . $ext, 'recording.' . $ext),
        'model'           => 'whisper-1',
        'language'        => 'pt',
        'response_format' => 'json',
    ],
    CURLOPT_TIMEOUT        => 60,
    CURLOPT_CONNECTTIMEOUT => 10,
]);

$response  = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_err  = curl_error($ch);
curl_close($ch);
@unlink($named_tmp);

if (!$response) {
    echo json_encode(['ok' => false, 'error' => 'Erro de conexão: ' . $curl_err]);
    exit;
}

$result = json_decode($response, true);
if ($http_code !== 200 || empty($result['text'])) {
    echo json_encode(['ok' => false, 'error' => $result['error']['message'] ?? 'Falha na transcrição.']);
    exit;
}

echo json_encode(['ok' => true, 'text' => trim($result['text'])]);
