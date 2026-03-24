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
<?php endif; ?>

<?php require_once dirname(__DIR__) . '/includes/foot.php'; ?>
