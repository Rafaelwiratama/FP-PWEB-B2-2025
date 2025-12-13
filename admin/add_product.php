<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/_guard.php'; 

// ambil kategori & platform
$categoriesStmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
$categories = $categoriesStmt->fetchAll();

$platformsStmt = $pdo->query("SELECT id, name FROM platforms ORDER BY name");
$platforms = $platformsStmt->fetchAll();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="container" style="padding-top:140px; padding-bottom:60px;">
  <a href="products.php" class="btn btn-outline-secondary btn-sm mb-3">&laquo; Kembali</a>

  <h2 class="mb-4">Tambah Produk</h2>

  <form action="process_add_product.php" method="post">
    <div class="mb-3">
      <label class="form-label">Kategori Utama</label>
      <select name="primary_category_id" class="form-select" required>
        <option value="">-- Pilih kategori --</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <div class="form-text">
        Kategori ini dipakai sebagai genre utama (untuk highlight di homepage).
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Kategori Tambahan (multi-genre)</label>
      <div class="row">
        <?php foreach ($categories as $cat): ?>
          <div class="col-md-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox"
                     name="extra_categories[]"
                     value="<?= $cat['id'] ?>"
                     id="cat<?= $cat['id'] ?>">
              <label class="form-check-label" for="cat<?= $cat['id'] ?>">
                <?= htmlspecialchars($cat['name']) ?>
              </label>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="form-text">
        Boleh kosong. Kategori utama otomatis ikut dimasukkan ke daftar multi-genre.
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Judul</label>
      <input type="text" name="title" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Slug (tanpa spasi, huruf kecil, pakai -)</label>
      <input type="text" name="slug" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Deskripsi</label>
      <textarea name="description" rows="6" class="form-control"></textarea>
    </div>

    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Harga (angka saja)</label>
        <input type="number" name="price" class="form-control" min="0" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Diskon (%)</label>
        <input type="number" name="discount_percent" class="form-control" min="0" max="100" value="0">
      </div>
      <div class="col-md-4">
        <label class="form-label">Nama file gambar</label>
        <input type="text" name="image" class="form-control" placeholder="misal: it-takes-two.jpg">
        <div class="form-text">Simpan file gambar ke folder <code>assets/images/</code>.</div>
      </div>
    </div>

    <hr class="my-4">

    <div class="mb-3">
      <label class="form-label">Platform tersedia</label>
      <div class="row">
        <?php foreach ($platforms as $pf): ?>
          <div class="col-md-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox"
                     name="platforms[]"
                     value="<?= $pf['id'] ?>"
                     id="pf<?= $pf['id'] ?>">
              <label class="form-check-label" for="pf<?= $pf['id'] ?>">
                <?= htmlspecialchars($pf['name']) ?>
              </label>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="form-text">
        Pilih minimal satu platform tempat kode akan diredeem (Steam, Epic, PS Store, dll).
      </div>
    </div>

    <hr class="my-4">

    <div class="mb-3">
      <label class="form-label">Tampilkan di section:</label>
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="is_best_deal" value="1" id="sBest">
        <label class="form-check-label" for="sBest">Best Deals</label>
      </div>
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="is_trending" value="1" id="sTrend">
        <label class="form-check-label" for="sTrend">Trending</label>
      </div>
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="is_upcoming" value="1" id="sUp">
        <label class="form-check-label" for="sUp">Upcoming</label>
      </div>
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="is_bestseller" value="1" id="sBestSeller">
        <label class="form-check-label" for="sBestSeller">Bestseller</label>
      </div>
    </div>

    <button type="submit" class="btn btn-primary">Simpan</button>
  </form>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
