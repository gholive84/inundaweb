<?php
require_once __DIR__ . '/boot.php';

// Already logged in
if (!empty($_SESSION['cms_user_id'])) {
    header('Location: ' . CMS_URL . '/');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['senha'] ?? '';

    if (auth_login($email, $pass)) {
        header('Location: ' . CMS_URL . '/');
        exit;
    }

    $error = 'E-mail ou senha incorretos.';
}

$flash = flash_get();
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — Inunda IA CMS</title>
  <meta name="robots" content="noindex, nofollow">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= CMS_URL ?>/assets/css/admin.css">
</head>
<body>
<div class="login-wrap">
  <div class="login-card">
    <span class="login-logo">inunda ia</span>
    <p class="login-sub">Área administrativa</p>

    <?php if ($flash): ?>
    <div class="flash flash--<?= h($flash['type']) ?>"><?= h($flash['msg']) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="flash flash--error"><?= h($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= CMS_URL ?>/login.php" novalidate>
      <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

      <div class="form-group" style="margin-bottom:16px">
        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" required autofocus
               value="<?= h($_POST['email'] ?? '') ?>"
               placeholder="seu@email.com">
      </div>

      <div class="form-group" style="margin-bottom:24px">
        <label for="senha">Senha</label>
        <input type="password" id="senha" name="senha" required placeholder="••••••••">
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">
        Entrar no CMS
      </button>
    </form>

    <div class="login-footer">
      <a href="<?= SITE_URL ?>/">← Voltar ao site</a>
    </div>
  </div>
</div>
</body>
</html>
