<?php
require_once dirname(__DIR__) . '/boot.php';
auth_check();

$page_title = 'Leads';
$active     = 'leads';
$pdo        = db();

$view = $_GET['view'] ?? 'kanban';

$statuses = [
    'novo'       => ['label' => 'Novos',       'color' => '#22d3ee', 'bg' => 'rgba(34,211,238,.08)'],
    'contactado' => ['label' => 'Contactados', 'color' => '#f59e0b', 'bg' => 'rgba(245,158,11,.08)'],
    'fechado'    => ['label' => 'Fechados',    'color' => '#22c55e', 'bg' => 'rgba(34,197,94,.08)'],
    'descartado' => ['label' => 'Descartados', 'color' => '#94a3b8', 'bg' => 'rgba(100,116,139,.08)'],
];

// Leads with comment count
try {
    $leads = $pdo->query(
        'SELECT l.*, COUNT(lc.id) AS comment_count
         FROM leads l
         LEFT JOIN lead_comments lc ON lc.lead_id = l.id
         GROUP BY l.id
         ORDER BY l.created_at DESC'
    )->fetchAll();
} catch (Exception $e) {
    // lead_comments table may not exist yet — run /cms/leads/leads-migrate.php
    $leads = $pdo->query('SELECT *, 0 AS comment_count FROM leads ORDER BY created_at DESC')->fetchAll();
}

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
  padding: 12px 14px;
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
  width: 8px; height: 8px;
  border-radius: 50%;
  flex-shrink: 0;
}
.kanban-col__actions {
  display: flex;
  align-items: center;
  gap: 6px;
}
.kanban-col__count {
  font-size: .75rem;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 100px;
  color: rgba(255,255,255,.6);
  background: rgba(255,255,255,.06);
}
.kanban-col__add {
  width: 24px; height: 24px;
  border-radius: 6px;
  background: rgba(255,255,255,.06);
  border: 1px solid var(--a-border);
  color: rgba(255,255,255,.5);
  cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: all .15s;
  flex-shrink: 0;
}
.kanban-col__add:hover {
  background: rgba(255,255,255,.1);
  color: #fff;
}

.kanban-cards {
  padding: 10px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  min-height: 80px;
  transition: background .15s;
}
.kanban-cards.drag-over {
  background: rgba(34,211,238,.05);
}

.kanban-card {
  background: var(--a-card-2);
  border: 1px solid var(--a-border);
  border-radius: 8px;
  padding: 12px;
  transition: border-color .18s, box-shadow .18s, opacity .15s;
  cursor: grab;
  user-select: none;
}
.kanban-card:active { cursor: grabbing; }
.kanban-card.dragging { opacity: .4; }
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
  margin-bottom: 6px;
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
  gap: 6px;
  margin-top: 8px;
}
.kanban-card__date { font-size: .7rem; color: var(--a-muted); }
.kanban-card__phone {
  font-size: .7rem;
  color: var(--a-primary);
  text-decoration: none;
}
.kanban-card__meta {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 8px;
}
.kanban-card__open {
  font-size: .7rem;
  color: var(--a-muted);
  background: none;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 3px;
  padding: 2px 0;
  transition: color .15s;
}
.kanban-card__open:hover { color: var(--a-primary); }
.kanban-card__comments {
  font-size: .7rem;
  color: var(--a-muted);
  display: flex;
  align-items: center;
  gap: 3px;
}
.kanban-card__comments.has-comments { color: var(--a-primary); }

/* ── Lead Detail Panel ── */
.lead-panel-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,.5);
  z-index: 1000;
}
.lead-panel-overlay.open { display: block; }

.lead-panel {
  position: fixed;
  top: 0; right: 0;
  width: 440px;
  max-width: 100vw;
  height: 100vh;
  background: var(--a-sidebar);
  border-left: 1px solid var(--a-border);
  display: flex;
  flex-direction: column;
  z-index: 1001;
  transform: translateX(100%);
  transition: transform .25s ease;
}
.lead-panel.open { transform: translateX(0); }

