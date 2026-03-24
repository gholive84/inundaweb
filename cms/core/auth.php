<?php
function auth_check(): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['cms_user_id'])) {
        header('Location: ' . CMS_URL . '/login.php');
        exit;
    }
}

function auth_user(): ?array {
    if (empty($_SESSION['cms_user_id'])) return null;
    return $_SESSION['cms_user'] ?? null;
}

function auth_is_admin(): bool {
    $u = auth_user();
    return $u && $u['role'] === 'admin';
}

function auth_login(string $email, string $pass): bool {
    try {
        $stmt = db()->prepare('SELECT * FROM cms_users WHERE email = ? AND active = 1 LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
    } catch (Exception $e) {
        return false;
    }

    if (!$user) return false;
    if (!password_verify($pass, $user['password_hash'])) return false;

    // update last_login
    try {
        $upd = db()->prepare('UPDATE cms_users SET last_login = NOW() WHERE id = ?');
        $upd->execute([$user['id']]);
    } catch (Exception $e) {}

    $_SESSION['cms_user_id'] = $user['id'];
    $_SESSION['cms_user']    = [
        'id'    => $user['id'],
        'nome'  => $user['nome'],
        'email' => $user['email'],
        'role'  => $user['role'],
    ];

    return true;
}

function auth_logout(): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
    header('Location: ' . CMS_URL . '/login.php');
    exit;
}

function csrf_token(): string {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_verify(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals(csrf_token(), $token)) {
        http_response_code(403);
        die('Token CSRF inválido.');
    }
}
