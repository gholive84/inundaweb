<?php
require_once dirname(__DIR__) . '/boot.php';
auth_check();

$pdo = db();
$id  = (int)($_GET['id'] ?? 0);

if (!$id) {
    flash_set('error', 'Lead não especificado.');
    header('Location: ' . CMS_URL . '/leads/');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM leads WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$lead = $stmt->fetch();

if (!$lead) {
    flash_set('error', 'Lead não encontrado.');
    header('Location: ' . CMS_URL . '/leads/');
    exit;
}

$errors = [];

// ── Save lead data ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    csrf_verify();
    $nome     = trim($_POST['nome']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $mensagem = trim($_POST['mensagem'] ?? '');
    $origem   = trim($_POST['origem']   ?? '');
    $valid_s  = ['novo','contactado','fechado','descartado'];
    $status   = in_array($_POST['status'] ?? '', $valid_s) ? $_POST['status'] : $lead['status'];

    if ($nome === '') {
        $errors[] = 'Nome é obrigatório.';
    } else {
        $pdo->prepare(
            'UPDATE leads SET nome=?, email=?, telefone=?, mensagem=?, status=?, origem=? WHERE id=?'
        )->execute([$nome, $email, $telefone, $mensagem, $status, $origem, $id]);
        flash_set('success', 'Lead atualizado com sucesso.');
        header('Location: ' . CMS_URL . '/leads/edit.php?id=' . $id);
        exit;
    }
}

// ── Delete lead ─────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    csrf_verify();
    $pdo->prepare('DELETE FROM leads WHERE id = ?')->execute([$id]);
    flash_set('success', 'Lead excluído.');
    header('Location: ' . CMS_URL . '/leads/');
    exit;
}

// ── Add comment ─────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'comment') {
    csrf_verify();
    $content = trim($_POST['comment_content'] ?? '');
    if ($content !== '') {
        $user_id = auth_user()['id'] ?? null;
        // auto-create table if needed
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS lead_comments (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                lead_id INT UNSIGNED NOT NULL,
                content TEXT NOT NULL,
                created_by INT UNSIGNED NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX (lead_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (Exception $e) {}
        $pdo->prepare('INSERT INTO lead_comments (lead_id, content, created_by) VALUES (?, ?, ?)')->execute([$id, $content, $user_id]);
    }
    header('Location: ' . CMS_URL . '/leads/edit.php?id=' . $id . '#comments');
    exit;
}

// ── Delete comment ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_comment') {
    csrf_verify();
    $cid = (int)($_POST['comment_id'] ?? 0);
    if ($cid) {
        $pdo->prepare('DELETE FROM lead_comments WHERE id = ? AND lead_id = ?')->execute([$cid, $id]);
    }
    header('Location: ' . CMS_URL . '/leads/edit.php?id=' . $id . '#comments');
    exit;
}

// ── Load comments ───────────────────────────────────────────────────
$comments = [];
try {
    $cs = $pdo->prepare(
        'SELECT lc.*, u.nome as user_name
         FROM lead_comments lc
         LEFT JOIN cms_users u ON u.id = lc.created_by
         WHERE lc.lead_id = ?
         ORDER BY lc.created_at ASC'
    );
    $cs->execute([$id]);
    $comments = $cs->fetchAll();
} catch (Exception $e) {}

$statuses = [
    'novo'       => 'Novo',
    'contactado' => 'Contactado',
    'fechado'    => 'Fechado',
    'descartado' => 'Descartado',
];

$status_colors = [
    'novo'       => '#22d3ee',
    'contactado' => '#f59e0b',
    'fechado'    => '#22c55e',
    'descartado' => '#94a3b8',
];

$page_title = 'Lead — ' . h($lead['nome'] ?? 'Sem nome');
$active     = 'leads';
require_once dirname(__DIR__) . '/includes/head.php';
?>

<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a href="<?= CMS_URL ?>/leads/" class="btn btn-secondary btn-sm">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
      Voltar
    </a>
    <div>
      <h1 class="page-header__title"><?= h($lead['nome'] ?? 'Sem nome') ?></h1>
      <p class="page-header__sub">
        <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:<?= $status_colors[$lead['status']] ?? '#94a3b8' ?>;margin-right:5px"></span>
        <?= $statuses[$lead['status']] ?? $lead['status'] ?> · recebido em <?= format_date($lead['created_at']) ?>
      </p>
    </div>
  </div>
  <form method="POST" onsubmit="return confirm('Excluir este lead? Esta ação não pode ser desfeita.')">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <input type="hidden" name="action" value="delete">
    <button type="submit" class="btn btn-sm" style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#f87171">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
      Excluir Lead
    </button>
  </form>
</div>

