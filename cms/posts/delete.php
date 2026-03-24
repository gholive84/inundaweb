<?php
require_once dirname(__DIR__) . '/boot.php';
auth_check();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . CMS_URL . '/posts/');
    exit;
}

csrf_verify();

$id = (int)($_POST['id'] ?? 0);
if ($id > 0) {
    $stmt = db()->prepare('DELETE FROM posts WHERE id = ?');
    $stmt->execute([$id]);
    flash_set('success', 'Post excluído com sucesso.');
} else {
    flash_set('error', 'ID inválido.');
}

header('Location: ' . CMS_URL . '/posts/');
exit;
