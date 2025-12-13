<?php
require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/../config/config.php';

$has_pc = false;
try {
    $res = $pdo->query("SHOW TABLES LIKE 'product_categories'")->fetchColumn();
    if ($res) $has_pc = true;
} catch (Exception $e) {
    $has_pc = false;
}

if ($has_pc) {
    $sql = "
    SELECT
        p.*,
        MAX(CASE WHEN pc.is_primary = 1 THEN c.name END)               AS primary_category,
        GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ', ')  AS all_categories
    FROM products p
    LEFT JOIN product_categories pc ON pc.product_id = p.id
    LEFT JOIN categories c          ON c.id = pc.category_id
    GROUP BY p.id
    ORDER BY p.created_at DESC
";
} else {
    $sql = "
    SELECT
        p.*,
        c.name AS primary_category,
        c.name AS all_categories
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    ORDER BY p.created_at DESC
    ";
}

$stmt = $pdo->query($sql);
$products = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<main class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Manajemen Produk</h1>

        <div class="d-flex gap-2">
            <a href="../index.php" class="btn btn-outline-secondary btn-sm">Lihat Website</a>
            <a href="product_form.php" class="btn btn-primary btn-sm">+ Tambah Produk</a>
        </div>
    </div>

    <?php if (empty($products)) : ?>
        <div class="alert alert-info">
            Belum ada produk. Tambah dulu lewat tombol <strong>Tambah Produk</strong>.
        </div>
    <?php else : ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th style="width:60px">#</th>
                        <th>Produk</th>
                        <th>Kategori Utama</th>
                        <th>Semua Kategori</th>
                        <th class="text-end">Harga</th>
                        <th class="text-center">Diskon</th>
                        <th class="text-center">Section</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($products as $p): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td style="min-width:260px">
                                <div class="d-flex align-items-center gap-3">
                                    <?php
                                    $img = '../assets/images/' . ($p['image'] ?? '');
                                    if (!empty($p['image']) && file_exists(__DIR__ . '/../assets/images/' . $p['image'])): ?>
                                        <img src="<?= '../assets/images/' . htmlspecialchars($p['image']) ?>" alt="" style="width:84px;height:56px;object-fit:cover;border-radius:6px;">
                                    <?php else: ?>
                                        <div style="width:84px;height:56px;background:#eee;border-radius:6px;display:flex;align-items:center;justify-content:center;color:#888;">No Image</div>
                                    <?php endif; ?>

                                    <div>
                                        <div class="fw-semibold"><?= htmlspecialchars($p['title']); ?></div>
                                        <div class="small text-muted">Slug: <?= htmlspecialchars($p['slug']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><?= $p['primary_category'] ? htmlspecialchars($p['primary_category']) : '<span class="badge bg-secondary">Belum</span>'; ?></td>
                            <td><?= $p['all_categories'] ? htmlspecialchars($p['all_categories']) : '<span class="text-muted small">-</span>'; ?></td>
                            <td class="text-end"><?= rupiah((int)$p['price']); ?></td>
                            <td class="text-center">
                                <?php if ((int)$p['discount_percent'] > 0): ?>
                                    <span class="badge bg-danger">-<?= (int)$p['discount_percent']; ?>%</span>
                                <?php else: ?>
                                    <span class="text-muted small">0%</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center small">
                                <?php if (!empty($p['is_featured']))   echo '<span class="badge bg-info text-dark me-1">Featured</span>'; ?>
                                <?php if (!empty($p['is_best_deal']))  echo '<span class="badge bg-success me-1">Best Deals</span>'; ?>
                                <?php if (!empty($p['is_trending']))   echo '<span class="badge bg-warning text-dark me-1">Trending</span>'; ?>
                                <?php if (!empty($p['is_upcoming']))   echo '<span class="badge bg-primary me-1">Upcoming</span>'; ?>
                                <?php if (!empty($p['is_bestseller'])) echo '<span class="badge bg-light text-dark me-1">Bestseller</span>'; ?>
                                <?php
                                if (empty($p['is_featured']) && empty($p['is_best_deal']) && empty($p['is_trending']) && empty($p['is_upcoming']) && empty($p['is_bestseller'])) {
                                    echo '<span class="text-muted">-</span>';
                                }
                                ?>
                            </td>
                            <td class="text-end">
                                <a href="product_form.php?id=<?= $p['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                <a href="product_delete.php?id=<?= $p['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus produk ini?');">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
