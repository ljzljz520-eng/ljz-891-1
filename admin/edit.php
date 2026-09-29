<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$db = getDB();
$flash = getFlash();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $id > 0;

// 初始化数据
$record = [
    'id' => 0,
    'phone' => '',
    'serial_number' => '',
    'product_category' => '',
    'product_model' => '',
    'purchase_channel' => '',
    'purchase_date' => '',
    'warranty_start' => '',
    'warranty_end' => '',
    'service_outlet' => '',
    'outlet_phone' => '',
    'notes' => '',
];

if ($isEdit) {
    $stmt = $db->prepare('SELECT * FROM warranties WHERE id = ?');
    $stmt->execute([$id]);
    $record = $stmt->fetch();
    if (!$record) {
        setFlash('error', '未找到该延保记录');
        redirect('index.php');
    }
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = '表单验证失败，请重试';
    }

    // 获取并清理输入
    $record['phone'] = trim($_POST['phone'] ?? '');
    $record['serial_number'] = trim($_POST['serial_number'] ?? '');
    $record['product_category'] = trim($_POST['product_category'] ?? '');
    $record['product_model'] = trim($_POST['product_model'] ?? '');
    $record['purchase_channel'] = trim($_POST['purchase_channel'] ?? '');
    $record['purchase_date'] = trim($_POST['purchase_date'] ?? '');
    $record['warranty_start'] = trim($_POST['warranty_start'] ?? '');
    $record['warranty_end'] = trim($_POST['warranty_end'] ?? '');
    $record['service_outlet'] = trim($_POST['service_outlet'] ?? '');
    $record['outlet_phone'] = trim($_POST['outlet_phone'] ?? '');
    $record['notes'] = trim($_POST['notes'] ?? '');

    // 验证
    if (empty($record['phone'])) {
        $errors[] = '购买手机号不能为空';
    } elseif (!validatePhone($record['phone'])) {
        $errors[] = '手机号格式不正确';
    }

    if (empty($record['serial_number'])) {
        $errors[] = '设备序列号不能为空';
    } elseif (!validateSerial($record['serial_number'])) {
        $errors[] = '序列号格式不正确（4-30位字母数字或连字符）';
    }

    if (empty($record['product_category'])) {
        $errors[] = '适用品类不能为空';
    }

    if (empty($record['warranty_start'])) {
        $errors[] = '延保开始日期不能为空';
    }

    if (empty($record['warranty_end'])) {
        $errors[] = '延保结束日期不能为空';
    }

    if (!empty($record['warranty_start']) && !empty($record['warranty_end'])
        && strtotime($record['warranty_end']) < strtotime($record['warranty_start'])) {
        $errors[] = '延保结束日期不能早于开始日期';
    }

    // 检查手机号+序列号唯一性
    if (empty($errors)) {
        $stmt = $db->prepare('SELECT id FROM warranties WHERE phone = ? AND serial_number = ? AND id != ?');
        $stmt->execute([$record['phone'], $record['serial_number'], $id]);
        if ($stmt->fetch()) {
            $errors[] = '该手机号+序列号的组合已存在，请勿重复添加';
        }
    }

    if (empty($errors)) {
        if ($isEdit) {
            $stmt = $db->prepare("UPDATE warranties SET
                phone = ?, serial_number = ?, product_category = ?, product_model = ?,
                purchase_channel = ?, purchase_date = ?, warranty_start = ?, warranty_end = ?,
                service_outlet = ?, outlet_phone = ?, notes = ?, updated_at = datetime('now', 'localtime')
                WHERE id = ?");
            $stmt->execute([
                $record['phone'], $record['serial_number'], $record['product_category'], $record['product_model'],
                $record['purchase_channel'], $record['purchase_date'], $record['warranty_start'], $record['warranty_end'],
                $record['service_outlet'], $record['outlet_phone'], $record['notes'], $id,
            ]);
            setFlash('success', '延保记录更新成功');
        } else {
            $stmt = $db->prepare("INSERT INTO warranties
                (phone, serial_number, product_category, product_model, purchase_channel,
                 purchase_date, warranty_start, warranty_end, service_outlet, outlet_phone, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $record['phone'], $record['serial_number'], $record['product_category'], $record['product_model'],
                $record['purchase_channel'], $record['purchase_date'], $record['warranty_start'], $record['warranty_end'],
                $record['service_outlet'], $record['outlet_phone'], $record['notes'],
            ]);
            setFlash('success', '延保记录添加成功');
        }
        redirect('index.php');
    }
}

