<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/midtrans_config.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];

$stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmtUser->execute([$userId]);
$user = $stmtUser->fetch();
if (!$user) {
    die('User tidak ditemukan.');
}

$productIds = array_keys($_SESSION['cart']);
$inQuery    = implode(',', array_fill(0, count($productIds), '?'));

$stmtProd = $pdo->prepare("SELECT * FROM products WHERE id IN ($inQuery)");
$stmtProd->execute($productIds);
$products = $stmtProd->fetchAll();
$productsById = [];
foreach ($products as $p) {
    $productsById[$p['id']] = $p;
}

$total         = 0;
$itemDetails   = [];
$productPlatforms = []; 

$stmtPf = $pdo->prepare("
    SELECT pp.product_id, pf.id, pf.name
    FROM product_platforms pp
    JOIN platforms pf ON pp.platform_id = pf.id
    WHERE pp.product_id IN ($inQuery)
    ORDER BY pf.name
");
$stmtPf->execute($productIds);
while ($row = $stmtPf->fetch()) {
    $productPlatforms[$row['product_id']][] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  
    $platformPost = $_POST['platform'] ?? [];
    foreach ($productIds as $pid) {
        if (empty($platformPost[$pid])) {
            $error = 'Semua produk harus memilih platform.';
            break;
        }
    }

    if (!isset($error)) {
        foreach ($productIds as $pid) {
            if (!isset($productsById[$pid])) continue;

            $prod  = $productsById[$pid];
            $qty   = (int)($_SESSION['cart'][$pid] ?? 1);
            if ($qty < 1) $qty = 1;

            $price = (int)$prod['price'];
            if ($prod['discount_percent'] > 0) {
                $price -= (int)round($price * $prod['discount_percent'] / 100);
            }
            $subtotal = $price * $qty;
            $total   += $subtotal;

            $platformId = (int)$platformPost[$pid];

            $itemDetails[] = [
                'id'       => (string)$pid,
                'price'    => $price,
                'quantity' => $qty,
                'name'     => substr($prod['title'], 0, 50),
            ];
        }

        $midtransOrderId = 'SLASHER-' . time() . '-' . rand(100,999);

        $pdo->beginTransaction();

        $stmtOrder = $pdo->prepare("
            INSERT INTO orders (user_id, total_price, status, payment_status, midtrans_order_id)
            VALUES (?, ?, 'pending', 'pending', ?)
        ");
        $stmtOrder->execute([$userId, $total, $midtransOrderId]);
        $orderId = $pdo->lastInsertId();

        $stmtItem = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, platform_id, quantity, unit_price)
            VALUES (?, ?, ?, ?, ?)
        ");

        foreach ($productIds as $pid) {
            if (!isset($productsById[$pid])) continue;

            $prod  = $productsById[$pid];
            $qty   = (int)($_SESSION['cart'][$pid] ?? 1);
            if ($qty < 1) $qty = 1;

            $price = (int)$prod['price'];
            if ($prod['discount_percent'] > 0) {
                $price -= (int)round($price * $prod['discount_percent'] / 100);
            }

            $platformId = (int)$platformPost[$pid];

            $stmtItem->execute([
                $orderId,
                $pid,
                $platformId,
                $qty,
                $price
            ]);
        }

        $payload = [
            'transaction_details' => [
                'order_id'      => $midtransOrderId,
                'gross_amount'  => (int)$total,
            ],
            'customer_details' => [
                'first_name' => $user['name'],
                'email'      => $user['email'],
            ],
            'item_details' => $itemDetails,
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $MIDTRANS_SNAP_BASE_URL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Basic ' . base64_encode($MIDTRANS_SERVER_KEY . ':'),
            ],
            CURLOPT_POSTFIELDS     => json_encode($payload),
        ]);

        $response = curl_exec($ch);
        if ($response === false) {
            $pdo->rollBack();
            die('Gagal menghubungi Midtrans: ' . curl_error($ch));
        }
        curl_close($ch);

        $result = json_decode($response, true);
        if (empty($result['token'])) {
            $pdo->rollBack();
            echo '<pre>';
            echo "Gagal mendapatkan Snap Token dari Midtrans.\n";
            print_r($result);
            echo '</pre>';
            exit;
        }

        $snapToken = $result['token'];

        $stmtUpd = $pdo->prepare("UPDATE orders SET snap_token = ? WHERE id = ?");
        $stmtUpd->execute([$snapToken, $orderId]);

        $pdo->commit();

        // kosongkan cart
        $_SESSION['cart'] = [];

        include __DIR__ . '/includes/header.php';
        ?>
        <main class="container" style="padding-top:140px; padding-bottom:60px;">
          <h2>Proses Pembayaran</h2>
          <p>Silakan selesaikan pembayaran di popup Midtrans.</p>
          <button id="pay-button" class="btn btn-primary mt-3">Bayar Sekarang</button>
        </main>

        <script src="<?= $MIDTRANS_SNAP_JS_URL ?>" data-client-key="<?= $MIDTRANS_CLIENT_KEY ?>"></script>
        <script>
          document.getElementById('pay-button').addEventListener('click', function () {
              snap.pay('<?= $snapToken ?>', {
                  onSuccess: function (result) {
                      window.location.href = 'payment_success.php?order_id=<?= $orderId ?>';
                  },
                  onPending: function (result) {
                      window.location.href = 'payment_pending.php?order_id=<?= $orderId ?>';
                  },
                  onError: function (result) {
                      alert('Terjadi error saat pembayaran.');
                  },
                  onClose: function () {
                      alert('Kamu menutup popup tanpa menyelesaikan pembayaran.');
                  }
              });
          });
        </script>
        <?php
        include __DIR__ . '/includes/footer.php';
        exit;
    }
}

