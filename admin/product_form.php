<?php
require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/../config/config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$editing = $id > 0;

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$platforms  = $pdo->query("SELECT * FROM platforms ORDER BY name")->fetchAll();

// default product
$product = [
    'title' => '',
    'slug' => '',
    'description' => '',
    'price' => 0,
    'discount_percent' => 0,
    'image' => '',
    'is_featured' => 0,
    'is_best_deal' => 0,
    'is_trending' => 0,
    'is_upcoming' => 0,
    'is_bestseller' => 0
];

$selectedCats = [];
$primaryCat = 0;
$selectedPlatforms = [];

if ($editing) {
    // produk
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $p = $stmt->fetch();

    if (!$p) {
        $_SESSION['flash_error'] = 'Produk tidak ditemukan';
        header('Location: products.php');
        exit;
    }
    $product = $p;

    // kategori produk
    $stmt = $pdo->prepare("
        SELECT category_id, is_primary
        FROM product_categories
        WHERE product_id = ?
    ");
    $stmt->execute([$id]);
    foreach ($stmt->fetchAll() as $c) {
        $selectedCats[] = (int)$c['category_id'];
        if ($c['is_primary']) {
            $primaryCat = (int)$c['category_id'];
        }
    }

    // platform produk
    $stmt = $pdo->prepare("
        SELECT platform_id
        FROM product_platforms
        WHERE product_id = ?
    ");
    $stmt->execute([$id]);
    $selectedPlatforms = array_column($stmt->fetchAll(), 'platform_id');
}

include __DIR__ . '/../includes/header.php';
?>

<main class="container py-5">
    <h1 class="h3 mb-4"><?= $editing ? 'Edit' : 'Tambah' ?> Produk</h1>

    <form action="process_add_product.php" method="post">
        <?php if ($editing): ?>
            <input type="hidden" name="id" value="<?= $id ?>">
        <?php endif; ?>

        <!-- BASIC -->
        <div class="mb-3">
            <label class="form-label">Judul</label>
            <input type="text" name="title" class="form-control"
                   value="<?= htmlspecialchars($product['title']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Slug</label>
            <input type="text" name="slug" class="form-control"
                   value="<?= htmlspecialchars($product['slug']) ?>" required>
            <div class="form-text">huruf kecil, tanpa spasi, pakai -</div>
        </div>

        <div class="mb-3">
            <label class="form-label">Deskripsi</label>
            <textarea name="description" rows="6" class="form-control"><?= htmlspecialchars($product['description']) ?></textarea>
        </div>

        <!-- PRICE -->
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Harga</label>
                <input type="number" name="price" class="form-control"
                       value="<?= (int)$product['price'] ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Diskon (%)</label>
                <input type="number" name="discount_percent" class="form-control"
                       value="<?= (int)$product['discount_percent'] ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Nama file gambar</label>
                <input type="text" name="image" class="form-control"
                       value="<?= htmlspecialchars($product['image']) ?>">
            </div>
        </div>

        <!-- CATEGORIES -->
        <div class="mb-4">
            <label class="form-label">Kategori</label>
            <div class="row">
                <?php foreach ($categories as $c): ?>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="categories[]"
                                   value="<?= $c['id'] ?>"
                                   id="cat<?= $c['id'] ?>"
                                   <?= in_array($c['id'], $selectedCats) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="cat<?= $c['id'] ?>">
                                <?= htmlspecialchars($c['name']) ?>
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-3">
                <label class="form-label">Kategori Utama</label>
                <select name="primary_category" class="form-select w-50">
                    <option value="0">-- pilih --</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $primaryCat == $c['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- PLATFORMS -->
        <div class="mb-4">
            <label class="form-label">Platform tersedia</label>
            <div class="row">
                <?php foreach ($platforms as $pf): ?>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="platforms[]"
                                   value="<?= $pf['id'] ?>"
                                   id="pf<?= $pf['id'] ?>"
                                   <?= in_array($pf['id'], $selectedPlatforms) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="pf<?= $pf['id'] ?>">
                                <?= htmlspecialchars($pf['name']) ?>
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- SECTIONS -->
        <div class="mb-4">
            <label class="form-label">Tampilkan di section</label><br>
            <?php
            $sections = [
                'is_featured'   => 'Featured',
                'is_best_deal'  => 'Best Deal',
                'is_trending'   => 'Trending',
                'is_upcoming'   => 'Upcoming',
                'is_bestseller' => 'Bestseller',
            ];
            foreach ($sections as $key => $label):
            ?>
                <div class="form-check form-check-inline">
                    <input class="form-check-input"
                           type="checkbox"
                           name="<?= $key ?>"
                           value="1"
                           id="<?= $key ?>"
                           <?= !empty($product[$key]) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="<?= $key ?>"><?= $label ?></label>
                </div>
            <?php endforeach; ?>
        </div>

        <button class="btn btn-primary"><?= $editing ? 'Simpan Perubahan' : 'Simpan' ?></button>
        <a href="products.php" class="btn btn-secondary">Kembali</a>
    </form>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
