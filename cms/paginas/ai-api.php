<?php
// Suppress warnings/notices so they don't corrupt the JSON output
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

$input = json_decode(file_get_contents('php://input'), true) ?? [];
csrf_verify_json($input);

$page_id     = (int)($input['page_id'] ?? 0);
$instruction = trim($input['instruction'] ?? '');

if (!$page_id || $instruction === '') {
    echo json_encode(['ok' => false, 'error' => 'Dados inválidos.']);
    exit;
}

// Load page
$pdo  = db();
$stmt = $pdo->prepare('SELECT * FROM paginas WHERE id = ? LIMIT 1');
$stmt->execute([$page_id]);
$pagina = $stmt->fetch();

if (!$pagina || !file_exists($pagina['file_path'])) {
    echo json_encode(['ok' => false, 'error' => 'Página não encontrada.']);
    exit;
}

$current_content = file_get_contents($pagina['file_path']);

// OpenAI settings
$api_key        = setting('openai_key', '');
$model          = setting('openai_model', 'gpt-4o');
$extra_context  = trim(setting('openai_context', ''));

// Auto-load site context file
$context_file = $_SERVER['DOCUMENT_ROOT'] . '/site/ai-context.txt';
$site_context = file_exists($context_file) ? trim(file_get_contents($context_file)) : '';

if (!$api_key) {
    echo json_encode(['ok' => false, 'error' => 'Chave OpenAI não configurada. Vá em Configurações → Inteligência Artificial.']);
    exit;
}

// Build prompt
$system_prompt = <<<PROMPT
Você é um desenvolvedor PHP/HTML especialista. Sua tarefa é modificar o código de uma página PHP de um site chamado Inunda IA, seguindo exatamente a instrução do usuário.

REGRAS OBRIGATÓRIAS:
1. Retorne APENAS o código PHP modificado, sem nenhuma explicação, sem markdown, sem blocos de código.
2. Mantenha toda a estrutura PHP existente (includes, variáveis \$root, \$page_title, etc.)
3. Preserve os includes do header e footer no início e fim
4. Apenas modifique o que o usuário pediu, sem alterar o restante
5. O código deve ser válido PHP/HTML
6. Para carrosséis/sliders: SEMPRE inclua o CDN do Swiper, a estrutura HTML completa E o script `new Swiper(...)` de inicialização. Sem o script de inicialização o carrossel não funciona.

Identidade visual do site:
- Cor primária: #22d3ee (ciano)
- Fundo escuro: #0F172A
- Gradiente: linear-gradient(135deg, #22d3ee, #06b6d4)
- Fonte: Inter
- Botões: .btn--gradient, .btn--ghost, .btn--accent

PROMPT;

// Append site context from ai-context.txt (design tokens, CSS classes, available libs)
if ($site_context !== '') {
    $system_prompt .= "\n\n" . $site_context;
}

// Append managed JS libraries (injected in site head/footer — AI must know they're available)
$site_libs = json_decode(setting('site_libraries', '[]'), true) ?: [];
if (!empty($site_libs)) {
    $lib_ctx = "\n\n## Bibliotecas JS carregadas globalmente no site (disponíveis em todas as páginas)\n";
    foreach ($site_libs as $lib) {
        $lib_ctx .= "- **{$lib['name']}**" . (!empty($lib['desc']) ? ": {$lib['desc']}" : '') . "\n";
        if (!empty($lib['css'])) $lib_ctx .= "  CSS CDN: {$lib['css']}\n";
        if (!empty($lib['js']))  $lib_ctx .= "  JS CDN: {$lib['js']}\n";
    }
    $system_prompt .= $lib_ctx;
}

// Append additional context from CMS settings (user-defined overrides/complements)
if ($extra_context !== '') {
    $system_prompt .= "\n\n## Contexto adicional (configurado pelo usuário):\n" . $extra_context;
}

$user_prompt = "Instrução do usuário: {$instruction}\n\nArquivo PHP atual:\n{$current_content}";

// Call OpenAI
$payload = [
    'model'       => $model,
    'messages'    => [
        ['role' => 'system', 'content' => $system_prompt],
        ['role' => 'user',   'content' => $user_prompt],
    ],
    'temperature' => 0.2,
    'max_tokens'  => 16000,
];

// Aumenta tempo de execução PHP para requests longos
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
    CURLOPT_SSL_VERIFYPEER  => true,
]);

$response  = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_err  = curl_error($ch);
curl_close($ch);

if ($response === false || $response === '') {
    echo json_encode(['ok' => false, 'error' => 'cURL falhou: ' . ($curl_err ?: 'sem resposta da API. Verifique a chave OpenAI.')]);
    exit;
}

$result = json_decode($response, true);

if ($http_code !== 200 || empty($result['choices'][0]['message']['content'])) {
    $err = $result['error']['message'] ?? 'Resposta inválida da API.';
    echo json_encode(['ok' => false, 'error' => "OpenAI: $err"]);
    exit;
}

$modified_content = trim($result['choices'][0]['message']['content']);

// Strip markdown code blocks if AI returned them anyway
$modified_content = preg_replace('/^```php\s*/i', '', $modified_content);
$modified_content = preg_replace('/^```\s*/i', '', $modified_content);
$modified_content = preg_replace('/\s*```$/i', '', $modified_content);
$modified_content = trim($modified_content);

// Save to temp session for preview (session already started by boot.php)
if (session_status() === PHP_SESSION_NONE) session_start();
$token = bin2hex(random_bytes(16));
$_SESSION['ai_preview'][$token] = [
    'content'   => $modified_content,
    'page_id'   => $page_id,
    'file_path' => $pagina['file_path'],
    'expires'   => time() + 3600,
];

echo json_encode([
    'ok'            => true,
    'preview_token' => $token,
    'message'       => 'Alterações geradas! Confira o preview à direita e publique se estiver satisfeito.',
    'content'       => $modified_content,
]);

// ── Helper: CSRF verify from JSON ──
function csrf_verify_json(array $input): void {
    $token = $input['csrf_token'] ?? '';
    if (session_status() === PHP_SESSION_NONE) session_start();
    $expected = $_SESSION['csrf_token'] ?? '';
    if (!$expected || !hash_equals($expected, $token)) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Token inválido.']);
        exit;
    }
}
