<?php
define('CMS_DIR', __DIR__);
define('SITE_ROOT', dirname(__DIR__));
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/db.php';
require_once __DIR__ . '/core/auth.php';
require_once __DIR__ . '/core/functions.php';
if (session_status() === PHP_SESSION_NONE) session_start();
