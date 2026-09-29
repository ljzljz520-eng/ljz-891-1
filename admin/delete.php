<?php
declare(strict_types=1);
require __DIR__ . '/auth.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}
check_csrf();

$id = (int)($_POST['id'] ?? 0);
if ($id > 0) {
    $stmt = get_db()->prepare('DELETE FROM warranties WHERE id = ?');
    $stmt->execute([$id]);
    set_flash('记录已删除');
}
header('Location: index.php');
exit;
