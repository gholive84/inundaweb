<?php
require_once dirname(__DIR__) . '/boot.php';
auth_check();
if (!auth_is_admin()) {
    flash_set('error', 'Acesso restrito a administradores.');
    header('Location: ' . CMS_URL . '/');
    exit;
}

$page_title = 'Configurações';
$active     = 'config_smtp';
$pdo        = db();
$errors     = [];
$tab        = $_GET['tab'] ?? 'smtp';
$valid_tabs = ['smtp', 'header', 'ia', 'libs'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $saved_tab = $_POST['tab'] ?? 'smtp';

    if ($saved_tab === 'smtp') {
        $keys = ['smtp_host','smtp_port','smtp_user','smtp_pass','smtp_from_name','smtp_from_email'];
        foreach ($keys as $k) {
            setting_save($k, trim($_POST[$k] ?? ''));
        }
        flash_set('success', 'Configurações SMTP salvas.');
        header('Location: ' . CMS_URL . '/configuracoes/?tab=smtp');
        exit;
    }

    if ($saved_tab === 'header') {
        setting_save('header_codes', $_POST['header_codes'] ?? '');
        flash_set('success', 'Códigos de cabeçalho salvos.');
        header('Location: ' . CMS_URL . '/configuracoes/?tab=header');
        exit;
    }

    if ($saved_tab === 'libs') {
        $libs_raw = $_POST['libs_json'] ?? '[]';
        $libs = json_decode($libs_raw, true);
        if (!is_array($libs)) $libs = [];
        // sanitize
        $libs = array_values(array_filter(array_map(function($l) {
            return [
                'name' => trim($l['name'] ?? ''),
                'desc' => trim($l['desc'] ?? ''),
                'css'  => trim($l['css']  ?? ''),
                'js'   => trim($l['js']   ?? ''),
            ];
        }, $libs), fn($l) => $l['name'] !== ''));
        setting_save('site_libraries', json_encode($libs));
        flash_set('success', 'Bibliotecas salvas.');
        header('Location: ' . CMS_URL . '/configuracoes/?tab=libs');
        exit;
    }

    if ($saved_tab === 'ia') {
        $key = trim($_POST['openai_key'] ?? '');
        if ($key !== '') setting_save('openai_key', $key);
        setting_save('openai_model', trim($_POST['openai_model'] ?? 'gpt-4o'));
        setting_save('openai_context', trim($_POST['openai_context'] ?? ''));
        flash_set('success', 'Configurações de IA salvas.');
        header('Location: ' . CMS_URL . '/configuracoes/?tab=ia');
        exit;
    }
}

require_once dirname(__DIR__) . '/includes/head.php';
?>

<div class="page-header">
  <div>
    <h1 class="page-header__title">Configurações</h1>
    <p class="page-header__sub">SMTP, analytics e códigos globais</p>
  </div>
</div>

<div class="tabs">
  <a href="?tab=smtp"   class="tab <?= $tab === 'smtp'   ? 'active' : '' ?>">E-mail / SMTP</a>
  <a href="?tab=header" class="tab <?= $tab === 'header' ? 'active' : '' ?>">Códigos do Cabeçalho</a>
  <a href="?tab=libs"   class="tab <?= $tab === 'libs'   ? 'active' : '' ?>">Bibliotecas JS</a>
  <a href="?tab=ia"     class="tab <?= $tab === 'ia'     ? 'active' : '' ?>">Inteligência Artificial</a>
</div>

<?php if ($tab === 'smtp'): ?>
<!-- ── SMTP ── -->
<form method="POST" novalidate>
  <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
  <input type="hidden" name="tab" value="smtp">

  <div class="adm-card">
    <div class="adm-card__header">
      <span class="adm-card__title">Configuração SMTP</span>
      <span class="adm-card__note" style="font-size:.8rem;color:var(--a-muted)">Usado para envio de notificações de leads</span>
    </div>
    <div class="adm-card__body">
      <div class="form-grid">

        <div class="form-group">
          <label>Host SMTP</label>
          <input type="text" name="smtp_host" value="<?= h(setting('smtp_host')) ?>"
                 placeholder="smtp.gmail.com">
        </div>

        <div class="form-group">
          <label>Porta</label>
          <input type="number" name="smtp_port" value="<?= h(setting('smtp_port','587')) ?>"
                 placeholder="587">
          <span class="form-hint">587 (TLS) ou 465 (SSL)</span>
        </div>

        <div class="form-group">
          <label>Usuário / E-mail SMTP</label>
          <input type="email" name="smtp_user" value="<?= h(setting('smtp_user')) ?>"
                 placeholder="seu@email.com">
        </div>

        <div class="form-group">
          <label>Senha SMTP</label>
          <input type="password" name="smtp_pass"
                 value="<?= h(setting('smtp_pass')) ?>"
                 placeholder="••••••••">
          <span class="form-hint">Deixe em branco para manter a senha atual</span>
        </div>

        <div class="form-group">
          <label>Nome do Remetente</label>
          <input type="text" name="smtp_from_name" value="<?= h(setting('smtp_from_name','Inunda IA')) ?>"
                 placeholder="Inunda IA">
        </div>

        <div class="form-group">
          <label>E-mail do Remetente</label>
          <input type="email" name="smtp_from_email" value="<?= h(setting('smtp_from_email')) ?>"
                 placeholder="contato@inundaia.com.br">
        </div>

      </div>
    </div>
  </div>

  <div class="form-actions">
    <button type="submit" class="btn btn-primary">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
      Salvar SMTP
    </button>
  </div>
