<?php
require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/../config/config.php';

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$title = trim($_POST['title'] ?? '');
$slug = trim($_POST['slug'] ?? '');
$description = $_POST['description'] ?? '';
$price = (int)($_POST['price'] ?? 0);
$discount_percent = (int)($_POST['discount_percent'] ?? 0);
$image = trim($_POST['image'] ?? '');
$is_featured = isset($_POST['is_featured']) ? 1 : 0;
$is_best_deal = isset($_POST['is_best_deal']) ? 1 : 0;
$is_trending = isset($_POST['is_trending']) ? 1 : 0;
$is_upcoming = isset($_POST['is_upcoming']) ? 1 : 0;
$is_bestseller = isset($_POST['is_bestseller']) ? 1 : 0;

$categories = $_POST['categories'] ?? [];
$primary_category = (int)($_POST['primary_category'] ?? 0);

if ($title === '' || $slug === '') {
    $_SESSION['flash_error'] = 'Judul dan slug wajib diisi.';
    header('Location: add_product.php');
    exit;
}

try {
    $pdo->beginTransaction();
    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE products SET title=?, slug=?, description=?, price=?, discount_percent=?, image=?, is_featured=?, is_best_deal=?, is_trending=?, is_upcoming=?, is_bestseller=? WHERE id=?");
        $stmt->execute([$title, $slug, $description, $price, $discount_percent, $image, $is_featured, $is_best_deal, $is_trending, $is_upcoming, $is_bestseller, $id]);
        $product_id = $id;
    } else {
        $stmt = $pdo->prepare("INSERT INTO products (title, slug, description, price, discount_percent, image, is_featured, is_best_deal, is_trending, is_upcoming, is_bestseller) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $slug, $description, $price, $discount_percent, $image, $is_featured, $is_best_deal, $is_trending, $is_upcoming, $is_bestseller]);
        $product_id = $pdo->lastInsertId();
    }

    $pdo->prepare("DELETE FROM product_categories WHERE product_id = ?")->execute([$product_id]);

    if (!empty($categories) && is_array($categories)) {
        foreach ($categories as $catid) {
            $is_primary = ($primary_category == (int)$catid) ? 1 : 0;
            $ins = $pdo->prepare("INSERT INTO product_categories (product_id, category_id, is_primary) VALUES (?, ?, ?)");
            $ins->execute([$product_id, (int)$catid, $is_primary]);
        }
    }
$platforms = $_POST['platforms'] ?? [];

$pdo->prepare("DELETE FROM product_platforms WHERE product_id = ?")
    ->execute([$product_id]);

if (!empty($platforms) && is_array($platforms)) {
    $stmtPf = $pdo->prepare("
        INSERT INTO product_platforms (product_id, platform_id)
        VALUES (?, ?)
    ");
    foreach ($platforms as $pfId) {
        $stmtPf->execute([$product_id, (int)$pfId]);
    }
}

    $pdo->commit();
    $_SESSION['flash_success'] = 'Produk berhasil disimpan.';
    header('Location: products.php');
    exit;
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['flash_error'] = 'Gagal menyimpan produk: ' . $e->getMessage();
    header('Location: products.php');
    exit;
}
