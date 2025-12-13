<?php
session_start();
require_once __DIR__ . '/config/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$orderId = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT
        oi.id,
        p.title,
        pf.name AS platform,
        rc.code
    FROM order_items oi
    JOIN products p ON p.id = oi.product_id
    JOIN platforms pf ON pf.id = oi.platform_id
    LEFT JOIN redeem_codes rc ON rc.order_item_id = oi.id
    WHERE oi.order_id = ?
");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="container mt-4">
    <h2>Order #<?= $orderId ?></h2>

    <?php if (!$items): ?>
        <div class="alert alert-warning mt-3">
            Tidak ada item dalam order ini.
        </div>
    <?php else: ?>
        <table class="table table-dark table-striped mt-3">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Platform</th>
                    <th>Redeem Code</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['title']) ?></td>
                    <td><?= htmlspecialchars($item['platform']) ?></td>
                    <td>
                        <?php if ($item['code']): ?>
                            <code><?= htmlspecialchars($item['code']) ?></code>
                        <?php else: ?>
                            <span class="text-warning">Belum tersedia</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
