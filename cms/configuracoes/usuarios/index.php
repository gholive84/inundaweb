<?php
require_once dirname(dirname(__DIR__)) . '/boot.php';
auth_check();
if (!auth_is_admin()) {
    flash_set('error', 'Acesso restrito a administradores.');
    header('Location: ' . CMS_URL . '/');
    exit;
}

$page_title = 'Usuários';
$active     = 'config_usuarios';
$pdo        = db();

$users = $pdo->query('SELECT id, nome, email, role, active, last_login, created_at FROM cms_users ORDER BY created_at ASC')->fetchAll();

require_once dirname(dirname(__DIR__)) . '/includes/head.php';
?>

<div class="page-header">
  <div>
    <h1 class="page-header__title">Usuários</h1>
    <p class="page-header__sub"><?= count($users) ?> usuário(s) cadastrado(s)</p>
  </div>
  <a href="<?= CMS_URL ?>/configuracoes/usuarios/edit.php" class="btn btn-primary">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Novo Usuário
  </a>
</div>

<div class="adm-card">
  <div class="adm-card__header">
    <span class="adm-card__title">Todos os Usuários</span>
  </div>

  <?php if (empty($users)): ?>
  <div class="empty-state">
    <div class="empty-state__icon">
      <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
    </div>
    <p class="empty-state__title">Nenhum usuário</p>
    <p class="empty-state__text">Crie o primeiro usuário.</p>
    <a href="<?= CMS_URL ?>/configuracoes/usuarios/edit.php" class="btn btn-primary">Novo Usuário</a>
  </div>
  <?php else: ?>
  <div class="adm-table-wrap">
    <table class="adm-table">
      <thead>
        <tr>
          <th>Nome</th>
          <th>E-mail</th>
          <th>Perfil</th>
          <th>Status</th>
          <th>Último acesso</th>
          <th style="text-align:right">Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $current_id = auth_user()['id'] ?? 0;
        foreach ($users as $u):
        ?>
        <tr>
          <td style="font-weight:600;color:#fff"><?= h($u['nome']) ?></td>
          <td><?= h($u['email']) ?></td>
          <td><span class="badge badge--<?= h($u['role']) ?>"><?= h($u['role']) ?></span></td>
          <td>
            <?php if ($u['active']): ?>
              <span class="badge badge--published">Ativo</span>
            <?php else: ?>
              <span class="badge badge--draft">Inativo</span>
            <?php endif; ?>
          </td>
          <td><?= $u['last_login'] ? format_date($u['last_login']) : '<span class="text-muted">Nunca</span>' ?></td>
          <td class="actions">
            <a href="<?= CMS_URL ?>/configuracoes/usuarios/edit.php?id=<?= (int)$u['id'] ?>"
               class="btn btn-secondary btn-sm">Editar</a>
            <?php if ((int)$u['id'] !== (int)$current_id): ?>
            <form method="POST" action="<?= CMS_URL ?>/configuracoes/usuarios/delete.php" style="display:inline">
              <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
              <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
              <button type="submit" class="btn btn-danger btn-sm"
                      data-confirm="Excluir o usuário '<?= h($u['nome']) ?>'?">Excluir</button>
            </form>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<?php require_once dirname(dirname(__DIR__)) . '/includes/foot.php'; ?>
