<?php
ini_set('display_errors', '0');
error_reporting(0);

require_once __DIR__ . '/boot.php';
auth_check();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método não permitido.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];
csrf_verify_json_chat($input);

$message = trim($input['message'] ?? '');
$history = $input['history'] ?? [];

if ($message === '') {
    echo json_encode(['ok' => false, 'error' => 'Mensagem vazia.']);
    exit;
}

$api_key = setting('openai_key', '');
$model   = setting('openai_model', 'gpt-4o');

if (!$api_key) {
    echo json_encode(['ok' => false, 'error' => 'Chave OpenAI não configurada. Vá em Configurações → IA.']);
    exit;
}

$pdo = db();

// ── Tool definitions ──────────────────────────────────────────────────────────
$tools = [
    [
        'type' => 'function',
        'function' => [
            'name'        => 'get_site_stats',
            'description' => 'Retorna estatísticas gerais do site: total de posts, páginas, leads, tipos de conteúdo e itens de conteúdo.',
            'parameters'  => ['type' => 'object', 'properties' => new stdClass()],
        ],
    ],
    [
        'type' => 'function',
        'function' => [
            'name'        => 'list_posts',
            'description' => 'Lista os posts do blog.',
            'parameters'  => [
                'type'       => 'object',
                'properties' => [
                    'limit'  => ['type' => 'integer', 'description' => 'Máximo de posts a retornar (padrão 10)'],
                    'status' => ['type' => 'string', 'enum' => ['all', 'published', 'draft'], 'description' => 'Filtrar por status'],
                ],
            ],
        ],
    ],
    [
        'type' => 'function',
        'function' => [
            'name'        => 'list_pages',
            'description' => 'Lista as páginas do site.',
            'parameters'  => ['type' => 'object', 'properties' => new stdClass()],
        ],
    ],
    [
        'type' => 'function',
        'function' => [
            'name'        => 'create_post',
            'description' => 'Cria um novo post no blog.',
            'parameters'  => [
                'type'       => 'object',
                'required'   => ['title', 'content'],
                'properties' => [
                    'title'     => ['type' => 'string', 'description' => 'Título do post'],
                    'excerpt'   => ['type' => 'string', 'description' => 'Resumo curto'],
                    'content'   => ['type' => 'string', 'description' => 'Conteúdo completo em HTML'],
                    'category'  => ['type' => 'string', 'description' => 'Categoria do post'],
                    'read_time' => ['type' => 'string', 'description' => 'Tempo de leitura ex: "5 min"'],
                    'status'    => ['type' => 'string', 'enum' => ['draft', 'published'], 'description' => 'Status do post'],
                ],
            ],
        ],
    ],
    [
        'type' => 'function',
        'function' => [
            'name'        => 'list_leads',
            'description' => 'Lista os leads/contatos recentes.',
            'parameters'  => [
                'type'       => 'object',
                'properties' => [
                    'limit'  => ['type' => 'integer', 'description' => 'Máximo de leads (padrão 10)'],
                    'status' => ['type' => 'string', 'description' => 'Filtrar por status'],
                ],
            ],
        ],
    ],
    [
        'type' => 'function',
        'function' => [
            'name'        => 'get_setting',
            'description' => 'Obtém o valor de uma configuração do site.',
            'parameters'  => [
                'type'       => 'object',
                'required'   => ['key'],
                'properties' => [
                    'key' => ['type' => 'string', 'description' => 'Chave da configuração (ex: site_title, header_menu, footer_menu)'],
                ],
            ],
        ],
    ],
    [
        'type' => 'function',
        'function' => [
            'name'        => 'update_setting',
            'description' => 'Atualiza uma configuração do site (ex: menus, título, etc.).',
            'parameters'  => [
                'type'       => 'object',
                'required'   => ['key', 'value'],
                'properties' => [
                    'key'   => ['type' => 'string', 'description' => 'Chave da configuração'],
                    'value' => ['type' => 'string', 'description' => 'Novo valor'],
                ],
            ],
        ],
    ],
];

// ── System prompt ─────────────────────────────────────────────────────────────
$system_prompt = <<<PROMPT
Você é o assistente de gestão do CMS Inunda IA. Você ajuda o administrador a gerenciar o site.

Você pode:
- Responder perguntas sobre o site (quantos posts, páginas, leads, etc.)
- Criar posts no blog
- Listar e consultar conteúdos existentes
- Consultar e atualizar configurações do site (como menus do header/footer)
- Fornecer orientações sobre como usar o CMS

