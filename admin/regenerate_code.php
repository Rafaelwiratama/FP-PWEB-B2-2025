<?php
require_once __DIR__ . '/../config/config.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) die('Invalid');

$stmt = $pdo->prepare("
    SELECT rc.platform_id, pf.slug
    FROM redeem_codes rc
    JOIN platforms pf ON pf.id = rc.platform_id
    WHERE rc.id = ?
");
$stmt->execute([$id]);
$data = $stmt->fetch();

if (!$data) die('Code tidak ditemukan');

$newCode = generate_redeem_code($data['slug']);

$update = $pdo->prepare("UPDATE redeem_codes SET code = ? WHERE id = ?");
$update->execute([$newCode, $id]);

$_SESSION['flash_success'] = 'Redeem code berhasil digenerate ulang';
header('Location: orders.php');
exit;
