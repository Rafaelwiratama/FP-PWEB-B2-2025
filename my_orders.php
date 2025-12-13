<?php
require_once __DIR__ . '/config/config.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT o.*
    FROM orders o
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <h2 class="mb-4">My Orders</h2>

    <?php if (!$orders): ?>
        <div class="alert alert-info">Belum ada pesanan.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-dark table-hover">
                <thead>
                    <tr>
                        <th>#Order</th>
                        <th>Tanggal</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($orders as $o): ?>
                    <tr>
                        <td>#<?= $o['id'] ?></td>
                        <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                        <td><?= rupiah($o['total_price']) ?></td>
                        <td>
                            <?php if ($o['payment_status'] === 'settlement'): ?>
                                <span class="badge bg-success">Paid</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark"><?= ucfirst($o['payment_status']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <a href="order_detail.php?id=<?= $o['id'] ?>"
                               class="btn btn-sm btn-outline-light">
                                Detail
                            </a>
                        </td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
    <?php endif ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