include __DIR__ . '/includes/header.php';

function rupiah_local($n) {
    return 'Rp ' . number_format($n, 0, ',', '.');
}
?>

<main class="container" style="padding-top:140px; padding-bottom:60px;">
  <h2 class="mb-4">Checkout</h2>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="post">
    <div class="table-responsive">
      <table class="table table-dark align-middle">
        <thead>
          <tr>
            <th>Game</th>
            <th>Platform</th>
            <th>Qty</th>
            <th>Harga</th>
            <th>Subtotal</th>
          </tr>
        </thead>
        <tbody>
        <?php
          $grand = 0;
          foreach ($productIds as $pid):
              if (!isset($productsById[$pid])) continue;
              $prod = $productsById[$pid];
              $qty  = (int)($_SESSION['cart'][$pid] ?? 1);
              if ($qty < 1) $qty = 1;
              $price = (int)$prod['price'];
              if ($prod['discount_percent'] > 0) {
                  $price -= (int)round($price * $prod['discount_percent'] / 100);
              }
              $subtotal = $price * $qty;
              $grand   += $subtotal;
        ?>
          <tr>
            <td><?= htmlspecialchars($prod['title']) ?></td>
            <td style="max-width:200px;">
              <?php
              $availablePlatforms = $productPlatforms[$pid] ?? [];
              ?>
              <select name="platform[<?= $pid ?>]" class="form-select form-select-sm" required>
                <option value="">Pilih platform</option>
                <?php foreach ($availablePlatforms as $pf): ?>
                  <option value="<?= $pf['id'] ?>">
                    <?= htmlspecialchars($pf['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </td>
            <td><?= $qty ?></td>
            <td><?= rupiah_local($price) ?></td>
            <td><?= rupiah_local($subtotal) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr>
            <th colspan="4" class="text-end">Total</th>
            <th><?= rupiah_local($grand) ?></th>
          </tr>
        </tfoot>
      </table>
    </div>

    <button type="submit" class="btn btn-primary mt-3">Lanjut ke Pembayaran</button>
  </form>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
