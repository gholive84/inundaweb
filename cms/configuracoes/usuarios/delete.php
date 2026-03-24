<?php
require_once dirname(dirname(__DIR__)) . '/boot.php';
auth_check();
if (!auth_is_admin()) {
    flash_set('error', 'Acesso restrito a administradores.');
    header('Location: ' . CMS_URL . '/');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . CMS_URL . '/configuracoes/usuarios/');
    exit;
}

csrf_verify();

$id         = (int)($_POST['id'] ?? 0);
$current_id = auth_user()['id'] ?? 0;

if ($id === (int)$current_id) {
    flash_set('error', 'Você não pode excluir seu próprio usuário.');
    header('Location: ' . CMS_URL . '/configuracoes/usuarios/');
    exit;
}

if ($id > 0) {
    $stmt = db()->prepare('DELETE FROM cms_users WHERE id = ?');
    $stmt->execute([$id]);
    flash_set('success', 'Usuário excluído.');
} else {
    flash_set('error', 'ID inválido.');
}

header('Location: ' . CMS_URL . '/configuracoes/usuarios/');
exit;
