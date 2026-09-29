<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$db = getDB();
$flash = getFlash();

// 搜索
$keyword = trim($_GET['keyword'] ?? '');
$statusFilter = $_GET['status'] ?? '';

$where = [];
$params = [];

if ($keyword !== '') {
    $where[] = '(phone LIKE ? OR serial_number LIKE ? OR product_category LIKE ? OR product_model LIKE ? OR purchase_channel LIKE ?)';
    $like = '%' . $keyword . '%';
    $params = array_merge($params, [$like, $like, $like, $like, $like]);
}

if ($statusFilter !== '') {
    $today = date('Y-m-d');
    if ($statusFilter === 'active') {
        $where[] = "warranty_end >= date('now')";
    } elseif ($statusFilter === 'expiring') {
        $where[] = "warranty_end >= date('now') AND warranty_end <= date('now', '+30 days')";
    } elseif ($statusFilter === 'expired') {
        $where[] = "warranty_end < date('now')";
    }
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// 统计
$totalCount = $db->query('SELECT COUNT(*) FROM warranties')->fetchColumn();
$activeCount = $db->query("SELECT COUNT(*) FROM warranties WHERE warranty_end >= date('now')")->fetchColumn();
$expiringCount = $db->query("SELECT COUNT(*) FROM warranties WHERE warranty_end >= date('now') AND warranty_end <= date('now', '+30 days')")->fetchColumn();
$expiredCount = $db->query("SELECT COUNT(*) FROM warranties WHERE warranty_end < date('now')")->fetchColumn();

// 查询列表
$sql = "SELECT * FROM warranties $whereSql ORDER BY created_at DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$warranties = $stmt->fetchAll();

$adminName = $_SESSION[ADMIN_SESSION]['username'] ?? '管理员';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>延保管理 - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/admin.css">
</head>
<body>
    <header class="admin-header">
        <div class="brand">◆ 延保管理后台</div>
        <div class="user-info">
            <span>👤 <?php echo e($adminName); ?></span>
            <a href="../index.php" target="_blank">查看前台</a>
            <a href="logout.php">退出登录</a>
        </div>
    </header>

    <main class="admin-container">
        <?php if ($flash): ?>
            <div class="alert alert-<?php echo e($flash['type']); ?>"><?php echo e($flash['message']); ?></div>
        <?php endif; ?>

        <!-- 统计卡片 -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">延保记录总数</div>
                <div class="stat-value"><?php echo $totalCount; ?></div>
            </div>
            <div class="stat-card success">
                <div class="stat-label">在保中</div>
                <div class="stat-value"><?php echo $activeCount; ?></div>
            </div>
            <div class="stat-card warning">
                <div class="stat-label">即将到期（30天内）</div>
                <div class="stat-value"><?php echo $expiringCount; ?></div>
            </div>
            <div class="stat-card danger">
                <div class="stat-label">已过期</div>
                <div class="stat-value"><?php echo $expiredCount; ?></div>
            </div>
        </div>

        <!-- 工具栏 -->
        <div class="toolbar">
            <form method="GET" action="" class="search-box">
                <input type="text" name="keyword" value="<?php echo e($keyword); ?>"
                       placeholder="搜索手机号/序列号/品类/渠道...">
                <select name="status" style="padding:8px 12px; border:1.5px solid var(--border); border-radius:8px; font-size:14px;">
                    <option value="">全部状态</option>
                    <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>在保中</option>
                    <option value="expiring" <?php echo $statusFilter === 'expiring' ? 'selected' : ''; ?>>即将到期</option>
                    <option value="expired" <?php echo $statusFilter === 'expired' ? 'selected' : ''; ?>>已过期</option>
                </select>
                <button type="submit" class="btn btn-secondary btn-sm">筛选</button>
            </form>
            <a href="edit.php" class="btn btn-primary btn-sm">+ 新增延保记录</a>
        </div>

        <!-- 数据表格 -->
        <div class="table-card">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>序号</th>
                            <th>购买手机号</th>
                            <th>设备序列号</th>
                            <th>适用品类</th>
                            <th>购买渠道</th>
                            <th>延保期限</th>
                            <th>服务网点</th>
                            <th>状态</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($warranties)): ?>
                            <tr>
                                <td colspan="9">
                                    <div class="empty">
                                        <div class="empty-icon">📋</div>
                                        <p>暂无延保记录</p>
                                        <a href="edit.php" class="btn btn-primary btn-sm" style="margin-top:12px;">新增第一条记录</a>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($warranties as $i => $w): ?>
                                <?php [$statusText, $statusClass] = warrantyStatus($w['warranty_end']); ?>
                                <tr>
                                    <td><?php echo $i + 1; ?></td>
                                    <td>
                                        <div class="cell-main"><?php echo e($w['phone']); ?></div>
                                    </td>
                                    <td>
                                        <div class="cell-main"><?php echo e($w['serial_number']); ?></div>
                                        <?php if ($w['product_model']): ?>
                                            <div class="cell-sub"><?php echo e($w['product_model']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($w['product_category']); ?></td>
                                    <td><?php echo e($w['purchase_channel']); ?></td>
                                    <td>
                                        <div class="cell-sub"><?php echo formatDate($w['warranty_start']); ?></div>
                                        <div class="cell-main"><?php echo formatDate($w['warranty_end']); ?></div>
                                    </td>
                                    <td>
                                        <div class="cell-main"><?php echo e($w['service_outlet']); ?></div>
                                        <?php if ($w['outlet_phone']): ?>
                                            <div class="cell-sub"><?php echo e($w['outlet_phone']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span></td>
                                    <td>
                                        <div class="cell-actions">
                                            <a href="edit.php?id=<?php echo $w['id']; ?>" class="btn btn-warning btn-sm">编辑</a>
                                            <a href="delete.php?id=<?php echo $w['id']; ?>" class="btn btn-danger btn-sm"
                                               onclick="return confirm('确定要删除该延保记录吗？此操作不可恢复。')">删除</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
