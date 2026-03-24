<?php
require_once dirname(__DIR__) . '/boot.php';
auth_check();

$page_title = 'Leads';
$active     = 'leads';
$pdo        = db();

$view = $_GET['view'] ?? 'kanban'; // kanban | lista

// All leads
$leads = $pdo->query("SELECT * FROM leads ORDER BY created_at DESC")->fetchAll();

$statuses = [
    'novo'        => ['label' => 'Novos',       'color' => '#22d3ee', 'bg' => 'rgba(34,211,238,.08)'],
    'contactado'  => ['label' => 'Contactados', 'color' => '#f59e0b', 'bg' => 'rgba(245,158,11,.08)'],
    'fechado'     => ['label' => 'Fechados',    'color' => '#22c55e', 'bg' => 'rgba(34,197,94,.08)'],
    'descartado'  => ['label' => 'Descartados', 'color' => '#94a3b8', 'bg' => 'rgba(100,116,139,.08)'],
];

// Group by status
$grouped = array_fill_keys(array_keys($statuses), []);
foreach ($leads as $l) {
    $s = $l['status'] ?? 'novo';
    if (!isset($grouped[$s])) $s = 'novo';
    $grouped[$s][] = $l;
}

require_once dirname(__DIR__) . '/includes/head.php';
?>

<div class="page-header">
  <div>
    <h1 class="page-header__title">Leads</h1>
    <p class="page-header__sub"><?= count($leads) ?> lead(s) no total</p>
  </div>
  <div style="display:flex;gap:8px;align-items:center">
    <a href="?view=kanban" class="btn btn-sm <?= $view === 'kanban' ? 'btn-primary' : 'btn-secondary' ?>">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="5" height="18" rx="1"/><rect x="10" y="3" width="5" height="11" rx="1"/><rect x="17" y="3" width="4" height="15" rx="1"/></svg>
      Kanban
    </a>
    <a href="?view=lista" class="btn btn-sm <?= $view === 'lista' ? 'btn-primary' : 'btn-secondary' ?>">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
      Lista
    </a>
  </div>
</div>

<?php if ($view === 'kanban'): ?>
<!-- ══ KANBAN ══ -->
<style>
.kanban-board {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  align-items: start;
}
@media (max-width: 1100px) { .kanban-board { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 600px)  { .kanban-board { grid-template-columns: 1fr; } }

.kanban-col {
  background: var(--a-card);
  border: 1px solid var(--a-border);
  border-radius: 12px;
  overflow: hidden;
}

.kanban-col__header {
  padding: 14px 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid var(--a-border);
}

.kanban-col__title {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: .875rem;
  font-weight: 700;
  color: #fff;
}

.kanban-col__dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  flex-shrink: 0;
}

.kanban-col__count {
  font-size: .75rem;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 100px;
  color: rgba(255,255,255,.6);
  background: rgba(255,255,255,.06);
}

.kanban-cards {
  padding: 10px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  min-height: 60px;
}

.kanban-card {
  background: var(--a-card-2);
  border: 1px solid var(--a-border);
  border-radius: 8px;
  padding: 12px;
  transition: border-color .18s, box-shadow .18s;
  cursor: pointer;
}

.kanban-card:hover {
  border-color: rgba(255,255,255,.18);
  box-shadow: 0 4px 16px rgba(0,0,0,.2);
}

