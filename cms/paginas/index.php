<?php
require_once dirname(__DIR__) . '/boot.php';
auth_check();

$page_title = 'Páginas';
$active     = 'paginas';
$pdo        = db();

$paginas = $pdo->query('SELECT * FROM paginas ORDER BY created_at ASC')->fetchAll();

require_once dirname(__DIR__) . '/includes/head.php';
?>

<div class="page-header">
  <div>
    <h1 class="page-header__title">Páginas</h1>
    <p class="page-header__sub">Referências de páginas do site</p>
  </div>
</div>

<div class="adm-card">
  <div class="adm-card__header">
    <div>
      <span class="adm-card__title">Páginas do Site</span>
      <p class="adm-card__note">As páginas são arquivos PHP. O banco armazena apenas referências.</p>
    </div>
  </div>

  <?php if (empty($paginas)): ?>
  <div class="empty-state">
    <div class="empty-state__icon">
      <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
    </div>
    <p class="empty-state__title">Nenhuma página cadastrada</p>
    <p class="empty-state__text">Execute o install.php para popular as páginas padrão.</p>
  </div>
  <?php else: ?>
  <div class="adm-table-wrap">
    <table class="adm-table">
      <thead>
        <tr>
          <th>Título</th>
          <th>URL</th>
          <th>Arquivo PHP</th>
          <th>Status</th>
          <th>Criado em</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($paginas as $pg): ?>
        <tr>
          <td style="font-weight:600;color:#fff"><?= h($pg['title']) ?></td>
          <td>
            <?php if ($pg['url']): ?>
            <a href="<?= SITE_URL . h($pg['url']) ?>" target="_blank" style="color:var(--a-primary)">
              <?= h($pg['url']) ?>
              <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;margin-left:2px"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            </a>
            <?php else: ?>
            <span class="text-muted">—</span>
            <?php endif; ?>
          </td>
          <td><code style="font-size:0.8125rem;color:var(--a-muted)"><?= h($pg['file_path']) ?></code></td>
          <td><span class="badge badge--<?= h($pg['status']) ?>"><?= h($pg['status']) ?></span></td>
          <td><?= format_date($pg['created_at']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<?php require_once dirname(__DIR__) . '/includes/foot.php'; ?>
