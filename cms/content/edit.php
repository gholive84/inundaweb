<?php
require_once dirname(__DIR__) . '/boot.php';
auth_check();

$pdo       = db();
$type_slug = $_GET['type'] ?? '';
$id        = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_new    = $id === 0;

if (!$type_slug) {
    header('Location: ' . CMS_URL . '/');
    exit;
}

$type_stmt = $pdo->prepare('SELECT * FROM content_types WHERE slug = ? LIMIT 1');
$type_stmt->execute([$type_slug]);
$type = $type_stmt->fetch();

if (!$type) {
    flash_set('error', 'Tipo de conteúdo não encontrado.');
    header('Location: ' . CMS_URL . '/');
    exit;
}

$raw_schema    = $type['fields_schema'] ?? '[]';
if (is_array($raw_schema)) $raw_schema = json_encode($raw_schema); // MySQL JSON col returns array in some drivers
$fields_schema = json_decode($raw_schema, true) ?: [];
$item          = null;
$fields_data   = [];
$errors        = [];

if (!$is_new) {
    $stmt = $pdo->prepare('SELECT * FROM content_items WHERE id = ? AND type_id = ? LIMIT 1');
    $stmt->execute([$id, $type['id']]);
    $item = $stmt->fetch();
    if (!$item) {
        flash_set('error', 'Item não encontrado.');
        header('Location: ' . CMS_URL . '/content/?type=' . urlencode($type_slug));
        exit;
    }
    $fields_data = json_decode($item['fields_data'], true) ?: [];
}

$page_title = $is_new ? 'Novo Item — ' . h($type['name']) : 'Editar Item';
$active     = 'content_' . h($type_slug);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $title  = trim($_POST['title'] ?? '');
    $sl     = trim($_POST['slug'] ?? '');
    $status = in_array($_POST['status'] ?? '', ['published','draft']) ? $_POST['status'] : 'draft';

    if ($title === '') $errors[] = 'O título é obrigatório.';
    if ($sl === '') $sl = slug($title);

    // Collect custom fields
    $fd = [];
    foreach ($fields_schema as $field) {
        $key = $field['key'] ?? '';
        if (!$key) continue;
        if ($field['type'] === 'checkbox') {
            $fd[$key] = isset($_POST['field_' . $key]) ? '1' : '0';
        } else {
            $fd[$key] = trim($_POST['field_' . $key] ?? '');
        }
    }

    // Check slug uniqueness within type
    if (empty($errors)) {
        $dup = $pdo->prepare('SELECT id FROM content_items WHERE type_id = ? AND slug = ? AND id != ?');
        $dup->execute([$type['id'], $sl, $id]);
        if ($dup->fetch()) $errors[] = 'Esse slug já está em uso neste tipo.';
    }

    if (empty($errors)) {
        if ($is_new) {
            $ins = $pdo->prepare(
                'INSERT INTO content_items (type_id, title, slug, status, fields_data) VALUES (?, ?, ?, ?, ?)'
            );
            $ins->execute([$type['id'], $title, $sl, $status, json_encode($fd)]);
            flash_set('success', 'Item criado com sucesso.');
        } else {
            $upd = $pdo->prepare(
                'UPDATE content_items SET title=?, slug=?, status=?, fields_data=? WHERE id=?'
            );
            $upd->execute([$title, $sl, $status, json_encode($fd), $id]);
            flash_set('success', 'Item atualizado com sucesso.');
        }
        header('Location: ' . CMS_URL . '/content/?type=' . urlencode($type_slug));
        exit;
    }

    // Re-populate
    $item = ['title' => $title, 'slug' => $sl, 'status' => $status];
    $fields_data = $fd;
}

require_once dirname(__DIR__) . '/includes/head.php';
?>

<div class="page-header">
  <div>
    <h1 class="page-header__title"><?= h($page_title) ?></h1>
    <p class="page-header__sub">Tipo: <?= h($type['name']) ?></p>
  </div>
  <a href="<?= CMS_URL ?>/content/?type=<?= h($type_slug) ?>" class="btn btn-secondary">← Voltar</a>
</div>

<?php if (!empty($errors)): ?>
<div class="flash flash--error">
  <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
  <?= h(implode(' ', $errors)) ?>
</div>
<?php endif; ?>

