<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/_guard.php';

/* =====================
   VALIDASI ADMIN
===================== */
if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    die('Akses ditolak');
}

$orderId = (int)($_GET['id'] ?? 0);
if (!$orderId) {
    die('Order tidak valid');
}

/* =====================
   AMBIL DATA ORDER
===================== */
$orderStmt = $pdo->prepare("
    SELECT id, payment_status
    FROM orders
    WHERE id = ?
");
$orderStmt->execute([$orderId]);
$order = $orderStmt->fetch();

if (!$order) {
    die('Order tidak ditemukan');
}

$canGenerate = in_array($order['payment_status'], ['settlement', 'paid']);

/* =====================
   AMBIL ITEM ORDER
===================== */
$stmt = $pdo->prepare("
    SELECT
        oi.id AS order_item_id,
        oi.quantity,
        p.title,
        pf.id AS platform_id,
        pf.name AS platform_name,
        pf.slug AS platform_slug,
        rc.code
    FROM order_items oi
    JOIN products p ON p.id = oi.product_id
    JOIN platforms pf ON pf.id = oi.platform_id
    LEFT JOIN redeem_codes rc ON rc.order_item_id = oi.id
    WHERE oi.order_id = ?
");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<main class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Admin · Order #<?= $orderId ?></h2>
        <span class="badge bg-<?= $canGenerate ? 'success' : 'warning' ?>">
            <?= strtoupper($order['payment_status']) ?>
        </span>
    </div>

    <?php if (!$items): ?>
        <div class="alert alert-info">
            Tidak ada item pada order ini.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-dark table-striped align-middle">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Platform</th>
                        <th>Redeem Code</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $it): ?>
                    <tr>
                        <td><?= htmlspecialchars($it['title']) ?></td>
                        <td><?= htmlspecialchars($it['platform_name']) ?></td>
                        <td>
                            <?php if ($it['code']): ?>
                                <code><?= htmlspecialchars($it['code']) ?></code>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">
                                    Belum tersedia
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <?php if ($canGenerate && !$it['code']): ?>
                                <form method="post"
                                      action="order_validate.php"
                                      class="d-inline">
                                    <input type="hidden"
                                           name="order_item_id"
                                           value="<?= (int)$it['order_item_id'] ?>">
                                    <button class="btn btn-sm btn-warning">
                                        Generate
                                    </button>
                                </form>
                            <?php elseif (!$canGenerate): ?>
                                <span class="text-muted small">
                                    Menunggu pembayaran
                                </span>
                            <?php else: ?>
                                <span class="text-success small">
                                    Sudah dibuat
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <a href="orders.php" class="btn btn-outline-secondary mt-3">
        &laquo; Kembali ke Orders
    </a>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
