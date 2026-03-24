<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Inunda — Tecnologia que flui com seu negócio. WordPress empresarial, suporte premium e hospedagem.">
    <title>Inunda — Tecnologia que flui com seu negócio</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles (v= usa data de modificação do arquivo como cache buster) -->
    <?php
    $css_files = ['reset','variables','global','components','sections','responsive'];
    $css_base  = $_SERVER['DOCUMENT_ROOT'] . '/site/assets/css/';
    foreach ($css_files as $f) {
        $path = $css_base . $f . '.css';
        $v    = file_exists($path) ? filemtime($path) : time();
        echo "<link rel=\"stylesheet\" href=\"/site/assets/css/{$f}.css?v={$v}\">\n    ";
    }
    ?>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/site/assets/img/favicon.svg">

    <!-- Códigos de cabeçalho (Analytics, GTM, etc.) gerenciados via CMS -->
    <?php
    try {
        if (!function_exists('db')) {
            $cms_config = $_SERVER['DOCUMENT_ROOT'] . '/cms/boot.php';
            if (file_exists($cms_config)) require_once $cms_config;
        }
        if (function_exists('setting')) {
            $hc = setting('header_codes', '');
            if ($hc !== '') echo $hc . "\n    ";
        }
    } catch (Exception $e) {}
    ?>
</head>
<body>
