<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/helpers.php';


$featuredGames = $pdo->query("
    SELECT * FROM products
    WHERE is_featured = 1
    ORDER BY created_at DESC
")->fetchAll();

$bestDeals = $pdo->query("
    SELECT * FROM products
    WHERE is_best_deal = 1
    ORDER BY created_at DESC
")->fetchAll();

$bestsellerGames = $pdo->query("
    SELECT * FROM products
    WHERE is_bestseller = 1
    ORDER BY created_at DESC
")->fetchAll();

$trendingGames = $pdo->query("
    SELECT * FROM products
    WHERE is_trending = 1
")->fetchAll();

$upcomingGames = $pdo->query("
    SELECT * FROM products
    WHERE is_upcoming = 1
")->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<!-- ================= HERO ================= -->
<section class="gv-hero">

  <div id="featuredCarousel"
       class="carousel slide"
       data-bs-ride="carousel"
       data-bs-interval="6000">

    <div class="carousel-inner">

      <?php foreach ($featuredGames as $i => $game): ?>
        <div class="carousel-item <?= $i === 0 ? 'active' : '' ?> gv-hero-item">

          <!-- IMAGE -->
          <img
            src="assets/images/<?= htmlspecialchars($game['image']) ?>"
            class="gv-hero-img"
            alt="<?= htmlspecialchars($game['title']) ?>">

          <!-- OVERLAY -->
          <div class="gv-hero-overlay"></div>

          <!-- TEXT -->
          <div class="container gv-hero-content">
            <div class="row">
              <div class="col-lg-6">

                <p class="gv-hero-kicker">FEATURED TODAY</p>

                <h1 class="gv-hero-title">
                  <?= htmlspecialchars($game['title']) ?>
                </h1>

                <p class="gv-hero-desc">
                  <?= htmlspecialchars(mb_strimwidth($game['description'], 0, 160, '...')) ?>
                </p>

                <div class="d-flex gap-3 mt-4">
                  <span class="gv-price-tag">
                    <?= rupiah(discounted_price($game)) ?>
                  </span>

                  <a href="product.php?slug=<?= urlencode($game['slug']) ?>"
                     class="btn gv-btn-primary px-4">
                    Buy Now
                  </a>
                </div>

              </div>
            </div>
          </div>

        </div>
      <?php endforeach; ?>

    </div>

    <!-- NAV -->
    <button class="carousel-control-prev" type="button"
            data-bs-target="#featuredCarousel" data-bs-slide="prev">
      <span class="carousel-control-prev-icon"></span>
    </button>

    <button class="carousel-control-next" type="button"
            data-bs-target="#featuredCarousel" data-bs-slide="next">
      <span class="carousel-control-next-icon"></span>
    </button>

  </div>

  <!-- DOT INDICATOR -->
  <div class="carousel-indicators gv-hero-dots">
    <?php foreach ($featuredGames as $i => $game): ?>
      <button
        type="button"
        data-bs-target="#featuredCarousel"
        data-bs-slide-to="<?= $i ?>"
        class="<?= $i === 0 ? 'active' : '' ?>">
      </button>
    <?php endforeach; ?>
  </div>

</section>

<!-- ================= BEST DEALS ================= -->
<section class="gv-section">
  <div class="container">
    <h2 class="gv-section-title">Best Deals</h2>

    <div class="row g-4">
      <?php foreach ($bestDeals as $g): ?>
        <div class="col-md-4">
          <a href="product.php?slug=<?= urlencode($g['slug']) ?>"
             class="text-decoration-none text-light">
            <article class="gv-game-card">
              <div class="gv-game-cover"
                   style="background-image:url('assets/images/<?= htmlspecialchars($g['image']) ?>')"
                   aria-label="<?= htmlspecialchars($g['title']) ?>"></div>
              <div class="gv-game-body">
                <h3><?= htmlspecialchars($g['title']) ?></h3>
                <span class="gv-price-current">
                  <?= rupiah(discounted_price($g)) ?>
                </span>
              </div>
            </article>
          </a>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>

<!-- ================= TRENDING ================= -->
<section class="gv-section gv-section-dark">
  <div class="container">
    <h2 class="gv-section-title">Trending</h2>

    <div class="row g-4">
      <?php if ($trendingGames): foreach ($trendingGames as $g): ?>
        <div class="col-md-4">
          <a href="product.php?slug=<?= urlencode($g['slug']) ?>"
             class="text-decoration-none text-light">
            <article class="gv-game-card">
              <div class="gv-game-cover"
                   style="background-image:url('assets/images/<?= htmlspecialchars($g['image']) ?>')"
                   aria-label="<?= htmlspecialchars($g['title']) ?>"></div>
              <div class="gv-game-body">
                <h3><?= htmlspecialchars($g['title']) ?></h3>
                <span class="gv-price-current">
                  <?= rupiah(discounted_price($g)) ?>
                </span>
              </div>
            </article>
          </a>
        </div>
      <?php endforeach; else: ?>
        <p class="text-muted">Belum ada game Trending.</p>
      <?php endif; ?>
    </div>

  </div>
</section>

<!-- ======================
     BEST SELLER
====================== -->
<section class="gv-section">
  <div class="container">
    <h2 class="gv-section-title">Best Seller</h2>

    <div class="row g-4">
      <?php if ($bestsellerGames): foreach ($bestsellerGames as $g): ?>
        <div class="col-md-4">
          <a href="product.php?slug=<?= urlencode($g['slug']) ?>"
             class="text-decoration-none text-light">

            <article class="gv-game-card">
              <div class="gv-game-cover"
                   style="background-image:url('assets/images/<?= htmlspecialchars($g['image']) ?>')">
              </div>

              <div class="gv-game-body">
                <h3><?= htmlspecialchars($g['title']) ?></h3>
                <span class="gv-price-current">
                  <?= rupiah(discounted_price($g)) ?>
                </span>
              </div>
            </article>

          </a>
        </div>
      <?php endforeach; else: ?>
        <p class="text-muted">Belum ada Best Seller.</p>
      <?php endif; ?>
    </div>
  </div>
</section>


<!-- ================= UPCOMING ================= -->
<section class="gv-section">
  <div class="container">
    <h2 class="gv-section-title">Upcoming Games</h2>

    <div class="row g-4">
      <?php foreach ($upcomingGames as $g): ?>
        <div class="col-md-4">
          <article class="gv-game-card">
            <div class="gv-game-cover"
                 style="background-image:url('assets/images/<?= htmlspecialchars($g['image']) ?>')">
            </div>
            <div class="gv-game-body text-center">
              <h3><?= htmlspecialchars($g['title']) ?></h3>
              <span class="badge bg-warning text-dark">Coming Soon</span>
            </div>
          </article>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- ================= NEWS ================= -->
<section class="gv-section gv-section-dark">
  <div class="container">
    <h2 class="gv-section-title mb-4 text-white">Game News</h2>

    <div class="row g-4">
      <?php
      $newsJson = file_get_contents("http://localhost/Slashers.com/news_api.php");
      $news = json_decode($newsJson, true);
      ?>

      <?php if (!empty($news)): ?>
        <?php foreach (array_slice($news, 0, 6) as $n): ?>
          <div class="col-md-4">
            <div class="card bg-secondary text-white h-100 shadow">
              <img src="<?= htmlspecialchars($n['image']) ?>"
                   class="card-img-top"
                   style="height:200px;object-fit:cover">

              <div class="card-body">
                <h5 class="card-title text-white">
                  <?= htmlspecialchars($n['title']) ?>
                </h5>

                <p class="small text-light">
                  <?= htmlspecialchars($n['source']) ?>
                </p>

                <a href="<?= htmlspecialchars($n['url']) ?>"
                   target="_blank"
                   class="btn btn-warning btn-sm">
                  Baca Berita
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="text-warning">Berita belum tersedia.</p>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
