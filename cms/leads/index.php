<?php
require_once dirname(__DIR__) . '/boot.php';
auth_check();

$page_title = 'Leads';
$active     = 'leads';
$pdo        = db();

$view = $_GET['view'] ?? 'kanban';

$statuses = [
    'novo'       => ['label' => 'Novos',       'color' => '#22d3ee'],
    'contactado' => ['label' => 'Contactados', 'color' => '#f59e0b'],
    'fechado'    => ['label' => 'Fechados',    'color' => '#22c55e'],
    'descartado' => ['label' => 'Descartados', 'color' => '#94a3b8'],
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
<style>
.kanban-board {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  align-items: start;
}
@media (max-width: 1100px) { .kanban-board { grid-template-columns: repeat(2,1fr); } }
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
.kanban-col__dot { width:8px;height:8px;border-radius:50%;flex-shrink:0; }
.kanban-col__right { display:flex;align-items:center;gap:6px; }
.kanban-col__count {
  font-size:.75rem;font-weight:600;padding:2px 8px;border-radius:100px;
  color:rgba(255,255,255,.6);background:rgba(255,255,255,.06);
}
.kanban-col__add {
  width:24px;height:24px;border-radius:6px;
  background:rgba(255,255,255,.06);border:1px solid var(--a-border);
  color:rgba(255,255,255,.5);cursor:pointer;
  display:flex;align-items:center;justify-content:center;
  transition:all .15s;flex-shrink:0;
}
.kanban-col__add:hover { background:rgba(255,255,255,.12);color:#fff; }

.kanban-cards {
  padding:10px;display:flex;flex-direction:column;gap:8px;
  min-height:80px;transition:background .15s;
}
.kanban-cards.drag-over { background:rgba(34,211,238,.06); }

.kanban-card {
  background:var(--a-card-2);border:1px solid var(--a-border);
  border-radius:8px;padding:12px;
  transition:border-color .18s,box-shadow .18s,opacity .15s;
  cursor:grab;user-select:none;
}
.kanban-card:active { cursor:grabbing; }
.kanban-card.dragging { opacity:.35; }
.kanban-card:hover { border-color:rgba(255,255,255,.18);box-shadow:0 4px 16px rgba(0,0,0,.2); }

.kanban-card__name { font-size:.875rem;font-weight:600;color:#fff;margin-bottom:3px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }
.kanban-card__email { font-size:.75rem;color:var(--a-muted);margin-bottom:6px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }
.kanban-card__msg { font-size:.75rem;color:rgba(255,255,255,.45);display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;margin-bottom:8px; }
.kanban-card__footer { display:flex;align-items:center;justify-content:space-between;gap:6px;flex-wrap:wrap; }
.kanban-card__date { font-size:.7rem;color:var(--a-muted); }
.kanban-card__actions { display:flex;align-items:center;gap:8px; }
.kanban-card__edit {
  font-size:.7rem;color:var(--a-muted);text-decoration:none;
  display:flex;align-items:center;gap:3px;padding:2px 6px;
  border-radius:4px;border:1px solid var(--a-border);
  transition:all .15s;
}
.kanban-card__edit:hover { color:var(--a-primary);border-color:var(--a-primary); }
.kanban-card__comments { font-size:.7rem;color:var(--a-muted);display:flex;align-items:center;gap:3px; }
.kanban-card__comments.has { color:var(--a-primary); }
.kanban-empty { padding:20px;text-align:center;font-size:.8rem;color:var(--a-muted); }
</style>

<div class="kanban-board">
  <?php foreach ($statuses as $sk => $sm): ?>
  <div class="kanban-col" data-col="<?= $sk ?>">
    <div class="kanban-col__header" style="border-top:3px solid <?= $sm['color'] ?>">
      <div class="kanban-col__title">
        <span class="kanban-col__dot" style="background:<?= $sm['color'] ?>"></span>
        <?= $sm['label'] ?>
      </div>
      <div class="kanban-col__right">
        <span class="kanban-col__count"><?= count($grouped[$sk]) ?></span>
        <button class="kanban-col__add" onclick="openAdd('<?= $sk ?>')" title="Novo lead">
          <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        </button>
      </div>
    </div>
    <div class="kanban-cards" id="col-<?= $sk ?>"
         ondragover="onDragOver(event)"
         ondragleave="onDragLeave(event)"
         ondrop="onDrop(event,'<?= $sk ?>')">
      <?php foreach ($grouped[$sk] as $lead): ?>
      <?php $cc = (int)($lead['comment_count'] ?? 0); ?>
      <div class="kanban-card"
           draggable="true"
           data-id="<?= (int)$lead['id'] ?>"
           ondragstart="onDragStart(event)"
           ondragend="this.classList.remove('dragging')">
        <div class="kanban-card__name"><?= h($lead['nome'] ?? 'Sem nome') ?></div>
        <div class="kanban-card__email"><?= h($lead['email'] ?? '—') ?></div>
        <?php if (!empty($lead['mensagem'])): ?>
        <div class="kanban-card__msg"><?= h($lead['mensagem']) ?></div>
        <?php endif; ?>
        <div class="kanban-card__footer">
          <span class="kanban-card__date"><?= format_date($lead['created_at']) ?></span>
          <div class="kanban-card__actions">
            <span class="kanban-card__comments <?= $cc > 0 ? 'has' : '' ?>">
              <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
              <?= $cc ?>
            </span>
            <a href="<?= CMS_URL ?>/leads/edit.php?id=<?= (int)$lead['id'] ?>"
               class="kanban-card__edit"
               onclick="event.stopPropagation()">
              <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              Editar
            </a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if (empty($grouped[$sk])): ?>
      <div class="kanban-empty">Nenhum lead</div>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- ══ ADD LEAD MODAL ══ -->
<div id="addModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:1000;align-items:center;justify-content:center">
  <div style="background:var(--a-sidebar);border:1px solid var(--a-border);border-radius:14px;width:480px;max-width:95vw;padding:24px">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
      <span style="font-size:1rem;font-weight:700;color:#fff">Novo Lead</span>
      <button onclick="closeAdd()" style="background:none;border:none;color:var(--a-muted);cursor:pointer;font-size:1.25rem;line-height:1;padding:4px">×</button>
    </div>
    <input type="hidden" id="newStatus" value="novo">
    <div class="form-grid">
      <div class="form-group form-group--full">
        <label>Nome *</label>
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
        <textarea id="newMensagem" rows="3"></textarea>
      </div>
    </div>
    <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:16px">
      <button class="btn btn-secondary btn-sm" onclick="closeAdd()">Cancelar</button>
      <button class="btn btn-primary btn-sm" id="saveBtn" onclick="saveLead()">Criar Lead</button>
    </div>
  </div>
</div>

<script>
const CSRF = document.querySelector('meta[name=csrf]')?.content ?? '';

/* ── Drag & Drop ── */
let dragId = null;

function onDragStart(e) {
    dragId = e.currentTarget.dataset.id;
    e.currentTarget.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', dragId);
}

function onDragOver(e) {
    e.preventDefault();
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
    const target = document.getElementById('col-' + newStatus);
    if (!card || !target || card.parentElement === target) return;

    const src = card.parentElement;
    const empty = target.querySelector('.kanban-empty');
    if (empty) empty.remove();
    target.appendChild(card);

    if (!src.querySelector('.kanban-card')) {
        src.innerHTML = '<div class="kanban-empty">Nenhum lead</div>';
    }
    updateCounts();

    fetch('/cms/leads/update-status.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id=${id}&status=${newStatus}&csrf_token=${CSRF}`
    });
}

function updateCounts() {
    document.querySelectorAll('.kanban-col').forEach(col => {
        const n = col.querySelectorAll('.kanban-card').length;
        col.querySelector('.kanban-col__count').textContent = n;
    });
}

/* ── Add Lead ── */
function openAdd(status) {
    document.getElementById('newStatus').value  = status;
    document.getElementById('newNome').value    = '';
    document.getElementById('newEmail').value   = '';
    document.getElementById('newTelefone').value = '';
    document.getElementById('newMensagem').value = '';
    const m = document.getElementById('addModal');
    m.style.display = 'flex';
    setTimeout(() => document.getElementById('newNome').focus(), 50);
}

function closeAdd() {
    document.getElementById('addModal').style.display = 'none';
}

document.getElementById('addModal').addEventListener('click', e => {
    if (e.target === e.currentTarget) closeAdd();
});

async function saveLead() {
    const nome = document.getElementById('newNome').value.trim();
    if (!nome) { document.getElementById('newNome').focus(); return; }

    const btn = document.getElementById('saveBtn');
    btn.disabled = true; btn.textContent = 'Salvando...';

    const res = await fetch('/cms/leads/create.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            nome,
            email:    document.getElementById('newEmail').value.trim(),
            telefone: document.getElementById('newTelefone').value.trim(),
            mensagem: document.getElementById('newMensagem').value.trim(),
            status:   document.getElementById('newStatus').value,
            csrf_token: CSRF
        })
    });
    const data = await res.json();
    btn.disabled = false; btn.textContent = 'Criar Lead';

    if (!data.ok) { alert('Erro: ' + data.error); return; }
    closeAdd();

    const l = data.lead;
    const col = document.getElementById('col-' + l.status);
    if (!col) return;

    const empty = col.querySelector('.kanban-empty');
    if (empty) empty.remove();

    const card = document.createElement('div');
    card.className = 'kanban-card';
    card.draggable = true;
    card.dataset.id = l.id;
    card.setAttribute('ondragstart', 'onDragStart(event)');
    card.setAttribute('ondragend', "this.classList.remove('dragging')");
    card.innerHTML = `
        <div class="kanban-card__name">${esc(l.nome)}</div>
        <div class="kanban-card__email">${esc(l.email || '—')}</div>
        <div class="kanban-card__footer">
          <span class="kanban-card__date">${esc(l.created_at || '')}</span>
          <div class="kanban-card__actions">
            <span class="kanban-card__comments">
              <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
              0
            </span>
            <a href="/cms/leads/edit.php?id=${l.id}" class="kanban-card__edit" onclick="event.stopPropagation()">
              <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              Editar
            </a>
          </div>
        </div>`;
    col.appendChild(card);
    updateCounts();
}

