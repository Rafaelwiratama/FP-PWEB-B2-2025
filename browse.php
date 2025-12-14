<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/helpers.php';

$q = trim($_GET['q'] ?? '');

if ($q !== '') {
    $stmt = $pdo->prepare("
        SELECT *
        FROM products
        WHERE title LIKE ?
        ORDER BY
          CASE
            WHEN title LIKE ? THEN 3
            WHEN title LIKE ? THEN 2
            ELSE 1
          END DESC,
          title ASC
    ");
    $stmt->execute(['%' . $q . '%', $q . '%', '% ' . $q . '%']);
} else {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
}

$products = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<main class="gv-section gv-product-page" id="browse">
  <div class="container">

    <?php if (!empty($_SESSION['error'])): ?>
      <div class="alert alert-warning">
        <?= htmlspecialchars($_SESSION['error']) ?>
      </div>
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <h2 class="gv-section-title mb-4">
      <?= $q ? 'Hasil Pencarian: "' . htmlspecialchars($q) . '"' : 'Browse Games' ?>
    </h2>

    <div class="row g-4">
      <?php foreach ($products as $p): ?>
        <div class="col-md-4">

          <a href="product.php?slug=<?= urlencode($p['slug']) ?>"
             class="text-decoration-none text-light">

            <article class="gv-game-card h-100">

              <div class="gv-game-cover"
                   style="background-image:url('assets/images/<?= htmlspecialchars($p['image']) ?>')">
              </div>

              <div class="gv-game-body">
                <h3><?= htmlspecialchars($p['title']) ?></h3>

                <!-- PRICE -->
                <?php if ($p['discount_percent'] > 0): ?>
                  <span class="gv-price-current">
                    <?= rupiah(discounted_price($p)) ?>
                  </span>
                <?php else: ?>
                  <span class="gv-price-current">
                    <?= rupiah($p['price']) ?>
                  </span>
                <?php endif; ?>

                <!-- ACTION -->
                <?php if ($p['is_upcoming'] || $p['price'] <= 0): ?>
                  <span class="badge bg-warning text-dark mt-2">
                    Coming Soon
                  </span>
                <?php else: ?>
                  <form method="post" action="cart.php"
                        onClick="event.stopPropagation();"
                        class="mt-2">
                    <input type="hidden" name="add_product_id" value="<?= $p['id'] ?>">
                    <button type="submit"
                            class="btn btn-sm btn-outline-light w-100">
                      + Tambah ke Keranjang
                    </button>
                  </form>
                <?php endif; ?>

              </div>
            </article>

          </a>

        </div>
      <?php endforeach; ?>
    </div>

  </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
