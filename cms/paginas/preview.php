<?php
/**
 * Serves the AI-modified preview of a PHP page.
 * Loads temp content from session and executes it in the page's original context.
 */
if (session_status() === PHP_SESSION_NONE) session_start();

$token = $_GET['token'] ?? '';

if (!$token || empty($_SESSION['ai_preview'][$token])) {
    http_response_code(404);
    echo '<p style="font-family:sans-serif;color:#ef4444;padding:20px">Preview expirado ou inválido. Gere novamente.</p>';
    exit;
}

$preview = $_SESSION['ai_preview'][$token];

// Expire check
if (time() > ($preview['expires'] ?? 0)) {
    unset($_SESSION['ai_preview'][$token]);
    http_response_code(410);
    echo '<p style="font-family:sans-serif;color:#ef4444;padding:20px">Preview expirado. Gere novamente.</p>';
    exit;
}

$content   = $preview['content'];
$file_path = $preview['file_path'];

// Write temp file next to the original so all relative paths work
$dir      = dirname($file_path);
$tmp_name = '_ai_preview_' . $token . '.php';
$tmp_path = $dir . '/' . $tmp_name;

file_put_contents($tmp_path, $content);

try {
    ob_start();
    include $tmp_path;
    $html = ob_get_clean();
    echo $html;
} catch (Throwable $e) {
    ob_end_clean();
    echo '<div style="font-family:sans-serif;background:#1e293b;color:#fca5a5;padding:24px;margin:20px;border-radius:8px">';
    echo '<strong>Erro no preview:</strong> ' . htmlspecialchars($e->getMessage());
    echo '</div>';
    echo '<div style="font-family:sans-serif;padding:20px;color:#e2e8f0">O restante do preview pode estar incompleto devido ao erro acima.</div>';
}

// Clean up temp file
@unlink($tmp_path);
