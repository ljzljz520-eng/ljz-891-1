<?php
/**
 * 系统安装脚本
 * 初始化数据库表、创建默认管理员、写入示例数据
 */

require_once __DIR__ . '/includes/auth.php';

initDB();
createDefaultAdmin();

// 写入示例数据
$db = getDB();

// 检查是否已有数据
$count = $db->query('SELECT COUNT(*) FROM warranties')->fetchColumn();

if ($count == 0) {
    $sampleData = [
        [
            'phone' => '13800138001',
            'serial_number' => 'SN20240001',
            'product_category' => '冰箱',
            'product_model' => 'BCD-520WKPZM',
            'purchase_channel' => '京东商城',
            'purchase_date' => '2023-06-18',
            'warranty_start' => '2023-06-18',
            'warranty_end' => '2026-06-18',
            'service_outlet' => '北京市朝阳区建国路服务中心',
            'outlet_phone' => '010-88886666',
            'notes' => '压缩机延保至10年',
        ],
        [
            'phone' => '13900139002',
            'serial_number' => 'SN20240002',
            'product_category' => '洗衣机',
            'product_model' => 'XQG100-1436',
            'purchase_channel' => '天猫旗舰店',
            'purchase_date' => '2024-01-15',
            'warranty_start' => '2024-01-15',
            'warranty_end' => '2027-01-15',
            'service_outlet' => '上海市浦东新区张江服务站',
            'outlet_phone' => '021-66668888',
            'notes' => '整机延保3年',
        ],
        [
            'phone' => '13700137003',
            'serial_number' => 'SN20240003',
            'product_category' => '空调',
            'product_model' => 'KFR-35GW/BP3DN8Y',
            'purchase_channel' => '线下国美门店',
            'purchase_date' => '2022-05-01',
            'warranty_start' => '2022-05-01',
            'warranty_end' => '2025-05-01',
            'service_outlet' => '广州市天河区体育西服务点',
            'outlet_phone' => '020-77778888',
            'notes' => '',
        ],
        [
            'phone' => '13600136004',
            'serial_number' => 'SN20240004',
            'product_category' => '电视',
            'product_model' => '65英寸4K超高清',
            'purchase_channel' => '苏宁易购',
            'purchase_date' => '2024-08-20',
            'warranty_start' => '2024-08-20',
            'warranty_end' => '2027-08-20',
            'service_outlet' => '深圳市南山区科技园服务中心',
            'outlet_phone' => '0755-99990000',
            'notes' => '屏幕延保5年',
        ],
        [
            'phone' => '13500135005',
            'serial_number' => 'SN20240005',
            'product_category' => '热水器',
            'product_model' => 'JSQ30-16L',
            'purchase_channel' => '京东商城',
            'purchase_date' => '2021-03-10',
            'warranty_start' => '2021-03-10',
            'warranty_end' => '2024-03-10',
            'service_outlet' => '成都市武侯区服务站',
            'outlet_phone' => '028-55556666',
            'notes' => '',
        ],
    ];

    $stmt = $db->prepare("INSERT INTO warranties
        (phone, serial_number, product_category, product_model, purchase_channel,
         purchase_date, warranty_start, warranty_end, service_outlet, outlet_phone, notes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($sampleData as $row) {
        $stmt->execute([
            $row['phone'], $row['serial_number'], $row['product_category'],
            $row['product_model'], $row['purchase_channel'], $row['purchase_date'],
            $row['warranty_start'], $row['warranty_end'], $row['service_outlet'],
            $row['outlet_phone'], $row['notes'],
        ]);
    }
}

$installed = true;
$adminUser = 'admin';
$adminPass = 'admin123';
$warrantyCount = $db->query('SELECT COUNT(*) FROM warranties')->fetchColumn();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>系统安装 - <?php echo SITE_NAME; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "PingFang SC", "Microsoft YaHei", sans-serif; background: #f0f5ff; color: #1f2937; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .install-card { background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(30,111,255,0.1); padding: 40px; max-width: 520px; width: 90%; }
        h1 { color: #1e6fff; font-size: 24px; margin-bottom: 20px; }
        .success { background: #ecfdf5; color: #065f46; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #10b981; }
        .info { background: #eff6ff; color: #1e40af; padding: 16px; border-radius: 8px; margin-bottom: 20px; }
        .info p { margin: 6px 0; }
        .info strong { color: #1e6fff; }
        .btn { display: inline-block; background: #1e6fff; color: #fff; padding: 10px 24px; border-radius: 8px; text-decoration: none; margin-right: 10px; margin-top: 10px; }
        .btn:hover { background: #1557d6; }
        .btn-secondary { background: #6b7280; }
        .btn-secondary:hover { background: #4b5563; }
    </style>
</head>
<body>
    <div class="install-card">
        <h1>系统安装完成</h1>
        <div class="success">✓ 数据库初始化成功，示例数据已导入</div>
        <div class="info">
            <p><strong>前台查询地址：</strong><a href="index.php">index.php</a></p>
            <p><strong>后台管理地址：</strong><a href="admin/login.php">admin/login.php</a></p>
            <p><strong>管理员账号：</strong><?php echo e($adminUser); ?></p>
            <p><strong>管理员密码：</strong><?php echo e($adminPass); ?></p>
            <p><strong>延保记录数：</strong><?php echo $warrantyCount; ?> 条</p>
        </div>
        <a href="index.php" class="btn">进入前台查询</a>
        <a href="admin/login.php" class="btn btn-secondary">进入后台管理</a>
    </div>
</body>
</html>
