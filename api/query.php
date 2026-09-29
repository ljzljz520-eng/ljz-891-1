<?php
/**
 * 延保查询 API 接口
 * 接收手机号和序列号，返回 JSON 格式的延保信息
 */

require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json; charset=utf-8');

// 仅允许 POST 请求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => '请求方式错误'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 获取输入
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = $_POST;
}

$phone = trim($input['phone'] ?? '');
$serial = trim($input['serial_number'] ?? '');

// 验证输入
$errors = [];
if (empty($phone)) {
    $errors[] = '请输入购买手机号';
} elseif (!validatePhone($phone)) {
    $errors[] = '手机号格式不正确';
}

if (empty($serial)) {
    $errors[] = '请输入设备序列号';
} elseif (!validateSerial($serial)) {
    $errors[] = '序列号格式不正确（4-30位字母数字或连字符）';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode('；', $errors)], JSON_UNESCAPED_UNICODE);
    exit;
}

// 查询数据库
try {
    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM warranties WHERE phone = ? AND serial_number = ? LIMIT 1');
    $stmt->execute([$phone, $serial]);
    $warranty = $stmt->fetch();

    if (!$warranty) {
        // 未找到记录
        echo json_encode([
            'success' => false,
            'not_found' => true,
            'message' => '未查询到相关延保信息',
            'service' => [
                'hotline' => SERVICE_HOTLINE,
                'email' => SERVICE_EMAIL,
            ],
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 计算延保状态
    [$statusText, $statusClass] = warrantyStatus($warranty['warranty_end']);
    $remainingDays = warrantyRemainingDays($warranty['warranty_end']);

    // 返回成功结果
    echo json_encode([
        'success' => true,
        'data' => [
            'id' => (int)$warranty['id'],
            'product_category' => $warranty['product_category'],
            'product_model' => $warranty['product_model'],
            'purchase_channel' => $warranty['purchase_channel'],
            'purchase_date' => formatDate($warranty['purchase_date']),
            'warranty_start' => formatDate($warranty['warranty_start']),
            'warranty_end' => formatDate($warranty['warranty_end']),
            'warranty_end_raw' => $warranty['warranty_end'],
            'service_outlet' => $warranty['service_outlet'],
            'outlet_phone' => $warranty['outlet_phone'],
            'notes' => $warranty['notes'],
            'status' => $statusText,
            'status_class' => $statusClass,
            'remaining_days' => $remainingDays,
        ],
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => '查询服务暂时不可用，请稍后重试'], JSON_UNESCAPED_UNICODE);
}