</form>

<?php elseif ($tab === 'header'): ?>
<!-- ── Header Codes ── -->
<form method="POST" novalidate>
  <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
  <input type="hidden" name="tab" value="header">

  <div class="adm-card">
    <div class="adm-card__header">
      <span class="adm-card__title">Códigos do Cabeçalho</span>
    </div>
    <div class="adm-card__body">

      <div class="flash flash--info" style="margin-bottom:20px">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
        Estes códigos são inseridos dentro do <code style="font-size:.8rem;background:rgba(255,255,255,0.08);padding:1px 5px;border-radius:3px">&lt;head&gt;</code> de todas as páginas do site. Use para Google Analytics, GTM, pixels, etc.
      </div>

      <div class="form-group">
        <label>Códigos HTML / Scripts</label>
        <textarea name="header_codes" style="min-height:300px;font-family:monospace;font-size:.875rem"
                  placeholder="<!-- Google Analytics -->&#10;<script async src=&quot;...&quot;></script>&#10;&#10;<!-- Google Tag Manager -->&#10;..."><?= h(setting('header_codes')) ?></textarea>
        <span class="form-hint">Cole o código completo incluindo as tags &lt;script&gt; ou &lt;meta&gt;.</span>
      </div>

    </div>
  </div>

  <div class="form-actions">
    <button type="submit" class="btn btn-primary">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
      Salvar Códigos
    </button>
  </div>
</form>
<?php elseif ($tab === 'libs'): ?>
<!-- ── Bibliotecas JS ── -->
<?php
$libs_json = setting('site_libraries', '[]');
$libs = json_decode($libs_json, true) ?: [];
?>
<form method="POST" novalidate id="libsForm">
  <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
  <input type="hidden" name="tab" value="libs">
  <input type="hidden" name="libs_json" id="libsJson" value="<?= h(json_encode($libs)) ?>">

  <div class="adm-card">
    <div class="adm-card__header">
      <span class="adm-card__title">Bibliotecas JS globais</span>
    </div>
    <div class="adm-card__body">

      <div class="flash flash--info" style="margin-bottom:20px;font-size:.8125rem">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
        As libs adicionadas aqui são injetadas automaticamente em <strong>todas as páginas do site</strong> (CSS no &lt;head&gt;, JS antes do &lt;/body&gt;) <strong>e</strong> no contexto da IA.
      </div>

      <div id="libsList" style="display:flex;flex-direction:column;gap:12px;margin-bottom:20px"></div>

      <div style="border:1px dashed var(--a-border);border-radius:10px;padding:16px">
        <p style="font-size:.8125rem;font-weight:600;color:rgba(255,255,255,.6);margin-bottom:12px;text-transform:uppercase;letter-spacing:.06em">Adicionar biblioteca</p>
        <div class="form-grid">
          <div class="form-group">
            <label>Nome <span style="color:#ef4444">*</span></label>
            <input type="text" id="newName" placeholder="Ex: Swiper.js v11">
          </div>
          <div class="form-group">
            <label>Descrição</label>
            <input type="text" id="newDesc" placeholder="Ex: Carrosséis e sliders">
          </div>
          <div class="form-group">
            <label>URL do CSS (opcional)</label>
            <input type="url" id="newCss" placeholder="https://cdn.../swiper-bundle.min.css">
          </div>
          <div class="form-group">
            <label>URL do JS</label>
            <input type="url" id="newJs" placeholder="https://cdn.../swiper-bundle.min.js">
          </div>
        </div>
        <button type="button" class="btn btn-secondary btn-sm" onclick="addLib()">
          <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Adicionar
        </button>
      </div>
    </div>
  </div>

  <div class="form-actions">
    <button type="submit" class="btn btn-primary">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
      Salvar Bibliotecas
    </button>
  </div>
</form>

