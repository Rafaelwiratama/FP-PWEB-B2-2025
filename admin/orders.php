<?php
require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/../config/config.php';

include __DIR__ . '/../includes/header.php';

$sql = "
SELECT o.id, o.user_id, o.total_price, o.payment_status, o.midtrans_order_id, o.created_at,
       u.name as customer_name, u.email as customer_email,
       (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as items_count
FROM orders o
LEFT JOIN users u ON u.id = o.user_id
ORDER BY o.created_at DESC
";
$orders = $pdo->query($sql)->fetchAll();
?>

<main class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Admin · Orders</h1>
        <a href="../index.php" class="btn btn-outline-secondary btn-sm">Lihat Website</a>
    </div>

    <?php if (!$orders): ?>
        <div class="alert alert-info">Belum ada order.</div>
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
                <?php foreach ($orders as $i => $o): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td>
                            #<?= $o['id'] ?>
                            <div class="small text-muted">
                                <?= htmlspecialchars($o['midtrans_order_id'] ?? '-') ?>
                            </div>
                        </td>
                        <td>
                            <?= htmlspecialchars($o['customer_name'] ?? 'Guest') ?><br>
                            <small class="text-muted"><?= htmlspecialchars($o['customer_email'] ?? '-') ?></small>
                        </td>
                        <td><?= (int)$o['items_count'] ?></td>
                        <td class="text-end"><?= rupiah($o['total_price']) ?></td>
                        <td>
                            <?php
                            $ps = $o['payment_status'] ?? 'pending';
                            $cls = match ($ps) {
                                'settlement', 'paid' => 'success',
                                'pending' => 'warning',
                                'cancel', 'expire' => 'danger',
                                default => 'secondary'
                            };
                            ?>
                            <span class="badge bg-<?= $cls ?>">
                                <?= ucfirst($ps) ?>
                            </span>
                        </td>
                        <td><?= date('Y-m-d H:i', strtotime($o['created_at'])) ?></td>
                        <td class="text-end">
                            <a href="order_detail.php?id=<?= $o['id'] ?>"
                               class="btn btn-sm btn-outline-primary">
                                Detail / Generate Code
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
