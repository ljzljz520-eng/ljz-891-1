<?php
require_once __DIR__ . '/includes/config.php';
initDB();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - 延保查询</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <header class="header">
        <div class="header-inner">
            <div class="logo">
                <span class="logo-icon">◆</span>
                <span><?php echo SITE_NAME; ?></span>
            </div>
            <nav class="header-nav">
                <a href="index.php">延保查询</a>
                <a href="admin/login.php">后台管理</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <section class="search-section">
            <h1 class="search-title">家电延保查询</h1>
            <p class="search-subtitle">请输入购买时登记的手机号和设备序列号，查询您的延保期限、服务网点等信息</p>
            <form id="queryForm" class="search-form" novalidate>
                <div class="form-group">
                    <label for="phone">购买手机号 <span class="required">*</span></label>
                    <input type="tel" id="phone" name="phone" class="form-input"
                           placeholder="请输入11位手机号" maxlength="11" autocomplete="tel">
                </div>
                <div class="form-group">
                    <label for="serial">设备序列号 <span class="required">*</span></label>
                    <input type="text" id="serial" name="serial_number" class="form-input"
                           placeholder="请输入设备序列号" maxlength="30" autocomplete="off">
                </div>
                <button type="submit" class="btn btn-primary" id="queryBtn">
                    <span id="btnText">立即查询</span>
                </button>
            </form>
        </section>

        <div id="alertBox"></div>

        <section class="result-section" id="resultSection" style="display:none;">
            <div class="result-header">
                <h2>延保信息</h2>
                <span class="status-badge" id="statusBadge"></span>
            </div>
            <div class="result-body" id="resultBody"></div>
        </section>
    </main>

    <footer class="footer">
        <p><?php echo SITE_NAME; ?> · 客服热线：<?php echo SERVICE_HOTLINE; ?></p>
    </footer>

    <script src="assets/app.js"></script>
</body>
</html>
