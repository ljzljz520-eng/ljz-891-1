<?php
/**
 * 数据库连接（PDO SQLite）
 */

function getDB(): PDO
{
    static $db = null;
    if ($db === null) {
        $dir = dirname(DB_PATH);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        try {
            $db = new PDO('sqlite:' . DB_PATH);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $db->exec('PRAGMA foreign_keys = ON');
            $db->exec('PRAGMA journal_mode = WAL');
        } catch (PDOException $e) {
            die('数据库连接失败: ' . $e->getMessage());
        }
    }
    return $db;
}

/**
 * 初始化数据库表结构
 */
function initDB(): void
{
    $db = getDB();

    $db->exec("CREATE TABLE IF NOT EXISTS admins (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL UNIQUE,
        password_hash TEXT NOT NULL,
        created_at TEXT NOT NULL DEFAULT (datetime('now', 'localtime'))
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS warranties (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        phone TEXT NOT NULL,
        serial_number TEXT NOT NULL,
        product_category TEXT NOT NULL DEFAULT '',
        product_model TEXT NOT NULL DEFAULT '',
        purchase_channel TEXT NOT NULL DEFAULT '',
        purchase_date TEXT NOT NULL DEFAULT '',
        warranty_start TEXT NOT NULL DEFAULT '',
        warranty_end TEXT NOT NULL DEFAULT '',
        service_outlet TEXT NOT NULL DEFAULT '',
        outlet_phone TEXT NOT NULL DEFAULT '',
        notes TEXT NOT NULL DEFAULT '',
        created_at TEXT NOT NULL DEFAULT (datetime('now', 'localtime')),
        updated_at TEXT NOT NULL DEFAULT (datetime('now', 'localtime'))
    )");

    // 索引加速查询
    $db->exec("CREATE INDEX IF NOT EXISTS idx_warranties_phone ON warranties(phone)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_warranties_serial ON warranties(serial_number)");
    $db->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_warranties_phone_serial ON warranties(phone, serial_number)");
}