<?php if ($errors): ?>
<div class="flash flash--error">
  <?php foreach ($errors as $e): ?><p><?= h($e) ?></p><?php endforeach; ?>
</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 380px;gap:20px;align-items:start">

  <!-- ── Lead Data ── -->
  <div>
    <form method="POST" novalidate>
      <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
      <input type="hidden" name="action" value="save">

      <div class="adm-card">
        <div class="adm-card__header">
          <span class="adm-card__title">Informações do Lead</span>
        </div>
        <div class="adm-card__body">
          <div class="form-grid">

            <div class="form-group form-group--full">
              <label>Nome <span style="color:#ef4444">*</span></label>
              <input type="text" name="nome" value="<?= h($lead['nome'] ?? '') ?>" required>
            </div>

            <div class="form-group">
              <label>E-mail</label>
              <input type="email" name="email" value="<?= h($lead['email'] ?? '') ?>">
            </div>

            <div class="form-group">
              <label>Telefone</label>
              <input type="text" name="telefone" value="<?= h($lead['telefone'] ?? '') ?>">
              <?php if (!empty($lead['telefone'])): ?>
              <span class="form-hint">
                <a href="https://wa.me/55<?= preg_replace('/\D/', '', $lead['telefone']) ?>" target="_blank" style="color:var(--a-primary)">
                  Abrir no WhatsApp →
                </a>
              </span>
              <?php endif; ?>
            </div>

            <div class="form-group">
              <label>Status</label>
              <select name="status">
                <?php foreach ($statuses as $sk => $sl): ?>
                <option value="<?= $sk ?>" <?= $lead['status'] === $sk ? 'selected' : '' ?>>
                  <?= $sl ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group">
              <label>Origem</label>
              <input type="text" name="origem" value="<?= h($lead['origem'] ?? '') ?>" placeholder="formulario-site">
            </div>

            <div class="form-group form-group--full">
              <label>Mensagem</label>
              <textarea name="mensagem" rows="5"><?= h($lead['mensagem'] ?? '') ?></textarea>
            </div>

          </div>
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          Salvar Alterações
        </button>
      </div>
    </form>
  </div>

  <!-- ── Comments ── -->
  <div id="comments">
    <div class="adm-card">
      <div class="adm-card__header">
        <span class="adm-card__title">
          Comentários
          <?php if (count($comments) > 0): ?>
          <span style="font-size:.75rem;font-weight:600;padding:2px 8px;border-radius:100px;background:rgba(34,211,238,.1);color:var(--a-primary);margin-left:6px"><?= count($comments) ?></span>
          <?php endif; ?>
        </span>
      </div>
      <div class="adm-card__body">

        <?php if (empty($comments)): ?>
        <p style="font-size:.875rem;color:var(--a-muted);margin-bottom:16px">Nenhum comentário ainda.</p>
        <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:20px">
          <?php foreach ($comments as $c): ?>
          <div style="background:rgba(255,255,255,.03);border:1px solid var(--a-border);border-radius:8px;padding:12px">
            <p style="font-size:.875rem;color:rgba(255,255,255,.85);line-height:1.55;white-space:pre-wrap;margin-bottom:6px"><?= h($c['content']) ?></p>
            <div style="display:flex;align-items:center;justify-content:space-between;gap:8px">
              <span style="font-size:.7rem;color:var(--a-muted)"><?= h($c['user_name'] ?? 'Admin') ?> · <?= format_date($c['created_at']) ?></span>
              <form method="POST" style="margin:0" onsubmit="return confirm('Excluir comentário?')">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="delete_comment">
                <input type="hidden" name="comment_id" value="<?= (int)$c['id'] ?>">
                <button type="submit" style="background:none;border:none;color:#64748b;cursor:pointer;font-size:.7rem;display:flex;align-items:center;gap:3px;padding:2px 4px;border-radius:4px;transition:color .15s" onmouseover="this.style.color='#f87171'" onmouseout="this.style.color='#64748b'">
                  <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/></svg>
                  Excluir
                </button>
              </form>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <form method="POST" novalidate>
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
          <input type="hidden" name="action" value="comment">
          <div class="form-group">
            <label>Novo comentário</label>
            <textarea name="comment_content" rows="3" placeholder="Adicione uma nota interna sobre este lead..."></textarea>
          </div>
          <button type="submit" class="btn btn-primary btn-sm">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            Comentar
          </button>
        </form>

      </div>
    </div>
  </div>

</div>

<style>
@media (max-width: 900px) {
  div[style*="grid-template-columns:1fr 380px"] {
    grid-template-columns: 1fr !important;
  }
}
</style>

<?php require_once dirname(__DIR__) . '/includes/foot.php'; ?>
