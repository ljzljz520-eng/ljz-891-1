<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    setFlash('error', '参数错误');
    redirect('index.php');
}

$db = getDB();
$stmt = $db->prepare('SELECT id FROM warranties WHERE id = ?');
$stmt->execute([$id]);
if (!$stmt->fetch()) {
    setFlash('error', '未找到该延保记录');
    redirect('index.php');
}

$stmt = $db->prepare('DELETE FROM warranties WHERE id = ?');
$stmt->execute([$id]);

setFlash('success', '延保记录已删除');
redirect('index.php');