.kanban-card__name {
  font-size: .875rem;
  font-weight: 600;
  color: #fff;
  margin-bottom: 4px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.kanban-card__email {
  font-size: .75rem;
  color: var(--a-muted);
  margin-bottom: 8px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.kanban-card__msg {
  font-size: .75rem;
  color: rgba(255,255,255,.5);
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  margin-bottom: 8px;
}

.kanban-card__footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
}

.kanban-card__date {
  font-size: .7rem;
  color: var(--a-muted);
}

.kanban-card__phone {
  font-size: .7rem;
  color: var(--a-primary);
  text-decoration: none;
}

.kanban-status-select {
  font-size: .7rem;
  padding: 3px 22px 3px 8px;
  border-radius: 4px;
  background: rgba(255,255,255,.05);
  border: 1px solid var(--a-border);
  color: var(--a-text);
  font-family: var(--a-font);
  cursor: pointer;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' fill='none' stroke='%2364748b' stroke-width='2' viewBox='0 0 24 24'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 6px center;
}
</style>

<div class="kanban-board">
  <?php foreach ($statuses as $status_key => $status_meta): ?>
  <div class="kanban-col" data-col="<?= $status_key ?>">
    <div class="kanban-col__header" style="border-top: 3px solid <?= $status_meta['color'] ?>">
      <div class="kanban-col__title">
        <span class="kanban-col__dot" style="background:<?= $status_meta['color'] ?>"></span>
        <?= $status_meta['label'] ?>
      </div>
      <span class="kanban-col__count"><?= count($grouped[$status_key]) ?></span>
    </div>
    <div class="kanban-cards" id="col-<?= $status_key ?>">
      <?php foreach ($grouped[$status_key] as $lead): ?>
      <div class="kanban-card" data-id="<?= (int)$lead['id'] ?>">
        <div class="kanban-card__name"><?= h($lead['nome'] ?? 'Sem nome') ?></div>
        <div class="kanban-card__email"><?= h($lead['email'] ?? '—') ?></div>
        <?php if (!empty($lead['mensagem'])): ?>
        <div class="kanban-card__msg"><?= h($lead['mensagem']) ?></div>
        <?php endif; ?>
        <div class="kanban-card__footer">
          <span class="kanban-card__date"><?= format_date($lead['created_at']) ?></span>
          <?php if (!empty($lead['telefone'])): ?>
          <a href="https://wa.me/55<?= preg_replace('/\D/', '', $lead['telefone']) ?>"
             target="_blank" class="kanban-card__phone" title="WhatsApp">
            📱 <?= h($lead['telefone']) ?>
          </a>
          <?php endif; ?>
        </div>
        <div style="margin-top:8px">
          <select class="kanban-status-select" data-id="<?= (int)$lead['id'] ?>">
            <?php foreach ($statuses as $sk => $sm): ?>
            <option value="<?= $sk ?>" <?= $lead['status'] === $sk ? 'selected' : '' ?>><?= $sm['label'] ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if (empty($grouped[$status_key])): ?>
      <div style="padding:16px;text-align:center;font-size:.8rem;color:var(--a-muted)">Nenhum lead</div>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<?php else: ?>
<!-- ══ LISTA ══ -->
<div class="adm-card">
  <div class="adm-card__header">
    <span class="adm-card__title">Todos os Leads</span>
    <div class="filter-bar">
      <a href="?view=lista" class="<?= !isset($_GET['status']) ? 'active' : '' ?>">Todos (<?= count($leads) ?>)</a>
      <?php foreach ($statuses as $sk => $sm): ?>
      <a href="?view=lista&status=<?= $sk ?>" class="<?= ($_GET['status'] ?? '') === $sk ? 'active' : '' ?>">
        <?= $sm['label'] ?> (<?= count($grouped[$sk]) ?>)
      </a>
      <?php endforeach; ?>
    </div>
  </div>

  <?php
  $list_leads = $leads;
  if (isset($_GET['status']) && isset($grouped[$_GET['status']])) {
      $list_leads = $grouped[$_GET['status']];
  }
  ?>

  <?php if (empty($list_leads)): ?>
  <div class="empty-state">
    <div class="empty-state__icon"><svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div>
    <p class="empty-state__title">Nenhum lead</p>
  </div>
  <?php else: ?>
  <div class="adm-table-wrap">
    <table class="adm-table">
      <thead>
        <tr>
          <th>Nome</th><th>E-mail</th><th>Telefone</th><th>Mensagem</th><th>Status</th><th>Data</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($list_leads as $lead): ?>
        <tr>
          <td style="font-weight:600;color:#fff"><?= h($lead['nome'] ?? '—') ?></td>
          <td><?= h($lead['email'] ?? '—') ?></td>
          <td>
            <?php if (!empty($lead['telefone'])): ?>
            <a href="https://wa.me/55<?= preg_replace('/\D/','',$lead['telefone']) ?>" target="_blank" style="color:var(--a-primary)"><?= h($lead['telefone']) ?></a>
            <?php else: ?>—<?php endif; ?>
          </td>
          <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= h(mb_substr($lead['mensagem'] ?? '—', 0, 60)) ?></td>
          <td>
            <select class="lead-status-select" data-id="<?= (int)$lead['id'] ?>">
              <?php foreach ($statuses as $sk => $sm): ?>
              <option value="<?= $sk ?>" <?= $lead['status'] === $sk ? 'selected' : '' ?>><?= $sm['label'] ?></option>
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
<?php endif; ?>

<script>
/* Kanban status change */
document.querySelectorAll('.kanban-status-select, .lead-status-select').forEach(sel => {
    sel.addEventListener('change', async () => {
        const id     = sel.dataset.id;
        const status = sel.value;
        const res = await fetch('/cms/leads/update-status.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `id=${id}&status=${status}&csrf_token=${document.querySelector('meta[name=csrf]')?.content ?? ''}`
        });
        const data = await res.json();
        if (data.ok && sel.classList.contains('kanban-status-select')) {
            // Move card to new column
            const card = sel.closest('.kanban-card');
            const targetCol = document.getElementById('col-' + status);
            if (card && targetCol) {
                const empty = targetCol.querySelector('[style*="Nenhum lead"]');
                if (empty) empty.remove();
                targetCol.appendChild(card);
                // Update source column empty state
                const srcCol = card.parentElement;
                if (srcCol && srcCol.querySelectorAll('.kanban-card').length === 0) {
                    srcCol.innerHTML = '<div style="padding:16px;text-align:center;font-size:.8rem;color:var(--a-muted)">Nenhum lead</div>';
                }
                // Update counts
                updateCounts();
            }
        }
    });
});

function updateCounts() {
    document.querySelectorAll('.kanban-col').forEach(col => {
        const key   = col.dataset.col;
        const count = col.querySelectorAll('.kanban-card').length;
        const badge = col.querySelector('.kanban-col__count');
        if (badge) badge.textContent = count;
    });
}
</script>

<?php require_once dirname(__DIR__) . '/includes/foot.php'; ?>
