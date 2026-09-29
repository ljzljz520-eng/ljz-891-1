<?php
/**
 * 辅助函数
 */

/**
 * 转义 HTML 输出
 */
function e(?string $str): string
{
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * 格式化日期显示
 */
function formatDate(?string $date): string
{
    if (empty($date) || $date === '0000-00-00') {
        return '—';
    }
    return date('Y年m月d日', strtotime($date));
}

/**
 * 计算延保剩余天数
 */
function warrantyRemainingDays(?string $endDate): ?int
{
    if (empty($endDate)) {
        return null;
    }
    $end = strtotime($endDate);
    $now = strtotime(date('Y-m-d'));
    $diff = $end - $now;
    return (int) floor($diff / 86400);
}

/**
 * 获取延保状态
 */
function warrantyStatus(?string $endDate): array
{
    $days = warrantyRemainingDays($endDate);
    if ($days === null) {
        return ['未知', 'status-unknown'];
    }
    if ($days < 0) {
        return ['已过期', 'status-expired'];
    }
    if ($days <= 30) {
        return ['即将到期', 'status-warning'];
    }
    return ['在保中', 'status-active'];
}

/**
 * 验证手机号格式
 */
function validatePhone(string $phone): bool
{
    return (bool) preg_match('/^1[3-9]\d{9}$/', $phone);
}

/**
 * 验证序列号格式（字母数字，4-30位）
 */
function validateSerial(string $serial): bool
{
    return (bool) preg_match('/^[A-Za-z0-9\-]{4,30}$/', $serial);
}

/**
 * 生成 CSRF Token
 */
function csrfToken(): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * 验证 CSRF Token
 */
function verifyCsrf(?string $token): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
}

/**
 * 重定向
 */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/**
 * 闪现消息
 */
function setFlash(string $type, string $message): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * 获取并清除闪现消息
 */
function getFlash(): ?array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}
