<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/helpers.php';


$slug = trim($_GET['slug'] ?? '');

if ($slug === '') {
    http_response_code(404);
    echo "Produk tidak ditemukan.";
    exit;
}

/* =========================
        DATA PRODUK
========================= */
$stmt = $pdo->prepare("
    SELECT *
    FROM products
    WHERE slug = ?
    LIMIT 1
");
$stmt->execute([$slug]);
$product = $stmt->fetch();

if (!$product) {
    http_response_code(404);
    echo "Produk tidak ditemukan.";
    exit;
}

$screensStmt = $pdo->prepare("
  SELECT image FROM product_screenshots
  WHERE product_id = ?
");
$screensStmt->execute([$product['id']]);
$screenshots = $screensStmt->fetchAll();


/* =========================
      GENRE / KATEGORI
========================= */
$catStmt = $pdo->prepare("
    SELECT c.name, pc.is_primary
    FROM product_categories pc
    JOIN categories c ON c.id = pc.category_id
    WHERE pc.product_id = ?
    ORDER BY pc.is_primary DESC, c.name
");
$catStmt->execute([$product['id']]);
$categories = $catStmt->fetchAll();

/* =========================
       PLATFORM
========================= */
$pfStmt = $pdo->prepare("
    SELECT p.name
    FROM product_platforms pp
    JOIN platforms p ON p.id = pp.platform_id
    WHERE pp.product_id = ?
    ORDER BY p.name
");
$pfStmt->execute([$product['id']]);
$platforms = $pfStmt->fetchAll();

/* =========================
          HARGA
========================= */
$price = (int)$product['price'];
$discount = (int)$product['discount_percent'];
$finalPrice = $discount > 0
    ? $price - ($price * $discount / 100)
    : $price;

$relatedStmt = $pdo->prepare("
  SELECT DISTINCT p.*
  FROM products p
  JOIN product_categories pc ON pc.product_id = p.id
  WHERE pc.category_id IN (
    SELECT category_id FROM product_categories
    WHERE product_id = ?
  )
  AND p.id != ?
  LIMIT 4
");
$relatedStmt->execute([$product['id'], $product['id']]);
$relatedGames = $relatedStmt->fetchAll();


include __DIR__ . '/includes/header.php';
?>

<main class="container gv-product-page" style="padding-top:120px; padding-bottom:80px;">

  <div class="row g-5">

    <!-- ================= IMAGE ================= -->
    <div class="col-md-5">
      <div class="card bg-dark border-0 shadow">
        <img
          src="assets/images/<?= htmlspecialchars($product['image']) ?>"
          class="card-img-top"
          alt="<?= htmlspecialchars($product['title']) ?>"
          style="object-fit:cover;">
      </div>
    </div>
    
<?php if ($screenshots): ?>
<div id="screenshotCarousel" class="carousel slide mt-4" data-bs-ride="carousel">
  <div class="carousel-inner">

    <?php foreach ($screenshots as $i => $s): ?>
      <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
        <img
          src="assets/images/<?= htmlspecialchars($s['image']) ?>"
          class="d-block w-100 rounded"
          style="object-fit:cover; max-height:320px;">
      </div>
    <?php endforeach; ?>

  </div>

  <button class="carousel-control-prev" type="button"
          data-bs-target="#screenshotCarousel" data-bs-slide="prev">
    <span class="carousel-control-prev-icon"></span>
  </button>

  <button class="carousel-control-next" type="button"
          data-bs-target="#screenshotCarousel" data-bs-slide="next">
    <span class="carousel-control-next-icon"></span>
  </button>
</div>
<?php endif; ?>

    <!-- ================= INFO ================= -->
    <div class="col-md-7">

      <h1 class="fw-bold mb-2"><?= htmlspecialchars($product['title']) ?></h1>

<div class="mb-3">
  <?php if ($product['is_featured']): ?>
    <span class="badge bg-info text-dark me-1">Featured</span>
  <?php endif; ?>
  <?php if ($product['is_best_deal']): ?>
    <span class="badge bg-success me-1">Best Deal</span>
  <?php endif; ?>
  <?php if ($product['is_bestseller']): ?>
    <span class="badge bg-warning text-dark me-1">Bestseller</span>
  <?php endif; ?>
</div>


      <!-- GENRE -->
      <?php if ($categories): ?>
        <div class="mb-3">
          <?php foreach ($categories as $c): ?>
            <span class="badge <?= $c['is_primary'] ? 'bg-primary' : 'bg-secondary' ?> me-1">
              <?= htmlspecialchars($c['name']) ?>
            </span>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- PLATFORM -->
      <?php if ($platforms): ?>
        <p class="text-muted mb-3">
          Platform:
          <?php foreach ($platforms as $i => $p): ?>
            <?= htmlspecialchars($p['name']) ?><?= $i < count($platforms)-1 ? ', ' : '' ?>
          <?php endforeach; ?>
        </p>
      <?php endif; ?>

      <!-- PRICE -->
      <div class="mb-4">
        <?php if ($discount > 0): ?>
          <span class="badge bg-danger me-2">-<?= $discount ?>%</span>
          <span class="text-muted text-decoration-line-through me-2">
            <?= rupiah($price) ?>
          </span>
          <span class="fs-4 fw-bold text-success">
            <?= rupiah($finalPrice) ?>
          </span>
        <?php else: ?>
          <span class="fs-4 fw-bold text-success">
            <?= rupiah($price) ?>
          </span>
        <?php endif; ?>
      </div>

      <!-- BUY -->
     <?php if (!$product['is_upcoming'] && $product['price'] > 0): ?>
<form method="post" action="cart.php" class="d-flex align-items-center gap-3 mb-4">
  <input type="hidden" name="add_product_id" value="<?= $product['id'] ?>">
  <input type="number" name="qty" value="1" min="1"
         class="form-control" style="width:90px;">
  <button class="btn btn-primary btn-lg px-4">
    Buy Now
  </button>
</form>
<?php else: ?>
  <span class="badge bg-warning text-dark fs-6">
    Produk belum tersedia
  </span>
<?php endif; ?>

      <!-- DESCRIPTION -->
      <div class="text-muted lh-lg">
        <?= nl2br(htmlspecialchars($product['description'])) ?>
      </div>

    </div>
  </div>

<?php if ($relatedGames): ?>
<section class="mt-5">
  <h4 class="fw-bold mb-4">Related Games</h4>

  <div class="row g-4">
    <?php foreach ($relatedGames as $g): ?>
      <div class="col-md-3">
        <a href="product.php?slug=<?= urlencode($g['slug']) ?>"
           class="text-decoration-none text-light">
          <article class="gv-game-card h-100">
            <div class="gv-game-cover"
                 style="background-image:url('assets/images/<?= htmlspecialchars($g['image']) ?>')"></div>
            <div class="gv-game-body">
  <h3><?= htmlspecialchars($g['title']) ?></h3>

  <?php if (!empty($g['is_upcoming'])): ?>
      <span class="badge bg-warning text-dark">
          Coming Soon
      </span>
  <?php else: ?>
      <span class="gv-price-current">
          <?= rupiah(discounted_price($g)) ?>
      </span>
  <?php endif; ?>
            </div>
          </article>
        </a>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

