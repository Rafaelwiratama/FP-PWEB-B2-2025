<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/_guard.php';

$success = null;
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = [
        'hero_title',
        'hero_subtitle',
        'hero_description',
        'hero_price',
        'contact_admin_name',
        'contact_admin_email',
        'contact_admin_whatsapp',
    ];

    foreach ($fields as $key) {
        $value = trim($_POST[$key] ?? '');
        $stmt  = $pdo->prepare("
            INSERT INTO settings (`key`, `value`) VALUES (?, ?)
            ON DUPLICATE KEY UPDATE value = VALUES(value)
        ");
        $stmt->execute([$key, $value]);
    }

    $success = 'Konten berhasil disimpan.';
}

// Ambil nilai untuk form
$data = [
    'hero_title'             => get_setting('hero_title', 'THE LAST OF US PART II'),
    'hero_subtitle'          => get_setting('hero_subtitle', 'FEATURED TODAY'),
    'hero_description'       => get_setting('hero_description', ''),
    'hero_price'             => get_setting('hero_price', '749000'),
    'contact_admin_name'     => get_setting('contact_admin_name', ''),
    'contact_admin_email'    => get_setting('contact_admin_email', ''),
    'contact_admin_whatsapp' => get_setting('contact_admin_whatsapp', ''),
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin · Kelola Konten</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">
<nav class="navbar navbar-dark bg-black">
    <div class="container-fluid">
        <span class="navbar-brand mb-0 h1">Slasher Admin</span>
        <div class="d-flex gap-2">
            <a href="../index.php" class="btn btn-outline-light btn-sm">Lihat Website</a>
            <a href="../logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container py-4">
    <h1 class="h3 mb-4">Kelola Konten</h1>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post" class="row g-4">
        <div class="col-lg-7">
            <div class="card bg-secondary-subtle text-dark">
                <div class="card-body">
                    <h5 class="card-title">Hero / Banner Utama</h5>

                    <div class="mb-3">
                        <label class="form-label">Subjudul Kecil (mis. "FEATURED TODAY")</label>
                        <input type="text" name="hero_subtitle" class="form-control"
                               value="<?= htmlspecialchars($data['hero_subtitle']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Judul Besar</label>
                        <input type="text" name="hero_title" class="form-control"
                               value="<?= htmlspecialchars($data['hero_title']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="hero_description" rows="4"
                                  class="form-control"><?= htmlspecialchars($data['hero_description']) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Harga (angka saja)</label>
                        <input type="number" name="hero_price" class="form-control"
                               value="<?= htmlspecialchars($data['hero_price']) ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card bg-secondary-subtle text-dark mb-3">
                <div class="card-body">
                    <h5 class="card-title">Contact Admin / Pemilik Web</h5>

                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" name="contact_admin_name" class="form-control"
                               value="<?= htmlspecialchars($data['contact_admin_name']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="contact_admin_email" class="form-control"
                               value="<?= htmlspecialchars($data['contact_admin_email']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">WhatsApp</label>
                        <input type="text" name="contact_admin_whatsapp" class="form-control"
                               value="<?= htmlspecialchars($data['contact_admin_whatsapp']) ?>">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100">Simpan Perubahan</button>
        </div>
    </form>
</div>
</body>
</html>
