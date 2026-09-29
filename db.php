<?php
declare(strict_types=1);

/**
 * 数据库连接（SQLite，免安装 MySQL，开箱即用）
 * 首次访问自动建表并写入示例数据
 */
function get_db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $dir = __DIR__ . '/data';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $pdo = new PDO('sqlite:' . $dir . '/warranty.db', null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA journal_mode = WAL');
    init_schema($pdo);
    return $pdo;
}

function init_schema(PDO $pdo): void
{
    $pdo->exec(<<<'SQL'
    CREATE TABLE IF NOT EXISTS warranties (
        id               INTEGER PRIMARY KEY AUTOINCREMENT,
        phone            TEXT NOT NULL,                 -- 购买手机号
        serial_number    TEXT NOT NULL,                 -- 设备序列号
        product_name     TEXT NOT NULL DEFAULT '',      -- 产品名称/型号
        category         TEXT NOT NULL DEFAULT '',      -- 适用品类
        purchase_channel TEXT NOT NULL DEFAULT '',      -- 购买渠道
        purchase_date    TEXT NOT NULL DEFAULT '',      -- 购买日期
        warranty_start   TEXT NOT NULL DEFAULT '',      -- 延保起始日
        warranty_end     TEXT NOT NULL DEFAULT '',      -- 延保截止日
        service_outlets  TEXT NOT NULL DEFAULT '',      -- 服务网点（每行一条）
        remark           TEXT NOT NULL DEFAULT '',      -- 备注
        created_at       TEXT NOT NULL DEFAULT (datetime('now','localtime')),
        updated_at       TEXT NOT NULL DEFAULT (datetime('now','localtime')),
        UNIQUE (phone, serial_number)
    )
    SQL);

    $pdo->exec(<<<'SQL'
    CREATE TABLE IF NOT EXISTS admins (
        id            INTEGER PRIMARY KEY AUTOINCREMENT,
        username      TEXT NOT NULL UNIQUE,
        password_hash TEXT NOT NULL
    )
    SQL);

    // 默认管理员：admin / admin123（请在部署后尽快修改）
    if ((int)$pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn() === 0) {
        $pdo->prepare('INSERT INTO admins (username, password_hash) VALUES (?, ?)')
            ->execute(['admin', password_hash('admin123', PASSWORD_DEFAULT)]);
    }

    // 示例数据，便于前台立即体验查询效果
    if ((int)$pdo->query('SELECT COUNT(*) FROM warranties')->fetchColumn() === 0) {
        $samples = [
            ['13800138000', 'SN20240315001', '对开门冰箱 BCD-520WGPZM', '冰箱', '线下授权专卖店', '2024-03-15', '2024-03-15', '2027-03-14',
             "北京朝阳服务中心：朝阳区建国路88号 010-85886666（09:00-18:00）\n北京海淀服务中心：海淀区中关村大街27号 010-62601122（09:00-18:00）",
             '整机延保3年，压缩机延保10年'],
            ['13912345678', 'AC2024-KFR35-8866', '变频空调 KFR-35GW/N8XHB1', '空调', '线上品牌旗舰店', '2024-06-01', '2024-06-01', '2028-05-31',
             "上海浦东服务中心：浦东新区张杨路500号 021-58369988（08:30-17:30）\n上海徐汇服务中心：徐汇区漕溪北路88号 021-64275566（08:30-17:30）",
             '含每年一次免费上门清洗保养'],
            ['13711112222', 'WM2022-88001234', '滚筒洗衣机 XQG100-BD14176', '洗衣机', '家电连锁卖场', '2022-11-11', '2022-11-11', '2025-11-10',
             "广州天河服务中心：天河区体育西路103号 020-38809900（09:00-18:00）",
             '电机延保10年'],
        ];
        $stmt = $pdo->prepare('INSERT INTO warranties
            (phone, serial_number, product_name, category, purchase_channel, purchase_date, warranty_start, warranty_end, service_outlets, remark)
            VALUES (?,?,?,?,?,?,?,?,?,?)');
        foreach ($samples as $s) {
            $stmt->execute($s);
        }
    }
}

/** HTML 转义，防 XSS */
function e(?string $s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

/** 日期格式化为中文显示 */
function fmt_date(string $d): string
{
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) {
        return '—';
    }
    return substr($d, 0, 4) . '年' . substr($d, 5, 2) . '月' . substr($d, 8, 2) . '日';
}
