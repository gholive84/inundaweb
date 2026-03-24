<?php
/**
 * Inunda CMS — Migration: paginas_history + openai settings
 * Acesse /cms/paginas/migrate.php UMA VEZ para criar a tabela de histórico.
 * Apague após executar.
 */
require_once dirname(__DIR__) . '/boot.php';
auth_check();
if (!auth_is_admin()) die('Acesso negado.');

$pdo = db();
$log = [];

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS paginas_history (
        id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        pagina_id  INT UNSIGNED NOT NULL,
        file_path  VARCHAR(400) NOT NULL,
        content    LONGTEXT NOT NULL,
        saved_by   INT UNSIGNED,
        saved_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_pagina (pagina_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $log[] = ['ok', 'Tabela paginas_history criada.'];

    // Default OpenAI settings
    $defaults = ['openai_key' => '', 'openai_model' => 'gpt-4o'];
    $ins = $pdo->prepare('INSERT IGNORE INTO settings (key_name, value) VALUES (?, ?)');
    foreach ($defaults as $k => $v) $ins->execute([$k, $v]);
    $log[] = ['ok', 'Settings de IA inseridos.'];

} catch (Exception $e) {
    $log[] = ['err', $e->getMessage()];
}
?><!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>Migration</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
<style>body{font-family:Inter,sans-serif;background:#070d18;color:#e2e8f0;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}.card{background:#111827;border:1px solid rgba(255,255,255,.07);border-radius:12px;padding:32px;max-width:480px;width:100%}h2{color:#fff;margin:0 0 20px}.item{padding:8px 0;border-bottom:1px solid rgba(255,255,255,.05);font-size:.875rem}.ok{color:#22c55e}.err{color:#ef4444}a{display:inline-block;margin-top:20px;padding:10px 20px;background:#22d3ee;color:#0a1122;border-radius:6px;text-decoration:none;font-weight:600}</style>
</head><body><div class="card">
<h2>Migration concluída</h2>
<?php foreach ($log as [$t, $m]): ?>
<div class="item <?= $t ?>">
  <?= $t === 'ok' ? '✓' : '✗' ?> <?= htmlspecialchars($m) ?>
</div>
<?php endforeach; ?>
<a href="<?= CMS_URL ?>/">← Voltar ao CMS</a>
</div></body></html>
