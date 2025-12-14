<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/_guard.php';

function generate_redeem_code(string $platformSlug): string {
    $prefix = strtoupper(substr($platformSlug ?: 'GAME', 0, 4));
    $chars  = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $raw    = '';
    for ($i = 0; $i < 16; $i++) {
        $raw .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $prefix . '-' .
           substr($raw,0,4) . '-' .
           substr($raw,4,4) . '-' .
           substr($raw,8,4) . '-' .
           substr($raw,12,4);
}

$orderItemId = (int)($_POST['order_item_id'] ?? 0);
if (!$orderItemId) die('Invalid item');

$stmt = $pdo->prepare("
    SELECT 
        oi.id,
        oi.order_id,
        o.payment_status,
        pf.id AS platform_id,
        pf.slug
    FROM order_items oi
    JOIN orders o ON o.id = oi.order_id
    JOIN platforms pf ON pf.id = oi.platform_id
    WHERE oi.id = ?
");
$stmt->execute([$orderItemId]);
$item = $stmt->fetch();

if (!$item) die('Item tidak ditemukan');

/* VALIDASI PAYMENT */
if (!in_array($item['payment_status'], ['settlement', 'paid'])) {
    die('Order belum dibayar');
}

/* CEK SUDAH ADA CODE */
$check = $pdo->prepare("SELECT id FROM redeem_codes WHERE order_item_id = ?");
$check->execute([$orderItemId]);
if ($check->fetch()) {
    header('Location: order_detail.php?id=' . $item['order_id']);
    exit;
}

/* GENERATE */
$code = generate_redeem_code($item['slug']);

$insert = $pdo->prepare("
    INSERT INTO redeem_codes (order_item_id, platform_id, code)
    VALUES (?, ?, ?)
");
$insert->execute([
    $orderItemId,
    $item['platform_id'],
    $code
]);

header('Location: order_detail.php?id=' . $item['order_id']);
exit;
