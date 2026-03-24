<?php
require_once dirname(dirname(__DIR__)) . '/boot.php';
auth_check();
if (!auth_is_admin()) {
    flash_set('error', 'Acesso restrito a administradores.');
    header('Location: ' . CMS_URL . '/');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . CMS_URL . '/configuracoes/tipos/');
    exit;
}

csrf_verify();

$id = (int)($_POST['id'] ?? 0);
if ($id > 0) {
    // content_items deleted via CASCADE
    $stmt = db()->prepare('DELETE FROM content_types WHERE id = ?');
    $stmt->execute([$id]);
    flash_set('success', 'Tipo e seus itens foram excluídos.');
} else {
    flash_set('error', 'ID inválido.');
}

header('Location: ' . CMS_URL . '/configuracoes/tipos/');
exit;
