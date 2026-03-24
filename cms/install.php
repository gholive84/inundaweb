<?php
/**
 * Inunda CMS — Instalador
 * Acesse /cms/install.php uma única vez para criar as tabelas e o usuário admin.
 * Apague ou proteja este arquivo após a instalação.
 */
define('CMS_DIR', __DIR__);
define('SITE_ROOT', dirname(__DIR__));
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/db.php';
require_once __DIR__ . '/core/functions.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$step   = (int)($_POST['step'] ?? 0);
$errors = [];
$log    = [];

// ── STEP 1: Criar tabelas ───────────────────────────────────────────
if ($step === 1) {
    try {
        $pdo = db();

        $tables = [

        // Usuários do CMS
        "CREATE TABLE IF NOT EXISTS cms_users (
            id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            nome          VARCHAR(150)  NOT NULL,
            email         VARCHAR(200)  NOT NULL UNIQUE,
            password_hash VARCHAR(255)  NOT NULL,
            role          ENUM('admin','editor') NOT NULL DEFAULT 'editor',
            active        TINYINT(1)    NOT NULL DEFAULT 1,
            last_login    DATETIME      NULL,
            created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        // Posts do blog
        "CREATE TABLE IF NOT EXISTS posts (
            id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            title          VARCHAR(500)  NOT NULL,
            slug           VARCHAR(300)  NOT NULL UNIQUE,
            excerpt        TEXT,
            content        LONGTEXT,
            image_url      VARCHAR(600),
            category       VARCHAR(100),
            category_slug  VARCHAR(100),
            read_time      VARCHAR(30),
            status         ENUM('published','draft') NOT NULL DEFAULT 'draft',
            created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        // Referências de páginas PHP
        "CREATE TABLE IF NOT EXISTS paginas (
            id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            title       VARCHAR(300) NOT NULL,
            slug        VARCHAR(200) NOT NULL UNIQUE,
            url         VARCHAR(300),
            file_path   VARCHAR(400),
            status      ENUM('active','inactive') NOT NULL DEFAULT 'active',
            created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        // Leads do formulário de contato
        "CREATE TABLE IF NOT EXISTS leads (
            id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            nome       VARCHAR(200),
            email      VARCHAR(200),
            telefone   VARCHAR(30),
            mensagem   TEXT,
            origem     VARCHAR(100) DEFAULT 'site',
            status     ENUM('novo','contactado','fechado','descartado') NOT NULL DEFAULT 'novo',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        // Configurações gerais (SMTP, header codes, etc.)
        "CREATE TABLE IF NOT EXISTS settings (
            id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            key_name   VARCHAR(100) NOT NULL UNIQUE,
            value      LONGTEXT,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        // Tipos de conteúdo dinâmicos
        "CREATE TABLE IF NOT EXISTS content_types (
            id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name           VARCHAR(150) NOT NULL,
            slug           VARCHAR(150) NOT NULL UNIQUE,
            icon           VARCHAR(50)  DEFAULT 'box',
            fields_schema  JSON,
            created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        // Itens dos tipos de conteúdo dinâmicos
        "CREATE TABLE IF NOT EXISTS content_items (
            id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            type_id      INT UNSIGNED NOT NULL,
            title        VARCHAR(500) NOT NULL,
            slug         VARCHAR(300) NOT NULL,
            status       ENUM('published','draft') NOT NULL DEFAULT 'draft',
            fields_data  JSON,
            created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_type_slug (type_id, slug),
            FOREIGN KEY (type_id) REFERENCES content_types(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        ];

        foreach ($tables as $sql) {
            $pdo->exec($sql);
            // Get table name from SQL for log
            preg_match('/TABLE IF NOT EXISTS (\w+)/i', $sql, $m);
            $log[] = ['ok', 'Tabela criada/verificada: ' . ($m[1] ?? '?')];
        }

        // ── Seed: posts ──
        $existing_posts = (int)$pdo->query('SELECT COUNT(*) FROM posts')->fetchColumn();
        if ($existing_posts === 0) {
            $posts_data = include SITE_ROOT . '/site/data/blog-posts.php';
            $ins = $pdo->prepare(
                'INSERT IGNORE INTO posts (title, slug, excerpt, content, image_url, category, category_slug, read_time, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            foreach ($posts_data as $p) {
                $ins->execute([
                    $p['title'],
                    $p['slug'],
                    $p['excerpt'] ?? '',
                    $p['content'] ?? '',
                    $p['image'] ?? '',
                    $p['category'] ?? '',
                    $p['category_slug'] ?? '',
                    $p['read_time'] ?? '',
                    'published',
                ]);
            }
            $log[] = ['ok', count($posts_data) . ' posts do blog importados com sucesso.'];
        } else {
            $log[] = ['info', "Posts já existem ($existing_posts). Seed ignorado."];
        }

        // ── Seed: paginas ──
        $existing_pgs = (int)$pdo->query('SELECT COUNT(*) FROM paginas')->fetchColumn();
        if ($existing_pgs === 0) {
            $pages_seed = [
                ['Home',                      '/',                       '/',                       SITE_ROOT . '/index.php'],
                ['Projetos WordPress Premium', '/projetos-wordpress-premium', '/projetos-wordpress-premium', SITE_ROOT . '/site/paginas/projetos-wordpress-premium.php'],
                ['Sites com IA',              '/sites-ia',               '/sites-ia',               SITE_ROOT . '/site/paginas/sites-ia.php'],
                ['Suporte WordPress Premium', '/suporte-wordpress-premium', '/suporte-wordpress-premium', SITE_ROOT . '/site/paginas/suporte-wordpress-premium.php'],
            ];
            $ins = $pdo->prepare(
                'INSERT IGNORE INTO paginas (title, slug, url, file_path, status)
                 VALUES (?, ?, ?, ?, ?)'
            );
            foreach ($pages_seed as [$title, $slug, $url, $file]) {
                $ins->execute([$title, $slug, $url, $file, 'active']);
            }
            $log[] = ['ok', count($pages_seed) . ' páginas registradas.'];
        } else {
            $log[] = ['info', "Páginas já existem ($existing_pgs). Seed ignorado."];
        }

        // ── Seed: settings padrão ──
        $default_settings = [
            'smtp_host'       => '',
            'smtp_port'       => '587',
            'smtp_user'       => '',
            'smtp_pass'       => '',
            'smtp_from_name'  => 'Inunda IA',
            'smtp_from_email' => '',
            'header_codes'    => '',
        ];
        $ins = $pdo->prepare(
            'INSERT IGNORE INTO settings (key_name, value) VALUES (?, ?)'
        );
        foreach ($default_settings as $k => $v) {
            $ins->execute([$k, $v]);
        }
        $log[] = ['ok', 'Configurações padrão inseridas.'];

        $log[] = ['done', 'Tabelas criadas com sucesso! Agora crie o usuário admin.'];
        $step = 2;

    } catch (Exception $e) {
        $errors[] = 'Erro ao criar tabelas: ' . $e->getMessage();
        $step = 0;
    }
}

// ── STEP 2: Criar usuário admin ──────────────────────────────────────
if ($step === 3) {
    $nome  = trim($_POST['nome']  ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['senha'] ?? '';
    $pass2 = $_POST['senha2'] ?? '';

    if ($nome === '')  $errors[] = 'Nome obrigatório.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'E-mail inválido.';
    if (strlen($pass) < 8) $errors[] = 'Senha deve ter pelo menos 8 caracteres.';
    if ($pass !== $pass2) $errors[] = 'Senhas não conferem.';

    if (empty($errors)) {
        try {
            $pdo  = db();
            $hash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);
            $ins  = $pdo->prepare(
                'INSERT INTO cms_users (nome, email, password_hash, role, active) VALUES (?, ?, ?, ?, 1)
                 ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), role = "admin", active = 1'
            );
            $ins->execute([$nome, $email, $hash, 'admin']);
            $log[] = ['ok', "Usuário admin '$nome' criado com sucesso!"];
            $log[] = ['done', 'Instalação concluída! Acesse o CMS e faça login.'];
            $step  = 4; // done
        } catch (Exception $e) {
            $errors[] = 'Erro ao criar usuário: ' . $e->getMessage();
            $step = 2;
        }
    } else {
        $step = 2;
    }
}
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Instalação — Inunda CMS</title>
  <meta name="robots" content="noindex, nofollow">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= CMS_URL ?>/assets/css/admin.css">