<style>
.lib-card {
  background: var(--a-card-2);
  border: 1px solid var(--a-border);
  border-radius: 10px;
  padding: 14px 16px;
  display: flex;
  align-items: center;
  gap: 12px;
}
.lib-card__info { flex: 1; min-width: 0; }
.lib-card__name { font-size: .875rem; font-weight: 600; color: #fff; }
.lib-card__desc { font-size: .75rem; color: var(--a-muted); margin-top: 2px; }
.lib-card__urls { font-size: .7rem; color: var(--a-primary); margin-top: 4px; word-break: break-all; }
</style>

<script>
let libs = <?= $libs_json ?: '[]' ?>;

function renderLibs() {
    const el = document.getElementById('libsList');
    if (!libs.length) {
        el.innerHTML = '<p style="font-size:.8rem;color:var(--a-muted)">Nenhuma biblioteca adicionada ainda.</p>';
        return;
    }
    el.innerHTML = libs.map((l, i) => `
        <div class="lib-card">
            <div class="lib-card__info">
                <div class="lib-card__name">${esc(l.name)}</div>
                ${l.desc ? `<div class="lib-card__desc">${esc(l.desc)}</div>` : ''}
                <div class="lib-card__urls">
                    ${l.css ? `CSS: ${esc(l.css)}<br>` : ''}
                    ${l.js  ? `JS: ${esc(l.js)}` : ''}
                </div>
            </div>
            <button type="button" class="btn btn-secondary btn-sm"
                    onclick="removeLib(${i})" title="Remover">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg>
            </button>
        </div>
    `).join('');
    document.getElementById('libsJson').value = JSON.stringify(libs);
}

function addLib() {
    const name = document.getElementById('newName').value.trim();
    if (!name) { alert('Informe o nome da biblioteca.'); return; }
    libs.push({
        name,
        desc: document.getElementById('newDesc').value.trim(),
        css:  document.getElementById('newCss').value.trim(),
        js:   document.getElementById('newJs').value.trim(),
    });
    document.getElementById('newName').value = '';
    document.getElementById('newDesc').value = '';
    document.getElementById('newCss').value  = '';
    document.getElementById('newJs').value   = '';
    renderLibs();
}

function removeLib(i) {
    libs.splice(i, 1);
    renderLibs();
}

function esc(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

renderLibs();
</script>

<?php elseif ($tab === 'ia'): ?>
<!-- ── IA ── -->
<form method="POST" novalidate>
  <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
  <input type="hidden" name="tab" value="ia">

  <div class="adm-card">
    <div class="adm-card__header">
      <span class="adm-card__title">OpenAI — Configuração</span>
    </div>
    <div class="adm-card__body">
      <div class="form-grid">

        <div class="form-group form-group--full">
          <label>Chave de API (OpenAI)</label>
          <input type="password" name="openai_key"
                 value="<?= h(setting('openai_key')) ?>"
                 placeholder="sk-proj-...">
          <span class="form-hint">Deixe em branco para manter a chave atual. Usada no editor de páginas com IA.</span>
        </div>

        <div class="form-group">
          <label>Modelo</label>
          <select name="openai_model">
            <?php foreach (['gpt-4o','gpt-4o-mini','gpt-4-turbo','gpt-3.5-turbo'] as $m): ?>
            <option value="<?= $m ?>" <?= setting('openai_model','gpt-4o') === $m ? 'selected' : '' ?>><?= $m ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group form-group--full">
          <?php
          $ctx_file = $_SERVER['DOCUMENT_ROOT'] . '/site/ai-context.txt';
          $ctx_exists = file_exists($ctx_file);
          ?>
          <label>Contexto adicional para a IA</label>
          <?php if ($ctx_exists): ?>
          <div class="flash flash--info" style="margin-bottom:10px;font-size:.8125rem">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
            O arquivo <code style="background:rgba(255,255,255,.08);padding:1px 5px;border-radius:3px">/site/ai-context.txt</code> é carregado automaticamente — ele já contém os tokens de design, classes CSS e bibliotecas disponíveis. Use o campo abaixo apenas para complementar.
          </div>
          <?php else: ?>
          <div class="flash flash--warning" style="margin-bottom:10px;font-size:.8125rem">
            Arquivo <code>/site/ai-context.txt</code> não encontrado. A IA usará apenas o contexto abaixo.
          </div>
          <?php endif; ?>
          <textarea name="openai_context" style="min-height:160px;font-family:monospace;font-size:.8125rem"
                    placeholder="Informações adicionais, overrides ou libs extras que a IA deve saber..."><?= h(setting('openai_context')) ?></textarea>
          <span class="form-hint">Injetado após o <code>ai-context.txt</code> no system prompt. Use para libs extras, padrões específicos de uma campanha, etc.</span>
        </div>

      </div>
    </div>
  </div>

  <div class="form-actions">
    <button type="submit" class="btn btn-primary">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
      Salvar IA
    </button>
  </div>
</form>
<?php endif; ?>

<?php require_once dirname(__DIR__) . '/includes/foot.php'; ?>
