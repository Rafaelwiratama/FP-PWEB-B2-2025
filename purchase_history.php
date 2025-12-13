<?php
require_once __DIR__ . '/config/config.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$stmt = $pdo->prepare("
  SELECT o.id, o.created_at, o.total_price, o.payment_status,
         GROUP_CONCAT(p.title SEPARATOR ', ') AS products
  FROM orders o
  JOIN order_items oi ON oi.order_id = o.id
  JOIN products p ON p.id = oi.product_id
  WHERE o.user_id = ?
    AND (o.payment_status = 'settlement' OR o.payment_status = 'paid')
  GROUP BY o.id
  ORDER BY o.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$rows = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<main class="container" style="padding-top:140px; padding-bottom:60px;">
  <h2 class="mb-4">Purchase History</h2>

  <?php if (!$rows): ?>
    <p>Belum ada pembayaran yang berhasil.</p>
  <?php else: ?>
    <div class="list-group">
      <?php foreach ($rows as $r): ?>
        <div class="list-group-item bg-dark text-light mb-2 border-secondary">
          <div class="d-flex justify-content-between">
            <div>
              <div class="small text-muted"><?= htmlspecialchars($r['created_at']) ?></div>
              <strong>Order #<?= htmlspecialchars($r['id']) ?></strong>
              <div class="small mt-1">
                Produk: <?= htmlspecialchars($r['products']) ?>
              </div>
            </div>
            <div class="text-end">
              <div><?= rupiah($r['total_price']) ?></div>
              <span class="badge bg-success">paid</span>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
