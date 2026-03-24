<?php
require_once dirname(dirname(__DIR__)) . '/boot.php';
auth_check();
if (!auth_is_admin()) {
    flash_set('error', 'Acesso restrito a administradores.');
    header('Location: ' . CMS_URL . '/');
    exit;
}

$pdo    = db();
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_new = $id === 0;
$type   = null;
$errors = [];
$fields_schema = [];

if (!$is_new) {
    $stmt = $pdo->prepare('SELECT * FROM content_types WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $type = $stmt->fetch();
    if (!$type) {
        flash_set('error', 'Tipo não encontrado.');
        header('Location: ' . CMS_URL . '/configuracoes/tipos/');
        exit;
    }
    $raw_schema    = $type['fields_schema'] ?? '[]';
    if (is_array($raw_schema)) $raw_schema = json_encode($raw_schema);
    $fields_schema = json_decode($raw_schema, true) ?: [];
}

$page_title = $is_new ? 'Novo Tipo de Conteúdo' : 'Editar Tipo';
$active     = 'config_tipos';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $name = trim($_POST['name'] ?? '');
    $sl   = trim($_POST['slug'] ?? '');
    if ($name === '') $errors[] = 'Nome obrigatório.';
    if ($sl === '') $sl = slug($name);

    // Build fields schema from JSON hidden input
    $fields = [];
    $fields_json_raw = trim($_POST['fields_json'] ?? '');
    if ($fields_json_raw !== '') {
        $decoded = json_decode($fields_json_raw, true);
        if (is_array($decoded)) {
            foreach ($decoded as $f) {
                $lbl = trim($f['label'] ?? '');
                $key = trim($f['key'] ?? '');
                if ($lbl === '' || $key === '') continue;
                $fields[] = ['label' => $lbl, 'key' => $key, 'type' => $f['type'] ?? 'text'];
            }
        }
    }
    // Legacy fallback (old POST array format — only if JSON not provided)
    if ($fields_json_raw === '') {
        $labels = $_POST['fields']['label'] ?? [];
        $keys   = $_POST['fields']['key']   ?? [];
        $ftypes = $_POST['fields']['type']  ?? [];
        foreach ($labels as $i => $lbl) {
            $lbl = trim($lbl);
            $key = trim($keys[$i] ?? '');
            if ($lbl === '' || $key === '') continue;
            $fields[] = ['label' => $lbl, 'key' => $key, 'type' => $ftypes[$i] ?? 'text'];
        }
    }

    if (empty($errors)) {
        $schema = json_encode($fields, JSON_UNESCAPED_UNICODE);
        if ($is_new) {
            // Check slug uniqueness
            $dup = $pdo->prepare('SELECT id FROM content_types WHERE slug = ?');
            $dup->execute([$sl]);
            if ($dup->fetch()) {
                $errors[] = 'Esse slug já existe.';
            } else {
                $ins = $pdo->prepare('INSERT INTO content_types (name, slug, fields_schema) VALUES (?, ?, ?)');
                $ins->execute([$name, $sl, $schema]);
                flash_set('success', 'Tipo criado com sucesso.');
                header('Location: ' . CMS_URL . '/configuracoes/tipos/');
                exit;
            }
        } else {
            $upd = $pdo->prepare('UPDATE content_types SET name=?, slug=?, fields_schema=? WHERE id=?');
            $upd->execute([$name, $sl, $schema, $id]);
            flash_set('success', 'Tipo atualizado com sucesso.');
            header('Location: ' . CMS_URL . '/configuracoes/tipos/');
            exit;
        }
    }

    // Re-populate
    $type = ['name' => $name, 'slug' => $sl];
    $fields_schema = $fields;
}

require_once dirname(dirname(__DIR__)) . '/includes/head.php';
?>

<div class="page-header">
  <div>
    <h1 class="page-header__title"><?= h($page_title) ?></h1>
    <p class="page-header__sub">Defina nome e campos personalizados do tipo</p>
  </div>
  <a href="<?= CMS_URL ?>/configuracoes/tipos/" class="btn btn-secondary">← Voltar</a>
</div>

<?php if (!empty($errors)): ?>
<div class="flash flash--error">
  <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
  <?= h(implode(' ', $errors)) ?>
</div>
<?php endif; ?>

<form method="POST" novalidate id="typeForm">
  <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
  <input type="hidden" name="fields_json" id="fieldsJsonInput" value="<?= h(json_encode($fields_schema, JSON_UNESCAPED_UNICODE)) ?>">

  <div class="adm-card">
    <div class="adm-card__header">
      <span class="adm-card__title">Identificação</span>
    </div>
    <div class="adm-card__body">
      <div class="form-grid">
        <div class="form-group">
          <label for="type_name">Nome *</label>
          <input type="text" id="type_name" name="name" required
                 value="<?= h($type['name'] ?? '') ?>"
                 placeholder="ex: Depoimentos">
        </div>
        <div class="form-group">
          <label for="type_slug">Slug *</label>
          <input type="text" id="type_slug" name="slug"
                 value="<?= h($type['slug'] ?? '') ?>"
                 placeholder="depoimentos"
                 <?= (!$is_new && !empty($type['slug'])) ? 'data-locked="1"' : '' ?>>
          <span class="form-hint">Gerado automaticamente. Usado na URL.</span>
        </div>
      </div>
    </div>
  </div>

  <div class="adm-card">
    <div class="adm-card__header">
      <span class="adm-card__title">Campos Personalizados</span>
    </div>
    <div class="adm-card__body">

      <div id="fieldsBuilder" class="fields-builder">
        <?php foreach ($fields_schema as $i => $field):
          $ftypes_opts = ['text'=>'Texto','textarea'=>'Área de texto','number'=>'Número','url'=>'URL','image'=>'Imagem (upload)','file'=>'Arquivo (upload)','date'=>'Data (calendário)','select'=>'Select','checkbox'=>'Checkbox'];
        ?>
        <div class="field-row">
          <div class="form-group" style="flex:1">
            <label>Label</label>
            <input type="text" class="fb-label" value="<?= h($field['label'] ?? '') ?>" placeholder="ex: Autor" required>
          </div>
          <div class="form-group" style="flex:1">
            <label>Chave (key)</label>
            <input type="text" class="fb-key" value="<?= h($field['key'] ?? '') ?>" placeholder="ex: autor" required data-locked="1">
          </div>
          <div class="form-group" style="width:150px">
            <label>Tipo</label>
            <select class="fb-type">
              <?php foreach ($ftypes_opts as $val => $lbl): ?>
              <option value="<?= $val ?>" <?= ($field['type'] ?? 'text') === $val ? 'selected' : '' ?>><?= $lbl ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="button" class="btn btn-danger btn-sm btn-remove-field" style="margin-bottom:1px" title="Remover">✕</button>
        </div>
        <?php endforeach; ?>
      </div>

      <button type="button" id="addField" class="btn-add-field">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Adicionar Campo
      </button>

    </div>
  </div>

  <div class="form-actions">
    <button type="submit" class="btn btn-primary">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
      <?= $is_new ? 'Criar Tipo' : 'Salvar Alterações' ?>
    </button>
    <a href="<?= CMS_URL ?>/configuracoes/tipos/" class="btn btn-secondary">Cancelar</a>
  </div>
</form>

<?php require_once dirname(dirname(__DIR__)) . '/includes/foot.php'; ?>
