<?php
require_once __DIR__ . '/../includes/auth.php';
adminLogout();
setFlash('success', '您已安全退出登录');
redirect('login.php');
