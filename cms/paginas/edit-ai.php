<?php
require_once dirname(__DIR__) . '/boot.php';
auth_check();

$pdo = db();
$id  = (int)($_GET['id'] ?? 0);

if (!$id) {
    flash_set('error', 'Página não especificada.');
    header('Location: ' . CMS_URL . '/paginas/');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM paginas WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$pagina = $stmt->fetch();

if (!$pagina) {
    flash_set('error', 'Página não encontrada.');
    header('Location: ' . CMS_URL . '/paginas/');
    exit;
}

$file_path = $pagina['file_path'];
if (!file_exists($file_path)) {
    flash_set('error', 'Arquivo PHP da página não encontrado: ' . $file_path);
    header('Location: ' . CMS_URL . '/paginas/');
    exit;
}

// Current content
$current_content = file_get_contents($file_path);

// History count
$hist_count = 0;
try {
    $hc = $pdo->prepare('SELECT COUNT(*) FROM paginas_history WHERE pagina_id = ?');
    $hc->execute([$id]);
    $hist_count = (int)$hc->fetchColumn();
} catch (Exception $e) {}

// Preview URL
$preview_url = $pagina['url'] ?? '/';

$page_title = 'Alterar com IA — ' . $pagina['title'];
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= h($page_title) ?> — Inunda CMS</title>
  <meta name="robots" content="noindex, nofollow">
  <meta name="csrf" content="<?= csrf_token() ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= CMS_URL ?>/assets/css/admin.css">
  <style>
    /* ── AI Editor Layout ── */
    body { overflow: hidden; }
    .ai-wrap {
      display: flex;
      height: 100vh;
      overflow: hidden;
    }

    /* Left: Chat */
    .ai-chat {
      width: 380px;
      flex-shrink: 0;
      background: var(--a-sidebar);
      border-right: 1px solid var(--a-border);
      display: flex;
      flex-direction: column;
      height: 100vh;
    }

    .ai-chat__header {
      padding: 16px 20px;
      border-bottom: 1px solid var(--a-border);
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-shrink: 0;
    }

    .ai-chat__title {
      font-size: .9375rem;
      font-weight: 700;
      color: #fff;
    }

    .ai-chat__page {
      font-size: .75rem;
      color: var(--a-muted);
      margin-top: 2px;
    }

    .ai-chat__messages {
      flex: 1;
      overflow-y: auto;
      padding: 16px;
      display: flex;
      flex-direction: column;
      gap: 12px;
      scrollbar-width: thin;
      scrollbar-color: rgba(255,255,255,0.08) transparent;
    }

    .ai-msg {
      max-width: 92%;
      padding: 10px 14px;
      border-radius: 10px;
      font-size: .875rem;
      line-height: 1.55;
    }

    .ai-msg--user {
      background: rgba(34,211,238,0.12);
      border: 1px solid rgba(34,211,238,0.2);
      color: #e2e8f0;
      align-self: flex-end;
    }

    .ai-msg--ai {
      background: rgba(255,255,255,0.05);
      border: 1px solid var(--a-border);
      color: rgba(255,255,255,0.85);
      align-self: flex-start;
    }

    .ai-msg--system {
      background: rgba(34,197,94,0.08);
      border: 1px solid rgba(34,197,94,0.2);
      color: #86efac;
      align-self: stretch;
      font-size: .8125rem;
      text-align: center;
    }

    .ai-msg--error {
      background: rgba(239,68,68,0.08);
      border: 1px solid rgba(239,68,68,0.2);
      color: #fca5a5;
      align-self: stretch;
      font-size: .8125rem;
    }

    .ai-msg__typing {
      display: flex;
      gap: 4px;
      align-items: center;
      padding: 10px 14px;
      background: rgba(255,255,255,0.05);
      border: 1px solid var(--a-border);
      border-radius: 10px;
      align-self: flex-start;
    }

    .ai-msg__typing span {
      width: 6px;
      height: 6px;
      background: var(--a-muted);
      border-radius: 50%;
      animation: typing 1.2s infinite;
    }
    .ai-msg__typing span:nth-child(2) { animation-delay: .2s; }
    .ai-msg__typing span:nth-child(3) { animation-delay: .4s; }

    @keyframes typing {
      0%, 60%, 100% { opacity: .3; transform: translateY(0); }
      30% { opacity: 1; transform: translateY(-4px); }
    }

    .ai-chat__input-area {
      padding: 12px 16px;
      border-top: 1px solid var(--a-border);
      flex-shrink: 0;
    }

    .ai-chat__input-row {
      display: flex;
      gap: 8px;
      align-items: flex-end;
    }

    .ai-chat__textarea {
      flex: 1;
      background: rgba(255,255,255,0.05);
      border: 1px solid rgba(255,255,255,0.12);
      border-radius: 8px;
      padding: 10px 14px;
      font-size: .875rem;
      color: #e2e8f0;
      font-family: var(--a-font);
      resize: none;
      min-height: 42px;
      max-height: 120px;
      outline: none;
      transition: border-color .18s;
      line-height: 1.5;
    }

    .ai-chat__textarea:focus {
      border-color: var(--a-primary);
    }

    .ai-chat__send {
      width: 40px;
      height: 40px;
      background: var(--a-primary);
      border: none;
      border-radius: 8px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      transition: all .18s;
      color: #0a1122;
    }
    .ai-chat__send:hover { background: var(--a-primary-dark); }
    .ai-chat__send:disabled { opacity: .5; cursor: not-allowed; }

    .ai-chat__actions {
      display: flex;
      gap: 8px;
      margin-top: 10px;
      flex-wrap: wrap;
    }

    /* Right: Preview */
    .ai-preview {
      flex: 1;
      display: flex;
      flex-direction: column;
      background: var(--a-bg);
      overflow: hidden;
    }

    .ai-preview__bar {
      height: 52px;
      background: var(--a-sidebar);
      border-bottom: 1px solid var(--a-border);
      display: flex;
      align-items: center;
      padding: 0 20px;
      gap: 16px;
      flex-shrink: 0;
    }

    .ai-preview__bar-title {
      font-size: .875rem;
      font-weight: 600;
      color: rgba(255,255,255,.7);
      flex: 1;
    }

    .ai-preview__status {
      font-size: .75rem;
      padding: 4px 10px;
      border-radius: 100px;
      font-weight: 600;
    }

    .ai-preview__status--live {
      background: rgba(34,197,94,.12);
      color: #22c55e;
    }

    .ai-preview__status--modified {
      background: rgba(245,158,11,.12);
      color: #f59e0b;
    }

    .ai-preview__iframe {
      flex: 1;
      border: none;
      width: 100%;
      height: 100%;
    }

    /* Sidebar back button */
    .ai-back {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: .8125rem;
      color: var(--a-muted);
      text-decoration: none;
      padding: 6px 0;
      transition: color .18s;
    }
    .ai-back:hover { color: var(--a-primary); }

    /* History panel */
    .history-list {
      display: flex;
      flex-direction: column;
      gap: 6px;
      max-height: 200px;
      overflow-y: auto;
    }
    .history-item {
      padding: 8px 12px;
      background: rgba(255,255,255,.03);
      border: 1px solid var(--a-border);
      border-radius: 6px;
      font-size: .8125rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
  </style>
</head>
<body>
<div class="ai-wrap">

  <!-- ══ CHAT PANEL ══ -->
  <div class="ai-chat">

    <div class="ai-chat__header">
      <div>
        <div class="ai-chat__title">Alterar com IA</div>
        <div class="ai-chat__page"><?= h($pagina['title']) ?></div>
      </div>
      <a href="<?= CMS_URL ?>/paginas/" class="ai-back">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        Voltar
      </a>
    </div>

    <div class="ai-chat__messages" id="chatMessages">
      <div class="ai-msg ai-msg--system">
        Olá! Descreva o que deseja alterar nesta página. A IA irá modificar o código e você poderá visualizar o preview antes de publicar.
      </div>
    </div>

    <div class="ai-chat__input-area">
      <div class="ai-chat__input-row">
        <textarea
          id="chatInput"
          class="ai-chat__textarea"
          placeholder="Ex: Mude a cor do botão principal para verde, adicione uma seção de FAQ..."
          rows="2"
        ></textarea>
        <button class="ai-chat__send" id="sendBtn" title="Enviar (Ctrl+Enter)">
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
        </button>
      </div>

      <div class="ai-chat__actions" id="chatActions" style="display:none">
        <button class="btn btn-primary btn-sm" id="publishBtn">
          <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/></svg>
          Publicar
        </button>
        <button class="btn btn-secondary btn-sm" id="discardBtn">
          Descartar alterações
        </button>
        <?php if ($hist_count > 0): ?>
        <button class="btn btn-secondary btn-sm" id="historyBtn">
          <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-4.95"/></svg>
          Histórico (<?= $hist_count ?>)
        </button>
        <?php endif; ?>
      </div>

      <?php if ($hist_count === 0): ?>
      <!-- Show history btn always if exists after publish -->
      <div id="historySection" style="display:none;margin-top:10px"></div>
      <?php else: ?>
      <div id="historySection" style="display:none;margin-top:10px"></div>
      <?php endif; ?>
    </div>
  </div>

  <!-- ══ PREVIEW PANEL ══ -->
  <div class="ai-preview">
    <div class="ai-preview__bar">
      <span class="ai-preview__bar-title">Preview — <?= h($pagina['url'] ?? '/') ?></span>
      <span class="ai-preview__status ai-preview__status--live" id="previewStatus">Versão atual</span>
      <a href="<?= h($pagina['url'] ?? '/') ?>" target="_blank" class="btn btn-secondary btn-sm">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
        Abrir site
      </a>
    </div>
    <iframe
      id="previewFrame"
      class="ai-preview__iframe"
      src="<?= h($pagina['url'] ?? '/') ?>"
    ></iframe>
  </div>

</div>

<!-- Data for JS -->
<script>
const CMS_URL     = '<?= CMS_URL ?>';
const PAGE_ID     = <?= $id ?>;
const PAGE_URL    = '<?= h($pagina['url'] ?? '/') ?>';
const CSRF_TOKEN  = document.querySelector('meta[name=csrf]').content;
</script>

<script>
/* ══ Chat State ══ */
let pendingContent = null; // PHP content waiting to be published

const messages   = document.getElementById('chatMessages');
const input      = document.getElementById('chatInput');
const sendBtn    = document.getElementById('sendBtn');
const actions    = document.getElementById('chatActions');
const publishBtn = document.getElementById('publishBtn');
const discardBtn = document.getElementById('discardBtn');
const histBtn    = document.getElementById('historyBtn');
const histSection = document.getElementById('historySection');
const previewFrame = document.getElementById('previewFrame');
const previewStatus = document.getElementById('previewStatus');

/* ── Add message bubble ── */
function addMsg(text, type) {
    const d = document.createElement('div');
    d.className = 'ai-msg ai-msg--' + type;
    d.textContent = text;
    messages.appendChild(d);
    messages.scrollTop = messages.scrollHeight;
    return d;
}

/* ── Typing indicator ── */
function addTyping() {
    const d = document.createElement('div');
    d.className = 'ai-msg__typing';
    d.innerHTML = '<span></span><span></span><span></span>';
    messages.appendChild(d);
    messages.scrollTop = messages.scrollHeight;
    return d;
}

/* ── Send message ── */
async function sendMessage() {
    const text = input.value.trim();
    if (!text || sendBtn.disabled) return;

    addMsg(text, 'user');
    input.value = '';
    input.style.height = 'auto';

    sendBtn.disabled = true;
    const typing = addTyping();

    try {
        const res = await fetch(CMS_URL + '/paginas/ai-api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                page_id:    PAGE_ID,
                instruction: text,
                csrf_token:  CSRF_TOKEN
            })
        });

        const data = await res.json();
        typing.remove();

        if (!data.ok) {
            addMsg('Erro: ' + (data.error || 'Falha na chamada à IA.'), 'error');
        } else {
            pendingContent = data.content;
            addMsg(data.message || 'Alterações prontas! Verifique o preview e publique se estiver satisfeito.', 'ai');

            // Load preview
            loadPreview(data.preview_token);
            previewStatus.textContent = 'Versão modificada';
            previewStatus.className = 'ai-preview__status ai-preview__status--modified';
            actions.style.display = 'flex';
        }
    } catch (err) {
        typing.remove();
        addMsg('Erro de conexão. Verifique sua internet e tente novamente.', 'error');
    }

    sendBtn.disabled = false;
    input.focus();
}

