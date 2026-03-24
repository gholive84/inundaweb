<?php
// Load content types for sidebar (may fail if DB not set up yet)
$_sidebar_types = [];
try {
    $stmt = db()->query('SELECT id, name, slug, icon FROM content_types ORDER BY name ASC');
    $_sidebar_types = $stmt->fetchAll();
} catch (Exception $e) {
    $_sidebar_types = [];
}

$_flash = flash_get();
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= h($page_title ?? 'CMS') ?> — Inunda IA CMS</title>
  <meta name="robots" content="noindex, nofollow">
  <meta name="csrf" content="<?= csrf_token() ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= CMS_URL ?>/assets/css/admin.css">
</head>
<body>
<div class="adm-wrap">

  <!-- SIDEBAR -->
  <aside class="adm-sidebar">
    <div class="adm-sidebar__brand">
      <span class="adm-sidebar__logo">inunda ia</span>
      <span class="adm-sidebar__sub">CMS Admin</span>
    </div>

    <nav class="adm-nav">
      <!-- Principal -->
      <div class="adm-nav__group">Principal</div>

      <div class="adm-nav__item <?= ($active ?? '') === 'dashboard' ? 'active' : '' ?>">
        <a href="<?= CMS_URL ?>/">
          <span class="adm-nav__icon">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
          </span>
          Dashboard
        </a>
      </div>

      <div class="adm-nav__item <?= ($active ?? '') === 'posts' ? 'active' : '' ?>">
        <a href="<?= CMS_URL ?>/posts/">
          <span class="adm-nav__icon">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
          </span>
          Posts
        </a>
      </div>

      <div class="adm-nav__item <?= ($active ?? '') === 'paginas' ? 'active' : '' ?>">
        <a href="<?= CMS_URL ?>/paginas/">
          <span class="adm-nav__icon">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
          </span>
          Páginas
        </a>
      </div>

      <div class="adm-nav__item <?= ($active ?? '') === 'leads' ? 'active' : '' ?>">
        <a href="<?= CMS_URL ?>/leads/">
          <span class="adm-nav__icon">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          </span>
          Leads
        </a>
      </div>

      <?php if (!empty($_sidebar_types)): ?>
      <!-- Conteúdo dinâmico -->
      <div class="adm-nav__group">Conteúdo</div>
      <?php foreach ($_sidebar_types as $ct): ?>
      <div class="adm-nav__item <?= ($active ?? '') === 'content_' . h($ct['slug']) ? 'active' : '' ?>">
        <a href="<?= CMS_URL ?>/content/?type=<?= h($ct['slug']) ?>">
          <span class="adm-nav__icon">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
          </span>
          <?= h($ct['name']) ?>
        </a>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>

      <!-- Configurações -->
      <div class="adm-nav__group">Configurações</div>

      <div class="adm-nav__item <?= ($active ?? '') === 'config_smtp' ? 'active' : '' ?>">
        <a href="<?= CMS_URL ?>/configuracoes/">
          <span class="adm-nav__icon">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          </span>
          Configurações
        </a>
      </div>

      <div class="adm-nav__item <?= ($active ?? '') === 'config_tipos' ? 'active' : '' ?>">
        <a href="<?= CMS_URL ?>/configuracoes/tipos/">
          <span class="adm-nav__icon">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
          </span>
          Tipos de Conteúdo
        </a>
      </div>

      <div class="adm-nav__item <?= ($active ?? '') === 'config_usuarios' ? 'active' : '' ?>">
        <a href="<?= CMS_URL ?>/configuracoes/usuarios/">
          <span class="adm-nav__icon">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          </span>
          Usuários
        </a>
      </div>
    </nav>

    <div class="adm-sidebar__footer">
      <a href="<?= CMS_URL ?>/logout.php">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        Sair do CMS
      </a>
    </div>
  </aside>

  <!-- MAIN -->
  <div class="adm-main">

    <!-- TOPBAR -->
    <header class="adm-topbar">
      <div class="d-flex align-center gap-3">
        <button class="adm-hamburger" id="admHamburger" aria-label="Menu">
          <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
        <span class="adm-topbar__title"><?= h($page_title ?? 'CMS') ?></span>
      </div>
      <div class="adm-topbar__right">
        <?php $u = auth_user(); ?>
        <span class="adm-topbar__user">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right:4px;vertical-align:middle"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          <?= h($u['nome'] ?? 'Admin') ?>
        </span>
        <a href="<?= CMS_URL ?>/logout.php" class="adm-topbar__logout">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          Sair
        </a>
      </div>
    </header>

    <!-- CONTENT -->
    <main class="adm-content">

      <?php if ($_flash): ?>
      <div class="flash flash--<?= h($_flash['type']) ?>">
        <?php if ($_flash['type'] === 'success'): ?>
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        <?php else: ?>
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <?php endif; ?>
        <?= h($_flash['msg']) ?>
      </div>
      <?php endif; ?>