function esc(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
</script>

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
      <thead><tr><th>Nome</th><th>E-mail</th><th>Telefone</th><th>Status</th><th>Coment.</th><th>Data</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($list_leads as $lead): ?>
        <tr>
          <td style="font-weight:600;color:#fff"><?= h($lead['nome'] ?? '—') ?></td>
          <td><?= h($lead['email'] ?? '—') ?></td>
          <td><?php if (!empty($lead['telefone'])): ?>
            <a href="https://wa.me/55<?= preg_replace('/\D/','',$lead['telefone']) ?>" target="_blank" style="color:var(--a-primary)"><?= h($lead['telefone']) ?></a>
          <?php else: ?>—<?php endif; ?></td>
          <td>
            <select class="lead-status-select" data-id="<?= (int)$lead['id'] ?>">
              <?php foreach ($statuses as $sk => $sm): ?>
              <option value="<?= $sk ?>" <?= $lead['status'] === $sk ? 'selected' : '' ?>><?= $sm['label'] ?></option>
              <?php endforeach; ?>
            </select>
          </td>
          <td style="color:<?= (int)$lead['comment_count'] > 0 ? 'var(--a-primary)' : 'var(--a-muted)' ?>;font-size:.8rem"><?= (int)$lead['comment_count'] ?></td>
          <td><?= format_date($lead['created_at']) ?></td>
          <td><a href="<?= CMS_URL ?>/leads/edit.php?id=<?= (int)$lead['id'] ?>" class="btn btn-secondary btn-xs">Editar</a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
<script>
const CSRF = document.querySelector('meta[name=csrf]')?.content ?? '';
document.querySelectorAll('.lead-status-select').forEach(sel => {
    sel.addEventListener('change', () => {
        fetch('/cms/leads/update-status.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `id=${sel.dataset.id}&status=${sel.value}&csrf_token=${CSRF}`
        });
    });
});
</script>
<?php endif; ?>

<?php require_once dirname(__DIR__) . '/includes/foot.php'; ?>
