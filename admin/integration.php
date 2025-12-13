<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/../config/midtrans_config.php';

function mask_key($key)
{
    if (!$key) return '-';
    $len = strlen($key);
    if ($len <= 8) {
        return str_repeat('*', $len);
    }
    return substr($key, 0, 4) . str_repeat('*', $len - 8) . substr($key, -4);
}

// base URL contoh untuk sandbox & produksi
$sandboxBaseUrl = 'http://localhost/Slashers.com';
$productionBaseUrl = 'https://slashers.com';

// callback URL
$sandboxCallbackUrl    = $sandboxBaseUrl . '/payment_pending.php';
$productionCallbackUrl = $productionBaseUrl . '/payment_pending.php';

// ambil riwayat pembayaran (order yang punya midtrans_order_id)
$paymentsStmt = $pdo->query("
    SELECT id, midtrans_order_id, total_price, payment_status, created_at
    FROM orders
    WHERE midtrans_order_id IS NOT NULL
    ORDER BY created_at DESC
    LIMIT 10
");
$payments = $paymentsStmt->fetchAll();
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Admin · Payment & Integration - Slasher.com</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet">
</head>
<body class="bg-dark text-light">

<nav class="navbar navbar-dark bg-black border-bottom border-secondary">
    <div class="container-fluid">
        <span class="navbar-brand mb-0 h6">Slasher Admin</span>
        <div class="d-flex gap-2">
            <a href="../index.php" class="btn btn-outline-light btn-sm">Lihat Website</a>
            <a href="../logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Payment & System Integration</h4>
    </div>

    <div class="row g-4 mb-4">
        <!-- Panel Midtrans -->
        <div class="col-lg-6">
            <div class="card bg-secondary-subtle text-dark">
                <div class="card-header bg-secondary text-light">
                    <strong>Midtrans Snap</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <span class="fw-semibold">Environment:</span>
                        <span class="badge bg-success ms-2">
                            <?php echo $MIDTRANS_IS_PRODUCTION ? 'Production' : 'Sandbox'; ?>
                        </span>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <p class="mb-1 fw-semibold">Client Key</p>
                            <p class="small mb-3">
                                <?php echo htmlspecialchars(mask_key($MIDTRANS_CLIENT_KEY)); ?>
                            </p>
                        </div>
                        <div class="col-sm-6">
                            <p class="mb-1 fw-semibold">Server Key</p>
                            <p class="small mb-3">
                                <?php echo htmlspecialchars(mask_key($MIDTRANS_SERVER_KEY)); ?>
                            </p>
                        </div>
                    </div>

                    <hr>

                    <p class="fw-semibold mb-1">Snap Endpoint</p>
                    <p class="small mb-2">
                        <?php echo htmlspecialchars($MIDTRANS_SNAP_BASE_URL); ?>
                    </p>

                    <p class="fw-semibold mb-1">Snap JS (Frontend)</p>
                    <p class="small mb-0">
                        <?php echo htmlspecialchars($MIDTRANS_SNAP_JS_URL); ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card bg-secondary-subtle text-dark">
                <div class="card-header bg-secondary text-light">
                    <strong>Callback / Notification URL</strong>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-1">
                        URL yang harus kamu set di dashboard Midtrans pada bagian
                        <em>Payment Notification URL</em> / <em>HTTP Notification</em>.
                    </p>

                    <div class="mb-3">
                        <p class="fw-semibold mb-1">Sandbox (Local Development)</p>
                        <code class="small d-block">
                            <?php echo htmlspecialchars($sandboxCallbackUrl); ?>
                        </code>
                    </div>

                    <div class="mb-3">
                        <p class="fw-semibold mb-1">Production (Live - slashers.com)</p>
                        <code class="small d-block">
                            <?php echo htmlspecialchars($productionCallbackUrl); ?>
                        </code>
                    </div>

                    <p class="small text-muted mb-0">
                        Pastikan file <code>payment_pending.php</code> bisa diakses dari
                        internet ketika sudah di-upload ke hosting produksi.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Riwayat pembayaran -->
    <div class="card bg-secondary-subtle text-dark">
        <div class="card-header bg-secondary text-light d-flex justify-content-between">
            <span><strong>Riwayat Pembayaran Terbaru</strong></span>
            <span class="small">
                Sumber data: tabel <code>orders</code>
            </span>
        </div>
        <div class="card-body p-0">
            <?php if (!$payments): ?>
                <p class="m-3 small mb-3">Belum ada transaksi yang terhubung dengan Midtrans.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0 align-middle">
                        <thead class="table-dark">
                        <tr>
                            <th>#Order</th>
                            <th>Midtrans Order ID</th>
                            <th>Total</th>
                            <th>Status Pembayaran</th>
                            <th>Dibuat</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($payments as $p): ?>
                            <tr>
                                <td>#<?= htmlspecialchars($p['id']) ?></td>
                                <td class="small"><?= htmlspecialchars($p['midtrans_order_id']) ?></td>
                                <td>
                                    <?= rupiah($p['total_price']) ?>
                                </td>
                                <td>
                                    <?php
                                    $status = $p['payment_status'] ?: 'pending';
                                    $badgeClass = match ($status) {
                                        'settlement', 'paid' => 'bg-success',
                                        'cancel', 'cancelled' => 'bg-danger',
                                        'expire' => 'bg-warning',
                                        default => 'bg-secondary'
                                    };
                                    ?>
                                    <span class="badge <?= $badgeClass ?>">
                                        <?= htmlspecialchars($status) ?>
                                    </span>
                                </td>
                                <td class="small">
                                    <?= htmlspecialchars($p['created_at']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
