<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/_guard.php';

if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Forbidden');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['role'])) {
    $user_id = (int)$_POST['user_id'];
    $role    = $_POST['role'] === 'admin' ? 'admin' : 'user';

    if ($user_id !== (int)$_SESSION['user_id']) {
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$role, $user_id]);
    }

    header('Location: users.php');
    exit;
}

$stmt = $pdo->query("SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin · Kelola Akun User</title>
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
    <h1 class="h3 mb-4">Kelola Akun User</h1>

    <div class="table-responsive">
        <table class="table table-dark table-striped table-sm align-middle">
            <thead>
            <tr>
                <th>#ID</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Role</th>
                <th>Terdaftar</th>
                <th>Aksi</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= (int)$u['id'] ?></td>
                    <td><?= htmlspecialchars($u['name']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= htmlspecialchars($u['role']) ?></td>
                    <td><?= htmlspecialchars($u['created_at']) ?></td>
                    <td>
                        <?php if ($u['id'] === (int)$_SESSION['user_id']): ?>
                            <span class="text-muted small">Akun sendiri</span>
                        <?php else: ?>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                <input type="hidden" name="role"
                                       value="<?= $u['role'] === 'admin' ? 'user' : 'admin' ?>">
                                <button type="submit"
                                        class="btn btn-sm <?= $u['role'] === 'admin' ? 'btn-outline-warning' : 'btn-outline-success' ?>">
                                    <?= $u['role'] === 'admin' ? 'Jadikan User' : 'Jadikan Admin' ?>
                                </button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
