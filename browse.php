<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/helpers.php';

/* =========================
       SEARCH QUERY
========================= */
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

    $stmt->execute([
        '%' . $q . '%', // filter
        $q . '%',       
        '% ' . $q . '%' 
    ]);
} else {
    $stmt = $pdo->query("
        SELECT *
        FROM products
        ORDER BY created_at DESC
    ");
}

$products = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<main class="gv-section gv-product-page" id="browse">
  <div class="container">

    <h2 class="gv-section-title mb-4">
      <?= $q ? 'Hasil Pencarian: "' . htmlspecialchars($q) . '"' : 'Browse Games' ?>
    </h2>

    <?php if (!$products): ?>
      <p class="text-muted">
        Tidak ada game ditemukan untuk "<strong><?= htmlspecialchars($q) ?></strong>"
      </p>
    <?php else: ?>
      <div class="row g-4">

        <?php foreach ($products as $p): ?>
          <div class="col-md-4">

            <a href="product.php?slug=<?= urlencode($p['slug']) ?>"
               class="text-decoration-none text-light">

              <article class="gv-game-card h-100">

                <!-- IMAGE -->
                <div class="gv-game-cover"
                     style="background-image:url('assets/images/<?= htmlspecialchars($p['image']) ?>')">
                </div>

                <!-- BODY -->
                <div class="gv-game-body">

                  <h3 class="gv-game-title mb-2">
                    <?= htmlspecialchars($p['title']) ?>
                  </h3>

                  <!-- BADGES -->
                  <div class="mb-2">
                    <?php if ($p['is_featured']): ?>
                      <span class="badge bg-info text-dark me-1">Featured</span>
                    <?php endif; ?>
                    <?php if ($p['is_best_deal']): ?>
                      <span class="badge bg-success me-1">Best Deal</span>
                    <?php endif; ?>
                    <?php if ($p['is_bestseller']): ?>
                      <span class="badge bg-warning text-dark me-1">Bestseller</span>
                    <?php endif; ?>
                  </div>

                  <!-- PRICE -->
                  <div class="gv-game-price-line mb-3">
                    <?php if ((int)$p['discount_percent'] > 0): ?>
                      <?php
                        $old = (int)$p['price'];
                        $new = $old - ($old * $p['discount_percent'] / 100);
                      ?>
                      <span class="badge bg-danger me-2">
                        -<?= (int)$p['discount_percent'] ?>%
                      </span>
                      <span class="text-muted text-decoration-line-through me-2">
                        <?= rupiah($old) ?>
                      </span>
                      <span class="gv-price-current">
                        <?= rupiah($new) ?>
                      </span>
                    <?php else: ?>
                      <span class="gv-price-current">
                        <?= rupiah((int)$p['price']) ?>
                      </span>
                    <?php endif; ?>
                  </div>

                  <!-- ACTION -->
                  <form method="post" action="cart.php">
                    <input type="hidden" name="add_product_id" value="<?= $p['id'] ?>">
                    <button type="submit"
                            class="btn btn-sm btn-outline-light w-100">
                      + Tambah ke Keranjang
                    </button>
                  </form>

                </div>
              </article>

            </a>

          </div>
        <?php endforeach; ?>

      </div>
    <?php endif; ?>

  </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
