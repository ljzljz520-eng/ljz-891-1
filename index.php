<?php
declare(strict_types=1);
require __DIR__ . '/db.php';

$phone    = trim((string)($_POST['phone'] ?? ''));
$serial   = trim((string)($_POST['serial'] ?? ''));
$searched = $_SERVER['REQUEST_METHOD'] === 'POST';
$error    = '';
$row      = null;

if ($searched) {
    if (!preg_match('/^1\d{10}$/', $phone)) {
        $error = '请输入正确的 11 位购买手机号';
    } elseif ($serial === '') {
        $error = '请输入设备序列号';
    } else {
        $stmt = get_db()->prepare('SELECT * FROM warranties WHERE phone = ? AND serial_number = ? LIMIT 1');
        $stmt->execute([$phone, $serial]);
        $row = $stmt->fetch() ?: null;
    }
}

// 计算延保状态
$valid = false;
$remainDays = 0;
if ($row && preg_match('/^\d{4}-\d{2}-\d{2}$/', $row['warranty_end'])) {
    $today = new DateTime('today');
    $end   = new DateTime($row['warranty_end']);
    $diff  = (int)$today->diff($end)->format('%r%a');
    $valid = $diff >= 0;
    $remainDays = max(0, $diff);
}
$outlets = $row ? array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $row['service_outlets'])))) : [];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>家电延保服务查询系统</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<header class="site-header">
    <div class="inner">
        <div class="brand">
            <span class="logo">🛡️</span>
            <span>家电延保服务查询系统<small>EXTENDED WARRANTY SERVICE</small></span>
        </div>
        <nav class="header-links">
            <a href="index.php">延保查询</a>
            <a href="admin/login.php">管理入口</a>
        </nav>
    </div>
</header>

<main class="page">

    <!-- 查询区 -->
    <section class="hero">
        <h1>延保信息查询</h1>
        <p>请输入购买时预留的手机号与设备序列号，查询延保期限、服务网点、购买渠道及适用品类</p>
        <form class="query-form" method="post" action="index.php" novalidate>
            <div class="field">
                <label for="phone">购买手机号</label>
                <input type="tel" id="phone" name="phone" maxlength="11" placeholder="请输入 11 位手机号"
                       value="<?= e($phone) ?>" required>
            </div>
            <div class="field">
                <label for="serial">设备序列号（SN）</label>
                <input type="text" id="serial" name="serial" placeholder="机身或保修卡上的序列号"
                       value="<?= e($serial) ?>" required>
            </div>
            <button class="btn" type="submit">🔍 立即查询</button>
        </form>
    </section>

    <?php if ($error !== ''): ?>
        <div class="alert alert-error">⚠️ <?= e($error) ?></div>
    <?php endif; ?>

    <?php if ($searched && $error === '' && $row): ?>
    <!-- 查询结果 -->
    <section class="result-card">
        <div class="status-banner <?= $valid ? 'valid' : 'expired' ?>">
            <span class="ttl"><?= $valid ? '✅ 延保服务有效' : '⌛ 延保服务已到期' ?></span>
            <span class="sub">
                <?php if ($valid): ?>
                    剩余 <?= $remainDays ?> 天（至 <?= fmt_date($row['warranty_end']) ?>）
                <?php else: ?>
                    已于 <?= fmt_date($row['warranty_end']) ?> 到期，如需续保请联系客服
                <?php endif; ?>
            </span>
        </div>
        <div class="result-body">
            <div class="info-grid">
                <div class="info-item">
                    <span class="ico">📦</span>
                    <div><div class="k">产品名称 / 型号</div><div class="v"><?= e($row['product_name']) ?: '—' ?></div></div>
                </div>
                <div class="info-item">
                    <span class="ico">🏷️</span>
                    <div><div class="k">适用品类</div><div class="v"><?= e($row['category']) ?: '—' ?></div></div>
                </div>
                <div class="info-item">
                    <span class="ico">🛒</span>
                    <div><div class="k">购买渠道</div><div class="v"><?= e($row['purchase_channel']) ?: '—' ?></div></div>
                </div>
                <div class="info-item">
                    <span class="ico">📅</span>
                    <div><div class="k">购买日期</div><div class="v"><?= fmt_date($row['purchase_date']) ?></div></div>
                </div>
                <div class="info-item">
                    <span class="ico">🛡️</span>
                    <div><div class="k">延保期限</div>
                        <div class="v"><?= fmt_date($row['warranty_start']) ?> 至 <?= fmt_date($row['warranty_end']) ?></div>
                    </div>
                </div>
                <div class="info-item">
                    <span class="ico">🔢</span>
                    <div><div class="k">设备序列号</div><div class="v"><?= e($row['serial_number']) ?></div></div>
                </div>
            </div>

            <?php if ($outlets): ?>
            <h3 class="section-title">就近服务网点</h3>
            <ul class="outlet-list">
                <?php foreach ($outlets as $o): ?>
                    <li>📍 <?= e($o) ?></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>

            <?php if (trim($row['remark']) !== ''): ?>
            <h3 class="section-title">服务说明</h3>
            <div class="remark-box">💡 <?= nl2br(e($row['remark'])) ?></div>
            <?php endif; ?>
        </div>
    </section>

    <?php elseif ($searched && $error === '' && !$row): ?>
    <!-- 未查询到 -->
    <section class="notfound">
        <div class="face">🔍</div>
        <h2>未查询到相关延保信息</h2>
        <p class="tip">系统未找到与「<?= e($phone) ?> / <?= e($serial) ?>」匹配的延保记录</p>
        <div class="cs-box">
            <h3>📞 建议您通过以下方式核对：</h3>
            <ul>
                <li>确认手机号是否为<b>购买时预留的号码</b>，序列号是否与机身铭牌 / 保修卡一致（注意区分 0 与 O、1 与 I）；</li>
                <li>若刚购买不久，延保信息可能尚在录入中，建议 <b>3 个工作日后</b>再次查询；</li>
                <li>拨打客服热线，由人工客服为您核对购买凭证并补录延保信息；</li>
                <li>通过购机门店或线上订单页的「延保服务」入口核实订单状态。</li>
            </ul>
            <div class="cs-hotline">全国客服热线：<b>400-800-9000</b>（每日 8:00 - 21:00）</div>
        </div>
    </section>
    <?php endif; ?>

</main>

<footer class="site-footer">
    © 2026 家电延保服务中心 · 全国客服热线 400-800-9000 · 本系统查询结果仅供参考，以购机凭证与延保协议为准
</footer>

</body>
</html>
