<?php
require_once dirname(__DIR__) . '/boot.php';
auth_check();
if (!auth_is_admin()) die('Acesso negado.');

$pdo = db();
$pdo->exec("
    CREATE TABLE IF NOT EXISTS lead_comments (
        id         INT AUTO_INCREMENT PRIMARY KEY,
        lead_id    INT NOT NULL,
        content    TEXT NOT NULL,
        created_by INT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX (lead_id),
        FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");
echo 'Tabela lead_comments criada com sucesso.';
