<?php
require_once __DIR__ . '/_guard.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
$stmt->execute([$id]);
$p = $stmt->fetch();

if ($p && $p['image']) {
    $path = __DIR__ . '/../assets/images/' . $p['image'];
    if (is_file($path)) {
        @unlink($path);
    }
}

$stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
$stmt->execute([$id]);

header('Location: index.php');
exit;
