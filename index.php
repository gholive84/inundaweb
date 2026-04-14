<?php
define('ROOT', __DIR__);
define('CMS_PATH', ROOT . '/cms/');

$config_file = ROOT . '/cms/config/config.php';
$cms_installed = file_exists($config_file);

include ROOT . '/site/includes/head.php';
include ROOT . '/site/includes/header.php';
include ROOT . '/site/sections/hero.php';
include ROOT . '/site/sections/services.php';
include ROOT . '/site/sections/about.php';
include ROOT . '/site/sections/portfolio.php';
include ROOT . '/site/sections/clients.php';
include ROOT . '/site/sections/contact.php';
include ROOT . '/site/sections/cta.php';
include ROOT . '/site/includes/footer.php';