/* ── Load preview via temp file ── */
function loadPreview(token) {
    previewFrame.src = CMS_URL + '/paginas/preview.php?token=' + encodeURIComponent(token) + '&t=' + Date.now();
}

/* ── Publish ── */
publishBtn?.addEventListener('click', async () => {
    if (!pendingContent) return;
    publishBtn.disabled = true;
    publishBtn.textContent = 'Publicando...';

    try {
        const res = await fetch(CMS_URL + '/paginas/publish.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                page_id:    PAGE_ID,
                content:    pendingContent,
                csrf_token: CSRF_TOKEN
            })
        });
        const data = await res.json();

        if (data.ok) {
            addMsg('✓ Página publicada com sucesso!', 'system');
            pendingContent = null;
            actions.style.display = 'none';
            previewFrame.src = PAGE_URL + '?t=' + Date.now();
            previewStatus.textContent = 'Versão atual';
            previewStatus.className = 'ai-preview__status ai-preview__status--live';
        } else {
            addMsg('Erro ao publicar: ' + (data.error || ''), 'error');
        }
    } catch (e) {
        addMsg('Erro de conexão ao publicar.', 'error');
    }

    publishBtn.disabled = false;
    publishBtn.textContent = 'Publicar';
});

/* ── Discard ── */
discardBtn?.addEventListener('click', () => {
    pendingContent = null;
    actions.style.display = 'none';
    previewFrame.src = PAGE_URL + '?t=' + Date.now();
    previewStatus.textContent = 'Versão atual';
    previewStatus.className = 'ai-preview__status ai-preview__status--live';
    addMsg('Alterações descartadas. A página voltou ao estado original.', 'system');
});