.lead-panel__header {
  padding: 16px 20px;
  border-bottom: 1px solid var(--a-border);
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-shrink: 0;
}
.lead-panel__title { font-size: .9375rem; font-weight: 700; color: #fff; }
.lead-panel__close {
  width: 32px; height: 32px;
  background: none;
  border: none;
  color: var(--a-muted);
  cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  border-radius: 6px;
  transition: all .15s;
}
.lead-panel__close:hover { background: rgba(255,255,255,.06); color: #fff; }

.lead-panel__body {
  flex: 1;
  overflow-y: auto;
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.lead-panel__field label {
  display: block;
  font-size: .75rem;
  font-weight: 600;
  color: var(--a-muted);
  text-transform: uppercase;
  letter-spacing: .06em;
  margin-bottom: 4px;
}
.lead-panel__field p {
  font-size: .875rem;
  color: rgba(255,255,255,.85);
}

.comments-section { border-top: 1px solid var(--a-border); padding-top: 16px; }
.comments-section__title {
  font-size: .8125rem;
  font-weight: 700;
  color: rgba(255,255,255,.6);
  text-transform: uppercase;
  letter-spacing: .06em;
  margin-bottom: 12px;
}
.comment-item {
  padding: 10px 12px;
  background: rgba(255,255,255,.03);
  border: 1px solid var(--a-border);
  border-radius: 8px;
  margin-bottom: 8px;
}
.comment-item__text { font-size: .875rem; color: rgba(255,255,255,.8); line-height: 1.5; }
.comment-item__meta { font-size: .7rem; color: var(--a-muted); margin-top: 4px; }

.comment-form { display: flex; flex-direction: column; gap: 8px; margin-top: 8px; }
.comment-form textarea {
  background: rgba(255,255,255,.05);
  border: 1px solid rgba(255,255,255,.12);
  border-radius: 8px;
  padding: 10px 12px;
  font-size: .875rem;
  color: #e2e8f0;
  font-family: var(--a-font);
  resize: none;
  min-height: 80px;
  outline: none;
  transition: border-color .18s;
}
.comment-form textarea:focus { border-color: var(--a-primary); }

/* ── Add Lead Modal ── */
.modal-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,.6);
  z-index: 1100;
  align-items: center;
  justify-content: center;
}
.modal-overlay.open { display: flex; }
.modal-box {
  background: var(--a-sidebar);
  border: 1px solid var(--a-border);
  border-radius: 14px;
  width: 480px;
  max-width: 95vw;
  padding: 24px;
}
.modal-box__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
}
.modal-box__title { font-size: 1rem; font-weight: 700; color: #fff; }
</style>

<div class="kanban-board">
  <?php foreach ($statuses as $status_key => $status_meta): ?>
  <div class="kanban-col" data-col="<?= $status_key ?>">
    <div class="kanban-col__header" style="border-top: 3px solid <?= $status_meta['color'] ?>">
      <div class="kanban-col__title">
        <span class="kanban-col__dot" style="background:<?= $status_meta['color'] ?>"></span>
        <?= $status_meta['label'] ?>
      </div>
      <div class="kanban-col__actions">
        <span class="kanban-col__count"><?= count($grouped[$status_key]) ?></span>
        <button class="kanban-col__add"
                onclick="openAddLead('<?= $status_key ?>')"
                title="Adicionar lead">
          <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        </button>
      </div>
    </div>
    <div class="kanban-cards" id="col-<?= $status_key ?>"
         ondragover="onDragOver(event)"
         ondragleave="onDragLeave(event)"
         ondrop="onDrop(event, '<?= $status_key ?>')">
      <?php foreach ($grouped[$status_key] as $lead): ?>
      <?php $ccount = (int)($lead['comment_count'] ?? 0); ?>
      <div class="kanban-card"
           draggable="true"
           data-id="<?= (int)$lead['id'] ?>"
           ondragstart="onDragStart(event)">
        <div class="kanban-card__name"><?= h($lead['nome'] ?? 'Sem nome') ?></div>
        <div class="kanban-card__email"><?= h($lead['email'] ?? '—') ?></div>
        <?php if (!empty($lead['mensagem'])): ?>
        <div class="kanban-card__msg"><?= h($lead['mensagem']) ?></div>
        <?php endif; ?>
        <div class="kanban-card__footer">
          <span class="kanban-card__date"><?= format_date($lead['created_at']) ?></span>
          <?php if (!empty($lead['telefone'])): ?>
          <a href="https://wa.me/55<?= preg_replace('/\D/', '', $lead['telefone']) ?>"
             target="_blank" class="kanban-card__phone" title="WhatsApp"
             onclick="event.stopPropagation()">
            📱 <?= h($lead['telefone']) ?>
          </a>
          <?php endif; ?>
        </div>
        <div class="kanban-card__meta">
          <button class="kanban-card__open" onclick="openLead(<?= (int)$lead['id'] ?>)">
            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            Detalhes
          </button>
          <span class="kanban-card__comments <?= $ccount > 0 ? 'has-comments' : '' ?>"
                id="comment-badge-<?= (int)$lead['id'] ?>">
            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            <?= $ccount ?>
          </span>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if (empty($grouped[$status_key])): ?>
      <div class="kanban-empty" style="padding:20px;text-align:center;font-size:.8rem;color:var(--a-muted)">
        Nenhum lead
      </div>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- ══ LEAD DETAIL PANEL ══ -->
<div class="lead-panel-overlay" id="panelOverlay" onclick="closePanel()"></div>
<div class="lead-panel" id="leadPanel">
  <div class="lead-panel__header">
    <span class="lead-panel__title" id="panelName">—</span>
    <button class="lead-panel__close" onclick="closePanel()">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
  </div>
  <div class="lead-panel__body" id="panelBody">
    <div style="color:var(--a-muted);font-size:.875rem">Carregando...</div>
  </div>
</div>

<!-- ══ ADD LEAD MODAL ══ -->
<div class="modal-overlay" id="addModal">
  <div class="modal-box">
    <div class="modal-box__header">
      <span class="modal-box__title">Novo Lead</span>
      <button class="lead-panel__close" onclick="closeAddModal()">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <input type="hidden" id="newLeadStatus" value="novo">
    <div class="form-grid">
      <div class="form-group form-group--full">
        <label>Nome <span style="color:#ef4444">*</span></label>
        <input type="text" id="newNome" placeholder="Nome completo">
      </div>
      <div class="form-group">
        <label>E-mail</label>
        <input type="email" id="newEmail" placeholder="email@exemplo.com">
      </div>
      <div class="form-group">
        <label>Telefone</label>
        <input type="text" id="newTelefone" placeholder="(41) 99999-9999">
      </div>
      <div class="form-group form-group--full">
        <label>Mensagem</label>
        <textarea id="newMensagem" rows="3" placeholder="Observações iniciais..."></textarea>
      </div>
    </div>
    <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:16px">
      <button class="btn btn-secondary btn-sm" onclick="closeAddModal()">Cancelar</button>
      <button class="btn btn-primary btn-sm" id="saveLeadBtn" onclick="saveLead()">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Criar Lead
      </button>
    </div>
  </div>
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

<script>
document.querySelectorAll('.lead-status-select').forEach(sel => {
    sel.addEventListener('change', async () => {
        await fetch('/cms/leads/update-status.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `id=${sel.dataset.id}&status=${sel.value}&csrf_token=${document.querySelector('meta[name=csrf]')?.content ?? ''}`
        });
    });
});
</script>
<?php endif; ?>

