<?php
/**
 * 系统配置文件
 */

// 错误报告（生产环境建议关闭）
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 时区
date_default_timezone_set('Asia/Shanghai');

// 数据库路径（SQLite）
define('DB_PATH', dirname(__DIR__) . '/data/warranty.db');

// 系统名称
define('SITE_NAME', '家电延保查询系统');

// 客服电话（查不到时展示）
define('SERVICE_HOTLINE', '400-888-0000');
define('SERVICE_EMAIL', 'service@example.com');

// 会话名称
define('ADMIN_SESSION', 'warranty_admin');

// 设置 Session 保存路径（确保目录可写）
$sessionPath = dirname(__DIR__) . '/data/sessions';
if (!is_dir($sessionPath)) {
    @mkdir($sessionPath, 0755, true);
}
if (is_dir($sessionPath) && is_writable($sessionPath)) {
    ini_set('session.save_path', $sessionPath);
}

// 包含数据库连接
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
