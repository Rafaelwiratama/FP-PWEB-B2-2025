<?php
session_start();
require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/../config/config.php';

$orderId = (int)($_GET['id'] ?? 0);
if (!$orderId) die('Invalid order');

$stmt = $pdo->prepare("
    SELECT 
        oi.id AS order_item_id,
        p.title,
        p.is_upcoming,
        pf.name AS platform,
        o.payment_status,
        rc.code
    FROM order_items oi
    JOIN orders o ON o.id = oi.order_id
    JOIN products p ON p.id = oi.product_id
    JOIN platforms pf ON pf.id = oi.platform_id
    LEFT JOIN redeem_codes rc ON rc.order_item_id = oi.id
    WHERE oi.order_id = ?
");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<h1>Admin · Order #<?= $orderId ?></h1>

<table class="table">
<thead>
<tr>
    <th>Produk</th>
    <th>Platform</th>
    <th>Redeem Code</th>
    <th>Aksi</th>
</tr>
</thead>
<tbody>
<?php foreach ($items as $item): ?>
<tr>
    <td><?= htmlspecialchars($item['title']) ?></td>
    <td><?= htmlspecialchars($item['platform']) ?></td>
    <td>
        <?= $item['code'] ? 
            '<span class="badge bg-success">'.$item['code'].'</span>' :
            '<span class="badge bg-warning">Belum tersedia</span>' ?>
    </td>

<?php if ($item['is_upcoming']): ?>
    <span class="badge bg-warning">Upcoming</span>
<?php elseif (!$item['code'] && $item['payment_status'] === 'settlement'): ?>
    <form method="post" action="order_validate.php">
        <input type="hidden" name="order_item_id" value="<?= $item['order_item_id'] ?>">
        <button class="btn btn-sm btn-success">Generate Code</button>
    </form>
<?php else: ?>
    -
<?php endif; ?>
</td>

    <td>
        <?php if (!$item['code'] && $item['payment_status'] === 'settlement'): ?>
            <form method="post" action="order_validate.php">
                <input type="hidden" name="order_item_id" value="<?= $item['order_item_id'] ?>">
                <button class="btn btn-sm btn-success">
                    Generate Code
                </button>
            </form>
        <?php else: ?>
            -
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<?php include __DIR__ . '/../includes/footer.php'; ?>