/* ── History ── */
histBtn?.addEventListener('click', async () => {
    if (histSection.style.display === 'block') {
        histSection.style.display = 'none';
        return;
    }
    histSection.innerHTML = '<div style="font-size:.75rem;color:#64748b;margin-bottom:6px">Carregando histórico...</div>';
    histSection.style.display = 'block';

    try {
        const res = await fetch(CMS_URL + '/paginas/history.php?id=' + PAGE_ID + '&csrf_token=' + CSRF_TOKEN);
        const data = await res.json();

        if (!data.ok || !data.items.length) {
            histSection.innerHTML = '<div style="font-size:.8rem;color:#64748b">Nenhum histórico ainda.</div>';
            return;
        }

        histSection.innerHTML = '<div class="history-list">' +
            data.items.map(item => `
                <div class="history-item">
                    <span>${item.saved_at}</span>
                    <button class="btn btn-secondary btn-xs"
                            onclick="revertTo(${item.id}, '${item.saved_at}')">
                        Reverter
                    </button>
                </div>
            `).join('') +
        '</div>';
    } catch (e) {
        histSection.innerHTML = '<div style="font-size:.8rem;color:#ef4444">Erro ao carregar histórico.</div>';
    }
});

/* ── Revert ── */
async function revertTo(histId, date) {
    if (!confirm('Reverter para a versão de ' + date + '?')) return;

    try {
        const res = await fetch(CMS_URL + '/paginas/revert.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ history_id: histId, page_id: PAGE_ID, csrf_token: CSRF_TOKEN })
        });
        const data = await res.json();

        if (data.ok) {
            addMsg('✓ Página revertida para a versão de ' + date, 'system');
            histSection.style.display = 'none';
            previewFrame.src = PAGE_URL + '?t=' + Date.now();
            previewStatus.textContent = 'Versão atual';
            previewStatus.className = 'ai-preview__status ai-preview__status--live';
        } else {
            addMsg('Erro ao reverter: ' + (data.error || ''), 'error');
        }
    } catch (e) {
        addMsg('Erro de conexão ao reverter.', 'error');
    }
}

/* ── Input events ── */
sendBtn.addEventListener('click', sendMessage);

input.addEventListener('keydown', e => {
    if (e.key === 'Enter' && e.ctrlKey) {
        e.preventDefault();
        sendMessage();
    }
});

input.addEventListener('input', () => {
    input.style.height = 'auto';
    input.style.height = Math.min(input.scrollHeight, 120) + 'px';
});

input.focus();
</script>
</body>
</html>
