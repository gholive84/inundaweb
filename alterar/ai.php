<?php
/**
 * alterar/ai.php — API de edição com IA (sem autenticação, demo)
 * Lê todos os arquivos de seção, envia para o AI, escreve os modificados.
 */
ini_set('display_errors', '0');
error_reporting(0);

require_once dirname(__DIR__) . '/cms/boot.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'error' => 'Método não permitido.']);
    exit;
}

$input       = json_decode(file_get_contents('php://input'), true) ?? [];
$instruction = trim($input['instruction'] ?? '');

if ($instruction === '') {
    echo json_encode(['ok' => false, 'error' => 'Instrução vazia.']);
    exit;
}

$api_key = setting('openai_key', '');
$model   = setting('openai_model', 'gpt-4o');

if (!$api_key) {
    echo json_encode(['ok' => false, 'error' => 'Chave OpenAI não configurada.']);
    exit;
}

// Arquivos que compõem a homepage (na ordem que aparecem na página)
$section_paths = [
    'site/includes/header.php',
    'site/sections/hero.php',
    'site/sections/services.php',
    'site/sections/about.php',
    'site/sections/portfolio.php',
    'site/sections/clients.php',
    'site/sections/contact.php',
    'site/sections/cta.php',
    'site/includes/footer.php',
];

$files = [];
foreach ($section_paths as $rel) {
    $full = SITE_ROOT . '/' . $rel;
    if (file_exists($full)) {
        $files[$rel] = file_get_contents($full);
    }
}

if (empty($files)) {
    echo json_encode(['ok' => false, 'error' => 'Arquivos de seção não encontrados.']);
    exit;
}

// Monta o contexto com todos os arquivos
$context = '';
foreach ($files as $path => $content) {
    $context .= "=== ARQUIVO: {$path} ===\n{$content}\n\n";
}

$system_prompt = <<<PROMPT
Você é um desenvolvedor PHP/HTML especialista trabalhando no site JZ Gráfica Digital.

O site é composto por múltiplos arquivos PHP de seção. Você receberá o conteúdo de todos eles e uma instrução de alteração.

REGRAS OBRIGATÓRIAS:
1. Identifique qual(is) arquivo(s) precisam ser modificados para cumprir a instrução.
2. Retorne APENAS os arquivos que foram modificados, no seguinte formato exato (sem markdown, sem explicações):

=== ARQUIVO: caminho/do/arquivo.php ===
[conteúdo completo do arquivo modificado]

=== ARQUIVO: outro/arquivo.php ===
[conteúdo completo do outro arquivo modificado]

3. Se nenhum arquivo precisar ser alterado, retorne: NENHUMA_ALTERACAO
4. Preserve toda a estrutura PHP existente (includes, variables, etc.)
5. O código deve ser PHP/HTML válido

Identidade visual JZ Gráfica:
- Cor primária: #EB4039 (vermelho)
- Fundo das seções: branco (#ffffff) e cinza claro (#F5F5F5)
- Fonte: Inter
- Botões: .btn--gradient (vermelho), .btn--ghost (transparente borda branca), .btn--white (branco)
- Gradiente: linear-gradient(135deg, #EB4039, #C9302B)
PROMPT;

$user_prompt = "Instrução: {$instruction}\n\nArquivos da homepage:\n\n{$context}";

$payload = [
    'model'       => $model,
    'messages'    => [
        ['role' => 'system', 'content' => $system_prompt],
        ['role' => 'user',   'content' => $user_prompt],
    ],
    'temperature' => 0.2,
    'max_tokens'  => 16000,
];

set_time_limit(180);

$ch = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key,
    ],
    CURLOPT_POSTFIELDS      => json_encode($payload),
    CURLOPT_TIMEOUT         => 150,
    CURLOPT_CONNECTTIMEOUT  => 15,
]);

$response  = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if (!$response) {
    echo json_encode(['ok' => false, 'error' => 'Sem resposta da API.']);
    exit;
}

$result = json_decode($response, true);
if ($http_code !== 200 || empty($result['choices'][0]['message']['content'])) {
    echo json_encode(['ok' => false, 'error' => $result['error']['message'] ?? 'Erro na API.']);
    exit;
}

$ai_response = trim($result['choices'][0]['message']['content']);

if ($ai_response === 'NENHUMA_ALTERACAO') {
    echo json_encode(['ok' => false, 'error' => 'A IA não identificou alterações necessárias. Tente ser mais específico.']);
    exit;
}

// Remove blocos de código markdown se o AI os incluir mesmo sendo instruído a não
$ai_response = preg_replace('/^```[a-z]*\s*/im', '', $ai_response);
$ai_response = preg_replace('/\s*```$/im', '', $ai_response);

// Parseia o retorno: separa por "=== ARQUIVO: path ==="
$modified_files = [];
$pattern = '/=== ARQUIVO:\s*([^\n=]+?)\s*===/';
preg_match_all($pattern, $ai_response, $matches, PREG_OFFSET_CAPTURE);

for ($i = 0; $i < count($matches[1]); $i++) {
    $file_rel  = trim($matches[1][$i][0]);
    $start_pos = $matches[0][$i][1] + strlen($matches[0][$i][0]);
    $end_pos   = isset($matches[0][$i + 1]) ? $matches[0][$i + 1][1] : strlen($ai_response);
    $content   = trim(substr($ai_response, $start_pos, $end_pos - $start_pos));

    // Valida que o arquivo está na lista permitida
    if (array_key_exists($file_rel, $files)) {
        $modified_files[$file_rel] = $content;
    }
}

if (empty($modified_files)) {
    echo json_encode(['ok' => false, 'error' => 'Não foi possível identificar os arquivos alterados na resposta da IA.']);
    exit;
}

// Backup e gravação dos arquivos modificados
$backup_dir = SITE_ROOT . '/cms/paginas/backups/';
if (!is_dir($backup_dir)) @mkdir($backup_dir, 0755, true);

$backup_stamp = date('Ymd_His');
$backed_up    = [];

foreach ($modified_files as $rel => $new_content) {
    $full_path   = SITE_ROOT . '/' . $rel;
    $backup_name = str_replace('/', '_', $rel) . '_' . $backup_stamp . '.bak';
    $backup_path = $backup_dir . $backup_name;

    @copy($full_path, $backup_path);
    $backed_up[] = $backup_name;

    file_put_contents($full_path, $new_content);

    // Invalida OPcache se disponível
    if (function_exists('opcache_invalidate')) {
        opcache_invalidate($full_path, true);
    }
}

$files_changed = implode(', ', array_keys($modified_files));

echo json_encode([
    'ok'      => true,
    'backups' => $backed_up,
    'files'   => array_keys($modified_files),
    'message' => 'Site atualizado! (' . count($modified_files) . ' arquivo(s) modificado(s)). Confira o preview e publique.',
]);
