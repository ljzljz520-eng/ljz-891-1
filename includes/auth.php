<?php
/**
 * 管理员认证
 */

require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * 检查管理员是否已登录
 */
function isAdminLoggedIn(): bool
{
    return !empty($_SESSION[ADMIN_SESSION]);
}

/**
 * 要求管理员登录，否则跳转
 */
function requireAdmin(): void
{
    if (!isAdminLoggedIn()) {
        setFlash('error', '请先登录后再访问该页面');
        redirect('login.php');
    }
}

/**
 * 管理员登录
 */
function adminLogin(string $username, string $password): bool
{
    $db = getDB();
    $stmt = $db->prepare('SELECT id, username, password_hash FROM admins WHERE username = ?');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        $_SESSION[ADMIN_SESSION] = [
            'id' => $admin['id'],
            'username' => $admin['username'],
            'login_time' => time(),
        ];
        // 重新生成 session ID 防止会话固定攻击
        session_regenerate_id(true);
        return true;
    }
    return false;
}

/**
 * 管理员登出
 */
function adminLogout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}

/**
 * 创建默认管理员（仅安装时使用）
 */
function createDefaultAdmin(): void
{
    $db = getDB();
    $stmt = $db->query('SELECT COUNT(*) FROM admins');
    if ($stmt->fetchColumn() == 0) {
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $db->prepare('INSERT INTO admins (username, password_hash) VALUES (?, ?)');
        $stmt->execute(['admin', $hash]);
    }
}
