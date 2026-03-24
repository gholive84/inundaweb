<?php
require_once dirname(__DIR__) . '/boot.php';
auth_check();

$page_title = 'Leads';
$active     = 'leads';
$pdo        = db();

$filter_status = $_GET['status'] ?? '';
$valid_statuses = ['novo','contactado','fechado','descartado'];

$where  = '';
$params = [];
if (in_array($filter_status, $valid_statuses)) {
    $where    = 'WHERE status = ?';
    $params[] = $filter_status;
}

$leads = $pdo->prepare("SELECT * FROM leads $where ORDER BY created_at DESC");
$leads->execute($params);
$leads = $leads->fetchAll();

// Counts per status
$counts = [];
$counts_stmt = $pdo->query("SELECT status, COUNT(*) as cnt FROM leads GROUP BY status");
foreach ($counts_stmt->fetchAll() as $row) {
    $counts[$row['status']] = $row['cnt'];
}
$counts['total'] = array_sum($counts);

require_once dirname(__DIR__) . '/includes/head.php';
?>

<div class="page-header">
  <div>
    <h1 class="page-header__title">Leads</h1>
    <p class="page-header__sub"><?= count($leads) ?> lead(s) encontrado(s)</p>
  </div>
</div>

<div class="adm-card">
  <div class="adm-card__header">
    <span class="adm-card__title">Todos os Leads</span>
    <div class="filter-bar">
      <a href="?" class="<?= $filter_status === '' ? 'active' : '' ?>">
        Todos <?php if (!empty($counts['total'])): ?><span style="opacity:.6">(<?= $counts['total'] ?>)</span><?php endif; ?>
      </a>
      <?php foreach ($valid_statuses as $s): ?>
      <a href="?status=<?= h($s) ?>" class="<?= $filter_status === $s ? 'active' : '' ?>">
        <?= ucfirst(h($s)) ?>
        <?php if (!empty($counts[$s])): ?><span style="opacity:.6">(<?= $counts[$s] ?>)</span><?php endif; ?>
      </a>
      <?php endforeach; ?>
    </div>
  </div>

  <?php if (empty($leads)): ?>
  <div class="empty-state">
    <div class="empty-state__icon">
      <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
    </div>
    <p class="empty-state__title">Nenhum lead encontrado</p>
    <p class="empty-state__text">Os leads do formulário de contato aparecerão aqui.</p>
  </div>
  <?php else: ?>
  <div class="adm-table-wrap">
    <table class="adm-table">
      <thead>
        <tr>
          <th>Nome</th>
          <th>E-mail</th>
          <th>Telefone</th>
          <th>Mensagem</th>
          <th>Status</th>
          <th>Data</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($leads as $lead): ?>
        <tr>
          <td style="font-weight:600;color:#fff"><?= h($lead['nome'] ?? '—') ?></td>
          <td><?= h($lead['email'] ?? '—') ?></td>
          <td><?= h($lead['telefone'] ?? '—') ?></td>
          <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
            <?= h(mb_substr($lead['mensagem'] ?? '—', 0, 60)) ?>
          </td>
          <td>
            <select class="lead-status-select" data-id="<?= (int)$lead['id'] ?>">
              <?php foreach ($valid_statuses as $s): ?>
              <option value="<?= h($s) ?>" <?= $lead['status'] === $s ? 'selected' : '' ?>><?= ucfirst(h($s)) ?></option>
              <?php endforeach; ?>
            </select>
          </td>
          <td><?= format_date($lead['created_at']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<?php require_once dirname(__DIR__) . '/includes/foot.php'; ?>