<?php if ($view === 'kanban'): ?>
<script>
const CSRF = document.querySelector('meta[name=csrf]')?.content ?? '';

/* ══ DRAG & DROP ══ */
let dragId = null;

function onDragStart(e) {
    dragId = e.currentTarget.dataset.id;
    e.currentTarget.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', dragId);
}

document.querySelectorAll('.kanban-card').forEach(c => {
    c.addEventListener('dragend', () => c.classList.remove('dragging'));
});

function onDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    e.currentTarget.classList.add('drag-over');
}

function onDragLeave(e) {
    e.currentTarget.classList.remove('drag-over');
}

async function onDrop(e, newStatus) {
    e.preventDefault();
    e.currentTarget.classList.remove('drag-over');
    const id = e.dataTransfer.getData('text/plain') || dragId;
    if (!id) return;

    const card = document.querySelector(`.kanban-card[data-id="${id}"]`);
    const targetCol = document.getElementById('col-' + newStatus);
    if (!card || !targetCol) return;

    const srcCol = card.parentElement;
    if (srcCol === targetCol) return;

    // Move DOM
    const empty = targetCol.querySelector('.kanban-empty');
    if (empty) empty.remove();
    targetCol.appendChild(card);

    if (srcCol.querySelectorAll('.kanban-card').length === 0) {
        srcCol.innerHTML = '<div class="kanban-empty" style="padding:20px;text-align:center;font-size:.8rem;color:var(--a-muted)">Nenhum lead</div>';
    }

    updateCounts();

    // Persist
    await fetch('/cms/leads/update-status.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id=${id}&status=${newStatus}&csrf_token=${CSRF}`
    });
}

