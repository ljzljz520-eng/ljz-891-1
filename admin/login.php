<?php
declare(strict_types=1);
require __DIR__ . '/auth.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    $stmt = get_db()->prepare('SELECT * FROM admins WHERE username = ?');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id']   = $admin['id'];
        $_SESSION['admin_name'] = $admin['username'];
        header('Location: index.php');
        exit;
    }
    $error = '用户名或密码错误';
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>管理员登录 · 家电延保服务查询系统</title>
<link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<header class="site-header">
    <div class="inner">
        <div class="brand">
            <span class="logo">🛡️</span>
            <span>家电延保 · 管理后台<small>WARRANTY ADMIN</small></span>
        </div>
        <nav class="header-links"><a href="../index.php">← 返回前台查询</a></nav>
    </div>
</header>

<div class="login-wrap">
    <div class="login-card">
        <div class="logo-line">
            <div class="badge">🔐</div>
            <h1>管理员登录</h1>
            <p>延保信息录入与维护平台</p>
        </div>
        <?php if ($error): ?>
            <div class="alert alert-error" style="margin-top:0;margin-bottom:16px;">⚠️ <?= e($error) ?></div>
        <?php endif; ?>
        <form method="post" action="login.php">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <div class="f-item">
                <label for="username">用户名</label>
                <input type="text" id="username" name="username" required autofocus placeholder="请输入用户名"
                       value="<?= e($_POST['username'] ?? '') ?>">
            </div>
            <div class="f-item">
                <label for="password">密码</label>
                <input type="password" id="password" name="password" required placeholder="请输入密码">
            </div>
            <button class="btn" type="submit">登 录</button>
        </form>
    </div>
</div>

<footer class="site-footer">© 2026 家电延保服务中心 · 管理后台</footer>

</body>
</html>
