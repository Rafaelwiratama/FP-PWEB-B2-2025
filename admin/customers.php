<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/_guard.php';

// Ambil daftar customer + jumlah order
$sql = "
    SELECT u.id, u.name, u.email, u.created_at,
           COUNT(o.id) AS total_orders
    FROM users u
    LEFT JOIN orders o ON o.user_id = u.id
    WHERE u.role = 'user'
    GROUP BY u.id, u.name, u.email, u.created_at
    ORDER BY u.created_at DESC
";
$customers = $pdo->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin · Data Customer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">
<nav class="navbar navbar-dark bg-black">
    <div class="container-fluid">
        <span class="navbar-brand mb-0 h1">Slasher Admin</span>
        <div class="d-flex gap-2">
            <a href="index.php" class="btn btn-outline-light btn-sm">Dashboard</a>
            <a href="../logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container py-4">
    <h1 class="h3 mb-4">Data Customer</h1>

    <?php if (!$customers): ?>
        <p class="text-muted">Belum ada customer.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-dark table-striped table-sm align-middle">
                <thead>
                <tr>
                    <th>#ID</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Total Order</th>
                    <th>Terdaftar</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($customers as $c): ?>
                    <tr>
                        <td><?= (int)$c['id'] ?></td>
                        <td><?= htmlspecialchars($c['name']) ?></td>
                        <td><?= htmlspecialchars($c['email']) ?></td>
                        <td><?= (int)$c['total_orders'] ?></td>
                        <td><?= htmlspecialchars($c['created_at']) ?></td>
                        <td>
                            <?php if ($c['total_orders'] > 0): ?>
                                <a href="orders.php?user_id=<?= (int)$c['id'] ?>" class="btn btn-sm btn-outline-info">
                                    Lihat Order
                                </a>
                            <?php else: ?>
                                <span class="text-muted small">Tidak ada order</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