function updateCounts() {
    document.querySelectorAll('.kanban-col').forEach(col => {
        const count = col.querySelectorAll('.kanban-card').length;
        const badge = col.querySelector('.kanban-col__count');
        if (badge) badge.textContent = count;
    });
}

/* ══ ADD LEAD ══ */
function openAddLead(status) {
    document.getElementById('newLeadStatus').value = status;
    document.getElementById('newNome').value    = '';
    document.getElementById('newEmail').value   = '';
    document.getElementById('newTelefone').value = '';
    document.getElementById('newMensagem').value = '';
    document.getElementById('addModal').classList.add('open');
    setTimeout(() => document.getElementById('newNome').focus(), 50);
}

function closeAddModal() {
    document.getElementById('addModal').classList.remove('open');
}

document.getElementById('addModal').addEventListener('click', e => {
    if (e.target === e.currentTarget) closeAddModal();
});

async function saveLead() {
    const nome = document.getElementById('newNome').value.trim();
    if (!nome) { document.getElementById('newNome').focus(); return; }

    const btn = document.getElementById('saveLeadBtn');
    btn.disabled = true;
    btn.textContent = 'Salvando...';

    const res = await fetch('/cms/leads/create.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            nome,
            email:    document.getElementById('newEmail').value.trim(),
            telefone: document.getElementById('newTelefone').value.trim(),
            mensagem: document.getElementById('newMensagem').value.trim(),
            status:   document.getElementById('newLeadStatus').value,
            csrf_token: CSRF
        })
    });

    const data = await res.json();
    btn.disabled = false;
    btn.innerHTML = '<svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Criar Lead';

    if (!data.ok) { alert('Erro: ' + data.error); return; }

    closeAddModal();
    const l = data.lead;
    const col = document.getElementById('col-' + l.status);
    if (!col) return;

    const empty = col.querySelector('.kanban-empty');
    if (empty) empty.remove();

    const card = document.createElement('div');
    card.className = 'kanban-card';
    card.draggable = true;
    card.dataset.id = l.id;
    card.addEventListener('dragend', () => card.classList.remove('dragging'));
    card.setAttribute('ondragstart', 'onDragStart(event)');
    card.innerHTML = `
        <div class="kanban-card__name">${esc(l.nome || 'Sem nome')}</div>
        <div class="kanban-card__email">${esc(l.email || '—')}</div>
        ${l.mensagem ? `<div class="kanban-card__msg">${esc(l.mensagem)}</div>` : ''}
        <div class="kanban-card__footer">
          <span class="kanban-card__date">${esc(l.created_at || '')}</span>
        </div>
        <div class="kanban-card__meta">
          <button class="kanban-card__open" onclick="openLead(${l.id})">
            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            Detalhes
          </button>
          <span class="kanban-card__comments" id="comment-badge-${l.id}">
            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            0
          </span>
        </div>`;
    col.appendChild(card);
    updateCounts();
}

/* ══ LEAD DETAIL PANEL ══ */
const STATUS_LABELS = <?= json_encode(array_map(fn($s) => $s['label'], $statuses)) ?>;
let currentLeadId = null;

