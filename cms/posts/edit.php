<?php
require_once dirname(__DIR__) . '/boot.php';
auth_check();

$pdo    = db();
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_new = $id === 0;
$post   = null;
$errors = [];

if (!$is_new) {
    $stmt = $pdo->prepare('SELECT * FROM posts WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $post = $stmt->fetch();
    if (!$post) {
        flash_set('error', 'Post não encontrado.');
        header('Location: ' . CMS_URL . '/posts/');
        exit;
    }
}

$page_title = $is_new ? 'Novo Post' : 'Editar Post';
$active     = 'posts';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $title      = trim($_POST['title'] ?? '');
    $sl         = trim($_POST['slug'] ?? '');
    $category   = trim($_POST['category'] ?? '');
    $read_time  = trim($_POST['read_time'] ?? '');
    $status     = in_array($_POST['status'] ?? '', ['published','draft']) ? $_POST['status'] : 'draft';
    $image_url  = trim($_POST['image_url'] ?? '');
    $excerpt    = trim($_POST['excerpt'] ?? '');
    $content    = $_POST['content'] ?? '';
    $cat_slug   = slug($category);

    if ($title === '') $errors[] = 'O título é obrigatório.';
    if ($sl    === '') $sl = slug($title);

    // Check slug uniqueness
    if (empty($errors)) {
        $dup = $pdo->prepare('SELECT id FROM posts WHERE slug = ? AND id != ?');
        $dup->execute([$sl, $id]);
        if ($dup->fetch()) $errors[] = 'Esse slug já está em uso.';
    }

    if (empty($errors)) {
        if ($is_new) {
            $ins = $pdo->prepare(
                'INSERT INTO posts (title, slug, excerpt, content, image_url, category, category_slug, read_time, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $ins->execute([$title, $sl, $excerpt, $content, $image_url, $category, $cat_slug, $read_time, $status]);
            flash_set('success', 'Post criado com sucesso.');
        } else {
            $upd = $pdo->prepare(
                'UPDATE posts SET title=?, slug=?, excerpt=?, content=?, image_url=?, category=?, category_slug=?, read_time=?, status=?
                 WHERE id=?'
            );
            $upd->execute([$title, $sl, $excerpt, $content, $image_url, $category, $cat_slug, $read_time, $status, $id]);
            flash_set('success', 'Post atualizado com sucesso.');
        }
        header('Location: ' . CMS_URL . '/posts/');
        exit;
    }

    // Re-populate
    $post = compact('title','slug','category','read_time','status','image_url','excerpt','content');
    $post['slug'] = $sl;
}

require_once dirname(__DIR__) . '/includes/head.php';
?>

<div class="page-header">
  <div>
    <h1 class="page-header__title"><?= h($page_title) ?></h1>
    <p class="page-header__sub">
      <?= $is_new ? 'Criar um novo post para o blog' : 'Editar post existente' ?>
    </p>
  </div>
  <a href="<?= CMS_URL ?>/posts/" class="btn btn-secondary">← Voltar</a>
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
      <span class="adm-card__title">Informações do Post</span>
    </div>
    <div class="adm-card__body">
      <div class="form-grid">

        <div class="form-group form-group--full">
          <label for="post_title">Título *</label>
          <input type="text" id="post_title" name="title" required
                 value="<?= h($post['title'] ?? '') ?>"
                 placeholder="Título do post">
        </div>

        <div class="form-group">
          <label for="post_slug">Slug *</label>
          <input type="text" id="post_slug" name="slug"
                 value="<?= h($post['slug'] ?? '') ?>"
                 placeholder="titulo-do-post"
                 <?= (!$is_new && !empty($post['slug'])) ? 'data-locked="1"' : '' ?>>
          <span class="form-hint">URL amigável. Gerado automaticamente pelo título.</span>
        </div>

        <div class="form-group">
          <label for="category">Categoria</label>
          <input type="text" id="category" name="category"
                 value="<?= h($post['category'] ?? '') ?>"
                 placeholder="ex: Performance, IA & Tecnologia">
        </div>

        <div class="form-group">
          <label for="read_time">Tempo de Leitura</label>
          <input type="text" id="read_time" name="read_time"
                 value="<?= h($post['read_time'] ?? '') ?>"
                 placeholder="5 min">
        </div>

        <div class="form-group">
          <label for="status">Status</label>
          <select id="status" name="status">
            <option value="draft" <?= ($post['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Rascunho</option>
            <option value="published" <?= ($post['status'] ?? '') === 'published' ? 'selected' : '' ?>>Publicado</option>
          </select>
        </div>

        <div class="form-group form-group--full">
          <label for="image_url">URL da Imagem</label>
          <input type="url" id="image_url" name="image_url"
                 value="<?= h($post['image_url'] ?? '') ?>"
                 placeholder="https://..."
                 data-preview="img_preview">
          <?php if (!empty($post['image_url'])): ?>
          <div class="img-preview-wrap">
            <img id="img_preview" src="<?= h($post['image_url']) ?>" alt="Preview" class="img-preview"
                 style="margin-top:8px">
          </div>
          <?php else: ?>
          <div class="img-preview-wrap">
            <img id="img_preview" src="" alt="Preview" class="img-preview" style="display:none;margin-top:8px">
          </div>
          <?php endif; ?>
        </div>

        <div class="form-group form-group--full">
          <label for="excerpt">Resumo</label>
          <textarea id="excerpt" name="excerpt" style="min-height:100px"
                    placeholder="Breve descrição do post (exibida na listagem do blog)"><?= h($post['excerpt'] ?? '') ?></textarea>
        </div>

        <div class="form-group form-group--full">
          <label for="content">Conteúdo</label>
          <textarea id="content" name="content" style="min-height:400px"
                    placeholder="Conteúdo completo do post (HTML permitido)"><?= h($post['content'] ?? '') ?></textarea>
        </div>

      </div>
    </div>
  </div>

  <div class="form-actions">
    <button type="submit" class="btn btn-primary">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
      <?= $is_new ? 'Criar Post' : 'Salvar Alterações' ?>
    </button>
    <a href="<?= CMS_URL ?>/posts/" class="btn btn-secondary">Cancelar</a>
  </div>
</form>

<?php require_once dirname(__DIR__) . '/includes/foot.php'; ?>
