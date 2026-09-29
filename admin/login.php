<?php
require_once __DIR__ . '/../includes/auth.php';

// 已登录则跳转
if (isAdminLoggedIn()) {
    redirect('index.php');
}

$error = '';
$flash = getFlash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = '请输入用户名和密码';
    } elseif (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = '表单验证失败，请重试';
    } elseif (adminLogin($username, $password)) {
        setFlash('success', '登录成功，欢迎回来！');
        redirect('index.php');
    } else {
        $error = '用户名或密码错误';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>后台登录 - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/admin.css">
</head>
<body>
    <div class="login-wrap">
        <div class="login-card">
            <h1>◆ 后台管理</h1>
            <p class="subtitle"><?php echo SITE_NAME; ?></p>

            <?php if ($flash): ?>
                <div class="alert alert-<?php echo e($flash['type']); ?>"><?php echo e($flash['message']); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo e($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
                <div class="form-group">
                    <label for="username">用户名</label>
                    <input type="text" id="username" name="username" class="form-input"
                           value="<?php echo e($_POST['username'] ?? ''); ?>" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password">密码</label>
                    <input type="password" id="password" name="password" class="form-input" required>
                </div>
                <button type="submit" class="btn btn-primary">登 录</button>
            </form>
            <p style="text-align:center; margin-top:20px; font-size:13px; color:var(--text-secondary);">
                默认账号：admin / admin123
            </p>
        </div>
    </div>
</body>
</html>
