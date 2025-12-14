<?php
require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/../config/config.php';

include __DIR__ . '/../includes/header.php';


$sql = "
SELECT o.id, o.user_id, o.total_price, o.status, o.payment_status, o.midtrans_order_id, o.created_at,
       u.name as customer_name, u.email as customer_email,
       (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as items_count
FROM orders o
LEFT JOIN users u ON u.id = o.user_id
ORDER BY o.created_at DESC
";
$stmt = $pdo->query($sql);
$orders = $stmt->fetchAll();
?>

<main class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Admin · Orders</h1>
        <div>

            <a href="../index.php" class="btn btn-outline-secondary btn-sm">Lihat Website</a>
        </div>
    </div>

    <?php if (empty($orders)): ?>
        <div class="alert alert-info">
            Belum ada order.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Order ID</th>
                        <th>Pembeli</th>
                        <th>Jumlah Item</th>
                        <th class="text-end">Total</th>
                        <th>Status Payment</th>
                        <th>Tanggal</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $i => $ord): ?>
                        <tr>
                            <td><?= $i+1; ?></td>
                            <td>#<?= $ord['id']; ?> <div class="small text-muted"><?= htmlspecialchars($ord['midtrans_order_id'] ?? ''); ?></div></td>
                            <td>
                                <?= htmlspecialchars($ord['customer_name'] ?? 'Guest'); ?><br>
                                <div class="small text-muted"><?= htmlspecialchars($ord['customer_email'] ?? '-'); ?></div>
                            </td>
                            <td><?= (int)$ord['items_count']; ?></td>
                           <td class="text-end">
    <a href="order_detail.php?id=<?= $ord['id']; ?>"
       class="btn btn-sm btn-outline-primary">
        Detail / Generate Code
    </a>
</td>
                                <?php
                                $ps = $ord['payment_status'] ?? 'pending';
                                $cls = 'secondary';
                                if ($ps === 'settlement' || $ps === 'paid') $cls = 'success';
                                if ($ps === 'pending') $cls = 'warning';
                                if ($ps === 'cancel' || $ps === 'expire') $cls = 'danger';
                                ?>
                                <span class="badge bg-<?= $cls ?>"><?= htmlspecialchars(ucfirst($ps)); ?></span>
                            </td>
                            <td><?= date('Y-m-d H:i', strtotime($ord['created_at'])); ?></td>
                            <td class="text-end">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>

