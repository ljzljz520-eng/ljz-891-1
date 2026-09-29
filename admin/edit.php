<?php
declare(strict_types=1);
require __DIR__ . '/auth.php';
require_admin();

$pdo = get_db();
$id     = (int)($_GET['id'] ?? 0);
$isEdit = $id > 0;

$categories = ['冰箱', '洗衣机', '空调', '电视机', '热水器', '厨房电器', '洗碗机', '烘干机', '小家电', '其他'];
$channels   = ['品牌官网', '线上品牌旗舰店', '线下专卖店', '授权经销商', '家电连锁卖场', '电商平台', '企业团购', '其他'];

// 默认空数据
$data = [
    'phone' => '', 'serial_number' => '', 'product_name' => '', 'category' => '',
    'purchase_channel' => '', 'purchase_date' => '', 'warranty_start' => '',
    'warranty_end' => '', 'service_outlets' => '', 'remark' => '',
];

if ($isEdit) {
    $stmt = $pdo->prepare('SELECT * FROM warranties WHERE id = ?');
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if (!$found) {
        header('Location: index.php');
        exit;
    }
    $data = array_merge($data, $found);
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    $data['phone']            = trim((string)($_POST['phone'] ?? ''));
    $data['serial_number']    = trim((string)($_POST['serial_number'] ?? ''));
    $data['product_name']     = trim((string)($_POST['product_name'] ?? ''));
    $data['category']         = trim((string)($_POST['category'] ?? ''));
    $data['purchase_channel'] = trim((string)($_POST['purchase_channel'] ?? ''));
    $data['purchase_date']    = trim((string)($_POST['purchase_date'] ?? ''));
    $data['warranty_start']   = trim((string)($_POST['warranty_start'] ?? ''));
    $data['warranty_end']     = trim((string)($_POST['warranty_end'] ?? ''));
    $data['service_outlets']  = trim((string)($_POST['service_outlets'] ?? ''));
    $data['remark']           = trim((string)($_POST['remark'] ?? ''));

    // ---- 校验 ----
    if (!preg_match('/^1\d{10}$/', $data['phone'])) {
        $errors[] = '请输入正确的 11 位手机号';
    }
    if ($data['serial_number'] === '') {
        $errors[] = '设备序列号不能为空';
    }
    if (!in_array($data['category'], $categories, true)) {
        $errors[] = '请选择有效的适用品类';
    }
    $dateRe = '/^\d{4}-\d{2}-\d{2}$/';
    foreach (['purchase_date' => '购买日期', 'warranty_start' => '延保起始日', 'warranty_end' => '延保截止日'] as $f => $label) {
        if ($data[$f] !== '' && !preg_match($dateRe, $data[$f])) {
            $errors[] = "{$label}日期格式不正确";
        }
    }
    if ($data['warranty_start'] !== '' && $data['warranty_end'] !== '' && $data['warranty_end'] < $data['warranty_start']) {
        $errors[] = '延保截止日不能早于起始日';
    }
    // 同一手机号 + 序列号 唯一
    $stmt = $pdo->prepare('SELECT id FROM warranties WHERE phone = ? AND serial_number = ? AND id <> ?');
    $stmt->execute([$data['phone'], $data['serial_number'], $id]);
    if ($stmt->fetch()) {
        $errors[] = '该手机号与设备序列号的延保记录已存在，请勿重复录入';
    }

    if (!$errors) {
        if ($isEdit) {
            $pdo->prepare("UPDATE warranties SET
                phone = ?, serial_number = ?, product_name = ?, category = ?, purchase_channel = ?,
                purchase_date = ?, warranty_start = ?, warranty_end = ?, service_outlets = ?, remark = ?,
                updated_at = datetime('now','localtime')
                WHERE id = ?")
                ->execute([
                    $data['phone'], $data['serial_number'], $data['product_name'], $data['category'],
                    $data['purchase_channel'], $data['purchase_date'], $data['warranty_start'],
                    $data['warranty_end'], $data['service_outlets'], $data['remark'], $id,
                ]);
            set_flash('延保信息已更新');
        } else {
            $pdo->prepare("INSERT INTO warranties
                (phone, serial_number, product_name, category, purchase_channel, purchase_date,
                 warranty_start, warranty_end, service_outlets, remark)
                VALUES (?,?,?,?,?,?,?,?,?,?)")
                ->execute([
                    $data['phone'], $data['serial_number'], $data['product_name'], $data['category'],
                    $data['purchase_channel'], $data['purchase_date'], $data['warranty_start'],
                    $data['warranty_end'], $data['service_outlets'], $data['remark'],
                ]);
            set_flash('延保信息录入成功');
        }
        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $isEdit ? '修改延保信息' : '录入延保信息' ?> · 管理后台</title>
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
            <a href="index.php">返回列表</a>
            <a href="logout.php">退出</a>
        </nav>
    </div>
</header>

<div class="admin-layout">
    <aside class="admin-side">
        <a href="index.php">延保信息管理</a>
        <a href="edit.php" class="active">＋ 录入延保信息</a>
    </aside>

    <main class="admin-main">
        <div class="panel">
            <h2><?= $isEdit ? '修改延保信息' : '录入延保信息' ?></h2>

            <?php if ($errors): ?>
            <ul class="err-list">
                <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
            </ul>
            <?php endif; ?>

            <form method="post" action="edit.php<?= $isEdit ? '?id=' . $id : '' ?>">
                <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

                <div class="form-grid">
                    <div class="f-item">
                        <label>购买手机号 <b>*</b></label>
                        <input type="tel" name="phone" maxlength="11" required placeholder="11 位手机号"
                               value="<?= e($data['phone']) ?>">
                    </div>
                    <div class="f-item">
                        <label>设备序列号（SN） <b>*</b></label>
                        <input type="text" name="serial_number" required placeholder="如 SN20240315001"
                               value="<?= e($data['serial_number']) ?>">
                    </div>
                    <div class="f-item">
                        <label>产品名称 / 型号 <b>*</b></label>
                        <input type="text" name="product_name" required placeholder="如 对开门冰箱 BCD-520W"
                               value="<?= e($data['product_name']) ?>">
                    </div>
                    <div class="f-item">
                        <label>适用品类 <b>*</b></label>
                        <select name="category" required>
                            <option value="">请选择品类</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= e($c) ?>" <?= $data['category'] === $c ? 'selected' : '' ?>><?= e($c) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="f-item">
                        <label>购买渠道</label>
                        <input type="text" name="purchase_channel" list="channel-list" placeholder="选择或输入渠道"
                               value="<?= e($data['purchase_channel']) ?>">
                        <datalist id="channel-list">
                            <?php foreach ($channels as $c): ?><option value="<?= e($c) ?>"></option><?php endforeach; ?>
                        </datalist>
                    </div>
                    <div class="f-item">
                        <label>购买日期</label>
                        <input type="date" name="purchase_date" value="<?= e($data['purchase_date']) ?>">
                    </div>
                    <div class="f-item">
                        <label>延保起始日</label>
                        <input type="date" name="warranty_start" value="<?= e($data['warranty_start']) ?>">
                    </div>
                    <div class="f-item">
                        <label>延保截止日</label>
                        <input type="date" name="warranty_end" value="<?= e($data['warranty_end']) ?>">
                    </div>
                    <div class="f-item full">
                        <label>服务网点（每行一个网点，建议包含名称、地址、电话、营业时间）</label>
                        <textarea name="service_outlets" placeholder="北京朝阳服务中心：朝阳区建国路88号 010-85886666（09:00-18:00）"><?= e($data['service_outlets']) ?></textarea>
                    </div>
                    <div class="f-item full">
                        <label>备注 / 服务说明</label>
                        <textarea name="remark" placeholder="如：整机延保3年，压缩机延保10年"><?= e($data['remark']) ?></textarea>
                    </div>
                </div>

                <div class="f-actions">
                    <button class="btn" type="submit"><?= $isEdit ? '💾 保存修改' : '✅ 确认录入' ?></button>
                    <a class="btn btn-outline" href="index.php">取消</a>
                </div>
            </form>
        </div>
    </main>
</div>

</body>
</html>
