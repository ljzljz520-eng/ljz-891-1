<?php
declare(strict_types=1);
require __DIR__ . '/auth.php';
require_admin();

$pdo = get_db();
$kw   = trim((string)($_GET['kw'] ?? ''));
$page = max(1, (int)($_GET['page'] ?? 1));
$size = 12;

$where  = '';
$params = [];
if ($kw !== '') {
    $where = 'WHERE phone LIKE ? OR serial_number LIKE ? OR product_name LIKE ? OR category LIKE ?';
    $like = '%' . $kw . '%';
    $params = [$like, $like, $like, $like];
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM warranties $where");
$stmt->execute($params);
$total = (int)$stmt->fetchColumn();
$pages = max(1, (int)ceil($total / $size));
$page  = min($page, $pages);
$offset = ($page - 1) * $size;

$stmt = $pdo->prepare("SELECT * FROM warranties $where ORDER BY id DESC LIMIT $size OFFSET $offset");
$stmt->execute($params);
$rows = $stmt->fetchAll();

$today = new DateTime('today');
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>延保信息管理 · 管理后台</title>
<link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<header class="site-header">
    <div class="inner">
        <div class="brand">
            <span class="logo">🛡️</span>
            <span>家电延保 · 管理后台<small>WARRANTY ADMIN</small></span>
        </div>
        <nav class="header-links">
            <a href="../index.php" target="_blank">查看前台</a>
            <a href="logout.php">退出（<?= e($_SESSION['admin_name'] ?? '') ?>）</a>
        </nav>
    </div>
</header>

<div class="admin-layout">
    <aside class="admin-side">
        <a href="index.php" class="active">延保信息管理</a>
        <a href="edit.php">＋ 录入延保信息</a>
    </aside>

    <main class="admin-main">
        <?php if ($flash): ?><div class="flash">✅ <?= e($flash) ?></div><?php endif; ?>

        <div class="panel">
            <h2>延保信息列表（共 <?= $total ?> 条）</h2>

            <form class="toolbar" method="get" action="index.php">
                <input type="text" name="kw" value="<?= e($kw) ?>" placeholder="手机号 / 序列号 / 产品 / 品类"
                       style="min-width:280px;">
                <button class="btn btn-sm" type="submit">🔍 搜索</button>
                <?php if ($kw !== ''): ?>
                    <a class="btn btn-sm btn-outline" href="index.php">清空</a>
                <?php endif; ?>
                <a class="btn btn-sm" href="edit.php" style="margin-left:auto;">＋ 录入新延保</a>
            </form>

            <div style="overflow-x:auto;">
            <table class="data">
                <thead>
                <tr>
                    <th>ID</th><th>手机号</th><th>设备序列号</th><th>产品 / 型号</th>
                    <th>适用品类</th><th>购买渠道</th><th>延保期限</th><th>状态</th><th>操作</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!$rows): ?>
                    <tr><td colspan="9" style="text-align:center;padding:36px;color:var(--ink-2);">
                        暂无数据，<a href="edit.php">点击录入第一条延保信息</a>
                    </td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $r):
                    $days = null;
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $r['warranty_end'])) {
                        $days = (int)$today->diff(new DateTime($r['warranty_end']))->format('%r%a');
                    }
                ?>
                <tr>
                    <td><?= (int)$r['id'] ?></td>
                    <td><?= e($r['phone']) ?></td>
                    <td><?= e($r['serial_number']) ?></td>
                    <td><?= e($r['product_name']) ?></td>
                    <td><?= e($r['category']) ?></td>
                    <td><?= e($r['purchase_channel']) ?></td>
                    <td style="white-space:nowrap;"><?= fmt_date($r['warranty_start']) ?><br>
                        <small style="color:var(--ink-2);">至 <?= fmt_date($r['warranty_end']) ?></small></td>
                    <td>
                        <?php if ($days === null): ?>
                            <span class="tag tag-gray">未设置</span>
                        <?php elseif ($days >= 0): ?>
                            <span class="tag tag-green">有效（剩 <?= $days ?> 天）</span>
                        <?php else: ?>
                            <span class="tag tag-red">已到期</span>
                        <?php endif; ?>
                    </td>
                    <td style="white-space:nowrap;">
                        <a class="btn btn-sm btn-outline" href="edit.php?id=<?= (int)$r['id'] ?>">修改</a>
                        <form method="post" action="delete.php" style="display:inline;"
                              onsubmit="return confirm('确定删除该延保记录吗？此操作不可恢复。');">
                            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                            <button class="btn btn-sm btn-danger" type="submit">删除</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>

            <?php if ($pages > 1): ?>
            <div class="pager">
                <?php
                $qs = fn(int $p): string => 'index.php?kw=' . urlencode($kw) . '&page=' . $p;
                if ($page > 1): ?><a href="<?= $qs($page - 1) ?>">上一页</a><?php endif;
                for ($p = 1; $p <= $pages; $p++):
                    if ($p == $page): ?><span class="cur"><?= $p ?></span>
                    <?php else: ?><a href="<?= $qs($p) ?>"><?= $p ?></a><?php endif;
                endfor;
                if ($page < $pages): ?><a href="<?= $qs($page + 1) ?>">下一页</a><?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>

</body>
</html>
