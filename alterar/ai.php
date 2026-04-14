<?php
/**
 * alterar/ai.php — API de edição com IA (sem autenticação, demo)
 * Escreve direto no index.php após backup. Sem sessão.
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

$homepage = SITE_ROOT . '/index.php';
if (!file_exists($homepage)) {
    echo json_encode(['ok' => false, 'error' => 'Homepage não encontrada.']);
    exit;
}

$current_content = file_get_contents($homepage);

$api_key = setting('openai_key', '');
$model   = setting('openai_model', 'gpt-4o');

if (!$api_key) {
    echo json_encode(['ok' => false, 'error' => 'Chave OpenAI não configurada.']);
    exit;
}

$site_context = '';
$ctx_file = SITE_ROOT . '/site/ai-context.txt';
if (file_exists($ctx_file)) $site_context = trim(file_get_contents($ctx_file));

$system_prompt = <<<PROMPT
Você é um desenvolvedor PHP/HTML especialista. Sua tarefa é modificar o código da homepage de um site chamado JZ Gráfica Digital, seguindo exatamente a instrução do usuário.

REGRAS OBRIGATÓRIAS:
1. Retorne APENAS o código PHP modificado, sem nenhuma explicação, sem markdown, sem blocos de código.
2. Mantenha toda a estrutura PHP existente (includes, defines, variáveis, etc.)
3. Preserve os includes do header, sections e footer
4. Apenas modifique o que o usuário pediu, sem alterar o restante
5. O código deve ser PHP/HTML válido

Identidade visual do site JZ Cópias:
- Cor primária: #EB4039 (vermelho)
- Fundo das seções: branco (#ffffff) e cinza claro (#F5F5F5)
- Fonte: Inter
- Botões: .btn--gradient (vermelho), .btn--ghost (transparente borda branca), .btn--white (branco)
- Gradiente: linear-gradient(135deg, #EB4039, #C9302B)
PROMPT;

if ($site_context !== '') {
    $system_prompt .= "\n\n" . $site_context;
}

$user_prompt = "Instrução: {$instruction}\n\nArquivo PHP atual:\n{$current_content}";

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

$modified = trim($result['choices'][0]['message']['content']);
$modified = preg_replace('/^```php\s*/i', '', $modified);
$modified = preg_replace('/^```\s*/i',   '', $modified);
$modified = preg_replace('/\s*```$/i',   '', $modified);
$modified = trim($modified);

// Backup antes de sobrescrever
$backup_dir = SITE_ROOT . '/cms/paginas/backups/';
if (!is_dir($backup_dir)) @mkdir($backup_dir, 0755, true);
$backup_name = 'index_' . date('Ymd_His') . '.php';
$backup_path = $backup_dir . $backup_name;
@copy($homepage, $backup_path);

// Escreve direto no arquivo — o iframe vai carregar a página real
if (file_put_contents($homepage, $modified) === false) {
    echo json_encode(['ok' => false, 'error' => 'Não foi possível salvar o arquivo.']);
    exit;
}

echo json_encode([
    'ok'      => true,
    'backup'  => $backup_name,
    'message' => 'Site atualizado! Confira o preview ao lado. Se gostar, clique em Publicar — ou Desfazer para voltar.',
]);
