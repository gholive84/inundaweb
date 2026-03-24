<?php
require_once dirname(__DIR__) . '/boot.php';
auth_check();

$page_title = 'Posts';
$active     = 'posts';
$pdo        = db();

$per_page = 20;
$page     = max(1, (int)($_GET['page'] ?? 1));
$status   = $_GET['status'] ?? '';
$offset   = ($page - 1) * $per_page;

$where  = '';
$params = [];
if ($status === 'published' || $status === 'draft') {
    $where    = 'WHERE status = ?';
    $params[] = $status;
}

$total  = $pdo->prepare("SELECT COUNT(*) FROM posts $where");
$total->execute($params);
$total  = (int)$total->fetchColumn();
$pages  = max(1, (int)ceil($total / $per_page));

$stmt = $pdo->prepare("SELECT id, title, image_url, category, status, created_at FROM posts $where ORDER BY created_at DESC LIMIT $per_page OFFSET $offset");
$stmt->execute($params);
$posts = $stmt->fetchAll();

require_once dirname(__DIR__) . '/includes/head.php';
?>

<div class="page-header">
  <div>
    <h1 class="page-header__title">Posts</h1>
    <p class="page-header__sub"><?= $total ?> post(s) encontrado(s)</p>
  </div>
  <a href="<?= CMS_URL ?>/posts/edit.php" class="btn btn-primary">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Novo Post
  </a>
</div>

<div class="adm-card">
  <div class="adm-card__header">
    <span class="adm-card__title">Todos os Posts</span>
    <div class="filter-bar">
      <a href="?" class="<?= $status === '' ? 'active' : '' ?>">Todos</a>
      <a href="?status=published" class="<?= $status === 'published' ? 'active' : '' ?>">Publicados</a>
      <a href="?status=draft" class="<?= $status === 'draft' ? 'active' : '' ?>">Rascunhos</a>
    </div>
  </div>

  <?php if (empty($posts)): ?>
  <div class="empty-state">
    <div class="empty-state__icon"><svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
    <p class="empty-state__title">Nenhum post encontrado</p>
    <p class="empty-state__text">Crie seu primeiro post clicando em "Novo Post".</p>
    <a href="<?= CMS_URL ?>/posts/edit.php" class="btn btn-primary">Novo Post</a>
  </div>
  <?php else: ?>
  <div class="adm-table-wrap">
    <table class="adm-table">
      <thead>
        <tr>
          <th style="width:60px">Img</th>
          <th>Título</th>
          <th>Categoria</th>
          <th>Status</th>
          <th>Data</th>
          <th style="text-align:right">Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($posts as $post): ?>
        <tr>
          <td>
            <?php if ($post['image_url']): ?>
            <img src="<?= h($post['image_url']) ?>" alt="" class="thumb" loading="lazy">
            <?php else: ?>
            <div class="thumb" style="background:var(--a-card-2);display:flex;align-items:center;justify-content:center">
              <svg width="18" height="18" fill="none" stroke="currentColor" opacity=".3" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            </div>
            <?php endif; ?>
          </td>
          <td>
            <span style="font-weight:600;color:#fff"><?= h($post['title']) ?></span>
          </td>
          <td><?= h($post['category'] ?? '—') ?></td>
          <td><span class="badge badge--<?= h($post['status']) ?>"><?= h($post['status']) ?></span></td>
          <td><?= format_date($post['created_at']) ?></td>
          <td class="actions">
            <a href="<?= CMS_URL ?>/posts/edit.php?id=<?= (int)$post['id'] ?>" class="btn btn-secondary btn-sm">Editar</a>
            <form method="POST" action="<?= CMS_URL ?>/posts/delete.php" style="display:inline">
              <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
              <input type="hidden" name="id" value="<?= (int)$post['id'] ?>">
              <button type="submit" class="btn btn-danger btn-sm"
                      data-confirm="Excluir este post permanentemente?">Excluir</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if ($pages > 1): ?>
  <div style="padding:16px 24px;border-top:1px solid var(--a-border)">
    <div class="pagination">
      <?php if ($page > 1): ?>
        <a href="?page=<?= $page-1 ?>&status=<?= h($status) ?>">← Anterior</a>
      <?php endif; ?>
      <?php for ($i = 1; $i <= $pages; $i++): ?>
        <?php if ($i == $page): ?>
          <span class="current"><?= $i ?></span>
        <?php else: ?>
          <a href="?page=<?= $i ?>&status=<?= h($status) ?>"><?= $i ?></a>
        <?php endif; ?>
      <?php endfor; ?>
      <?php if ($page < $pages): ?>
        <a href="?page=<?= $page+1 ?>&status=<?= h($status) ?>">Próxima →</a>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>
  <?php endif; ?>
</div>

<?php require_once dirname(__DIR__) . '/includes/foot.php'; ?>
