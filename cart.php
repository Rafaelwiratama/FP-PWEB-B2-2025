<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/helpers.php';


// ADD PRODUCT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product_id'])) {
    $pid = (int)$_POST['add_product_id'];
    $qty = (int)($_POST['qty'] ?? 1);
    $stmt = $pdo->prepare("SELECT is_upcoming, price FROM products WHERE id = ?");
$stmt->execute([$pid]);
$product = $stmt->fetch();

if (!$product || $product['is_upcoming'] || $product['price'] <= 0) {
    die('Produk belum tersedia untuk dibeli');
}

    if ($qty < 1) $qty = 1;

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    if (!isset($_SESSION['cart'][$pid])) {
        $_SESSION['cart'][$pid] = 0;
    }
    $_SESSION['cart'][$pid] += $qty;

    header('Location: cart.php');
    exit;
}

// UPDATE QTY
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    if (isset($_POST['qty']) && is_array($_POST['qty'])) {
        foreach ($_POST['qty'] as $pid => $q) {
            $pid = (int)$pid;
            $q   = (int)$q;
            if ($q <= 0) {
                unset($_SESSION['cart'][$pid]);
            } else {
                $_SESSION['cart'][$pid] = $q;
            }
        }
    }
    header('Location: cart.php');
    exit;
}

// REMOVE SINGLE ITEM
if (isset($_GET['remove'])) {
    $pid = (int)$_GET['remove'];
    unset($_SESSION['cart'][$pid]);
    header('Location: cart.php');
    exit;
}

$items = [];
$total = 0;

if (!empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $in  = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($in)");
    $stmt->execute($ids);
    $items = $stmt->fetchAll();

    foreach ($items as &$it) {
        $qty = $_SESSION['cart'][$it['id']];
        $price = $it['price'];
        if ($it['discount_percent'] > 0) {
            $price = $price - ($price * $it['discount_percent'] / 100);
        }
        $it['qty']   = $qty;
        $it['price_final'] = $price;
        $it['sub']   = $qty * $price;
        $total      += $it['sub'];
    }
}

include __DIR__ . '/includes/header.php';
?>

<main class="container" style="padding-top:140px; padding-bottom:60px;">
  <h2 class="mb-4">Keranjang Belanja</h2>

  <?php if (!$items): ?>
    <p>Keranjang masih kosong. <a href="browse.php">Mulai belanja</a></p>
  <?php else: ?>
    <form method="post">
      <input type="hidden" name="update_cart" value="1">
      <div class="table-responsive mb-3">
        <table class="table table-dark table-striped align-middle">
          <thead>
            <tr>
              <th>Produk</th>
              <th style="width:120px;">Qty</th>
              <th>Harga</th>
              <th>Subtotal</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $it): ?>
              <tr>
                <td><?= htmlspecialchars($it['title']) ?></td>
                <td>
                  <input type="number" name="qty[<?= $it['id'] ?>]" class="form-control form-control-sm"
                         value="<?= $it['qty'] ?>" min="0">
                </td>
                <td><?= rupiah($it['price_final']) ?></td>
                <td><?= rupiah($it['sub']) ?></td>
                <td>
                  <a href="cart.php?remove=<?= $it['id'] ?>" class="btn btn-sm btn-danger">Hapus</a>
                </td>
              </tr>
            <?php endforeach; ?>
            <tr>
              <th colspan="3" class="text-end">Total</th>
              <th><?= rupiah($total) ?></th>
              <th></th>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="d-flex justify-content-between">
        <button type="submit" class="btn btn-outline-light">Update Keranjang</button>
        <a href="checkout.php" class="btn btn-primary">Checkout</a>
      </div>
    </form>
  <?php endif; ?>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
