<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/../config/midtrans_config.php';

$phpVersion = PHP_VERSION;

// cek koneksi database
$dbOk = true;
$dbMessage = 'OK';
try {
    $pdo->query('SELECT 1');
} catch (Throwable $e) {
    $dbOk = false;
    $dbMessage = $e->getMessage();
}

$midtransConfigured = !empty($MIDTRANS_SERVER_KEY) && !empty($MIDTRANS_CLIENT_KEY);

$pendingFile   = realpath(__DIR__ . '/../payment_pending.php');
$successFile   = realpath(__DIR__ . '/../payment_success.php');
$midtransConf  = realpath(__DIR__ . '/../config/midtrans_config.php');

function status_badge(bool $ok): string
{
    return $ok ? '<span class="badge bg-success">OK</span>' :
                 '<span class="badge bg-danger">Problem</span>';
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Admin · System Status - Slasher.com</title>
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
        <h4 class="mb-0">System Status & Monitoring</h4>
    </div>

    <div class="row g-4 mb-4">
        <!-- Server info -->
        <div class="col-lg-4">
            <div class="card bg-secondary-subtle text-dark h-100">
                <div class="card-header bg-secondary text-light">
                    <strong>Server & PHP</strong>
                </div>
                <div class="card-body small">
                    <p class="mb-1">
                        <span class="fw-semibold">PHP Version:</span>
                        <span class="ms-2"><?= htmlspecialchars($phpVersion) ?></span>
                    </p>
                    <p class="mb-1">
                        <span class="fw-semibold">Server Software:</span>
                        <span class="ms-2"><?= htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? '-') ?></span>
                    </p>
                    <p class="mb-1">
                        <span class="fw-semibold">Document Root:</span>
                        <span class="ms-2"><?= htmlspecialchars($_SERVER['DOCUMENT_ROOT'] ?? '-') ?></span>
                    </p>
                    <p class="mb-0 text-muted">
                        Untuk deployment produksi, pastikan versi PHP di hosting kompatibel dengan proyek ini.
                    </p>
                </div>
            </div>
        </div>

        <!-- Database -->
        <div class="col-lg-4">
            <div class="card bg-secondary-subtle text-dark h-100">
                <div class="card-header bg-secondary text-light">
                    <strong>Database Connection</strong>
                </div>
                <div class="card-body small">
                    <p class="mb-2">
                        Status: <?= $dbOk ? status_badge(true) : status_badge(false) ?>
                    </p>
                    <p class="mb-1">
                        Host: <code><?= htmlspecialchars($host ?? 'localhost') ?></code>
                    </p>
                    <p class="mb-1">
                        Database: <code><?= htmlspecialchars($db ?? 'slasher_db') ?></code>
                    </p>
                    <?php if (!$dbOk): ?>
                        <div class="alert alert-danger mt-2 small mb-0">
                            Error: <?= htmlspecialchars($dbMessage) ?>
                        </div>
                    <?php else: ?>
                        <p class="mt-2 mb-0 text-muted">
                            Koneksi database berjalan normal.
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Midtrans status -->
        <div class="col-lg-4">
            <div class="card bg-secondary-subtle text-dark h-100">
                <div class="card-header bg-secondary text-light">
                    <strong>Midtrans Integration</strong>
                </div>
                <div class="card-body small">
                    <p class="mb-2">
                        Status konfigurasi:
                        <?= $midtransConfigured ? status_badge(true) : status_badge(false) ?>
                    </p>

                    <p class="mb-1">
                        Environment:
                        <span class="badge bg-info ms-1">
                            <?= $MIDTRANS_IS_PRODUCTION ? 'Production' : 'Sandbox' ?>
                        </span>
                    </p>
                    <p class="mb-1">
                        Snap Endpoint:
                        <code><?= htmlspecialchars($MIDTRANS_SNAP_BASE_URL) ?></code>
                    </p>
                    <p class="mb-1">
                        Snap JS:
                        <code><?= htmlspecialchars($MIDTRANS_SNAP_JS_URL) ?></code>
                    </p>

                    <p class="mt-2 mb-0 text-muted">
                        Untuk tes end-to-end, lakukan transaksi di website lalu cek log
                        di halaman <em>Payment & Integration</em> dan dashboard Midtrans.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="card bg-secondary-subtle text-dark mb-4">
        <div class="card-header bg-secondary text-light">
            <strong>File & Callback Check</strong>
        </div>
        <div class="card-body small">
            <div class="row">
                <div class="col-md-4">
                    <p class="fw-semibold mb-1">File Penting</p>
                    <ul class="list-unstyled mb-0">
                        <li>
                            payment_pending.php:
                            <?= status_badge($pendingFile && is_readable($pendingFile)) ?>
                        </li>
                        <li>
                            payment_success.php:
                            <?= status_badge($successFile && is_readable($successFile)) ?>
                        </li>
                        <li>
                            config/midtrans_config.php:
                            <?= status_badge($midtransConf && is_readable($midtransConf)) ?>
                        </li>
                    </ul>
                </div>
                <div class="col-md-8">
                    <p class="fw-semibold mb-1">Contoh Callback URL Produksi</p>
                    <code class="d-block mb-2">
                        https://slashers.com/payment_pending.php
                    </code>
                    <p class="mb-0 text-muted">
                        Pastikan domain <strong>slashers.com</strong> sudah mengarah
                        ke folder proyek ini di hosting, dan URL di atas sudah
                        diset di dashboard Midtrans.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Catatan -->
    <div class="card bg-secondary-subtle text-dark">
        <div class="card-header bg-secondary text-light">
            <strong>Catatan untuk Deployment</strong>
        </div>
        <div class="card-body small">
            <ul class="mb-0">
                <li>Gunakan <strong>environment Sandbox</strong> untuk pengujian di localhost.</li>
                <li>Saat go-live, ubah konfigurasi Midtrans ke <strong>Production</strong> dan perbarui
                    <code>$MIDTRANS_SERVER_KEY</code> dan <code>$MIDTRANS_CLIENT_KEY</code>.
                </li>
                <li>Pastikan file konfigurasi tidak di-commit ke repo publik (GitHub, dsb.).</li>
            </ul>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