$adminName = $_SESSION[ADMIN_SESSION]['username'] ?? '管理员';
$categories = ['冰箱', '洗衣机', '空调', '电视', '热水器', '油烟机', '燃气灶', '洗碗机', '微波炉', '净水器', '空气净化器', '其他'];
$channels = ['京东商城', '天猫旗舰店', '苏宁易购', '国美门店', '线下专卖店', '品牌官网', '其他'];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? '编辑' : '新增'; ?>延保记录 - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/admin.css">
</head>
<body>
    <header class="admin-header">
        <div class="brand">◆ 延保管理后台</div>
        <div class="user-info">
            <span>👤 <?php echo e($adminName); ?></span>
            <a href="index.php">返回列表</a>
            <a href="logout.php">退出登录</a>
        </div>
    </header>

    <main class="admin-container">
        <div class="form-card">
            <h2><?php echo $isEdit ? '编辑延保记录' : '新增延保记录'; ?></h2>

            <?php if ($flash): ?>
                <div class="alert alert-<?php echo e($flash['type']); ?>"><?php echo e($flash['message']); ?></div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <strong>请修正以下错误：</strong>
                    <ul style="margin-top:6px; padding-left:20px;">
                        <?php foreach ($errors as $err): ?>
                            <li><?php echo e($err); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">

                <div class="form-row">
                    <div class="form-field">
                        <label>购买手机号 <span class="req">*</span></label>
                        <input type="tel" name="phone" value="<?php echo e($record['phone']); ?>"
                               placeholder="11位手机号" maxlength="11" required>
                    </div>
                    <div class="form-field">
                        <label>设备序列号 <span class="req">*</span></label>
                        <input type="text" name="serial_number" value="<?php echo e($record['serial_number']); ?>"
                               placeholder="设备序列号" maxlength="30" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-field">
                        <label>适用品类 <span class="req">*</span></label>
                        <select name="product_category" required>
                            <option value="">请选择品类</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo e($cat); ?>" <?php echo $record['product_category'] === $cat ? 'selected' : ''; ?>>
                                    <?php echo e($cat); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-field">
                        <label>产品型号</label>
                        <input type="text" name="product_model" value="<?php echo e($record['product_model']); ?>"
                               placeholder="如 BCD-520WKPZM">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-field">
                        <label>购买渠道</label>
                        <select name="purchase_channel">
                            <option value="">请选择渠道</option>
                            <?php foreach ($channels as $ch): ?>
                                <option value="<?php echo e($ch); ?>" <?php echo $record['purchase_channel'] === $ch ? 'selected' : ''; ?>>
                                    <?php echo e($ch); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-field">
                        <label>购买日期</label>
                        <input type="date" name="purchase_date" value="<?php echo e($record['purchase_date']); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-field">
                        <label>延保开始日期 <span class="req">*</span></label>
                        <input type="date" name="warranty_start" value="<?php echo e($record['warranty_start']); ?>" required>
                    </div>
                    <div class="form-field">
                        <label>延保结束日期 <span class="req">*</span></label>
                        <input type="date" name="warranty_end" value="<?php echo e($record['warranty_end']); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-field">
                        <label>服务网点</label>
                        <input type="text" name="service_outlet" value="<?php echo e($record['service_outlet']); ?>"
                               placeholder="如 北京市朝阳区建国路服务中心">
                    </div>
                    <div class="form-field">
                        <label>网点联系电话</label>
                        <input type="tel" name="outlet_phone" value="<?php echo e($record['outlet_phone']); ?>"
                               placeholder="如 010-88886666">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-field full">
                        <label>备注说明</label>
                        <textarea name="notes" placeholder="其他需要说明的信息"><?php echo e($record['notes']); ?></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><?php echo $isEdit ? '保存修改' : '确认新增'; ?></button>
                    <a href="index.php" class="btn btn-secondary">取消返回</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
