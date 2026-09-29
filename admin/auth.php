<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/** 未登录则跳转登录页 */
function require_admin(): void
{
    if (empty($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}

/** CSRF Token */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

/** 校验 POST 中的 CSRF Token */
function check_csrf(): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(403);
        exit('表单已失效（CSRF 校验失败），请返回重试。');
    }
}

/** 一次性提示消息 */
function set_flash(string $msg): void { $_SESSION['flash'] = $msg; }
function get_flash(): ?string { $m = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $m; }
