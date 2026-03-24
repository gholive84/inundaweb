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
$user   = null;
$errors = [];

if (!$is_new) {
    $stmt = $pdo->prepare('SELECT * FROM cms_users WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    if (!$user) {
        flash_set('error', 'Usuário não encontrado.');
        header('Location: ' . CMS_URL . '/configuracoes/usuarios/');
        exit;
    }
}

$page_title = $is_new ? 'Novo Usuário' : 'Editar Usuário';
$active     = 'config_usuarios';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $nome   = trim($_POST['nome']  ?? '');
    $email  = trim($_POST['email'] ?? '');
    $role   = in_array($_POST['role'] ?? '', ['admin','editor']) ? $_POST['role'] : 'editor';
    $active_flag = isset($_POST['active']) ? 1 : 0;
    $senha  = $_POST['senha']  ?? '';
    $senha2 = $_POST['senha2'] ?? '';

    if ($nome === '') $errors[] = 'Nome obrigatório.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'E-mail inválido.';
    if ($is_new && strlen($senha) < 8) $errors[] = 'Senha deve ter pelo menos 8 caracteres.';
    if ($senha !== '' && $senha !== $senha2) $errors[] = 'Senhas não conferem.';

    // Check duplicate email
    if (empty($errors)) {
        $dup = $pdo->prepare('SELECT id FROM cms_users WHERE email = ? AND id != ?');
        $dup->execute([$email, $id]);
        if ($dup->fetch()) $errors[] = 'Este e-mail já está em uso.';
    }

    if (empty($errors)) {
        if ($is_new) {
            $hash = password_hash($senha, PASSWORD_BCRYPT, ['cost' => 12]);
            $ins  = $pdo->prepare(
                'INSERT INTO cms_users (nome, email, password_hash, role, active) VALUES (?, ?, ?, ?, ?)'
            );
            $ins->execute([$nome, $email, $hash, $role, $active_flag]);
            flash_set('success', 'Usuário criado com sucesso.');
        } else {
            if ($senha !== '') {
                $hash = password_hash($senha, PASSWORD_BCRYPT, ['cost' => 12]);
                $upd  = $pdo->prepare(
                    'UPDATE cms_users SET nome=?, email=?, password_hash=?, role=?, active=? WHERE id=?'
                );
                $upd->execute([$nome, $email, $hash, $role, $active_flag, $id]);
            } else {
                $upd = $pdo->prepare(
                    'UPDATE cms_users SET nome=?, email=?, role=?, active=? WHERE id=?'
                );
                $upd->execute([$nome, $email, $role, $active_flag, $id]);
            }
            flash_set('success', 'Usuário atualizado com sucesso.');
        }
        header('Location: ' . CMS_URL . '/configuracoes/usuarios/');
        exit;
    }

    // Re-populate
    $user = compact('nome','email','role');
    $user['active'] = $active_flag;
}

require_once dirname(dirname(__DIR__)) . '/includes/head.php';
?>

<div class="page-header">
  <div>
    <h1 class="page-header__title"><?= h($page_title) ?></h1>
    <p class="page-header__sub"><?= $is_new ? 'Adicionar novo acesso ao CMS' : 'Editar dados do usuário' ?></p>
  </div>
  <a href="<?= CMS_URL ?>/configuracoes/usuarios/" class="btn btn-secondary">← Voltar</a>
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
      <span class="adm-card__title">Dados do Usuário</span>
    </div>
    <div class="adm-card__body">
      <div class="form-grid">

        <div class="form-group form-group--full">
          <label>Nome completo *</label>
          <input type="text" name="nome" required
                 value="<?= h($user['nome'] ?? '') ?>"
                 placeholder="Nome completo">
        </div>

        <div class="form-group">
          <label>E-mail *</label>
          <input type="email" name="email" required
                 value="<?= h($user['email'] ?? '') ?>"
                 placeholder="email@exemplo.com">
        </div>

        <div class="form-group">
          <label>Perfil</label>
          <select name="role">
            <option value="editor" <?= ($user['role'] ?? 'editor') === 'editor' ? 'selected' : '' ?>>Editor</option>
            <option value="admin"  <?= ($user['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Administrador</option>
          </select>
        </div>

        <div class="form-group">
          <label><?= $is_new ? 'Senha *' : 'Nova Senha' ?></label>
          <input type="password" name="senha" placeholder="••••••••"
                 <?= $is_new ? 'required' : '' ?>>
          <?php if (!$is_new): ?>
          <span class="form-hint">Deixe em branco para manter a senha atual.</span>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label>Confirmar Senha</label>
          <input type="password" name="senha2" placeholder="••••••••">
        </div>

        <div class="form-group form-group--full">
          <label class="check-label">
            <input type="checkbox" name="active" value="1"
                   <?= ($user['active'] ?? 1) ? 'checked' : '' ?>>
            Usuário ativo (pode fazer login)
          </label>
        </div>

      </div>
    </div>
  </div>

  <div class="form-actions">
    <button type="submit" class="btn btn-primary">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
      <?= $is_new ? 'Criar Usuário' : 'Salvar Alterações' ?>
    </button>
    <a href="<?= CMS_URL ?>/configuracoes/usuarios/" class="btn btn-secondary">Cancelar</a>
  </div>
</form>

<?php require_once dirname(dirname(__DIR__)) . '/includes/foot.php'; ?>
