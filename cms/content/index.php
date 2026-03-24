<?php
require_once dirname(__DIR__) . '/boot.php';
auth_check();

$pdo       = db();
$type_slug = $_GET['type'] ?? '';

if (!$type_slug) {
    header('Location: ' . CMS_URL . '/');
    exit;
}

$type_stmt = $pdo->prepare('SELECT * FROM content_types WHERE slug = ? LIMIT 1');
$type_stmt->execute([$type_slug]);
$type = $type_stmt->fetch();

if (!$type) {
    flash_set('error', 'Tipo de conteúdo não encontrado.');
    header('Location: ' . CMS_URL . '/');
    exit;
}

$page_title = h($type['name']);
$active     = 'content_' . h($type_slug);

$items = $pdo->prepare(
    'SELECT id, title, slug, status, created_at FROM content_items WHERE type_id = ? ORDER BY created_at DESC'
);
$items->execute([$type['id']]);
$items = $items->fetchAll();

require_once dirname(__DIR__) . '/includes/head.php';
?>

<div class="page-header">
  <div>
    <h1 class="page-header__title"><?= h($type['name']) ?></h1>
    <p class="page-header__sub"><?= count($items) ?> item(s)</p>
  </div>
  <a href="<?= CMS_URL ?>/content/edit.php?type=<?= h($type_slug) ?>" class="btn btn-primary">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Novo Item
  </a>
</div>

<div class="adm-card">
  <div class="adm-card__header">
    <span class="adm-card__title">Itens: <?= h($type['name']) ?></span>
  </div>

  <?php if (empty($items)): ?>
  <div class="empty-state">
    <div class="empty-state__icon">
      <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
    </div>
    <p class="empty-state__title">Nenhum item ainda</p>
    <p class="empty-state__text">Adicione o primeiro item para este tipo de conteúdo.</p>
    <a href="<?= CMS_URL ?>/content/edit.php?type=<?= h($type_slug) ?>" class="btn btn-primary">Novo Item</a>
  </div>
  <?php else: ?>
  <div class="adm-table-wrap">
    <table class="adm-table">
      <thead>
        <tr>
          <th>Título</th>
          <th>Slug</th>
          <th>Status</th>
          <th>Data</th>
          <th style="text-align:right">Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $item): ?>
        <tr>
          <td style="font-weight:600;color:#fff"><?= h($item['title']) ?></td>
          <td><code style="font-size:0.8rem;color:var(--a-muted)"><?= h($item['slug']) ?></code></td>
          <td><span class="badge badge--<?= h($item['status']) ?>"><?= h($item['status']) ?></span></td>
          <td><?= format_date($item['created_at']) ?></td>
          <td class="actions">
            <a href="<?= CMS_URL ?>/content/edit.php?type=<?= h($type_slug) ?>&id=<?= (int)$item['id'] ?>"
               class="btn btn-secondary btn-sm">Editar</a>
            <form method="POST" action="<?= CMS_URL ?>/content/delete.php" style="display:inline">
              <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
              <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
              <input type="hidden" name="type" value="<?= h($type_slug) ?>">
              <button type="submit" class="btn btn-danger btn-sm"
                      data-confirm="Excluir este item permanentemente?">Excluir</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<?php require_once dirname(__DIR__) . '/includes/foot.php'; ?>
