<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/_guard.php';

if ($_SESSION['role'] !== 'admin') {
    die('Akses ditolak');
}

$orderId = (int)($_GET['id'] ?? 0);
if (!$orderId) {
    die('Order tidak valid');
}

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

/* =====================
   GENERATE CODE
===================== */
function generate_redeem_code(string $platformSlug): string {
    $prefix = strtoupper(substr($platformSlug ?: 'GAME', 0, 4));
    $chars  = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $code   = '';
    for ($i = 0; $i < 16; $i++) {
        $code .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $prefix . '-' .
           substr($code,0,4) . '-' .
           substr($code,4,4) . '-' .
           substr($code,8,4) . '-' .
           substr($code,12,4);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate'])) {
    $insert = $pdo->prepare("
        INSERT INTO redeem_codes (order_item_id, platform_id, code)
        VALUES (?, ?, ?)
    ");

    foreach ($items as $item) {
        if ($item['code']) continue;

        for ($i = 0; $i < $item['quantity']; $i++) {
            $code = generate_redeem_code($item['platform_slug']);
            $insert->execute([
                $item['order_item_id'],
                $item['platform_id'],
                $code
            ]);
        }
    }

    header("Location: order_detail.php?id=$orderId");
    exit;
}

include __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
  <h2>Admin · Order #<?= $orderId ?></h2>

  <form method="post" class="mb-3">
    <button name="generate" class="btn btn-warning">
      Generate Redeem Code
    </button>
  </form>

  <table class="table table-dark table-striped">
    <thead>
      <tr>
        <th>Produk</th>
        <th>Platform</th>
        <th>Redeem Code</th>
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
            <span class="badge bg-warning text-dark">Belum tersedia</span>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