async function openLead(id) {
    currentLeadId = id;
    document.getElementById('panelOverlay').classList.add('open');
    document.getElementById('leadPanel').classList.add('open');
    document.getElementById('panelName').textContent = 'Carregando...';
    document.getElementById('panelBody').innerHTML = '<div style="color:var(--a-muted);font-size:.875rem">Carregando...</div>';

    // Load lead data
    const res = await fetch(`/cms/leads/get.php?id=${id}&csrf_token=${CSRF}`);
    const data = await res.json();
    if (!data.ok) { document.getElementById('panelBody').innerHTML = '<p style="color:#ef4444">Erro ao carregar lead.</p>'; return; }
    const l = data.lead;

    document.getElementById('panelName').textContent = l.nome || 'Sem nome';

    // Load comments
    const cRes = await fetch(`/cms/leads/comments.php?lead_id=${id}`);
    const cData = await cRes.json();
    const comments = cData.comments || [];

    document.getElementById('panelBody').innerHTML = `
        <div class="lead-panel__field"><label>Nome</label><p>${esc(l.nome || '—')}</p></div>
        <div class="lead-panel__field"><label>E-mail</label><p>${l.email ? `<a href="mailto:${esc(l.email)}" style="color:var(--a-primary)">${esc(l.email)}</a>` : '—'}</p></div>
        <div class="lead-panel__field"><label>Telefone</label><p>${l.telefone ? `<a href="https://wa.me/55${l.telefone.replace(/\D/g,'')}" target="_blank" style="color:var(--a-primary)">📱 ${esc(l.telefone)}</a>` : '—'}</p></div>
        <div class="lead-panel__field"><label>Mensagem</label><p style="white-space:pre-wrap">${esc(l.mensagem || '—')}</p></div>
        <div class="lead-panel__field"><label>Status</label>
          <select class="panel-status-select" data-id="${l.id}" style="margin-top:4px;font-size:.8125rem;padding:6px 10px;border-radius:6px;background:rgba(255,255,255,.05);border:1px solid var(--a-border);color:var(--a-text);font-family:var(--a-font)">
            ${Object.entries(STATUS_LABELS).map(([k,v])=>`<option value="${k}" ${l.status===k?'selected':''}>${v}</option>`).join('')}
          </select>
        </div>
        <div class="lead-panel__field"><label>Origem</label><p>${esc(l.origem || '—')}</p></div>
        <div class="lead-panel__field"><label>Recebido em</label><p>${esc(l.created_at || '—')}</p></div>

        <div class="comments-section">
          <div class="comments-section__title">Comentários</div>
          <div id="commentsList">${renderComments(comments)}</div>
          <div class="comment-form">
            <textarea id="commentInput" placeholder="Adicionar comentário..."></textarea>
            <div style="display:flex;justify-content:flex-end">
              <button class="btn btn-primary btn-sm" onclick="addComment()">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                Comentar
              </button>
            </div>
          </div>
        </div>`;

    // Status change from panel
    document.querySelector('#panelBody .panel-status-select')?.addEventListener('change', async function() {
        await fetch('/cms/leads/update-status.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `id=${this.dataset.id}&status=${this.value}&csrf_token=${CSRF}`
        });
    });
}

function renderComments(comments) {
    if (!comments.length) return '<p style="font-size:.8rem;color:var(--a-muted);margin-bottom:8px">Nenhum comentário ainda.</p>';
    return comments.map(c => `
        <div class="comment-item">
          <div class="comment-item__text">${esc(c.content)}</div>
          <div class="comment-item__meta">${esc(c.user_name || 'Admin')} · ${esc(c.created_at)}</div>
        </div>`).join('');
}

async function addComment() {
    const input = document.getElementById('commentInput');
    const content = input.value.trim();
    if (!content) { input.focus(); return; }

    const res = await fetch('/cms/leads/comments.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ lead_id: currentLeadId, content, csrf_token: CSRF })
    });
    const data = await res.json();
    if (!data.ok) { alert('Erro ao salvar comentário.'); return; }

    input.value = '';
    const list = document.getElementById('commentsList');
    const c = data.comment;
    // Remove "no comments" placeholder
    list.querySelectorAll('p').forEach(p => p.remove());
    const el = document.createElement('div');
    el.className = 'comment-item';
    el.innerHTML = `<div class="comment-item__text">${esc(c.content)}</div><div class="comment-item__meta">${esc(c.user_name || 'Admin')} · ${esc(c.created_at)}</div>`;
    list.appendChild(el);

    // Update badge on card
    const badge = document.getElementById('comment-badge-' + currentLeadId);
    if (badge) {
        const cur = parseInt(badge.textContent.trim()) || 0;
        badge.textContent = cur + 1;
        badge.classList.add('has-comments');
    }
}

function closePanel() {
    document.getElementById('panelOverlay').classList.remove('open');
    document.getElementById('leadPanel').classList.remove('open');
    currentLeadId = null;
}

function esc(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>
<?php endif; ?>

<?php require_once dirname(__DIR__) . '/includes/foot.php'; ?>
