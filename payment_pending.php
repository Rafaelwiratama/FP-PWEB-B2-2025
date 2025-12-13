<?php
require_once __DIR__ . '/config/config.php';

$orderId = (int)($_GET['order_id'] ?? 0);

if ($orderId) {
    $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'pending' WHERE id = ?");
    $stmt->execute([$orderId]);
}

include __DIR__ . '/includes/header.php';
?>

<main class="container" style="padding-top:140px; padding-bottom:60px;">
  <h2>Pembayaran Menunggu</h2>
  <p>Pembayaran untuk order <strong>#<?= $orderId ?></strong> masih menunggu (pending).</p>
  <p>Silakan cek instruksi pembayaran di halaman Midtrans.</p>
  <a href="index.php" class="btn btn-primary mt-3">Kembali ke Beranda</a>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