Seja objetivo e direto. Quando executar uma ação, confirme o que foi feito e forneça um link quando aplicável.

URLs do CMS: {CMS_URL}
PROMPT;

$system_prompt = str_replace('{CMS_URL}', CMS_URL, $system_prompt);

// ── Build messages array ──────────────────────────────────────────────────────
$messages = [['role' => 'system', 'content' => $system_prompt]];

// Add sanitized history (only role + content)
foreach ($history as $h) {
    $role = in_array($h['role'] ?? '', ['user', 'assistant']) ? $h['role'] : null;
    $content = trim($h['content'] ?? '');
    if ($role && $content !== '') {
        $messages[] = ['role' => $role, 'content' => $content];
    }
}

$messages[] = ['role' => 'user', 'content' => $message];

// ── OpenAI call with tool loop ────────────────────────────────────────────────
set_time_limit(180);

$final_text = '';
$actions_done = [];

for ($iteration = 0; $iteration < 5; $iteration++) {
    $payload = [
        'model'       => $model,
        'messages'    => $messages,
        'tools'       => $tools,
        'tool_choice' => 'auto',
        'temperature' => 0.4,
        'max_tokens'  => 4000,
    ];

    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_key,
        ],
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_TIMEOUT        => 120,
        CURLOPT_CONNECTTIMEOUT => 15,
    ]);

    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_err  = curl_error($ch);
    curl_close($ch);

    if ($response === false || $http_code !== 200) {
        $err = 'Erro ao chamar a IA';
        if ($response) {
            $r = json_decode($response, true);
            $err = $r['error']['message'] ?? $err;
        }
        echo json_encode(['ok' => false, 'error' => $err . ($curl_err ? ": $curl_err" : '')]);
        exit;
    }

    $result  = json_decode($response, true);
    $choice  = $result['choices'][0] ?? null;
    $msg     = $choice['message'] ?? [];
    $finish  = $choice['finish_reason'] ?? '';

    // Add assistant message to history
    $messages[] = $msg;

    if ($finish === 'stop' || empty($msg['tool_calls'])) {
        $final_text = $msg['content'] ?? '';
        break;
    }

    // Process tool calls
    foreach ($msg['tool_calls'] as $tc) {
        $fn_name = $tc['function']['name'] ?? '';
        $fn_args = json_decode($tc['function']['arguments'] ?? '{}', true) ?: [];
        $tool_id = $tc['id'];

        $tool_result = execute_tool($fn_name, $fn_args, $pdo, $actions_done);

        $messages[] = [
            'role'         => 'tool',
            'tool_call_id' => $tool_id,
            'content'      => json_encode($tool_result, JSON_UNESCAPED_UNICODE),
        ];
    }
}

echo json_encode([
    'ok'      => true,
    'message' => $final_text ?: 'Concluído.',
    'actions' => $actions_done,
]);
exit;

