<?php
require_once dirname(__DIR__) . '/boot.php';
auth_check();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . CMS_URL . '/');
    exit;
}

csrf_verify();

$id        = (int)($_POST['id'] ?? 0);
$type_slug = $_POST['type'] ?? '';

if ($id > 0) {
    $stmt = db()->prepare('DELETE FROM content_items WHERE id = ?');
    $stmt->execute([$id]);
    flash_set('success', 'Item excluído com sucesso.');
} else {
    flash_set('error', 'ID inválido.');
}

$redirect = $type_slug
    ? CMS_URL . '/content/?type=' . urlencode($type_slug)
    : CMS_URL . '/';

header('Location: ' . $redirect);
exit;