</head>
<body>
<div class="install-wrap">
  <div class="install-card" style="max-width:520px">

    <span style="font-size:1.5rem;font-weight:800;letter-spacing:-0.02em;background:linear-gradient(135deg,#22d3ee,#67e8f9);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;display:block;text-align:center;margin-bottom:6px">inunda ia</span>
    <p style="text-align:center;color:#64748b;font-size:.875rem;margin-bottom:28px">Instalador do CMS</p>

    <?php foreach ($errors as $err): ?>
    <div class="flash flash--error" style="margin-bottom:16px">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <?= htmlspecialchars($err) ?>
    </div>
    <?php endforeach; ?>

    <?php if (!empty($log)): ?>
    <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:8px;padding:16px;margin-bottom:24px;font-size:.8125rem;font-family:monospace">
      <?php foreach ($log as [$type, $msg]): ?>
        <?php
          $color = $type === 'ok' ? '#22c55e' : ($type === 'done' ? '#22d3ee' : '#64748b');
          $prefix = $type === 'ok' ? '✓' : ($type === 'done' ? '★' : '→');
        ?>
        <div style="color:<?= $color ?>;margin-bottom:4px"><?= $prefix ?> <?= htmlspecialchars($msg) ?></div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if ($step === 0): ?>
    <!-- STEP 0: Boas-vindas -->
    <h2 class="install-title">Bem-vindo ao instalador</h2>
    <p class="install-sub">Este wizard irá criar as tabelas no banco de dados e configurar o usuário administrador.</p>
    <div style="background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.3);border-radius:8px;padding:14px;margin-bottom:24px;font-size:.875rem;color:#f59e0b">
      <strong>⚠ Atenção:</strong> Execute apenas uma vez. Apague ou proteja este arquivo após a instalação.
    </div>
    <form method="POST">
      <input type="hidden" name="step" value="1">
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">
        Iniciar instalação →
      </button>
    </form>

    <?php elseif ($step === 2): ?>
    <!-- STEP 2: Criar usuário admin -->
    <h2 class="install-title">Criar usuário administrador</h2>
    <p class="install-sub">Configure o acesso ao painel.</p>
    <form method="POST" novalidate>
      <input type="hidden" name="step" value="3">
      <div class="form-group" style="margin-bottom:16px">
        <label>Nome completo</label>
        <input type="text" name="nome" required placeholder="Seu nome" value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>">
      </div>
      <div class="form-group" style="margin-bottom:16px">
        <label>E-mail</label>
        <input type="email" name="email" required placeholder="admin@inundaia.com.br" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-group" style="margin-bottom:16px">
        <label>Senha (mínimo 8 caracteres)</label>
        <input type="password" name="senha" required placeholder="••••••••">
      </div>
      <div class="form-group" style="margin-bottom:24px">
        <label>Confirmar senha</label>
        <input type="password" name="senha2" required placeholder="••••••••">
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">
        Criar admin e finalizar →
      </button>
    </form>

    <?php elseif ($step === 4): ?>
    <!-- STEP 4: Concluído -->
    <div style="text-align:center;padding:20px 0">
      <div style="font-size:3rem;margin-bottom:16px">🎉</div>
      <h2 class="install-title" style="text-align:center">Instalação concluída!</h2>
      <p class="install-sub" style="text-align:center">O CMS está pronto para uso.</p>
      <a href="<?= CMS_URL ?>/login.php" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:8px">
        Ir para o login →
      </a>
      <p style="margin-top:20px;font-size:.8rem;color:#ef4444;text-align:center">
        ⚠ Apague ou renomeie este arquivo: <code>cms/install.php</code>
      </p>
    </div>
    <?php endif; ?>

  </div>
</div>
</body>
</html>
