<?php
require_once dirname(dirname(__DIR__)) . '/boot.php';
auth_check();
if (!auth_is_admin()) {
    flash_set('error', 'Acesso restrito a administradores.');
    header('Location: ' . CMS_URL . '/');
    exit;
}

$page_title = 'Tipos de Conteúdo';
$active     = 'config_tipos';
$pdo        = db();

$types = $pdo->query('SELECT ct.*, (SELECT COUNT(*) FROM content_items ci WHERE ci.type_id = ct.id) as item_count FROM content_types ct ORDER BY ct.name ASC')->fetchAll();

require_once dirname(dirname(__DIR__)) . '/includes/head.php';
?>

<div class="page-header">
  <div>
    <h1 class="page-header__title">Tipos de Conteúdo</h1>
    <p class="page-header__sub">Estruturas dinâmicas de conteúdo (depoimentos, cases, FAQs, etc.)</p>
  </div>
  <a href="<?= CMS_URL ?>/configuracoes/tipos/edit.php" class="btn btn-primary">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Novo Tipo
  </a>
</div>

<div class="adm-card">
  <div class="adm-card__header">
    <span class="adm-card__title">Todos os Tipos</span>
  </div>

  <?php if (empty($types)): ?>
  <div class="empty-state">
    <div class="empty-state__icon">
      <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
    </div>
    <p class="empty-state__title">Nenhum tipo criado</p>
    <p class="empty-state__text">Crie tipos personalizados como "Depoimentos", "Cases" ou "FAQs".</p>
    <a href="<?= CMS_URL ?>/configuracoes/tipos/edit.php" class="btn btn-primary">Novo Tipo</a>
  </div>
  <?php else: ?>
  <div class="adm-table-wrap">
    <table class="adm-table">
      <thead>
        <tr>
          <th>Nome</th>
          <th>Slug</th>
          <th>Campos</th>
          <th>Itens</th>
          <th>Criado em</th>
          <th style="text-align:right">Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($types as $t): ?>
        <?php $fields = json_decode($t['fields_schema'] ?? '[]', true) ?: []; ?>
        <tr>
          <td style="font-weight:600;color:#fff"><?= h($t['name']) ?></td>
          <td><code style="font-size:.8rem;color:var(--a-muted)"><?= h($t['slug']) ?></code></td>
          <td><?= count($fields) ?></td>
          <td>
            <a href="<?= CMS_URL ?>/content/?type=<?= h($t['slug']) ?>" style="color:var(--a-primary)">
              <?= (int)$t['item_count'] ?> item(s)
            </a>
          </td>
          <td><?= format_date($t['created_at']) ?></td>
          <td class="actions">
            <a href="<?= CMS_URL ?>/configuracoes/tipos/edit.php?id=<?= (int)$t['id'] ?>"
               class="btn btn-secondary btn-sm">Editar</a>
            <form method="POST" action="<?= CMS_URL ?>/configuracoes/tipos/delete.php" style="display:inline">
              <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
              <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
              <button type="submit" class="btn btn-danger btn-sm"
                      data-confirm="Excluir '<?= h($t['name']) ?>' e todos os seus itens?">Excluir</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<?php require_once dirname(dirname(__DIR__)) . '/includes/foot.php'; ?>
