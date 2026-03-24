<?php
require_once __DIR__ . '/boot.php';
auth_check();

$page_title = 'Dashboard';
$active     = 'dashboard';

$pdo = db();

// Stats
$total_published = $pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'published'")->fetchColumn();
$total_draft     = $pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'draft'")->fetchColumn();
$total_leads_new = $pdo->query("SELECT COUNT(*) FROM leads WHERE status = 'novo'")->fetchColumn();
$total_pages     = $pdo->query("SELECT COUNT(*) FROM paginas WHERE status = 'active'")->fetchColumn();

// Recent leads
$recent_leads = $pdo->query(
    "SELECT id, nome, email, status, created_at FROM leads ORDER BY created_at DESC LIMIT 10"
)->fetchAll();

// Recent posts
$recent_posts = $pdo->query(
    "SELECT id, title, category, status, created_at FROM posts ORDER BY created_at DESC LIMIT 5"
)->fetchAll();

require_once __DIR__ . '/includes/head.php';
?>

<div class="page-header">
  <div>
    <h1 class="page-header__title">Dashboard</h1>
    <p class="page-header__sub">Visão geral do seu CMS</p>
  </div>
  <a href="<?= CMS_URL ?>/posts/edit.php" class="btn btn-primary">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Novo Post
  </a>
</div>

<div class="stats-grid">
  <div class="stat-card stat-card--primary">
    <span class="stat-card__label">Posts Publicados</span>
    <span class="stat-card__value"><?= (int)$total_published ?></span>
    <span class="stat-card__sub">no blog</span>
  </div>
  <div class="stat-card">
    <span class="stat-card__label">Rascunhos</span>
    <span class="stat-card__value"><?= (int)$total_draft ?></span>
    <span class="stat-card__sub">aguardando publicação</span>
  </div>
  <div class="stat-card stat-card--success">
    <span class="stat-card__label">Leads Novos</span>
    <span class="stat-card__value"><?= (int)$total_leads_new ?></span>
    <span class="stat-card__sub">sem contato ainda</span>
  </div>
  <div class="stat-card">
    <span class="stat-card__label">Páginas Ativas</span>
    <span class="stat-card__value"><?= (int)$total_pages ?></span>
    <span class="stat-card__sub">no site</span>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;flex-wrap:wrap">

  <!-- Recent Leads -->
  <div class="adm-card" style="grid-column: 1/-1">
    <div class="adm-card__header">
      <span class="adm-card__title">Leads Recentes</span>
      <a href="<?= CMS_URL ?>/leads/" class="btn btn-secondary btn-sm">Ver todos</a>
    </div>
    <?php if (empty($recent_leads)): ?>
    <div class="empty-state">
      <div class="empty-state__icon"><svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div>
      <p class="empty-state__title">Nenhum lead ainda</p>
      <p class="empty-state__text">Os leads do formulário de contato aparecerão aqui.</p>
    </div>
    <?php else: ?>
    <div class="adm-table-wrap">
      <table class="adm-table">
        <thead>
          <tr>
            <th>Nome</th>
            <th>E-mail</th>
            <th>Status</th>
            <th>Data</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recent_leads as $lead): ?>
          <tr>
            <td><?= h($lead['nome'] ?? '—') ?></td>
            <td><?= h($lead['email'] ?? '—') ?></td>
            <td><span class="badge badge--<?= h($lead['status']) ?>"><?= h($lead['status']) ?></span></td>
            <td><?= format_date($lead['created_at']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

  <!-- Recent Posts -->
  <div class="adm-card" style="grid-column: 1/-1">
    <div class="adm-card__header">
      <span class="adm-card__title">Posts Recentes</span>
      <a href="<?= CMS_URL ?>/posts/" class="btn btn-secondary btn-sm">Ver todos</a>
    </div>
    <?php if (empty($recent_posts)): ?>
    <div class="empty-state">
      <div class="empty-state__icon"><svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
      <p class="empty-state__title">Nenhum post ainda</p>
      <p class="empty-state__text">Crie seu primeiro post no blog.</p>
    </div>
    <?php else: ?>
    <div class="adm-table-wrap">
      <table class="adm-table">
        <thead>
          <tr>
            <th>Título</th>
            <th>Categoria</th>
            <th>Status</th>
            <th>Data</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recent_posts as $post): ?>
          <tr>
            <td><?= h($post['title']) ?></td>
            <td><?= h($post['category'] ?? '—') ?></td>
            <td><span class="badge badge--<?= h($post['status']) ?>"><?= h($post['status']) ?></span></td>
            <td><?= format_date($post['created_at']) ?></td>
            <td class="actions">
              <a href="<?= CMS_URL ?>/posts/edit.php?id=<?= (int)$post['id'] ?>" class="btn btn-secondary btn-sm">Editar</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

</div>

<?php require_once __DIR__ . '/includes/foot.php'; ?>