// ── Tool execution ────────────────────────────────────────────────────────────
function execute_tool(string $name, array $args, PDO $pdo, array &$actions): array {
    switch ($name) {

        case 'get_site_stats':
            $stats = [];
            try {
                $stats['posts_total']      = (int)$pdo->query('SELECT COUNT(*) FROM posts')->fetchColumn();
                $stats['posts_published']  = (int)$pdo->query("SELECT COUNT(*) FROM posts WHERE status='published'")->fetchColumn();
                $stats['posts_draft']      = (int)$pdo->query("SELECT COUNT(*) FROM posts WHERE status='draft'")->fetchColumn();
                $stats['paginas']          = (int)$pdo->query('SELECT COUNT(*) FROM paginas')->fetchColumn();
                $stats['leads']            = (int)$pdo->query('SELECT COUNT(*) FROM leads')->fetchColumn();
                $stats['leads_new']        = (int)$pdo->query("SELECT COUNT(*) FROM leads WHERE status='novo'")->fetchColumn();
                $stats['content_types']    = (int)$pdo->query('SELECT COUNT(*) FROM content_types')->fetchColumn();
                $stats['content_items']    = (int)$pdo->query('SELECT COUNT(*) FROM content_items')->fetchColumn();
            } catch (Exception $e) {}
            return ['ok' => true, 'stats' => $stats];

        case 'list_posts':
            $limit  = min((int)($args['limit'] ?? 10), 50);
            $status = $args['status'] ?? 'all';
            $where  = ($status !== 'all') ? "WHERE status = " . $pdo->quote($status) : '';
            try {
                $rows = $pdo->query(
                    "SELECT id, title, slug, category, status, created_at FROM posts $where ORDER BY created_at DESC LIMIT $limit"
                )->fetchAll(PDO::FETCH_ASSOC);
                return ['ok' => true, 'posts' => $rows, 'count' => count($rows)];
            } catch (Exception $e) {
                return ['ok' => false, 'error' => $e->getMessage()];
            }

        case 'list_pages':
            try {
                $rows = $pdo->query(
                    'SELECT id, title, slug, status FROM paginas ORDER BY title ASC'
                )->fetchAll(PDO::FETCH_ASSOC);
                return ['ok' => true, 'pages' => $rows, 'count' => count($rows)];
            } catch (Exception $e) {
                return ['ok' => false, 'error' => $e->getMessage()];
            }

        case 'create_post':
            $title     = trim($args['title'] ?? '');
            $content   = trim($args['content'] ?? '');
            $excerpt   = trim($args['excerpt'] ?? '');
            $category  = trim($args['category'] ?? '');
            $read_time = trim($args['read_time'] ?? '');
            $status    = in_array($args['status'] ?? '', ['published', 'draft']) ? $args['status'] : 'draft';
            $sl        = slug($title);
            $cat_slug  = slug($category);

            if (!$title || !$content) {
                return ['ok' => false, 'error' => 'Título e conteúdo são obrigatórios.'];
            }
            try {
                // Ensure unique slug
                $base_sl = $sl;
                $suffix  = 1;
                while ($pdo->prepare('SELECT id FROM posts WHERE slug = ?')->execute([$sl]) &&
                       $pdo->prepare('SELECT id FROM posts WHERE slug = ?')->execute([$sl]) &&
                       $pdo->query("SELECT id FROM posts WHERE slug = " . $pdo->quote($sl))->fetchColumn()) {
                    $sl = $base_sl . '-' . $suffix++;
                }
                $ins = $pdo->prepare(
                    'INSERT INTO posts (title, slug, excerpt, content, category, category_slug, read_time, status)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
                );
                $ins->execute([$title, $sl, $excerpt, $content, $category, $cat_slug, $read_time, $status]);
                $new_id = $pdo->lastInsertId();
                $actions[] = [
                    'type'  => 'post_created',
                    'id'    => $new_id,
                    'title' => $title,
                    'slug'  => $sl,
                    'url'   => CMS_URL . '/posts/edit.php?id=' . $new_id,
                ];
                return ['ok' => true, 'post_id' => $new_id, 'slug' => $sl, 'status' => $status];
            } catch (Exception $e) {
                return ['ok' => false, 'error' => $e->getMessage()];
            }

        case 'list_leads':
            $limit  = min((int)($args['limit'] ?? 10), 50);
            $status = trim($args['status'] ?? '');
            $where  = $status ? "WHERE status = " . $pdo->quote($status) : '';
            try {
                $rows = $pdo->query(
                    "SELECT id, nome, email, status, origem, created_at FROM leads $where ORDER BY created_at DESC LIMIT $limit"
                )->fetchAll(PDO::FETCH_ASSOC);
                return ['ok' => true, 'leads' => $rows, 'count' => count($rows)];
            } catch (Exception $e) {
                return ['ok' => false, 'error' => $e->getMessage()];
            }

        case 'get_setting':
            $key = trim($args['key'] ?? '');
            if (!$key) return ['ok' => false, 'error' => 'Chave obrigatória.'];
            $val = setting($key, '__not_found__');
            return ['ok' => true, 'key' => $key, 'value' => $val === '__not_found__' ? null : $val];

        case 'update_setting':
            $key   = trim($args['key'] ?? '');
            $value = $args['value'] ?? '';
            if (!$key) return ['ok' => false, 'error' => 'Chave obrigatória.'];
            try {
                setting_save($key, $value);
                $actions[] = ['type' => 'setting_updated', 'key' => $key];
                return ['ok' => true, 'key' => $key, 'updated' => true];
            } catch (Exception $e) {
                return ['ok' => false, 'error' => $e->getMessage()];
            }

        default:
            return ['ok' => false, 'error' => "Ferramenta desconhecida: $name"];
    }
}

function csrf_verify_json_chat(array $input): void {
    $token = $input['csrf_token'] ?? '';
    if (session_status() === PHP_SESSION_NONE) session_start();
    $expected = $_SESSION['csrf_token'] ?? '';
    if (!$expected || !hash_equals($expected, $token)) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Token inválido.']);
        exit;
    }
}
