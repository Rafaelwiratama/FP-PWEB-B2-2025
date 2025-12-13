<?php
require_once __DIR__ . '/config/config.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$orderId = (int)($_GET['order_id'] ?? 0);
if (!$orderId) {
    header('Location: index.php');
    exit;
}

// order
$stmtOrder = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmtOrder->execute([$orderId]);
$order = $stmtOrder->fetch();

if (!$order || $order['user_id'] != $_SESSION['user_id']) {
    die('Order tidak ditemukan.');
}

// generate kode redeem
function generate_redeem_code(string $platformSlug): string {
    $prefix = strtoupper(substr($platformSlug ?: 'GAME', 0, 4));
    $chars  = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $code   = '';
    for ($i = 0; $i < 16; $i++) {
        $code .= $chars[random_int(0, strlen($chars) - 1)];
    }
    // format: PREFIX-XXXX-XXXX-XXXX-XXXX
    return $prefix . '-' . substr($code,0,4) . '-' . substr($code,4,4) . '-' . substr($code,8,4) . '-' . substr($code,12,4);
}

if ($order['status'] !== 'paid') {
    $upd = $pdo->prepare("UPDATE orders SET status = 'paid', payment_status = 'settlement' WHERE id = ?");
    $upd->execute([$orderId]);
}

$stmtItems = $pdo->prepare("
    SELECT oi.*, p.title,
           pf.slug AS platform_slug,
           pf.name AS platform_name
    FROM order_items oi
    JOIN products  p  ON oi.product_id = p.id
    JOIN platforms pf ON oi.platform_id = pf.id
    WHERE oi.order_id = ?
");
$stmtItems->execute([$orderId]);
$items = $stmtItems->fetchAll();

$insertCode = $pdo->prepare("
    INSERT INTO redeem_codes (order_item_id, platform_id, code)
    VALUES (?,?,?)
");

// generate kode 
foreach ($items as $item) {
    $check = $pdo->prepare("SELECT COUNT(*) FROM redeem_codes WHERE order_item_id = ?");
    $check->execute([$item['id']]);
    $already = (int)$check->fetchColumn();
    if ($already > 0) {
        continue;
    }

    $qty = (int)$item['quantity'];
    if ($qty < 1) $qty = 1;

    for ($i = 0; $i < $qty; $i++) {
        $code = generate_redeem_code($item['platform_slug'] ?? '');
        $insertCode->execute([
            $item['id'],
            $item['platform_id'],
            $code
        ]);
    }
}

include __DIR__ . '/includes/header.php';
?>

<main class="container" style="padding-top:140px; padding-bottom:60px;">
  <h2>Pembayaran Berhasil</h2>
  <p>Terima kasih! Pesanan kamu dengan ID <strong>#<?= $orderId ?></strong> sudah dibayar.</p>

  <h4 class="mt-4 mb-3">Kode Redeem Kamu</h4>

  <?php foreach ($items as $item): ?>
    <div class="card mb-3">
      <div class="card-body">
        <h5 class="card-title mb-1">
          <?= htmlspecialchars($item['title']) ?>
          <small class="text-muted">
            (<?= htmlspecialchars($item['platform_name']) ?>)
          </small>
        </h5>

        <?php
        $rcStmt = $pdo->prepare("
            SELECT code FROM redeem_codes
            WHERE order_item_id = ?
            ORDER BY id
        ");
        $rcStmt->execute([$item['id']]);
        $codes = $rcStmt->fetchAll();
        ?>

        <?php if ($codes): ?>
          <ul class="mb-0">
            <?php foreach ($codes as $rc): ?>
              <li><code><?= htmlspecialchars($rc['code']) ?></code></li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p class="mb-0 text-muted">Belum ada kode.</p>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>

  <a href="my_orders.php" class="btn btn-primary mt-3">Lihat Riwayat Pesanan</a>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