<form method="POST" novalidate>
  <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

  <div class="adm-card">
    <div class="adm-card__header">
      <span class="adm-card__title">Informações Básicas</span>
    </div>
    <div class="adm-card__body">
      <div class="form-grid">
        <div class="form-group form-group--full">
          <label for="item_title">Título *</label>
          <input type="text" id="item_title" name="title" required
                 value="<?= h($item['title'] ?? '') ?>"
                 placeholder="Título do item">
        </div>
        <div class="form-group">
          <label for="item_slug">Slug</label>
          <input type="text" id="item_slug" name="slug"
                 value="<?= h($item['slug'] ?? '') ?>"
                 placeholder="slug-do-item"
                 <?= (!$is_new && !empty($item['slug'])) ? 'data-locked="1"' : '' ?>>
          <span class="form-hint">Gerado automaticamente pelo título.</span>
        </div>
        <div class="form-group">
          <label for="status">Status</label>
          <select id="status" name="status">
            <option value="draft" <?= ($item['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Rascunho</option>
            <option value="published" <?= ($item['status'] ?? '') === 'published' ? 'selected' : '' ?>>Publicado</option>
          </select>
        </div>
      </div>
    </div>
  </div>

  <?php if (!empty($fields_schema)): ?>
  <div class="adm-card">
    <div class="adm-card__header">
      <span class="adm-card__title">Campos Personalizados</span>
    </div>
    <div class="adm-card__body">
      <div class="form-grid">
        <?php foreach ($fields_schema as $field):
          $key  = $field['key'] ?? '';
          $lbl  = $field['label'] ?? $key;
          $ftype = $field['type'] ?? 'text';
          $val  = $fields_data[$key] ?? '';
          $full = in_array($ftype, ['textarea','image']) ? 'form-group--full' : '';
        ?>
        <div class="form-group <?= $full ?>">
          <label for="field_<?= h($key) ?>"><?= h($lbl) ?></label>
          <?php if ($ftype === 'textarea'): ?>
            <textarea id="field_<?= h($key) ?>" name="field_<?= h($key) ?>"><?= h($val) ?></textarea>
          <?php elseif ($ftype === 'checkbox'): ?>
            <label class="check-label">
              <input type="checkbox" id="field_<?= h($key) ?>" name="field_<?= h($key) ?>"
                     <?= $val === '1' ? 'checked' : '' ?>>
              <?= h($lbl) ?>
            </label>
          <?php elseif ($ftype === 'image'): ?>
            <div style="display:flex;gap:8px;align-items:center">
              <input type="text" id="field_<?= h($key) ?>" name="field_<?= h($key) ?>"
                     value="<?= h($val) ?>"
                     placeholder="https://... ou use o botão Upload"
                     data-preview="prev_<?= h($key) ?>" style="flex:1">
              <button type="button" class="btn btn-secondary btn-sm"
                      data-upload-for="field_<?= h($key) ?>"
                      data-upload-preview="prev_<?= h($key) ?>">
                ↑ Upload
              </button>
            </div>
            <img id="prev_<?= h($key) ?>" src="<?= h($val) ?>" alt=""
                 class="img-preview" style="<?= $val ? '' : 'display:none;' ?>margin-top:8px">
          <?php elseif ($ftype === 'file'): ?>
            <div style="display:flex;gap:8px;align-items:center">
              <input type="text" id="field_<?= h($key) ?>" name="field_<?= h($key) ?>"
                     value="<?= h($val) ?>"
                     placeholder="URL do arquivo ou use Upload"
                     style="flex:1">
              <button type="button" class="btn btn-secondary btn-sm"
                      data-upload-for="field_<?= h($key) ?>">
                ↑ Upload
              </button>
            </div>
            <?php if ($val): ?>
            <a href="<?= h($val) ?>" target="_blank" style="font-size:.75rem;color:var(--a-primary);margin-top:4px;display:inline-block">
              📎 Ver arquivo atual
            </a>
            <?php endif; ?>
          <?php elseif ($ftype === 'number'): ?>
            <input type="number" id="field_<?= h($key) ?>" name="field_<?= h($key) ?>"
                   value="<?= h($val) ?>">
          <?php elseif ($ftype === 'url'): ?>
            <input type="url" id="field_<?= h($key) ?>" name="field_<?= h($key) ?>"
                   value="<?= h($val) ?>" placeholder="https://...">
          <?php elseif ($ftype === 'date'): ?>
            <input type="date" id="field_<?= h($key) ?>" name="field_<?= h($key) ?>"
                   value="<?= h($val) ?>">
          <?php elseif ($ftype === 'select'): ?>
            <input type="text" id="field_<?= h($key) ?>" name="field_<?= h($key) ?>"
                   value="<?= h($val) ?>"
                   placeholder="Valor da opção">
            <span class="form-hint">Para tipo Select: insira o valor diretamente.</span>
          <?php else: ?>
            <input type="text" id="field_<?= h($key) ?>" name="field_<?= h($key) ?>"
                   value="<?= h($val) ?>">
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <div class="form-actions">
    <button type="submit" class="btn btn-primary">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
      <?= $is_new ? 'Criar Item' : 'Salvar Alterações' ?>
    </button>
    <a href="<?= CMS_URL ?>/content/?type=<?= h($type_slug) ?>" class="btn btn-secondary">Cancelar</a>
  </div>
</form>

<?php require_once dirname(__DIR__) . '/includes/foot.php'; ?>
