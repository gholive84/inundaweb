<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title>Editor com IA — JZ Gráfica</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --red:        #EB4039;
      --red-dark:   #C9302B;
      --bg:         #0f172a;
      --sidebar:    #111827;
      --border:     rgba(255,255,255,0.08);
      --muted:      #64748b;
      --font:       'Inter', system-ui, sans-serif;
    }

    html, body { height: 100%; overflow: hidden; font-family: var(--font); background: var(--bg); color: #e2e8f0; }

    /* ── Layout ── */
    .wrap { display: flex; height: 100dvh; overflow: hidden; }

    /* ── Left panel ── */
    .panel {
      width: 380px;
      flex-shrink: 0;
      background: var(--sidebar);
      border-right: 1px solid var(--border);
      display: flex;
      flex-direction: column;
      height: 100dvh;
    }

    /* ── Header ── */
    .panel__head {
      padding: 18px 20px 14px;
      border-bottom: 1px solid var(--border);
      flex-shrink: 0;
    }

    .panel__logo {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 10px;
    }

    .panel__logo-badge {
      font-size: .65rem;
      font-weight: 700;
      letter-spacing: .08em;
      text-transform: uppercase;
      background: linear-gradient(135deg, var(--red), var(--red-dark));
      color: #fff;
      padding: 3px 8px;
      border-radius: 4px;
    }

    .panel__logo-name {
      font-size: .9375rem;
      font-weight: 700;
      color: #fff;
    }

    .panel__desc {
      font-size: .8125rem;
      color: var(--muted);
      line-height: 1.5;
    }

    /* ── Tips ── */
    .panel__tips {
      padding: 10px 20px;
      border-bottom: 1px solid var(--border);
      flex-shrink: 0;
      display: flex;
      gap: 6px;
      flex-wrap: wrap;
    }

    .tip-chip {
      font-size: .75rem;
      color: rgba(255,255,255,.5);
      background: rgba(255,255,255,.04);
      border: 1px solid var(--border);
      border-radius: 20px;
      padding: 4px 10px;
      cursor: pointer;
      transition: all .15s;
      white-space: nowrap;
    }
    .tip-chip:hover {
      color: #fff;
      border-color: rgba(235,64,57,.4);
      background: rgba(235,64,57,.07);
    }

    /* ── Messages ── */
    .messages {
      flex: 1;
      overflow-y: auto;
      padding: 16px;
      display: flex;
      flex-direction: column;
      gap: 10px;
      scrollbar-width: thin;
      scrollbar-color: rgba(255,255,255,.07) transparent;
    }

    .msg {
      max-width: 94%;
      padding: 10px 14px;
      border-radius: 12px;
      font-size: .875rem;
      line-height: 1.55;
    }

    .msg--user {
      background: rgba(235,64,57,.12);
      border: 1px solid rgba(235,64,57,.22);
      color: #fecaca;
      align-self: flex-end;
      border-bottom-right-radius: 4px;
    }

    .msg--ai {
      background: rgba(255,255,255,.05);
      border: 1px solid var(--border);
      color: rgba(255,255,255,.85);
      align-self: flex-start;
      border-bottom-left-radius: 4px;
    }

    .msg--system {
      background: rgba(34,197,94,.07);
      border: 1px solid rgba(34,197,94,.18);
      color: #86efac;
      align-self: stretch;
      font-size: .8125rem;
      text-align: center;
      border-radius: 8px;
    }

    .msg--error {
      background: rgba(239,68,68,.08);
      border: 1px solid rgba(239,68,68,.2);
      color: #fca5a5;
      align-self: stretch;
      font-size: .8125rem;
      border-radius: 8px;
    }

    .typing {
      display: flex;
      gap: 5px;
      align-items: center;
      padding: 12px 16px;
      background: rgba(255,255,255,.04);
      border: 1px solid var(--border);
      border-radius: 12px;
      align-self: flex-start;
    }
    .typing span {
      width: 6px; height: 6px;
      background: var(--muted);
      border-radius: 50%;
      animation: blink 1.2s infinite;
    }
    .typing span:nth-child(2) { animation-delay: .2s; }
    .typing span:nth-child(3) { animation-delay: .4s; }
    @keyframes blink {
      0%,60%,100% { opacity:.25; transform:translateY(0); }
      30%          { opacity:1;   transform:translateY(-4px); }
    }

    /* ── Input area ── */
    .input-area {
      padding: 12px 16px 16px;
      border-top: 1px solid var(--border);
      flex-shrink: 0;
    }

    .input-row {
      display: flex;
      gap: 8px;
      align-items: flex-end;
    }

    .textarea {
      flex: 1;
      background: rgba(255,255,255,.05);
      border: 1px solid rgba(255,255,255,.1);
      border-radius: 10px;
      padding: 10px 14px;
      font-size: .875rem;
      color: #e2e8f0;
      font-family: var(--font);
      resize: none;
      min-height: 42px;
      max-height: 120px;
      outline: none;
      transition: border-color .18s;
      line-height: 1.5;
    }
    .textarea::placeholder { color: rgba(255,255,255,.25); }
    .textarea:focus { border-color: rgba(235,64,57,.5); }

    .btn-icon {
      width: 42px; height: 42px;
      border: none;
      border-radius: 10px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      transition: all .18s;
    }

    .btn-send {
      background: linear-gradient(135deg, var(--red), var(--red-dark));
      color: #fff;
    }
    .btn-send:hover { filter: brightness(1.1); transform: translateY(-1px); }
    .btn-send:disabled { opacity: .45; cursor: not-allowed; transform: none; }

    .btn-mic {
      background: rgba(255,255,255,.06);
      border: 1px solid rgba(255,255,255,.1);
      color: rgba(255,255,255,.65);
    }
    .btn-mic:hover { background: rgba(255,255,255,.1); color: #fff; }
    .btn-mic:disabled { opacity: .35; cursor: not-allowed; }
    .btn-mic--rec {
      background: rgba(239,68,68,.15) !important;
      border-color: rgba(239,68,68,.45) !important;
      color: #ef4444 !important;
      animation: pulse-mic 1.5s ease-in-out infinite;
    }
    .btn-mic--proc {
      background: rgba(245,158,11,.1) !important;
      border-color: rgba(245,158,11,.3) !important;
      color: #f59e0b !important;
    }
    @keyframes pulse-mic {
      0%,100% { box-shadow: 0 0 0 0 rgba(239,68,68,.4); }
      50%      { box-shadow: 0 0 0 8px rgba(239,68,68,0); }
    }

    /* recording bar */
    .rec-bar {
      display: none;
      align-items: center;
      gap: 8px;
      padding: 6px 12px;
      background: rgba(239,68,68,.07);
      border: 1px solid rgba(239,68,68,.18);
      border-radius: 8px;
      margin-top: 8px;
      font-size: .8rem;
      color: #fca5a5;
    }
    .rec-bar.on { display: flex; }
    .rec-dot {
      width: 8px; height: 8px;
      background: #ef4444;
      border-radius: 50%;
      flex-shrink: 0;
      animation: pulse-mic 1.5s ease-in-out infinite;
    }
    .rec-timer { margin-left: auto; color: rgba(255,255,255,.35); font-variant-numeric: tabular-nums; }

    /* action buttons */
    .actions {
      display: none;
      gap: 8px;
      margin-top: 10px;
    }
    .actions.on { display: flex; }

    .btn-action {
      flex: 1;
      padding: 9px 14px;
      border-radius: 8px;
      font-size: .8125rem;
      font-weight: 600;
      cursor: pointer;
      border: none;
      font-family: var(--font);
      transition: all .18s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
    }
    .btn-publish {
      background: linear-gradient(135deg, var(--red), var(--red-dark));
      color: #fff;
    }
    .btn-publish:hover { filter: brightness(1.1); }
    .btn-publish:disabled { opacity: .5; cursor: not-allowed; }

    .btn-discard {
      background: rgba(255,255,255,.06);
      border: 1px solid var(--border);
      color: rgba(255,255,255,.6);
    }
    .btn-discard:hover { background: rgba(255,255,255,.1); color: #fff; }

    /* ── Right: Preview ── */
    .preview {
      flex: 1;
      display: flex;
      flex-direction: column;
      background: #fff;
      overflow: hidden;
    }

    .preview__bar {
      height: 48px;
      background: var(--sidebar);
      border-bottom: 1px solid var(--border);
      display: flex;
      align-items: center;
      padding: 0 18px;
      gap: 12px;
      flex-shrink: 0;
    }

    .preview__dots { display: flex; gap: 6px; }
    .preview__dots span {
      width: 11px; height: 11px;
      border-radius: 50%;
    }
    .preview__dots span:nth-child(1) { background: #ef4444; }
    .preview__dots span:nth-child(2) { background: #f59e0b; }
    .preview__dots span:nth-child(3) { background: #22c55e; }

    .preview__url {
      flex: 1;
      background: rgba(255,255,255,.06);
      border: 1px solid var(--border);
      border-radius: 6px;
      padding: 5px 12px;
      font-size: .8125rem;
      color: rgba(255,255,255,.45);
      font-family: 'SF Mono', 'Fira Code', monospace;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .preview__status {
      font-size: .7rem;
      font-weight: 700;
      letter-spacing: .06em;
      text-transform: uppercase;
      padding: 3px 9px;
      border-radius: 20px;
    }
    .status--live     { background: rgba(34,197,94,.1);  color: #22c55e; }
    .status--modified { background: rgba(245,158,11,.1); color: #f59e0b; }

    .preview__iframe {
      flex: 1;
      border: none;
      width: 100%;
      height: 100%;
    }

    /* ── Spin animation ── */
    @keyframes spin { to { transform: rotate(360deg); } }

    /* ── Mobile tabs ── */
    .mob-tabs {
      display: none;
      flex-shrink: 0;
      background: var(--sidebar);
      border-bottom: 1px solid var(--border);
    }
    .mob-tab {
      flex: 1;
      padding: 12px 0;
      background: none;
      border: none;
      font-family: var(--font);
      font-size: .8125rem;
      font-weight: 600;
      color: rgba(255,255,255,.4);
      cursor: pointer;
      border-bottom: 2px solid transparent;
      transition: all .18s;
    }
    .mob-tab.active {
      color: #fff;
      border-bottom-color: var(--red);
    }

    /* ── Input safe area ── */
    .input-area {
      padding-bottom: calc(16px + env(safe-area-inset-bottom, 0px));
    }

    /* ── Responsive ── */
    @media (max-width: 767px) {
      .wrap { flex-direction: column; }

      .mob-tabs { display: flex; }

      .panel {
        width: 100%;
        height: 0;
        flex: 1;
        border-right: none;
        border-bottom: 1px solid var(--border);
        display: none;
      }
      .panel.mob-active { display: flex; }

      .preview {
        display: none;
      }
      .preview.mob-active { display: flex; }

      .panel__tips { overflow-x: auto; flex-wrap: nowrap; padding-bottom: 8px; }
      .panel__tips::-webkit-scrollbar { height: 3px; }
      .panel__tips::-webkit-scrollbar-thumb { background: rgba(255,255,255,.1); border-radius: 2px; }

      .preview__bar { display: none; }
      .preview__iframe { height: 100%; }
    }
  </style>
</head>
<body>
<div class="wrap">

  <!-- ══ ABAS MOBILE ══ -->
  <div class="mob-tabs" id="mobTabs">
    <button class="mob-tab active" id="tabChat" onclick="switchTab('chat')">💬 Chat IA</button>
    <button class="mob-tab" id="tabPreview" onclick="switchTab('preview')">👁️ Preview</button>
  </div>

  <!-- ══ PAINEL ESQUERDO ══ -->
  <div class="panel mob-active" id="panelChat">

    <!-- Cabeçalho -->
    <div class="panel__head">
      <div class="panel__logo">
        <span class="panel__logo-name">JZ Gráfica</span>
        <span class="panel__logo-badge">IA Demo</span>
      </div>
      <p class="panel__desc">
        Descreva ou fale o que deseja alterar na página inicial. A IA modifica o site em tempo real.
      </p>
    </div>

    <!-- Sugestões rápidas -->
    <div class="panel__tips" id="tips">
      <span class="tip-chip">Mude o título do hero</span>
      <span class="tip-chip">Troque a cor do botão CTA</span>
      <span class="tip-chip">Adicione um novo serviço</span>
      <span class="tip-chip">Altere o texto da seção Sobre</span>
      <span class="tip-chip">Mude o texto do rodapé</span>
    </div>

    <!-- Mensagens -->
    <div class="messages" id="msgs">
      <div class="msg msg--system">
        👋 Olá! Escreva ou grave um áudio descrevendo o que quer alterar na página inicial. Após a IA gerar as mudanças, você poderá visualizar e publicar.
      </div>
    </div>

    <!-- Área de input -->
    <div class="input-area">
      <div class="input-row">
        <textarea
          id="inp"
          class="textarea"
          placeholder="Ex: Mude o título para 'A melhor gráfica de Curitiba'..."
          rows="2"
        ></textarea>

        <!-- Botão microfone -->
        <button class="btn-icon btn-mic" id="micBtn" title="Gravar áudio">
          <svg id="micSvg" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <rect x="9" y="2" width="6" height="12" rx="3"/>
            <path d="M19 10a7 7 0 0 1-14 0"/>
            <line x1="12" y1="19" x2="12" y2="22"/>
            <line x1="8" y1="22" x2="16" y2="22"/>
          </svg>
        </button>

        <!-- Botão enviar -->
        <button class="btn-icon btn-send" id="sendBtn" title="Enviar (Ctrl+Enter)">
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <line x1="22" y1="2" x2="11" y2="13"/>
            <polygon points="22 2 15 22 11 13 2 9 22 2"/>
          </svg>
        </button>
      </div>

      <!-- Barra de gravação -->
      <div class="rec-bar" id="recBar">
        <span class="rec-dot"></span>
        <span>Gravando... clique no microfone para parar</span>
        <span class="rec-timer" id="recTimer">0:00</span>
      </div>

      <!-- Ações pós-geração -->
      <div class="actions" id="actions">
        <button class="btn-action btn-publish" id="publishBtn">
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
          Publicar
        </button>
        <button class="btn-action btn-discard" id="discardBtn">Desfazer</button>
      </div>
    </div>

  </div>

  <!-- ══ PREVIEW ══ -->
  <div class="preview" id="panelPreview">
    <div class="preview__bar">
      <div class="preview__dots">
        <span></span><span></span><span></span>
      </div>
      <div class="preview__url">jzcopias.com.br</div>
      <span class="preview__status status--live" id="status">Ao vivo</span>
    </div>
    <iframe class="preview__iframe" id="frame" src="/"></iframe>
  </div>

</div>

<script>
/* ══ Estado ══ */
let pendingBackups = [];   // array de nomes de backup gerados pelo ai.php
let pendingFiles   = [];   // array de arquivos modificados
let locked         = false;

const msgs      = document.getElementById('msgs');
const inp       = document.getElementById('inp');
const sendBtn   = document.getElementById('sendBtn');
const actions   = document.getElementById('actions');
const publishBtn= document.getElementById('publishBtn');
const discardBtn= document.getElementById('discardBtn');
const frame     = document.getElementById('frame');
const status    = document.getElementById('status');
const tips      = document.getElementById('tips');

/* ── Adicionar mensagem ── */
function addMsg(text, type) {
  const d = document.createElement('div');
  d.className = 'msg msg--' + type;
  d.textContent = text;
  msgs.appendChild(d);
  msgs.scrollTop = msgs.scrollHeight;
  return d;
}

/* ── Typing indicator ── */
function addTyping() {
  const d = document.createElement('div');
  d.className = 'typing';
  d.innerHTML = '<span></span><span></span><span></span>';
  msgs.appendChild(d);
  msgs.scrollTop = msgs.scrollHeight;
  return d;
}

/* ── Lock input ── */
function setLocked(val) {
  locked = val;
  sendBtn.disabled = val;
  inp.disabled = val;
  inp.placeholder = val
    ? 'Publique ou desfaça antes de enviar um novo pedido.'
    : 'Ex: Mude o título para "A melhor gráfica de Curitiba"...';
  if (!val) inp.focus();
}

/* ── Enviar instrução (aceita texto diretamente para uso do áudio) ── */
async function sendInstruction(text) {
  if (!text || locked) return;

  addMsg(text, 'user');
  inp.value = '';
  inp.style.height = 'auto';
  sendBtn.disabled = true;
  const t = addTyping();

  try {
    const res  = await fetch('/alterar/ai.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ instruction: text })
    });
    const data = await res.json();
    t.remove();

    if (!data.ok) {
      addMsg('Erro: ' + (data.error || 'Falha na chamada à IA.'), 'error');
      sendBtn.disabled = false;
    } else {
      pendingBackups = data.backups || [];
      pendingFiles   = data.files   || [];
      addMsg(data.message || 'Site atualizado! Confira o preview.', 'ai');
      // iframe aponta para o site real (já foi salvo pelo ai.php)
      frame.src = '/?t=' + Date.now();
      status.textContent = 'Modificado';
      status.className = 'preview__status status--modified';
      actions.classList.add('on');
      setLocked(true);
      maybeSwitchToPreview();
    }
  } catch (err) {
    t.remove();
    addMsg('Erro de conexão: ' + err.message, 'error');
    sendBtn.disabled = false;
  }
}

/* ── Wrapper para o botão Enviar ── */
function send() {
  const text = inp.value.trim();
  if (text) sendInstruction(text);
}

/* ── Publicar (arquivos já estão salvos — apenas confirma e libera) ── */
publishBtn.addEventListener('click', async () => {
  if (!pendingBackups.length) return;
  publishBtn.disabled = true;
  publishBtn.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation:spin .7s linear infinite"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4"/></svg> Confirmando...';

  try {
    const res  = await fetch('/alterar/publish.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ action: 'publish' })
    });
    const data = await res.json();

    if (data.ok) {
      addMsg('✓ Alterações confirmadas e publicadas!', 'system');
      pendingBackups = [];
      pendingFiles   = [];
      actions.classList.remove('on');
      status.textContent = 'Ao vivo';
      status.className = 'preview__status status--live';
      setLocked(false);
    } else {
      addMsg('Erro: ' + (data.error || ''), 'error');
    }
  } catch (e) {
    addMsg('Erro de conexão.', 'error');
  }

  publishBtn.disabled = false;
  publishBtn.innerHTML = '<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg> Publicar';
});

/* ── Desfazer (restaura backups) ── */
discardBtn.addEventListener('click', async () => {
  if (!pendingBackups.length) return;
  discardBtn.disabled = true;
  discardBtn.textContent = 'Desfazendo...';

  try {
    const res  = await fetch('/alterar/publish.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ action: 'discard', backups: pendingBackups, files: pendingFiles })
    });
    const data = await res.json();

    if (data.ok) {
      addMsg('Alterações desfeitas. Site restaurado.', 'system');
      pendingBackups = [];
      pendingFiles   = [];
      actions.classList.remove('on');
      frame.src = '/?t=' + Date.now();
      status.textContent = 'Ao vivo';
      status.className = 'preview__status status--live';
      setLocked(false);
    } else {
      addMsg('Erro ao desfazer: ' + (data.error || ''), 'error');
    }
  } catch (e) {
    addMsg('Erro de conexão.', 'error');
  }

  discardBtn.disabled = false;
  discardBtn.textContent = 'Desfazer';
});

/* ── Sugestões rápidas ── */
tips.querySelectorAll('.tip-chip').forEach(chip => {
  chip.addEventListener('click', () => {
    inp.value = chip.textContent;
    inp.focus();
    inp.style.height = 'auto';
    inp.style.height = Math.min(inp.scrollHeight, 120) + 'px';
  });
});

/* ── Eventos de input ── */
sendBtn.addEventListener('click', send);
inp.addEventListener('keydown', e => {
  if (e.key === 'Enter' && e.ctrlKey) { e.preventDefault(); send(); }
});
inp.addEventListener('input', () => {
  inp.style.height = 'auto';
  inp.style.height = Math.min(inp.scrollHeight, 120) + 'px';
});

inp.focus();

/* ══ Abas mobile ══ */
function switchTab(tab) {
  const chat    = document.getElementById('panelChat');
  const preview = document.getElementById('panelPreview');
  const tChat   = document.getElementById('tabChat');
  const tPrev   = document.getElementById('tabPreview');

  if (tab === 'chat') {
    chat.classList.add('mob-active');
    preview.classList.remove('mob-active');
    tChat.classList.add('active');
    tPrev.classList.remove('active');
    inp.focus();
  } else {
    preview.classList.add('mob-active');
    chat.classList.remove('mob-active');
    tPrev.classList.add('active');
    tChat.classList.remove('active');
  }
}

/* ── Ao gerar preview, auto-switch para aba preview no mobile ── */
function maybeSwitchToPreview() {
  if (window.innerWidth <= 767) switchTab('preview');
}

/* ══ Gravação de áudio ══ */
const micBtn  = document.getElementById('micBtn');
const recBar  = document.getElementById('recBar');
const recTimer= document.getElementById('recTimer');

const SVG_MIC = `<svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
  <rect x="9" y="2" width="6" height="12" rx="3"/>
  <path d="M19 10a7 7 0 0 1-14 0"/>
  <line x1="12" y1="19" x2="12" y2="22"/>
  <line x1="8" y1="22" x2="16" y2="22"/>
</svg>`;

const SVG_STOP = `<svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
  <rect x="5" y="5" width="14" height="14" rx="2"/>
</svg>`;

const SVG_SPIN = `<svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="animation:spin .7s linear infinite">
  <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4"/>
</svg>`;

let mediaRecorder = null;
let audioChunks   = [];
let isRecording   = false;
let timerInterval = null;
let recSeconds    = 0;

if (!navigator.mediaDevices?.getUserMedia || !window.MediaRecorder) {
  micBtn.disabled = true;
  micBtn.title = 'Gravação não suportada neste navegador';
  micBtn.style.opacity = '.25';
}

micBtn.addEventListener('click', () => isRecording ? stopRec() : startRec());

async function startRec() {
  try {
    const stream = await navigator.mediaDevices.getUserMedia({ audio: true, video: false });
    const mime = ['audio/webm;codecs=opus','audio/webm','audio/ogg;codecs=opus','']
      .find(m => !m || MediaRecorder.isTypeSupported(m)) ?? '';

    mediaRecorder = new MediaRecorder(stream, mime ? { mimeType: mime } : {});
    audioChunks   = [];
    recSeconds    = 0;

    mediaRecorder.ondataavailable = e => { if (e.data.size > 0) audioChunks.push(e.data); };
    mediaRecorder.onstop = async () => {
      stream.getTracks().forEach(t => t.stop());
      const ext  = mediaRecorder.mimeType.includes('ogg') ? 'ogg' : 'webm';
      const blob = new Blob(audioChunks, { type: mediaRecorder.mimeType || 'audio/webm' });
      await transcribe(blob, ext);
    };

    mediaRecorder.start(250);
    isRecording = true;

    micBtn.classList.add('btn-mic--rec');
    micBtn.title     = 'Parar gravação';
    micBtn.innerHTML = SVG_STOP;
    recBar.classList.add('on');

    timerInterval = setInterval(() => {
      recSeconds++;
      const m = Math.floor(recSeconds / 60);
      const s = String(recSeconds % 60).padStart(2, '0');
      recTimer.textContent = `${m}:${s}`;
    }, 1000);

  } catch (err) {
    const msg = err.name === 'NotAllowedError'
      ? 'Permissão de microfone negada. Habilite nas configurações do navegador.'
      : 'Erro ao acessar microfone: ' + err.message;
    addMsg(msg, 'error');
  }
}

function stopRec() {
  if (!mediaRecorder || !isRecording) return;
  clearInterval(timerInterval);
  isRecording = false;
  mediaRecorder.stop();

  micBtn.classList.remove('btn-mic--rec');
  micBtn.classList.add('btn-mic--proc');
  micBtn.innerHTML = SVG_SPIN;
  micBtn.disabled  = true;
  micBtn.title     = 'Transcrevendo...';
  recBar.classList.remove('on');
}

async function transcribe(blob, ext) {
  const procMsg = addMsg('🎙️ Processando áudio...', 'system');

  try {
    const fd = new FormData();
    fd.append('audio', blob, `recording.${ext}`);

    const res  = await fetch('/alterar/transcribe.php', { method: 'POST', body: fd });
    const data = await res.json();
    procMsg.remove();

    micBtn.classList.remove('btn-mic--proc');
    micBtn.disabled  = false;
    micBtn.title     = 'Gravar áudio';
    micBtn.innerHTML = SVG_MIC;

    if (data.ok && data.text) {
      // Envia direto para a IA sem exibir o texto no textarea
      await sendInstruction(data.text);
    } else {
      addMsg('Erro na transcrição: ' + (data.error || 'Tente novamente.'), 'error');
    }
  } catch (err) {
    procMsg.remove();
    micBtn.classList.remove('btn-mic--proc');
    micBtn.disabled  = false;
    micBtn.title     = 'Gravar áudio';
    micBtn.innerHTML = SVG_MIC;
    addMsg('Erro ao enviar áudio: ' + err.message, 'error');
  }
}
</script>
</body>
</html>
